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

namespace theme_eguru\output;

use html_writer;
use custom_menu;
use moodle_url;
use context_course;
use theme_config;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . "/course/renderer.php");
require_once($CFG->libdir . '/coursecatlib.php');

/**
 * Renderers to align Moodle's HTML with that expected by Bootstrap
 *
 * @package    theme_aviv2018
 * @copyright  2012 Bas Brands, www.basbrands.nl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class core_renderer extends \theme_bootstrapbase_core_renderer {

    public function standard_head_html() {
        global $SITE, $PAGE;

        $output = parent::standard_head_html();
        $output .= "<link href=\"https://fonts.googleapis.com/css?family=Assistant:400,700,800&amp;subset=hebrew\" rel=\"stylesheet\">\n";
        return $output;
    }

}
