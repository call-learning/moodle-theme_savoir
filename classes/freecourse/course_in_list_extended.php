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
 * Utility for Freecourse renderable
 *
 * @package   theme_savoir
 * @copyright 2019 - Clément Jourdain (clement.jourdain@gmail.com) & Laurent David (laurent@call-learning.fr)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_savoir\freecourse;
defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/lib/coursecatlib.php');

use context_helper;
use course_in_list;
use stdClass;
use moodle_url;
use theme_savoir\utils;

class course_in_list_extended extends course_in_list {

    /** @var an url to the course image url or false - stores result of call to init_course_image_url() */
    protected $courseimageurl = null;

    /** @var extra classes for this course display - stores result of call to init_course_image_url() */
    protected $classes = "";

    const COURSE_IMAGE_FILE_NAME_PREFIX = 'course_image';

    /**
     * Creates an instance of the class from record
     *
     * @param stdClass $record except fields from course table it may contain
     *     field hassummary indicating that summary field is not empty.
     *     Also it is recommended to have context fields here ready for
     *     context preloading
     */
    public function __construct(stdClass $record) {
        context_helper::preload_from_record($record);
        $this->record = new stdClass();
        foreach ($record as $key => $value) {
            $this->record->$key = $value;
        }
        $this->init_course_image_url(); // We do that so we can cache the result later.
    }

    /**
     * Returns an image from the course overview files that has got the right pattern
     *
     * @return an image url or false
     * @see course_in_list_extended::COURSE_IMAGE_FILE_NAME_PREFIX
     *
     */
    public function course_image_url() {
        return $this->courseimageurl;
    }

    /**
     * Returns an class from the course overview files that has got the right pattern
     *
     * @return a class or false
     *
     */
    public function classes() {
        return $this->classes;
    }

    /** Get view URL */
    public function view_url() {
        return new \moodle_url('/course/view.php', [
                'id' => $this->record->id,
        ]);
    }

    public function teachers_list() {
        return utils::get_course_contact_list($this, false, false);
    }
    /**
     * Initialise the courseimageurl field. This file should match the pattern defined by
     * course_in_list_extended::COURSE_IMAGE_FILE_NAME_PREFIX
     *
     * @return an image url or false
     */
    protected function init_course_image_url() {
        $files = $this->get_course_overviewfiles();
        $courseimageurl = false;
        foreach ($files as $f) {
            if (pathinfo($f->get_filename(), PATHINFO_FILENAME) == self::COURSE_IMAGE_FILE_NAME_PREFIX) {
                if ($isimage = $f->is_valid_image()) {
                    $courseimageurl = moodle_url::make_pluginfile_url(
                            $f->get_contextid(),
                            'course',
                            'overviewfiles',
                            null,
                            $f->get_filepath(),
                            $f->get_filename());
                    break;
                }
            }
        }
        // Use Geo pattern
        if (!$courseimageurl) {
            $pattern = new \core_geopattern();
            $pattern->setColor('#eeeeee');
            $pattern->patternbyid($this->record->id);
            $this->classes = 'coursepattern';
            $courseimageurl = $pattern->datauri();
        }
        $this->courseimageurl = $courseimageurl;
    }
}