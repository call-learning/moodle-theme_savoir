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
 * Event observers for Theme Savoir
 *
 * @package   theme_savoir
 * @copyright 2019 - Clément Jourdain (clement.jourdain@gmail.com) & Laurent David (laurent@call-learning.fr)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\event\course_created;
use core\event\user_loggedin;
use theme_savoir\utils;

defined('MOODLE_INTERNAL') || die();

/**
 * Event observer for Theme Savoir
 * @copyright 2019 - Clément Jourdain (clement.jourdain@gmail.com) & Laurent David (laurent@call-learning.fr)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class theme_savoir_observer {

    /**
     * Make sure that when user logs in the nav bar is open
     *
     * @param user_loggedin $event
     * @throws moodle_exception
     */
    public static function user_loggedin(user_loggedin $event) {
        set_user_preference('drawer-open-nav', 'true');
    }

    /**
     * Add syllabus when creating a new course
     * @param course_created $event
     * @throws dml_exception
     */
    public static function course_set_syllabus(course_created $event) {
        global $DB;
        $courseid = $event->objectid;
        $courserecord = $DB->get_record('course', array('id' => $courseid));
        if (empty($courserecord->summary)) {
            utils::set_course_syllabus($courserecord);
        }
    }
}
