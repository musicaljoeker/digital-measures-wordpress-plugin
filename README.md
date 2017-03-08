# Digital Measures Wordpress plugin

This plugin allows higher educational institutions who use Digital Measures' Activity Insight to pull data using the web API (Web Services).
Then use that to populate faculty publication citations, educational acheivements (degrees), and presentation citations, in MLA and APA citation standards using shortcodes.

## License
General Public License version 2.

## Setup

Install files to the `/wp-content/plugins/digitalmeasures/` directory.


Add the following to your `wp-config.php`, or copy or move config.inc.php to `wp-content/digitalmeasures/config.inc.php`.
Then edit that file to reflect your Digital Measures' Web Services Account credentials.

<code><pre>
   $dm_configs = array
  (
    'default' => array
    (
      'username' => '<b>digital measures webservices account username goes here</b>',
      'password' => '<b>password goes here</b>',
      'key' => '<b>University key</b>'
    )
  );
</pre></code>

Enable the plugin in the plugin page of the admin section.


## Shortcode for Directory

<code>[digitalmeasures type="directory"]</code>

 - *type* should be "directory" to create a directory.

## Shortcode for Departments
<code>[digitalmeasures type="departments"]</code>

 - *type* should be "departments" to create a list of departments.

## Shortcode for Publications

<code>[digitalmeasures username="epanther" published_only="true" profile_only="false" limit="5" instance="default" format="apa"]</code>

 - *username* should be the username in Digital Measures for the faculty member.
 - *type* is optional. The default is "publication".
 - *published_only* is optional. The default is "no", and will limit publications listed to those with a status of published.
 - *profile_only* is optional. The default is "yes", and will limit publications listed to those where "show on faculty profile page" in Digital Measures is set to "yes".
 - *limit* is optional. The default is "0" which lists all matching publications, otherwise this is the maximum number of publications to list.
 - *instance* is optional. This will choose which instance of Digital Meausres to pull from. These are setup in the config.inc.php file.
 - *format* is optional. This decides which citation standard to use. Options are "mla" for MLA style or "apa" for APA style. The default style is MLA.
 - *authors* is optional. If present this will limit the authors in the following way:
   - If the number of authors on the publication is less than this value, all authors are shown.
   - If the number of authors on the publication is one more than all authors, all authors are shown.
   - If the number of authors on the publication is two or more larger than authors, this number of authors will be shown followed by "and others" in MLA or "et. al" in APA.
 - *cache* is optional. If supplied, it will override the "Cache Length" value entered in on the Digital Measures Settings page for the data retrieved by this instance of the shortcode.
 - *unpublished_text* is optional. If supplied, it will override the "Alternate Text for Unpublished Works" value entered in on the Digital Measures Settings page for the data retrieved by this instance of the shortcode.

## Shortcode for Education


<code>[digitalmeasures type="education" username="epanther" show_location="true" show_dates="range"]</code>

<code>[digitalmeasures type="education" username="epanther" show_dates="true"]</code>

 - *username* should be the username in Digital Measures for the faculty member.
 - *type* should be "education" to show the education of a faculty member.
 - *show_location* is optional. This controls showing or not showing the locations (city and state) of the university the degree was earned at. The default is "false".
 - *show_dates* is optional. This controls if and how the dates of the degree are shown. The default is "false".
   - *true* shows the completion date only.
   - *range* shows the start and end dates for the degrees.
   - *false* doesn't show the dates at all.

## Shortcode for Presentations

<code>[digitalmeasures type="presentations" username="epanther" profile_only="false" limit="5" instance="default" format="apa"]</code>

 - *username* should be the username in Digital Measures for the faculty member.
 - *type* should be "presentations" to show presentations.
 - *profile_only* is optional. The default is "yes", and will limit presentations listed to those where "show on faculty profile page" in Digital Measures is set to "yes".
 - *limit* is optional. The default is "0" which lists all matching presentations, otherwise this is the maximum number of presentations to list.
 - *instance* is optional. This will choose which instance of Digital Measures to pull from. These are setup in the config.inc.php file.
 - *format* is optional. This decides which citation standard to use. Options are "mla" for MLA style or "apa" for APA style, default is MLA.
 - *authors* is optional. If present this will limit the authors in the following way:
   - If the number of authors on the presentation is less than this value, all authors are shown.
   - If the number of authors on the presentation is one more than all authors, all authors are shown.
   - If the number of authors on the presentation is two or more larger than authors, this number of authors will be shown followed by "and others" in MLA or "et. al" in APA.

## Shortcode for Awards and Honors

<code>[digitalmeasures type="awards" username="epanther"]</code>

 - *username* should be the username in Digital Measures for the faculty member.
 - *type* should be "awards" to show awards.
 - *limit* is optional. The default is "0" which lists all awards, otherwise this is the maximum number of awards to list.

## Shortcode for Current Research

<code>[digitalmeasures type="current_research" username="epanther"]</code>

 - *username* should be the username in Digital Measures for the faculty member.
 - *type* should be "current_research" to show current research.
 - *limit* is optional. The default is "0" which lists all research, otherwise this is the maximum number of items to list.

## Shortcode for Contracts and Grants

<code>[digitalmeasures type="grants" username="epanther"]</code>

 - *username* should be the username in Digital Measures for the faculty member.
 - *type* should be "grants" to show grants.
 - *limit* is optional. The default is "0" which lists all grants, otherwise this is the maximum number of grants to list.

##  Shortcode for Picture

 <code>[digitalmeasures type="picture" username="epanther"]</code>

  - *username* should be the username in Digital Measures for the faculty member.
  - *type* should be "picture" to show picture.

##  Shortcode for Contact

   <code>[digitalmeasures type="contact" username="epanther"]</code>

    - *username* should be the username in Digital Measures for the faculty member.
    - *type* should be "contact" to show contact information.

##  Shortcode for Department

   <code>[digitalmeasures type="department" username="epanther"]</code>

      - *username* should be the username in Digital Measures for the faculty member.
      - *type* should be "department" to show what department the member is in.

##  Shortcode for Expertise

   <code>[digitalmeasures type="expertise" username="epanther"]</code>
      - *username* should be the username in Digital Measures for the faculty member.
      - *type* should be "expertise" to show expertises.

##  Shortcode for Proffesional Membership

     <code>[digitalmeasures type="profMember" username="epanther"]</code>
      - *username* should be the username in Digital Measures for the faculty member.
      - *type* should be "profMember" to show Proffesional Memberships of the member.

##  Shortcode for Department List

     <code>[digitalmeasures type="departmentlist" department="epanther"]</code>
      - *username* should be the department name in Digital Measures for Department.
      - *type* should be "departmentlist" to show the members in the department.        
