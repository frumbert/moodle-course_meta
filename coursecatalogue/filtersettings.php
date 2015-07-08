<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

	// template html / markup
	$name = 'filter_coursecatalogue_template';
	$title = get_string('configure','filter_coursecatalogue');
	$description = get_string('configure_desc', 'filter_coursecatalogue');
	$settings->add(new admin_setting_configtextarea($name, $title, $description, '', PARAM_RAW, 100));

	// checkbox to determine if css and script are registered
	$name = 'filter_coursecatalogue_defaults';
	$title = get_string('defaultstyles','filter_coursecatalogue');
	$description = get_string('defaultstyles_desc', 'filter_coursecatalogue');
	$settings->add(new admin_setting_configcheckbox($name, $title, $description, '1'));

	// execute the view rebuild
	if (file_exists($CFG->dirroot.'/local/course_meta/lib.php')) {
		require_once($CFG->dirroot.'/local/course_meta/lib.php');
		course_meta_rebuild_view();	
	}

}