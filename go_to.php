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
 * Version information
 *
 * @package    mod
 * @subpackage opencast
 * @copyright  2013-2015 Université de Lausanne
 * @author     Nicolas.Dunand@unil.ch
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

include('../../config.php');

$url_b64 = required_param('url', PARAM_RAW_TRIMMED);
$salt_b64 = required_param('salt', PARAM_RAW_TRIMMED);
$token_b64 = required_param('token', PARAM_RAW_TRIMMED);
$swid = required_param('swid', PARAM_INT);

if (!$opencast = $DB->get_record('opencast', ['id' => $swid])) {
    print_error('invalidcoursemodule');
}

if (!$course = $DB->get_record('course', ['id' => $opencast->course])) {
    print_error('coursemisconf');
}

$return_course = new moodle_url('/course/view.php', ['id' => $course->id]);

if (!$module = $DB->get_record('modules', ['name' => 'opencast'])) {
    print_error('invalidcoursemodule', null, $return_course);
}

if (!$cm = $DB->get_record('course_modules',
        ['course' => $course->id, 'module' => $module->id, 'instance' => $opencast->id])
) {
    print_error('invalidcoursemodule', null, $return_course);
}

if (!$context = context_module::instance($cm->id)) {
    print_error('badcontext', null, $return_course);
}

require_login($course);
require_capability('mod/opencast:use', $context);

$url = base64_decode($url_b64);
$salt = base64_decode($salt_b64);
$token = base64_decode($token_b64);

if ($token == sha1(mod_opencast_series::getValueForKey('default_sysaccount') . $salt . $opencast->id . $url)) {
    $eventparams = ['context' => $context, 'objectid' => $opencast->id];
    $event = \mod_opencast\event\clip_viewed::create($eventparams);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('opencast', $opencast);
    $event->trigger();

    // 1.- request signing of the URL
    $time = time();
    $validity_time_seconds = 3600;
    $valid_until = $time + $validity_time_seconds;
    $signing_request_params = [
            'url'          => $url,
            'valid-until'  => date('Y-m-d', $valid_until) . 'T' . gmdate('H:i:s', $valid_until) . 'Z'
    ];
    if (mod_opencast_series::getValueForKey('use_ipaddr_restriction')) {
        $signing_request_params['valid-source'] = getremoteaddr();
    }
    $signed_url = mod_opencast_apicall::sendRequest('/security/sign', 'POST', $signing_request_params);

    // 2.- redirect to signed URL
    header("Location: " . $signed_url->url);
    exit;
}

print_error('redirfailed', 'opencast');

