<?php

require('../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/local/course_meta/profile/lib.php');
require_once($CFG->dirroot.'/local/course_meta/profile/definelib.php');

$action   = optional_param('action', '', PARAM_ALPHA);

$redirect = $CFG->wwwroot.'/local/course_meta/profile/index.php';

$strchangessaved    = get_string('changessaved');
$strcancelled       = get_string('cancelled');
$strdefaultcategory = get_string('profiledefaultcategory', 'admin');
$strnofields        = get_string('profilenofieldsdefined', 'admin');
$strcreatefield     = get_string('profilecreatefield', 'admin');
$strtitle = get_string('courseprofilefields', 'local_course_meta');
$systemcontext = get_system_context();

require_login();
require_capability('local/course_meta:admin', $systemcontext);

$PAGE->set_url('/local/course_meta/profile/index.php');
$PAGE->set_pagetype('site-index');
$PAGE->set_docs_path('');
$PAGE->set_pagelayout('general');
$PAGE->set_context($systemcontext);
$PAGE->set_title($strtitle);

/// Do we have any actions to perform before printing the header

switch ($action) {
    case 'movecategory':
        $id  = required_param('id', PARAM_INT);
        $dir = required_param('dir', PARAM_ALPHA);

        if (confirm_sesskey()) {
            profile_move_category($id, $dir);
        }
        redirect($redirect);
        break;
    case 'movefield':
        $id  = required_param('id', PARAM_INT);
        $dir = required_param('dir', PARAM_ALPHA);

        if (confirm_sesskey()) {
            profile_move_field($id, $dir);
        }
        redirect($redirect);
        break;
    case 'deletecategory':
        $id      = required_param('id', PARAM_INT);
        profile_delete_category($id);
        redirect($redirect,get_string('deleted'));
        break;
    case 'deletefield':
        $id      = required_param('id', PARAM_INT);
        $confirm = optional_param('confirm', 0, PARAM_BOOL);

        $datacount = $DB->count_records('course_meta_info_data', array('fieldid'=>$id));
        if (data_submitted() and ($confirm and confirm_sesskey()) or $datacount===0) {
            profile_delete_field($id);
            redirect($redirect,get_string('deleted'));
        }

        //ask for confirmation
        $fieldname = $DB->get_field('course_meta_info_field', 'name', array('id'=>$id));
        $optionsyes = array ('id'=>$id, 'confirm'=>1, 'action'=>'deletefield', 'sesskey'=>sesskey());
        $strheading = get_string('profiledeletefield', 'admin', $fieldname);
        $PAGE->navbar->add($strheading);
        echo $OUTPUT->header();
        echo $OUTPUT->heading($strheading);
        $formcontinue = new single_button(new moodle_url($redirect, $optionsyes), get_string('yes'), 'post');
        $formcancel = new single_button(new moodle_url($redirect), get_string('no'), 'get');
        echo $OUTPUT->confirm(get_string('profileconfirmfielddeletion', 'admin', $datacount), $formcontinue, $formcancel);
        echo $OUTPUT->footer();
        die;
        break;
    case 'editfield':
        $id       = optional_param('id', 0, PARAM_INT);
        $datatype = optional_param('datatype', '', PARAM_ALPHA);

        profile_edit_field($id, $datatype, $redirect);
        die;
        break;
    case 'editcategory':
        $id = optional_param('id', 0, PARAM_INT);

        profile_edit_category($id, $redirect);
        die;
        break;
    default:
        //normal form
}

/// Print the header
echo $OUTPUT->header();
echo $OUTPUT->heading($strtitle);

/// Check that we have at least one category defined
if ($DB->count_records('course_meta_info_category') == 0) {
    $defaultcategory = new stdClass();
    $defaultcategory->name = $strdefaultcategory;
    $defaultcategory->sortorder = 1;
    $DB->insert_record('course_meta_info_category', $defaultcategory);
    redirect($redirect);
}

/// Show all categories
$categories = $DB->get_records('course_meta_info_category', null, 'sortorder ASC');

foreach ($categories as $category) {
    $table = new html_table();
    $table->head  = array(get_string('profilefield', 'admin'), get_string('description'), get_string('edit'));
    $table->align = array('left', 'left', 'right');
    $table->width = '95%';
    $table->attributes['class'] = 'generaltable profilefield';
    $table->data = array();

    if ($fields = $DB->get_records('course_meta_info_field', array('categoryid'=>$category->id), 'sortorder ASC')) {
        foreach ($fields as $field) {
            // $table->data[] = array(format_string($field->name).'<br /><small>'.format_string($field->description).'</small>', profile_field_icons($field));
            $table->data[] = array(format_string($field->name), format_string($field->description), profile_field_icons($field));
        }
    }

    echo $OUTPUT->heading(format_string($category->name) .' '.profile_category_icons($category));
    if (count($table->data)) {
        echo html_writer::table($table);
    } else {
        echo $OUTPUT->notification($strnofields);
    }

} /// End of $categories foreach




echo '<hr />';
echo '<div class="profileeditor">';

/// Create a new field link
$options = profile_list_datatypes();
$popupurl = new moodle_url('/local/course_meta/profile/index.php?id=0&action=editfield');
echo $OUTPUT->single_select($popupurl, 'datatype', $options, '', array(''=>$strcreatefield), 'newfieldform');

