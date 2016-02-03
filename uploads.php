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

$id = required_param('id', PARAM_INT); // Course Module ID

$url = new moodle_url('/mod/opencast/uploads.php', ['id' => $id]);
$return_channel = new moodle_url('/mod/opencast/view.php', ['id' => $id]);

$PAGE->set_url($url);

if (!$cm = get_coursemodule_from_id('opencast', $id)) {
    print_error('invalidcoursemodule');
}

if (!$course = $DB->get_record('course', ['id' => $cm->course])) {
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

if (!has_capability('mod/opencast:isproducer', $context)) {
    print_error('feature_forbidden', 'opencast', $return_channel);
}

$sc_obj = new mod_opencast_series();
$sc_obj->fetch($opencast->id);

// Display

$PAGE->set_title(format_string($opencast->name));
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();
$renderer = $PAGE->get_renderer('mod_opencast');
echo html_writer::tag('h2', get_string('uploaded_clips', 'opencast'));
$renderer->display_user_pending_clips(true, true, true, true, $sc_obj->getIvt());
echo html_writer::link($return_channel, get_string('back_to_channel', 'opencast'));
echo $OUTPUT->footer();

