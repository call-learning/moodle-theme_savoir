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
 * CLI script allowing to run internal/ setup functions multiple times
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php');
require_once(__DIR__ . '/../lib.php');
require_once(__DIR__ . '/../locallib.php');

$usage = "Run dashboard setup script

Usage:
    # php dashboardsetup.php --type=<userrole>
    # php dashboardsetup.php [--help|-h]

Options:
    -h --help                   Print this help.
    --type=userrole             Name of the user role that will be used to setup the dashboard
    --dry-run=<1|0>        If false/0, change dashboard setups for given users, if not just list actions to be done
";

list($options, $unrecognised) = cli_get_params([
    'help' => false,
    'type' => 'student',
    'dry-run' => true,
], [
    'h' => 'help'
]);

if ($unrecognised) {
    $unrecognised = implode(PHP_EOL . '  ', $unrecognised);
    cli_error(get_string('cliunknowoption', 'core_admin', $unrecognised));
}

if ($options['help']) {
    cli_writeln($usage);
    exit(2);
}

$rs = $DB->get_recordset('user', array('confirmed' => 1, 'deleted' => '0'));
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
switch ($options['type']) {
    case 'teacher':
        $defaultblockinstances = [
            [
                'blockname' => 'savoir_mycourses',
                'defaultregion' => 'content',
                'defaultweight' => 0
            ],
        ];
        break;
}
if ($options['dry-run']) {
    cli_writeln('Dry run option on : use --dry-run=false to really modify the dashboards');
}
foreach ($rs as $user) {
    if ($user->deleted == 1) {
        continue;
    }
    if (has_role_from_name($user->id, $options['type'])) {
        if (!$options['dry-run']) {
            setup_dashboard_blocks($user->id, $defaultblockinstances);
        }
        $userfullname = fullname($user);
        $targetrole = $options['type'];
        cli_writeln("User ({$user->id}) {$userfullname} : reset dashboard to {$targetrole} role");
    }
}
if ($options['dry-run']) {
    cli_writeln('Dashboards are unmodified, as dry run option is on : use --dry-run=false to really modify the dashboards');
}