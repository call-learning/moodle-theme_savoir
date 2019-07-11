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
 * Utility class
 *
 * @package   theme_savoir
 * @copyright 2019 - Clément Jourdain (clement.jourdain@gmail.com) & Laurent David (laurent@call-learning.fr)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_savoir;

defined('MOODLE_INTERNAL') || die();

use html_writer;
use moodle_url;
/**
 * Event observer for Theme Savoir
 */
class utils {
    public static function get_course_contact_list($course_in_list, $displaybyrole=true, $ishtml=true) {

        $content = '';
        $contacts = $course_in_list->get_course_contacts();
        $contactbyroles = array_reduce($contacts,
                function($c, $i) {
                    if (empty($c[$i['rolename']])) {
                        $c[$i['rolename']] = [];
                    }
                    $c[$i['rolename']][] = $i;
                    return $c;
                }, array());

        foreach ($contactbyroles as $rolename => $contactlist) {
            $names = [];
            foreach($contactlist as $coursecontact) {
                $currentnameprint = $coursecontact['username'];
                if ($ishtml) {
                    $names[] =
                            html_writer::link(new moodle_url('/user/view.php',
                                    array('id' => $coursecontact['user']->id, 'course' => SITEID)),
                                    $currentnameprint);
                } else {
                    $names[] = $currentnameprint;
                }
            }
            if (count($names)>1) {
                $rolename.='s';
            }
            if ($displaybyrole) {
                $roleprint = $rolename . ': ' . implode(', ', $names);
                if ($ishtml) {
                    $content .= html_writer::tag(
                            'span',
                            $roleprint,
                            array('class' => 'teachers-list'));
                } else {
                    $content .=  $roleprint;
                }
            } else {
                if ($content) $content.= ', ';
                $content .= implode(', ', $names);
            }

        }
        return $content;
    }
}