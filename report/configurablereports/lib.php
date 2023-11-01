<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Version details.
 *
 * @package    report
 * @subpackage configurablereports
 * @copyright  2009 Sam Hemelryk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * This function extends the navigation with the report items
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $course The course to object for the report
 * @param stdClass $context The context of the course
 */
function report_configurablereports_extend_navigation_course($navigation, $course, $context) {

    if (has_capability('report/configurablereports:view', $context)) {
        $url = new moodle_url('/report/configurablereports/index.php', array('id' => $course->id));
        $navigation->add(get_string('pluginname', 'report_configurablereports'), $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
    }
}

/**
 * This function extends the course navigation with the report items
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $user
 * @param stdClass $course The course to object for the report
 */
function report_configurablereports_extend_navigation_user($navigation, $user, $course) {

    return; //TODO: this plugin was not linked from navigation in 2.0, let's keep it that way for now --skodak

    if (report_configurablereports_can_access_user_report($user, $course)) {
        $url = new moodle_url('/report/configurablereports/index.php', array('userid' => $user->id, 'id' => $course->id));
        $navigation->add(get_string('pluginname', 'report_configurablereports'), $url);
    }
}

/**
 * Is current user allowed to access this report
 *
 * @private defined in lib.php for performance reasons
 *
 * @param stdClass $user
 * @param stdClass $course
 * @return bool
 */
function report_configurablereports_can_access_user_report($user, $course) {
    global $USER, $CFG;

    $coursecontext = context_course::instance($course->id);
    $personalcontext = context_user::instance($user->id);

    if (has_capability('report/configurablereports:view', $coursecontext)) {
        return true;
    }

    if (has_capability('moodle/user:viewuseractivitiesreport', $personalcontext)) {
        if ($course->showreports and (is_viewing($coursecontext, $user) or is_enrolled($coursecontext, $user))) {
            return true;
        }

    } else if ($user->id == $USER->id) {
        if ($course->showreports and (is_viewing($coursecontext, $USER) or is_enrolled($coursecontext, $USER))) {
            return true;
        }
    }

    return false;
}

/**
 * Return a list of page types
 * @param string $pagetype current page type
 * @param stdClass $parentcontext Block's parent context
 * @param stdClass $currentcontext Current context of block
 * @return array
 */
function report_configurablereports_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $array = array(
        '*' => get_string('page-x', 'pagetype'),
        'report-*' => get_string('page-report-x', 'pagetype'),
        'report-configurablereports-*' => get_string('page-report-configurablereports-x', 'report_configurablereports'),
        'report-configurablereports-index' => get_string('page-report-configurablereports-index', 'report_configurablereports'),
        'report-configurablereports-user' => get_string('page-report-configurablereports-user', 'report_configurablereports')
    );
    return $array;
}

/**
 * Add reports to myprofile page.
 *
 * @param \core_user\output\myprofile\tree $tree Tree object
 * @param stdClass $user user object
 * @param bool $iscurrentuser
 * @param stdClass $course Course object
 *
 * @return bool
 */
function report_configurablereports_myprofile_navigation(core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {
    if (empty($course)) {
        // We want to display these reports under the site context.
        $course = get_fast_modinfo(SITEID)->get_course();
    }

    //if (has_capability('report/configurablereports:view', $context)) {
    $url = new moodle_url('/report/configurablereports/index.php', array('id' => $course->id));

    $node = new core_user\output\myprofile\node('reports', 'configurablereports', get_string('configurablereports', 'report_configurablereports'), null, $url);
    $tree->add_node($node);
    // }
    return true;
}