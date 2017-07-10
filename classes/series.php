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
 * @author     Nicolas Dunand <Nicolas.Dunand@unil.ch>
 * @author     Fabian Schmid <schmid@ilub.unibe.ch>
 * @author     Martin Studer <ms@studer-raimann.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

define('OPENCAST_CHANNEL_PROD', 'periodic');
define('OPENCAST_CHANNEL_TEST', 'test');

define('OPENCAST_CHANNEL_NEW', 'new channel');
define('OPENCAST_CHANNEL_EXISTING', 'existing channel');

define('OPENCAST_ANNOTATIONS', 1);
define('OPENCAST_NO_ANNOTATIONS', 0);

define('OPENCAST_CLIP_UPLOADED', 1);
define('OPENCAST_CLIP_READY', 2);
define('OPENCAST_CLIP_TRYAGAIN', 3);
define('OPENCAST_STALE_PERIOD', 3600 * 6);

define('OPENCAST_PROCESSING_SUCCEEDED', 'SUCCEEDED');

// fix for ENT_XML1 not defined in PHP < 5.4.0
defined('ENT_XML1') or define('ENT_XML1', 16);

class mod_opencast_series {

    /**
     * @var bool
     */
    protected $allow_annotations;

    /**
     * @var bool
     */
//    protected $template_id;

    /**
     * @var string
     */
    protected $ext_id;

    /**
     * @var string
     */
    protected $organization_domain;

    /**
     * @var string
     */
    protected $introduction_text;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    protected $sys_account;

    /**
     * Constructor
     *
     * @access    public
     */
    function __construct() {
        global $PAGE;

        $PAGE->requires->js('/mod/opencast/js/javascript.js');

        // initially, set $sys_account and $organization_domain to current $USER's
        $sc_user = new mod_opencast_user();
        // TODO remove as we're now only using local org (i.e. unil.ch for us)
//        $this->setSysAccount($this->getSysAccountByOrganization(self::getOrganizationByEmail($sc_user->getExternalAccount())));
        $this->organization_domain = self::getOrganizationByEmail($sc_user->getExternalAccount());
    }

    /**
     * @param $email
     *
     * @return mixed
     */
    public static function getOrganizationByEmail($email) {
        if (!$email) {
            return false;
        }

        return preg_replace('/^[^@]+@([^.]+\.)?([^.]+\.ch)$/', '$2', $email);
    }

    /**
     * @return array
     */
    public static function getEnabledOrgnanizations() {
        // TODO remove as we're now only using local org (i.e. unil.ch for us)
        return [];
        $enabled_institutions_str = self::getValueForKey('enabled_institutions');

        return explode(',', str_replace(' ', '', $enabled_institutions_str));
    }

    /**
     * @param $organization
     * @param $force_org
     *
     * @return string
     */
    public static function getSysAccountByOrganization($organization, $force_org = false) {
        // TODO remove as we're now only using local org (i.e. unil.ch for us)
        return '';
        if (in_array($organization, self::getEnabledOrgnanizations())) {
            $sys_account_key = $organization . '_sysaccount';

            return self::getValueForKey($sys_account_key);
        }
        else if (!$force_org) {
            return self::getValueForKey('default_sysaccount');
        }

        return '';
    }

    /**
     *
     * @return string
     */
    public static function getSysAccountOfUser() {
        // TODO remove as we're now only using local org (i.e. unil.ch for us)
        return '';
        $scuser = new mod_opencast_user();
        $organizationDomain = self::getOrganizationByEmail($scuser->getExternalAccount());
        $sys_account = self::getSysAccountByOrganization($organizationDomain);

        if ($sys_account == '') {
            // If the user's external account has no sysaccount,
            // the default sysaccount is used.
            $sys_account = self::getValueForKey('default_sysaccount');
        }

        return $sys_account;
    }

    /**
     *
     * @param type $enforceglobalmax
     *
     * @return array
     */
    public static function getMaxfilesizes($enforceglobalmax = false) {
        $sizemb = get_string('sizemb');
        $sizes = [
                5 * 1024 * 1024   => '5 ' . $sizemb, 10 * 1024 * 1024 => '10 ' . $sizemb,
                20 * 1024 * 1024  => '20 ' . $sizemb, 50 * 1024 * 1024 => '50 ' . $sizemb,
                100 * 1024 * 1024 => '100 ' . $sizemb, 250 * 1024 * 1024 => '250 ' . $sizemb,
                500 * 1024 * 1024 => '500 ' . $sizemb,
        ];
        if ($enforceglobalmax) {
            // enforme global maximum
            $globalmax = mod_opencast_series::getValueForKey('userupload_maxfilesize');
            foreach ($sizes as $size => $label) {
                if ($size > $globalmax) {
                    unset($sizes[$size]);
                }
            }
        }

        return $sizes;
    }

