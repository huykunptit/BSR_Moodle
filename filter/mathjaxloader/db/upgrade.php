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
 * MathJAX filter upgrade code.
 *
 * @package    filter_mathjaxloader
 * @copyright  2014 Damyon Wiese (damyon@moodle.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_filter_mathjaxloader_upgrade($oldversion) {
    global $CFG;

    // Automatically generated Moodle v3.9.0 release upgrade line.
    // Put any upgrade step following this.

    if ($oldversion < 2021052501) {
        // Update CDN url.
        $originalurl =  $CFG->wwwroot . '/lib/mathjax/MathJax.js';
        $newurl = $CFG->wwwroot . '/lib/mathjax/MathJax.js';
        $currenturl = get_config('filter_mathjaxloader', 'httpsurl');
        if ($currenturl == $originalurl) {
            set_config('httpsurl', $newurl, 'filter_mathjaxloader');
        }

        upgrade_plugin_savepoint(true, 2021052501, 'filter', 'mathjaxloader');
    }

    // Automatically generated Moodle v4.0.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v4.1.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v4.2.0 release upgrade line.
    // Put any upgrade step following this.

    return true;
}
