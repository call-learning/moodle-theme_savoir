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
 * Standard library functions for savoir theme.
 *
 * @package   theme_savoir
 * @copyright 2019 - Clément Jourdain (clement.jourdain@gmail.com) & Laurent David (laurent@call-learning.fr)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Global CSS Processor
 *
 * @param string $css
 * @param theme_config $theme
 * @return string
 */
function theme_savoir_get_main_scss_content(theme_config $theme) {
    global $CFG;

    $scss = '';
    $filename = !empty($theme->settings->preset) ? $theme->settings->preset : null;
    $fs = get_file_storage();

    $context = context_system::instance();
    if ($filename == 'default.scss') {
        // We still load the default preset files directly from the boost theme. No sense in duplicating them.
        $scss .= file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss');
    } else if ($filename == 'plain.scss') {
        // We still load the default preset files directly from the boost theme. No sense in duplicating them.
        $scss .= file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/plain.scss');

    } else if ($filename && ($presetfile = $fs->get_file($context->id, 'theme_savoir', 'preset', 0, '/', $filename))) {
        // This preset file was fetched from the file area for theme_savoir and not theme_boost (see the line above).
        $scss .= $presetfile->get_content();
    }
    // Pre CSS - this is loaded AFTER any prescss from the setting but before the main scss.
    $pre = file_get_contents($CFG->dirroot . '/theme/savoir/scss/pre.scss');
    // Post CSS - this is loaded AFTER the main scss but before the extra scss from the setting.
    $post = file_get_contents($CFG->dirroot . '/theme/savoir/scss/post.scss');

    // Combine them together.
    return $pre . "\n" . $scss . "\n" . $post;
}

/**
 * Process site branding changes.
 *
 * @throws Exception
 * @throws coding_exception
 * @throws dml_exception
 */
function theme_savoir_process_site_branding() {
    theme_reset_all_caches();
}

/**
 * CSS Processor
 *
 * @param string $css
 * @param theme_config $theme
 * @return string
 */
function theme_savoir_process_css($css, theme_config $theme) {
    global $OUTPUT;

    // Get URL for the coverimage front page
    $coverimagfpeurl = $theme->setting_file_url('coverimagefp', 'coverimagefp');
    if (!$coverimagfpeurl) {
        $coverimagefpurl = $OUTPUT->image_url('coverimagefp', 'theme');
    }

    $replacementimages = array(
            'coverimagefp' => "background-image: url($coverimagefpurl);",
    );
    foreach ($replacementimages as $type => $csscode) {
        $anchor = "/**setting:$type**/";
        $css = str_replace($anchor, $csscode, $css);
    }

    return $css;
}

/**
 * Get SCSS to prepend.
 *
 * @param theme_config $theme The theme config object.
 * @return array
 */
function theme_savoir_get_pre_scss($theme) {
    global $CFG;

    $scss = '';
    $configurable = [
        // Config key => [variableName, ...].
            'primarycolor' => ['primary'],
            'secondarycolor' => ['secondary'],
    ];

    // Prepend variables first.
    foreach ($configurable as $configkey => $targets) {
        $value = isset($theme->settings->{$configkey}) ? $theme->settings->{$configkey} : null;
        if (empty($value)) {
            continue;
        }
        array_map(function($target) use (&$scss, $value) {
            $scss .= '$' . $target . ': ' . $value . ";\n";
        }, (array) $targets);
    }

    return $scss;
}

/**
 * Serves any files associated with the theme settings.
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return bool
 */
function theme_savoir_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {

    // Check if the files to serve are in the usual setting file area
    $themesettingsfilearea = [
            'coverimagefp',
            'favicon'
    ];

    if ($context->contextlevel == CONTEXT_SYSTEM && in_array($filearea, $themesettingsfilearea)) {
        $theme = theme_config::load('savoir');
        return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
    } else {
        send_file_not_found();
    }
}

/**
 * Callback to add footer elements.
 *
 * @return string HTML footer content
 */
function theme_savoir_standard_footer_html() {
    $additionallinks = ['legal'];
    $output = '';

    foreach ($additionallinks as $adlink) {
        // TODO add static page or use existing pages.
        /*        $url = new moodle_url('/local/staticpage/view.php?page=' . $adlink);
                $output .= html_writer::div(html_writer::link($url, get_string($adlink, 'theme_savoir')), 'theme_savoir');
        */
    }
    return $output;
}

