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
  private $used;

  public function __construct($name, $sourceCode)
  {
    $this->name = $name;
    $this->sourceCode = $sourceCode;
    //echo $this->sourceCode;
    $this->parseCode();
    $this->init();
  }

  public function setVariable($varName, $varValue)
  {
    if (in_array($varName, $this->varList)) {
      $this->tempCode = preg_replace('/\{#' . $varName . '\}/', $varValue, $this->tempCode);
      //echo $this->tempCode;
    } else {
      throw new EZException(6, array($this->name, $varName));
    }
  }

  public function closeContext()
  {
    return $this->generateCode();
  }

  private function init()
  {
    $this->used = true;
    $this->tempCode = $this->sourceCode;
  }

  public function generateCode()
  {
    if ($this->used) {
      $this->generatedCode = $this->tempCode;
      return $this->generatedCode;
    } else {
      return $this->generatedCode;
    }
  }

  private function parseCode()
  {
    $regexp = "/\{#([a-zA-Z0-9_]+)\}/";
    preg_match_all($regexp, $this->sourceCode, $matchedData);
    $this->varList = array_merge($this->varList, $matchedData[1]);
  }


}
