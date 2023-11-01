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
 * This is built using the bootstrapbase template to allow for new theme's using
 * Moodle's new Bootstrap theme engine
 *
 * @package     theme_eguru
 * @copyright   2015 Nephzat Dev Team , nephzat.com
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

$THEME->name = 'eguru';
$THEME->doctype = 'html5';
$THEME->parents = array('bootstrapbase');
$THEME->sheets = array('eguru', 'font-awesome', 'ort');
$THEME->sheets[] = 'custom';
$THEME->supportscssoptimisation = false;
$THEME->yuicssmodules = array();
$THEME->enable_dock = true;
$THEME->editor_sheets = array();

$no = get_config('theme_eguru', 'patternselect'); // Selected theme pattern no returned.

if ($no) {
    $THEME->sheets[] = 'color_scheme-' . $no;
} else {
    $THEME->sheets[] = 'color_scheme-default';
}

$THEME->rendererfactory = 'theme_overridden_renderer_factory';
$THEME->csspostprocess = 'theme_eguru_process_css';

$THEME->layouts = array(
    // The site home page.
    'frontpage' => array(
        'file' => 'frontpage.php', /* Lea 2016/02 - show ort customized front page*/
        //'regions' => array(''), //'regions' => array('side-pre'), /* Tsofiya: 17/08/2017: remove region, causing problems */
        'defaultregion' => '', //'defaultregion' => 'side-pre',
        'options' => array('nonavbar' => true),
    ),
    'login' => array(
        'file' => 'login.php',
        'regions' => array(),
        'options' => array('langmenu' => true),
    ),
    // My dashboard page.
    'mydashboard' => array(
        'file' => 'columns3mydashboard.php',
        'regions' => array('side-pre', 'side-post'),
        'defaultregion' => 'side-pre',
        'options' => array('langmenu' => true),
    ),
);

$THEME->blockrtlmanipulations = array(
    'side-pre' => 'side-post',
    'side-post' => 'side-pre'
);