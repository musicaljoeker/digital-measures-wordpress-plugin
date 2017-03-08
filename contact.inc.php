<?php
/**
* @file contact.inc.php
* Contains the contact class.
**/


/**
* @class contact
* contact object for digitial measures, represents the contact information
**/
class contact
{
  private $prefix;
  private $first_name;
  private $last_name;
  private $middle_name;
  private $office;
  private $phone;
  private $email;
  private $expertise;



  /**
  * Constructor for contact object.
  * @param string $prefix The title of the contact.
  * @param string $first_name The first name of the member
  * @param string $last_name The last name of the member
  * @param string $middle_name The middle name of the member
  * @param string $office The office of the member
  * @param string $phone The phone number of the member
  * @param string $email The email of the member
  * @param array $expertise The expertises of the member
  *
  **/
  public function __construct($prefix,$first_name,$last_name,$middle_name,$office,$phone,$email,$expertise)
  {
    $this->prefix = $prefix;
    $this->first_name = $first_name;
    $this->last_name = $last_name;
    $this->middle_name = $middle_name;
    $this->office = $office;
    $this->phone = $phone;
    $this->email = $email;
    $this->expertise = $expertise;




  }
  /**
  * Display the infromation contact.
  * @return the HTML of the information contact.
  **/
  public function display()
  {
    //  $ret .= '<div class= "name-label">Name:</td><td class="name">'. $this->first_name." ".$this->last_name. '</div>';
    $ret .= '<div class= "office-label">Office:</td><td class="building">'. $this->office. '</div>';
    $ret .= '<div class= "phone-label">Office Phone:</td><td class="phone">' .$this->phone . '</div>';
    $ret .= '<div class= "email-label">Email:</td><td class="email"><a href="'.$this->email.'"></a>'. $this->email .'</div>';
    $ret .= '<div class= "expertise-label">Expertrise:';
    if(is_array($this->expertise))
    {
      $i = count($this->expertise);
      foreach($this->expertise as $exp)
      {
        $ret .= '<div class="expertise">' . $exp . ($i > 1 ? ',' : '') . '</div> ' ;
        $i--;
      }
    }
    return $ret;

  }
}

?>
