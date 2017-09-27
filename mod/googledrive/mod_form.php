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
 * The main googledrive configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod_googledrive
 * @copyright  2016 Nadav Kavalerchik <nadavkav@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/googledrive/locallib.php');

use mod_googledrive\googledrive;

/**
 * Module instance settings form
 *
 * @package    mod_googledrive
 * @copyright  2016 Nadav Kavalerchik <nadavkav@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_googledrive_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG, $COURSE;

        $mform = $this->_form;

        //$config = get_config('googledrive');

        // Adding the "general" fieldset, where all the common settings are showed.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        //require_once($CFG->libdir . '/google/lib.php');
        $gdrive = new googledrive(null);
        if (!$gdrive->check_google_login()) {
            $googleauthlink = $gdrive->display_login_button();
            $mform->addElement('static', 'needauthentication', '', get_string('needauthentication', 'googledrive'). '<br/>'. $googleauthlink);
            //$mform->addElement('html', $googleauthlink);
        }

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('googledrivename', 'googledrive'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'googledrivename', 'googledrive');

        // Adding the standard "intro" and "introformat" fields.
        //$this->standard_intro_elements();
        $mform->addElement('textarea', 'intro', get_string('googledriveintro', 'googledrive'), array('size' => '64'));
        $mform->setType('intro', PARAM_TEXT);
        $mform->addElement('hidden', 'introformat', FORMAT_PLAIN);
        $mform->setType('introformat', PARAM_INT);

        $rawmimetypes = gdrive_filetypes();
        foreach($rawmimetypes as $key => $rawmimetype) {
            $listmimetyles[$key] = $rawmimetype['name'];
        }
        $mform->addElement('select', 'gdrivetype', get_string('filetypes', 'googledrive'), $listmimetyles);
        $mform->setDefault('gdrivetype', GDRIVEFILETYPE_DOCUMENT);
        $mform->addHelpButton('gdrivetype', 'filetypes_moreinfo', 'googledrive');

        $rawfilepermissions = gdrive_filepermissions();
        foreach($rawfilepermissions  as $key => $rawfilepermission) {
            $listfilepermissions[$key] = $rawfilepermission['name'];
        }
        $mform->addElement('select', 'gdrivepermissions', get_string('gdrivepermissions', 'googledrive'), $listfilepermissions);
        $mform->setDefault('gdrivepermissions', GDRIVEFILEPERMISSION_AUTHER_STUDENTS_RC);
        $mform->addHelpButton('gdrivepermissions', 'gdrivepermissions_moreinfo', 'googledrive');

        $mform->addElement('static', 'nextisagooglefileurl', '', get_string('nextisagooglefileurl', 'googledrive'));
        //$mform->addElement('hidden', 'externalurl');
        //$mform->addElement('url', 'externalurl', get_string('externalurl', 'url'), array('size'=>'60'), array('usefilepicker'=>true));
        $mform->addElement('text', 'gdriveurl', get_string('gdriveurl', 'googledrive'), array('size'=>'60'));
        $mform->setType('gdriveurl', PARAM_RAW_TRIMMED);
        //$mform->addRule('externalurl', null, 'required', null, 'client');

        $mform->addElement('hidden', 'display');
        $mform->setType('display', PARAM_INT);
        $mform->setDefault('display', 0);
        $mform->addElement('hidden', 'displayoptions');
        $mform->setType('displayoptions', PARAM_TEXT);
        $mform->setDefault('displayoptions', '');
        $mform->addElement('hidden', 'course');
        $mform->setType('course', PARAM_INT);
        $mform->setDefault('course', $COURSE->id);

        // Adding the rest of googledrive settings, spreading all them into this fieldset
        // ... or adding more fieldsets ('header' elements) if needed for better logic.
        //$mform->addElement('static', 'label1', 'googledrivesetting1', 'Your googledrive fields go here. Replace me!');

        //$mform->addElement('header', 'googledrivefieldset', get_string('googledrivefieldset', 'googledrive'));
        //$mform->addElement('static', 'label2', 'googledrivesetting2', 'Your googledrive fields go here. Replace me!');

        // Add standard grading elements.
        //$this->standard_grading_coursemodule_elements();

        // Add standard elements, common to all modules.
        $this->standard_coursemodule_elements();

        // Add standard buttons, common to all modules.
        $this->add_action_buttons();
    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Validating Entered url, we are looking for obvious problems only,
        // teachers are responsible for testing if it actually works.

        // This is not a security validation!! Teachers are allowed to enter "javascript:alert(666)" for example.

        // NOTE: do not try to explain the difference between URL and URI, people would be only confused...

        if (!empty($data['gdriveurl'])) {
            $url = $data['gdriveurl'];
            if (preg_match('|^/|', $url)) {
                // links relative to server root are ok - no validation necessary

            } else if (preg_match('|^[a-z]+://|i', $url) or preg_match('|^https?:|i', $url) or preg_match('|^ftp:|i', $url)) {
                // normal URL
                if (!googledrive_appears_valid_url($url)) {
                    $errors['gdriveurl'] = get_string('invalidurl', 'url');
                }

            } else if (preg_match('|^[a-z]+:|i', $url)) {
                // general URI such as teamspeak, mailto, etc. - it may or may not work in all browsers,
                // we do not validate these at all, sorry

            } else {
                // invalid URI, we try to fix it by adding 'http://' prefix,
                // relative links are NOT allowed because we display the link on different pages!
                if (!googledrive_appears_valid_url('http://'.$url)) {
                    $errors['gdriveurl'] = get_string('invalidurl', 'url');
                }
            }
        }
        return $errors;
    }
}
