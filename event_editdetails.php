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
$clip_identifier = required_param('clip_identifier', PARAM_RAW_TRIMMED);   // Clip ext_id
$action = optional_param('action', '', PARAM_ALPHA);
$userid = optional_param('userid', 0, PARAM_INT);

$url = new moodle_url('/mod/opencast/event_editdetails.php', ['id' => $id, 'clip_identifier' => $clip_identifier]);
$return_channel = new moodle_url('/mod/opencast/view.php', ['id' => $id]);

if ($userid > 0) {
    //    $url->param('setowner', $userid);
    if (!$setuser = $DB->get_record('user', ['id' => $userid])) {
        print_error('invaliduser', null, $url);
    }
}

$PAGE->set_url($url);
$PAGE->requires->jquery();

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

$sc_obj = new mod_opencast_series();
$sc_obj->fetch($opencast->id);
$sc_clip = new mod_opencast_event($sc_obj, $clip_identifier, false, $opencast->id);

// Perform action ?
if (in_array($action, ['edit']) && confirm_sesskey() && has_capability('mod/opencast:isproducer', $context)
) {
    /*
     * $confirm
     * AND sesskey() ok
     * AND has producer rights
     */
    if ($action === 'edit') {
        $sc_clip->setTitle(optional_param('title', $sc_clip->getTitle(), PARAM_RAW_TRIMMED));
        $sc_clip->setSubtitle(optional_param('subtitle', $sc_clip->getSubtitle(), PARAM_RAW_TRIMMED));
        $sc_clip->setPresenter(optional_param('presenter', $sc_clip->getPresenter(), PARAM_RAW_TRIMMED));
        $sc_clip->setLocation(optional_param('location', $sc_clip->getLocation(), PARAM_RAW_TRIMMED));
        if ($userid !== 0) {
            if ($userid == -1) {
                $sc_clip->setOwner('');
            }
            else {
                $sc_user = new mod_opencast_user(null, $userid);
                $newowner_aaiUniqueId = $sc_user->getExternalAccount();
                if ($newowner_aaiUniqueId) {
                    $newowner = new mod_opencast_user($newowner_aaiUniqueId);
                    $sc_clip->setOwner($newowner_aaiUniqueId);
                }
                else {
                    print_error('owner_no_switch_account', 'opencast', $url,
                            $setuser->lastname . ', ' . $setuser->firstname);
                }
            }
        }
        $sc_clip->update();
        $eventparams = ['context' => $context, 'objectid' => $opencast->id];
        $event = \mod_opencast\event\clip_editdetails::create($eventparams);
        $event->add_record_snapshot('course_modules', $cm);
        $event->add_record_snapshot('course', $course);
        $event->add_record_snapshot('opencast', $opencast);
        $event->trigger();
    }

    redirect($url);
}

// Display

$PAGE->set_title(format_string($opencast->name));
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();

$renderer = $PAGE->get_renderer('mod_opencast');

echo html_writer::tag('h2', get_string('set_clip_details', 'opencast'));
echo html_writer::tag('p', get_string('set_clip_details_warning', 'opencast'), ['class' => 'notify']);
echo html_writer::start_tag('table', ['class' => 'opencast-clips']);
$renderer->display_singleclip_table_header(false, true, $opencast->userupload, false);
$renderer->display_clip_outline($sc_clip, false, false, null, true, $opencast->userupload, false, false);
echo html_writer::end_tag('table');

if (has_capability('mod/opencast:isproducer', $context)) {
    echo html_writer::start_tag('form',
            ['method' => 'post', 'action' => 'event_editdetails.php', 'class' => 'details border']);
    echo html_writer::input_hidden_params($url, ['action', 'userid']);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'action', 'value' => 'edit']);
    echo html_writer::start_div();
    echo html_writer::tag('span', get_string('title', 'opencast'));
    echo html_writer::empty_tag('input', ['type' => 'text', 'name' => 'title', 'value' => $sc_clip->getTitle()]);
    echo html_writer::end_div();
    echo html_writer::start_div();
    echo html_writer::tag('span', get_string('subtitle', 'opencast'));
    echo html_writer::empty_tag('input', ['type' => 'text', 'name' => 'subtitle', 'value' => $sc_clip->getSubtitle()]);
    echo html_writer::end_div();
    echo html_writer::start_div();
    echo html_writer::tag('span', get_string('presenter', 'opencast'));
    echo html_writer::empty_tag('input',
            ['type' => 'text', 'name' => 'presenter', 'value' => $sc_clip->getPresenter()]);
    echo html_writer::end_div();
    echo html_writer::start_div();
    echo html_writer::tag('span', get_string('location', 'opencast'));
    echo html_writer::empty_tag('input', ['type' => 'text', 'name' => 'location', 'value' => $sc_clip->getLocation()]);
    echo html_writer::end_div();
    echo html_writer::start_div();
    echo html_writer::tag('span', get_string('owner', 'opencast'));
    $renderer->display_user_selector(true, 'event_editdetails.php', get_string('setnewowner', 'opencast'), true, true,
            true, $sc_clip->getOwnerUserId());
    echo html_writer::end_div();
    echo html_writer::start_div();
    echo html_writer::tag('span', '&nbsp;');
    echo html_writer::empty_tag('input', ['type' => 'submit', 'value' => get_string('savechanges'), 'class' => 'btn btn-primary']);
    echo html_writer::end_div();
    echo html_writer::end_tag('form');
}

/*
if ( $USER->id == $sc_clip->getOwnerUserId() || has_capability('mod/opencast:isproducer', $context) ) {
    $renderer->display_user_selector(true, 'event_editdetails.php', get_string('setnewowner', 'opencast'), true, true);
    echo html_writer::start_tag('form', array('method' => 'post', 'action' => 'event_editdetails.php', 'class' => 'border'));
    echo html_writer::input_hidden_params($url, array('action', 'userid'));
    echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()));
    echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'action', 'value' => 'remove'));
    echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'userid', 'value' => '-1'));
    echo html_writer::empty_tag('input', array('type' => 'submit', 'value' => get_string('removeowner', 'opencast')));
    echo html_writer::end_tag('form');
}
*/

echo html_writer::link($return_channel, get_string('back_to_channel', 'opencast'));

echo $OUTPUT->footer();

