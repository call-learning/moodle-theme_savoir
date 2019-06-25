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

use context_system;
use custom_menu;
use html_writer;
use moodle_url;
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
        if (strpos($this->text,self::ENSAM_ROOT_URL) !== false) {
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
     * Wrapper for header elements.
     *
     * @return string HTML to display the main header.
     */
    public function full_header() {
        global $PAGE;

        $html = html_writer::start_tag('header', array('id' => 'page-header', 'class' => 'row'));
        $html .= html_writer::start_div('col-xs-12 p-a-1');
        $html .= html_writer::start_div('card');
        $html .= html_writer::start_div('card-block');
        $html .= html_writer::div($this->context_header_settings_menu(), 'pull-xs-right context-header-settings-menu');
        $html .= html_writer::start_div('pull-xs-left');
        $html .= $this->context_header();
        $html .= html_writer::end_div();
        $pageheadingbutton = $this->page_heading_button();
        if (empty($PAGE->layout_options['nonavbar'])) {
            $html .= html_writer::start_div('clearfix w-100 pull-xs-left', array('id' => 'page-navbar'));
            $html .= html_writer::tag('div', $this->navbar(), array('class' => 'breadcrumb-nav'));
            $html .= html_writer::div($pageheadingbutton, 'breadcrumb-button pull-xs-right');
            $html .= html_writer::end_div();
        } else if ($pageheadingbutton) {
            $html .= html_writer::div($pageheadingbutton, 'breadcrumb-button nonavbar pull-xs-right');
        }
        if ($this->is_on_frontpage()) {
            $options = new stdClass();
            $options->noclean = true;    // Don't clean Javascripts etc
            $options->overflowdiv = false;
            $context = context_course::instance($this->page->course->id);
            $summary =
                    file_rewrite_pluginfile_urls($this->page->course->summary, 'pluginfile.php', $context->id, 'course', 'summary',
                            null);
            $content = format_text($summary, $this->page->course->summaryformat, $options);
            if (!isloggedin()) {
                $content .= html_writer::link(get_login_url(), get_string('login'), array('class' => 'connect-button'));
            }

            $html .= html_writer::tag('div', $content, array('class' => 'site-frontpage-slogan'));
        } else {
            $html .= html_writer::tag('div', $this->course_header(), array('id' => 'course-header'));
        }
        $html .= html_writer::end_div();
        $html .= html_writer::end_div();
        $html .= html_writer::end_div();
        $html .= html_writer::end_tag('header');
        return $html;
    }

    /**
     * This is an optional menu that can be added to a layout by a theme. It contains the
     * menu for the course administration, only on the course main page.
     *
     * @return string
     */
    public function context_header($headerinfo = null, $headinglevel = 1) {
        if ($this->is_on_frontpage()) {
            return '';
        } else {
            return parent::context_header($headerinfo, $headinglevel);
        }
    }

    public function is_on_frontpage() {
        return ($this->page->pagelayout == 'frontpage');
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

    public function should_display_navbar_logo() {
        $logo = $this->get_compact_logo_url();
        return !empty($logo);
    }

    /**
     * Construct a user menu, returning HTML that can be echoed out by a
     * layout file.
     *
     * @param stdClass $user A user object, usually $USER.
     * @param bool $withlinks true if a dropdown should be built.
     * @return string HTML fragment.
     */
    public function user_menu($user = null, $withlinks = null) {
        global $USER, $CFG;
        require_once($CFG->dirroot . '/user/lib.php');
        require_once($CFG->libdir . '/authlib.php');

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

        $signuppage = $this->is_signup_page();
        $signupurl = "$CFG->wwwroot/login/signup.php";

        if (!signup_is_enabled()) {
            $signuppage = false;
        }
        // If not logged in, show the typical not-logged-in string.
        if (!isloggedin()) {
            $loginhtml = \html_writer::span(get_string('login'), 'login-item');
            $signuphtml = \html_writer::span(get_string('createaccount'), 'login-item');

            if (!$loginpage) {
                $loginhtml = \html_writer::link($loginurl, $loginhtml);
            }

            if (!$signuppage) {
                $signuphtml = \html_writer::link($signupurl, $signuphtml);
            }

            $returnstr = $signuphtml . $loginhtml;
            return html_writer::div(
                    html_writer::span(
                            $returnstr,
                            'login'
                    ),
                    $usermenuclasses
            );

        } else {
            return parent::user_menu($user, $withlinks);
        }
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
        if ($PAGE->pagelayout == 'frontpage') {
            return false;
        }
        return true;
    }
}
