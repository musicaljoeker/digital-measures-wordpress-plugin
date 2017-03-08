<?php
/**
* Plugin Name: Digital Measures Shortcodes
* Plugin UPI:
* Description: A plugin to provide access to Digital Measures' Activity Insight data like citation information for faculty's publications, presentations and education.
* Version: 1.2
* Author: Jeremy Streich
* Author URI: http://uwm.edu/lsito/
**/

/**
* @file digitalmeasures.php
* This file provides the Wordpress framing for the Digital Measures Activity Insight plugin.
* @Author Jeremy Streich
* @Author  with edits from Taylor Korthals
**/



// Defaults Values for Digitial Measures ShortCode


/**
* Default maximum cache (int in minutes)
**/
DEFINE("DM_CACHELENGTH",1440);

/**
* Default username is blank.  If username is left blank the shortcode will be empty
**/
DEFINE("DM_USERNAME", '');

/**
* Default tpye is 'publications', the only current supported option.
**/
DEFINE("DM_TYPE", 'publications');

/**
* Default published_only is false, show works in with other statuses.
**/
DEFINE("DM_PUBLISHED_ONLY", "no");

/**
* Default profile_only is true, show only the works the faculty mark in Digital Measures.
**/
DEFINE("DM_PROFILE_ONLY", "yes");

/**
* Default format is 'mla'
**/
DEFINE("DM_FORMAT", 'mla');

/**
* Default limit is 0, meaning publication list is unlimited.
**/
DEFINE("DM_LIMIT", 0);

DEFINE("DM_AUTHORS",null);

DEFINE("DM_SHOW_LOCATION",false);
DEFINE("DM_SHOW_DATES",true);

/**
* Deafault instance is 'default'
**/
DEFINE('DM_INSTANCE','default');

// register the shortcode
add_shortcode("digitalmeasures","digitalmeasures_handler");

// Add the CSS to the head of the document
add_action('wp_enqueue_scripts','digitalmeasures_load_style','wp_insert_post');


function digitalmeasures_load_style()
{

  wp_register_style('digitalmeasures-style',plugins_url('/style/digitalmeasures.css', __FILE__));
  wp_enqueue_style('digitalmeasures-style');
}

/**
* Function used to handle shortcode.
* @param array $incomingfrompost the array WordPress passes to shortcodes
*   'username' => username (UWM epanther) for digital measures. If omitted or left blank, the shortcode will show no output.
*   'type' => What type of digital meausres data are using. Currently only publications are supported. (optional, default 'publications')
*   'published_only' => If true, only show items that are listed as published. (Optional, default false)
*   'profile_only' => If true, only show items that have profile set. (Optional, default true)
*   'limit' => If 0 no limit, otherwise this is the max number of items to show. (Optional, default 0)
*   'format' => Citation standard to use, curredntly supported is 'mla' and 'apa'. (Optional, default 'mla').
* @return string The digital measures data.
**/
function digitalmeasures_handler($incomingfrompost)
{

  $incomingfrompost = shortcode_atts
  (
    array
    (
      'department' => DM_DEPARTMENT,
      'username' => DM_USERNAME,
      'type' => DM_TYPE,
      'published_only' => get_option('dm_published_only',DM_PUBLISHED_ONLY),
      'profile_only' => get_option('dm_profile_only',DM_PROFILE_ONLY),
      'limit' => get_option('dm_limit',DM_LIMIT),
      'format' => get_option('dm_format',DM_FORMAT),
      'instance' => DM_INSTANCE,
      'show_dates' => DM_SHOW_DATES,
      'show_location' => DM_SHOW_LOCATION,
      'authors' => get_option('dm_authors',DM_AUTHORS),
      'cache' => get_option('dm_cache_length',DM_CACHELENGTH),
      'unpublished_text' => get_option('dm_unpublished_text','')
    ),
    $incomingfrompost
  );
  return digitalmeasures_function($incomingfrompost);
}