/**
 * Navigation
 */

require_once($CFG->libdir . '/navigationlib.php');

class savoir_flat_navigation extends flat_navigation {
    /**
     * Build the list of navigation nodes based on the current navigation and settings trees.
     *
     */
    public function initialise() {
        global $USER, $PAGE, $CFG;
        $isstudent = has_role_from_name($USER->id, 'student');
        $isteacher = has_role_from_name($USER->id, 'teacher')
                || has_role_from_name($USER->id, 'editingteacher')
                || has_role_from_name($USER->id, 'coursecreator');
        $isstaff = has_role_from_name($USER->id, 'manager');

        if (is_siteadmin() || $isteacher || $isstaff ) {
            parent::initialise();
            $this->add_help_node();
            if ($isteacher) {
                $this->remove('mycourses', navigation_node::NODETYPE_LEAF);
                foreach ($this->getIterator() as $node) {
                    if ($node->type == navigation_node::TYPE_COURSE) {
                        $this->remove($node->key, navigation_node::TYPE_COURSE);
                    }
                }
            }
            return;
        }

        $course = $PAGE->course;
        $this->page->navigation->initialise();
        $studentblocks = [
                [
                        'url' => '/my',
                        'label' => get_string('dashboardtitle', 'theme_savoir'),
                        'key' => 'myhome',
                        'icon' => array('name' => 'i/home', 'component' => 'moodle')
                ],
                [
                        'url' => '/theme/savoir/pages/mycourses.php',
                        'label' => get_string('mycourses'),
                        'key' => 'mycourses',
                        'icon' => array('name' => 'i/course', 'component' => 'moodle')
                ],
                [
                        'url' => '/course/index.php',
                        'label' => get_string('catalog', 'theme_savoir'),
                        'key' => 'catalog',
                        'icon' => array('name' => 'i/catalog', 'component' => 'theme_savoir')
                ]

        ];


        foreach ($studentblocks as $nl) {
            $navlink = navigation_node::create(
                    $nl['label'],
                    new moodle_url($nl['url']),
                    navigation_node::TYPE_CUSTOM,
                    null,
                    $nl['key'],
                    new pix_icon(
                            $nl['icon']['name'],
                            '',
                            $nl['icon']['component']));
            $flat = new flat_navigation_node($navlink, 0);
            $this->add($flat);

        }
        $this->add_help_node($isstudent, $isteacher, $isstaff);
        $this->add_other_nodes($isstudent, $isteacher, $isstaff);
    }

    protected function add_help_node($istudent = true, $isteacher = true, $isstaff = true) {
        global $CFG;
        $studentcourseid = get_config('theme_savoir', 'studenthelpcourse');
        $studentcourseid = $studentcourseid ? $studentcourseid : SITEID;
        $studentguideurl = new moodle_url($CFG->wwwroot.'/course/view.php', array('id' => $studentcourseid));
        $staffcourseid = get_config('theme_savoir', 'staffhelpcourse');
        $staffcourseid = $staffcourseid ? $staffcourseid : SITEID;
        $staffguideurl = new moodle_url($CFG->wwwroot.'/course/view.php', array('id' => $staffcourseid));

        /* Here we should add a choice of help courses (two for teachers/admin, one for student, depending on the extend of this
        user's roles */

        $navlink = navigation_node::create(
                get_string('guide', 'theme_savoir'),
                null,
                navigation_node::TYPE_CUSTOM,
                null,
                'helpmenu',
                new pix_icon(
                        'e/help',
                        '',
                        'moodle'));
        $flat = new flat_navigation_node($navlink, 0);
        $this->add($flat);

        $navlink = navigation_node::create(
                get_string('studentguide', 'theme_savoir'),
                $studentguideurl,
                navigation_node::TYPE_CUSTOM,
                null,
                'studentguide',
                new pix_icon(
                        'i/user',
                        '',
                        'moodle'));
        $flat = new flat_navigation_node($navlink, 1);
        $this->add($flat);

        if ($isteacher || $isstaff) {
            $navlink = navigation_node::create(
                    get_string('staffguide', 'theme_savoir'),
                    $staffguideurl,
                    navigation_node::TYPE_CUSTOM,
                    null,
                    'staffguide',
                    new pix_icon(
                            'i/users',
                            '',
                            'moodle'));
            $flat = new flat_navigation_node($navlink, 1);
            $this->add($flat);
        }
    }