    /**
     *
     * @return array
     */
    public static function getAllowedFileExtensions() {
        $extensions = [];
        foreach (explode(',', self::getValueForKey('uploadfile_extensions')) as $extension) {
            $extensions[] = strtolower(trim($extension));
        }

        return $extensions;
    }

    /**
     *
     * @param array $options
     *
     * @return boolean|\SimpleXMLElement result or false if error
     */
    public function createClip($options) {

        $required_options = ['title'];
        foreach ($required_options as $required_option) {
            if (!isset($options[$required_option])) {
                print_error('missing_param');
            }
        }
        $options['issued_on'] = date('Y-m-d H:i');

        $scuser = new mod_opencast_user();
        $url = '/events';

        foreach ($options as &$option) {
            //            if ($option == '') {
            //                $option = ''; // TODO WAIT FOR SWITCH : remove when back-end bug fixed (empty values not possible)
            //            }
            $option = htmlspecialchars($option, ENT_XML1, 'UTF-8');
        }

        $metadata = [
                [
                        'flavor' => 'dublincore/episode', 'fields' => [
                        [
                                'id' => 'title', 'value' => $options['title']
                        ], [
                                'id' => 'description', 'value' => $options['subtitle']
                        ], [
                                'id' => 'isPartOf', 'value' => $this->ext_id
                        ], [
                                'id' => 'startDate', 'value' => date('Y-m-d')
                        ], [
                                'id' => 'startTime', 'value' => date('H:i:s')
                        ], [
                                'id' => 'creator', 'value' => [$options['presenter']]
                        ]// TODO WAIT FOR SWITCH to change 'creator' to 'presenter'
                    , [
                                'id' => 'location', 'value' => $options['location']
                        ]
                ]
                ]
        ];

        $acls = [
                [
                        'allow'  => true,
                        'action' => 'read',
                        'role'   => 'ROLE_ORG_PRODUCER'
                ],
                [
                        'allow'  => true,
                        'action' => 'write',
                        'role'   => 'ROLE_ORG_PRODUCER'
                ],
                [
                        'allow'  => true,
                        'action' => 'read',
                        'role'   => 'ROLE_EXTERNAL_APPLICATION'
                ],
                [
                        'allow'  => true,
                        'action' => 'write',
                        'role'   => 'ROLE_EXTERNAL_APPLICATION'
                ],
                [
                        'allow'  => true,
                        'action' => 'read',
                        'role'   => 'ROLE_AAI_USER_' . $scuser->getExternalAccount()
                ],
                [
                        'allow'  => true,
                        'action' => 'write',
                        'role'   => 'ROLE_AAI_USER_' . $scuser->getExternalAccount()
                ]
        ];

        if (isset($options['ivt__owner'])) {
            $acls[] = [
                    'allow'  => true,
                    'action' => 'read',
                    'role'   => 'ROLE_USER_IVT_AAI_' . $options['ivt__owner']
            ];
            $acls[] = [
                    'allow'  => true,
                    'action' => 'write',
                    'role'   => 'ROLE_USER_IVT_AAI_' . $options['ivt__owner']
            ];
        }

        $data = [
                'metadata'   => json_encode($metadata, JSON_UNESCAPED_SLASHES),
                'acl'        => json_encode($acls, JSON_UNESCAPED_SLASHES),
                'processing' => '{"workflow": "' . self::getValueForKey('import_workflow') . '", "configuration": {}}'
            // see https://dokuwiki.toolbox.switch.ch/opencast-api/api_conventions
        ];

        return mod_opencast_apicall::sendRequest($url, 'POST', $data, null, null, $options['filename']);
    }

