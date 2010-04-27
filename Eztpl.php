<?php

namespace eztpl;



require_once 'EZException.php';
require_once 'EZContext.php';
require_once 'EZFunctions.php';

class EZtpl
{

  private $templateData;
  private $contexts = array();
  private $contextInstances = array();

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
        $this->createContext();
        $this->openContext();
      } else {
        throw new EZException(2, $templateFile);
      }
    } else {
      throw new EZException(1, $templateFile);
    }
  }

  /**
   * Function displaying the template after processing the data
   * @param $context the name of the context to display
   */
  public function display($context = "|root|")
  {
    $this->closeContext($context);
    echo $this->contextInstances[$context]->getGeneratedCode();
  }

  /**
   * Function setting the value of a variable in the template
   * @param $prefixedVarName Name of the variable with the context as a prefix if the variable is a context variable Ex: my_context.my_var
   * @param $varValue Value of the variable
   */
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
    $this->contextInstances[$context]->setVariable($variable, $varValue);
    return $this;
  }

  /**
   * Initializes a context to work with
   * @param $context The context name
   */
  public function openContext($context = "|root|")
  {
    if (!isset($this->contextInstances[$context])) {
      throw new EZException(9, $context);
    }
    $this->contextInstances[$context]->init();
    return $this;
  }

  /**
   * Closes a context
   * @param $context The context name
   */
  public function closeContext($context = "|root|")
  {
    if (!isset($this->contextInstances[$context])) {
      throw new EZException(9, $context);
    }
    $this->contextInstances[$context]->closeContext();
    return $this;
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
   * @param $type 0 if you want the context with the tags and 1 if you don't want the tags
   */
  private function getContextSource($templateSource, $contextName, $type = 0)
  {
    preg_match("#<!--EZT_$contextName-->(.*)<!--/EZT_$contextName-->#s", $templateSource, $matchedData);
    return $matchedData[$type];
  }

  /**
   * Creates the instance of the context when parsing the code
   * @param $context The name of the context to instanciate
   */
  private function createContext($context = "|root|")
  {
    $this->contextInstances[$context] = new EZContext($context, $this->contexts[$context]['src']);

    // Add subcontexts if found
    if(@count($this->contexts[$context]['children'])) {
      foreach ($this->contexts[$context]['children'] as $childContext) {
        $this->createContext($childContext);
        $this->contextInstances[$context]->addSubContext($this->contextInstances[$childContext]);
      }
    }
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
          $this->contexts[$context]['children'][] = $childContext;
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
