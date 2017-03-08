<?php
/**
* @file department.inc.php
* Contains the department class.
**/


/**
* @class department
* department object for digitial measures, shows information for the factuly and staff that belong to selected department
**/

class departmentlist
{

  private $username;
  private $config;

  /**
  * Constructor for department object.
  * @param string $username of factuly/staff in the department
  * @param array $config of settings from config file
  **/
  public function __construct($username,$config)
  {
    $this->username= $username;
    $this->config= $config;

  }
  /**
  * Display the factuly/staff information.
  * @return the factuly/staff information.
  **/
  public function display()
  {
    require_once('profile.inc.php');
    //checks to see if member is active
    $profile = new profile($this->username,$this->config);

    if($profile->active())
    {
      $ret  = '<div class="inner">';
      $ret.= $profile->linkname(); //creates a link to the members page on their name
      $ret.= $profile->education("true","range");
      $ret .= $profile->picture();//factuly/staff picture from titan files
      $ret .= $profile->contact();//factuly/staff contact information from contact class
      $ret .= '</div>';
      return $ret;

    }
  }
}

?>