    /**
     * @return array
     * @throws coding_exception
     */
    public static function processUploadedClips() {
        global $CFG, $DB;

        $admin = get_admin();
        $noreplyuser = core_user::get_noreply_user();
        $opencasts = $DB->get_records('opencast');

        // first, some maintenance: delete stale records (e.g. if an error occured at SCast)
        $staletime = time() - OPENCAST_STALE_PERIOD;
        $stale_records = $DB->get_records_select('opencast_uploadedclip',
                'status = ' . OPENCAST_CLIP_UPLOADED . ' AND timestamp < ' . $staletime);
        foreach ($stale_records as $stale_record) {
            $user_stale = $DB->get_record('user', ['id' => $stale_record->userid]);
            if ($user_stale) {
                // notify uploader
                $a_s = new stdClass();
                $a_s->filename = $stale_record->filename;
                $cm_s = get_coursemodule_from_instance('opencast', $stale_record->opencastid);
                $a_s->link = $CFG->wwwroot . '/mod/opencast/view.php?id=' . $cm_s->id;
                email_to_user($user_stale, $admin, get_string('clipstale_subject', 'opencast'),
                        get_string('clipstale_body', 'opencast', $a_s));
                // notify admin too
                $a_s->userlink = $CFG->wwwroot . '/user/profile.php?id=' . $user_stale->id;
                $a_s->userfullname = fullname($user_stale);
                email_to_user($admin, $noreplyuser, get_string('clipstale_subject_admin', 'opencast'),
                        get_string('clipstale_body_admin', 'opencast', $a_s));
            }
        }
        $DB->delete_records_select('opencast_uploadedclip',
                'status = ' . OPENCAST_CLIP_UPLOADED . ' AND timestamp < ' . $staletime);

        // now, let's deal with the remaining ones, checking one by one if they have been processed
        $pending = [];
        $uploaded = [];

        foreach ($opencasts as $opencast) {

            $uploaded_videos = $DB->get_records('opencast_uploadedclip', ['opencastid' => $opencast->id]);
            if (!$uploaded_videos) {
                continue;
            }

            $series = new mod_opencast_series();
            try {
                // try and fetch the series on the back-end BUT do not halt on error
                $fetch_result = $series->fetch($opencast->id, true, true, false);
                if ($fetch_result == false) {
                    throw new moodle_exception('api_404', 'opencast');
                }
            }
            catch (Exception $e) {
                // error with this channel: do not halt because we might be processing other jobs (unattended)
                if ($e->errorcode === 'channel_not_found') { // TODO figure out the errorcode for the new API
                    // channel not existing anymore: stop looking for it ever again
                    $opencast->userupload = 0;
                    $DB->update_record('opencast', $opencast);
                }
                continue;
            }

            $series_events = $series->getEvents([], false); // don't use the cache as this would be new data
            $series_event_indentifiers = [];
            foreach ($series_events as $series_event) {
                $series_event_indentifiers[] = (string)$series_event->identifier;
            }
            foreach ($uploaded_videos as $uploaded_video) {
                if ($uploaded_video->status == OPENCAST_CLIP_READY) {
                    // encoding finished
                    if (!in_array($uploaded_video->ext_id, $series_event_indentifiers)) {
                        // clip deleted
                        $DB->delete_records('opencast_uploadedclip', ['id' => $uploaded_video->id]);
                    }
                    else {
                        $uploaded[] = $uploaded_video->filename;
                    }
                }
                else if (in_array($uploaded_video->ext_id, $series_event_indentifiers)) {
                    // clip being processed: check whether it's ready
                    foreach ($series_events as $series_event) {
                        if ($series_event->identifier == $uploaded_video->ext_id) {
                            if ($series_event->processing_state == OPENCAST_PROCESSING_SUCCEEDED) {
                                // it's ready!
                                // refresh the cache for this event
                                mod_opencast_apicall::sendRequest('/events/' . $series_event->identifier . '?withpublications=true', 'GET', null, false, false, null, true, false);
                                $uploaded[] = $uploaded_video->filename;
                                $uploaded_video->status = OPENCAST_CLIP_READY;
                                $DB->update_record('opencast_uploadedclip', $uploaded_video);
                                $user = $DB->get_record('user', ['id' => $uploaded_video->userid]);
                                if ($user !== false) {
                                    // notify user
                                    $a = new stdClass();
                                    $a->filename = $uploaded_video->filename;
                                    $a->cliptitle = $uploaded_video->title;
                                    $cm = get_coursemodule_from_instance('opencast', $opencast->id);
                                    $a->link = $CFG->wwwroot . '/mod/opencast/view.php?id=' . $cm->id;
                                    email_to_user($user, $noreplyuser, get_string('clipready_subject', 'opencast'),
                                            get_string('clipready_body', 'opencast', $a));
                                }
                            }
                        }
                    }
                }
                else {
                    // clip still pending
                    $pending[] = $uploaded_video->filename;
                }
            }
        }

        return [$pending, $uploaded];
    }

    /**
     *
     * @return string
     */
    public function getOrganization() {
        return $this->organization_domain;
    }

