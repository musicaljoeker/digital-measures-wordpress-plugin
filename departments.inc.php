<?php
/**
* @file department.inc.php
* Contains the department class.
**/


/**
* @class department
* department object for digitial measures, shows information for the factuly and staff that belong to selected department
**/

class departments
{

  private $departments;
  private $parentTitle;
  private $departmentNoSpace;
  private $parentID;
  /**
  * Constructor for department object.
  * @param string $departments each unique department
  **/
  public function __construct($departments)
  {
    $this->departments= $departments;
    $this->departmentNoSpace = str_replace(' ','-',$this->departments);
  }

  //creates the page for each department is one does not already exist and populates with with members from the department
  public function createpage()
  {
    $this->parentID =get_the_ID();
    $this->parentTitle = get_the_title($parentID);

    if(null==get_page_by_title($this->departmentNoSpace))
    {
      try
      {
        $this->new_page = array(
          'slug' => $this->departmentNoSpace,
          'title' => $this->departmentNoSpace,
          'content' => '[digitalmeasures type="departmentlist" department="'.$this->departments.'"]'
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
          'post_type' 	=> 'page',
          'post_name'	 => $this->new_page['slug'],
          'comment_status' => 'closed',
          'ping_status' => 'closed',
          'post_content' => $this->new_page['content'],
          'post_status' => 'publish',
          'post_parent'=>$this->parentID, //ID number of directory page
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
  * Display the department name and creates a link for the department page.
  * @return the department name.
  **/
  public function display()
  {

    $this->createpage();
    $ret= '<div id= "departments"><a href="'.$_SERVER['SERVER_NAME'].':8888/'.$this->parentTitle."/".$this->departmentNoSpace.'" >'. $this->departments.'</a></div>';
    $ret.= "A listing of faculty and/or staff associated with the ".$this->departments." Department.";
    return  $ret;
  }
}

?>
