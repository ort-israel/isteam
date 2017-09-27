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
 * Library of interface functions and constants for module googledrive
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 *
 * All the googledrive specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package    mod_googledrive
 * @copyright  2016 Nadav Kavalerchik <nadavkav@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use mod_googledrive\googledrive;

/**
 * Google Drive file types.
 */
define('GDRIVEFILETYPE_DOCUMENT', 0);       // 'application/vnd.google-apps.document'
define('GDRIVEFILETYPE_PRESENTATION', 1);   // 'application/vnd.google-apps.presentation'
define('GDRIVEFILETYPE_SPREADSHEET', 2);    // 'application/vnd.google-apps.spreadsheet'
define('GDRIVEFILETYPE_MINDMAP', 3);        // 'application/vnd.google-apps.drive-sdk.758379822725' // 3rd party MindMup.com

/**
 * Google Drive file permissions.
 */
define('GDRIVEFILEPERMISSION_AUTHER_STUDENTS_RC', 0); // Auther (teacher) can RW and students can Comment.
define('GDRIVEFILEPERMISSION_AUTHER_STUDENTS_RW', 1); // Auther (teacher) can RW and students can Read and Write.

/**
 * @return array google drive file type names and mimetypes.
 * http://stackoverflow.com/questions/11412497/what-are-the-google-apps-mime-types-in-google-docs-and-google-drive#11415443
 */
function gdrive_filetypes() {
    $mimetypes = array();
    $mimetypes[GDRIVEFILETYPE_DOCUMENT] = array(
        'name' => get_string('gdrive_document', 'mod_googledrive'),
        'mimetype' => 'application/vnd.google-apps.document',
        'linktemplate' => 'https://docs.google.com/document/d/%s/edit?usp=sharing');
    $mimetypes[GDRIVEFILETYPE_PRESENTATION] = array(
        'name' => get_string('gdrive_presentation', 'mod_googledrive'),
        'mimetype' => 'application/vnd.google-apps.presentation',
        'linktemplate' => 'https://docs.google.com/presentation/d/%s/edit?usp=sharing');
    $mimetypes[GDRIVEFILETYPE_SPREADSHEET] = array(
        'name' => get_string('gdrive_spreadsheet', 'mod_googledrive'),
        'mimetype' => 'application/vnd.google-apps.spreadsheet',
        'linktemplate' => 'https://docs.google.com/spreadsheet/d/%s/edit?usp=sharing');
    $mimetypes[GDRIVEFILETYPE_MINDMAP] = array(
        'name' => get_string('gdrive_mindmap', 'mod_googledrive'),
        'mimetype' => 'application/vnd.google-apps.drive-sdk.758379822725',
        'linktemplate' => 'https://drive.mindmup.com/map/%s');

    return $mimetypes;
}

/**
 * @return array google drive file $filepermission names and ids.
 */
function gdrive_filepermissions() {
    $filepermissions = array();
    $filepermissions[GDRIVEFILEPERMISSION_AUTHER_STUDENTS_RC] =
        array('name' => get_string('gdrive_authorstudentsrc', 'mod_googledrive'));
    $filepermissions[GDRIVEFILEPERMISSION_AUTHER_STUDENTS_RW] =
        array('name' => get_string('gdrive_authorstudentsrw', 'mod_googledrive'));
    return $filepermissions;
}

/* Moodle core API */

/**
 * Returns the information on whether the module supports a feature
 *
 * See {@link plugin_supports()} for more info.
 *
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function googledrive_supports($feature) {

    switch($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return false;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the googledrive into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param stdClass $googledrive Submitted data from the form in mod_form.php
 * @param mod_googledrive_mod_form $mform The form instance itself (if needed)
 * @return int The id of the newly inserted googledrive record
 */
function googledrive_add_instance(stdClass $googledrive, mod_googledrive_mod_form $mform = null) {
    global $DB, $USER;

    $googledrive->timecreated = time();

    // Embed GoogleDoc
    //$googledrive->display = RESOURCELIB_DISPLAY_EMBED;
    //$googledrive->introformat = FORMAT_PLAIN;
    //$googledrive->intro = 'Google Doc';

    //$data->externalurl = url_fix_submitted_url($data->externalurl);
    $gdrive = new googledrive(null);
    if (!$gdrive->check_google_login()) {
        //$googleauthlink = $gdrive->display_login_button();
        //$mform->addElement('html',$googleauthlink);
        debugging('Error - not authenticated with Google!');
    }
    $author = array('emailAddress' => $USER->email, 'displayName' => fullname($USER));
    $context = context_course::instance($googledrive->course);
    //$users = get_enrolled_users($context);
    $coursestudents = get_role_users(5, $context);
    foreach ($coursestudents as $student) {
        $students[] = array('id' => $student->id, 'emailAddress' => $student->email,
            'displayName' => $student->firstname . ' ' . $student->lastname);
    }

    $gdrivedoc = $gdrive->create_gdrive_file($googledrive->name, $googledrive->gdrivetype,
        $googledrive->gdrivepermissions, $author, $students);
    $gdrivedoclink = new \moodle_url($gdrivedoc);
    $googledrive->gdriveurl = $gdrivedoclink->out(false);


    // You may have to add extra stuff in here.

    $googledrive->id = $DB->insert_record('googledrive', $googledrive);

    //googledrive_grade_item_update($googledrive);

    return $googledrive->id;
}

