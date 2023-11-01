<?php
$bodyclasses = array();
$userrole = ' role-teacher';
$isstudent = false;
$userroles = get_user_roles($PAGE->context, $USER->id, true);
foreach ($userroles as $role) {
    if ($role->roleid == 5) $isstudent = true;
}
if ($isstudent) {
    $userrole = ' role-student';
}
if (has_capability('moodle/site:config', context_system::instance())) {
    $userrole = ' role-admin';
}
array_push($bodyclasses, $userrole);
