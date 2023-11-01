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
 * Internal library of functions for module googledrive
 *
 * All the googledrive specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package    mod_googledrive
 * @copyright  2016 Nadav Kavalerchik <nadavkav@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/*
 * Does something really useful with the passed things
 *
 * @param array $things
 * @return object
 *function googledrive_do_something_useful(array $things) {
 *    return new stdClass();
 *}
 */

/**
 * This methods does weak url validation, we are looking for major problems only,
 * no strict RFE validation.
 *
 * @param $url
 * @return bool true is seems valid, false if definitely not valid URL
 */
function googledrive_appears_valid_url($url) {
    if (preg_match('/^(\/|https?:|ftp:)/i', $url)) {
        // note: this is not exact validation, we look for severely malformed URLs only
        return (bool)preg_match('/^[a-z]+:\/\/([^:@\s]+:[^@\s]+@)?[a-z0-9_\.\-]+(:[0-9]+)?(\/[^#]*)?(#.*)?$/i', $url);
    } else {
        return (bool)preg_match('/^[a-z]+:\/\/...*$/i', $url);
    }
}
