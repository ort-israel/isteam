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
 * @package   mod_googledrive
 * @copyright  2016 Nadav Kavalerchik <nadavkav@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once($CFG->dirroot.'/mod/googledrive/lib.php');

    $rawmimetypes = gdrive_filetypes();
    foreach($rawmimetypes as $key => $rawmimetype) {
        $listmimetyles[$key] = $rawmimetype['name'];
    }

    $settings->add(new admin_setting_configmultiselect('googledrive_filetypes', get_string('filetypes', 'mod_googledrive'),
                       get_string('filetypes_moreinfo', 'mod_googledrive'), array(GDRIVEFILETYPE_DOCUMENT), $listmimetyles ));

/*
    if (!empty($CFG->enablerssfeeds)) {
        $options = array(
            0 => get_string('none'),
            1 => get_string('discussions', 'forum'),
            2 => get_string('posts', 'forum')
        );
        $settings->add(new admin_setting_configselect('forum_rsstype', get_string('rsstypedefault', 'forum'),
                get_string('configrsstypedefault', 'forum'), 0, $options));

        $options = array(
            0  => '0',
            1  => '1',
            2  => '2',
            3  => '3',
            4  => '4',
            5  => '5',
            10 => '10',
            15 => '15',
            20 => '20',
            25 => '25',
            30 => '30',
            40 => '40',
            50 => '50'
        );
        $settings->add(new admin_setting_configselect('forum_rssarticles', get_string('rssarticles', 'forum'),
                get_string('configrssarticlesdefault', 'forum'), 0, $options));
    }
*/
/*
// TODO: Sort out what is needed from mod_URL ...
        require_once("$CFG->libdir/resourcelib.php");

        $displayoptions = resourcelib_get_displayoptions(array(RESOURCELIB_DISPLAY_AUTO,
            RESOURCELIB_DISPLAY_EMBED,
            RESOURCELIB_DISPLAY_FRAME,
            RESOURCELIB_DISPLAY_OPEN,
            RESOURCELIB_DISPLAY_NEW,
            RESOURCELIB_DISPLAY_POPUP,
        ));
        $defaultdisplayoptions = array(RESOURCELIB_DISPLAY_AUTO,
            RESOURCELIB_DISPLAY_EMBED,
            RESOURCELIB_DISPLAY_OPEN,
            RESOURCELIB_DISPLAY_POPUP,
        );

        //--- general settings -----------------------------------------------------------------------------------
        $settings->add(new admin_setting_configtext('url/framesize',
            get_string('framesize', 'url'), get_string('configframesize', 'url'), 130, PARAM_INT));
        $settings->add(new admin_setting_configpasswordunmask('url/secretphrase', get_string('password'),
            get_string('configsecretphrase', 'url'), ''));
        $settings->add(new admin_setting_configcheckbox('url/rolesinparams',
            get_string('rolesinparams', 'url'), get_string('configrolesinparams', 'url'), false));
        $settings->add(new admin_setting_configmultiselect('url/displayoptions',
            get_string('displayoptions', 'url'), get_string('configdisplayoptions', 'url'),
            $defaultdisplayoptions, $displayoptions));

        //--- modedit defaults -----------------------------------------------------------------------------------
        $settings->add(new admin_setting_heading('urlmodeditdefaults', get_string('modeditdefaults', 'admin'), get_string('condifmodeditdefaults', 'admin')));

        $settings->add(new admin_setting_configcheckbox('url/printintro',
            get_string('printintro', 'url'), get_string('printintroexplain', 'url'), 1));
        $settings->add(new admin_setting_configselect('url/display',
            get_string('displayselect', 'url'), get_string('displayselectexplain', 'url'), RESOURCELIB_DISPLAY_AUTO, $displayoptions));
        $settings->add(new admin_setting_configtext('url/popupwidth',
            get_string('popupwidth', 'url'), get_string('popupwidthexplain', 'url'), 620, PARAM_INT, 7));
        $settings->add(new admin_setting_configtext('url/popupheight',
            get_string('popupheight', 'url'), get_string('popupheightexplain', 'url'), 450, PARAM_INT, 7));
*/

}

