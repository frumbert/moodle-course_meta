<?php

class profile_field_checkbox extends profile_field_base {

    /**
     * Constructor method.
     * Pulls out the options for the checkbox from the database and sets the
     * the corresponding key for the data if it exists
     */
    function profile_field_checkbox($fieldid=0, $courseid=0) {
        global $DB;
        //first call parent constructor
        $this->profile_field_base($fieldid, $courseid);

        if (!empty($this->field)) {
            $datafield = $DB->get_field('course_meta_info_data', 'data', array('courseid' => $this->courseid, 'fieldid' => $this->fieldid));
            if ($datafield !== false) {
                $this->data = $datafield;
            } else {
                $this->data = $this->field->defaultdata;
            }
        }
    }

    function edit_field_add(&$mform) {
        /// Create the form field
        $checkbox = &$mform->addElement('advcheckbox', $this->inputname, format_string($this->field->name));
        if ($this->data == '1') {
            $checkbox->setChecked(true);
        }
        $mform->setType($this->inputname, PARAM_BOOL);
        if (!has_capability('moodle/user:update', get_context_instance(CONTEXT_SYSTEM))) {
            $mform->addRule($this->inputname, get_string('required'), 'nonzero', null, 'client');
        }
    }

    /**
     * Display the data for this field
     */
    function display_data() {
        $options = new stdClass();
        $options->para = false;
        $checked = intval($this->data) === 1 ? 'checked="checked"' : '';
        return '<input disabled="disabled" type="checkbox" name="'.$this->inputname.'" '.$checked.' />';
    }

}


