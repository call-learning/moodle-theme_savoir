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

        if (is_siteadmin()) {
            parent::initialise();
            return;
        }

        $course = $PAGE->course;
        $this->page->navigation->initialise();
        $studentblocks = [
                [
                        'url' => '/my',
                        'label' => get_string('myhome'),
                        'key' => 'myhome',
                        'icon' => array('name' => 'i/dashboard')
                ],
                [
                        'url' => '/theme/savoir/pages/mycourses.php',
                        'label' => get_string('mycourses'),
                        'key' => 'mycourses',
                        'icon' => array('name' => 'i/course')
                ],
                [
                        'url' => '/course/index.php',
                        'label' => get_string('catalog', 'theme_savoir'),
                        'key' => 'catalog',
                        'icon' => array('name' => 'i/catalog', 'component' => 'theme_savoir')
                ]

        ];

        $isstudent = has_role_from_name($USER->id, 'student');
        $isteacher = has_role_from_name($USER->id, 'teacher');
        $iseditingteacher = has_role_from_name($USER->id, 'editingteacher');
        foreach ($studentblocks as $nl) {
            $url = new moodle_url($nl['url']);
            $navlink = navigation_node::create($nl['label'], $url);
            $flat = new flat_navigation_node($navlink, 0);
            $flat->set_showdivider(true);
            $flat->key = $nl['key'];
            $flat->icon = new pix_icon(
                    $nl['icon']['name'],
                    '',
                    empty($nl['icon']['component']) ? 'moodle' : $nl['icon']['component']);
            $this->add($flat);
        }
        $this->add_help_node();
    }
    protected function add_help_node() {
        /* Here we should add a choice of help courses (two for teachers/admin, one for student, depending on the extend of this
        user's roles */

    }
}

function has_role_from_name($userid, $rolestring) {
    global $DB;
    $role = $DB->get_record('role', array('shortname' => $rolestring));
    return user_has_role_assignment($userid, $role->id);
}

/**
 * ------------------------------------------------------------------------------------------------
 *              Setup
 * ------------------------------------------------------------------------------------------------
 */

require_once($CFG->dirroot . '/my/lib.php');

/**
 * Setup Main System Dashboard (student view)
 *
 */
function setup_system_dashboard() {
    global $DB, $CFG;
    // We want all or nothing here.
    $transaction = $DB->start_delegated_transaction();
    // Get the MY_PAGE_PRIVATE which is the dashboard (MY_PAGE_PUBLIC is the user profile page).
    $sysdashpage = $DB->get_record('my_pages', array('userid' => null, 'name' => '__default', 'private' => MY_PAGE_PRIVATE));
    $syscontext = context_system::instance();

    savoir_utils_delete_dashboard_blocks($syscontext, $sysdashpage);

    $defaultblockinstances = [
            [
                    'blockname' => 'calendar_month',
                    'defaultregion' => 'content',
                    'defaultweight' => -1
            ],
            [
                    'blockname' => 'calendar_upcoming',
                    'defaultregion' => 'content',
                    'defaultweight' => -2
            ],
            [
                    'blockname' => 'myoverview',
                    'defaultregion' => 'content',
                    'defaultweight' => 0
            ],
    ];
    savoir_utils_add_blocks($syscontext, 'my-index', $sysdashpage->id, $defaultblockinstances);
    $transaction->allow_commit();
    return true;
}

/**
 * Delete all blocks from this dashboard
 *
 * @param $context
 * @param $page
 * @throws dml_exception
 */
function savoir_utils_delete_dashboard_blocks($context, $page) {
    global $DB;
    if ($blocks = $DB->get_records('block_instances', array('parentcontextid' => $context->id,
            'pagetypepattern' => 'my-index'))) {
        foreach ($blocks as $block) {
            if (is_null($block->subpagepattern) || $block->subpagepattern == $page->id) {
                blocks_delete_instance($block);
            }
        }
    }
    $DB->delete_records('block_positions', ['subpage' => $page->id, 'pagetype' => 'my-index', 'contextid' => $context->id]);
}

/**
 * Add a block to a page
 *
 * @param $context
 * @param $pagepattern
 * @param $subpagepattern
 * @param $newblocks
 * @throws dml_exception
 */
