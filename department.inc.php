<?php
/**
* @file department.inc.php
* Contains the department class.
**/


/**
* @class department
* department object for digitial measures, shows categories that a faculty/staff member belongs to.
**/
class department
{
  private $indexKey;
  private $entryKey;
  private $text;



  /**
  * Constructor for department object.
  * @param string $indexKey type of the faculty/staff.
  * @param string $entryKey The department key of the faculty/staff.
  * @param string $text The text of the department of the faculty/staff.
  **/
  public function __construct($indexKey,$entryKey,$text)
  {
    $this->indexKey = $indexKey;
    $this->entryKey= $entryKey;
    $this->text= $text;

  }


  /**
  * Display the department.
  * @return the HTML of the department.
  **/
  public function display()
  {
    $ret='<div id="department">'.$this->indexKey.":".$this->text .'</div>';

    return $ret;
  }
}

?>
