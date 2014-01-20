<?php

/*

Creates a course catalogue with tabs across the top and courses down the page.
The format of the course list is set by template

Requires: /local/course_meta/
		  /local/customscripts/
		  $CFG->customscripts = __DIR__.'/local/customscripts'; to be set in config.php
		  A template to be set (Site administration > Plugins > Filters > Course catalogue)

Set-up
	Create a course custom field category called 'Catalogue'
	Create a field with shortname of Tab, which is of type menukeys
	Set the values for each tab, and display names
	Create a checkbox field called 'listed' - tick this to include courses in the catalogue
	Create your custom fields such as icons or descriptions
	Create the required view in the database bylooking at the filter settings page
	Set a template for courses (on filter settings page)
	Check your common filter settings cache timeout setting - useful to set to 0 for debugging.

Usage:
- Enable the filter for the frontpage.
- In an editable region such as the default topic, place the tag [course-catalogue] where the catalogue will appear.

*/
require_once ($CFG->dirroot.'/filter/coursecatalogue/lib.php');

class filter_coursecatalogue extends moodle_text_filter {
    public function filter($text, array $options = array()) {
		global $DB, $CFG, $PAGE;
	    $find = '/\\[course-catalogue\\]/';

        if (!isset($CFG->filter_coursecatalogue_template)) {
			return preg_replace($find, '', $text);
        }
	    if (preg_match($find,$text)) {

   	        if ($DB->count_records_sql('select count(*) from {vw_course_metadata} where listed = 1') == 0) {
				return preg_replace($find, '', $text);
   	        }

			// include tab clicker code
			$module = array(
				'name'     => 'filter_coursecatalogue',
				'fullpath' => '/filter/coursecatalogue/filter.js',
				'requires' => array()
			);
			$PAGE->requires->js_init_call('M.filter_coursecatalogue.init', array(), false, $module);

	    	$categoryid = filter_coursecatalogue_course_meta_categoryid('Catalogue');
			$tabs = filter_coursecatalogue_course_meta_info_row('tab', $categoryid);
			$keys = preg_split('/\r\n|\r|\n/',$tabs->param1); // value
			$vals = preg_split('/\r\n|\r|\n/',$tabs->param2); // text

			$out = Array();
			$out[] = html_writer::start_tag('div', Array('id'=>'tab-catalogue')) . "\n";
		    $out[] = html_writer::start_tag('ul', Array('id'=>'tab-links')) . "\n";

			$i=0;
		    foreach ($keys as $key) {
		    	$out[] = html_writer::tag('li',
		    					html_writer::tag('a', $vals[$i], Array('href'=>'#tab_'.$key)),
		    					Array('class'=> ($i==0?'active':''))
		    			);
		    	$i++;
		    }
			$out[] = html_writer::end_tag('ul') . html_writer::start_tag('div', Array('id'=>'tab-bodies'));

			$i=0;
		    foreach ($keys as $key) {
		    	$out[] = html_writer::start_tag('div', Array('id'=>'tab_'.$key, 'style'=>'display:'.($i==0?'block':'none')));
			    $courses = filter_coursecatalogue_courses_in_catalogue($categoryid, $key);
			    foreach ($courses as $course) {
					$out[] = filter_coursecatlogue_format_course_template($course, $CFG->filter_coursecatalogue_template);
			    }
				$out[] = html_writer::end_tag('div');
				$i++;
			}
		    $out[] = html_writer::end_tag('div'); // #tab-bodies
		    $out[] = html_writer::end_tag('div'); // #tab-catalogue

			return preg_replace($find, implode('',$out), $text);
	    }
		return $text;
    }
}

?>

