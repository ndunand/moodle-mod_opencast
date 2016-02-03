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

if (!isloggedin()) {
    $error = ['error' => get_string('loggedout', 'opencast')];
    echo json_encode($error);
    exit;
}

require_once($CFG->dirroot . '/mod/opencast/lib.php');

$id = required_param('id', PARAM_INT);
$filterstr = optional_param('filterstr', '', PARAM_RAW_TRIMMED);
$sortkey = optional_param('sortkey', 'sortablerecordingdate', PARAM_ALPHAEXT);
$sortdir = optional_param('sortdir', 'asc', PARAM_ALPHA);
$offset = optional_param('offset', 0, PARAM_INT);
if (!isset($SESSION->modopencast_clipsperpage)) {
    $SESSION->modopencast_clipsperpage = 10;
}
$length = optional_param('length', $SESSION->modopencast_clipsperpage, PARAM_INT);
$SESSION->modopencast_clipsperpage = $length;

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

$allclips = [];

$sc_obj = new mod_opencast_series();
$sc_obj->fetch($opencast->id, true);

$sc_user = new mod_opencast_user();

$arr_filter = [];
$filters = explode('&', urldecode($filterstr));
foreach ($filters as $filter) {
    $parts = explode('=', $filter);
    if (count($parts) == 2) {
        $arr_filter[$parts[0]] = $parts[1];
    }
}

$xml_clips = $sc_obj->getEvents($arr_filter);
$xml_clips_access_allowed = $sc_obj->checkAccess($xml_clips);
$clips = [];
foreach ($xml_clips_access_allowed as $xml_clip) {
    $clips[] = (array)$xml_clip;
}

if (mod_opencast_series::getValueForKey('display_select_columns')) {
    $xml_all_clips = $sc_obj->getEvents();
    $xml_all_clips_access_allowed = $sc_obj->checkAccess($xml_all_clips);
    $all_clips = [];
    foreach ($xml_all_clips_access_allowed as $xml_all_clip) {
        $all_clips[] = (array)$xml_all_clip;
    }
}

$clip_objs = [];

foreach ($clips as $clip) {

    if (!isset($allclips[$clip['identifier']])) {
        $mod_opencast_clip = new mod_opencast_event($sc_obj, $clip['identifier'], false, $opencast->id);
        $allclips[$clip['identifier']] = $mod_opencast_clip;
    }
    else {
        $mod_opencast_clip = $allclips[$clip['identifier']];
    }
    $title = $mod_opencast_clip->getTitle();
    if ($title == '') {
        $mod_opencast_clip->setTitle(get_string('untitled_clip', 'opencast'));
    }
    $dtime = DateTime::createFromFormat("Y-m-d\TH:i:s\Z", $clip['start']);
    $timestamp = $dtime->getTimestamp();
    $mod_opencast_clip->setRecordingDate(userdate($timestamp));
    $mod_opencast_clip->editdetails_page = '#opencast-inactive';
    //    $mod_opencast_clip->editclip_page = '#opencast-inactive';
    $mod_opencast_clip->deleteclip_page = '#opencast-inactive';
    $mod_opencast_clip->clipmembers_page = '#opencast-inactive';
    if (has_capability('mod/opencast:isproducer', $context)) {
        // current USER is channel producer in Moodle (i.e. Teacher)
        $mod_opencast_clip->editdetails_page =
                $CFG->wwwroot . '/mod/opencast/event_editdetails.php?id=' . $cm->id . '&clip_identifier=' . $mod_opencast_clip->getExtId();
        $mod_opencast_clip->deleteclip_page =
                $CFG->wwwroot . '/mod/opencast/event_delete.php?id=' . $cm->id . '&clip_ext_id=' . $mod_opencast_clip->getExtId();
    }
    if ($mod_opencast_clip->getOwnerUserId() == $USER->id) {
        // current USER is clip owner
        if ($sc_obj->getIvt() && $sc_obj->getInvitingPossible()) {
            $mod_opencast_clip->clipmembers_page =
                    $CFG->wwwroot . '/mod/opencast/event_members.php?id=' . $cm->id . '&clip_identifier=' . $mod_opencast_clip->getExtId();
        }
    }
    $owner = $mod_opencast_clip->getOwner();
    unset($mod_opencast_clip->owner); // we don't want SWITCHaai uniqueID to appear in the JSON
    if ($owner == '') {
        $mod_opencast_clip->owner_name = '';
    }
    else {
        $owner_moodle_id = mod_opencast_user::getMoodleUserIdFromExtId($owner);
        if ($owner_moodle_user = $DB->get_record('user', ['id' => $owner_moodle_id])) {
            $mod_opencast_clip->owner_name = $owner_moodle_user->lastname . ', ' . $owner_moodle_user->firstname;
        }
        else {
            $mod_opencast_clip->owner_name = get_string('owner_not_in_moodle', 'opencast');
        }
    }
    //    if (!$mod_opencast_clip->AnnotationLink) {
    //        // hack because if present it will fill our template
    //        unset($mod_opencast_clip->AnnotationLink);
    //    }

    $clip_objs[] = $mod_opencast_clip;
}

if (mod_opencast_series::getValueForKey('display_select_columns')) {
    $all_clip_objs = [];
    foreach ($all_clips as $clip) {

        if (!isset($allclips[$clip['identifier']])) {

            $mod_opencast_clip = new mod_opencast_event($sc_obj, $clip['identifier'], false, $opencast->id);
            $mod_opencast_clip->editdetails_page = '#opencast-inactive';
            $mod_opencast_clip->deleteclip_page = '#opencast-inactive';
            $mod_opencast_clip->clipmembers_page = '#opencast-inactive';
            if (has_capability('mod/opencast:isproducer', $context)) {
                // current USER is channel producer in Moodle (i.e. Teacher)
                if ($sc_obj->getIvt()) {
                    $mod_opencast_clip->editdetails_page = '#some-page';
                }
                if ($sc_obj->isProducer($sc_user->getExternalAccount())) {
                    // current user is actual SwitchCast producer
                    $mod_opencast_clip->deleteclip_page = '#some-page';
                }
            }
            if ($mod_opencast_clip->getOwnerUserId() == $USER->id) {
                // current USER is clip owner
                if ($sc_obj->getIvt() && $sc_obj->getInvitingPossible()) {
                    $mod_opencast_clip->clipmembers_page = '#some-page';
                }
            }
            $owner = $mod_opencast_clip->getOwner();
            unset($mod_opencast_clip->owner); // we don't want SWITCHaai uniqueID to appear in the JSON output
            if ($owner == '') {
                $mod_opencast_clip->owner_name = '';
            }
            else {
                $mod_opencast_clip->owner_name = 'SOME_NAME';
            }

            $allclips[$clip['identifier']] = $mod_opencast_clip;
        }

        else {
            $mod_opencast_clip = $allclips[$clip['identifier']];
        }

        $all_clip_objs[] = $mod_opencast_clip;
    }
}

usort($clip_objs, 'opencast_clip_sort');

if ($sortdir == 'desc') {
    $clip_objs = array_reverse($clip_objs);
}

$visible_clips = array_slice($clip_objs, $offset, $length);

$json = ['count' => count($clip_objs), 'clips' => $visible_clips,];

if (mod_opencast_series::getValueForKey('display_select_columns')) {
    $json['allclips'] = $all_clip_objs;
}

echo json_encode($json);

function opencast_clip_sort($a, $b) {
    global $sortkey;
    if ($a->$sortkey > $b->$sortkey) {
        return 1;
    }
    else {
        return -1;
    }

    return 0;
}