/**
* Function that does the work for displaying the shortcode.
* @param array $args the arguments passed from the handler
* @see digitalmeasures_handler()
* @return string The citations for publications from digital measures.
**/
function digitalmeasures_function($args)
{

  global $dm_configs;
  if(!isset($dm_configs))
  {
    if(defined('WP_CONTENT_DIR'))
    {
      $dm_config_file = WP_CONTENT_DIR . '/digitalmeasures/config.inc.php';
    }

    if(file_exists($dm_config_file))
    {
      require_once($dm_config_file);
    }
    else
    {
      require_once('config.inc.php');
    }
  }

  // if($args['username'] == '')
  // {
  //   return '';
  // }
  if($args['department'] == '')
  {
    return '';
  }
  require_once('alldepartments.inc.php');
  require_once('profile.inc.php');
  $profile = get_transient('dm_' . $args['username']);
  if($profile === false)
  {
    $profile = new profile($args['username'],$dm_configs[$args['instance']]);
    set_transient('dm_' . $args['username'],$profile, $args['cache'] * 60);
    $trans = get_transient('dm_transients');
    if(!$trans)
    {
      $trans = array();
    }
    $trans[] = 'dm_' . $args['username'];
    set_transient('dm_transients',$trans, $args['cache'] * 60);
    //echo 'Not cached.';
  }
  else
  {
    //echo 'cached.';
  }
  // sets the alldepartments class with arguments containing the slected department and the configs for the digital Measures
  $alldepartments = get_transient('dm_' . $args['department']);
  if($alldepartments === false)
  {
    $alldepartments = new alldepartments($args['department'],$dm_configs[$args['instance']]);
    set_transient('dm_' . $args['department'],$alldepartments, $args['cache'] * 60);
    $trans = get_transient('dm_transients');
    if(!$trans)
    {
      $trans = array();
    }
    $trans[] = 'dm_' . $args['department'];
    set_transient('dm_transients',$trans, $args['cache'] * 60);
    //echo 'Not cached.';
  }
  else
  {
    //echo 'cached.';
  }
  if($args['type'] == 'publications') // PUBLICATIONS
  {
    if($args['format'] == 'apa')
    {
      $ret = $profile->apa
      (
        'html',
        ($args['published_only'] === 'true' || $args['published_only'] === true || $args['published_only'] === 'yes'),
        ($args['profile_only'] === 'true' || $args['profile_only'] === true || $args['profile_only'] === 'yes'),
        ($args['limit'] == 0 ? null : $args['limit']), $args['authors'], $args['unpublished_text']
      );
      return $ret;
    }
    else
    {

      $ret = $profile->mla
      (
        'html',
        ($args['published_only'] === 'true' || $args['published_only'] === true || $args['published_only'] === 'yes'),
        ($args['profile_only'] === 'true' || $args['profile_only'] === true || $args['profile_only'] === 'yes'),
        $args['limit'],
        $args['authors'],
        $args['unpublished_text'],
        (stripos($args['format'],'strict') !==  false)
      );

      return $ret;
    }
  }
  else if($args['type'] == 'presentations') // PRESENTATIONS
  {
    if($args['format'] == 'apa')
    {
      $ret = $profile->apa_presentations
      (
        'html',
        ($args['profile_only'] === 'true' || $args['profile_only'] === true),
        $args['limit']
      );
      return $ret;
    }
    else
    {
      $ret = $profile->mla_presentations
      (
        'html',
        ($args['profile_only'] === 'true' || $args['profile_only'] === true),
        $args['limit']
      );
      return $ret;
    }
  }
  else if($args['type'] == 'education') // EDUCATION
  {
    $ret = $profile->education($args['show_location'],$args['show_dates']);
    return $ret;
  }
  else if($args['type'] == 'awards') // EDUCATION
  {
    $ret = $profile->awards($args['limit']);
    return $ret;
  }
  else if($args['type'] == 'current_research') // EDUCATION
  {
    $ret = $profile->current_research($args['limit']);
    return $ret;
  }
  else if($args['type'] == 'grants') // EDUCATION
  {
    $ret = $profile->grants($args['limit']);
    return $ret;
  }
  else if($args['type']=='department')//Department
  {
    $ret = $profile->department();
    return $ret;
  }
  else if($args['type']=='contact') //Contact Information
  {
    $ret = $profile->contact();
    return $ret;
  }
  else if($args['type']=='fullname') //Contact Information
  {
    $ret = $profile->fullname();
    return $ret;
  }
  else if($args['type']=='picture')//Picture
  {
    $ret = $profile->picture();
    return $ret;
  }
  else if($args['type']=='expertise')//Expertise
  {
    $ret = $profile->expertise();
    return $ret;
  }
  else if($args['type']=='profMember')//Proffesional Memberships
  {
    $ret = $profile->profMember($args['limit']);
    return $ret;
  }
  else if($args['type']=='departmentlist')//List of Department Members
  {
    $ret = $alldepartments->departmentlist();
    return $ret;
  }
  else if($args['type']=='directory')//List of Department Members
  {
    $ret = $alldepartments->directory();
    return $ret;
  }
  else if($args['type']=='departments')//List of Department Members
  {
    $ret = $alldepartments->departments();
    return $ret;
  }
  else if($args['type']=='fullname')//List of Department Members
  {
    $ret = $profile->fullname();
    return $ret;
  }
}