    /**
     * @param $email
     *
     * @return bool returns true iff the organization of the email-address is the same as the channels organization.
     */
    public function isAllowedAsPublisher($email) {
        return $this->organization_domain == self::getOrganizationByEmail($email);
    }

    /**
     * set Sys Account
     *
     */
    public function setSysAccount($a_sys_account) {
        // TODO remove as we're now only using local org (i.e. unil.ch for us)
        return;
        $this->sys_account = $a_sys_account;
    }

    /**
     * get Sys Account
     *
     */
    public function getSysAccount() {
        // TODO remove as we're now only using local org (i.e. unil.ch for us)
        return '';
        if (!$this->sys_account) {
            // Fallback
            $this->sys_account = self::getValueForKey('default_sysaccount');
        }

        return $this->sys_account;
    }

    /**
     * add Producer
     *
     */
    public function addProducer($aaiUniqueId, $usesysaccount = true) {

        if (!$this->isAllowedAsPublisher($aaiUniqueId)) {
            // only add producers from the same institution as the channel's
            return false;
        }

        $url = '/series';
        $url .= '/' . $this->getExtId();
        $url .= '/acl';

        $ch_acls = mod_opencast_apicall::sendRequest($url, 'GET', null, false, true, null, false);

        $count_ch_acls = count($ch_acls);
        for ($i = 0; $i < $count_ch_acls; $i++) {
            $acl = $ch_acls[$i];
            if ($acl->allow == true && ($acl->action == 'read' || $acl->action == 'write') && $acl->role == 'ROLE_AAI_USER_' . $aaiUniqueId) {
                // already a producer, break
                return true;
            }
            if ($acl->role == 'ROLE_AAI_USER_' . $aaiUniqueId) {
                // delete any other ACL the user would have
                unset($ch_acls[$i]);
            }
        }

        // if we reach this point, we have to actually add the user as a producer:
        $ch_acls[] = ['allow' => true, 'action' => 'read', 'role' => 'ROLE_AAI_USER_' . $aaiUniqueId];
        $ch_acls[] = ['allow' => true, 'action' => 'write', 'role' => 'ROLE_AAI_USER_' . $aaiUniqueId];
        $data = ['acl' => json_encode($ch_acls, JSON_UNESCAPED_SLASHES)];

        mod_opencast_apicall::sendRequest($url, 'PUT', $data, false, true, null, false);
        $this->setProducer($aaiUniqueId);

        return true;
    }

    /**
     * is Producer
     *
     */
    public function isProducer($aaiUniqueId) {
        $arr_producer = $this->getProducers();

        return in_array($aaiUniqueId, $arr_producer);
    }

    /**
     *
     * @return array
     */
    public function getAllSysAccounts() {
        global $DB;
        $sysaccounts = [];
        $configs = $DB->get_records_sql("SELECT * FROM {opencast_config} WHERE name LIKE '%sysaccount'");
        foreach ($configs as $config) {
            $sysaccounts[] = $config->value;
        }

        return $sysaccounts;
    }

    /**
     * get Producers
     *
     */
    public function getProducers() {
        if (isset($this->producers)) {
            return $this->producers;
        }
        else {
            return [];
        }
    }

    /**
     *
     * @param string $key
     *
     * @return string
     */
    public static function getValueForKey($key) {
        return get_config('opencast', str_replace('.', 'DOT', $key));
    }

    /**
     * set Producers
     *
     */
    public function setProducer($aaiUniqueId) {
        if ($this->isAllowedAsPublisher($aaiUniqueId)) {
            // only add producers from the same institution
            $this->producers[] = $aaiUniqueId;
        }
    }

    /**
     *
     * @param type $aaiUniqueId
     *
     * @return boolean success state
     */
    public function removeProducer($aaiUniqueId) {

        if (!$this->isAllowedAsPublisher($aaiUniqueId)) {
            // only remove producers from the same institution
            return false;
        }

        $url = '/series';
        $url .= '/' . $this->getExtId();
        $url .= '/acl';

        $ch_acls = mod_opencast_apicall::sendRequest($url, 'GET');

        $count_ch_acls = count($ch_acls);
        for ($i = 0; $i < $count_ch_acls; $i++) {
            $acl = $ch_acls[$i];
            if ($acl->role == 'ROLE_AAI_USER_' . $aaiUniqueId) {
                // delete any ACL the user would have
                unset($ch_acls[$i]);
            }
        }

        $data = ['acl' => json_encode($ch_acls, JSON_UNESCAPED_SLASHES)];
        mod_opencast_apicall::sendRequest($url, 'PUT', $data);

        return true;
    }

