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
 * @package    mod_opencast
 * @copyright  2013-2019 Université de Lausanne
 * @author     Nicolas.Dunand@unil.ch
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/clilib.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

$USER = get_admin();

require_once($CFG->dirroot . '/mod/opencast/lib.php');

//require_capability('moodle/site:config', \context_system::instance());
// TODO make access control

list($options, $unrecognised) = cli_get_params(['id' => null]);

if ($options['id'] == null) {
    cli_writeln('usage : --id=<ID>');
    exit(2);
}

$id = (int)$options['id']; // Course Module ID

$url = new moodle_url('/mod/opencast/view.php', ['id' => $id]);

$PAGE->set_url($url);

if (!$cm = get_coursemodule_from_id('opencast', $id)) {
    print_error('invalidcoursemodule');
}

if (!$course = $DB->get_record("course", ["id" => $cm->course])) {
    print_error('coursemisconf');
}

$return_course = new moodle_url('/course/view.php', ['id' => $course->id]);

if (!$opencast = opencast_get_opencast($cm->instance)) {
    print_error('invalidcoursemodule', null, $return_course);
}

if (!$context = context_module::instance($cm->id)) {
    print_error('badcontext', null, $return_course);
}

$PAGE->set_title(format_string($opencast->name));
$PAGE->set_heading($course->fullname);

//$eventparams = ['context' => $context, 'objectid' => $opencast->id];
//$event = \mod_opencast\event\series_downloaded::create($eventparams);
//$event->add_record_snapshot('course_modules', $cm);
//$event->add_record_snapshot('course', $course);
//$event->add_record_snapshot('opencast', $opencast);
//$event->trigger();

/**/

$series = new mod_opencast_series();
$series->fetch($opencast->id);

$nonverified_clips = $series->getEvents();
$nbclips = count($nonverified_clips);

if (!$nbclips) {
    print_error('noclipsinchannel', 'opencast', $return_course);
}

//$clips = $series->checkAccess($nonverified_clips);
//
//if (!count($clips)) {
//    print_error('novisibleclipsinchannel', 'opencast', $return_course);
//}

$tempdir = make_temp_directory('mod_opencast_tempdownload_' . time() . '_series_' . $series->getExtId() . '_' . preg_replace( '/[^a-zA-Z0-9]+/', '-', $series->title));

/** @var mod_opencast_event[] $clips */
$fileno = 0;
foreach ($nonverified_clips as $clip) {
    $mod_opencast_clip = new mod_opencast_event($series, $clip->identifier, false, $opencast->id);
    $gotourl = $mod_opencast_clip->getLinkMov();
    $ownerid = $mod_opencast_clip->getOwnerUserId();
    $owner = $DB->get_record('user', ['id' => $ownerid]);
    if (!$gotourl) {
        continue; // some videos are failed or not dowloadable/visible anyway
    }
    $parts = parse_url($gotourl);
    parse_str($parts['query'], $query);
    $url = base64_decode($query['url']);
    $time = time();
    $validity_time_seconds = 60;
    $valid_until = $time + $validity_time_seconds;
    $signed_url = mod_opencast_apicall::sendRequest('/security/sign', 'POST', [
            'url'          => $url,
            'valid-until'  => date('Y-m-d', $valid_until) . 'T' . gmdate('H:i:s', $valid_until) . 'Z'
    ]);
    $fileno++;
    $filename = $tempdir . DIRECTORY_SEPARATOR . 'file' . str_pad($fileno, 4, '0', STR_PAD_LEFT)  . '-' . preg_replace( '/[^a-zA-Z0-9]+/', '-', $owner->email) . '-' . preg_replace( '/[^a-zA-Z0-9]+/', '-', $mod_opencast_clip->title) . '.mp4';
    exec('curl "' . $signed_url->url . '" -o ' . $filename . ' > /dev/null 2>&1');
    mtrace('file ' . $fileno . '/' . $nbclips . ' : ' . $filename . ' saved');
}

mtrace('done – ' . $fileno . ' files saved in ' . $tempdir);

