<?php
/**
* @file profile.inc.php
* The meat of the applications member level happens within this file.
* @author Jeremy Streich
* @author with edits from Taylor Korthals
**/


function dm_startsWith($haystack, $needle)
{
  return $needle === "" || strpos($haystack, $needle) === 0;
}

function dm_endsWith($haystack, $needle)
{
  return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
}

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

  require_once('contributor.inc.php');
  require_once('publication.inc.php');
  require_once('presentation.inc.php');
  require_once('degree.inc.php');
  require_once('award.inc.php');
  require_once('research.inc.php');
  require_once('grant.inc.php');
  require_once('department.inc.php');
  require_once('contact.inc.php');
  require_once('picture.inc.php');
  require_once('expertise.inc.php');
  require_once('profmember.inc.php');


  /**
  * @class profile
  * This object represents a faculty's profile, as far as their intelectual contributions. Fetches information from Digital Measures.
  **/
  class profile
  {
    private $username;
    private $password;
    private $epanther;
    private $school;
    private $college;
    private $department;
    private $first_name;
    private $middle_name;
    private $last_name;
    private $email;
    private $phone;
    private $phone_ex;
    private $room;
    private $website;
    private $network;
    private $research;
    private $pubs;
    private $education;
    private $awards;
    private $current_research;
    private $grants;
    private $activemember;
    private $picture;
    private $pictureIS;


    /**
    * Constructor for the publications class which popluates the profile by fetching information from Digital Measures
    * @param string $u the ePatherID of the user.
    * @param array $config Activity Insight Configuration
    **/
    function __construct($u,$config = null)
    {
      $this->username = $config['username'];
      $this->password = $config['password'];
      $this->epanther = $u;

      $ret = '';
      //$config['key']
      $ch = curl_init('https://www.digitalmeasures.com/login/service/v4/UserSchema/USERNAME:' . $u . '/INDIVIDUAL-ACTIVITIES-'.$config['key']);
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

      $this->contact = array();
      $this->pubs = array();
      $this->presentations = array();
      $this->education = array();
      $this->awards = array();
      $this->grants = array();
      $this->department = array();
      $this->expertise = array();
      $this->profMember = array();
      $this->activeyear = array();

      // see if an active profile this year
      $activeyear=substr($xml->ADMIN->AC_YEAR,-4);
      if($activeyear== date("Y"))
      {
        $this->activemember = true;
      }
      else
      {
        $this->activemember = false;
      }

      //Get School and Department
      foreach($xml->IndexEntry as $unit)
      {
        $attrs = $unit->attributes();
        if($attrs['indexKey'] == 'COLLEGE')
        {
          $unit->college = (string)$attrs['entryKey'];
        }
        else if($attrs['indexKey'] == 'DEPARTMENT')
        {
          $unit->department = (string)$attrs['entryKey'];
        }
      }
      //department
      try
      {
        foreach($xml->IndexEntry as $unit)
        $attrs = $unit->attributes();
        $this->department[]= new department
        (
          $unit->indexKey=(string)$attrs['indexKey'],
          $unit->entryKey=(string)$attrs['enteryKey'],
          $unit->text=(string)$attrs['text']
        );
      }
      catch(Exception $e)
      {
        echo $e->message;
      }

      //Get Contact information
      $this->first_name = (string)$xml->PCI->FNAME;
      $this->last_name = (string)$xml->PCI->LNAME;
      $this->middle_name = (string)$xml->PCI->MNAME;
      $this->email = (string)$xml->PCI->EMAIL;
      $this->phone = '(' . $xml->PCI->OPHONE1 . ') ' . $xml->PCI->OPHONE1 . '-' . $xml->PCI->OPHONE3;
      $this->phone_ex = (string)$xml->PCI->OPHONE3;
      $this->room = (string)$xml->PCI->ROOMNUM;
      $this->website = (string)$xml->PCI->WEBSITE;
      $this->network = ($xml->RESEARCH_INTEREST->SHARE == 'Yes');
      $this->research = (string)$xml->RESEARCH_INTEREST->INTEREST;

      //picture
      try
      {
        //Tests to see if a picture exists
        if(!empty($xml->PCI->UPLOAD_PHOTO))
        {
          $this->pictureIS= true;
        };
        $this->picture=new picture($xml->PCI->UPLOAD_PHOTO);
      }
      catch(Exception $e)
      {
        echo $e->message;
      }

      //Contact Info
      try
      {
        $cont=$xml->PCI;
        $admin=$xml->ADMIN;
        $exp = array();
        foreach($cont->PCI_EXPERTISE as $ex)
        {
          $exp[] = $ex->EXPERTISE;
        }
        $this->contact[]= new contact
        (
          $cont->PREFIX,
          $cont->FNAME,
          $cont->LNAME,
          $cont->MNAME,
          $cont->BUILDING,
          $cont->OPHONE,
          $cont->EMAIL,
          $exp
        );
      }
      catch(Exception $e)
      {
        echo $e->message;
      }
      //memberships
      try
      {
        foreach($xml->GENSERVE as $serv)
        {
          $attrs = $serv->attributes();
          $s[]= array();
          foreach ($serv->DTM_START as $dts)
          {
            $s[] = $dts;
          }
          $e[]= array();
          foreach ($serv->DTM_END as $dte)
          {
            $e[] = $dte;
          }
          $this->profMember[]= new profMember
          (
            $serv->TYPE,
            $serv->TITLE,
            $serv->ORG,
            $serv->ROLE,
            $serv->RESPONSIBILITIES,
            $s,
            $e
          );
        }
      }
      catch(Exception $e)
      {
        echo $e->message;
      }
      //Expertise
      try
      {
        foreach($xml->PCI as $pci)
        {
          $exp = array();
          foreach($pci->PCI_EXPERTISE as $ex)
          {
            $exp[] = $ex->EXPERTISE;
          }
          $this->expertise[] = new expertise($exp);
        }
      }
      catch(Exception $e)
      {
        echo $e->message;
      }
      // Publications
      try
      {
        foreach($xml->INTELLCONT as $pub)
        {
          $attrs = $pub->attributes();
          $a = array();
          foreach($pub->INTELLCONT_AUTH as $author)
          {
            $a[] = new contributor($author->FNAME,$author->MNAME,$author->LNAME);
          }
          $e = array();
          foreach($pub->INTELLCONT_EDITOR as $editor)
          {
            $e[] = new contributor($editor->FNAME,$editor->MNAME,$editor->LNAME);
          }
          if(count($e) == 0 && isset($pub->EDITORS) && '' != $pub->EDITORS && null != $pub->EDITORS)
          {
            $e[0] = new contributor($pub->EDITORS);
          }

          $is_article = (strpos($pub->CONTYPE,'Journal Article') === 0) || (strpos($pub->CONTYPE,'Online Article') === 0);
          // // // //
          $this->pubs[] = new publication
          (
            $attrs['id'],
            $pub->CONTYPE,
            $pub->STATUS,
            $pub->TITLE,
            ($is_article ? $pub->PUBLISHER : $pub->TITLE_SECONDARY),
            (isset($pub->INCLUDE_PROFILE) ? $pub->INCLUDE_PROFILE : false), //$pub->INCLUDE_PROFILE,
            $a,
            $e,
            ($is_article ? '' : $pub->PUBLISHER),
            $pub->PUBCTYST,
            $pub->VOLUME,
            $pub->ISSUE,
            $pub->PAGENUM,
            $pub->DOI,
            $pub->ISBNISSN,
            $pub->DTD_PUB,
            $pub->DTM_PUB,
            $pub->DTY_PUB,
            $pub->WEB_ADDRESS
          );
        }
      }
      catch(Exception $e)
      {
        echo $e->message;
      }

      // Presentations

      try
      {
        foreach($xml->PRESENT as $pub)
        {
          $attrs = $pub->attributes();
          $a = array();
          foreach($pub->PRESENT_AUTH as $author)
          {
            $a[] = new contributor($author->FNAME,$author->MNAME,$author->LNAME);
          }

          $this->presentations[] = new presentation
          (
            $pub->NAME,
            $a,
            $pub->TITLE,
            $pub->LOCATION,
            $pub->DTM_DATE,
            $pub->DTY_DATE,
            (isset($pub->INCLUDE_PROFILE) ? $pub->INCLUDE_PROFILE : false)
          );
        }
      }
      catch(Exception $e)
      {
        echo $e->message;
      }

      // EDUCATION

      try
      {
        foreach($xml->EDUCATION as $ed)
        {
          $attrs = $ed->attributes();
          $this->education[] = new degree
          (
            ($ed->DEG != 'Other' ? $ed->DEG : $ed->DEGOTHER),
            $ed->MAJOR,
            $ed->SCHOOL,
            $ed->LOCATION,
            $attrs['startDate'],
            $attrs['endDate']
          );
        }
      }
      catch(Exception $e)
      {
        echo $e->message;
      }
      //Awards
      try
      {
        foreach($xml->AWARDHONOR as $aw)
        {
          $this->awards[] = new award
          (
            $aw->NAME,
            $aw->ORG,
            $aw->DTY_DATE,
            $aw->DTM_DATE,
            $aw->DTD_DATE
          );
        }
      }
      catch(Exception $e)
      {
        echo $e->message;
      }

      //research
      try
      {
        foreach($xml->RESPROG as $re)
        {
          $cols = array();
          foreach($re->RESPROG_COLL as $col)
          {
            $cols[] = (string)$col->NAME;
          }
          $this->current_research[] = new research
          (
            $re->TITLE,
            $re->DESC,
            $cols
          );
        }
      }
      catch(Exception $e)
      {
        echo $e->message;
      }
      //grant
      try
      {
        foreach($xml->CONGRANT as $gr)
        {
          $invest = array();
          foreach($gr->CONGRANT_INVEST as $iv)
          {
            $invest[] = $iv->FNAME . ' ' . $iv->LNAME;
          }
          $this->grants[] = new grant
          (
            $gr->TITLE,
            $gr->SPONORG,
            $gr->TYPE,
            $invest
          );
        }
      }
      catch(Exception $e)
      {
        echo $e->message;
      }


      if(is_array($this->pubs))
      {
        usort($this->pubs,"pub_cmp");
      }
      if(is_array($this->education))
      {
        usort($this->education,"ed_cmp");
        $this->education = array_reverse($this->education);
      }


    }
    /**
    * Show this faculty department as HTML.
    **/
    public function department()
    {
      $ret= '<div id="department">';
      foreach($this->department as $unit)
      {
        $ret .= $unit->display();
      }
      $ret .='</dev>';
      return $ret;
    }
    /**
    * Show this faculty education as HTML.
    * @param bool $show_location weather or not to show the location of the shcool.
    * @param string $show_dates how to show the dates.
    * @return HTML of the education as as a string.
    **/
    public function education($show_location,$show_dates)
    {
      $ret = '<div id="education">';
      foreach($this->education as $ed)
      {
        $ret .= $ed->display($show_location,$show_dates);
      }
      $ret .='</div>';
      return $ret;
    }
    /**
    * Show this faculty expertise as HTML.
    * @return HTML of the expertise as as a string.
    **/
    public function expertise()
    {
      $ret= '<div id="expertise">';
      foreach($this->expertise as $exp)
      {
        $ret .= $exp->display();
      }
      $ret .='</dev>';
      return $ret;
    }
    /**
    * Show this faculty contact as HTML.
    * @return HTML of the contact as as a string.
    **/
    public function contact()
    {
      // $ret= '<div id="contact">';
      foreach($this->contact as $cont)
      {
        $ret .= $cont->display();
      }
      // $ret .='</dev>';
      return $ret;
    }
    //Gets full Name from member
    public function fullname()
    {
      $ret = '<div id ="fullname">';
      $ret .= $this->first_name." ".$this->middle_name." ".$this->last_name;
      $ret .= '</div>';
      return $ret;
    }

    public function linkname()
    {                                                                     //should be changed to match whereever the directory is
      $ret= '<div id= "nameList"><a href="'.$_SERVER['SERVER_NAME'].":8888/directory/".$this->epanther.'">'. $this->first_name." ".$this->middle_name." ".$this->last_name. '</a></div>';

      return $ret;
    }

    /**
    * Show this faculty Proffesional Memberships as HTML.
    * @return HTML of the expertise as as a string.
    **/
    public function profMember($limit = 0)
    {
      $ret= '<div id="profMember">';
      $i = 0;
      foreach($this->profMember as $mem)
      {
        $ret .= $mem->display();
        $i++;
        if($limit && $i >= $limit)
        {
          break;
        }
      }
      $ret .='</dev>';
      return $ret;
    }
    /**
    * Show this faculty Picture as HTML.
    * @return HTML of the Picture Location as as a string.
    **/
    public function picture()
    {
      if($this->pictureIS)
      {

        $ret.= $this->picture->display();

        return $ret;
      }
    }
    /**
    * Show this faculty is active or not.
    * @return true if member is active in the current year.
    **/
    public function active()
    {
      if($this->activemember)
      {
        return true;
      }
    }
    /**
    * Show this faculty member's awards as HTML.
    * @return HTML of the awards as as a string.
    **/
    public function awards($limit = 0)
    {
      $ret = '<div id="awards">';
      $i = 0;
      foreach($this->awards as $aw)
      {
        $ret .= $aw->display();
        $i++;
        if($limit && $i >= $limit)
        {
          break;
        }
      }
      $ret .='</div>';
      return $ret;
    }

    /**
    * Show this faculty member's research as HTML.
    * @return HTML of the research as as a string.
    **/
    public function current_research($limit = 0)
    {
      $ret = '<div id="current-research">';
      $i = 0;
      foreach($this->current_research as $re)
      {
        $ret .= $re->display();
        $i++;
        if($limit && $i >= $limit)
        {
          break;
        }
      }
      $ret .='</div>';
      return $ret;
    }


    /**
    * Show this faculty member's research as HTML.
    * @return HTML of the research as as a string.
    **/
    public function grants($limit = 0)
    {
      $ret = '<div id="grants">';
      $i = 0;
      foreach($this->grants as $gr)
      {
        $ret .= $gr->display();
        $i++;
        if($limit && $i >= $limit)
        {
          break;
        }
      }
      $ret .='</div>';
      return $ret;
    }


    /**
    * Output this profile as list of mla citations of publications.
    * @parma string $type 'html' or 'text' supported. This is what the output method will eventually be.
    * @param bool $only_published if true then only published works will be output
    * @param bool $only_profile if true then olny the publications marked to be shown on a profile page will be output.
    * @param int $limit the maximum number of publications to display. limit of 0 means show all matching the previous arguments.
    * @param string $unpublished The text to be shown in place of date for unpublished works.
    * @param bool $strict If the mla is stict format or not.
    * @return string of mla formated citations for output
    **/
    public function mla($type = 'html', $only_published = false,$only_profile = false,$limit = 0,$authors = null,$unpublished = '',$strict = false)
    {
      $ret = '';
      if('array' == $type)
      {
        $ret = array();
      }
      $i = 1;
      foreach($this->pubs as $p)
      {
        if( ( !$only_published || ($only_published && $p->is_published())) &&
        ( !$only_profile   || ($only_profile   && $p->in_profile()  ))
        )
        {
          $i++;
          if('html' == $type)
          {
            $ret .= '<div class="publication">';
          }
          if('array' == $type)
          {
            $ret[] = $p->mla($type,$authors,$unpublished,$strict);
          }
          else
          {
            $ret .= $p->mla($type,$authors,$unpublished,$strict);
          }
          if('html' == $type)
          {
            $ret .= '</div>';
          }
        }
        if($limit != 0 && $i > $limit)
        {
          break;
        }
      }
      return $ret;
    }


    /**
    * Output this profile as list of mla citations of presentations.
    * @parma string $type 'html' or 'text' supported. This is what the output method will eventually be.
    * @param bool $only_profile if true then olny the presentations marked to be shown on a profile page will be output.
    * @param int $limit the maximum number of presentations to display. limit of 0 means show all matching the previous arguments.
    * @return string of mla formated citations for output
    **/
    public function mla_presentations($type = 'html',$only_profile = false,$limit = 0)
    {
      $ret = '';
      if('array' == $type)
      {
        $ret = array();
      }
      $i = 1;
      foreach($this->presentations as $p)
      {

        if( !$only_profile   || ($only_profile   && $p->in_profile()) )
        {
          $i++;
          if('html' == $type)
          {
            $ret .= '<div class="presentation">';
          }
          if('array' == $type)
          {
            $ret[] = $p->mla($type);
          }
          else
          {
            $ret .= $p->mla($type);
          }
          if('html' == $type)
          {
            $ret .= '</div>';
          }
        }
        if($limit != 0 && $i > $limit)
        {
          break;
        }
      }

      return $ret;
    }



    /**
    * Output this profile's publications as list of APA citations of publications.
    * @parma string $type 'html' or 'text' supported. This is what the output method will eventually be.
    * @param bool $only_published if true then only published works will be output
    * @param bool $only_profile if true then olny the publications marked to be shown on a profile page will be output.
    * @param int $limit the maximum number of publications to display. limit of 0 means show all matching the previous arguments.
    * @param int $authors the number of authors to display.
    * @param string $unpublished The text to shown in place of date for unpublished works.
    * @return string of mla formated citations for output
    **/
    function apa($type = 'html', $only_published = false,$only_profile = false,$limit = 0,$authors = null,$unpublished = '')
    {
      $ret = '';
      if('array' == $type)
      {
        $ret = array();
      }
      $i = 1;
      foreach($this->pubs as $p)
      {



        if( ( !$only_published || ($only_published && $p->is_published())) &&
        ( !$only_profile   || ($only_profile   && $p->in_profile()  ))
        )
        {
          $i++;
          if('html' == $type)
          {
            $ret .= '<div class="publication">';
          }
          if('array' == $type)
          {
            $ret[] = $p->apa($type,$authors,$unpublished);
          }
          else
          {
            $ret .= $p->apa($type,$authors,$unpublished);
          }
          if('html' == $type)
          {
            $ret .= '</div>';
          }
        }
        if($limit != 0 && $i > $limit)
        {
          break;
        }

      }
      return $ret;
    }

    /**
    * Output this profile as list of mla citations of presentations.
    * @parma string $type 'html' or 'text' supported. This is what the output method will eventually be.
    * @param bool $only_profile if true then olny the presentations marked to be shown on a profile page will be output.
    * @param int $limit the maximum number of presentations to display. limit of 0 means show all matching the previous arguments.
    * @return string of mla formated citations for output
    **/
    public function apa_presentations($type = 'html',$only_profile = false,$limit = 0)
    {
      $ret = '';
      if('array' == $type)
      {
        $ret = array();
      }
      $i = 1;
      foreach($this->presentations as $p)
      {



        if( !$only_profile   || ($only_profile   && $p->in_profile()) )
        {
          $i++;
          if('html' == $type)
          {
            $ret .= '<div class="presentation">';
          }
          if('array' == $type)
          {
            $ret[] = $p->apa($type);
          }
          else
          {
            $ret .= $p->apa($type);
          }
          if('html' == $type)
          {
            $ret .= '</div>';
          }
        }
        if($limit != 0 && $i > $limit)
        {
          break;
        }
      }
      return $ret;
    }

  };

  ?>
