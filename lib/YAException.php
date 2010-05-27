<?php

/** EZException
 * The exception class dedicated to EZtpl
 */
class EZException extends \Exception
{
  public $message;
  private $errCode;
  private $params;

  /**
   * The constructor of the exception class
   * @param $errCode Error code of the exception
   * @param $params Parameters of the exception (can be an array or a string)
   */
  public function __construct($errCode, $params)
  {
    $this->errCode = $errCode;
    $this->params = $params;
  }

  /**
   * Returns the error message
   */
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
      case 5:
        return "The context $this->params isn't closed";
        break;
      case 6:
        return "The variable " . $this->params[1] . " can't be assigned in the " . $this->params[0] . " context";
        break;
      case 7:

        break;
      case 8:

        break;
      default:
        return "There is an error !<br />Error Code : $this->errCode<br />Parameters : $this->params<br />";
    }
  }
}