    /**
     * get Clips
     *
     * @param array $filters
     *
     * @return bool|stdClass
     * @throws moodle_exception
     */
    public function getEvents($filters = [], $usecache = true) {

        $url = '/events';
        $url .= '?filter=series:' . $this->getExtId();

        $events = mod_opencast_apicall::sendRequest($url, 'GET', null, false, $usecache, null, false, true); // dont run-as, we want all events, we'll be filtering later

        $count_events = count($events);
        for ($i = 0; $i < $count_events; $i++) {
            if ($events[$i]->processing_state != OPENCAST_PROCESSING_SUCCEEDED) {
                // do not dislay unprocessed events as part of the series
                unset ($events[$i]);
                continue;
            }
            // then process filters
            foreach ($filters as $filter_key => $value) {
                if (!$value) {
                    // skip empty filters
                    continue;
                }
                if (in_array($filter_key, ['title', 'location'])) {
                    // TODO WAIT FOR SWITCH : check if works when "presenter" bug is resolved, cf. https://dokuwiki.toolbox.switch.ch/opencast-api/api_known_issues
                    // direct attributes: easy!
                    if (strpos($events[$i]->$filter_key, $value) === false) {
                        unset ($events[$i]);
                        continue 2;
                    }
                }
                else if ($filter_key == 'ivt_owner') {
                    // we have to check this event's full ACLs
                    $event = new mod_opencast_event($this, $events[$i]->identifier);
                    if ($event->getOwner() != $value) {
                        unset ($events[$i]);
                        continue 2;
                    }
                }
                else if ($filter_key == 'withoutowner' && $value == 'true') {
                    $event = new mod_opencast_event($this, $events[$i]->identifier);
                    if ($event->getOwner() != '') {
                        unset ($events[$i]);
                        continue 2;
                    }
                }
                else if ($filter_key == 'presenter') {
                    $event = new mod_opencast_event($this, $events[$i]->identifier);
                    if (strpos($event->getPresenter(), $value) === false) {
                        unset ($events[$i]);
                        continue 2;
                    }
                }
            }
        }

        return $events;
    }

    /**
     * Check access to clips, and give back only the ones we have access to.
     *
     * @param $clips array
     *
     * @return array
     */
    public function checkAccess($clips = []) {
        global $allclips, $opencast;
        $newData = [];
        foreach ($clips as $clip) {
            // we have to instantiate a new mod_opencast_clip because $clip is a SimpleXMLElement
            if (!isset($allclips[(string)$clip->identifier])) {
                $clipobj = new mod_opencast_event($this, (string)$clip->identifier, false, $opencast->id);
                $allclips[(string)$clip->identifier] = $clipobj;
            }
            else {
                $clipobj = $allclips[(string)$clip->identifier];
            }
            if ($clipobj->isAllowed('read')) {
                $newData[] = $clip;
            }
        }

        return $newData;
    }

    /**
     * Returns a list of the templates allowed by the Moodle administrator.
     *
     * @return array a list of enabled templates (id => name)
     */
//    public static function getEnabledTemplates() {
//        return [];
        //        $enabled_templates = explode("\n", self::getValueForKey('enabled_templates'));
        //        $templates = [];
        //        foreach ($enabled_templates as $enabled_template) {
        //            $parts = explode('::', $enabled_template);
        //            if (count($parts) !== 2) {
        //                continue;
        //            }
        //            $t_id = $parts[0];
        //            $t_title = $parts[1];
        //            if (!trim($t_title)) {
        //                // use SwitchCast official template name
        //                $t = self::getAllTemplates();
        //                $t_title = $t[$t_id];
        //            }
        //            $templates[$t_id] = $t_title;
        //        }
        //
        //        return $templates;
//    }

    /**
     * Returns a SwitchCast template ID when given a SwitchCast template name,
     * returns false if this template is not found or not enabled by the
     * Moodle administrator.
     *
     * @param string $template_name
     *
     * @return boolean|integer the template ID or false if not found
     */
//    public static function getTemplateIdFromName($template_name) {
//        if (!in_array($template_name, self::getAllTemplates())) {
//            return false;
//        }
//        $id = array_search($template_name, self::getAllTemplates());
//        if (!array_key_exists($id, self::getEnabledTemplates())) {
//            return false;
//        }
//
//        return $id;
//    }

