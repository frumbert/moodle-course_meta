<?php
/**
 * course catalogue for avant, install view.
 * if fields are added to the course, they must be also updated in the view
 * since mysql can't do dynamic views
 *
 * @package    filter
 * @subpackage data
 * @copyright  2014 tim
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->dirroot.'/local/course_meta/lib.php');

function xmldb_filter_coursecatalogue_install() {

    global $DB;
    $dbman = $DB->get_manager();
	if ($dbman->table_exists('course_meta_info_data')) {
		course_meta_rebuild_view();
	}

}