<?php

namespace eztpl;

class EZException extends \Exception
{
  public $message;
  private $errCode;
  private $params;
  public function __construct($errCode, $params)
  {
    $this->errCode = $errCode;
    $this->params = $params;
  }

  public function getTraceData()
  {
    switch ($this->errCode) {
      case 1:
        return "The template file $this->params is not a valid file";
        break;
      case 2:
        return "The template file $this->params is not readable";
        break;
      case 3:
        return "The context $this->params is not properly closed";
        break;
      case 4:
        return "The context $this->params is already defined. Please use another name";
        break;
      default:
        return "There is an error !<br />Error Code : $this->errCode<br />Parameters : $this->params<br />";
    }
  }
}
