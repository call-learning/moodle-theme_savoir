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

defined('MOODLE_INTERNAL') || die;// Main settings.

$name = 'theme_savoir/branding_title';
$heading = new lang_string('brading_title', 'theme_savoir');
$description = new lang_string('brading_title_desc', 'theme_savoir');;
$setting = new admin_setting_heading($name, $heading, $description);
$settings->add($setting);

// Cover image for front-page file setting.
$name = 'theme_savoir/favicon';
$title = new lang_string('favicon', 'theme_savoir');
$description = new lang_string('favicondesc', 'theme_savoir');
$opts = array('accepted_types' => array('.ico'));
$setting = new admin_setting_configstoredfile($name, $title, $description, 'favicon', 0, $opts);
$setting->set_updatedcallback('theme_savoir_process_site_branding');
$settings->add($setting);

// Cover image for front-page file setting.
$name = 'theme_savoir/coverimagefp';
$title = new lang_string('coverimagefp', 'theme_savoir');
$description = new lang_string('coverimagefpdesc', 'theme_savoir');
$opts = array('accepted_types' => array('.png', '.jpg', '.gif', '.webp', '.svg'));
$setting = new admin_setting_configstoredfile($name, $title, $description, 'coverimagefp', 0, $opts);
$setting->set_updatedcallback('theme_savoir_process_site_branding');
$settings->add($setting);

$name = 'theme_savoir/primarycolor';
$title = get_string('primarycolor', 'theme_savoir');
$description = get_string('primarycolor_desc', 'theme_savoir');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '#8D2363');
$setting->set_updatedcallback('theme_reset_all_caches');
$settings->add($setting);

$name = 'theme_savoir/secondarycolor';
$title = get_string('secondarycolor', 'theme_savoir');
$description = get_string('secondarycolor_desc', 'theme_savoir');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '#F29400');
$setting->set_updatedcallback('theme_reset_all_caches');
$settings->add($setting);

global $DB;
$sql = "SELECT c.id, "
        . $DB->sql_concat("c.fullname", "':'", "c.id")
        . " as name FROM {course} c WHERE c.idnumber LIKE '%GUIDE_%'";
$helpcoursechoices = $DB->get_records_sql_menu($sql);
if ($helpcoursechoices) {
    $name = 'theme_savoir/studenthelpcourse';
    $title = get_string('studenthelpcourse', 'theme_savoir');
    $description = get_string('studenthelpcourse_desc', 'theme_savoir');
    $setting = new admin_setting_configselect($name, $title, $description, SITEID, $helpcoursechoices);
    $settings->add($setting);

    $name = 'theme_savoir/staffhelpcourse';
    $title = get_string('staffhelpcourse', 'theme_savoir');
    $description = get_string('staffhelpcourse_desc', 'theme_savoir');
    $setting = new admin_setting_configselect($name, $title, $description, SITEID, $helpcoursechoices);
    $settings->add($setting);

}
require_once($CFG->dirroot.'/theme/savoir/locallib.php');
$options = get_course_menu_toolbaritems();

$name = 'theme_savoir/coursemenuhandytoolbar';
$title = get_string('coursemenuhandytoolbar', 'theme_savoir');
$description = get_string('coursemenuhandytoolbar_desc', 'theme_savoir');
$setting = new admin_setting_configmultiselect($name, $title, $description, [], $options);
$settings->add($setting);


// Frontpage message (alert)
$name = 'theme_savoir/fpmessage';
$title = new lang_string('fpmessage', 'theme_savoir');
$description = new lang_string('fpmessage_desc', 'theme_savoir');
$setting = new admin_setting_confightmleditor($name, $title, $description, '');
$settings->add($setting);


$name = 'theme_savoir/fpmessageenabled';
$title = new lang_string('fpmessageenabled', 'theme_savoir');
$description = new lang_string('fpmessageenabled_desc', 'theme_savoir');
$setting = new admin_setting_configcheckbox($name, $title, $description, '0');
$settings->add($setting);