    protected function add_other_nodes($istudent = true, $isteacher = true, $isstaff = true) {
        global $PAGE;
        // Add-a-block in editing mode.
        if (isset($this->page->theme->addblockposition) &&
                $this->page->theme->addblockposition == BLOCK_ADDBLOCK_POSITION_FLATNAV &&
                $PAGE->user_is_editing() && $PAGE->user_can_edit_blocks() &&
                ($addable = $PAGE->blocks->get_addable_blocks())) {
            $url = new moodle_url($PAGE->url, ['bui_addblock' => '', 'sesskey' => sesskey()]);
            $addablock = navigation_node::create(get_string('addblock'), $url);
            $flat = new flat_navigation_node($addablock, 0);
            $flat->set_showdivider(true);
            $flat->key = 'addblock';
            $flat->icon = new pix_icon('i/addblock', '');
            $this->add($flat);
            $blocks = [];
            foreach ($addable as $block) {
                $blocks[] = $block->name;
            }
            $params = array('blocks' => $blocks, 'url' => '?' . $url->get_query_string(false));
            $PAGE->requires->js_call_amd('core/addblockmodal', 'init', array($params));
        }
    }

    protected function add_node($url, $label, $key, $iconname, $iconcomponent = 'moodle', $indent = 0) {

        $navlink = navigation_node::create($label, $url);
        $flat = new flat_navigation_node($navlink, $indent);
        $flat->set_showdivider(true);
        $flat->key = $key;
        $flat->icon = new pix_icon(
                $iconname,
                '',
                empty($iconcomponent) ? 'moodle' : $iconcomponent);
        $this->add($flat);
    }
}

function has_role_from_name($userid, $rolestring) {
    global $DB;
    $role = $DB->get_record('role', array('shortname' => $rolestring));
    return user_has_role_assignment($userid, $role->id);
}

/**
 * A shortcut to get all values for a two column layout like templates
 *
 * Note: we need to pass the $output as a variable as the global $OUTPUT is not set correctly
 * (see https://moodle.org/mod/forum/discuss.php?d=336651)
 *
 * @param $output
 * @return array
 * @throws coding_exception
 */
function get_context_two_columns_layout($output) {
    global $CFG, $PAGE, $SITE;

    user_preference_allow_ajax_update('drawer-open-nav', PARAM_ALPHA);
    require_once($CFG->libdir . '/behat/lib.php');

    if (isloggedin() && !isguestuser()) {
        $navdraweropen = (get_user_preferences('drawer-open-nav', 'true') == 'true');
    } else {
        $navdraweropen = false;
    }
    $extraclasses = [];
    if ($navdraweropen) {
        $extraclasses[] = 'drawer-open-left';
    }
    $bodyattributes = $output->body_attributes($extraclasses);
    $blockshtml = $output->blocks('side-pre');
    $hasblocks = strpos($blockshtml, 'data-block=') !== false;
    $regionmainsettingsmenu = $output->region_main_settings_menu();
    $templatecontext = [
            'sitename' => format_string($SITE->shortname, true, ['context' => context_course::instance(SITEID), "escape" => false]),
            'output' => $output,
            'sidepreblocks' => $blockshtml,
            'hasblocks' => $hasblocks,
            'bodyattributes' => $bodyattributes,
            'navdraweropen' => $navdraweropen,
            'regionmainsettingsmenu' => $regionmainsettingsmenu,
            'hasregionmainsettingsmenu' => !empty($regionmainsettingsmenu)
    ];

    $flatnav = new savoir_flat_navigation($PAGE);
    $flatnav->initialise();

    $templatecontext['flatnavigation'] = $flatnav;
    return $templatecontext;
}

/**
 * Get additional icon mapping for font-awesome and this theme.
 */
function theme_savoir_get_fontawesome_icon_map() {
    return [
            'theme_savoir:i/minus' => 'fa-minus',
            'theme_savoir:t/expanded' => 'fa-chevron-down',
            'theme_savoir:t/collapsed' => 'fa-chevron-right',
    ];
}
