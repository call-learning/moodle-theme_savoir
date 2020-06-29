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
 * TopColl Renderer override
 * Add the course syllabus/description at the top of the course
 *
 * @package   theme_savoir
 * @copyright 2019 - Clément Jourdain (clement.jourdain@gmail.com) & Laurent David (laurent@call-learning.fr)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use theme_savoir\format_renderer_trait;

defined('MOODLE_INTERNAL') || die;

if (file_exists("$CFG->dirroot/course/format/topcoll/renderer.php")) {
    include_once($CFG->dirroot . "/course/format/topcoll/renderer.php");

    class theme_savoir_format_topcoll_renderer extends format_topcoll_renderer {
        use format_renderer_trait;

        /**
         * Output the html for a multiple section page and make sure we output the section 0
         * as a syllabus and make sure we display the course summary or th section sumary if it already
         * exists
         *
         * @param stdClass $course The course entry from DB
         * @param array $sections (argument not used)
         * @param array $mods (argument not used)
         * @param array $modnames (argument not used)
         * @param array $modnamesused (argument not used)
         */
        public function print_multiple_section_page($course, $sections, $mods, $modnames, $modnamesused) {
            $modinfo = get_fast_modinfo($course);
            $course = $this->courseformat->get_course();
            if (empty($this->tcsettings)) {
                $this->tcsettings = $this->courseformat->get_settings();
            }

            $context = context_course::instance($course->id);
            // Title with completion help icon.
            $completioninfo = new completion_info($course);
            echo $completioninfo->display_help_icon();
            echo $this->output->heading($this->page_title(), 2, 'accesshide');

            // Copy activity clipboard..
            echo $this->course_activity_clipboard($course, 0);

            // Now the list of sections..
            if ($this->formatresponsive) {
                $this->tccolumnwidth = 100; // Reset to default.
            }
            echo $this->start_section_list();

            $sections = $modinfo->get_section_info_all();
            // General section if non-empty.
            $thissection = $sections[0];
            unset($sections[0]);
            // SAVOIR: Always display Section 0.
            echo $this->get_section_0_content($thissection);
            // END SAVOIR.
            $shownonetoggle = false;
            $coursenumsections = $this->courseformat->get_last_section_number();
            if ($coursenumsections > 0) {
                $sectiondisplayarray = array();
                if ($coursenumsections > 1) {
                    if (($this->userisediting) || ($this->tcsettings['onesection'] == 1)) {
                        // Collapsed Topics all toggles.
                        echo $this->toggle_all();
                    }
                    if ($this->tcsettings['displayinstructions'] == 2) {
                        // Collapsed Topics instructions.
                        echo $this->display_instructions();
                    }
                }
                $currentsectionfirst = false;
                if (($this->tcsettings['layoutstructure'] == 4) && (!$this->userisediting)) {
                    $currentsectionfirst = true;
                }

                if (($this->tcsettings['layoutstructure'] != 3) || ($this->userisediting)) {
                    $section = 1;
                } else {
                    $timenow = time();
                    $weekofseconds = 604800;
                    $course->enddate = $course->startdate + ($weekofseconds * $coursenumsections);
                    $section = $coursenumsections;
                    $weekdate = $course->enddate;      // This should be 0:00 Monday of that week.
                    $weekdate -= 7200;                 // Subtract two hours to avoid possible DST problems.
                }

                $numsections = $coursenumsections; // Because we want to manipulate this for column breakpoints.
                if (($this->tcsettings['layoutstructure'] == 3) && ($this->userisediting == false)) {
                    $loopsection = 1;
                    $numsections = 0;
                    while ($loopsection <= $coursenumsections) {
                        $nextweekdate = $weekdate - ($weekofseconds);
                        if ((($thissection->uservisible ||
                                    ($thissection->visible && !$thissection->available &&
                                        !empty($thissection->availableinfo))) &&
                                ($nextweekdate <= $timenow)) == true) {
                            $numsections++; // Section not shown so do not count in columns calculation.
                        }
                        $weekdate = $nextweekdate;
                        $section--;
                        $loopsection++;
                    }
                    // Reset.
                    $section = $coursenumsections;
                    $weekdate = $course->enddate;      // This should be 0:00 Monday of that week.
                    $weekdate -= 7200;                 // Subtract two hours to avoid possible DST problems.
                }

                if ($numsections < $this->tcsettings['layoutcolumns']) {
                    $this->tcsettings['layoutcolumns'] = $numsections;  // Help to ensure a reasonable display.
                }
                if (($this->tcsettings['layoutcolumns'] > 1) && ($this->mobiletheme === false)) {
                    if ($this->tcsettings['layoutcolumns'] > 4) {
                        // Default in config.php (and reset in database) or database has been changed incorrectly.
                        $this->tcsettings['layoutcolumns'] = 4;

                        // Update....
                        $this->courseformat->update_topcoll_columns_setting($this->tcsettings['layoutcolumns']);
                    }

                    if (($this->tablettheme === true) && ($this->tcsettings['layoutcolumns'] > 2)) {
                        // Use a maximum of 2 for tablets.
                        $this->tcsettings['layoutcolumns'] = 2;
                    }

                    if ($this->formatresponsive) {
                        $this->tccolumnwidth = 100 / $this->tcsettings['layoutcolumns'];
                        if ($this->tcsettings['layoutcolumnorientation'] == 2) { // Horizontal column layout.
                            $this->tccolumnwidth -= 0.5;
                            $this->tccolumnpadding = 0; // In 'px'.
                        } else {
                            $this->tccolumnwidth -= 0.2;
                            $this->tccolumnpadding = 0; // In 'px'.
                        }
                    }
                } else if ($this->tcsettings['layoutcolumns'] < 1) {
                    // Distributed default in plugin settings (and reset in database) or database has been changed incorrectly.
                    $this->tcsettings['layoutcolumns'] = 1;

                    // Update....
                    $this->courseformat->update_topcoll_columns_setting($this->tcsettings['layoutcolumns']);
                }

                echo $this->end_section_list();
                if ((!$this->formatresponsive) && ($this->tcsettings['layoutcolumnorientation'] == 1)) { // Vertical columns.
                    echo html_writer::start_tag('div', array('class' => $this->get_row_class()));
                }
                echo $this->start_toggle_section_list();

                $loopsection = 1;
                $breaking = false; // Once the first section is shown we can decide if we break on another column.

                while ($loopsection <= $coursenumsections) {
                    if (($this->tcsettings['layoutstructure'] == 3) && ($this->userisediting == false)) {
                        $nextweekdate = $weekdate - ($weekofseconds);
                    }
                    $thissection = $modinfo->get_section_info($section);

                    /* Show the section if the user is permitted to access it, OR if it's not available
                      but there is some available info text which explains the reason & should display. */
                    if (($this->tcsettings['layoutstructure'] != 3) || ($this->userisediting)) {
                        $showsection = $thissection->uservisible ||
                            ($thissection->visible && !$thissection->available && !empty($thissection->availableinfo));
                    } else {
                        $showsection = ($thissection->uservisible ||
                                ($thissection->visible && !$thissection->available &&
                                    !empty($thissection->availableinfo))) &&
                            ($nextweekdate <= $timenow);
                    }
                    if (($currentsectionfirst == true) && ($showsection == true)) {
                        // Show the section if we were meant to and it is the current section:....
                        $showsection = ($course->marker == $section);
                    } else if (($this->tcsettings['layoutstructure'] == 4) &&
                        ($course->marker == $section) && (!$this->userisediting)) {
                        $showsection = false; // Do not reshow current section.
                    }
                    if (!$showsection) {
                        // Hidden section message is overridden by 'unavailable' control.
                        $testhidden = false;
                        if ($this->tcsettings['layoutstructure'] != 4) {
                            if (($this->tcsettings['layoutstructure'] != 3) || ($this->userisediting)) {
                                $testhidden = true;
                            } else if ($nextweekdate <= $timenow) {
                                $testhidden = true;
                            }
                        } else {
                            if (($currentsectionfirst == true) && ($course->marker == $section)) {
                                $testhidden = true;
                            } else if (($currentsectionfirst == false) && ($course->marker != $section)) {
                                $testhidden = true;
                            }
                        }
                        if ($testhidden) {
                            if (!$course->hiddensections && $thissection->available) {
                                $thissection->ishidden = true;
                                $sectiondisplayarray[] = $thissection;
                            }
                        }
                    } else {
                        if ($this->isoldtogglepreference == true) {
                            $togglestate = substr($this->togglelib->get_toggles(), $section, 1);
                            if ($togglestate == '1') {
                                $thissection->toggle = true;
                            } else {
                                $thissection->toggle = false;
                            }
                        } else {
                            $thissection->toggle = $this->togglelib->get_toggle_state($thissection->section);
                        }

                        if ($this->courseformat->is_section_current($thissection)) {
                            $this->currentsection = $thissection->section;
                            $thissection->toggle = true; // Open current section regardless of toggle state.
                            $this->togglelib->set_toggle_state($thissection->section, true);
                        }

                        $thissection->isshown = true;
                        $sectiondisplayarray[] = $thissection;
                    }

                    if (($this->tcsettings['layoutstructure'] != 3) || ($this->userisediting)) {
                        $section++;
                    } else {
                        $section--;
                        if (($this->tcsettings['layoutstructure'] == 3) && ($this->userisediting == false)) {
                            $weekdate = $nextweekdate;
                        }
                    }

                    $loopsection++;
                    if (($currentsectionfirst == true) && ($loopsection > $coursenumsections)) {
                        // Now show the rest.
                        $currentsectionfirst = false;
                        $loopsection = 1;
                        $section = 1;
                    }
                    if ($section > $coursenumsections) {
                        // Activities inside this section are 'orphaned', this section will be printed as 'stealth' below.
                        break;
                    }
                }

                $canbreak = ($this->tcsettings['layoutcolumns'] > 1);
                $columncount = 1;
                $breakpoint = 0;
                $shownsectioncount = 0;
                if ((!$this->userisediting) && ($this->tcsettings['onesection'] == 2) && (!empty($this->currentsection))) {
                    $shownonetoggle = $this->currentsection; // One toggle open only, so as we have a current section it will be it.
                }
                foreach ($sectiondisplayarray as $thissection) {
                    $shownsectioncount++;

                    if (!empty($thissection->ishidden)) {
                        echo $this->section_hidden($thissection);
                    } else if (!empty($thissection->issummary)) {
                        echo $this->section_summary($thissection, $course, null);
                    } else if (!empty($thissection->isshown)) {
                        if ((!$this->userisediting) && ($this->tcsettings['onesection'] == 2)) {
                            if ($thissection->toggle) {
                                if (!empty($shownonetoggle)) {
                                    // Make sure the current section is not closed if set above.
                                    if ($shownonetoggle != $thissection->section) {
                                        // There is already a toggle open so others need to be closed.
                                        $thissection->toggle = false;
                                        $this->togglelib->set_toggle_state($thissection->section, false);
                                    }
                                } else {
                                    // No open toggle, so as this is the first, it can be the one.
                                    $shownonetoggle = $thissection->section;
                                }
                            }
                        }
                        echo $this->section_header($thissection, $course, false, 0);
                        if ($thissection->uservisible) {
                            echo $this->courserenderer->course_section_cm_list($course, $thissection, 0);
                            echo $this->courserenderer->course_section_add_cm_control($course, $thissection->section, 0);
                        }
                        echo html_writer::end_tag('div');
                        echo $this->section_footer();
                    }

                    // Only check for breaking up the structure with rows if more than one column
                    // and when we output all of the sections.
                    if ($canbreak === true) {
                        // Only break in non-mobile themes or using a responsive theme.
                        if ((!$this->formatresponsive) || ($this->mobiletheme === false)) {
                            if ($this->tcsettings['layoutcolumnorientation'] == 1) {  // Vertical mode.
                                // This is not perfect yet as does not tally the shown sections and divide by columns.
                                if (($breaking == false) && ($showsection == true)) {
                                    $breaking = true;
                                    // Divide the number of sections by the number of columns.
                                    $breakpoint = $numsections / $this->tcsettings['layoutcolumns'];
                                }

                                if (($breaking == true) && ($shownsectioncount >= $breakpoint) &&
                                    ($columncount < $this->tcsettings['layoutcolumns'])) {
                                    echo $this->end_section_list();
                                    echo $this->start_toggle_section_list();
                                    $columncount++;
                                    // Next breakpoint is...
                                    $breakpoint += $numsections / $this->tcsettings['layoutcolumns'];
                                }
                            } else {  // Horizontal mode.
                                if (($breaking == false) && ($showsection == true)) {
                                    $breaking = true;
                                    // The lowest value here for layoutcolumns is 2
                                    // and the maximum for shownsectioncount is 2, so...
                                    $breakpoint = $this->tcsettings['layoutcolumns'];
                                }

                                if (($breaking == true) && ($shownsectioncount >= $breakpoint)) {
                                    echo $this->end_section_list();
                                    echo $this->start_toggle_section_list();
                                    // Next breakpoint is...
                                    $breakpoint += $this->tcsettings['layoutcolumns'];
                                }
                            }
                        }
                    }

                    unset($sections[$thissection->section]);
                }
            }

            $changenumsections = '';
            if ($this->userisediting and has_capability('moodle/course:update', $context)) {
                // Print stealth sections if present.
                foreach ($modinfo->get_section_info_all() as $section => $thissection) {
                    if ($section <= $coursenumsections or empty($modinfo->sections[$section])) {
                        // This is not stealth section or it is empty.
                        continue;
                    }
                    echo $this->stealth_section_header($section);
                    echo $this->courserenderer->course_section_cm_list($course, $thissection->section, 0);
                    echo $this->stealth_section_footer();
                }

                $changenumsections = $this->change_number_sections($course, 0);
            }
            echo $this->end_section_list();
            if ($coursenumsections > 0) {
                if ((!$this->formatresponsive) && ($this->tcsettings['layoutcolumnorientation'] == 1)) { // Vertical columns.
                    echo html_writer::end_tag('div');
                }
            }

            echo $changenumsections;

            // Now initialise the JavaScript.
            $toggles = $this->togglelib->get_toggles();
            $this->page->requires->js_init_call('M.format_topcoll.init', array(
                $course->id,
                $toggles,
                $coursenumsections,
                $this->defaulttogglepersistence,
                $this->defaultuserpreference,
                ((!$this->userisediting) && ($this->tcsettings['onesection'] == 2)),
                $shownonetoggle,
                $this->userisediting));
            // Make sure the database has the correct state of the toggles if changed by the code.
            // This ensures that a no-change page reload is correct.
            set_user_preference('topcoll_toggle_' . $course->id, $toggles);
        }
    }
}