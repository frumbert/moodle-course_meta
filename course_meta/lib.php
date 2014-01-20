<?php

/**
 * Get a given course custom profile field
 * @global $DB
 * @param string $field the field name
 * @return object
 */
function course_meta_get_course_custom_field($course, $field) {
    global $DB;

    $sql = "SELECT ud.data
            FROM {course_meta_info_data} ud
            INNER JOIN {course_meta_info_field} uf
            ON ud.fieldid = uf.id
            WHERE uf.shortname = :field
            AND ud.courseid = :courseid";
    // Return the field
    if ($field = $DB->get_record_sql($sql, array('field' => $field, 'courseid' => $course))) {
        return $field->data;
    }

    return '';
}

//
//	A VIEW with courseid column, then columns representing each of the metadata fields, with their values.
//
function course_meta_rebuild_view() {
	global $DB;
	$built = Array();
	$sql = "SELECT shortname FROM {course_meta_info_field}";
	if ($rows = $DB->get_recordset_sql($sql)) {
		foreach ($rows as $row) {
			$built[] = "MAX(IF(f.shortname = '". $row->shortname ."', i.data, NULL)) AS " . $row->shortname;
		}
	}
	if (count($built) > 0) {
		$sql = "CREATE OR REPLACE VIEW {vw_course_metadata} AS (
				SELECT c.`id` as courseid, c.`fullname`,
				f.`categoryid`,".implode($built,',')." FROM {course_meta_info_data} i
				JOIN {course_meta_info_field} f ON i.fieldid = f.id
				JOIN {course} c ON i.courseid = c.id
				GROUP BY i.courseid)";
		$DB->execute($sql);
	}
}
	