    /**
     * To create a channel we need an aai account that is allowed to register a new channel.
     * Thus the first choice is the aai account of the current user, if he doesn't have an
     * account we use the system account.
     */
    function doCreate() {
        global $USER;
        $scuser = new mod_opencast_user();

        // if the current USER has no switchaai account, prevent channel creation
        if ($scuser->getExternalAccount() == '') {
            print_error('user_notaai', 'opencast');
        }

        if ($this->getExtId() == '') {
            // No ext_id: that's a new channel to be created at SWITCHcast server
            $url = '/series';

            $data = [
                    'metadata'                             => json_encode([
                            [
                                    'label'  => 'Opencast Series DublinCore', 'flavor' => 'dublincore/series',
                                    'fields' => [
                                            [
                                                    'id' => 'title', 'value' => $this->title
                                            ]
                                    ]
                            ]
                    ], // TODO NOW : add other metadata (description, location, presenter(s), , etc.)
                            JSON_UNESCAPED_SLASHES), 'acl' => json_encode([
                        // see https://dokuwiki.toolbox.switch.ch/opencast-api/api_conventions
                        [

                                'allow' => true, 'action' => 'read', 'role' => 'ROLE_ORG_PRODUCER'
                        ], [
                                'allow' => true, 'action' => 'write', 'role' => 'ROLE_ORG_PRODUCER'
                        ], [
                                'allow' => true, 'action' => 'read', 'role' => 'ROLE_EXTERNAL_APPLICATION'
                        ], [
                                'allow' => true, 'action' => 'write', 'role' => 'ROLE_EXTERNAL_APPLICATION'
                        ], [
                                'allow' => true, 'action' => 'read',
                                'role'  => 'ROLE_AAI_USER_' . $scuser->getExternalAccount()
                        ], [
                                'allow' => true, 'action' => 'write',
                                'role'  => 'ROLE_AAI_USER_' . $scuser->getExternalAccount()
                        ]

                    ], JSON_UNESCAPED_SLASHES),
                    //                     'theme'                       => '1'
            ];

            $new_series = mod_opencast_apicall::sendRequest($url, 'POST', $data);

            // Check ext_id
            if ($new_series->identifier) {
                $this->setExtId($new_series->identifier);
            }
            else {
                print_error('errorchannelcreation', 'opencast');
            }
        }

        else {
            // existing channel at SWITCHcast server, to be updated
            // basically, we only add our sysAccount as producer at the SWITCHcast server
            $this->update();
        }
    }

    /**
     *
     * @param int|string $id
     * @param bool       $inmoodle
     * @param bool       $returninfo
     * @param bool       $haltonerror
     *
     * @return type
     * @throws moodle_exception
     */
    function fetch($id, $inmoodle = true, $returninfo = false, $haltonerror = true) {
        global $DB;

        if ($inmoodle) {
            // there must be a DB record
            if (is_number($id)) {
                $rec = $DB->get_record('opencast', ['id' => $id]);
            }
            else {
                $rec = $DB->get_record('opencast', ['ext_id' => $id]);
            }

            $this->setExtId($rec->ext_id);
            $this->setIvt($rec->is_ivt);
            $this->setInvitingPossible($rec->inviting);
            // TODO remove as we're now only using local org (i.e. unil.ch for us)
//            $this->setSysAccount($this->getSysAccountByOrganization($rec->organization_domain));
            $this->setOrganizationDomain($rec->organization_domain);
        }
        else if (!is_number($id)) {
            // channel not in Moodle
            $this->setExtId($id);
        }
        else {
            print_error('');
        }

        $url = '/series';
        $url .= '/' . $this->getExtId();

        $ch = mod_opencast_apicall::sendRequest($url, 'GET', null, false, true, null, false, $haltonerror);
        if (!$ch && !$haltonerror) {
            // no need to go further
            return false;
        }

        $ch_metadata = mod_opencast_apicall::sendRequest($url . '/metadata', 'GET', null, false, true, null, false);
        $ch_acls = mod_opencast_apicall::sendRequest($url . '/acl', 'GET', null, false, true, null, false);
        foreach ($ch_acls as $acl) {
            if ($acl->allow == true && $acl->action == 'write' && preg_match('/^ROLE\_([^\_]+)\_USER$/', $acl->role,
                            $matches)
            ) {
                $this->setProducer($matches[1]);
            }
        }

        $this->setChannelName((string)$ch->title);

        // $metadata_fields = self::search_collection($ch_metadata, 'flavor', 'dublincore/series', 'fields');
        // $this->setLicense(self::search_collection($metadata_fields, 'id', 'license', 'value'));
        //		$this->setAllowAnnotations(trim((string)$ch->allow_annotations) == 'yes');
        //        $this->setOrganizationDomain((string)$ch->organization_name);

        //		$this->setUploadForm($ch->urls->url[1]);
        //		$this->setEditLink($ch->urls->url[4]);

        if (!$inmoodle || $returninfo) {
            // we just want the channel info
            $ch->meadata = $ch_metadata;
            $ch->acl = $ch_acls;

            return $ch;
        }
    }

