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
 * @author     Nicolas Dunand <Nicolas.Dunand@unil.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

require_once($CFG->dirroot . '/mod/opencast/lib.php');
require_once($CFG->libdir . '/completionlib.php');

$id = required_param('id', PARAM_INT);                 // Course Module ID

$url = new moodle_url('/mod/opencast/view.php', ['id' => $id]);

$PAGE->set_url($url);
$PAGE->requires->jquery();

if (!$cm = get_coursemodule_from_id('opencast', $id)) {
    print_error('invalidcoursemodule');
}

if (!$course = $DB->get_record("course", ["id" => $cm->course])) {
    print_error('coursemisconf');
}

$return_course = new moodle_url('/course/view.php', ['id' => $course->id]);

require_course_login($course, false, $cm);

if (!$opencast = opencast_get_opencast($cm->instance)) {
    print_error('invalidcoursemodule', null, $return_course);
}

if (!$context = context_module::instance($cm->id)) {
    print_error('badcontext', null, $return_course);
}

if (!in_array($opencast->organization_domain, mod_opencast_series::getEnabledOrgnanizations())) {
    // TODO remove as we're now only using local org (i.e. unil.ch for us)
//    print_error('badorganization', 'opencast', $return_course);
}

$PAGE->set_title(format_string($opencast->name));
$PAGE->set_heading($course->fullname);

/// Mark as viewed
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$eventparams = ['context' => $context, 'objectid' => $opencast->id];
$event = \mod_opencast\event\course_module_viewed::create($eventparams);
$event->add_record_snapshot('course_modules', $cm);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('opencast', $opencast);
$event->trigger();

$allclips = [];

echo $OUTPUT->header();

$renderer = $PAGE->get_renderer('mod_opencast');
$renderer->display_channel_content();

echo $OUTPUT->footer();

mod_opencast_series::processUploadedClips();
