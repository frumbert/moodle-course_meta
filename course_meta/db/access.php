<?php

/**
 * @package   localcourse_meta
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$capabilities = array(
    'local/course_meta:admin' => array(
        'riskbitmask' => RISK_DATALOSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    ),
    'local/course_meta:view' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'users' => CAP_ALLOW
        )
    ),
    'local/course_meta:edit' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW
            )
        )

);
