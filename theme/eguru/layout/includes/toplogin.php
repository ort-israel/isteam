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
 * @package   theme_eguru
 * @copyright 2015 Nephzat Dev Team,nephzat.com
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$identifieduser = isloggedin() && !isguestuser();
?>

<div class="custom-menu <?php echo $identifieduser ? 'identified-user' : 'anonymous-user' ?>">
    <?php if ($identifieduser) {
        if ($CFG->branch > "27") { ?>
            <ul>
                <li class="custom-menu-item login-item">
                    <?php echo $OUTPUT->user_menu(); ?>
                </li>
            </ul>
        <?php }
    } else { //if (!isloggedin() || isguestuser())
        ?>
        <ul>
            <li class="custom-menu-item login-item">
                <a class="login-link" href="<?php echo new moodle_url('/login/index.php'); ?>">
                    <?php echo get_string("login") . $guesttxt; ?></a>
            </li>
            <li class="custom-menu-item guest-course-item">
                <a class="guest-course-link" href="<?php echo new moodle_url(''); ?>">
                    <?php //echo get_string("join site"); ?>
                </a>
            </li>
        </ul>
        <?php
    }
    ?>
</div>