<?php

/**
 * Handles defining the element when creating
 *
 * @package    profilefield
 * @subpackage menukeys
 * @author     Mark Nelson <mark@moodle.com.au>, Pukunui Technology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class profile_define_menukeys extends profile_define_base {

    function define_form_specific(&$form) {
        // Param 1 for the code options
        $form->addElement('textarea', 'param1', get_string('profilemenukeys', 'profilefield_menukeys'), array('rows' => 6, 'cols' => 40));
        $form->setType('param1', PARAM_MULTILANG);

        // Param 2 for menu type contains the options
        $form->addElement('textarea', 'param2', get_string('profilemenuoptions', 'admin'), array('rows' => 6, 'cols' => 40));
        $form->setType('param2', PARAM_MULTILANG);

        // Default data
        $form->addElement('text', 'defaultdata', get_string('profiledefaultdata', 'admin'), 'size="50"');
        $form->setType('defaultdata', PARAM_MULTILANG);
    }

    function define_validate_specific($data, $files) {
        $err = array();

        $data->param1 = str_replace("\r", '', $data->param1);
        $data->param2 = str_replace("\r", '', $data->param2);

        // Get the keys
        $keys = explode("\n", $data->param1);
        $numkeys = count($keys);
        $options = explode("\n", $data->param2);
        $numoptions = count($options);

        // Check that we have at least 2 options
        if ($keys === false) {
            $err['param1'] = get_string('profilemenunokeys', 'profilefield_menukeys');
        } else if ($numkeys < 2) {
            $err['param1'] = get_string('profilemenutoofewkeys', 'profilefield_menukeys');
        } else if ($numkeys != $numoptions) {
            $err['param2'] = get_string('profilemenuoptionkeymismatch', 'profilefield_menukeys');
        } else if (!empty($data->defaultdata) &&
                !in_array($data->defaultdata, $keys)) { // Check the default data exists in the options
            $err['defaultdata'] = get_string('profilemenudefaultnotinoptions', 'admin');
        }

        return $err;
    }

    function define_save_preprocess($data) {
        $data->param1 = str_replace("\r", '', $data->param1);
        $data->param2 = str_replace("\r", '', $data->param2);

        return $data;
    }

}


