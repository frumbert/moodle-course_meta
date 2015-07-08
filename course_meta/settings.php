<?php

/**
 * @package   localcourse_meta
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$ADMIN->add('courses'
			, new admin_externalpage('courseprofilefields',
									get_string('courseprofilefields', 'local_course_meta'),
									$CFG->wwwroot."/local/course_meta/profile/index.php"
									)
			);

// local/course_meta:admin'));