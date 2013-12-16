<?php

/**
 * Handles saving and displaying the menu keys
 *
 * @package    profilefield
 * @subpackage menukeys
 * @copyright  Aspen
 * @author     Mark Nelson <mark@moodle.com.au>, Pukunui Technology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class profile_field_menukeys extends profile_field_base {
    var $options;
    var $datakey;

    /**
     * Constructor method.
     * Pulls out the options for the menu from the database and sets the
     * the corresponding key for the data if it exists
     */
    function profile_field_menukeys($fieldid=0, $courseid=0) {
        //first call parent constructor
        $this->profile_field_base($fieldid, $courseid);

        // Param 1 for menukeys type is the keys
        $keys = explode("\n", $this->field->param1);
        // Param 1 for menukeys type is the options
        $options = explode("\n", $this->field->param2);
        $numoptions = count($options);

        $this->options = array();
        for ($i=0; $i<$numoptions; $i++) {
            $this->options[$keys[$i]] = $options[$i];
        }

        // Set the data key
        if ($this->data !== NULL) {
            $this->datakey = (int)array_search($this->data, $this->options);
        }
    }

    /**
     * Create the code snippet for this field instance
     * Overwrites the base class method
     * @param   object   moodleform instance
     */
    function edit_field_add(&$mform) {
        $mform->addElement('select', $this->inputname, format_string($this->field->name), $this->options);
    }

    /**
     * Set the default value for this field instance
     * Overwrites the base class method
     */
    function edit_field_set_default(&$mform) {
        if (FALSE !==array_search($this->field->defaultdata, $this->options)){
            $defaultkey = (int)array_search($this->field->defaultdata, $this->options);
        } else {
            $defaultkey = '';
        }
        $mform->setDefault($this->inputname, $defaultkey);
    }

    /**
     * HardFreeze the field if locked.
     * @param   object   instance of the moodleform class
     */
    function edit_field_set_locked(&$mform) {
        if (!$mform->elementExists($this->inputname)) {
            return;
        }
        if ($this->is_locked() and !has_capability('moodle/user:update', get_context_instance(CONTEXT_SYSTEM))) {
            $mform->hardFreeze($this->inputname);
            $mform->setConstant($this->inputname, $this->datakey);
        }
    }

    /**
     * Display the data for this field
     */
    function display_data() {
        return $this->options[$this->data];
    }
}


