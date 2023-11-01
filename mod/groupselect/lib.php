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
 * Library of functions and constants of Group selection module
 *
 * @package    mod
 * @subpackage groupselect
 * @copyright  2008-2011 Petr Skoda (http://skodak.org)
 * @copyright  2014 Tampere University of Technology, P. Pyykkönen (pirkka.pyykkonen ÄT tut.fi)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * List of features supported in groupselect module
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, false if not, null if doesn't know
 */
function groupselect_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_ARCHETYPE:           return MOD_ARCHETYPE_OTHER;
        case FEATURE_GROUPS:                  return true;  // only separate mode makes sense - you hide members of other groups here
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_GROUPMEMBERSONLY:        return false;  // this could be very confusing
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return false;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;

        default: return null;
    }
}

/**
 * Returns all other caps used in module
 * @return array
 */
function groupselect_get_extra_capabilities() {
    return array('moodle/site:accessallgroups', 'moodle/site:viewfullnames');
}

/**
 * Given an object containing all the necessary data, (defined by the form in mod.html)
 * this function will create a new instance and return the id number of the new instance.
 *
 * @param object $groupselect Object containing all the necessary data defined by the form in mod_form.php
 * $return int The id of the newly created instance
 */
function groupselect_add_instance($groupselect) {
    global $DB;

    $groupselect->timecreated = time();
    $groupselect->timemodified = time();

    $groupselect->id = $DB->insert_record('groupselect', $groupselect);

    groupselect_set_events($groupselect);

    return $groupselect->id;
}


/**
 * Update an existing instance with new data.
 *
 * @param object $groupselect An object containing all the necessary data defined by the mod_form.php
 * @return bool
 */
function groupselect_update_instance($groupselect) {
    global $DB;

    $groupselect->timemodified = time();
    $groupselect->id = $groupselect->instance;

    $DB->update_record('groupselect', $groupselect);

    groupselect_set_events($groupselect);

    return true;
}


/**
 * Permanently delete the instance of the module and any data that depends on it.
 *
 * @param int $id Instance id
 * @return bool
 */
function groupselect_delete_instance($id) {
    global $DB;
    // delete group password rows related to this instance (but not the groups)
    $DB->delete_records('groupselect_passwords', array('instance_id'=>$id));

    $DB->delete_records('groupselect_groups_teachers', array('instance_id'=>$id));

    $DB->delete_records('groupselect', array('id'=>$id));

    return true;
}

/**
 * This standard function will check all instances of this module
 * and make sure there are up-to-date events created for each of them.
 * If courseid = 0, then every chat event in the site is checked, else
 * only chat events belonging to the course specified are checked.
 * This function is used, in its new format, by restore_refresh_events()
 *
 * @param int $courseid
 * @return bool
 */
function groupselect_refresh_events($courseid = 0) {
    global $DB;

    $params = $courseid ? ['course' => $courseid] : [];
    $modules = $DB->get_records('groupselect', $params);

    foreach ($modules as $module) {
        groupselect_set_events($module);
    }
    return true;
}

/**
 * This creates new events given as timeopen and closeopen by $feedback.
 *
 * @param stdClass $groupselect
 * @return void
 */
function groupselect_set_events($groupselect) {
    global $DB, $CFG;

    // Include calendar/lib.php.
    require_once($CFG->dirroot.'/calendar/lib.php');

    // Get CMID if not sent as part of $groupselect.
    if (!isset($groupselect->coursemodule)) {
        $cm = get_coursemodule_from_instance('groupselect',
                $groupselect->id, $groupselect->course);
        $groupselect->coursemodule = $cm->id;
    }

    // Find existing calendar event.
    $event = $DB->get_record('event',
            array('modulename' => 'groupselect',
                'instance' => $groupselect->id, 'eventtype' => 'due'));

    if ($event) {
        $calendarevent = calendar_event::load($event);

        if ($groupselect->timedue) {
            // Update calendar event.
            $data = fullclone($event);
            $data->name = $groupselect->name;
            $data->description = format_module_intro('groupselect', $groupselect, $groupselect->coursemodule);
            $data->timestart = $groupselect->timedue;
            $calendarevent->update($data);
        } else {
            // Delete calendar event.
            $calendarevent->delete();
        }

    } else if ($groupselect->timedue) {

        // Create calendar event.
        $event->name = $groupselect->name;
        $event->description = format_module_intro('groupselect', $groupselect, $groupselect->coursemodule); // TODO: this is weird
        $event->courseid = $groupselect->course;
        $event->groupid = 0;
        $event->userid = 0;
        $event->modulename = 'groupselect';
        $event->instance = $groupselect->id;
        $event->eventtype = 'due';
        $event->timestart = $groupselect->timedue;
        $event->timeduration = 0;

        calendar_event::create($event);
    }
}


/**
 * Returns the users with data in this module
 *
 * We have no data/users here but this must exists in every module
 *
 * @param int $groupselectid
 * @return bool
 */
function groupselect_get_participants($groupselectid) {
    // no participants here - all data is stored in the group tables
    return false;
}


/**
 * groupselect_get_view_actions
 *
 * @return array
 */
function groupselect_get_view_actions() {
    return array('view', 'export');
}


/**
 * groupselect_get_post_actions
 *
 * @return array
 */
function groupselect_get_post_actions() {
    return array('select', 'unselect', 'create', 'assign');
}


/**
 * This function is used by the reset_course_userdata function in moodlelib.
 *
 * @param $data the data submitted from the reset course.
 * @return array status array
 */
function groupselect_reset_userdata($data) {
    // no resetting here - all data is stored in the group tables
    return array();
}

/**
 * Used to create exportable csv-file in view.php
 *
 * @param $data the data submitted from the reset course.
 * @return array status array
 */
function groupselect_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    // Check the contextlevel is as expected - if your plugin is a block, this becomes CONTEXT_BLOCK, etc.
    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    // Make sure the filearea is one of those used by the plugin.
    if ($filearea !== 'export') { //&& $filearea !== 'anotherexpectedfilearea') {
        return false;
    }

    // Make sure the user is logged in and has access to the module (plugins that are not course modules should leave out the 'cm' part).
    require_login($course, true, $cm);

    // Check the relevant capabilities - these may vary depending on the filearea being accessed.
    if (!has_capability('mod/groupselect:export', $context)) {
        return false;
    }

    // Leave this line out if you set the itemid to null in make_pluginfile_url (set $itemid to 0 instead).
    $itemid = array_shift($args); // The first item in the $args array.

    // Use the itemid to retrieve any relevant data records and perform any security checks to see if the
    // user really does have access to the file in question.

    // Extract the filename / filepath from the $args array.
    $filename = array_pop($args); // The last item in the $args array.
    if (!$args) {
        $filepath = '/'; // $args is empty => the path is '/'
    } else {
        $filepath = '/'.implode('/', $args).'/'; // $args contains elements of the filepath
    }

    // Retrieve the file from the Files API.
    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'mod_groupselect', $filearea, $itemid, $filepath, $filename);
    if (!$file) {
        return false; // The file does not exist.
    }

    // We can now send the file back to the browser - in this case with a cache lifetime of 1 day and no filtering.
    // From Moodle 2.3, use send_stored_file instead.
    send_stored_file($file, 86400, 0, 'true', $options);
}
