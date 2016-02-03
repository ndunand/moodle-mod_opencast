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

defined('MOODLE_INTERNAL') || die();

define ('DISPLAY_HORIZONTAL_LAYOUT', 0);
define ('DISPLAY_VERTICAL_LAYOUT', 1);

class mod_opencast_renderer extends plugin_renderer_base {

    protected $displayed_userids;

    /**
     * Constructor
     *
     * @param moodle_page $page
     * @param string      $target one of rendering target constants
     */
    public function __construct(moodle_page $page, $target) {
        global $opencast, $SESSION;
        $SESSION->opencastid = $opencast->id;
        $this->series = new mod_opencast_series();
        $this->series->fetch($opencast->id);
        $this->scuser = new mod_opencast_user();
        $this->displayed_userids = [];
        parent::__construct($page, $target);
    }

    /**
     * Displays channel header + content
     *
     */
    function display_channel_content() {

        global $context, $PAGE, $allclips;

        $PAGE->requires->js('/mod/opencast/js/pure.js');
        $PAGE->requires->js('/mod/opencast/js/get_clips.js');

        /*
         * Register the User as SwitchCast producer if necessary,
         * and remove him from the producers list if needed.
         */
        if ($this->scuser->getExternalAccount()) {
            if (has_capability('mod/opencast:isproducer', $context)) {
                // add as producer, if not already
                if (!$this->series->isProducer($this->scuser->getExternalAccount())) {
                    $this->series->addProducer($this->scuser->getExternalAccount());
                }
            }
            else {
                // remove from producers, if needed
                if ($this->series->isProducer($this->scuser->getExternalAccount())) {
                    $this->series->removeProducer($this->scuser->getExternalAccount());
                }
            }
        }

        $this->display_user_pending_clips(false, true);
        $this->display_channel_outline();

        $nonverified_clips = $this->series->getEvents();

        //        echo '<pre>'; print_r($nonverified_clips); die();

        if (!count($nonverified_clips)) {
            print_string('noclipsinchannel', 'opencast');

            return;
        }
        $this->clips = $this->series->checkAccess($nonverified_clips);
        //        echo '<pre>'; print_r($this->clips); die();

        if (count($this->clips)) {
            echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'table-params']);
            echo html_writer::start_tag('table',
                    ['class' => 'opencast-clips-table opencast-clips', 'id' => 'opencast-clips-table']);
            echo html_writer::start_tag('tr');
            $title_th = html_writer::tag('a', get_string('cliptitle', 'opencast'), ['href' => '#']);
            $title_th .= html_writer::empty_tag('br');
            $title_th .= html_writer::empty_tag('input', ['type' => 'checkbox', 'id' => 'clip-show-subtitle']);
            $title_th .= html_writer::tag('label', get_string('showsubtitles', 'opencast'),
                    ['for' => 'clip-show-subtitle']);
            echo html_writer::tag('th', $title_th, ['class' => 'opencast-sortable', 'data-sortkey' => 'title']);
            echo html_writer::tag('th', html_writer::tag('a', get_string('presenter', 'opencast'), ['href' => '#']),
                    ['class' => 'opencast-presenter opencast-sortable', 'data-sortkey' => 'presenter']);
            echo html_writer::tag('th', html_writer::tag('a', get_string('location', 'opencast'), ['href' => '#']),
                    ['class' => 'opencast-location opencast-sortable', 'data-sortkey' => 'location']);
            echo html_writer::tag('th',
                    html_writer::tag('a', get_string('recordingstation', 'opencast'), ['href' => '#']), [
                            'class'        => 'opencast-recordingstation opencast-sortable',
                            'data-sortkey' => 'recordingstation'
                    ]);
            echo html_writer::tag('th', get_string('date', 'opencast'));
            echo html_writer::tag('th', html_writer::tag('a', get_string('owner', 'opencast'), ['href' => '#']),
                    ['class' => 'opencast-owner opencast-sortable', 'data-sortkey' => 'owner_name']);
            echo html_writer::tag('th', get_string('actions', 'opencast'), ['class' => 'opencast-actions']);
            echo html_writer::end_tag('tr');
            foreach ($this->clips as $clip) {
                //                echo '<pre>'.print_r($clip, true).'</pre>';
                $sc_clip = $allclips[(string)$clip->identifier];
                $this->display_clip_outline($sc_clip, true, true, 'all', $this->series->getIvt());
                break;
                // NOTE ND : we display only one row, that we'll use as a template
            }
            echo html_writer::end_tag('table');
            echo html_writer::tag('div', '', ['class' => 'loading']);
        }
        else {
            print_string('novisibleclipsinchannel', 'opencast');
        }
    }

    /**
     * Display a SWITCHcast channel activity's header
     *
     */
    function display_channel_outline() {
        global $CFG, $OUTPUT, $opencast, $cm, $context, $SESSION;

        if (has_capability('mod/opencast:isproducer',
                        $context) || ($opencast->userupload && has_capability('mod/opencast:uploadclip', $context))
        ) {
            echo html_writer::tag('a', get_string('upload_clip', 'opencast'), [
                            'href'  => $CFG->wwwroot . '/mod/opencast/upload_event.php?id=' . $cm->id,
                            'class' => 'upload button'
                    ]);
        }
        if (has_capability('mod/opencast:isproducer', $context) && $opencast->userupload) {
            echo html_writer::tag('a', get_string('view_useruploads', 'opencast'), [
                            'href'  => $CFG->wwwroot . '/mod/opencast/uploads.php?id=' . $cm->id,
                            'class' => 'upload button'
                    ]);
        }
        if ($this->series->isProducer($this->scuser->getExternalAccount())) {
            //            echo html_writer::tag('a', get_string('upload_clip', 'opencast'), array('href' => $this->scobj->getUploadForm(), 'class' => 'upload button', 'target' => '_blank'));
            echo html_writer::tag('a', get_string('edit_at_switch', 'opencast'),
                    ['href' => $this->series->getEditLink(), 'class' => 'editchannel button', 'target' => '_blank']);
            if ($this->series->hasReferencedChannels() > 1) {
                echo html_writer::tag('div', get_string('channel_several_refs', 'opencast'),
                        ['class' => 'opencast-notice']);
            }
        }

        echo html_writer::tag('h2', $opencast->name);

        if ($opencast->intro) {
            echo $OUTPUT->box(format_module_intro('opencast', $opencast, $cm->id), 'generalbox', 'intro');
        }

        echo html_writer::tag('a', get_string('filters', 'opencast'),
                ['href' => '#', 'class' => 'opencast-filters-toggle']);

        echo html_writer::start_tag('div', ['class' => 'opencast-pagination']);
        echo html_writer::tag('input', '',
                ['type' => 'hidden', 'id' => 'opencast-cmid-hidden-input', 'value' => $cm->id]);
        echo html_writer::start_tag('div', ['class' => 'ajax-controls-perpage']);
        echo html_writer::tag('span', get_string('itemsperpage', 'opencast'));
        $perpage_values = [5, 10, 20, 50, 100];
        $perpage_options = array_combine($perpage_values, $perpage_values);
        $perpage_option_selected =
                isset($SESSION->modopencast_clipsperpage) ? ($SESSION->modopencast_clipsperpage) : (10);
        echo html_writer::select($perpage_options, 'opencast-perpage', $perpage_option_selected);
        echo html_writer::end_tag('div');
        echo html_writer::start_tag('div', ['class' => 'ajax-controls-pageno']);
        echo html_writer::tag('span', get_string('pageno', 'opencast'));
        $pages = [1];
        $pages_options = array_combine($pages, $pages);
        echo html_writer::select($pages_options, 'opencast-pageno', '1');
        echo html_writer::end_tag('div');
        echo html_writer::start_tag('div', ['class' => 'ajax-controls-pagination']);
        echo html_writer::tag('span', get_string('pagination', 'opencast'));
        echo html_writer::end_tag('div');
        echo html_writer::end_tag('div');

        echo html_writer::start_tag('div', ['class' => 'opencast-filters']);
        echo html_writer::start_tag('div', ['class' => 'ajax-controls-title']);
        echo html_writer::tag('span', get_string('title', 'opencast'));
        echo html_writer::empty_tag('input', ['type' => 'text', 'name' => 'opencast-title']);
        echo html_writer::end_tag('div');
        echo html_writer::start_tag('div', ['class' => 'ajax-controls-presenter']);
        echo html_writer::tag('span', get_string('presenter', 'opencast'));
        echo html_writer::empty_tag('input', ['type' => 'text', 'name' => 'opencast-presenter']);
        echo html_writer::end_tag('div');
        echo html_writer::start_tag('div', ['class' => 'ajax-controls-location opencast-location']);
        echo html_writer::tag('span', get_string('location', 'opencast'));
        echo html_writer::empty_tag('input', ['type' => 'text', 'name' => 'opencast-location']);
        echo html_writer::end_tag('div');
        echo html_writer::start_tag('div', ['class' => 'ajax-controls-recordingstation opencast-recordingstation']);
        echo html_writer::tag('span', get_string('recordingstation', 'opencast'));
        echo html_writer::empty_tag('input', ['type' => 'text', 'name' => 'opencast-recordingstation']);
        echo html_writer::end_tag('div');
        echo html_writer::start_tag('div', ['class' => 'ajax-controls-owner opencast-owner']);
        echo html_writer::tag('span', get_string('owner', 'opencast'));
        $owners_records = get_users_by_capability($context, 'mod/opencast:use', 'u.id, u.firstname, u.lastname',
                'u.lastname, u.firstname');
        $owners_options = [];
        foreach ($owners_records as $owner_record) {
            if ($aaiuniqueid = mod_opencast_user::getExtIdFromMoodleUserId($owner_record->id)) {
                $owners_options[$aaiuniqueid] = $owner_record->lastname . ', ' . $owner_record->firstname;
            }
        }
        echo html_writer::select($owners_options, 'opencast-owner');
        echo html_writer::end_tag('div');
        echo html_writer::start_tag('div', ['class' => 'ajax-controls-withoutowner opencast-owner']);
        echo html_writer::tag('span', get_string('withoutowner', 'opencast'));
        echo html_writer::empty_tag('input', ['type' => 'checkbox', 'name' => 'opencast-withoutowner']);
        echo html_writer::end_tag('div');

        echo html_writer::start_tag('div');
        echo html_writer::tag('span', '&nbsp;');
        echo html_writer::tag('button', get_string('resetfilters', 'opencast'), ['class' => 'cancel']);
        echo html_writer::tag('span', '&nbsp;');
        echo html_writer::tag('button', get_string('ok'), ['class' => 'ok']);
        echo html_writer::end_tag('div');

        echo html_writer::end_tag('div');

        echo html_writer::tag('div', '', ['class' => 'clearer']);
    }

    /**
     * Displays a header for singleclip display
     *
     * @param bool $withactions show actions column
     * @param bool $with_owner
     * @param bool $with_uploader
     * @param bool $with_recordingstation
     */
    function display_singleclip_table_header($withactions = false, $with_owner = true, $with_uploader = false,
                                             $with_recordingstation = true) {

        echo html_writer::start_tag('tr');

        echo html_writer::tag('th', get_string('cliptitle', 'opencast'));
        echo html_writer::tag('th', get_string('presenter', 'opencast'), ['class' => 'opencast-presenter']);
        echo html_writer::tag('th', get_string('location', 'opencast'), ['class' => 'opencast-location']);
        if ($with_recordingstation) {
            echo html_writer::tag('th', get_string('recording_station', 'opencast'),
                    ['class' => 'opencast-recordingstation']);
        }
        echo html_writer::tag('th', get_string('date', 'opencast'), ['class' => 'opencast-recordingdate']);
        if ($with_owner) {
            echo html_writer::tag('th', get_string('owner', 'opencast'), ['class' => 'opencast-owner']);
        }
        if ($with_uploader) {
            echo html_writer::tag('th', get_string('uploader', 'opencast'), ['class' => 'opencast-owner']);
        }
        if ($withactions) {
            echo html_writer::tag('th', get_string('actions', 'opencast'), ['class' => 'opencast-actions']);
        }

        echo html_writer::end_tag('tr');
    }

    /**
     * Displays a clip outline in a table row
     *
     * @param mod_opencast_event $sc_clip         a SWITCHcast clip object
     * @param bool                $with_actions    display action buttons
     * @param bool                $is_template     use row as template
     * @param string              $allowed_actions comma separated list of allowed actions, used if $with_actions is true
     * @param bool                $with_owner      display owner column
     * @param bool                $with_uploader   display uploader column
     * @param bool                $with_recordingstation
     * @param bool                $with_playbuttons
     */
    function display_clip_outline(mod_opencast_event $sc_clip, $with_actions = true, $is_template = false,
                                  $allowed_actions = 'all', $with_owner = false, $with_uploader = false,
                                  $with_recordingstation = true, $with_playbuttons = true) {
        global $CFG, $DB, $cm;

        $title = $sc_clip->getTitle();
        if ($title == '') {
            $title = get_string('untitled_clip', 'opencast');
        }
        $subtitle = $sc_clip->getSubtitle();
        $title = html_writer::tag('span', $title, ['class' => 'title']);
        $title .= html_writer::tag('div', $subtitle, ['class' => 'subtitle']);

        $owner = $sc_clip->getOwner();
        if ($owner == '') {
            $owner = get_string('no_owner', 'opencast');
        }
        else {
            $owner_moodle_id = mod_opencast_user::getMoodleUserIdFromExtId($owner);
            if ($owner_moodle_user = $DB->get_record('user', ['id' => $owner_moodle_id])) {
                $owner = $owner_moodle_user->lastname . ', ' . $owner_moodle_user->firstname;
            }
            else {
                $owner = get_string('owner_not_in_moodle', 'opencast');
            }
        }

        $uploader = '';
        if ($with_uploader) {
            $uploaded_clip = $DB->get_record('opencast_uploadedclip', ['ext_id' => $sc_clip->getExtId()]);
            if ($uploaded_clip) {
                if ($uploader_moodle_user = $DB->get_record('user', ['id' => $uploaded_clip->userid])) {
                    $uploader = $uploader_moodle_user->lastname . ', ' . $uploader_moodle_user->firstname;
                }
            }
        }

        if ($is_template) {
            $extraclass = ($this->series->getIvt()) ? ('with-owner') : ('without-owner');
            echo html_writer::start_tag('tr', ['class' => 'opencast-clip-template-row ' . $extraclass]);
        }
        else {
            echo html_writer::start_tag('tr');
        }

        echo html_writer::start_tag('td');
        echo html_writer::start_tag('div', ['class' => 'cliplabel', 'title' => $subtitle]);
        echo html_writer::empty_tag('img', ['src' => $sc_clip->getCover()]);
        echo html_writer::tag('h3', $title);
        if ($with_playbuttons) {
            echo html_writer::start_tag('div', ['class' => 'linkbar']);
            //            echo html_writer::tag('span', $sc_clip->getLinkBox());
            if ($is_template) {
                echo html_writer::tag('a', '', [
                                'href'  => '#opencast-inactive', 'title' => get_string('annotations', 'opencast'),
                                'class' => 'annotate', 'target' => '_blank'
                        ]);
            }
            else if ($this->series->getAllowAnnotations()) {
                echo html_writer::tag('a', '', [
                                'href'   => $sc_clip->getAnnotationLink(),
                                'title'  => get_string('annotations', 'opencast'), 'class' => 'annotate',
                                'target' => '_blank'
                        ]);
            }
            echo html_writer::tag('a', '', [
                            'href'  => $sc_clip->getLinkFlash(), 'title' => get_string('flash', 'opencast'),
                            'class' => 'flash', 'target' => '_blank'
                    ]);
            //        echo html_writer::tag('span', $sc_clip->getLinkMp4());
            echo html_writer::tag('a', '', [
                            'href'  => $sc_clip->getLinkMov(), 'title' => get_string('mov', 'opencast'),
                            'class' => 'mov', 'target' => '_blank'
                    ]);
//            echo html_writer::tag('a', '', [
//                            'href'  => $sc_clip->getLinkM4v(), 'title' => get_string('m4v', 'opencast'),
//                            'class' => 'm4v', 'target' => '_blank'
//                    ]);
            //        echo html_writer::tag('span', $sc_clip->getSubtitle());
            echo html_writer::end_tag('div');
        }
        echo html_writer::end_tag('div');
        echo html_writer::end_tag('td');

        echo html_writer::start_tag('td', ['class' => 'opencast-presenter']);
        echo html_writer::tag('span', $sc_clip->getPresenter());
        echo html_writer::end_tag('td');

        echo html_writer::start_tag('td', ['class' => 'opencast-location']);
        echo html_writer::tag('span', $sc_clip->getLocation());
        echo html_writer::end_tag('td');

        if ($with_recordingstation) {
            echo html_writer::start_tag('td', ['class' => 'opencast-recordingstation']);
            echo html_writer::tag('span', $sc_clip->getRecordingStation());
            echo html_writer::end_tag('td');
        }

        echo html_writer::start_tag('td', ['class' => 'opencast-recordingdate']);
        echo html_writer::tag('span', $sc_clip->getRecordingDate());
        echo html_writer::end_tag('td');

        if ($is_template || $with_owner) {
            echo html_writer::start_tag('td', ['class' => 'opencast-owner']);
            echo html_writer::tag('span', $owner);
            echo html_writer::end_tag('td');
        }

        if ($with_uploader) {
            echo html_writer::start_tag('td', ['class' => 'opencast-uploader']);
            echo html_writer::tag('span', $uploader);
            echo html_writer::end_tag('td');
        }

        $allowed_actions = explode(',', $allowed_actions);
        if ($with_actions && count($allowed_actions)) {
            echo html_writer::start_tag('td', ['class' => 'opencast-actions']);
            echo html_writer::start_tag('div', ['class' => 'opencast-hidden-actions']);
            if (in_array('editdetails', $allowed_actions) || in_array('all', $allowed_actions)) {
                echo html_writer::tag('a', get_string('editdetails', 'opencast'), [
                                'href'  => $CFG->wwwroot . '/mod/opencast/event_editdetails.php?id=' . $cm->id . '&clip_ext_id=' . $sc_clip->getExtId(),
                                'class' => 'button opencast-editdetails'
                        ]);
            }
            if (in_array('invite', $allowed_actions) || in_array('all', $allowed_actions)) {
                echo html_writer::tag('a', get_string('editmembers', 'opencast'), [
                                'href'  => $CFG->wwwroot . '/mod/opencast/event_members.php?id=' . $cm->id . '&clip_identifier=' . $sc_clip->getExtId(),
                                'class' => 'button opencast-clipmembers'
                        ]);
            }
            if (in_array('delete', $allowed_actions) || in_array('all', $allowed_actions)) {
                echo html_writer::tag('a', get_string('delete_clip', 'opencast'), [
                                'href'  => $CFG->wwwroot . '/mod/opencast/event_delete.php?id=' . $cm->id . '&clip_ext_id=' . $sc_clip->getExtId(),
                                'class' => 'button opencast-deleteclip'
                        ]);
            }
            echo html_writer::end_tag('div');
            echo html_writer::end_tag('td');
        }

        echo html_writer::end_tag('tr');
    }

    /**
     * Displays user outlines of each channel teacher (for the clip members table)
     *
     */
    function display_channel_teachers() {
        global $context;
        $teachers = get_users_by_capability($context, 'mod/opencast:seeallclips', 'u.id');
        foreach ($teachers as $teacher) {
            $this->display_user_outline($teacher->id, false, true);
        }
    }

    /**
     * Displays user outlines of the clip owner (for the clip members table)
     *
     */
    function display_clip_owner() {
        global $sc_clip;
        $owner_moodle_id = mod_opencast_user::getMoodleUserIdFromExtId($sc_clip->getOwner());
        if ($owner_moodle_id) {
            $this->display_user_outline($owner_moodle_id, false, false, false);
        }
    }

    /**
     * Displays user outlines of the clip uploader (for the clip members table)
     *
     */
    function display_clip_uploader() {
        global $sc_clip, $DB;
        $record = $DB->get_record('opencast_uploadedclip', ['ext_id' => $sc_clip->getExtId()]);
        if ($record) {
            $this->display_user_outline($record->userid, false, false, false, false, true);
        }
    }

    /**
     * Displays user outlines of each group member (for the clip members table)
     *
     */
    function display_group_members() {
        global $sc_obj, $sc_clip, $cm, $context;
        if (groups_get_activity_groupmode($cm) == NOGROUPS || $sc_obj->getIvt() == false || $sc_clip->getOwner() == false
        ) {
            return;
        }
        $users = get_users_by_capability($context, 'mod/opencast:use', 'u.id');
        foreach ($users as $userid => $user) {
            if (mod_opencast_user::checkSameGroup(mod_opencast_user::getMoodleUserIdFromExtId($sc_clip->getOwner()),
                    $userid)
            ) {
                $this->display_user_outline($userid, false, false, false, true);
            }
        }
    }

    /**
     * Displays a list of a user's pending and uploaded clips
     *
     * @param bool $show_uploaded
     * @param bool $show_pending
     * @param bool $allusers
     * @param bool $with_uploader display clip uploader
     * @param bool $with_owner    display clip owner
     */
    function display_user_pending_clips($show_uploaded = true, $show_pending = true, $allusers = false,
                                        $with_uploader = false, $with_owner = true) {
        global $DB, $opencast, $USER, $context;

        $isproducer = has_capability('mod/opencast:isproducer', $context);

        if ($allusers && $isproducer) {
            // display for all users
            $uploaded_title = 'uploadedclips';
            $pending_title = 'pendingclips';
            $records = $DB->get_records('opencast_uploadedclip', ['opencastid' => $opencast->id]);
        }
        else {
            // display for current user
            $uploaded_title = 'myuploadedclips';
            $pending_title = 'mypendingclips';
            $records = $DB->get_records('opencast_uploadedclip',
                    ['userid' => $USER->id, 'opencastid' => $opencast->id]);
        }

        $sc_obj = new mod_opencast_series();
        $sc_obj->fetch($opencast->id);
        $pending = [];
        $uploaded = [];
        foreach ($records as $record) {
            if ($record->status == OPENCAST_CLIP_READY) {
                // encoding finished
                $uploaded[] = $record;
            }
            else if ($record->status == OPENCAST_CLIP_UPLOADED) {
                // encoding in progress
                $pending[] = $record;
            }
        }
        // display clips uploaded by this user:
        if ($show_uploaded && count($uploaded)) {
            echo html_writer::tag('h3', get_string($uploaded_title, 'opencast', count($uploaded)));
            echo html_writer::start_tag('table', ['class' => 'opencast-clips']);
            $this->display_singleclip_table_header(false, $with_owner, $with_uploader, false);
            foreach ($uploaded as $uploaded_record) {
                $sc_clip = new mod_opencast_event($sc_obj, $uploaded_record->ext_id, null, $uploaded_record->opencastid);
                $this->display_clip_outline($sc_clip, false, false, null, $with_owner, $with_uploader, false, false);
            }
            echo html_writer::end_tag('table');
        }
        // display this user's pending clips (uploaded but not yet available):
        if ($show_pending && count($pending)) {
            echo html_writer::tag('h3', get_string($pending_title, 'opencast', count($pending)));
            echo html_writer::start_tag('table', ['class' => 'opencast-clips']);
            $this->display_singleclip_table_header(false, $with_owner, $with_uploader, false);
            foreach ($pending as $pending_record) {
                try {
                    $sc_clip = new mod_opencast_event($sc_obj, $pending_record->ext_id, null, $pending_record->opencastid);
                }
                catch (Exception $e) {
                    if ($e->errorcode == 'api_404' && $e->module == 'opencast') {
                        $DB->delete_records('opencast_uploadedclip', ['id' => $pending_record->id]);
                    }
                    continue;
                }
                $this->display_clip_outline($sc_clip, false, false, null, $with_owner, $with_uploader, false, false);
            }
            echo html_writer::end_tag('table');
        }
        if ($allusers && !count($records)) {
            echo html_writer::tag('p', get_string('nouploadedclips', 'opencast'));
        }
    }

    /**
     * Displays user details in a table row (for the clip members page)
     *
     * @param int  $userid       Moodle user ID
     * @param bool $isdeleteable whether user is removeable (button shown)
     * @param bool $isteacher
     * @param bool $isowner
     * @param bool $isgroupmember
     * @param bool $isuploader
     *
     * @return bool true if user was displayed
     */
    function display_user_outline($userid, $isdeleteable = false, $isteacher = false, $isowner = false,
                                  $isgroupmember = false, $isuploader = false) {
        global $course, $cm, $OUTPUT, $DB, $context, $url;

        if (in_array($userid, $this->displayed_userids)) {
            return;
        }

        $user = $DB->get_record('user', ['id' => $userid]);
        if ($user === false) {
            return;
        }

        echo html_writer::start_tag('tr');
        echo html_writer::start_tag('td');
        // Note ND : output logic copied from user/index.php
        echo $OUTPUT->user_picture($user, ['size' => 50, 'courseid' => $course->id]);
        $email = '';
        if ($user->maildisplay == 1 or ($user->maildisplay == 2 and ($course->id != SITEID) and !isguestuser()) or has_capability('moodle/course:viewhiddenuserfields',
                        $context)
        ) {
            $email = ' ' . $user->email;
        }
        echo html_writer::tag('div', $user->lastname . ', ' . $user->firstname);
        echo html_writer::end_tag('td');
        echo html_writer::tag('td', $email);
        if ($isteacher === true) {
            echo html_writer::tag('td', get_string('channel_teacher', 'opencast'));
        }
        else if ($isowner === true) {
            echo html_writer::tag('td', get_string('clip_owner', 'opencast'));
        }
        else if ($isgroupmember === true) {
            echo html_writer::tag('td', get_string('group_member', 'opencast'));
        }
        else if ($isuploader === true) {
            echo html_writer::tag('td', get_string('clip_uploader', 'opencast'));
        }
        else {
            echo html_writer::tag('td', get_string('clip_member', 'opencast'));
        }
        echo html_writer::start_tag('td');
        if ($isdeleteable === true) { // the user is an invited member
            echo html_writer::start_tag('form', [
                    'method'   => 'post', 'action' => 'event_members.php',
                    'onsubmit' => 'return confirm(\'' . get_string('confirm_removeuser', 'opencast') . '\');'
            ]);
            echo html_writer::input_hidden_params($url, ['action', 'userid']);
            echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
            echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'action', 'value' => 'remove']);
            echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'userid', 'value' => $user->id]);
            echo html_writer::empty_tag('input', ['type' => 'submit', 'value' => get_string('remove')]);
            echo html_writer::end_tag('form');
        }
        echo html_writer::end_tag('td');
        echo html_writer::end_tag('tr');
        $this->displayed_userids[] = $userid;

        return true;
    }

    /**
     * Displays a list of the invited members of a SWITCHcast clip
     *
     */
    function display_clip_members() {
        global $sc_clip;
        echo html_writer::start_tag('table', ['class' => 'opencast-clips opencast-clips-members']);
        echo html_writer::start_tag('tr');
        echo html_writer::tag('th', get_string('name'));
        echo html_writer::tag('th', get_string('email'));
        echo html_writer::tag('th', get_string('context', 'opencast'));
        echo html_writer::tag('th', get_string('actions'));
        echo html_writer::end_tag('tr');
        $this->display_channel_teachers();
        $this->display_clip_owner();
        $this->display_clip_uploader();
        $this->display_group_members();
        $members = $sc_clip->getMembers();
        foreach ($members as $member) {
            $this->display_user_outline($member, true, false);
        }
        echo html_writer::end_tag('table');
    }

    /**
     * Displays a user selector
     *
     * @param bool   $withproducers    shall the producers be included ?
     * @param string $action_url       where the form shall be posted
     * @param string $buttonlabel      value attribute of the submit button
     * @param bool   $switchaaionly    display users with ExternalAccount only
     * @param bool   $with_emtpyoption display 'remove user' option or not
     * @param bool   $selectonly       display HTML SELECT element only
     * @param int    $selected_id      if not zero, select OPTION with this index
     */
    function display_user_selector($withproducers = false, $action_url = '', $buttonlabel = 'OK',
                                   $switchaaionly = false, $with_emtpyoption = false, $selectonly = false,
                                   $selected_id = 0) {
        global $context, $url, $course;
        if ($withproducers === false) {
            $producers = get_users_by_capability($context, 'mod/opencast:isproducer', 'u.id');
        }
        $possible_users = get_users_by_capability($context, 'mod/opencast:use',
                'u.id, u.lastname, u.firstname, u.maildisplay, u.email', 'u.lastname, u.firstname');
        $options = [];
        if ($with_emtpyoption) {
            $options[-1] = '(' . get_string('removeowner', 'opencast') . ')';
        }
        foreach ($possible_users as $possible_user_id => $possible_user) {
            if (in_array($possible_user_id, $this->displayed_userids)) {
                continue;
            }
            if ($withproducers === false && array_key_exists($possible_user_id, $producers)) {
                continue;
            }
            if ($switchaaionly && !mod_opencast_user::getExtIdFromMoodleUserId($possible_user_id)) {
                continue;
            }
            $option_text = $possible_user->lastname . ', ' . $possible_user->firstname;
            if ($possible_user->maildisplay == 1 or ($possible_user->maildisplay == 2 and ($course->id != SITEID) and !isguestuser()) or has_capability('moodle/course:viewhiddenuserfields',
                            $context)
            ) {
                $option_text .= ' (' . $possible_user->email . ')';
            }
            $options[$possible_user_id] = $option_text;
        }
        if (count($options)) {
            if (!$selectonly) {
                echo html_writer::start_tag('form', [
                        'method'   => 'post', 'action' => $action_url,
                        'onsubmit' => 'return document.getElementById(\'menuuserid\').selectedIndex != 0;'
                ]);
                echo html_writer::input_hidden_params($url, ['action', 'userid']);
                echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
                echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'action', 'value' => 'add']);
            }
            echo html_writer::select($options, 'userid', $selected_id);
            if (!$selectonly) {
                echo html_writer::empty_tag('input', ['type' => 'submit', 'value' => $buttonlabel]);
                echo html_writer::end_tag('form');
            }
        }
        else {
            if (!$selectonly) {
                echo html_writer::start_tag('form');
            }
            echo html_writer::select($options, 'userid', null, null, ['disabled' => 'disabled']);
            if (!$selectonly) {
                echo html_writer::empty_tag('input',
                        ['type' => 'submit', 'value' => $buttonlabel, 'disabled' => 'disabled']);
                echo html_writer::tag('div', get_string('nomoreusers', 'opencast'));
                echo html_writer::end_tag('form');
            }
        }
    }
}

