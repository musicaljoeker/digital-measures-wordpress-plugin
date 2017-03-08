<?php
/**
* @file profMember.inc.php
* Contains the profMember class.
**/


/**
* @class profMember
* profMember object for digitial measures, represents a research profMember, teaching profMember or businesss contract.
**/
class profMember
{
  private $type;
  private $title;
  private $org;
  private $role;
  private $responsibilities;
  private $dtmstart;
  private $dtmend;


  /**
  * Constructor for profMember object.
  * @param string $title The title of the Proffesional Membership.
  * @param string $org The org for the of the Proffesional Membership.
  * @param string $role The type of role in the Proffesional Membership.
  * @param string $responsibilities The responsibilities of the member in Proffesional Membership
  * @param string $dtmstart The start of the member in Proffesional Membership
  * @param string $dtmend The end of the member in Proffesional Membership
  **/
  public function __construct($type,$title,$org,$role,$responsibilities,$dtmstart,$dtmend)
  {
    $this->type = $type;
    $this->title = $title;
    $this->org = $org;
    $this->role = $role;
    $this->responsibilities = $responsibilities;
    $this->dtmstart = $dtmstart;
    $this->dtmend = $dtmend;

  }
  /**
  * Display the profMember.
  * @return the HTML of the profMember.
  **/
  public function display($limit = 0)
  {

    $ret  = '<div class="profMember">';
    $ret .= '<span class="type">'.$this->type.', </span>';
    $ret .= '<span class="type">'.$this->title.', </span>';
    $ret .= '<span class="type">'.$this->org.', </span>';
    $ret .= '<span class="type">'.$this->role.', </span>';
    $ret .= '<span class="type">'.$this->responsibilities.', </span>';
    if(is_array($this->dtmstart))
    {
      $i = count($this->dtmstart);

    }
    if(is_array($this->dtmend))
    {
      $i = count($this->dtmend);
      foreach($this->dtmend as $dte)
      {
        $ret .= '<span class="dtmend">' . $dte . ($i > 1 ? ',' : '') . '</span> ' ;
        $i--;
      }
    }
    $ret .= '</dev>';
    return $ret;

  }
}

?>
