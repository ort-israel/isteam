<?php

/**
 * @param $USER
 * @return array
 */
function get_user_isteam_course() {
    global $USER;

    $usercourses = enrol_get_users_courses($USER->id);
    $useronlycoursearr = array_filter($usercourses, function ($obj) {
        //Tsofiya 03/09/2017: changed identifying isteam course to be by short name
        //return is_course_isteam($obj->idnumber);
        return is_course_isteam($obj->shortname);
    });
    $useronlycoursearr = array_values($useronlycoursearr);
    $useronlycourse = array_pop($useronlycoursearr);
    return $useronlycourse;
}


//Tsofiya 03/09/2017: check is course isteam by shortname
/*function is_course_isteam($coursenumber) {
$tmp = explode('-', $coursenumber);
return $tmp[0] === 'isteam';
}*/
function is_course_isteam($shortname) {
    $tmp = explode('-', $shortname);
    return $tmp[0] === 'iSTEAM';
}

function get_current_course($course) {
    global $DB, $SITE;
    if (!empty($course->id)) {
        if ($course->id != SITEID) {
            $course = $this->page->course;
        } else {
            $select = context_helper::get_preload_record_columns_sql('ctx');
            $sql = "SELECT c.*, $select
                          FROM {course} c
                          JOIN {context} ctx ON c.id = ctx.instanceid
                         WHERE c.id = :courseid AND ctx.contextlevel = :contextlevel";
            $params = array('courseid' => $course->id, 'contextlevel' => CONTEXT_COURSE);
            $course = $DB->get_record_sql($sql, $params, MUST_EXIST);
            context_helper::preload_from_record($course);
        }
    } else {
        //print_object($SITE);
        $course = $SITE;
    }
    return $course;
}

