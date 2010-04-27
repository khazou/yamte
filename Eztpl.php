<?php

namespace eztpl;



require_once 'EZException.php';
require_once 'EZContext.php';
require_once 'EZFunctions.php';

class EZtpl
{

  private $templateData;
  private $contexts = array();

  /**
   * Simple constructor taking the template file as a parameter
   * @param $templateFile Template file to use
   */
  public function __construct($templateFile)
  {
    if (is_file($templateFile)) {
      if ($fileData = fopen($templateFile, 'r')) {
        $this->templateData = fread($fileData, filesize($templateFile));
        $this->parseTemplate($this->templateData);
      } else {
        throw new EZException(2, $templateFile);
      }
    } else {
      throw new EZException(1, $templateFile);
    }
  }

  /**
   * Function displaying the template after processing the data
   */
  public function display()
  {
  }

  public function setVariable($prefixedVarName, $varValue)
  {
    //If the variable has a prefix
    $varData = explode(".", $prefixedVarName);
    if (count($varData) == 2) {
      $context = $varData[0];
      $variable = $varData[1];
    } else {
      $context = "|root|";
      $variable = $varData[0];
    }
  }

  /**
   * Function returning the first context found in the source
   * @param $templateSource the source to check
   */
  private function getContextName($templateSource)
  {
    $regexp = "<!--EZT_([a-zA-Z0-9_]+)-->";
    if(preg_match($regexp, $templateSource, $matchedData)) {
      return $matchedData[1];
    } else {
      return false;
    }
  }

  /**
   * Function returning true if the context has an end tag
   * @param $templateSource the source to check
   * @param $contextName name of the context
   */
  private function hasContextEndTag($templateSource, $contextName)
  {
    $regexp = "<!--/EZT_" . $contextName . "-->";
    preg_match($regexp, $templateSource, $matchedData);
    if (count($matchedData)) {
      return true;
    } else {
      throw new EZException(3, $contextName);
    }
  }

  /**
   * Function returning the data inside the context tags
   * @param $templateSource the source to check
   * @param $contextName the name of the context to retrieve
   */
  private function getContextSource($templateSource, $contextName, $type = 0)
  {
    preg_match("#<!--EZT_$contextName-->(.*)<!--/EZT_$contextName-->#s", $templateSource, $matchedData);
    return $matchedData[$type];
  }

  /**
   * Function parsing the template looking for defined contexts
   * @param $templateData the source to check
   * @param $context name of the context
   */
  private function parseTemplate($templateData, $context = "|root|")
  {
    // If the context already exists, throw an exception
    if (isset($this->contexts[$context])) {
      throw new EZException(4, $context);
    } else {
      // Save the context source in the array
      $this->contexts[$context]['src'] = $templateData;
      // Parse the contexts in the source $templateData
      while ($childContext = $this->getContextName($this->contexts[$context]['src'])) {
        // If the context isn't closed in the source, throw an exception
        if (!$this->hasContextEndTag($this->contexts[$context]['src'], $childContext)) {
          throw new EZException(5, $childContext);
        } else {
          // Execute the parseTemplate method for the context found
          $this->parseTemplate($this->getContextSource($this->contexts[$context]['src'], $childContext, 1), $childContext);
          // Add the context to the list of childs
          $this->contexts[$context]['childs'][] = $childContext;
          // Replace the context data by an identifier in the source
          $this->contexts[$context]['src'] = str_replace($this->getContextSource($this->contexts[$context]['src'], $childContext, 0),"|$childContext|", $this->contexts[$context]['src']);
          if (count(explode("|$childContext|", $this->contexts[$context]['src'])) > 2) {
            throw new EZException(6, $childContext);
          }
        }
      }
    }
  }


}