function dm_create_menu()
{
  add_submenu_page('options-general.php','Digital Measures Shortcodes','Digital Measures','manage_options','dm_settings','dm_settings_page');
  add_action( 'admin_init', 'register_dm_settings' );
}


function register_dm_settings()
{
  register_setting('dm-settings-group', 'dm_unpublished_text');
  register_setting('dm-settings-group', 'dm_cache_length');
  register_setting('dm-settings-group', 'dm_format');
  register_setting('dm-settings-group', 'dm_limit');
  register_setting('dm-settings-group', 'dm_published_only');
  register_setting('dm-settings-group', 'dm_profile_only');
  register_setting('dm-settings-group', 'dm_authors');
}


function dm_settings_page()
{
  ?>
  <div class="wrap">
    <h2>Digital Measures Shortcode</h2>
    <h3>Settings</h3>
    <form action="options.php" method="post">
      <?php settings_fields( 'dm-settings-group' ); ?>
      <?php do_settings_sections( 'dm-settings-group' ); ?>
      <table class="form-table">
        <tr valign="top">
          <th scope="row">Cache Length</th>
          <td>
            <input type="number" name="dm_cache_length" value="<?php echo esc_attr( get_option('dm_cache_length',DM_CACHELENGTH) ); ?>" />
            <p class="description">The maximum amount of time, in <b>minutes</b>, to keep Digital Measures data cached.</p>
            <button onclick="event.preventDefault();jQuery.post(ajaxurl,{action:'dm_clear_cache'},function(response){alert('Cache cleared.');});">Clear Digital Measures Cache</button>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row">Alternate Text for Unpublished Works</th>
          <td>
            (<input type="text" name="dm_unpublished_text" value="<?php echo esc_attr( get_option('dm_unpublished_text') ); ?>" />)
            <p class="description">The text to replace the year when a work isn't published yet. The text will be rendered inside of parenthsies. Suggested/common values are "Forthcoming" and "In Press".</p>
          </td>
        </tr>
        <tr>
          <th scope="row">Default Citation Format</th>
          <td>
            <input type="radio" name="dm_format" value="mla" <?php echo (get_option('dm_format',DM_FORMAT) == 'mla' ? 'checked="checked"' : ''); ?> />MLA<br/>
            <input type="radio" name="dm_format" value="apa" <?php echo (get_option('dm_format',DM_FORMAT) == 'apa' ? 'checked="checked"' : ''); ?>/>APA<br/>
            <input type="radio" name="dm_format" value="strict-mla" <?php echo (get_option('dm_format',DM_FORMAT) == 'strict-mla' ? 'checked="checked"' : ''); ?>/>Strict MLA
            <p class="description">The default citation standard to use for the site. This setting can be overridden using the <code>format</code> option on a shortcode.</p>
          </td>
        </tr>
        <tr>
          <th scope="row">Default Limit</th>
          <td>
            <input type="number" name="dm_limit" value="<?php echo esc_attr( get_option('dm_limit',DM_LIMIT) ); ?>" />
            <p class="description">The maximum number of items to list. Setting this to "0" will list all matching items. This setting can be overridden using the <code>limit</code> option on a shortcode.</p>
          </td>
        </tr>
        <tr>
          <th scope="row">Default published_only</th>
          <td>
            <input type="radio" name="dm_published_only" value="yes" <?php echo (get_option('dm_published_only',DM_PUBLISHED_ONLY) === true || get_option('dm_published_only',DM_PUBLISHED_ONLY) === 'true' || get_option('dm_published_only',DM_PUBLISHED_ONLY) === 'yes' ? 'checked="checked"' : ''); ?> />Yes<br/>
            <input type="radio" name="dm_published_only" value="no" <?php echo (get_option('dm_published_only',DM_PUBLISHED_ONLY) === false || get_option('dm_published_only',DM_PUBLISHED_ONLY) === 'false' || get_option('dm_published_only',DM_PUBLISHED_ONLY) === 'no'  ? 'checked="checked"' : ''); ?> />No<br/>
            <p class="description">If set to "yes", this will limit publications listed to those with a status of published. This setting can be overridden using the <code>published_only</code> option on a shortcode.</p>
          </td>
        </tr>
        <tr>
          <th scope="row">Default profile_only</th>
          <td>
            <input type="radio" name="dm_profile_only" value="yes" <?php echo (get_option('dm_profile_only',DM_PROFILE_ONLY) === true || get_option('dm_profile_only',DM_PROFILE_ONLY) === 'true' || get_option('dm_profile_only',DM_PROFILE_ONLY) === 'yes'  ? 'checked="checked"' : ''); ?> />Yes<br/>
            <input type="radio" name="dm_profile_only" value="no" <?php echo (get_option('dm_profile_only',DM_PROFILE_ONLY) === false || get_option('dm_profile_only',DM_PROFILE_ONLY) === 'false' || get_option('dm_profile_only',DM_PROFILE_ONLY) === 'no' ? 'checked="checked"' : ''); ?> />No<br/>
            <p class="description">If set to "yes", this will limit publications or presentations listed to those where "show on faculty profile page" in Digital Measures is set to "yes" This setting can be overridden using the <code>profile_only</code> option on a shortcode.</p>
          </td>
        </tr>

        <tr>
          <th scope="row">Default Author Limit</th>
          <td>
            <input type="number" name="dm_authors" value="<?php echo esc_attr( get_option('dm_authors',DM_AUTHORS) ); ?>" />
            <p class="description">If non-zero this will limit the authors in the following way:
              <ul style="list-style-type: square;padding-left:20px;">
                <li>If the number of authors on the publication is less than this value, all authors are shown.</li>
                <li>If the number of authors on the publication is one more than all authors, all authors are shown.</li>
                <li>If the number of authors on the publication is two or more larger than authors, this number of authors will be shown followed by "and others" in MLA or "et. al" in APA.</li>
              </ul>
              This setting can be overridden using the <code>authors</code> option on a shortcode.</p>
            </td>
          </tr>


        </table>
        <script>
        (function($) {
          $(document).ready(function(){
            //clicking button will get variable from the form box
            $('#active-button').click(function(event){
              event.preventDefault();
              // uses ajax to pass activate to ActiveMembers.inc.php
              jQuery.ajax({
                url:path,
                type: 'POST',
                success: function (data) {
                  console.log("got this: " + data);
                  alert(data)
                },
                error: function (){
                  alert('Failed to enter data');
                  console.log("failed");
                }
              });
            });
          });
        })(jQuery);
        </script>

        <?php submit_button(); ?>
        <!-- adds a button to refresh the active members list -->
        <?php submit_button('Re-load Active Faculty', 'primary','active-button' ); ?>
      </form>
      <hr/>
      <h3>How to use the ShortCode</h3>
      <h4 id="shortcode-for-Directory">Shortcode for Directory<a href="#shortcode-for-directory"></a></h4>
      <p><code>[digitalmeasures type="directory"]</code></p>
      <ul style="list-style-type: square;padding-left:20px;">
        <li><em>type</em> should be "directory" to create a directory. This will also automatically create a page for each faculty member if one is not there already</li>
      </ul>
    </li>
  </ul>

  <h4 id="shortcode-for-departments">Shortcode for Departments<a href="#shortcode-for-departments"></a></h4>
  <p><code>[digitalmeasures type="departments"]</code></p>
  <ul style="list-style-type: square;padding-left:20px;">
    <li><em>type</em> should be "departments" to a list of all departments.This will autocreate a page for each department with infomration on the factuly members</li>
  </ul>
</li>
</ul>

<h4 id="shortcode-for-publications">Shortcode for Publications<a href="#shortcode-for-publications"></a></h4>
<p><code>[digitalmeasures username="epanther" published_only="true" profile_only="false" limit="5" instance="default" format="apa"]</code></p>
<p><code>[digitalmeasures username="epanther"]</code></p>
<ul style="list-style-type: square;padding-left:20px;">
  <li><em>username</em> should be the username in Digital Measures for the faculty member.</li>
  <li><em>type</em> is optional. The default is "publication".</li>
  <li><em>published_only</em> is optional. The default is "no", and will limit publications listed to those with a status of published.</li>
  <li><em>profile_only</em> is optional. The default is "yes", and will limit publications listed to those where "show on faculty profile page" in Digital Measures is set to "yes".</li>
  <li><em>limit</em> is optional. The default is "0" which lists all matching publications, otherwise this is the maximum number of publications to list.</li>
  <li><em>instance</em> is optional. This will choose which instance of Digital Meausres to pull from. These are setup in the config.inc.php file.</li>
  <li><em>format</em> is optional. This decides which citation standard to use. Options are "mla" for MLA style or "apa" for APA style. The default style is MLA.</li>
  <li><em>authors</em> is optional. If present this will limit the authors in the following way:
    <ul style="list-style-type: square;padding-left:20px;">
      <li>If the number of authors on the publication is less than this value, all authors are shown.</li>
      <li>If the number of authors on the publication is one more than all authors, all authors are shown.</li>
      <li>If the number of authors on the publication is two or more larger than authors, this number of authors will be shown followed by "and others" in MLA or "et. al" in APA.</li>
    </ul>
  </li>
  <li><em>cache</em> is optional. If supplied, it will override the "Cache Length" value entered in on the Digital Measures Settings page for the data retrieved by this instance of the shortcode.</li>
  <li><em>unpublished_text</em> is optional. If supplied, it will override the "Alternate Text for Unpublished Works" value entered in on the Digital Measures Settings page for the data retrieved by this instance of the shortcode.</li>
</ul>

<h4 id="shortcode-for-education">Shortcode for Education<a href="#shortcode-for-education"></a></h4>
<p><code>[digitalmeasures type="education" username="epanther" show_location="true" show_dates="range"]</code></p>
<p><code>[digitalmeasures type="education" username="epanther" show_dates="true"]</code></p>
<ul style="list-style-type: square;padding-left:20px;">
  <li><em>username</em> should be the username in Digital Measures for the faculty member.</li>
  <li><em>type</em> should be "education" to show the education of a faculty member.</li>
  <li><em>show_location</em> is optional. This controls showing or not showing the locations (city and state) of the university the degree was earned at. The default is "false".</li>
  <li><em>show_dates</em> is optional. This controls if and how the dates of the degree are shown. The default is "false".
    <ul style="list-style-type: square;padding-left:20px;">
      <li><em>true</em> shows the completion date only.</li>
      <li><em>range</em> shows the start and end dates for the degrees.</li>
      <li><em>false</em> doesn't show the dates at all.</li>
    </ul>
  </li>
</ul>

<h4 id="shortcode-for-presentations">Shortcode for Presentations<a href="#shortcode-for-presentations"></a></h4>
<p><code>[digitalmeasures type="presentations" username="epanther" profile_only="false" limit="5" instance="default" format="apa"]</code></p>
<p><code>[digitalmeasures type="presentations" username="epanther"]</code></p>
<ul style="list-style-type: square;padding-left:20px;">
  <li><em>username</em> should be the username in Digital Measures for the faculty member.</li>
  <li><em>type</em> should be "presentations" to show presentations.</li>
  <li><em>profile_only</em> is optional. The default is "yes", and will limit publications listed to those where "show on faculty profile page" in Digital Measures is set to "yes".</li>
  <li><em>limit</em> is optional. The default is "0" which lists all matching publications, otherwise this is the maximum number of publications to list.</li>
  <li><em>instance</em> is optional. This will choose which instance of Digital Measures to pull from. These are setup in the config.inc.php file.</li>
  <li><em>format</em> is optional. This decides which citation standard to use. Options are "mla" for MLA style or "apa" for APA style, default is MLA.</li>
  <li><em>authors</em> is optional. If present this will limit the authors in the following way:
    <ul style="list-style-type: square;padding-left:20px;"><li>If the number of authors on the publication is less than this value, all authors are shown.</li>
      <li>If the number of authors on the publication is one more than all authors, all authors are shown.</li>
      <li>If the number of authors on the publication is two or more larger than authors, this number of authors will be shown followed by "and others" in MLA or "et. al" in APA.</li>
    </ul>
  </li>
</ul>

<h5 id="shortcode-for-picture">Shortcode for Picture<a href="#shortcode-for-picture"></a></h4>
  <p><code>[digitalmeasures type="picture" username="epanther"]</code></p>
  <ul style="list-style-type: square;padding-left:20px;">
    <li><em>type</em> should be "picture" to show stored picture.</li>
    <li><em>username</em> should be the username in Digital Measures for the faculty member.</li>
  </ul>
</li>
</ul>

<h5 id="shortcode-for-contact">Shortcode for Contact<a href="#shortcode-for-contact"></a></h4>
  <p><code>[digitalmeasures type="contact" username="epanther"]</code></p>
  <ul style="list-style-type: square;padding-left:20px;">
    <li><em>type</em> should be "contact" to show contact information.</li>
    <li><em>username</em> should be the username in Digital Measures for the faculty member.</li>
  </ul>
</li>
</ul>

<h5 id="shortcode-for-department">Shortcode for Department<a href="#shortcode-for-department"></a></h4>
  <p><code>[digitalmeasures type="department" username="epanther"]</code></p>
  <ul style="list-style-type: square;padding-left:20px;">
    <li><em>type</em> should be "department" to show department.</li>
    <li><em>username</em> should be the username in Digital Measures for the faculty member.</li>
  </ul>
</li>
</ul>

<h5 id="shortcode-for-expertises">Shortcode for Expertises<a href="#shortcode-for-expertises"></a></h4>
  <p><code>[digitalmeasures type="expertise" username="epanther"]</code></p>
  <ul style="list-style-type: square;padding-left:20px;">
    <li><em>type</em> should be "expertise" to show expertises.</li>
    <li><em>username</em> should be the username in Digital Measures for the faculty member.</li>
  </ul>
</li>
</ul>

<h5 id="shortcode-for-profmember">Shortcode for Proffesional Memberships<a href="#shortcode-for-profmember"></a></h4>
  <p><code>[digitalmeasures type="profMember" username="epanther"]</code></p>
  <ul style="list-style-type: square;padding-left:20px;">
    <li><em>type</em> should be "profMember" to show Proffesional Memberships.</li>
    <li><em>username</em> should be the username in Digital Measures for the faculty member.</li>
  </ul>
</li>
</ul>

<h5 id="shortcode-for-departmentlist">Shortcode for Department List<a href="#shortcode-for-departmentlist"></a></h4>
  <p><code>[digitalmeasures type="departmentlist" department="epanther"]</code></p>
  <ul style="list-style-type: square;padding-left:20px;">
    <li><em>type</em> should be "departmentlist" to show Department List.</li>
    <li><em>department</em> should be the Department wanted.</li>
  </ul>
</li>
</ul>
</div>
<?php
}


add_action('admin_menu', 'dm_create_menu');

function dm_clear_cache()
{
  $trans = get_transient('dm_transients');
  if(!$trans)
  {
    $trans = array();
  }
  $i = 0;
  foreach($trans as $t)
  {
    ++$i;
    delete_transient($t);
  }
  echo $i;
  $trans = array();
  set_transient('dm_transients',$trans,DM_CACHELENGTH * 60);
}

add_action('wp_ajax_dm_clear_cache','dm_clear_cache');




?>
