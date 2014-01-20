<?php
/*
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

function filepicker_options() {
    return array(
			    'subdirs' => 0,
			    'maxfiles' => 1,
			    'context' => context_system::instance(),
			    'accepted_types'=>'*',
			    'return_types' => FILE_INTERNAL
	);
}

class profile_field_file extends profile_field_base {

    /**
     * Saves the data coming from form
     * @param   mixed   data coming from the form
     * @param   string  name of the prefix (ie, competency)
     * @return  mixed   returns data id if success of db insert/update, false on fail, 0 if not permitted
     */
    function edit_save_data($itemnew) { // , $prefix){ //, $tableprefix) {
        global $DB; //, $FILEPICKER_OPTIONS;
        
        $options = filepicker_options();

        $formelement = $this->inputname . "_filemanager";
        if (!isset($itemnew->$formelement)) {
            // field not present in form, probably locked and invisible - skip it
            return;
        }

		// if no existing stored data for this object, make a record so we can use its row id
        if (!$DB->get_field('course_meta_info_data', 'id', array('courseid' => $itemnew->id, 'fieldid' => $this->field->id))) {
	        $data = new stdClass();
	        $data->courseid		= $itemnew->id;
	        $data->fieldid      = $this->field->id;
	        $data->data = $itemnew->id . "," . $this->field->id; // the value in the view will contain reference to this row; we dont know our id yet
            $DB->insert_record('course_meta_info_data', $data);
        }
        // look up possibly new row id of course_meta_info_data - this will be the itemid in the file store
		$dataid = $DB->get_field('course_meta_info_data', 'id', array('courseid' => $itemnew->id, 'fieldid' => $this->field->id));

		// set the form data to contain the filemanager instance data for this file
        $itemnew = file_postupdate_standard_filemanager($itemnew,
        												$this->inputname,
        												$options,
        												$options['context'],
                                                        'course_meta_customfield',
                                                        'course_meta_filemanager',
                                                        $dataid);
    }

    /**
    * during editing this places file manager onto the page
    */
    function edit_field_add(&$mform) {
		$options = filepicker_options();
	   
        /// Create the file picker
        $mform->addElement('filemanager', $this->inputname.'_filemanager', format_string($this->field->name), null, $options);
    }

    /**
    * Sets the required flag for the field in the form object
    * @param   object   instance of the moodleform class
    */
    function edit_field_set_required(&$mform) {
        if ($this->is_required()) {
            $mform->addRule($this->inputname.'_filemanager', get_string('customfieldrequired', 'course_meta_customfield'), 'required', null, 'client');
        }
    }


    /**
    * Sets the locked flag for the field in the form object
    * @param   object   instance of the moodleform class
    */
    function edit_field_set_locked(&$mform) {
        if (!$mform->elementExists($this->inputname)) {
            return;
        }
        if ($this->is_locked()) {
            $mform->hardFreeze($this->inputname);
            $mform->disabledif($this->inputname, 1);
            $mform->setConstant($this->inputname, $this->data);
        }
    }

    /**
    * gets the moodle file url for this stored item
    */
	function display_data() {
        // global $FILEPICKER_OPTIONS;
        $options = filepicker_options();
        if (empty($this->data)) {
            return get_string('notset', 'profilefield_file'); // probably should be empty
        }
		$fileid = $this->id; // id; // row id from course_meta_info_data
        $context = $options['context']; // context_system::instance();
        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id,
        							'course_meta_customfield',
        							'course_meta_filemanager',
        							$fileid,
        							null,
        							false);
        if (count($files)>0) {
            $file = array_shift($files); // first item only
            $url = new moodle_url("/pluginfile.php/{$file->get_contextid()}/{$file->get_component()}/{$file->get_filearea()}" . $file->get_filepath() . $file->get_itemid().'/'.$file->get_filename());
            return $url;
        }
	}

    /**
    * during editing this prepares the file manager with the correct file reference
    * @param   object   instance of the userdata for this form (course edit form)
    */
	function edit_load_user_data(&$user) {
		global $DB; //, $FILEPICKER_OPTIONS;
		$options = filepicker_options();
		//$dataid = $this->data;
		//if (!empty($dataid)) {
		if ($dataid = $DB->get_field('course_meta_info_data', 'id', array('courseid' => $this->courseid, 'fieldid' => $this->fieldid))) {
			$user->{$this->inputname} = file_prepare_standard_filemanager($user,
																		$this->inputname,
																		$options,
																		$options['context'],
																		'course_meta_customfield',
																		'course_meta_filemanager',
																		$dataid);
		}
	}
	
}
?>
