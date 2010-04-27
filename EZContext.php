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

  public function __construct($name, $sourceCode)
  {
    $this->name = $name;
    $this->sourceCode = $sourceCode;
    $this->parseCode();
  }

  public function setVariable($varName, $varValue)
  {
    if (in_array($varName, $this->varList)) {
      $this->tempCode = preg_replace('/\{#' . $varName . '\}/', $varValue, $this->tempCode);
    } else {
      throw new EZException(6, array($this->name, $varName));
    }
  }

  public function closeContext()
  {
    if (!$this->used) {
      throw new EZException(8, $this->name);
    }
    $this->generateCode();

    $this->used = false;
  }

  public function init()
  {
    if ($this->used) {
      throw new EZException(7, $this->name);
    }
    $this->used = true;
    $this->tempCode = $this->sourceCode;
  }

  public function reset(){
    $this->used = false;
    $this->generated = NULL;
  }

  public function getGeneratedCode()
  {
    return $this->generatedCode;
  }

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



  public function addSubContext(&$subContext)
  {
    $this->subContextList[$subContext->name] = &$subContext;
  }

  private function parseCode()
  {
    $regexp = "/\{#([a-zA-Z0-9_]+)\}/";
    preg_match_all($regexp, $this->sourceCode, $matchedData);
    $this->varList = array_merge($this->varList, $matchedData[1]);
  }


}