    /**
     * @param $array
     * @param $where_key
     * @param $where_value
     * @param $wanted_key
     *
     * @return null
     */
    public static function search_collection($array, $where_key, $where_value, $wanted_key) {
        // TODO warning, this will not work anymore, as collections are not sent back by the API anymore, (see e-mail from Sven, 20150730)
        foreach ($array as $element) {
            if ($element->$where_key == $where_value) {
                return $element->$wanted_key;
            }
        }

        return null;
    }

    /**
     * Update data
     *
     * @return boolean true if success
     */
    function update() {

        // TODO we might not even need to do that, license breaks things anyway

//        $url = '/series/' . $this->getExtId() . '/metadata?type=dublincore/series';
//
//        $data = [
//                'metadata' => json_encode([
//                        ['id' => 'license', 'value' => $this->getLicense()],
//                ], JSON_UNESCAPED_SLASHES)
//        ];
//
//        mod_opencast_apicall::sendRequest($url, 'PUT', $data); // will break on error

        return true;
    }

    /**
     * Finds the external_authority name of this LMS at the SWITCHcast server
     *
     * @return string|bool name if found, false if not found
     */
    public function getExternalAuthName($id = 0) {

        // TODO remove as we're now only using local org (i.e. unil.ch for us)

        if ($id === 0) {
            $id = $this->getValueForKey('external_authority_id');
        }

        $url = $this->getValueForKey('switch_api_host');
        $url .= '/users/' . $this->getSysAccount();
        $url .= '/channels';
        $url .= '/new.xml';

        $new = mod_opencast_oldapi::sendRequest($url, 'GET');

        if (count($new->external_authority_id[0]) > 0) {
            foreach ($new->external_authority_id[0] as $external_auth) {
                $attr = $external_auth->attributes();
                if ((int)$attr['value'] == (int)$id) {
                    return (string)$external_auth;
                }
            }
        }

        return false;
    }

    /**
     * getAllLicences
     *
     * @return array
     */
//    public function getAllLicenses() {
//
//        if (!$this->getExtId()) {
//            // series not already on back-end, fallback:
//            // TODO this has changed (noticed on 20151204, actually se e-mail from Sven 20150730), don't know where to find the info now, as it's not documented
//            $licenses = [
//                    "ALLRIGHTS"   => "EVENTS.LICENSE.ALLRIGHTS",
//                    "CC-BY" => "EVENTS.LICENSE.CCBY",
//                    "CC-BY-NC"    => "EVENTS.LICENSE.CCBYNC",
//                    "CC-BY-NC-ND" => "EVENTS.LICENSE.CCBYNCND",
//                    "CC-BY-NC-SA" => "EVENTS.LICENSE.CCBYNCSA",
//                    "CC-BY-ND" => "EVENTS.LICENSE.CCBYND",
//                    "CC-BY-SA"    => "EVENTS.LICENSE.CCBYSA",
//                    "CC0" => "EVENTS.LICENSE.CC0"
//            ];
//        }
//        else {
//            $series_metadata =
//                    mod_opencast_apicall::sendRequest('/series/' . $this->getExtId() . '/metadata?type=dublincore/series',
//                            'GET');
//            $licenses = self::search_collection($series_metadata, 'id', 'license', 'collection');
//        }
//
//        foreach ($licenses as $key => &$value) {
//            $value = get_string("license_" . $value, 'opencast');
//        }
//
//        return $licenses;
//    }

    /**
     * gets all templates enabled at the SwitchCast server
     *
     * @return array
     */
//    public static function getAllTemplates() {
//        return [];

        //        $url = self::getValueForKey('switch_api_host');
        //        $url .= '/users/' . self::getValueForKey('default_sysaccount');
        //        $url .= '/channels';
        //        $url .= '/new.xml';
        //
        //        $new = mod_opencast_oldapi::sendRequest($url, 'GET');
        //        $templates = [];
        //
        //        if (count($new->template_id[0]) > 0) {
        //            foreach ($new->template_id[0] as $template) {
        //                $attr = $template->attributes();
        //                $value = (string)$attr['value'];
        //                $templates[$value] = (string)$template;
        //            }
        //
        //            return $templates;
        //        }
        //        else {
        //            return [];
        //        }
//    }

