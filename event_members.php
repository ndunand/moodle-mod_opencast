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
$clip_ext_id = required_param('clip_identifier', PARAM_RAW_TRIMMED);   // Clip ext_id
$action = optional_param('action', '', PARAM_ALPHA);
$userid = optional_param('userid', 0, PARAM_INT);

$url = new moodle_url('/mod/opencast/event_members.php', ['id' => $id, 'clip_identifier' => $clip_ext_id]);
$return_channel = new moodle_url('/mod/opencast/view.php', ['id' => $id]);

//if ($action !== '' && $userid !== 0) {
//    $url->param('action', $action);
//    $url->param('userid', $userid);
//}

$PAGE->set_url($url);

if (!$cm = get_coursemodule_from_id('opencast', $id)) {
    print_error('invalidcoursemodule');
}

if (!$context = context_module::instance($cm->id)) {
    print_error('badcontext', null, $return_course);
}

if (!$course = $DB->get_record('course', ['id' => $cm->course])) {
    print_error('coursemisconf');
}

$return_course = new moodle_url('/course/view.php', ['id' => $course->id]);

if ($userid !== 0 && !$user = $DB->get_record('user', ['id' => $userid])) {
    print_error('invaliduser', null, $return_channel);
}

if ($action !== '' && !in_array($action, ['add', 'remove'])) {
    print_error('invalidaction', null, $return_channel);
}

require_course_login($course, false, $cm);

if (!$opencast = opencast_get_opencast($cm->instance)) {
    print_error('invalidcoursemodule', null, $return_course);
}

$sc_obj = new mod_opencast_series();
$sc_obj->fetch($opencast->id);
$sc_clip = new mod_opencast_event($sc_obj, $clip_ext_id, false, $opencast->id);

if ($sc_clip->getOwnerUserId() != $USER->id) {
    print_error('invalidaction', null, $return_channel);
}

// Perform action ?
if ($action !== '' && $userid !== 0 && confirm_sesskey() && has_capability('mod/opencast:use',
                $context) && $opencast->inviting && ($USER->id == $sc_clip->getOwnerUserId() || has_capability('mod/opencast:isproducer',
                        $context))
) {
    /*
     * $action AND $userid are defined
     * and channel allows clip inviting
     * and $USER has rights and is clip owner
     */

    if ($action == 'add') {
        $sc_clip->addMember($userid, $course->id, $opencast->id);
    }
    else if ($action == 'remove') {
        $sc_clip->removeMember($userid, $course->id, $opencast->id);
    }

    $eventparams = ['context' => $context, 'objectid' => $opencast->id, 'relateduserid' => $userid];
    if ($action == 'add') {
        $event = \mod_opencast\event\member_invited::create($eventparams);
    }
    else if ($action == 'remove') {
        $event = \mod_opencast\event\member_revoked::create($eventparams);
    }
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('opencast', $opencast);
    $event->trigger();

    redirect($url);
}

else if ($action !== '') {
    print_error('actionnotallowed', null, $return_channel);
}

// Display

$PAGE->set_title(format_string($opencast->name));
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();

$renderer = $PAGE->get_renderer('mod_opencast');

echo html_writer::tag('h2', get_string('editmembers_long', 'opencast'));

echo html_writer::start_tag('table', ['class' => 'opencast-clips']);
$renderer->display_singleclip_table_header(false, true, $opencast->userupload, false);
$renderer->display_clip_outline($sc_clip, false, false, null, true, $opencast->userupload, false, false);
echo html_writer::end_tag('table');

$renderer->display_clip_members();
$renderer->display_user_selector(false, 'event_members.php', get_string('addmember', 'opencast'));

echo html_writer::link($return_channel, get_string('back_to_channel', 'opencast'));

echo $OUTPUT->footer();
