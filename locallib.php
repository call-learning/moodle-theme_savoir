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

use theme_savoir\utils;

defined('MOODLE_INTERNAL') || die();

/**
 * Setting Tools
 */

class savoir_settings_navigation  extends settings_navigation {
    public function initialise() {
        global $PAGE;
        /* This is where it also gets very hackish because question_extend_settings_navigation function
        does not take the $page but gets the global $PAGE */
        $oldpage = $PAGE;
        $PAGE = $this->page;
        $this->load_course_settings();
        $PAGE = $oldpage;
    }
}

/**
 * This function is a bit of a hack, it gets all course setting values from a fake front page
 *
 * @param null $page
 * @param null $currentitem
 * @return array
 * @throws coding_exception
 */
function get_course_menu_toolbaritems($page = null, $currentitem = null, $currentpath = '') {
    global $CFG;
    // Add default items which won't be in the list as we are basing our search on SITEID (see enrol_add_course_navigation).
    $items = array('/courseadmin/users/review' => get_string('enrolledusers', 'enrol'),
        '/courseadmin/users/manageinstances' => get_string('enrolmentinstances', 'enrol'),
        '/courseadmin/users/override' => get_string('permissions', 'role'));
    if (!$page && empty($CFG->upgraderunning)) {
        global $CFG;
        $page = new moodle_page();
        $page->set_context(context_course::instance(SITEID));
        $page->set_url($CFG->wwwroot . '/');
        $pagenav = new savoir_settings_navigation($page);
        $pagenav->initialise();
        $currentitem = $pagenav->find('courseadmin', navigation_node::TYPE_COURSE);
    }
    if ($currentitem) {
        if ($currentitem->action) {
            $items += array($currentpath . '/' . $currentitem->key => $currentitem->text);
        }
        foreach ($currentitem->children as $child) {
            $items += get_course_menu_toolbaritems($page, $child, $currentpath . '/' . $currentitem->key);
        }
    }
    return $items;
}

/*
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
    global $DB;
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
            'blockname' => 'savoir_mycourses',
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
        $blockpositions = new stdClass();
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
    global $DB;
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
        // Clone the basic system page record.
        if (!$systempage = $DB->get_record('my_pages', array('userid' => null, 'private' => MY_PAGE_PRIVATE))) {
            return false;  // Error.
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
                'blockname' => 'savoir_mycourses',
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
    // TODO setup the CSS for savoir.
    set_config('mobilecssurl', $CFG->wwwroot . '/theme/savoir/mobile/savoirmobile.css');
    return true;
}

function setup_theme() {
    return setup_mobile_css();
}

function setup_syllabus() {
    global $DB;

    // We want all or nothing here.
    $transaction = $DB->start_delegated_transaction();
    $courses = $DB->get_recordset_sql("SELECT id,summary FROM {course} WHERE format IN ('topics','topcoll')");
    foreach ($courses as $c) {
        if ($c->id != SITEID) { // Skip Site frontpage.
            utils::set_course_syllabus($c);
        }
    }
    $transaction->allow_commit();
    return true;
}