    /**
     * Read data from db
     *
     */
    public function hasReferencedChannels() {
        $referenced_channels = $this->getAllReferences();

        return count($referenced_channels);
    }

    /**
     * Read data from db
     *
     */
    public function getAllReferences($ext_id = false) {
        global $DB;

        if (!$ext_id) {
            $ext_id = $this->getExtId();
        }

        $records = $DB->get_records('opencast', ['ext_id' => $ext_id]);

        foreach ($records as $record) {
            $count[] = $record->id;
        }

        return $count;
    }

    /**
     * setExtId
     *
     * @param string $a_val
     */
    public function setExtId($a_val) {
        $this->ext_id = (string)$a_val;
    }

    /**
     * getExtId
     *
     * @return string
     */
    public function getExtId() {
        return $this->ext_id;
    }

    /**
     * setExtId
     *
     * @param string $a_val
     */
    public function setChannelName($a_val) {
        $this->title = $a_val;
    }

    /**
     * getExtId
     *
     * @return string
     */
    public function getChannelName() {
        return $this->title;
    }

    /**
     * setEstimatedContentInHours
     *
     * @param int $a_val
     */
    public function setEstimatedContentInHours($a_val) {
        $this->estimatet_content_in_hours = $a_val;
    }

    /**
     * getEstimatedContentInHours
     *
     * @return int
     */
    public function getEstimatedContentInHours() {
        return $this->estimatet_content_in_hours;
    }

    /**
     * setLifetimeOfContentinMonth
     *
     * @param int $a_val
     */
    public function setLifetimeOfContentinMonth($a_val) {
        $this->LifetimeOfContentinMonth = $a_val;
    }

    /**
     * getLifetimeOfContentinMonth
     *
     * @return int
     */
    public function getLifetimeOfContentinMonth() {
        return $this->LifetimeOfContentinMonth;
    }

    /**
     * setDepartment
     *
     * @param string $a_val
     */
    public function setDepartment($a_val) {
        $this->department = $a_val;
    }

    /**
     * getDepartment
     *
     * @return string
     */
    public function getDepartment() {
        return $this->department;
    }

    /**
     * setLicense
     *
     * @param string $license
     */
//    public function setLicense($license) {
//        $this->license = $license;
//    }

    /**
     * getLicense
     *
     * @return string
     */
//    public function getLicense() {
//        return $this->license;
//    }

    /**
     *
     * @param type $a_val
     */
//    public function setTemplateId($a_val) {
//        $this->template_id = (int)$a_val;
//    }

    /**
     *
     * @return integer
     */
//    public function getTemplateId() {
//        return $this->template_id;
//    }

    /**
     * setInvitingPossible
     *
     * @param int $a_val
     */
    public function setInvitingPossible($a_val) {
        $this->inviting_possible = $a_val;
    }

    /**
     * getInvitingPossible
     *
     * @return int
     */
    public function getInvitingPossible() {
        return $this->inviting_possible;
    }

    /**
     * setIvt
     *
     * @param int $a_val
     */
    public function setIvt($a_val) {
        $this->ivt = $a_val;
    }

    /**
     * getIvt
     *
     * @return int
     */
    public function getIvt() {
        return $this->ivt;
    }

    /**
     * setUploadForm
     *
     * @param string $a_val
     */
    public function setUploadForm($a_val) {
        $this->upload_form = $a_val;
    }

    /**
     * getUploadForm
     *
     * @return string
     */
    public function getUploadForm() {
        return $this->upload_form;
    }

    /**
     * getEditLink
     *
     * @return string
     */
    public function getEditLink() {
        $url = trim($this->getValueForKey('switch_admin_host'), '/') . '/admin-ng/index.html#/events/series';

        return $url;
    }

    /**
     * @return boolean
     */
    public function getAllowAnnotations() {
        return $this->allow_annotations;
    }

    /**
     * @param string $organization_domain
     */
    public function setOrganizationDomain($organization_domain) {
        $this->organization_domain = $organization_domain;
    }

    /**
     * @param boolean $allow_annotations
     */
    public function setAllowAnnotations($allow_annotations) {
        $this->allow_annotations = $allow_annotations;
    }

    /**
     * @param string $kind periodic|test
     */
    public function setChannelKind($kind) {
        $this->channel_kind = $kind;
    }

    /**
     * return string $kind
     */
    public function getChannelKind() {
        return $this->channel_kind;
    }
}


