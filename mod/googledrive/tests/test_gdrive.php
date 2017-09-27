<?php

require('../../config.php');
require_once('classes/googledrive.php');
use mod_url\googledrive;

//require_once($CFG->libdir . '/google/lib.php');
$gdrive = new googledrive(null);
if (!$gdrive->check_google_login()) {
    $googleauthlink = $gdrive->display_login_button();
    echo $googleauthlink;
}
