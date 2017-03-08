<?php
/**
* @file picture.inc.php
* Contains the picture class.
**/


/**
* @class picture
* picture object for digitial measures, represents a research picture, teaching picture or businesss contract.
**/
class picture
{

  private $picture;


  /**
  * Constructor for picture object.
  * @param string $picrure is the end URL of the picture of the factuly/staff
  **/
  public function __construct($picture)
  {
    $this->picture=$picture;

  }


  /**
  * Display the picture.
  * @return the HTML of the picture.
  **/
  public function display()
  {
    $ret = '<div class= "picture">';
    $ret .= '<img src = https://titanfiles.uwosh.edu/groups/COBDigitalMeasures/' .$this->picture.' style="width:102px;height:128px;" align ="right">';
    $ret.= '</div>';
    return $ret;
  }
}

?>