/**
 * Updates an instance of the googledrive in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param stdClass $googledrive An object from the form in mod_form.php
 * @param mod_googledrive_mod_form $mform The form instance itself (if needed)
 * @return boolean Success/Fail
 */
function googledrive_update_instance(stdClass $googledrive, mod_googledrive_mod_form $mform = null) {
    global $DB;

    $googledrive->timemodified = time();
    $googledrive->id = $googledrive->instance;

    if ($googledrive->intro == null) $googledrive->intro = $mform->get_current()->intro;
    if ($googledrive->introformat == null) $googledrive->introformat = $mform->get_current()->introformat;

    $googledrive->introeditor = null;

    // You may have to add extra stuff in here.

    $result = $DB->update_record('googledrive', $googledrive);

    //googledrive_grade_item_update($googledrive);

    return $result;
}

/**
 * This standard function will check all instances of this module
 * and make sure there are up-to-date events created for each of them.
 * If courseid = 0, then every googledrive event in the site is checked, else
 * only googledrive events belonging to the course specified are checked.
 * This is only required if the module is generating calendar events.
 *
 * @param int $courseid Course ID
 * @return bool
 */
function googledrive_refresh_events($courseid = 0) {
    global $DB;

    if ($courseid == 0) {
        if (!$googledrives = $DB->get_records('googledrive')) {
            return true;
        }
    } else {
        if (!$googledrives = $DB->get_records('googledrive', array('course' => $courseid))) {
            return true;
        }
    }

    foreach ($googledrives as $googledrive) {
        // Create a function such as the one below to deal with updating calendar events.
        // googledrive_update_events($googledrive);
    }

    return true;
}

/**
 * Removes an instance of the newmodule from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function googledrive_delete_instance($id) {
    global $DB;

    if (! $googledrive = $DB->get_record('googledrive', array('id' => $id))) {
        return false;
    }

    // Delete any dependent records here.

    $DB->delete_records('googledrive', array('id' => $googledrive->id));

    //googledrive_grade_item_delete($googledrive);

    return true;
}

/**
 * Returns a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 *
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @param stdClass $course The course record
 * @param stdClass $user The user record
 * @param cm_info|stdClass $mod The course module info object or record
 * @param stdClass $googledrive The googledrive instance record
 * @return stdClass|null
 */
function googledrive_user_outline($course, $user, $mod, $googledrive) {

    $return = new stdClass();
    $return->time = 0;
    $return->info = '';
    return $return;
}

/**
 * Prints a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * It is supposed to echo directly without returning a value.
 *
 * @param stdClass $course the current course record
 * @param stdClass $user the record of the user we are generating report for
 * @param cm_info $mod course module info
 * @param stdClass $googledrive the module instance record
 */
function googledrive_user_complete($course, $user, $mod, $googledrive) {
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in googledrive activities and print it out.
 *
 * @param stdClass $course The course record
 * @param bool $viewfullnames Should we display full names
 * @param int $timestart Print activity since this timestamp
 * @return boolean True if anything was printed, otherwise false
 */
function googledrive_print_recent_activity($course, $viewfullnames, $timestart) {
    return false;
}

/**
 * Prepares the recent activity data
 *
 * This callback function is supposed to populate the passed array with
 * custom activity records. These records are then rendered into HTML via
 * {@link googledrive_print_recent_mod_activity()}.
 *
 * Returns void, it adds items into $activities and increases $index.
 *
 * @param array $activities sequentially indexed array of objects with added 'cmid' property
 * @param int $index the index in the $activities to use for the next record
 * @param int $timestart append activity since this time
 * @param int $courseid the id of the course we produce the report for
 * @param int $cmid course module id
 * @param int $userid check for a particular user's activity only, defaults to 0 (all users)
 * @param int $groupid check for a particular group's activity only, defaults to 0 (all groups)
 */
function googledrive_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid=0, $groupid=0) {
}

/**
 * Prints single activity item prepared by {@link googledrive_get_recent_mod_activity()}
 *
 * @param stdClass $activity activity record with added 'cmid' property
 * @param int $courseid the id of the course we produce the report for
 * @param bool $detail print detailed report
 * @param array $modnames as returned by {@link get_module_types_names()}
 * @param bool $viewfullnames display users' full names
 */
function googledrive_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
}

/**
 * Function to be run periodically according to the moodle cron
 *
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * Note that this has been deprecated in favour of scheduled task API.
 *
 * @return boolean
 */
function googledrive_cron () {
    return true;
}

