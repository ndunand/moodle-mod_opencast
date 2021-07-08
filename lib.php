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

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $opencast Moodle {opencast} table DB record
 *
 * @return int newly created instance ID
 */
function opencast_add_instance($opencast) {
    global $DB, $USER;

    $opencast->timemodified = time();

    $scast = new mod_opencast_series();

    if (isset($opencast->newchannelname)) {
        $scast->setChannelName($opencast->newchannelname);
    }
    //$scast->setCourseId();
    //    $scast->setLicense($opencast->license);
//    $scast->setDepartment($opencast->department);
    $scast->setAllowAnnotations($opencast->allow_annotations == OPENCAST_ANNOTATIONS);
//    if (isset($opencast->template_id)) { // not set if creating new instance with existing channel
//        $scast->setTemplateId($opencast->template_id);
//    }
    $scast->setIvt($opencast->is_ivt);
    if (isset($opencast->inviting)) {
        $scast->setInvitingPossible($opencast->inviting);
    }
    $scast->setOrganizationDomain(mod_opencast_series::getOrganizationByEmail($USER->email));
    $opencast->organization_domain = $scast->getOrganization();

    if ($opencast->channelnew == OPENCAST_CHANNEL_NEW) {
        // New channel
        $scast->setProducer(mod_opencast_user::getExtIdFromMoodleUserId($USER->id));
        $scast->doCreate();
        $opencast->ext_id = $scast->getExtId();
    }
    else {
        // Existing channel
        $scast->setExtId($opencast->ext_id);
        $scast->update();
    }

    $opencast->id = $DB->insert_record('opencast', $opencast);

    $completiontimeexpected = !empty($opencast->completionexpected) ? $opencast->completionexpected : null;
    \core_completion\api::update_completion_date_event($opencast->coursemodule, 'opencast', $opencast->id, $completiontimeexpected);

    return $opencast->id;
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $opencast Moodle {opencast} table DB record
 *
 * @return bool true if everything went well
 */
function opencast_update_instance($opencast) {
    global $DB;

    $opencast->id = $opencast->instance;
    $opencast->timemodified = time();

    $scast = new mod_opencast_series();
    $scast->fetch($opencast->id);

    //$scast->setCourseId();
    //    $scast->setLicense($opencast->license);
    //    $scast->setDepartment($opencast->department);
    $scast->setAllowAnnotations($opencast->allow_annotations == OPENCAST_ANNOTATIONS);
    $scast->setIvt($opencast->is_ivt);
    if (!isset($opencast->inviting) || $opencast->is_ivt == false) {
        $opencast->inviting = false;
    }
    $scast->setInvitingPossible($opencast->inviting);

    // Existing channel
    $scast->setExtId($opencast->ext_id);
    $mod_opencast_update = $scast->update();

    $opencast->ext_id = $scast->getExtId();

    $moodle_update = $DB->update_record('opencast', $opencast);

    $completiontimeexpected = !empty($opencast->completionexpected) ? $opencast->completionexpected : null;
    \core_completion\api::update_completion_date_event($opencast->coursemodule, 'opencast', $opencast->id, $completiontimeexpected);

    return $mod_opencast_update && $moodle_update;
}

/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id the ID of the {opencast} DB record
 *
 * @return bool true if succesful
 */
function opencast_delete_instance($id) {
    global $DB;

    // make sure plugin instance exists
    if (!$opencast = $DB->get_record('opencast', ['id' => $id])) {
        return false;
    }

    // delete all clip members of this plugin instance
    if (!$DB->delete_records('opencast_cmember', ['opencastid' => $opencast->id])) {
        return false;
    }

    // delete plugin instance itself
    if (!$DB->delete_records('opencast', ['id' => $opencast->id])) {
        return false;
    }

    return true;
}

/**
 * Gets a full opencast record
 *
 * @param int $opencastid the ID of the {opencast} DB record
 *
 * @return object|bool The {opencast} DB record or false
 */
function opencast_get_opencast($opencastid) {
    global $DB;

    if ($opencast = $DB->get_record('opencast', ['id' => $opencastid])) {
        return $opencast;
    }

    return false;
}

/**
 * Implementation of the function for printing the form elements that control
 * whether the course reset functionality affects the opencast.
 *
 * @param object $mform form passed by reference
 */
function opencast_reset_course_form_definition(&$mform) {
    $mform->addElement('header', 'opencastheader', get_string('modulenameplural', 'opencast'));
    $mform->addElement('advcheckbox', 'reset_opencast', get_string('removeclipmembers', 'opencast'));
}

/**
 * Course reset form defaults.
 *
 * @return array
 */
function opencast_reset_course_form_defaults($course) {
    return ['reset_opencast' => 1];
}

/**
 * Actual implementation of the reset course functionality, delete all the
 * opencast clip members for course $data->courseid.
 *
 * @param object $data the data submitted from the reset course.
 *
 * @return array status array
 */
function opencast_reset_userdata($data) {
    global $CFG, $DB;

    $componentstr = get_string('modulenameplural', 'opencast');
    $status = [];

    if (!empty($data->reset_opencast)) {
        $DB->delete_records('opencast_cmember', ['courseid' => $data->courseid]);
        $status[] = [
                'component' => $componentstr, 'item' => get_string('removeclipmembers', 'opencast'), 'error' => false
        ];
    }

    return $status;
}

/**
 * @uses FEATURE_GROUPS
 * @uses FEATURE_GROUPINGS
 * @uses FEATURE_GROUPMEMBERSONLY
 * @uses FEATURE_MOD_INTRO
 * @uses FEATURE_COMPLETION_TRACKS_VIEWS
 * @uses FEATURE_GRADE_HAS_GRADE
 * @uses FEATURE_GRADE_OUTCOMES
 *
 * @param string $feature FEATURE_xx constant for requested feature
 *
 * @return mixed True if module supports feature, null if doesn't know
 */
function opencast_supports($feature) {
    switch ($feature) {
        case FEATURE_IDNUMBER:
            return false;
        case FEATURE_GROUPS:
            return true;
        case FEATURE_GROUPINGS:
            return false;
        case FEATURE_GROUPMEMBERSONLY:
            return false;
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
        case FEATURE_COMPLETION_HAS_RULES:
            return false;
        case FEATURE_GRADE_HAS_GRADE:
            return false;
        case FEATURE_GRADE_OUTCOMES:
            return false;
        case FEATURE_MOD_ARCHETYPE:
            return MOD_ARCHETYPE_OTHER;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_NO_VIEW_LINK:
            return false;
        case FEATURE_SHOW_DESCRIPTION:
            return true;

        default:
            return null;
    }
}

/**
 * Adds module specific settings to the settings block
 *
 * @param settings_navigation $settings       The settings navigation object
 * @param navigation_node     $opencastnode The node to add module settings to
 */
function opencast_extend_settings_navigation(settings_navigation $settings, navigation_node $opencastnode) {
    global $PAGE;

    $cmid = $PAGE->cm->id;
    $context = $PAGE->cm->context;
    $opencast = opencast_get_opencast($PAGE->cm->instance);

    if (has_capability('mod/opencast:isproducer', $context)
            || ($opencast->userupload && has_capability('mod/opencast:uploadclip', $context))) {
        $opencastnode->add(get_string('upload_clip', 'opencast'),
                new \moodle_url('/mod/opencast/upload_event.php?id=' . $cmid));
    }
    if (has_capability('mod/opencast:isproducer', $context) && $opencast->userupload) {
        $opencastnode->add(get_string('view_useruploads', 'opencast'),
                new \moodle_url('/mod/opencast/uploads.php?id=' . $cmid));
    }
    if (has_capability('mod/opencast:isproducer', $context)) {
        $sc_obj = new mod_opencast_series();
        $sc_obj->fetch($opencast->id);
        $opencastnode->add(get_string('edit_at_switch', 'opencast'), new \moodle_url($sc_obj->getEditLink()));
    }

    // NOTE ND : forget it because no way to make this open in a new window
    //    if (has_capability('mod/opencast:isproducer', $PAGE->cm->context)) {
    //        $sc_obj = new mod_opencast_obj();
    //        $sc_obj->read($PAGE->cm->instance);
    //        if ($sc_obj->isProducer(mod_opencast_user::getExtIdFromMoodleUserId($USER->id))) {
    //            $opencastnode->add(get_string('edit_at_switch', 'opencast'), new moodle_url($sc_obj->getEditLink()), navigation_node::TYPE_SETTING);
    //            $opencastnode->add(get_string('upload_clip', 'opencast'), new moodle_url($sc_obj->getUploadForm()), navigation_node::TYPE_SETTING);
    //        }
    //    }
}

/**
 * Obtains the automatic completion state for this opencast based on any conditions
 * present in the settings.
 *
 * @param object $course Course
 * @param object $cm     Course-module
 * @param int    $userid User ID
 * @param bool   $type   Type of comparison (or/and; can be used as return value if no conditions)
 *
 * @return bool True if completed, false if not, $type if conditions not set.
 */
//function opencast_get_completion_state($course, $cm, $userid, $type) {
//    global $CFG,$DB;
//
//    // Get opencast details
//    $opencast = $DB->get_record('opencast', array('id'=>$cm->instance), '*', MUST_EXIST);
//
//    // If completion option is enabled, evaluate it and return true/false
//    if($opencast->completionsubmit) {
//        $useranswer = opencast_get_user_answer($opencast, $userid);
//        return $useranswer !== false;
//    } else {
//        // Completion option is not enabled so just return $type
//        return $type;
//    }
//}

/**
 * Return a list of page types
 *
 * @param string   $pagetype       current page type
 * @param stdClass $parentcontext  Block's parent context
 * @param stdClass $currentcontext Current context of block
 */
function opencast_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $module_pagetype = ['mod-opencast-*' => get_string('page-mod-opencast-x', 'opencast')];

    return $module_pagetype;
}

/**
 * mod_opencast cron
 *
 * @return true
 */
function opencast_cron() {
    mtrace('mod_opencast: processing uploaded clips');
    mod_opencast_series::processUploadedClips();

    return true;
}

/**
 * This function receives a calendar event and returns the action associated with it, or null if there is none.
 *
 * This is used by block_myoverview in order to display the event appropriately. If null is returned then the event
 * is not displayed on the block.
 *
 * @param calendar_event $event
 * @param \core_calendar\action_factory $factory
 * @return \core_calendar\local\event\entities\action_interface|null
 */
function mod_opencast_core_calendar_provide_event_action(calendar_event $event,
                                                            \core_calendar\action_factory $factory) {
    $cm = get_fast_modinfo($event->courseid)->instances['opencast'][$event->instance];

    $completion = new \completion_info($cm->get_course());

    $completiondata = $completion->get_data($cm, false);

    if ($completiondata->completionstate != COMPLETION_INCOMPLETE) {
        return null;
    }

    return $factory->create_instance(
            get_string('view'),
            new \moodle_url('/mod/opencast/view.php', ['id' => $cm->id]),
            1,
            true
    );
}

