<?php
/**
* @file memberlist.inc.php
* Contains the memberlist class.
**/


/**
* @class memberlist
* memberlist object for digitial measures, represents the memberlist information
**/
class memberlist
{
  private $username;
  private $first_name;
  private $last_name;
  private $middle_name;
  private $newpage;
  private $new_page_id;
  private $parentID;

  /**
  * Constructor for memberlist object.
  * @param string $prefix The title of the memberlist.
  * @param string $first_name The first name of the member
  * @param string $last_name The last name of the member
  * @param string $middle_name The middle name of the member
  **/
  public function __construct($username,$first_name,$last_name,$middle_name)
  {
    $this->username = $username;
    $this->first_name = $first_name;
    $this->last_name = $last_name;
    $this->middle_name = $middle_name;
  }
  //if not page already exists then it creates one and populates it with member details
  public function createpage()
  {
    $this->parentID =get_the_ID();
    $this->parentTitle = get_the_title($parentID);
    if(null==get_page_by_title($this->username))
    {
      try
      {
        $this->new_page = array(
          'slug' => $this->username,
          'title' => $this->username,
          'content' => '[digitalmeasures type="fullname" username="'.$this->username.'"]
          [digitalmeasures type="picture" username="'.$this->username.'"]
          [digitalmeasures type="education" username="'.$this->username.'" show_location="true" show_dates="range"]
          [digitalmeasures type="contact" username="'.$this->username.'"]
          [digitalmeasures type="publication" username="'.$this->username.'"]
          [digitalmeasures username="'.$this->username.'" published_only="true" profile_only="false" limit="5" instance="default" format="apa"]
          [digitalmeasures type="grants" username="'.$this->username.'"]'
        );
      }
      catch(Exception $e)
      {
        echo $e->message;
      }

      try
      {
        $this->new_page_id = wp_insert_post(array(
          'post_title' => $this->new_page['title'],
          'post_type'	=> 'page',
          'post_name'	 => $this->new_page['slug'],
          'comment_status' => 'closed',
          'ping_status' => 'closed',
          'post_content' => $this->new_page['content'],
          'post_status' => 'publish',
          'post_parent'=> $this->parentID, //ID number of directory page
          'post_author' => 1,
          'menu_order' => 0
        ));
      }
      catch(Exception $e)
      {
        echo $e->message;
      }
    }
    else
    {

    }
  }
  /**
  * Display the name of member as a link to their page.
  * @return the HTML of the information memberlist.
  **/
  public function display()
  {
    $this->createpage();                                               //for testing purposes the 8888 is there and should be removed
    $ret='<div id= "nameList"><a href="'.$_SERVER['SERVER_NAME'].":8888/".$this->parentTitle."/".$this->username.'">'. $this->first_name." ".$this->middle_name." ".$this->last_name. '</a></div>';
    return $ret;

  }
}

?>
