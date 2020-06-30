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
 * My Courses
 *
 * @package   theme_savoir
 * @copyright 2019 - Clément Jourdain (clement.jourdain@gmail.com) & Laurent David (laurent@call-learning.fr)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../../config.php');

require_login();

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/theme/savoir/pages/mycourses.php');
$PAGE->set_pagelayout('standard');

$PAGE->set_title(get_string('mycourses'));
$PAGE->set_heading(get_string('mycourses'));

echo $OUTPUT->header();

$renderable = new \block_savoir_mycourses\output\main('courses');
$renderer = $PAGE->get_renderer('core');
echo $renderer->render_from_template(
    'block_savoir_mycourses/timeline-view-courses', $renderable->export_for_template($renderer));

echo $OUTPUT->footer();
