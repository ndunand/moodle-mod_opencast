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

require_once('../../config.php');
require_once($CFG->dirroot . '/mod/opencast/lib.php');

$id = required_param('id', PARAM_INT);                 // Course Module ID
$event_identifier = required_param('clip_ext_id', PARAM_RAW_TRIMMED);   // Clip ext_id
$confirm = optional_param('confirm', 0, PARAM_INT);

$url = new moodle_url('/mod/opencast/event_delete.php', ['id' => $id, 'clip_ext_id' => $event_identifier]);
$return_channel = new moodle_url('/mod/opencast/view.php', ['id' => $id]);

if ($confirm !== 0) {
    $url->param('confirm', $confirm);
}

$PAGE->set_url($url);

if (!$cm = get_coursemodule_from_id('opencast', $id)) {
    print_error('invalidcoursemodule');
}

if (!$course = $DB->get_record('course', ['id' => $cm->course])) {
    print_error('coursemisconf');
}

require_course_login($course, false, $cm);

if (!$opencast = opencast_get_opencast($cm->instance)) {
    print_error('invalidcoursemodule');
}

if (!$context = context_module::instance($cm->id)) {
    print_error('badcontext');
}

if (!has_capability('mod/opencast:isproducer', $context)) {
    print_error('feature_forbidden', 'opencast', $return_channel);
}

$sc_obj = new mod_opencast_series();
$sc_obj->fetch($opencast->id);
$sc_clip = new mod_opencast_event($sc_obj, $event_identifier, false, $opencast->id);

// Perform action ?
if ($confirm === 1 && confirm_sesskey() && has_capability('mod/opencast:isproducer', $context)
) {
    /*
     * $confirm
     * AND sesskey() ok
     * AND $USER has producer rights
     */
    $sc_clip->delete();

    $eventparams = ['context' => $context, 'objectid' => $opencast->id];
    $event = \mod_opencast\event\clip_deleted::create($eventparams);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('opencast', $opencast);
    $event->trigger();

    redirect($return_channel);
}

// Display

$PAGE->set_title(format_string($opencast->name));
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();

$renderer = $PAGE->get_renderer('mod_opencast');

echo html_writer::tag('h2', get_string('delete_clip', 'opencast'));
echo html_writer::start_tag('table', ['class' => 'opencast-clips']);
$renderer->display_singleclip_table_header(false, $sc_obj->getIvt(), $opencast->userupload, false);
$renderer->display_clip_outline($sc_clip, false, false, null, $sc_obj->getIvt(), $opencast->userupload, false);
echo html_writer::end_tag('table');

$delete_url = new moodle_url('/mod/opencast/event_delete.php',
        ['sesskey' => sesskey(), 'confirm' => 1, 'id' => $id, 'clip_ext_id' => $event_identifier]);
$button = new single_button($delete_url, get_string('delete_clip', 'opencast'), 'post');
echo $OUTPUT->confirm(get_string('delete_clip_confirm', 'opencast'), $button, $return_channel);
echo $OUTPUT->footer();

