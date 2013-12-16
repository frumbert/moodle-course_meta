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
