<?php

	//
	//	List courses plus their metadata for a given category that are listed in the catalogue
	//	Params: metadata category (id), name of tab
	//	Returns: db recordset object
	//
	function filter_coursecatalogue_courses_in_catalogue($categoryid, $tabname) {
		global $DB;
		$sql = "SELECT * FROM {vw_course_metadata} WHERE listed = 1 AND categoryid = :cat AND tab = :tab ORDER BY fullname";
		return $DB->get_recordset_sql($sql, array('cat'=>$categoryid, 'tab'=>$tabname));
	}

	//
	//	Gets a field's value
	//	Params: course (id), field (short name), metdata category (id)
	//  Returns: string
	//
	function filter_coursecatalogue_course_meta_field_value($course, $field, $categoryid) {
	    global $DB;
	    $sql = "SELECT ud.data
	            FROM {course_meta_info_data} ud
	            INNER JOIN {course_meta_info_field} uf
	            ON ud.fieldid = uf.id
	            WHERE uf.shortname = :field
	            AND uf.categoryid = :id
	            AND ud.courseid = :courseid";
	    if ($field = $DB->get_record_sql($sql, array('field'=>$field, 'courseid'=>$course, 'id'=>$categoryid))) {
	        return $field->data;
	    }
	    return '';
	}
	
	function filter_coursecatalogue_course_meta_field_id($courseid, $fieldid) {
	    global $DB;
	    $sql = "SELECT id FROM {course_meta_info_data} WHERE courseid = :course AND fieldid = :field";
	    if ($field = $DB->get_record_sql($sql, array('course'=>courseid, 'field'=>$fieldid))) {
	        return $field->id;
	    }
	}
	
	//
	//	Look up the id of a category by name
	//	Params: name of category
	//	Returns: integer
	//
	function filter_coursecatalogue_course_meta_categoryid($name) {
		global $DB;
		$sql = "SELECT id FROM {course_meta_info_category} WHERE name = :name";
	    if ($field = $DB->get_record_sql($sql, array('name' => $name))) {
	    	return $field->id;
	    }
	    return -1;
	}
	
	//
	// Moodle can't return the result of a SP, and MySQL can't do pivots too easily, so we build a view
	// Views can't have dynamic columns either, so we have to rebuild the view itself to reflect any new
	// column names that might have been added.
	//
	
	function filter_coursecatalogue_rebuild_view() {
		global $DB;
		$built = Array();
		$sql = "SELECT shortname FROM {course_meta_info_field}";
		if ($rows = $DB->get_recordset_sql($sql)) {
			foreach ($rows as $row) {
				$built[] = "MAX(IF(f.shortname = '". $row->shortname ."', i.data, NULL)) AS " . $row->shortname;
			}
		}
		if (count($built)>0) {
			$sql = "CREATE OR REPLACE VIEW {vw_course_metadata} AS (
					SELECT c.fullname, ". implode($built,',') .", f.`categoryid`,  c.`id` as courseid
					FROM {course_meta_info_data} i
					JOIN {course_meta_info_field} f ON i.fieldid = f.id
					JOIN {course} c ON i.courseid = c.id
					GROUP BY i.courseid)";
			$DB->execute($sql);
		}
	}
	
	//
	// Cron task to keep view up to date
	//
	
	function filter_coursecatalogue_cron () {
		filter_coursecatalogue_rebuild_view();
	}
	
	//
	//	Grab a givem record from the meta field definition
	//	Params: shortname of field, metadata categoryid
	//	Returns: db row object
	//
	function filter_coursecatalogue_course_meta_info_row($shortname, $categoryid) {
		global $DB;
		$sql = "SELECT * FROM {course_meta_info_field}
				WHERE shortname = :name
				AND categoryid = :id";
		if ($row = $DB->get_record_sql($sql, array('name' => $shortname, 'id' => $categoryid))) {
			return $row;
		}
		return false;
	}
	
	function filter_coursecatlogue_format_cpdinfo($csv) {
		$lines = preg_split('/\r\n|\r|\n/',$csv);
		$out = Array();
		$out[] = html_writer::start_tag('table', array('class'=>'cpd-text')) . html_writer::start_tag('tbody'); // "<table><tbody>";
		foreach ($lines as $line) {
			$cells = explode(';',$line);
			$out[] = html_writer::tag('tr',
                html_writer::tag('th', $cells[0], array('class' => 'label c0')) .
                html_writer::tag('td', $cells[1], array('class' => 'info c1')));
		}
		$out[] = html_writer::end_tag('tbody') . html_writer::end_tag('table');
		return implode($out,'');
	}
	
	function filter_coursecatlogue_format_file($vals) {
		global $DB;
        $context = context_system::instance();
        $fs = get_file_storage();
        if (empty($vals)) { return; }
		$val = explode(",",$vals);
		$fileid = $DB->get_field('course_meta_info_data', 'id', array('courseid' => $val[0], 'fieldid' => $val[1]));
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
        return '';
	}
	
	function filter_coursecatlogue_format_course_template($course, $template) {
		global $DB;
		if (!empty($template)) {
		
			$course_as_array = (array)$course; // CAST an object to an array
			$course_keys = array_keys($course_as_array); // Create an array of the keys
			foreach ($course_keys as $key) { // replace known templates their values
				$template = str_replace('['.$key.']',$course->{$key},$template);
			}

			// handle IF blocks
			$start = strpos($template, '[#if ');
			while ($start > 0):
				$start_close = strpos($template, ']', $start);
				$key = substr($template, $start + 5, $start_close - ($start + 5));
				if (empty($course->{$key}) || ($course->{$key} == FALSE)) { // ($course->{$key} === 0) || 
					$end = strpos($template, '[/if ' . $key . ']', $start_close);
					$end_close = $end + strlen($key) + 6; // strpos($template, ']', $end);
					$val = substr($template, $start, $end_close - $start);
					$template = str_replace($val,'',$template);
				} else {
					$template = str_replace('[#if '.$key.']','',$template);
					$template = str_replace('[/if '.$key.']','',$template);
				}
				$start = strpos($template, '[#if ');
			endwhile;
			
			// handle TABLE blocks
			$start = strpos($template, '[#table ');
			while ($start > 0):
				$start_close = strpos($template, ']', $start);
				$key = substr($template, $start + 8, $start_close - ($start + 8));
				$val = filter_coursecatlogue_format_cpdinfo($course->{$key});
				$template = str_replace('[#table ' . $key . ']', $val, $template);
				$start = strpos($template, '[#table ');
			endwhile;

			// handle IMAGE blocks
			$start = strpos($template, '[#image ');
			while ($start > 0):
				$start_close = strpos($template, ']', $start);
				$key = substr($template, $start + 8, $start_close - ($start + 8));
				$val = html_writer::empty_tag('img', Array('src'=>filter_coursecatlogue_format_file($course->{$key})));
				$template = str_replace('[#image ' . $key . ']', $val, $template);
				$start = strpos($template, '[#image ');
			endwhile;

			// handle LINK blocks
			$start = strpos($template, '[#link ');
			while ($start > 0):
				$start_close = strpos($template, ']', $start);
				$key = substr($template, $start + 7, $start_close - ($start + 7));
				$val = html_writer::empty_tag('a', Array('href'=>filter_coursecatlogue_format_file($course->{$key})));
				$template = str_replace('[#link ' . $key . ']', $val, $template);
				$start = strpos($template, '[#link ');
			endwhile;

			// return the final template string
			return $template;

		}
		return 'empty';
	}