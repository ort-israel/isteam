<?php

/**
 * This is built using the bootstrapbase template to allow for new theme's using
 * Moodle's new Bootstrap theme engine
 *
 * @package     theme_eguru
 * @copyright   2015 Nephzat Dev Team, nephzat.com
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

//require_once($CFG->dirroot . "/blocks/course_overview/renderer.php");
require_once($CFG->dirroot . "/lib/ortutils.php");

class theme_eguru_block_course_overview_renderer extends block_course_overview_renderer {

    /**
     * Construct contents of course_overview block
     *
     * @param array $courses list of courses in sorted order
     * @param array $overviews list of course overviews
     * @return string html to be displayed in course_overview block
     */
    public function course_overview($courses, $overviews) {
        $html = '';
        $config = get_config('block_course_overview');
        if ($config->showcategories != BLOCKS_COURSE_OVERVIEW_SHOWCATEGORIES_NONE) {
            global $CFG;
            require_once($CFG->libdir . '/coursecatlib.php');
        }
        $ismovingcourse = false;
        $courseordernumber = 0;
        $maxcourses = count($courses);
        $userediting = false;
        // Intialise string/icon etc if user is editing and courses > 1
        if ($this->page->user_is_editing() && (count($courses) > 1)) {
            $userediting = true;
            $this->page->requires->js_init_call('M.block_course_overview.add_handles');

            // Check if course is moving
            $ismovingcourse = optional_param('movecourse', FALSE, PARAM_BOOL);
            $movingcourseid = optional_param('courseid', 0, PARAM_INT);
        }

        // Render first movehere icon.
        if ($ismovingcourse) {
            // Remove movecourse param from url.
            $this->page->ensure_param_not_in_url('movecourse');

            // Show moving course notice, so user knows what is being moved.
            $html .= $this->output->box_start('notice');
            $a = new stdClass();
            $a->fullname = $courses[$movingcourseid]->fullname;
            $a->cancellink = html_writer::link($this->page->url, get_string('cancel'));
            $html .= get_string('movingcourse', 'block_course_overview', $a);
            $html .= $this->output->box_end();

            $moveurl = new moodle_url('/blocks/course_overview/move.php',
                array('sesskey' => sesskey(), 'moveto' => 0, 'courseid' => $movingcourseid));
            // Create move icon, so it can be used.
            $movetofirsticon = html_writer::empty_tag('img',
                array('src' => $this->output->image_url('movehere'),
                    'alt' => get_string('movetofirst', 'block_course_overview', $courses[$movingcourseid]->fullname),
                    'title' => get_string('movehere')));
            $moveurl = html_writer::link($moveurl, $movetofirsticon);
            $html .= html_writer::tag('div', $moveurl, array('class' => 'movehere'));
        }

        foreach ($courses as $key => $course) {
            // If moving course, then don't show course which needs to be moved.
            if ($ismovingcourse && ($course->id == $movingcourseid)) {
                continue;
            }
            // Lea - add class to isteam course 
            $classes = is_course_isteam($course->idnumber) ? 'isteam-course' : '';
            $html .= $this->output->box_start('coursebox' . ' ' . $classes, "course-{$course->id}");
            $html .= html_writer::start_tag('div', array('class' => 'course_title'));
            // If user is editing, then add move icons.
            if ($userediting && !$ismovingcourse) {
                $moveicon = html_writer::empty_tag('img',
                    array('src' => $this->image_url('t/move')->out(false),
                        'alt' => get_string('movecourse', 'block_course_overview', $course->fullname),
                        'title' => get_string('move')));
                $moveurl = new moodle_url($this->page->url, array('sesskey' => sesskey(), 'movecourse' => 1, 'courseid' => $course->id));
                $moveurl = html_writer::link($moveurl, $moveicon);
                $html .= html_writer::tag('div', $moveurl, array('class' => 'move'));

            }

            // No need to pass title through s() here as it will be done automatically by html_writer.
            $attributes = array('title' => $course->fullname);
            if ($course->id > 0) {
                if (empty($course->visible)) {
                    $attributes['class'] = 'dimmed';
                }
                $courseurl = new moodle_url('/course/view.php', array('id' => $course->id));
                $coursefullname = format_string(get_course_display_name_for_list($course), true, $course->id);
                $link = html_writer::link($courseurl, $coursefullname, $attributes);
                $html .= $this->output->heading($link, 2, 'title');

                // Lea - Add course overviewfile
                $html .= $this->display_course_overviewfiles($course->id);
            } else {
                $html .= $this->output->heading(html_writer::link(
                        new moodle_url('/auth/mnet/jump.php', array('hostid' => $course->hostid, 'wantsurl' => '/course/view.php?id=' . $course->remoteid)),
                        format_string($course->shortname, true), $attributes) . ' (' . format_string($course->hostname) . ')', 2, 'title');
            }
            //$html .= $this->output->box('', 'flush');
            $html .= html_writer::end_tag('div');

            // Lea 2016/02 - show student progress here:
            $html .= $this->user_completion_status($course, $html);


            if (!empty($config->showchildren) && ($course->id > 0)) {
                // List children here.
                if ($children = block_course_overview_get_child_shortnames($course->id)) {
                    $html .= html_writer::tag('span', $children, array('class' => 'coursechildren'));
                }
            }

            // If user is moving courses, then down't show overview.
            if (isset($overviews[$course->id]) && !$ismovingcourse) {
                $html .= $this->activity_display($course->id, $overviews[$course->id]);
            }

            if ($config->showcategories != BLOCKS_COURSE_OVERVIEW_SHOWCATEGORIES_NONE) {
                // List category parent or categories path here.
                $currentcategory = coursecat::get($course->category, IGNORE_MISSING);
                if ($currentcategory !== null) {
                    $html .= html_writer::start_tag('div', array('class' => 'categorypath'));
                    if ($config->showcategories == BLOCKS_COURSE_OVERVIEW_SHOWCATEGORIES_FULL_PATH) {
                        foreach ($currentcategory->get_parents() as $categoryid) {
                            $category = coursecat::get($categoryid, IGNORE_MISSING);
                            if ($category !== null) {
                                $html .= $category->get_formatted_name() . ' / ';
                            }
                        }
                    }
                    $html .= $currentcategory->get_formatted_name();
                    $html .= html_writer::end_tag('div');
                }
            }

            //$html .= $this->output->box('', 'flush');
            $html .= $this->output->box_end();
            $courseordernumber++;
            if ($ismovingcourse) {
                $moveurl = new moodle_url('/blocks/course_overview/move.php',
                    array('sesskey' => sesskey(), 'moveto' => $courseordernumber, 'courseid' => $movingcourseid));
                $a = new stdClass();
                $a->movingcoursename = $courses[$movingcourseid]->fullname;
                $a->currentcoursename = $course->fullname;
                $movehereicon = html_writer::empty_tag('img',
                    array('src' => $this->output->image_url('movehere'),
                        'alt' => get_string('moveafterhere', 'block_course_overview', $a),
                        'title' => get_string('movehere')));
                $moveurl = html_writer::link($moveurl, $movehereicon);
                $html .= html_writer::tag('div', $moveurl, array('class' => 'movehere'));
            }
        }
        // Wrap course list in a div and return.
        return html_writer::tag('div', $html, array('class' => 'course_list'));
    }



    private function get_user_course_completion($modinfo, $user) {

        $result = new stdClass();
        $result->total = 0;
        $result->complete = 0;


        foreach ($modinfo->sections as $sectionid => $section) {

            $thissection = $modinfo->get_section_info($sectionid);

            $cancomplete = isloggedin() && !isguestuser();

            $completioninfo = new completion_info($modinfo->get_course());
            foreach ($modinfo->sections[$thissection->section] as $cmid) {
                $thismod = $modinfo->cms[$cmid];
                if ($thismod->modname == 'label') {
                    // Labels are special (not interesting for students)!
                    continue;
                }

                if ($thismod->uservisible) {
                    if ($thismod->modname == 'assign') { // only count mods of type assign, towards completion

                        if ($cancomplete && $completioninfo->is_enabled($thismod) != COMPLETION_TRACKING_NONE) {
                            $result->total++;
                            $completiondata = $completioninfo->get_data($thismod, true, $user->id);
                            if ($completiondata->completionstate == COMPLETION_COMPLETE ||
                                $completiondata->completionstate == COMPLETION_COMPLETE_PASS
                            ) {
                                $result->complete++;
                            }
                        }
                    }
                }
            }
        }
        return $result;
    }

    private function display_user_completion_status($completionstatus, $userid, $username) {
        global $PAGE;
        // Now that we have $total & $complete (yes!) :-)
        // Let's display it with redialProgress from D3js...

        // Output section completion data
        $output = '';
        if ($completionstatus->total > 0) {

            $output .= html_writer::tag('div', '', array('id' => 'radialprogress' . $userid,
                'class' => 'radialprogress', 'data-value' => $completionstatus->complete,
                'data-maxvalue' => $completionstatus->total));

            $PAGE->requires->js('/theme/eguru/javascript/d3js/d3.min.js');
            $PAGE->requires->js('/theme/eguru/javascript/d3js/radialProgress.js');
            $PAGE->requires->js('/theme/eguru/javascript/d3js/init.js');
            $PAGE->requires->js_init_call('start_radialProgress', array(
                $userid,
                $username,
                $completionstatus->complete,
                $completionstatus->total));
        }
        // added to topics/styles.css
        $PAGE->requires->css('/blocks/group_completion_status/javascript/d3js/style.css');

        return $output;
    }

    /**
     * @param $course
     * @param $USER
     * @param $html
     * @return string
     */
    protected function user_completion_status($course, $html) {
        global $USER;
        $html = '';

        // show only if user is student:
        $userroles = get_user_roles(context_course::instance($course->id), $USER->id);
        $isstudent = array_filter($userroles, function ($obj2) {

            if ($obj2->roleid == STUDENT) {
                return true;
            }
            return false;
        });

        if (count($isstudent) > 0) {
            // wrap all group statuses in a div
            $html .= html_writer::start_tag('div', array('class' => 'student-completion-status'));
            $modinfo = get_fast_modinfo($course);

            // get the completion status of this student in this course
            $usercompletions = $this->get_user_course_completion($modinfo, $USER);

            // display the status graphically
            $html .= $this->display_user_completion_status($usercompletions, $USER->id, $USER->firstname . ' ' . $USER->lastname);

            $html .= html_writer::end_tag('div');

        }
        return $html;
    }

    /**
     * Checks if course has any associated overview files
     *
     * @return bool
     */
    public function has_course_overviewfiles($courseid) {
        global $CFG;
        if (empty($CFG->courseoverviewfileslimit)) {
            return false;
        }
        $fs = get_file_storage();
        $context = context_course::instance($courseid);
        return !$fs->is_area_empty($context->id, 'course', 'overviewfiles');
    }

    /**
     * Returns all course overview files
     *
     * @return array array of stored_file objects
     */
    public function get_course_overviewfiles($courseid) {
        global $CFG;
        if (empty($CFG->courseoverviewfileslimit)) {
            return array();
        }
        require_once($CFG->libdir . '/filestorage/file_storage.php');
        require_once($CFG->dirroot . '/course/lib.php');
        $fs = get_file_storage();
        $context = context_course::instance($courseid);
        $files = $fs->get_area_files($context->id, 'course', 'overviewfiles', false, 'filename', false);
        if (count($files)) {
            $overviewfilesoptions = course_overviewfiles_options($courseid);
            $acceptedtypes = $overviewfilesoptions['accepted_types'];
            if ($acceptedtypes !== '*') {
                // Filter only files with allowed extensions.
                require_once($CFG->libdir . '/filelib.php');
                foreach ($files as $key => $file) {
                    if (!file_extension_in_typegroup($file->get_filename(), $acceptedtypes)) {
                        unset($files[$key]);
                    }
                }
            }
            if (count($files) > $CFG->courseoverviewfileslimit) {
                // Return no more than $CFG->courseoverviewfileslimit files.
                $files = array_slice($files, 0, $CFG->courseoverviewfileslimit, true);
            }
        }
        return $files;
    }

    protected function display_course_overviewfiles($courseid) {
        global $CFG;
        // display course overview files
        $contentimages = $contentfiles = '';
        foreach ($this->get_course_overviewfiles($courseid) as $file) {
            $isimage = $file->is_valid_image();
            $url = file_encode_url("$CFG->wwwroot/pluginfile.php",
                '/' . $file->get_contextid() . '/' . $file->get_component() . '/' .
                $file->get_filearea() . $file->get_filepath() . $file->get_filename(), !$isimage);
            if ($isimage) {
                $contentimages .= html_writer::tag('div',
                    html_writer::empty_tag('img', array('src' => $url)),
                    array('class' => 'courseimage'));
            } else {
                $image = $this->output->pix_icon(file_file_icon($file, 24), $file->get_filename(), 'moodle');
                $filename = html_writer::tag('span', $image, array('class' => 'fp-icon')) .
                    html_writer::tag('span', $file->get_filename(), array('class' => 'fp-filename'));
                $contentfiles .= html_writer::tag('span',
                    html_writer::link($url, $filename),
                    array('class' => 'coursefile fp-filename-icon'));
            }
        }
        return $contentimages . $contentfiles;
    }
}