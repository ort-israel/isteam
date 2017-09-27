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
 * Section_modules_navbar block caps.
 *
 * @package    block_section_modules_navbar
 * @copyright  Lea Cohen <leac@ort.org.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class block_section_modules_navbar extends block_list {

    function init() {
        $this->title = get_string('pluginname', 'block_section_modules_navbar');
    }

    function get_content() {
        global $OUTPUT;
        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        // user/index.php expect course context, so get one if page has module context.
        $currentcontext = $this->page->context->get_course_context(false);

        // don't show block in main course page
        if (empty($this->page->cm->id)) {
            $this->content = '';
            return $this->content;
        }


        // Get current activity's section
        $modinfo = get_fast_modinfo($this->page->course);


        // Get all activities in given section
        if (!empty($modinfo->sections[$this->page->cm->sectionnum])) {
            $this->listActivities($modinfo, $this->page->cm->sectionnum);
        }

        // Use cookies to save last section number before user navigated to common/hidden section 11
        // and display a link to that section when in section 11. (11 is hardcoded for project isteam!)
        $saved_sectionnum = 0;
        if ($this->page->cm->sectionnum != 11) {
            setcookie('moodle_smnav_section', $this->page->cm->sectionnum, time() + (86400 * 30), "/");
            // Always add activities from the 11th section.
            // TODO: Add a settings panel for defining which section holds the hidden activities
            $this->listActivities($modinfo, 11);
            return $this->content;
        } else {
            /* Save a cookie of the last section the user was in, so able to return to it.
             * Do so only in sections 1-10 */
            $saved_sectionnum = $_COOKIE['moodle_smnav_section'];

            $sectionmodnumbers = $modinfo->sections[$saved_sectionnum];
            $section_firstmodinfo = $modinfo->cms[$sectionmodnumbers[0]];
            if (isset($section_firstmodinfo) AND $section_firstmodinfo->uservisible
                AND !empty($section_firstmodinfo->url)
            ) {
                $url = $section_firstmodinfo->url;
                // Get a link to the course's frontpage.
                $sectionname = get_section_name($this->page->course, $saved_sectionnum);
                //$icon = '<img src="' . $icon = $OUTPUT->pix_url('icon', 'page') . '" class="icon" alt="" />&nbsp;';
                $this->content->items[] = '<a title="' . $sectionname . '" href="' . $url->out(false) . '" class="back-to-section">' /*. $icon */ . get_string('back_to_step', 'block_section_modules_navbar') . ': ' . $sectionname . '</a>';
            }
        }
    }

    // my moodle can only have SITEID and it's redundant here, so take it away
    public function applicable_formats() {
        return array('all' => false,
            'site' => true,
            'site-index' => true,
            'course-view' => true,
            'course-view-social' => false,
            'mod' => true,
            'mod-quiz' => false);
    }

    public function instance_allow_multiple() {
        return true;
    }

    function has_config() {
        return false;
    }

    public function cron() {
        mtrace("Hey, my cron script is running");

        // do something

        return true;
    }

    /**
     * @param $modinfo
     */
    public function listActivities($modinfo, $sectionNum) {
        global $PAGE;
        var_dump($sectionNum);
        foreach ($modinfo->sections[$sectionNum] as $cmid) {
            $cm = $modinfo->cms[$cmid];
            if (!$cm->uservisible) {
                continue;
            }

            $content = $cm->get_formatted_content(array('overflowdiv' => true, 'noclean' => true));
            $instancename = $cm->get_formatted_name();

            if (!($url = $cm->url)) {
            } else {
                $linkcss = $cm->visible ? '' : ' class="dimmed" ';
                // Accessibility: incidental image - should be empty Alt text
                $icon = '<img src="' . $cm->get_icon_url() . '" class="icon" alt="" />&nbsp;';
                $currentclass = $this->page->url == $cm->url ? 'class="current"' : '';
                $this->content->items[] = '<a title="' . $cm->modplural . '" ' . $linkcss . ' ' . $cm->extra . ' href="' .
                    $url . '"' . $currentclass . ' >' . $icon . $instancename . '</a>';
            }
        }
    }
}
