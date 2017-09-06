<?php

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/course/format/grid/renderer.php');

class eguru_format_grid_renderer extends format_grid_renderer {


    /**
     * Output the html for a multiple section page
     *
     * @param stdClass $course The course entry from DB
     * @param array $sections The course_sections entries from the DB
     * @param array $mods
     * @param array $modnames
     * @param array $modnamesused
     */
    /*public function print_multiple_section_page($course, $sections, $mods, $modnames, $modnamesused) {
        global $PAGE;
        $summarystatus = $this->courseformat->get_summary_visibility($course->id);
        $context = context_course::instance($course->id);
        $editing = $PAGE->user_is_editing();
        $hascapvishidsect = has_capability('moodle/course:viewhiddensections', $context);

        if ($editing) {
            $streditsummary = get_string('editsummary');
            $urlpicedit = $this->output->pix_url('t/edit');
        } else {
            $urlpicedit = false;
            $streditsummary = '';
        }

        echo html_writer::start_tag('div', array('id' => 'gridmiddle-column'));
        echo $this->output->skip_link_target();

        $modinfo = get_fast_modinfo($course);
        $sections = $modinfo->get_section_info_all();

        // Start at 1 to skip the summary block or include the summary block if it's in the grid display.
        $this->topic0_at_top = $summarystatus->showsummary == 1;
        if ($this->topic0_at_top) {
            $this->topic0_at_top = $this->make_block_topic0($course, $sections, $modinfo, $editing, $urlpicedit,
                $streditsummary, false);
            // For the purpose of the grid shade box shown array topic 0 is not shown.
            $this->shadeboxshownarray[0] = 1;
        }
        echo html_writer::start_tag('div', array('id' => 'gridiconcontainer', 'role' => 'navigation',
            'aria-label' => get_string('gridimagecontainer', 'format_grid')));
        echo html_writer::start_tag('ul', array('class' => 'gridicons'));
        // Print all of the image containers.
        $this->make_block_icon_topics($context->id, $modinfo, $course, $editing, $hascapvishidsect, $urlpicedit);
        echo html_writer::end_tag('ul');
        echo html_writer::end_tag('div');
        echo html_writer::start_tag('div', array('id' => 'gridshadebox'));
        echo html_writer::tag('div', '', array('id' => 'gridshadebox_overlay', 'style' => 'display: none;'));

        $gridshadeboxcontentclasses = array('hide_content');
        if (!$editing) {
            if ($this->settings['fitsectioncontainertowindow'] == 2) {
                $gridshadeboxcontentclasses[] = 'fit_to_window';
            } else {
                $gridshadeboxcontentclasses[] = 'absolute';
            }
        }

        echo html_writer::start_tag('div', array('id' => 'gridshadebox_content', 'class' => implode(' ', $gridshadeboxcontentclasses),
            'role' => 'region',
            'aria-label' => get_string('shadeboxcontent', 'format_grid')));

        $deviceextra = '';
        switch ($this->portable) {
            case 1: // Mobile.
                $deviceextra = ' gridshadebox_mobile';
                break;
            case 2: // Tablet.
                $deviceextra = ' gridshadebox_tablet';
                break;
            default:
                break;
        }
        echo html_writer::tag('img', '', array('id' => 'gridshadebox_close', 'style' => 'display: none;',
            'class' => $deviceextra,
            'src' => $this->output->pix_url('close', 'format_grid'),
            'role' => 'link',
            'aria-label' => get_string('closeshadebox', 'format_grid')));

        // Only show the arrows if there is more than one box shown.
        if (($course->numsections > 1) || (($course->numsections == 1) && (!$this->topic0_at_top))) {
            echo html_writer::start_tag('div', array('id' => 'gridshadebox_left',
                'class' => 'gridshadebox_area gridshadebox_left_area',
                'style' => 'display: none;',
                'role' => 'link',
                'aria-label' => get_string('previoussection', 'format_grid')));
            echo html_writer::tag('img', '', array('class' => 'gridshadebox_arrow gridshadebox_left'.$deviceextra,
                'src' => $this->output->pix_url('fa-arrow-circle-left-w', 'format_grid')));
            echo html_writer::end_tag('div');
            echo html_writer::start_tag('div', array('id' => 'gridshadebox_right',
                'class' => 'gridshadebox_area gridshadebox_right_area',
                'style' => 'display: none;',
                'role' => 'link',
                'aria-label' => get_string('nextsection', 'format_grid')));
            echo html_writer::tag('img', '', array('class' => 'gridshadebox_arrow gridshadebox_right'.$deviceextra,
                'src' => $this->output->pix_url('fa-arrow-circle-right-w', 'format_grid')));
            echo html_writer::end_tag('div');
        }

        echo $this->start_section_list();
        // If currently moving a file then show the current clipboard.
        $this->make_block_show_clipboard_if_file_moving($course);

        // Print Section 0 with general activities.
        if (!$this->topic0_at_top) {
            $this->make_block_topic0($course, $sections, $modinfo, $editing, $urlpicedit, $streditsummary, false);
        }

        // Now all the normal modules by topic.
        // Everything below uses "section" terminology - each "section" is a topic/module.
        $this->make_block_topics($course, $sections, $modinfo, $editing, $hascapvishidsect, $streditsummary,
            $urlpicedit, false);

        echo html_writer::end_tag('div');
        echo html_writer::end_tag('div');
        echo html_writer::tag('div', '&nbsp;', array('class' => 'clearer'));
        echo html_writer::end_tag('div');

        $sectionredirect = null;
        if ($course->coursedisplay == COURSE_DISPLAY_MULTIPAGE) {
            // Get the redirect URL prefix for keyboard control with the 'Show one section per page' layout.
            $sectionredirect = $this->courseformat->get_view_url(null)->out(true);
        }

        // Initialise the shade box functionality:...
        $PAGE->requires->js_init_call('M.format_grid.init', array(
            $PAGE->user_is_editing(),
            $sectionredirect,
            $course->numsections,
            json_encode($this->shadeboxshownarray)));
        // Initialise the key control functionality...
        $PAGE->requires->yui_module('moodle-format_grid-gridkeys', 'M.format_grid.gridkeys.init', null, null, true);
    }*/

    private function make_block_topic0($course, $sections, $modinfo, $editing, $urlpicedit, $streditsummary,
                                       $onsectionpage) {
        return '555';
    }
}