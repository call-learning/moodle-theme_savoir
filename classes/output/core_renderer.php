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

namespace theme_savoir\output;

use action_link;
use action_menu;
use action_menu_filler;
use action_menu_link_secondary;
use block_contents;
use block_move_target;
use coding_exception;
use context_header;
use context_system;
use core_text;
use custom_menu;
use html_writer;
use moodle_url;
use navigation_node;
use pix_icon;
use renderer_base;
use stdClass;
use context_course;

defined('MOODLE_INTERNAL') || die;

class savoir_custom_menu extends custom_menu {
    const ENSAM_ROOT_URL = 'ensam.eu';

    /**
     * Hightlight the first word in case we have a menu with URL matching ENSAM's sites
     * Up to one level down only
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output Used to do a final render of any components that need to be rendered for export.
     * @return array
     */
    public function export_for_template(renderer_base $output) {
        global $CFG;
        $context = parent::export_for_template($output);
        if (strpos($this->text, self::ENSAM_ROOT_URL) !== false) {
            $context->text = preg_replace("/^(\w+)/", '<span class="savoir-site-name">${1}</span>', $context->text);
        }
        $context->haschildren = !empty($this->children) && (count($this->children) > 0);
        foreach ($context->children as $child) {
            if (strpos($child->text, self::ENSAM_ROOT_URL) !== false) {
                $child->text = preg_replace("/^(\w+)/", '<span class="savoir-site-name">${1}</span>', $child->text);
            }
        }

        return $context;
    }
}

