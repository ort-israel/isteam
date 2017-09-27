<?php

require('../../config.php');

use \mod_googledrive\googledrive;

require_login();

/// Parameters
$cmid   = required_param('cmid', PARAM_INT); // Repository ID

/// Headers to make it not cacheable
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

/// Wait as long as it takes for this script to finish
core_php_time_limit::raise();

$gdrive = new googledrive($cmid);
$gdrive->callback();
$strauthenticated = get_string('gdrivepermissionsauthenticated', 'googledrive');
$strclose = get_string('close', 'quiz');
echo "<h2 style='text-align: center;'>$strauthenticated</h2>";
echo "<div style='text-align: center;'><button onclick='window.close();'>$strclose</button></div>";
// Auto close the window, in 5sec.
echo "<script type='text/javascript'><!--
setTimeout('self.close()',5000);
--></script>";