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
 * @copyright  2013-2017 Université de Lausanne
 * @author     Nicolas.Dunand@unil.ch
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/user/files_form.php');
require_once($CFG->dirroot . '/repository/lib.php');
require_once($CFG->dirroot . '/mod/opencast/lib.php');

$id = required_param('id', PARAM_INT); // Course Module ID

$url = new moodle_url('/mod/opencast/upload_event.php', ['id' => $id]);
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

$sc_obj = new mod_opencast_series();
$sc_obj->fetch($opencast->id);

// Display

$PAGE->set_title(format_string($opencast->name));
$PAGE->set_heading($course->fullname);

if ($opencast->userupload) {
    $maxbytes = min(mod_opencast_series::getValueForKey('userupload_maxfilesize'), $opencast->userupload_maxfilesize);
}
else {
    $maxbytes = mod_opencast_series::getValueForKey('userupload_maxfilesize');
}

$usercontext = context_user::instance($USER->id);

$data = new stdClass();
$data->returnurl = $return_channel;
$options = [
        'subdirs'      => 0, 'maxbytes' => $maxbytes, 'maxfiles' => 1, 'accepted_types' => ['video'],
        'areamaxbytes' => $maxbytes
];
file_prepare_standard_filemanager($data, 'files', $options, $usercontext, 'mod_opencast', 'userfiles', $id);

$mform = new mod_opencast_upload_form($url, ['data' => $data, 'options' => $options]);

if ($mform->is_cancelled()) {
    redirect($return_channel);
}
else if ($formdata = $mform->get_data()) {
    $formdata = file_postupdate_standard_filemanager($formdata, 'files', $options, $usercontext, 'mod_opencast',
            'userfiles', $id);
    $fs = get_file_storage();
    $files = $fs->get_area_files($usercontext->id, 'mod_opencast', 'userfiles', $id);
    foreach ($files as $file) {
        $filesize = $file->get_filesize();
        if (!$filesize) {
            $file->delete();
            continue;
        }
        if ($file->get_mimetype() && substr($file->get_mimetype(), 0, 5) !== 'video') {
            $file->delete();
            print_error('fileis_notavideo', 'opencast', $url, $file->get_mimetype());
        }

        $filename = $file->get_filename();
        preg_match('/\.([^.]+)$/', $filename, $extension);

        if (!in_array(strtolower($extension[1]), mod_opencast_series::getAllowedFileExtensions())) {
            $file->delete();
            $a = new stdClass();
            $a->yours = $extension[1];
            $a->allowed = implode(', ', mod_opencast_series::getAllowedFileExtensions());
            print_error('fileis_notextensionallowed', 'opencast', $url, $a);
        }

        $filetoupload = $CFG->tempdir . '/files/mod_opencast_' . md5(microtime()) . '.' . $extension[1];
        $a_file = $file->copy_content_to_temp();
        rename($a_file, $filetoupload);

        try {
            $result = $sc_obj->createClip([
                    'title'      => $formdata->cliptitle, 'subtitle' => $formdata->clipsubtitle,
                    'presenter'  => $formdata->clippresenter, 'location' => $formdata->cliplocation,
                    'ivt__owner' => mod_opencast_user::getExtIdFromMoodleUserId($USER->id),
                    'filename'   => $filetoupload
            ]);
        }
        catch (Exception $e) {
            unlink($filetoupload);
            $file->delete();
            $retryurl = new moodle_url($url, ['formdata' => serialize($formdata)]);
            print_error('userupload_error', 'opencast', $retryurl);
        }
        unlink($filetoupload);
        $file->delete();
    }
}

if (isset($formdata) && isset($result)) {
    // data submitted: record file upload
    $uploaded_clip = new stdClass();
    $uploaded_clip->userid = $USER->id;
    $uploaded_clip->filename = $filename;
    $uploaded_clip->filesize = $filesize;
    $uploaded_clip->opencastid = $opencast->id;
    $uploaded_clip->timestamp = time();
    $uploaded_clip->title = $formdata->cliptitle;
    $uploaded_clip->subtitle = $formdata->clipsubtitle;
    $uploaded_clip->presenter = $formdata->clippresenter;
    $uploaded_clip->location = $formdata->cliplocation;
    if ($result !== false) {
        $uploaded_clip->ext_id = (string)$result->identifier;
        $uploaded_clip->status = OPENCAST_CLIP_UPLOADED;
    }
    else {
        $uploaded_clip->status = OPENCAST_CLIP_TRYAGAIN;
    }
    if (!$DB->insert_record('opencast_uploadedclip', $uploaded_clip)) {
        print_error('error');
    }
    $eventparams = ['context' => $context, 'objectid' => $opencast->id];
    $event = \mod_opencast\event\clip_uploaded::create($eventparams);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('opencast', $opencast);
    $event->trigger();
    redirect($return_channel);
}
else {
    // no data submitted yet; display recap & form
    echo $OUTPUT->header();
    $renderer = $PAGE->get_renderer('mod_opencast');
    echo html_writer::tag('h2', get_string('upload_clip', 'opencast'));
    $upl_help = $OUTPUT->help_icon('upload_clip_misc', 'opencast', '');
    $upl_a = mod_opencast_series::getValueForKey('uploadfile_extensions');
    echo html_writer::tag('p', get_string('upload_clip_info', 'opencast', $upl_a) . $upl_help);
    $renderer->display_user_pending_clips(true, true, false, false, false);
    // The following two set_context()'s are a dirty hack, but we have to do this,
    // otherwise the couse/site maxbytes limit is enforced.
    // (see MoodleQuickForm_filemanager class constructor)
    $PAGE->set_context($usercontext);
    if ($formdata = unserialize(optional_param('formdata', '', PARAM_RAW_TRIMMED))) {
        $mform->set_data($formdata);
    }
    $mform->display();
    $PAGE->set_context($context);
    echo html_writer::link($return_channel, get_string('back_to_channel', 'opencast'));
    echo $OUTPUT->footer();
}