/**
 * Renderers to align Moodle's HTML with that expected by Bootstrap
 *
 * @package   theme_savoir
 * @copyright 2019 - Clément Jourdain (clement.jourdain@gmail.com) & Laurent David (laurent@call-learning.fr)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class core_renderer extends \theme_boost\output\core_renderer {

    /**
     * Wrapper for header elements. Add elements for Frontpage (title, slogan, login button)
     *
     * @return string HTML to display the main header.
     */
    public function full_header() {
        global $PAGE;
        $header = new stdClass();
        $header->settingsmenu = $this->context_header_settings_menu();
        $header->handytoolbar = $this->context_handy_toolbar();
        $header->contextheader = $this->context_header();
        $header->hasnavbar = empty($PAGE->layout_options['nonavbar']);
        $header->navbar = $this->navbar();
        $header->pageheadingbutton = $this->page_heading_button();
        $header->courseheader = $this->course_header();
        $template = 'theme_savoir/header';
        if ($this->is_on_frontpage()) {
            $template = 'theme_savoir/header_fp';
            $options = new stdClass();
            $options->noclean = true;    // Don't clean Javascripts etc.
            $options->overflowdiv = false;
            $context = context_course::instance($this->page->course->id);
            $summary =
                    file_rewrite_pluginfile_urls(
                            $this->page->course->summary,
                            'pluginfile.php',
                            $context->id,
                            'course',
                            'summary',
                            null);
            $content = format_text($summary, $this->page->course->summaryformat, $options);
            if (!isloggedin()) {
                $header->loginurl = get_login_url();
            }
            $header->frontpageslogan = $content;
            $header->frontpagestitle = $this->page->course->shortname;
            $header->alertmessage = format_text(get_config('theme_savoir','fpmessage'),FORMAT_HTML);
            $header->alertenabled = get_config('theme_savoir','fpmessageenabled');
        } else if ($this->is_on_page_with_description()) {
            $header->pageslogan = get_string(preg_replace('/^theme-savoir-pages-/', '', $this->page->pagetype, 1) . '-description', 'theme_savoir');
            $header->bgimageurl = $this->image_url('genericbackground', 'theme_savoir');;
            $template = 'theme_savoir/header_desc';
        }
        return $this->render_from_template($template, $header);
    }



    /**
     * This is an optional menu that can be added to a layout by a theme. It contains the
     * menu for the course administration, only on the course main page.
     *
     * @return string
     */
    public function context_handy_toolbar() {
        $rendered = "";
        $context = $this->page->context;

        $items = $this->page->navbar->get_items();
        $currentnode = end($items);

        // We are on the course home page.
        if (($context->contextlevel == CONTEXT_COURSE) &&
                !empty($currentnode) &&
                ($currentnode->type == navigation_node::TYPE_COURSE || $currentnode->type == navigation_node::TYPE_SECTION)) {
            $settingsnode = $this->page->settingsnav->find('courseadmin', navigation_node::TYPE_COURSE);

            $itemstoextractfrommenu = explode(',',get_config('theme_savoir','coursemenuhandytoolbar'));
            if ($itemstoextractfrommenu && $settingsnode) {
                $extracteditems = [];
                $this->extract_toolbaritems($settingsnode, '/'.$settingsnode->key, $itemstoextractfrommenu, $extracteditems);
                $context = new stdClass();
                $context->items = $extracteditems;
                $rendered = $this->render_from_template('theme_savoir/handy_toolbar', $context);
            }
        }

        return $rendered;
    }

    protected function extract_toolbaritems($currentnode, $currentpath, $itemstoextractfrommenu, &$extracteditems) {
        if ($currentnode) {
            foreach ($currentnode->children as $child) {
                if (in_array($currentpath.'/'.$child->key, $itemstoextractfrommenu, true)) {
                    $extracteditems[] = $child;
                }
                $this->extract_toolbaritems($child, $currentpath.'/'.$child->key, $itemstoextractfrommenu, $extracteditems);
            }
        }
    }
    public function is_on_frontpage() {
        return ($this->page->pagelayout == 'frontpage');
    }

    public function is_on_dashboard() {
        return ($this->page->pagelayout == 'mydashboard');
    }

    public function is_on_page_with_description() {
        return ($this->page->pagelayout == 'pagewithdescription');
    }
    /**
     * Get Logo URL
     * If it has not been overriden by core_admin config, serve the logo in pix
     */
    public function get_logo_url($maxwidth = null, $maxheight = 200) {
        global $OUTPUT;
        $logourl = parent::get_logo_url($maxwidth, $maxheight);
        if (!$logourl) {
            $logourl = $this->image_url('logo', 'theme_savoir');
        }
        return $logourl;
    }

    /**
     * Get the compact logo URL.
     *
     * @return string
     */
    public function get_compact_logo_url($maxwidth = 100, $maxheight = 100) {
        global $OUTPUT;
        $compactlogourl = parent::get_compact_logo_url($maxwidth, $maxheight);
        if (!$compactlogourl) {
            $compactlogourl = $this->image_url('logocompact', 'theme_savoir');
        }
        return $compactlogourl;
    }

    /**
     * Returns the URL for the favicon.
     *
     * @since Moodle 2.5.1 2.6
     * @return string The favicon URL
     */
    public function favicon() {
        $favicon = get_config('theme_savoir', 'favicon');
        if (empty($favicon)) {
            return $this->image_url('favicon', 'theme');
        }
        return moodle_url::make_pluginfile_url(
                context_system::instance()->id,
                'theme_savoir',
                'favicon',
                0 ,
                theme_get_revision(),
                $favicon)->out();
    }


    public function should_display_navbar_logo() {
        $logo = $this->get_compact_logo_url();
        return !empty($logo);
    }

    /*
     * Overriding the custom_menu function ensures the custom menu is
     * always shown, even if no menu items are configured in the global
     * theme settings page.
     */
    public function custom_menu($custommenuitems = '') {
        global $CFG;

        if (empty($custommenuitems) && !empty($CFG->custommenuitems)) {
            $custommenuitems = $CFG->custommenuitems;
        }
        $custommenu = new savoir_custom_menu($custommenuitems, current_language());
        $custommenu->set_url(new moodle_url($CFG->wwwroot));
        $custommenu->set_text('Savoir.ensam.eu');
        return $this->render_custom_menu($custommenu);
    }

    /*
     * This renders the bootstrap top menu.
     *
     * This renderer is needed to enable the Bootstrap style navigation.
     */
    protected function render_custom_menu(custom_menu $menu) {
        global $CFG;

        $langs = get_string_manager()->get_list_of_translations();
        $haslangmenu = $this->lang_menu() != '';

        if (!$menu->has_children() && !$haslangmenu) {
            return '';
        }

        if ($haslangmenu) {
            $strlang = get_string('language');
            $currentlang = current_language();
            if (isset($langs[$currentlang])) {
                $currentlang = $langs[$currentlang];
            } else {
                $currentlang = $strlang;
            }
            $this->language = $menu->add($currentlang, new moodle_url('#'), $strlang, 10000);
            foreach ($langs as $langtype => $langname) {
                $this->language->add($langname, new moodle_url($this->page->url, array('lang' => $langtype)), $langname);
            }
        }

        $context = $menu->export_for_template($this);
        $content = $this->render_from_template('core/custom_menu_item', $context);

        return $content;
    }

    /**
     * Check whether the current page is a signup page
     *
     * @return bool
     * @see is_login_page()
     */
    protected function is_signup_page() {
        // This is the same hack as for login page. Well...
        return in_array(
                $this->page->url->out_as_local_url(false, array()),
                array(
                        '/login/signup.php'
                )
        );
    }

    /**
     * The standard tags that should be included in the <head> tag
     * including a meta description for the front page
     *
     * @return string HTML fragment.
     */
    public function standard_head_html() {
        global $SITE, $PAGE;

        $output = parent::standard_head_html();
        $output .= '<link href=\"https://fonts.googleapis.com/css?family=Roboto|Nova+Mono|Roboto+Mono|Tinos\" rel=\"stylesheet\">';

        return $output;
    }

    public function should_display_sandwitch_menu() {
        global $PAGE;
        global $USER;

        if ($PAGE->pagelayout == 'frontpage' || !isloggedin() || isguestuser()) {
            return false;
        }
        return true;
    }

    /**
     * Just override default behaviour when user not logged in, so we don't display "You are not logged in"
     */
    public function user_menu($user = null, $withlinks = null) {
        global $USER, $CFG;
        require_once($CFG->dirroot . '/user/lib.php');

        if (is_null($user)) {
            $user = $USER;
        }

        // Note: this behaviour is intended to match that of core_renderer::login_info,
        // but should not be considered to be good practice; layout options are
        // intended to be theme-specific. Please don't copy this snippet anywhere else.
        if (is_null($withlinks)) {
            $withlinks = empty($this->page->layout_options['nologinlinks']);
        }

        // Add a class for when $withlinks is false.
        $usermenuclasses = 'usermenu';
        if (!$withlinks) {
            $usermenuclasses .= ' withoutlinks';
        }

        $returnstr = "";

        // If during initial install, return the empty return string.
        if (during_initial_install()) {
            return $returnstr;
        }

        $loginpage = $this->is_login_page();
        $loginurl = get_login_url();
        // If not logged in, show the typical not-logged-in string.
        if (!isloggedin()) {
            // SAVOIR-ENSAM: Modificiations : do not display prompt.
            $returnstr = "";
            // SAVOIR-ENSAM: Modificiations.
            if (!$loginpage) {
                $returnstr .= " (<a href=\"$loginurl\">" . get_string('login') . '</a>)';
            }
            return html_writer::div(
                    html_writer::span(
                            $returnstr,
                            'login'
                    ),
                    $usermenuclasses
            );

        }

        // If logged in as a guest user, show a string to that effect.
        if (isguestuser()) {
            $returnstr = get_string('loggedinasguest');
            if (!$loginpage && $withlinks) {
                $returnstr .= " (<a href=\"$loginurl\">" . get_string('login') . '</a>)';
            }

            return html_writer::div(
                    html_writer::span(
                            $returnstr,
                            'login'
                    ),
                    $usermenuclasses
            );
        }

        // Get some navigation opts.
        $opts = user_get_user_navigation_info($user, $this->page);

        $avatarclasses = "avatars";
        $avatarcontents = html_writer::span($opts->metadata['useravatar'], 'avatar current');
        $usertextcontents = $opts->metadata['userfullname'];

        // Other user.
        if (!empty($opts->metadata['asotheruser'])) {
            $avatarcontents .= html_writer::span(
                    $opts->metadata['realuseravatar'],
                    'avatar realuser'
            );
            $usertextcontents = $opts->metadata['realuserfullname'];
            $usertextcontents .= html_writer::tag(
                    'span',
                    get_string(
                            'loggedinas',
                            'moodle',
                            html_writer::span(
                                    $opts->metadata['userfullname'],
                                    'value'
                            )
                    ),
                    array('class' => 'meta viewingas')
            );
        }

        // Role.
        if (!empty($opts->metadata['asotherrole'])) {
            $role = core_text::strtolower(preg_replace('#[ ]+#', '-', trim($opts->metadata['rolename'])));
            $usertextcontents .= html_writer::span(
                    $opts->metadata['rolename'],
                    'meta role role-' . $role
            );
        }

        // User login failures.
        if (!empty($opts->metadata['userloginfail'])) {
            $usertextcontents .= html_writer::span(
                    $opts->metadata['userloginfail'],
                    'meta loginfailures'
            );
        }

        // MNet.
        if (!empty($opts->metadata['asmnetuser'])) {
            $mnet = strtolower(preg_replace('#[ ]+#', '-', trim($opts->metadata['mnetidprovidername'])));
            $usertextcontents .= html_writer::span(
                    $opts->metadata['mnetidprovidername'],
                    'meta mnet mnet-' . $mnet
            );
        }
        // SAVOIR-ENSAM: Modificiations : switch avatar and login name.
        $returnstr .= html_writer::span(
                html_writer::span($avatarcontents, $avatarclasses) .
                html_writer::span($usertextcontents, 'usertext mr-1'),
                'userbutton'
        );
        // END SAVOIR-ENSAM.

        // Create a divider (well, a filler).
        $divider = new action_menu_filler();
        $divider->primary = false;

        $am = new action_menu();
        $am->set_menu_trigger(
                $returnstr
        );
        $am->set_action_label(get_string('usermenu'));
        $am->set_alignment(action_menu::TR, action_menu::BR);
        $am->set_nowrap_on_items();

        // SAVOIR-ENSAM: Modificiations : filter out unwanted menu for student
        // Filter out the dashboard menu.
        $FILTER_FOR_ALL_USERS =  ['mymoodle,admin','messages,message'];
        $this->filter_action_menu($opts->navitems, $FILTER_FOR_ALL_USERS);
        if (!has_role_from_name($USER->id, 'teacher') && !has_role_from_name($USER->id, 'editingteacher')) {
            $this->filter_action_menu($opts->navitems, ['grades,grades']);
        }
        // END SAVOIR-ENSAM.
        if ($withlinks) {
            $navitemcount = count($opts->navitems);
            $idx = 0;
            foreach ($opts->navitems as $key => $value) {

                switch ($value->itemtype) {
                    case 'divider':
                        // If the nav item is a divider, add one and skip link processing.
                        $am->add($divider);
                        break;

                    case 'invalid':
                        // Silently skip invalid entries (should we post a notification?).
                        break;

                    case 'link':
                        // Process this as a link item.
                        $pix = null;
                        if (isset($value->pix) && !empty($value->pix)) {
                            $pix = new pix_icon($value->pix, '', null, array('class' => 'iconsmall'));
                        } else if (isset($value->imgsrc) && !empty($value->imgsrc)) {
                            $value->title = html_writer::img(
                                            $value->imgsrc,
                                            $value->title,
                                            array('class' => 'iconsmall')
                                    ) . $value->title;
                        }

                        $al = new action_menu_link_secondary(
                                $value->url,
                                $pix,
                                $value->title,
                                array('class' => 'icon')
                        );
                        if (!empty($value->titleidentifier)) {
                            $al->attributes['data-title'] = $value->titleidentifier;
                        }
                        $am->add($al);
                        break;
                }

                $idx++;

                // Add dividers after the first item and before the last item.
                if ($idx == 1 || $idx == $navitemcount - 1) {
                    $am->add($divider);
                }
            }
        }

        return html_writer::div(
                $this->render($am),
                $usermenuclasses
        );
    }
    private function filter_action_menu(&$navigationlinks, $filternamesarray) {
        $navigationlinks =  array_filter($navigationlinks, function($menu) use ($filternamesarray) {
            return !in_array($menu->titleidentifier, $filternamesarray);
        });
    }
    /**
     * Renders a custom block region.
     * OVERRIDE FOR SAVOIR DASHBOARD : layout block in the center area
     * Use this method if you want to add an additional block region to the content of the page.
     * Please note this should only be used in special situations.
     * We want to leave the theme is control where ever possible!
     *
     * This method must use the same method that the theme uses within its layout file.
     * As such it asks the theme what method it is using.
     * It can be one of two values, blocks or blocks_for_region (deprecated).
     *
     * @param string $regionname The name of the custom region to add.
     * @return string HTML for the block region.
     */
    public function custom_block_region($regionname) {
        if ($this->page->theme->get_block_render_method() === 'blocks') {
            global $PAGE;
            if ($PAGE->pagelayout == 'mydashboard') {
                return $this->blocks($regionname,
                        array('d-flex', 'flex-wrap', 'justify-content-between')); // Wrap and flex the blocks
            } else {
                return $this->blocks($regionname);
            }
        } else {
            return $this->blocks_for_region($regionname);
        }
    }

    /**
     * Output all the blocks in a particular region.
     * OVERRIDE FOR SAVOIR DASHBOARD : layout block in the center area
     *
     * @param string $region the name of a region on this page.
     * @return string the HTML to be output.
     */
    public function blocks_for_region($region) {
        global $PAGE;
        $blockcontents = $this->page->blocks->get_content_for_region($region, $this);
        $blocks = $this->page->blocks->get_blocks_for_region($region);
        $lastblock = null;
        $zones = array();
        foreach ($blocks as $block) {
            $zones[] = $block->title;
        }
        $output = '';

        $oddnumberblocks = (count($blockcontents) % 2);
        foreach ($blockcontents as $index => $bc) {
            if ($bc instanceof block_contents) {
                $blockclass = '';

                if ($PAGE->pagelayout == 'mydashboard') {
                    $blockclass = ($index == (count($blockcontents) - 1)
                            && $oddnumberblocks) ?
                            'db-singleblock' : 'db-doubleblock';
                    // Then add ml-auto or mr-auto depending on the side of the block.
                    $blockclass .= ' ';
                    $blockclass .= (($index % 2) ? 'ml-auto' : 'mr-auto');
                }
                $output .= $this->block($bc, $region, $blockclass);
                $lastblock = $bc->title;
            } else if ($bc instanceof block_move_target) {
                $output .= $this->block_move_target($bc, $zones, $lastblock, $region);
            } else {
                throw new coding_exception('Unexpected type of thing (' . get_class($bc) . ') found in list of block contents.');
            }
        }
        return $output;
    }

    /**
     * Prints a nice side block with an optional header.
     * OVERRIDE FOR SAVOIR DASHBOARD : layout block in the center area
     *
     * @param block_contents $bc HTML for the content
     * @param string $region the region the block is appearing in.
     * @return string the HTML to be output.
     */
    public function block(block_contents $bc, $region, $additionalclasses = '') {
        $bc = clone($bc); // Avoid messing up the object passed in.
        $bc->attributes['class'] .= $additionalclasses;
        return parent::block($bc, $region);
    }
}