function savoir_utils_add_blocks($context, $pagepattern, $subpageid, $newblocks) {
    global $DB;
    $availableblocks = $DB->get_records_menu('block', ['visible' => 1], '', 'id,name');

    foreach ($newblocks as $blockinstance) {
        // Check this block type is installed and enabled before adding.
        if (!in_array($blockinstance['blockname'], $availableblocks)) {
            continue;
        }

        // Add common properties.
        $blockinstance['parentcontextid'] = $context->id; // System context.
        $blockinstance['showinsubcontexts'] = empty($blockinstance['showinsubcontexts']) ? 0 : $blockinstance['showinsubcontexts'];
        $blockinstance['pagetypepattern'] = $pagepattern;
        $blockinstance['subpagepattern'] = $subpageid;
        if (!empty($blockinstance['data'])) {
            $data = json_decode($blockinstance['data']);
            $blockinstance['configdata'] = base64_encode(serialize($data));
        } else {
            $blockinstance['configdata'] = '';
        }
        $blockinstance['timecreated'] = time();
        $blockinstance['timemodified'] = time();
        // Add the block instances.
        $biid = $DB->insert_record('block_instances', $blockinstance);

        // Ensure context is properly created.
        context_block::instance($biid, MUST_EXIST);
        // Then add the block position (we assume it does not already exists).
        $blockpositions = new \stdClass();
        $blockpositions->subpage = $subpageid;
        $blockpositions->contextid = $context->id;
        $blockpositions->blockinstanceid = $biid;
        $blockpositions->visible = 1;
        $blockpositions->pagetype = $pagepattern;
        $blockpositions->region = empty($blockinstance['defaultregion']) ? 'side-pre' : $blockinstance['defaultregion'];
        $blockpositions->weight = empty($blockinstance['defaultweight']) ? 0 : $blockinstance['defaultweight'];

        $DB->insert_record('block_positions', get_object_vars($blockpositions));
    }

}

/**
 * Setup all Dashboard blocks for a given student
 * It deletes previous blocks instances so proceed with caution
 *
 * @return bool
 * @throws dml_exception
 * @throws dml_transaction_exception
 */
function setup_dashboard_blocks($userid = null, $defaultblockinstances = null) {
    global $DB, $CFG;
    // We want all or nothing here.
    $transaction = $DB->start_delegated_transaction();

    if (empty($userid)) {
        $adminuser = get_admin();
        $userid = $adminuser->id;
    }
    // Get the MY_PAGE_PRIVATE which is the dashboard (MY_PAGE_PUBLIC is the user profile page).
    $customiseduserdbpage = $DB->get_record('my_pages', array('userid' => $userid, 'private' => MY_PAGE_PRIVATE));
    $usercontext = context_user::instance($userid);
    if (!$customiseduserdbpage) {
        // Then we must create this page
        // Clone the basic system page record
        if (!$systempage = $DB->get_record('my_pages', array('userid' => null, 'private' => MY_PAGE_PRIVATE))) {
            return false;  // error
        }

        $customiseduserdbpage = clone($systempage);
        unset($customiseduserdbpage->id);
        $customiseduserdbpage->userid = $userid;
        $customiseduserdbpage->id = $DB->insert_record('my_pages', $customiseduserdbpage);
    }

    savoir_utils_delete_dashboard_blocks($usercontext, $customiseduserdbpage); // If it already exists.?
    if (!$defaultblockinstances) {
        $defaultblockinstances = [
                [
                        'blockname' => 'calendar_month',
                        'defaultregion' => 'content',
                        'defaultweight' => -1
                ],
                [
                        'blockname' => 'calendar_upcoming',
                        'defaultregion' => 'content',
                        'defaultweight' => -2
                ],
                [
                        'blockname' => 'myoverview',
                        'defaultregion' => 'content',
                        'defaultweight' => 0
                ],
        ];
    }
    savoir_utils_add_blocks($usercontext, 'my-index', $customiseduserdbpage->id, $defaultblockinstances);
    $transaction->allow_commit();
    return true;
}

function setup_mobile_css() {
    global $CFG;
    // TODO setup the CSS for savoir
    set_config('mobilecssurl', $CFG->wwwroot . '/theme/savoir/mobile/savoirmobile.css');
}

function setup_theme() {
    return setup_mobile_css();
}

