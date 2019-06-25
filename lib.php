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
function theme_savoir_process_site_branding()
{
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
        $coverimagefpurl = $OUTPUT->image_url('coverimagefp','theme');
    }

    $replacementimages = array (
        'coverimagefp'=> "background-image: url($coverimagefpurl);",
    );
    foreach( $replacementimages as $type => $csscode ) {
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
 * ------------------------------------------------------------------------------------------------
 *              Setup
 * ------------------------------------------------------------------------------------------------
 */


/**
* Setup all frontpage blocks
* It deletes previous blocks instances so proceed with caution
* @return bool
    * @throws dml_exception
* @throws dml_transaction_exception
*/
function setup_frontpage_blocks()
{
    global $DB, $CFG;
    // We want all or nothing here.
    $transaction = $DB->start_delegated_transaction();

    $context = context_course::instance(SITEID);
    if ($blocks = $DB->get_records('block_instances',
        array('parentcontextid' => $context->id, 'pagetypepattern' => 'site-index'))) {
        foreach ($blocks as $block) {
            blocks_delete_instance($block);
        }
    }

    $DB->delete_records('block_positions', array(
        'contextid' => $context->id,
        'pagetype' => 'site-index'
    ));

    $defaultblockinstances = [
        [
            'blockname' => 'html',
            'defaultregion' => 'side-pre',
            'defaultweight' => -9
        ],
    ];

    $availableblocks = $DB->get_records_menu('block', ['visible' => 1], '', 'id,name');

    foreach ($defaultblockinstances as $blockinstance) {
        // Check this block type is installed and enabled before adding.
        if (!in_array($blockinstance['blockname'], $availableblocks)) {
            continue;
        }

        // Add common properties.
        $blockinstance['parentcontextid'] = $context->id; // System context.
        $blockinstance['showinsubcontexts'] = 0;
        $blockinstance['pagetypepattern'] = 'site-index';
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
    }

    $transaction->allow_commit();
    return true;

}

function setup_front_page_section() {
    global $DB;
    // Reset string cache
    get_string_manager()->reset_caches();
    // Get the titles
    $title = get_string('front_page_section_title','theme_savoir');
    $content = get_string('front_page_section_content','theme_savoir');

    $record = $DB->get_record('course_sections',array('course'=> SITEID ));
    if (!$record) {
        $record = new stdClass();
        $record->course = SITEID;
        $record->section = 1;
    }
    $record->summmaryformat = 1;
    $record->sequence = '';
    $record->visible = 1;
    $record->availability = '{"op":"&","c":[],"showc":[]}';
    $record->timemodified = time();
    $record->name = $title;
    $record->summary = $content;
    if (!empty($record->id)) {
        $DB->update_record('course_sections',$record);
    } else {
        $DB->insert_record('course_sections', $record);
    }

}

function setup_mobile_css() {
    global $CFG;
    // TODO setup the CSS for savoir
    set_config('mobilecssurl',$CFG->wwwroot.'/theme/savoir/mobile/savoirmobile.css');
}

function setup_theme() {
    return setup_mobile_css();
}

