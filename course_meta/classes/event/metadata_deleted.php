<?php
	
namespace local_course_meta\event;

defined('MOODLE_INTERNAL') || die();

class metadata_deleted extends \core\event\course_deleted {

    protected function init() {
	    $this->data['crud'] = 'd';
	    $this->data['edulevel'] = self::LEVEL_TEACHING;
        $this->data['objecttable'] = 'courses'; // because course_meta extends courses table;
        parent::init();
    }
    
    public static function get_name() {
	    return get_string('event_course_meta','course_meta');
    }

    public static function get_description() {
	    return "The user with id {$this->userid} deleted metadata fields for course with id {$this->objectid}.";
    }
}
