<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

	$name = 'filter_coursecatalogue_template';
	$title = get_string('configure','filter_coursecatalogue');
	$description = get_string('configure_desc', 'filter_coursecatalogue');

	$settings->add(new admin_setting_configtextarea($name, $title, $description, ''));

	if (file_exists($CFG->dirroot.'/local/course_meta/lib.php')) {
		require_once($CFG->dirroot.'/local/course_meta/lib.php');
		course_meta_rebuild_view();	
	}

}