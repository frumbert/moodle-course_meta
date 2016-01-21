<?php

/*
replaces the text [coursemeta.categoryname.fieldshortname] with its value for this course context
*/

class filter_coursemeta extends moodle_text_filter {
	public function filter($text, array $options = array()) {
		global $COURSE, $DB;

		$find = '/\[\w+\.(\w+)\.(\w+)*\]/';
		$courseid = $COURSE->id;

		preg_match_all($find,$text, $matches, PREG_SET_ORDER);

		foreach ($matches as $val) {
			$sql = "SELECT id FROM {course_meta_info_category} WHERE name = :name";
			if ($categoryid = $DB->get_record_sql($sql, array('name' => $val[1]))) {
				$sql = "SELECT ud.data
					FROM {course_meta_info_data} ud
					INNER JOIN {course_meta_info_field} uf
					ON ud.fieldid = uf.id
					WHERE uf.shortname = :field
					AND uf.categoryid = :id
					AND ud.courseid = :courseid";
				if ($field = $DB->get_record_sql($sql, array('field'=>$val[2], 'courseid'=>$courseid, 'id'=>$categoryid->id))) {
					$text = str_replace($val[0], $field->data, $text);
				}
			}
		}

		return $text;
	}
}
?>