/**
 * Returns all other caps used in the module
 *
 * For example, this could be array('moodle/site:accessallgroups') if the
 * module uses that capability.
 *
 * @return array
 */
function googledrive_get_extra_capabilities() {
    return array();
}

/* Gradebook API */

/**
 * Is a given scale used by the instance of googledrive?
 *
 * This function returns if a scale is being used by one googledrive
 * if it has support for grading and scales.
 *
 * @param int $googledriveid ID of an instance of this module
 * @param int $scaleid ID of the scale
 * @return bool true if the scale is used by the given googledrive instance
 */
function googledrive_scale_used($googledriveid, $scaleid) {
    global $DB;

    if ($scaleid and $DB->record_exists('googledrive', array('id' => $googledriveid, 'grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Checks if scale is being used by any instance of googledrive.
 *
 * This is used to find out if scale used anywhere.
 *
 * @param int $scaleid ID of the scale
 * @return boolean true if the scale is used by any googledrive instance
 */
function googledrive_scale_used_anywhere($scaleid) {
    global $DB;

    if ($scaleid and $DB->record_exists('googledrive', array('grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Creates or updates grade item for the given googledrive instance
 *
 * Needed by {@link grade_update_mod_grades()}.
 *
 * @param stdClass $googledrive instance object with extra cmidnumber and modname property
 * @param bool $reset reset grades in the gradebook
 * @return void
 */
function googledrive_grade_item_update(stdClass $googledrive, $reset=false) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    $item = array();
    $item['itemname'] = clean_param($googledrive->name, PARAM_NOTAGS);
    $item['gradetype'] = GRADE_TYPE_VALUE;

    if ($googledrive->grade > 0) {
        $item['gradetype'] = GRADE_TYPE_VALUE;
        $item['grademax']  = $googledrive->grade;
        $item['grademin']  = 0;
    } else if ($googledrive->grade < 0) {
        $item['gradetype'] = GRADE_TYPE_SCALE;
        $item['scaleid']   = -$googledrive->grade;
    } else {
        $item['gradetype'] = GRADE_TYPE_NONE;
    }

    if ($reset) {
        $item['reset'] = true;
    }

    grade_update('mod/googledrive', $googledrive->course, 'mod', 'googledrive',
            $googledrive->id, 0, null, $item);
}

/**
 * Delete grade item for given googledrive instance
 *
 * @param stdClass $googledrive instance object
 * @return grade_item
 */
function googledrive_grade_item_delete($googledrive) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    return grade_update('mod/googledrive', $googledrive->course, 'mod', 'googledrive',
            $googledrive->id, 0, null, array('deleted' => 1));
}

/**
 * Update googledrive grades in the gradebook
 *
 * Needed by {@link grade_update_mod_grades()}.
 *
 * @param stdClass $googledrive instance object with extra cmidnumber and modname property
 * @param int $userid update grade of specific user only, 0 means all participants
 */
function googledrive_update_grades(stdClass $googledrive, $userid = 0) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');

    // Populate array of grade objects indexed by userid.
    $grades = array();

    grade_update('mod/googledrive', $googledrive->course, 'mod', 'googledrive', $googledrive->id, 0, $grades);
}

/* File API */

/**
 * Returns the lists of all browsable file areas within the given module context
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@link file_browser::get_file_info_context_module()}
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return array of [(string)filearea] => (string)description
 */
function googledrive_get_file_areas($course, $cm, $context) {
    return array();
}

/**
 * File browsing support for googledrive file areas
 *
 * @package mod_googledrive
 * @category files
 *
 * @param file_browser $browser
 * @param array $areas
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return file_info instance or null if not found
 */
function googledrive_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    return null;
}

/**
 * Serves the files from the googledrive file areas
 *
 * @package mod_googledrive
 * @category files
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the googledrive's context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 */
function googledrive_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload, array $options=array()) {
    global $DB, $CFG;

    if ($context->contextlevel != CONTEXT_MODULE) {
        send_file_not_found();
    }

    require_login($course, true, $cm);

    send_file_not_found();
}

/* Navigation API */

/**
 * Extends the global navigation tree by adding googledrive nodes if there is a relevant content
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $navref An object representing the navigation tree node of the googledrive module instance
 * @param stdClass $course current course record
 * @param stdClass $module current googledrive instance record
 * @param cm_info $cm course module information
 */
/*
function googledrive_extend_navigation(navigation_node $navref, stdClass $course, stdClass $module, cm_info $cm) {
    // TODO Delete this function and its docblock, or implement it.
}
*/

/**
 * Extends the settings navigation with the googledrive settings
 *
 * This function is called when the context for the page is a googledrive module. This is not called by AJAX
 * so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav complete settings navigation tree
 * @param navigation_node $googledrivenode googledrive administration node
 */
function googledrive_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $googledrivenode=null) {
    // TODO Delete this function and its docblock, or implement it.

    // TODO: what Google drive documents are allowed SELECT box
}
