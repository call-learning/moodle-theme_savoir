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
 * Open Catalog
 *
 * @package   theme_savoir
 * @copyright 2019 - Clément Jourdain (clement.jourdain@gmail.com) & Laurent David (laurent@call-learning.fr)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../../config.php');

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/theme/savoir/pages/opencatalog.php');
$PAGE->set_pagelayout('pagewithdescription');

$PAGE->set_title(get_string('opencatalog', 'theme_savoir'));
$PAGE->set_heading(get_string('opencatalog','theme_savoir'));
echo $OUTPUT->header();

$renderable = new theme_savoir\freecourse\freecourse_list_renderable();
$renderer = $PAGE->get_renderer('core');
echo $renderer->render_from_template(
        'theme_savoir/freecourse-list', $renderable->export_for_template($renderer));;

echo $OUTPUT->footer();
