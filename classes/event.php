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
 * @author     Fabian Schmid <fabian.schmid@ilub.unibe.ch>
 * @author     Martin Studer <ms@studer-raimann.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class mod_opencast_event {

    const PROCESSING_STATE_PROCESSING = 'RUNNING';
    const PROCESSING_STATE_FAILED = 'FAILED';
    const PROCESSING_STATE_SUCCEEDED = 'SUCCEEDED';

    public $owner;

    /**
     * Constructor
     *
     * @param mod_opencast_series $a_obj_scast     SWITCHcast channel the clip belongs to
     * @param string                $clip_identifier clip ID as SWITCHcast
     * @param bool                  $isempty
     * @param int                   $opencast_id
     */
    public function __construct(mod_opencast_series $a_obj_scast, $clip_identifier, $isempty = false,
                                $opencast_id = 0) {
        if (!$isempty) {
            $this->series = $a_obj_scast;
            $this->opencast_id = $opencast_id;
            //		$this->obj_id = $a_obj_scast->obj_id;
            $this->channel_ext_id = $a_obj_scast->getExtId();
            $this->sys_account = $a_obj_scast->getSysAccount();
            $this->members = [];
            $this->setExtId($clip_identifier);
            $this->setChannelEditLink($a_obj_scast->getEditLink());

            $this->fetch();
        }
    }

    /**
     * Fetch event data from the SWITCHcast server
     *
     */
    public function fetch() {
        global $DB;

        // no URL pre-signing here, we'll sign them later on-the-fly
        $event = mod_opencast_apicall::sendRequest('/events/' . $this->getExtId() . '?withpublications=true', 'GET', null, false, true, null, false, true); // dont run-as, we want all events, we have filtered already
        $event_acls = mod_opencast_apicall::sendRequest('/events/' . $this->getExtId() . '/acl', "GET", null, false, true, null, false, true); // dont run-as, we want all events, we have filtered already
        while ($acl = array_pop($event_acls)) {
            if ($acl->allow == true && $acl->action == 'write' && preg_match('/^ROLE\_AAI\_USER\_([^\_]+)$/', $acl->role,
                            $matches) && $matches[1] != mod_opencast_series::getValueForKey('default_sysaccount')
            ) {
                $this->setOwner($matches[1]);
                break;
            }
        }

        $this->setExtId((string)$event->identifier);
        $this->setTitle((string)$event->title);
        $this->setSubtitle((string)$event->description);
        $this->setLocation((string)$event->location);
        $this->setPresenter(implode(', ', $event->presenter));
        //		$this->setRecordingDate((string) $simplexmlobj->recording_date);
        $this->setRecordingDate((string)$event->created);
        $this->setSortableRecordingDate((string)$event->created);
        //        $this->setRecordingStation((string) $clip->ivt__recordingstation);

        $this->setLinkMov($this->getUrlFor($event, 'Download'));
        $this->setCover($this->getUrlFor($event, 'Cover image'));
        global $opencast;
        if ($opencast->allow_annotations == OPENCAST_ANNOTATIONS) {
            $this->setAnnotationLink($this->getUrlFor($event, 'Annotate'));
        }
        $this->setLinkFlash($this->getUrlFor($event, 'Streaming'));

        $members = $DB->get_records('opencast_cmember', ['clip_ext_id' => $this->getExtId()]);
        foreach ($members as $member) {
            $this->setMember($member->userid);
        }
    }

    /**
     * Gets various URLs from a clip's XML
     *
     * @param stdClass $event object from JSON for a clip
     * @param string   $label label of the URL within the JSON
     *
     * @return string the URL
     */
    private function getUrlFor($event, $label = '') {
        global $CFG, $context;
        $url = '';

        if (in_array($label, ['Download'])) {
            // check that the user has the capability to download
            if (!has_capability('mod/opencast:downloadclip', $context)) {
                return '#opencast-inactive';
            }
        }
        foreach ($event->publications as $publication) {
            switch ($label) {

                case 'Streaming':
                    if ($publication->channel == mod_opencast_series::getValueForKey('pubchannel_videoplayer')) {
                        $url = $publication->url;
                    }
                    break;

                case 'Download':
                    if ($publication->channel == mod_opencast_series::getValueForKey('pubchannel_download')) {
                        foreach ($publication->media as $media) {
                            if (in_array('download', $media->tags)) {
                                $url = $media->url;
                                break;
                            }
                        }
                    }
                    break;

                case 'Annotate':
                    if ($publication->channel == mod_opencast_series::getValueForKey('pubchannel_annotate')) {
                        $url = $publication->url;

                        break;
                    }
                    break;

                case 'Cover image':
                    $preview_urls = [];
                    $thumbnail_flavors = explode(',', mod_opencast_series::getValueForKey('thumbnail_flavors'));
                    foreach ($thumbnail_flavors as &$thumbnail_flavor) {
                        $thumbnail_flavor = trim($thumbnail_flavor);
                    }
                    if ($publication->channel == mod_opencast_series::getValueForKey('pubchannel_download')) {
                        foreach ($publication->attachments as $attachment) {
                            if (in_array($attachment->flavor, $thumbnail_flavors)) {
                                $preview_urls[] = $attachment->url;
                            }
                        }
                    }
                    if (count($preview_urls)) {
                        $url = array_pop($preview_urls);
                    }
                    break;

                default:
                    return '';
                    break;
            }
        }

        if ($url) {
            // proceed through go_to.php for on-the-fly signing of URLs
            $salt = rand(100000, 999999);
            $link = $CFG->wwwroot . '/mod/opencast/go_to.php';
            $link .= '?url=' . base64_encode($url);
            $link .= '&salt=' . base64_encode($salt);
            $link .= '&token=' . base64_encode(sha1(mod_opencast_series::getValueForKey('default_sysaccount') . $salt . $this->opencast_id . $url));
            $link .= '&swid=' . $this->opencast_id;

            return $link;
        }

        return '';
    }

    /**
     * Updates event metadata at SWITCHCast server
     *
     * @return bool true if succesful
     */
    public function update() {

        // TODO WAIT FOR SWITCH : can not send empty strings (to remove e.g. description), see my e-mail to switch-api on 20150703
        // first, update clip metadata
        $event_metadata = [
                'metadata' => json_encode([
                        ['id' => 'title', 'value' => $this->getTitle()],
                        ['id' => 'isPartOf', 'value' => $this->series->getExtId()],
                        ['id' => 'description', 'value' => $this->getSubtitle()],
                        ['id' => 'location', 'value' => $this->getLocation()], // TODO WAIT FOR SWITCH not working
                        ['id' => 'creator', 'value' => explode(',', $this->getPresenter())]
                        // TODO WAIT FOR SWITCH not working

                ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        ];

        mod_opencast_apicall::sendRequest('/events/' . $this->getExtId() . '/metadata?type=dublincore/episode', 'PUT',
                $event_metadata);

        // second, update ACLs with (possibly) new owner :
        // 1.- get current ACLs
        $event_acls = mod_opencast_apicall::sendRequest('/events/' . $this->getExtId() . '/acl', 'GET', null, false, true, null, false, true);
        // 2.- delete any ACL that is not the system_user
        $new_acls = [];
        while ($acl = array_pop($event_acls)) {
            if (preg_match('/^ROLE\_AAI\_USER\_([^\_]+)$/', $acl->role,
                            $matches) && $matches[1] != mod_opencast_series::getValueForKey('default_sysaccount')
            ) {
                // drop ACL (i.e. do nothing)
            }
            else {
                // user is either OpenCast internal, or system_user -> keep ACL
                $new_acls[] = $acl;
            }
        }
        // 3.- add specified owner
        $new_acls[] = ['action' => 'write', 'allow' => true, 'role' => 'ROLE_AAI_USER_' . $this->getOwner()];
        if ($this->series->getIvt()) {
            $new_acls[] = ['action' => 'write', 'allow' => true, 'role' => 'ROLE_USER_IVT_AAI_' . $this->getOwner()];
        }

        $data = ['acl' => json_encode($new_acls, JSON_UNESCAPED_SLASHES)];

        mod_opencast_apicall::sendRequest('/events/' . $this->getExtId() . '/acl', 'PUT', $data);

        return true;
    }

    /**
     * Deletes the clip at SWITCHcast server
     *
     * @return boolean true if succesful
     */
    public function delete() {
        global $DB;

        $url = '/events/' . $this->getExtId();

        mod_opencast_apicall::sendRequest($url, "DELETE");

        $DB->delete_records('opencast_cmember', ['clip_ext_id' => $this->getExtId()]);

        return true;
    }

    /**
     * Checks the current USER's permission on the event
     *
     * @param string $perm_type permission : 'read' or 'write'
     *
     * @return bool true if permission granted
     */
    public function isAllowed($perm_type) {
        global $DB, $USER, $context;

        if (!has_capability('mod/opencast:use', $context)) {
            return false;
        }

        $mod_opencast_user = new mod_opencast_user();
        $user_uploaded_events = $DB->get_records('opencast_uploadedclip', ['userid' => $USER->id]);
        $user_uploaded_events_extids = [];
        if (is_array($user_uploaded_events)) {
            foreach ($user_uploaded_events as $user_uploaded_event) {
                $user_uploaded_events_extids[] = $user_uploaded_event->ext_id;
            }
        }

        if ($perm_type == 'write') {
            if (has_capability('mod/opencast:isproducer',
                            $context) || (($mod_opencast_user->getExternalAccount() == $this->getOwner()) && $this->getOwner() !== '') || in_array($this->getExtId(),
                            $user_uploaded_events_extids)
            ) {
                /*
                 * the current $USER is channel producer
                 * OR the current $USER is the clip owner
                 * OR the current $USER is the user who uploaded the clip
                 */
                return true;
            }
        }
        else if ($perm_type == 'read') {
            if ((has_capability('mod/opencast:isproducer', $context)) || (has_capability('mod/opencast:seeallclips',
                            $context)) || ($this->series->getIvt() && $this->getOwner() !== '' && ($mod_opencast_user->getExternalAccount() == $this->getOwner())) || ($this->series->getIvt() == false) || ($this->series->getIvt() == true && $this->series->getInvitingPossible() == true && is_numeric(array_search($USER->id,
                                    $this->getMembers()))) || (mod_opencast_user::checkSameGroup(mod_opencast_user::getMoodleUserIdFromExtId($this->getOwner()),
                            $USER->id)) || in_array($this->getExtId(), $user_uploaded_events_extids)
            ) {
                /*
                 * the current $USER is channel producer
                 * the current $USER has the mod/opencast:seeallclips capability
                 * OR activity is set in individual mode AND the current $USER is the clip owner
                 * OR there are no individual clip permissions set for this activity
                 * OR activity is set in individual mode AND $USER is an invited member of a clip
                 * OR is in the same user group as the clip owner
                 * OR the current $USER is the user who uploaded the clip
                 */
                return true;
            }
        }

        return false;
    }

    /**
     * Adds a member (invitation) to the clip
     *
     * @param int $userid Moodle user ID
     * @param     $courseid
     * @param     $opencastid
     *
     * @return bool true if member added
     * @throws coding_exception
     */
    public function addMember($userid, $courseid, $opencastid) {
        global $DB, $context;

        $userid = (int)$userid;
        if (in_array($userid, $this->getMembers())) {
            return false;
        }
        if (!has_capability('mod/opencast:use', $context, $userid)) {
            return false;
        }

        $new_record = new stdClass();
        $new_record->userid = $userid;
        $new_record->clip_ext_id = $this->getExtId();
        $new_record->courseid = $courseid;
        $new_record->opencastid = $opencastid;
        $DB->insert_record('opencast_cmember', $new_record);

        $this->members[] = $userid;

        return true;
    }

    /**
     * Removes a member (invitation) from the clip
     *
     * @param int $userid Moodle user ID
     * @param     $courseid
     * @param     $opencastid
     */
    public function removeMember($userid, $courseid, $opencastid) {
        global $DB;
        $DB->delete_records('opencast_cmember', [
                'clip_ext_id'  => $this->getExtId(), 'userid' => $userid, 'courseid' => $courseid,
                'opencastid' => $opencastid
        ]);
        $newmembers = [];
        foreach ($this->members as $member) {
            if ((int)$member !== (int)$userid) {
                $newmembers[] = $member;
            }
        }
        $this->members = $newmembers;
    }

    /**
     * Gets the Moodle user IUD of the event's owner
     *
     * @return int|bool a Moodle user ID or false if not found
     */
    public function getOwnerUserId() {
        global $DB;

        if (!$this->getOwner()) {
            // owner not defined
            return false;
        }

        $uid_field = $this->series->getValueForKey('uid_field');
        if (strpos($uid_field, '::') !== false) {
            $params = explode('::', $uid_field);
            $table = $params[0];
            $fieldid = $params[1];
            $u = $DB->get_record_select($table,
                    'fieldid = ' . (int)$fieldid . ' AND data = \'' . (string)$this->getOwner() . '\'', [], '*',
                    IGNORE_MULTIPLE);
            $userid = $u->userid;
        }
        else {
            $u = $DB->get_record('user', [$uid_field => $this->getOwner()]);
            $userid = $u->id;
        }

        return $userid;
    }

    public function isMember($userid) {
        return in_array($userid, $this->getMembers());
    }

    public function getMembers() {
        return $this->members;
    }

    public function setMember($member) {
        $this->members[] = $member;
    }

    public function setChannelEditLink($a_channel_edit_link) {
        $this->channel_edit_link = $a_channel_edit_link;
    }

    public function getChannelEditLink() {
        return $this->channel_edit_link;
    }

    public function setExtId($a_val) {
        $this->ext_id = $a_val;
    }

    public function getExtId() {
        return $this->ext_id;
    }

    public function setTitle($a_val) {
        $this->title = $a_val;
    }

    public function getTitle() {
        return $this->title;
    }

    public function setCover($a_val) {
        $this->cover = $a_val;
    }

    public function getCover() {
        return $this->cover;
    }

    public function setAnnotationLink($a_val) {
        $this->AnnotationLink = $a_val;
    }

    public function getAnnotationLink() {
        return $this->AnnotationLink;
    }

    public function setStreamingHtml($a_val) {
        $this->streaming_html = $a_val;
    }

    public function getStreamingHtml() {
        return $this->streaming_html;
    }

    public function setLinkBox($a_val) {
        $this->link_box = $a_val;
    }

    public function getLinkBox() {
        return $this->link_box;
    }

    public function setLinkFlash($a_val) {
        $this->linkflash = $a_val;
    }

    public function getLinkFlash() {
        return $this->linkflash;
    }

    public function setLinkMp4($a_val) {
        $this->linkmp4 = $a_val;
    }

    public function getLinkMp4() {
        return $this->linkmp4;
    }

    public function setLinkMov($a_val) {
        $this->linkmov = $a_val;
    }

    public function getLinkMov() {
        return $this->linkmov;
    }

    public function setLinkM4v($a_val) {
        $this->linkm4v = $a_val;
    }

    public function getLinkM4v() {
        return $this->linkm4v;
    }

    public function setSubtitle($a_val) {
        $this->subtitle = $a_val;
    }

    public function getSubtitle() {
        return $this->subtitle;
    }

    public function setPresenter($a_val) {
        $this->presenter = $a_val;
    }

    public function getPresenter() {
        return $this->presenter;
    }

    public function setOwner($a_val) {
        $this->owner = $a_val;
    }

    public function getOwner() {
        return $this->owner;
    }

    public function setLectureDate($a_val) {
        $this->lecture_date = $a_val;
    }

    public function getLectureDate() {
        return $this->lecture_date;
    }

    public function setLocation($a_val) {
        $this->location = $a_val;
    }

    public function getLocation() {
        return $this->location;
    }

    public function setDownloadlinks($a_val) {
        $this->downloadlinks = $a_val;
    }

    public function getDownloadlinks() {
        return $this->downloadlinks;
    }

    public function setRecordingStation($a_val) {
        $this->recordingstation = $a_val;
    }

    public function getRecordingStation() {
        return $this->recordingstation;
    }

    public function setRecordingDate($a_val) {
        $this->recordingdate = $a_val;
    }

    public function getRecordingDate() {
        return $this->recordingdate;
    }

    public function setSortableRecordingDate($a_val) {
        $this->sortablerecordingdate = $a_val;
    }

    public function getSortableRecordingDate() {
        return $this->sortablerecordingdate;
    }
}


