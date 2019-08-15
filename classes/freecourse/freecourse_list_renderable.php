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
 * Renderable for free courses
 *
 * @package   theme_savoir
 * @copyright 2019 - Clément Jourdain (clement.jourdain@gmail.com) & Laurent David (laurent@call-learning.fr)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_savoir\freecourse;
defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot.'/lib/coursecatlib.php');

use renderable;
use renderer_base;
use templatable;

/**
 * Class containing data for my overview block.
 *
 * @copyright  2019 CALL Learning <laurent@call-learning.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class freecourse_list_renderable implements renderable, templatable {

    /**
     * Constructor.
     *
     */
    public function __construct() {
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     * This will export list of course sorted by category
     *
     * @param \renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        global $DB,$CFG;

        $selfenrolmentcourseid = $DB->get_fieldset_select('enrol','courseid',"enrol = 'guest' AND status=0");
        $context = new \stdClass();
        $context->coursesbycategory = []; // List of categories with associated courses

        foreach ($selfenrolmentcourseid as $cid) {
           $record = get_course($cid);
           $course = new course_in_list_extended($record);
            if ($course->is_uservisible()) {
                // Fetch only visible courses that have enrolment as guest.
                $categoryvisible = true; // Check that we can view the category of this course too
                if (!array_key_exists($course->category, $context->coursesbycategory)) {
                    // Create category object if it exists
                    $category = \coursecat::get($course->category);
                    $categoryinlist = new \stdClass();
                    $categoryinlist->name = $category->name;
                    $categoryinlist->courses = [];

                    $categoryvisible = $category->is_uservisible();
                    if ($categoryvisible) {
                        $context->coursesbycategory[$course->category] = $categoryinlist;
                    }
                }

                    if ($categoryvisible) {
                        $context->coursesbycategory[$course->category]->courses[] = $course;
                    }
            }
        }
        $context->coursesbycategory = array_values($context->coursesbycategory); // If we don't do that, Mustache
        // interpret this value as a non iterable
        // Then we sort it by alphabetical order

         usort($context->coursesbycategory, function($a, $b) {
            return  strcmp($a->name , $b->name);
         });
        return $context;
    }
}
