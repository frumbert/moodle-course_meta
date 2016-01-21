<?php
/**
 * coursemeta filter version information
 *
 * @package    filter
 * @subpackage data
 * @copyright  2014 tim
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version  = 2016012200;
$plugin->requires = 2012061700;  // Requires this Moodle version
$plugin->component= 'filter_coursemeta';

// $plugin->cron	= 43200; // run twice a day