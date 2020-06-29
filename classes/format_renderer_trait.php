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
 * Course renderer common routines for all formats
 *
 * @package   theme_savoir
 * @copyright 2019 - Clément Jourdain (clement.jourdain@gmail.com) & Laurent David (laurent@call-learning.fr)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_savoir;

use context_course;
use html_writer;
use moodle_url;
use stdClass;

defined('MOODLE_INTERNAL') || die;

trait format_renderer_trait {
    public function get_section_0_content($section) {
        $tcontext = new stdClass();
        $course = $section->modinfo->get_course();
        $tcontext->courseid = $course->id;
        $tcontext->syllabustitle = get_string('coursesyllabustitle', 'theme_savoir');
        if ($section->summary || $section->name) {
            $tcontext->content = parent::format_summary_text($section);
            $tcontext->content = $this->section_header($section, $course, false, 0);

        } else {
            global $CFG, $PAGE;
            $isuserediting = $PAGE->user_is_editing();

            require_once($CFG->libdir . '/filelib.php');
            $context = context_course::instance($course->id);
            $summary = file_rewrite_pluginfile_urls($course->summary, 'pluginfile.php', $context->id, 'course', 'summary', null);
            $summary = format_text($summary, $course->summaryformat, [], $course->id);

            if (!empty($section->name) && $section->name !== '') {
                $tcontext->syllabustitle = $section->name;
            }

            $liattributes = array(
                'id' => 'section-' . $section->section,
                'class' => 'section main clearfix',
                'role' => 'region',
                'aria-label' => $tcontext->syllabustitle
            );

            $tcontext->content = html_writer::start_tag('li', $liattributes);

            if ($isuserediting) {
                $leftcontent = $this->section_left_content($section, $course, false);
                $rightcontent = $this->section_right_content($section, $course, false);
                $tcontext->content .= html_writer::tag('div', $leftcontent, array('class' => 'left side'));
                $tcontext->content .= html_writer::tag('div', $rightcontent, array('class' => 'right side'));
            }
            $tcontext->content .= html_writer::start_tag('div', array('class' => 'content'));

            $tcontext->content .= $this->section_availability($section);
            $tcontext->content .= html_writer::start_tag('div', array('class' => 'summary'));
            $tcontext->content .= $summary;

            if ($isuserediting && has_capability('moodle/course:update', $context)) {
                $url = new moodle_url($CFG->wwwroot . '/course/edit.php?', array('id' => $course->id));
                $tcontext->content .= html_writer::link($url,
                    $this->output->pix_icon('t/edit', get_string('edit')),
                    array('title' => get_string('editsection', 'moodle'))
                );
            }
            $tcontext->content .= html_writer::end_tag('div');
        }
        $tcontext->content .= $this->courserenderer->course_section_cm_list($course, $section, 0);
        $tcontext->content .= $this->courserenderer->course_section_add_cm_control($course, 0, 0);
        $tcontext->content .= $this->section_footer();
        $tcontext->expanded = true;
        $tcontext->csclosedstatus = "";
        $tcontext->coursesyllabusprefname = 'coursesSyllabusStatus';

        if (isloggedin() && !isguestuser()) {
            user_preference_allow_ajax_update($tcontext->coursesyllabusprefname, PARAM_RAW);
            if (get_user_preferences($tcontext->coursesyllabusprefname)) {
                $tcontext->csclosedstatus = get_user_preferences($tcontext->coursesyllabusprefname);
                $currentstatus = explode(',', $tcontext->csclosedstatus);
                $tcontext->expanded = !in_array($course->id, $currentstatus);

            }
        } else {
            // Don't store anything on user prefs.
            $tcontext->coursesyllabusprefname = '';
        }
        return $this->render_from_template('theme_savoir/course_syllabus', $tcontext);
    }
}

