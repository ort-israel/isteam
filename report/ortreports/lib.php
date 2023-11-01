<?php
/**
 * Add nodes to myprofile page.
 *
 * @param \core_user\output\myprofile\tree $tree Tree object
 * @param stdClass $user user object
 * @param bool $iscurrentuser
 * @param stdClass $course Course object
 *
 * @return bool
 */
function report_ortreports_myprofile_navigation(core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {
    global $CFG;
    if (empty($course)) {
        // We want to display these reports under the site context.
        $course = get_fast_modinfo(SITEID)->get_course();
    }

    require_once($CFG->libdir . "/ortutils.php");

    $useronlycourse = get_user_isteam_course();

    /*$myaoublog = new stdClass();
    $myaoublog->itemtype = 'link';
    $myaoublog->title = get_string("projectdiary", "theme_eguru");
    $myaoublog->url = new moodle_url('/mod/oublog/index.php', array('id' => $useronlycourse->id));
    $myaoublog->pix = "i/course";*/

    // Link to the iSTEAM course assignments page.
    $url = new moodle_url('/mod/assign/index.php', array('id' => $useronlycourse->id));
    $node = new core_user\output\myprofile\node('reports', 'ortreportsassignment',
        get_string('ortreportassignments', 'report_ortreports'), null, $url);
    $tree->add_node($node);

    // Link to the iSTEAM oublog page.
    $url = new moodle_url('/mod/oublog/index.php', array('id' => $useronlycourse->id));
    $node = new core_user\output\myprofile\node('reports', 'ortreportsblogs',
        get_string('ortreportblogs', 'report_ortreports'), null, $url);
    $tree->add_node($node);
    return true;
}