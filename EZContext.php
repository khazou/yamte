<?php

namespace eztpl;

require_once 'EZException.php';
require_once 'EZFunctions.php';

class EZContext
{
  private $name;
  private $sourceCode;
  private $varList = array();
  private $subContextList = array();
  private $tempCode;
  private $generatedCode;
  private $used = false;

  /**
   * Simple constructor for the context (is only invoked by the EZtpl::createContext() method)
   * @param $name The name of the context
   * @param $sourceCode The context's source code
   */
  public function __construct($name, $sourceCode)
  {
    $this->name = $name;
    $this->sourceCode = $sourceCode;
    $this->parseCode();
  }

  /**
   * Sets a variable in the context
   * @param $varName name of the variable
   * @param $varValue value of the variable
   */
  public function setVariable($varName, $varValue)
  {
    if (in_array($varName, $this->varList)) {
      $this->tempCode = preg_replace('/\{#' . $varName . '\}/', $varValue, $this->tempCode);
    } else {
      throw new EZException(6, array($this->name, $varName));
    }
  }

  /**
   * Closes a context and generates the code
   */
  public function closeContext()
  {
    if (!$this->used) {
      throw new EZException(8, $this->name);
    }
    $this->generateCode();

    $this->used = false;
  }

  /**
   * initializes the context
   */
  public function init()
  {
    if ($this->used) {
      throw new EZException(7, $this->name);
    }
    $this->used = true;
    $this->tempCode = $this->sourceCode;
  }

  /**
   * Resets the context after generating the code
   */
  public function reset(){
    $this->used = false;
    $this->generated = NULL;
  }

  /**
   * Simple accessor for the code that has been generated
   */
  public function getGeneratedCode()
  {
    return $this->generatedCode;
  }

  /**
   * Generates the code for the context
   */
  private function generateCode()
  {
    if ($this->used) {
      // Replace all unused variables by an empty string
      $this->tempCode = preg_replace("/\{#([a-zA-Z0-9_]+)\}/", "", $this->tempCode);
      // Generate all children contexts code
      if (count($this->subContextList)) {
        foreach (array_keys($this->subContextList) as $subContext) {
          // Generate the children contexts code and reset them
          $text = ($this->subContextList[$subContext]->used) ? $this->subContextList[$subContext]->generateCode() : $this->subContextList[$subContext]->generatedCode;
          $this->tempCode = preg_replace("/(\|$subContext\|)/", $text, $this->tempCode);
          $this->subContextList[$subContext]->reset();
        }
      }
      $this->generatedCode .= $this->tempCode;
      return $this->generatedCode;
    } else {
      return $this->generatedCode;
    }
  }

  /**
   * Adds a child context to the $subContextList array
   * @param $subContext the instance of the child context
   */
  public function addSubContext(&$subContext)
  {
    $this->subContextList[$subContext->name] = &$subContext;
  }

  /**
   * Parses the context's source code for variables
   */
  private function parseCode()
  {
    $regexp = "/\{#([a-zA-Z0-9_]+)\}/";
    preg_match_all($regexp, $this->sourceCode, $matchedData);
    $this->varList = array_merge($this->varList, $matchedData[1]);
  }


}
