<?php
/**
* @file expertise.inc.php
* Contains the expertise class.
**/


/**
* @class expertise
* expertise object for digitial measures, represents a expertises of the member.
**/
class expertise
{

  private $expertise;
  /**
  * Constructor for expertise object.
  * @param array $expertise What the expertises of the member has.
  **/
  public function __construct($expertise)
  {
    $this->expertise = $expertise;
  }
  /**
  * Display the expertise.
  * @return the HTML of the expertise.
  **/
  public function display()
  {
    $ret = '<div class="expertise">'."Expertise: ";
    if(is_array($this->expertise))
    {
      $i = count($this->expertise);
      foreach($this->expertise as $exp)
      {
        $ret .= '<span class="expertise">' . $exp . ($i > 1 ? ',' : '') . '</span> ' ;
        $i--;
      }
    }
    $ret .= '</div>';
    return $ret;

  }
}

?>
