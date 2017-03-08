<?php
/**
* @file alldepartments.inc.php
* The meat of the applications department level happens within this file.
* @author Jeremy Streich
* @author with edits from Taylor Korthals
**/


// function dm_startsWith($haystack, $needle)
// {
//     return $needle === "" || strpos($haystack, $needle) === 0;
// }
//
// function dm_endsWith($haystack, $needle)
// {
//     return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
// }

define
(
  'dm_months',
  serialize(array
  (
    1  => 'January',
    2  => 'February',
    3  => 'March',
    4  => 'May',
    5  => 'June',
    6  => 'July',
    7  => 'August',
    9  => 'September',
    10 => 'October',
    11 => 'November',
    12 => 'December'
    ))
  );

  require_once('departmentlist.inc.php');
  require_once('memberlist.inc.php');
  require_once('profile.inc.php');
  require_once('departments.inc.php');



  /**
  * @class alldepartments
  * This object represents all faculty from a specific department and their infromation. Fetches information from Digital Measures.
  **/
  class alldepartments
  {
    private $username;
    private $password;
    private $departments;
    private $memberlist;

    /**
    * Constructor for the alldepartments class which popluates the departments by fetching information from Digital Measures
    * @param string $d the selected department to gather information for.
    * @param array $config Activity Insight Configuration
    **/
    function __construct($d,$config = null)
    {
      global $wpdb;
      $this->department = $d;
      $this->username = $config['username'];
      $this->password = $config['password'];
      $ret = '';
      //URL for all member xml data sheet
      $ch = curl_init('https://digitalmeasures.com/login/service/v4/SchemaData/INDIVIDUAL-ACTIVITIES-Business/PCI');
      curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 0);
      curl_setopt($ch, CURLOPT_USERPWD, "$this->username:$this->password");
      curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
      $ret = curl_exec($ch);
      curl_close($ch);

      $this->username = '';
      $this->password = '';

      //Make parsing easier by removing dmd namespace
      $ret = str_replace('dmd:','',$ret);
      // echo $ret;
      $dom = new DOMDocument;
      $good = $dom->loadXML($ret);
      if (!$good)
      {
        echo $ret;
        exit;
      }
      $xml = simplexml_import_dom($dom);
      $this->departmentlist = array();
      $this->departments = array();
      $alldeps=array();
      //passes the config file information to the departmentlist class for use in the profile class
      $configs= array("username"=>$config['username'], "password"=>$config['password'], "key"=>$config['key']);

      //gets members from the specified department
      try
      {
        foreach($xml->Record as $entery)
        {
          $a;
          foreach($entery->IndexEntry as $index)
          {
            if($index['entryKey'] ==   $this->department)
            {
              $a=$entery['username'];
              $this->departmentlist[]= new departmentlist
              (
                $a,
                $configs
              );
            }
          }
        }
      }
      catch(Exception $e)
      {
        echo $e->message;
      }
      //  creates an array of active usernames
      $prefix= $wpdb->prefix;
      $digital_measures = $prefix . "digital_measures";
      $activemembers =$wpdb->get_results("SELECT * FROM $digital_measures ;", ARRAY_A);
      //Collects Name and User Name from all Members
      try
      {
        foreach($activemembers as $mem)
        {
          $this->memberlist[]= new memberlist
          (
            $mem['username'],
            $mem['first_name'],
            $mem['middle_name'],
            $mem['last_name']
          );
        }

      }

      //   below if the code for looking into digital measures database and getting the users from that, it does not take being active into account
      // try
      // {
      // foreach($xml->Record as $rec)
      //       $user=$rec['username'];
      //      foreach($rec->PCI as $pci)
      //     {
      //         $this->memberlist[]= new memberlist
      //           (
      //           $user,
      //           $pci->FNAME,
      //           $pci->LNAME,
      //           $pci->MNAME
      //           );
      //       }
      // }
      // }
      catch(Exception $e)
      {
        echo $e->message;
      }
      //find all of the unique departments
      try
      {
        foreach($xml->Record as $entery)
        {
          foreach($entery->IndexEntry as $index)
          {
            if($index['indexKey']=="DEPARTMENT")
            {
              $d = $index['text'];
              $alldeps[]=($d);
            }
          }
        }
      }
      catch(Exception $e)
      {
        echo $e->message;
      }
      // finds only unique departments
      foreach(array_unique($alldeps) as $d)
      {
        $this->departments[]=new departments ($d);
      }

    }

    //lists all the information of the members within the department
    function departmentlist()
    {
      $ret= '<div id="departmentList">';
      foreach($this->departmentlist as $unit)
      {
        $ret .= $unit->display();
      }

      $ret .='</dev>';
      return $ret;
    }
    //creates a list of all active faculty-staff members and creates a page for each
    function directory()
    {
      $ret = '<div id="members">';
      foreach ($this->memberlist as $user)
      {
        $ret .= $user->display();
      }
      $ret .= '<div>';
      return $ret;
    }
    //crerates a list of all departmetns
    function departments()
    {
      $ret= '<div id="departments">';
      foreach($this->departments as $dep)
      {
        $ret .= $dep->display();
      }
      $ret .='</dev>';
      return $ret;
    }
  };

?>