//add a div with a class so themers can hide, style or reposition the text
html_writer::start_tag('div',array('class'=>'adminuseractionhint'));
echo get_string('or', 'lesson');
html_writer::end_tag('div');

/// Create a new category link
$options = array('action'=>'editcategory');
echo $OUTPUT->single_button(new moodle_url('index.php', $options), get_string('profilecreatecategory', 'admin'));

echo '</div>';

echo $OUTPUT->footer();
die;


/***** Some functions relevant to this script *****/

/**
 * Create a string containing the editing icons for the user profile categories
 * @param   object   the category object
 * @return  string   the icon string
 */
function profile_category_icons($category) {
    global $CFG, $USER, $DB, $OUTPUT;

    $strdelete   = get_string('delete');
    $strmoveup   = get_string('moveup');
    $strmovedown = get_string('movedown');
    $stredit     = get_string('edit');

    $categorycount = $DB->count_records('course_meta_info_category');
    $fieldcount    = $DB->count_records('course_meta_info_field', array('categoryid'=>$category->id));

    /// Edit
    $editstr = '<a title="'.$stredit.'" href="index.php?id='.$category->id.'&amp;action=editcategory"><img src="'.$OUTPUT->pix_url('t/edit') . '" alt="'.$stredit.'" class="iconsmall" /></a> ';

    /// Delete
    /// Can only delete the last category if there are no fields in it
    if ( ($categorycount > 1) or ($fieldcount == 0) ) {
        $editstr .= '<a title="'.$strdelete.'" href="index.php?id='.$category->id.'&amp;action=deletecategory';
        $editstr .= '"><img src="'.$OUTPUT->pix_url('t/delete') . '" alt="'.$strdelete.'" class="iconsmall" /></a> ';
    } else {
        $editstr .= '<img src="'.$OUTPUT->pix_url('spacer') . '" alt="" class="iconsmall" /> ';
    }

    /// Move up
    if ($category->sortorder > 1) {
        $editstr .= '<a title="'.$strmoveup.'" href="index.php?id='.$category->id.'&amp;action=movecategory&amp;dir=up&amp;sesskey='.sesskey().'"><img src="'.$OUTPUT->pix_url('t/up') . '" alt="'.$strmoveup.'" class="iconsmall" /></a> ';
    } else {
        $editstr .= '<img src="'.$OUTPUT->pix_url('spacer') . '" alt="" class="iconsmall" /> ';
    }

    /// Move down
    if ($category->sortorder < $categorycount) {
        $editstr .= '<a title="'.$strmovedown.'" href="index.php?id='.$category->id.'&amp;action=movecategory&amp;dir=down&amp;sesskey='.sesskey().'"><img src="'.$OUTPUT->pix_url('t/down') . '" alt="'.$strmovedown.'" class="iconsmall" /></a> ';
    } else {
        $editstr .= '<img src="'.$OUTPUT->pix_url('spacer') . '" alt="" class="iconsmall" /> ';
    }

    return $editstr;
}

/**
 * Create a string containing the editing icons for the user profile fields
 * @param   object   the field object
 * @return  string   the icon string
 */
function profile_field_icons($field) {
    global $CFG, $USER, $DB, $OUTPUT;

    $strdelete   = get_string('delete');
    $strmoveup   = get_string('moveup');
    $strmovedown = get_string('movedown');
    $stredit     = get_string('edit');

    $fieldcount = $DB->count_records('course_meta_info_field', array('categoryid'=>$field->categoryid));
    $datacount  = $DB->count_records('course_meta_info_data', array('fieldid'=>$field->id));

    /// Edit
    $editstr = '<a title="'.$stredit.'" href="index.php?id='.$field->id.'&amp;action=editfield"><img src="'.$OUTPUT->pix_url('t/edit') . '" alt="'.$stredit.'" class="iconsmall" /></a> ';

    /// Delete
    $editstr .= '<a title="'.$strdelete.'" href="index.php?id='.$field->id.'&amp;action=deletefield';
    $editstr .= '"><img src="'.$OUTPUT->pix_url('t/delete') . '" alt="'.$strdelete.'" class="iconsmall" /></a> ';

    /// Move up
    if ($field->sortorder > 1) {
        $editstr .= '<a title="'.$strmoveup.'" href="index.php?id='.$field->id.'&amp;action=movefield&amp;dir=up&amp;sesskey='.sesskey().'"><img src="'.$OUTPUT->pix_url('t/up') . '" alt="'.$strmoveup.'" class="iconsmall" /></a> ';
     } else {
        $editstr .= '<img src="'.$OUTPUT->pix_url('spacer') . '" alt="" class="iconsmall" /> ';
    }

    /// Move down
    if ($field->sortorder < $fieldcount) {
        $editstr .= '<a title="'.$strmovedown.'" href="index.php?id='.$field->id.'&amp;action=movefield&amp;dir=down&amp;sesskey='.sesskey().'"><img src="'.$OUTPUT->pix_url('t/down') . '" alt="'.$strmovedown.'" class="iconsmall" /></a> ';
    } else {
        $editstr .= '<img src="'.$OUTPUT->pix_url('spacer') . '" alt="" class="iconsmall" /> ';
    }

    return $editstr;
}



