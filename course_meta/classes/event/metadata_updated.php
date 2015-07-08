<?php
	
namespace local_course_meta\event;

defined('MOODLE_INTERNAL') || die();

class metadata_updated extends \core\event\course_updated {

    protected function init() {
        $this->data['objecttable'] = 'course';
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
        parent::init();
    }

    public static function get_name() {
        return get_string('eventmetadataupdated','local_course_meta');
    }

    public function get_description() {
	    return "The user with id {$this->userid} updated metadata fields for course with id {$this->objectid}.";
    }
}
