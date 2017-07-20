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

require_once('../../../config.php');

$action = optional_param('action', '', PARAM_ALPHA);
$onlycourse = optional_param('onlycourse', 0, PARAM_INT);
$bypassevenifactivitynotmigrated = optional_param('bypassevenifactivitynotmigrated', 0, PARAM_INT);

define('MIGRATE_CAST1_ACTIVITY_PREFIX', '(migrated from SWITCHcast 1) - ');
define('MIGRATE_CAST1_ACTIVITY_DEACTIVATE', true);
define('MIGRATE_CAST2_ACTIVITY_ACTIVATE', true);
define('MIGRATE_CAST_STOP_ON_EXTRA', true);
define('MIGRATE_CAST_DEBUG_DISPLAY', false);
define('MIGRATE_CAST_TRY_USING_DB_TRANSACTION', true);

if (MIGRATE_CAST_DEBUG_DISPLAY) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

require_login();

$context = context_system::instance();
require_capability('moodle/site:config', $context);

$PAGE->set_context($context);
$PAGE->set_url('/mod/opencast/switchcast_importer');
$PAGE->set_title('SWITCHcast1 to SWITCHcast2 migration');
$PAGE->set_heading('SWITCHcast1 to SWITCHcast2 migration');
$PAGE->requires->jquery();

echo $OUTPUT->header();
echo $OUTPUT->box_start('cast2migration', null, array('style' => 'padding: 2em;'));

if (get_plugin_version('opencast') < 2016060900) {
    die ('mod_opencast plugin version needs to be at least 2016060900');
}

if (get_plugin_version('switchcast') < 2015012800) {
    die ('mod_switchcast plugin version needs to be at least 2015012800');
}

if (!$action) {

    echo html_writer::tag('h1', 'SWITCHcast1 to SWITCHcast2 migration');
    echo html_writer::tag('h2', 'Migration checklist');
    echo html_writer::tag('p', 'Please make sure <strong>you have understood</strong> and applied each assumption below.');
    echo html_writer::checkbox('checklistbkp', '', false, 'Moodle database is backed up');
    echo html_writer::empty_tag('br');
    echo html_writer::checkbox('checklistsyncc1c2', '', false, 'you made sure with SWITCH that your Cast1 and Cast2 content are in sync');
    echo html_writer::empty_tag('br');
    echo html_writer::checkbox('checklistmodswitchcast', '', false, 'you have applied the <code>' . $CFG->dirroot . '/mod/opencast/switchcast_importer/mod_switchcast-make-readonly.patch</code> patch file to the SWITCHcast1 plugin (mod_switchcast)');
    echo html_writer::empty_tag('br');
    echo html_writer::checkbox('checklistmaint', '', false, 'Moodle is in maintenance mode');
    echo html_writer::empty_tag('br');
    echo html_writer::checkbox('checklistmodopencast', '', false, 'the SWITCHcast2 plugin (mod_opencast) is installed and setup correctly');
    echo html_writer::empty_tag('br');
    echo html_writer::checkbox('checklistmodopencast2', '', false, 'the SWITCHcast2 plugin (mod_opencast) is working properly – channel add, display, etc.');
    echo html_writer::empty_tag('br');
    echo html_writer::checkbox('checklisttestmigr', '', false, 'you have checked (or are checking) this migration within just one course, set in the settings below');
    echo html_writer::empty_tag('hr');

    echo html_writer::tag('h2', 'Migration parameters');
    echo html_writer::start_tag('form', array('method' => 'post', 'onsubmit' => 'return mod_opencast_migration_check_checklist()', 'enctype' => 'multipart/form-data'));
    echo html_writer::tag('p', 'JSON mappings file <br/><em>This file has should have been sent to you by the SWITCHcast team.</em>');
    echo html_writer::empty_tag('input', array('type' => 'file', 'name' => 'jsonfile'));
    echo html_writer::tag('p', '&nbsp;');
    echo html_writer::tag('p', 'For testing, migrate only SWITCHcast activities within this course <br/><em>If you first want to test the migration on only one course, enter its ID here.</em>');
    echo html_writer::empty_tag('input', array('name' => 'onlycourse'));
    echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()));
    echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'action', 'value' => 'migrate'));
    echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'bypassevenifactivitynotmigrated', 'value' => $bypassevenifactivitynotmigrated));
    echo html_writer::tag('p', '&nbsp;');
    if ($bypassevenifactivitynotmigrated) {
        echo html_writer::checkbox('bypassevenifactivitynotmigrated', '', true, 'migrate even though some SWITCHcast activities are not present in the mappings', ['disabled' => 'disabled']);
    }
    echo html_writer::empty_tag('hr');
    echo html_writer::tag('button', 'Start migration', array('type' => 'submit'));
    echo html_writer::end_tag('form');

    $script = <<<EOF
function mod_opencast_migration_check_checklist(){
    var okay = false;
    $('.cast2migration input[type=checkbox]').each(function(){
        if ($(this).is(':checked') == false) {
            var id = $(this).attr('id');
            alert('Please check that ' + $('label[for=' + id + ']').text());
            okay = false;
            return false;
        }
        okay = true;
    });
    return okay;
}
EOF;


    echo html_writer::script($script);

}

if ($action == 'migrate') {

    echo "<h1>Processing migration</h1>";

    require_sesskey();

    if (!isset($_FILES)) {
        die('JSON file not found');
    }

    if (!isset($_FILES['jsonfile'])) {
        die('JSON file not found');
    }

    $json = file_get_contents($_FILES['jsonfile']['tmp_name']);

    if (!$json) {
        die('JSON file not found');
    }

    $data = json_decode($json);

    if (!$data) {
        die('could not decode JSON data');
    }

    if (count($data->errors)) {
        die('migration file reports errors, please solve these before continuing (to acknowledge, take them out of the JSON file)');
    }

    if (!count($data->clips)) {
        die('no clips found in JSON file');
    }

    if (MIGRATE_CAST_TRY_USING_DB_TRANSACTION) {
        $transaction = $DB->start_delegated_transaction();
    }

    $mappings = array(
            'clips'    => array(),
            'channels' => array()
    );

    foreach ($data->clips as $clip) {
        $mappings['clips'][$clip->ext_id] = array(
                'cast1_id' => $clip->ext_id,
                'cast2_id' => $clip->cast2_event_id,
                'cast2_series_id' => $clip->cast2_series_id
        );
        $allow_annotations = ($clip->channel_allow_annotations == 'yes') ? (1) : (0);
        $mappings['channels'][$clip->channel_ext_id] = array(
                'cast1_id'          => $clip->channel_ext_id,
                'cast2_id'          => $clip->cast2_series_id,
                'allow_annotations' => $allow_annotations
        );
    }

    echo '<p>Found <!--' . count($mappings['clips']) . ' clips and -->' . count($mappings['channels']) . ' channels to migrate.</p>';

    $cast1_activities = $DB->get_records('switchcast');

    $activitynotmigrated = array();

    foreach ($cast1_activities as $cast1_activity) {
        if (in_array($cast1_activity->ext_id, array_keys($mappings['channels']))) {
            //        echo "<p>cast1 activity \"{$cast1_activity->name}\" will be migrated</p>";
        }
        else {
            $activitynotmigrated[$cast1_activity->ext_id] = $cast1_activity->name;
            //        echo "<p>cast1 activity {$cast1_activity->name} will NOT be migrated</p>";
        }
    }
    if (count($activitynotmigrated)) {
        $nbactivitynotmigrated = count(array_unique($activitynotmigrated));
        echo "<p>An extra {$nbactivitynotmigrated} SWITCHcast1 activities have been found in Moodle whose channel was not in the JSON mappings file – these can't be migrated.</p>";
        if (!$bypassevenifactivitynotmigrated) {
            stop_if_stop_on_extra('bypassevenifactivitynotmigrated', array_unique($activitynotmigrated));
        }
    }

    $cast1_clips = array();
    $cast1_cmembers = $DB->get_records('switchcast_cmember');
    foreach ($cast1_cmembers as $cast1_cmember) {
        if (!in_array($cast1_cmember->clip_ext_id, $cast1_clips)) {
            $cast1_clips[] = $cast1_cmember->clip_ext_id;
        }
    }

    $cast1_uploadedclips = $DB->get_records('switchcast_uploadedclip');
    foreach ($cast1_uploadedclips as $cast1_uploadedclip) {
        if (!in_array($cast1_uploadedclip->ext_id, $cast1_clips)) {
            $cast1_clips[] = $cast1_uploadedclip->ext_id;
        }
    }

    $clipnotmigrated = array();

    foreach ($cast1_clips as $cast1_clip) {
        if (in_array($cast1_clip, array_keys($mappings['clips']))) {
            //        echo "<p>cast1 clip \"{$mappings['clips'][$cast1_clip]->title}\" will be migrated</p>";
        }
        else {
            $clipnotmigrated[] = $cast1_clip;
            //        echo "<p>cast1 clip {$mappings['clips'][$cast1_clip]->title} will NOT be migrated</p>";
        }
    }
    if (count($clipnotmigrated)) {
        $nbclipnotmigrated = count(array_unique($clipnotmigrated));
//        echo "<p>An extra {$nbclipnotmigrated} clips have been found in Moodle but not in the JSON mappings file and can't be migrated.</p>";
//        stop_if_stop_on_extra();
    }

    echo "<br />";

    $studentroles = array_keys(get_archetype_roles('student'));

    $cast1_dbmodule = $DB->get_record('modules', array('name' => 'switchcast'), 'id', MUST_EXIST);
    $cast2_dbmodule = $DB->get_record('modules', array('name' => 'opencast'), 'id', MUST_EXIST);

    $activitymigrated = 0;
    $activityalreadymigrated = 0;
    $activitynotinthiscourse = 0;

    foreach ($cast1_activities as $cast1_activity) {
        // first, let's map the activities (i.e. channels to series)
        if (!in_array($cast1_activity->ext_id, array_keys($mappings['channels']))) {
            // not in the mappings, pass
            continue;
        }
        $courseid = $cast1_activity->course;
        if ($onlycourse && $courseid != $onlycourse) {
            // not migrating this course
            $activitynotinthiscourse++;
            continue;
        }
        if (strpos($cast1_activity->name, MIGRATE_CAST1_ACTIVITY_PREFIX) !== false) {
            // already migrated
            $activityalreadymigrated++;
            continue;
        }
        $thismapping = $mappings['channels'][$cast1_activity->ext_id];
        $cm_old = $DB->get_record('course_modules', array(
                'course'   => $cast1_activity->course,
                'module'   => $cast1_dbmodule->id,
                'instance' => $cast1_activity->id
        ), '*', MUST_EXIST);
        // create the new mod_opencast activity
        $cast2_activity = clone $cast1_activity;
        unset($cast2_activity->id);
        $cast2_activity->allow_annotations = $thismapping['allow_annotations'];
        $cast2_activity->ext_id = $thismapping['cast2_id'];
        $cast2_activity_testexists = clone $cast2_activity;
        unset($cast2_activity_testexists->intro);
        $newinstancealreadyexists = $DB->get_record('opencast', (array)$cast2_activity_testexists);
        if ($newinstancealreadyexists) {
            continue;
        }
        $newinstanceid = $DB->insert_record('opencast', $cast2_activity);
        // then create the corresponding new course module
        $cm_new = clone $cm_old;
        unset($cm_new->id);
        $cm_new->module = $cast2_dbmodule->id;
        $cm_new->instance = $newinstanceid;
        $cm_new->added = time();
        if (!MIGRATE_CAST2_ACTIVITY_ACTIVATE) {
            $cm_new->visible = 0;
        }
        $newcmid = $DB->insert_record('course_modules', $cm_new);
        $cm_new->id = $newcmid;
        $oldcmctx = context_module::instance($cm_old->id);
        $newcmctx = context_module::instance($cm_new->id);
        $roleswhocandownloadclips = get_roles_with_cap_in_context($oldcmctx, 'mod/switchcast:downloadclip')[0];
        foreach ($studentroles as $studentrole) {
            if (!in_array($studentrole, $roleswhocandownloadclips)) {
                // we have to deny this permission in the new module too
                role_change_permission($studentrole, $newcmctx, 'mod/opencast:downloadclip', CAP_PREVENT);
            }
        }
        // finally, place this new course module correctly after the original one
        $coursesection = $DB->get_record('course_sections', array('id' => $cm_old->section));
        $modulesequence = explode(',', $coursesection->sequence);
        $newmodulesequence = array();
        foreach ($modulesequence as $modid) {
            $newmodulesequence[] = $modid;
            if ($modid == $cm_old->id) {
                $newmodulesequence[] = $newcmid;
            }
        }
        $coursesection->sequence = implode(',', $newmodulesequence);
        $DB->update_record('course_sections', $coursesection);
        if (MIGRATE_CAST1_ACTIVITY_DEACTIVATE) {
            $cm_old->visible = 0;
            $DB->update_record('course_modules', $cm_old);
            $cast1_activity->name = MIGRATE_CAST1_ACTIVITY_PREFIX . $cast1_activity->name;
            $DB->update_record('switchcast', $cast1_activity);
        }
        // finally, rebuild the course case, so that the modinfo registers the new module ans its position
        $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
        course_modinfo::build_course_cache($course);
        $activitymigrated++;
    }

    $clipmigrated = 0;

    foreach ($cast1_clips as $cast1_clip) {
        if (!in_array($cast1_clip, array_keys($mappings['clips']))) {
            // not in the mappings, pass
            continue;
        }
        $thismapping = $mappings['clips'][$cast1_clip];
        // migrate clip members, i.e. invitations
        $cmembers = $DB->get_records('switchcast_cmember', array('clip_ext_id' => $cast1_clip));
        foreach ($cmembers as $cmember) {
            $cmember->clip_ext_id = $thismapping['cast2_id'];
            $cmember->opencastid = opencastid_from_switchcastid($cmember->switchcastid);
            if (!$cmember->opencastid) {
                continue;
            }
            //echo '<pre>'; print_r($cmember); die();
            unset($cmember->id);
            unset($cmember->switchcastid);
            $samerecord = $DB->get_record('opencast_cmember', array(
                    'clip_ext_id' => $cmember->clip_ext_id,
                    'userid'      => $cmember->userid,
                    'opencastid'  => $cmember->opencastid
            ));
            if ($samerecord) {
                continue;
            }
            $DB->insert_record('opencast_cmember', $cmember);
        }
        // migrate uploaded clip info
        $uploadedclip = $DB->get_record('switchcast_uploadedclip', array('ext_id' => $cast1_clip));
        if ($uploadedclip) {
            $uploadedclip->ext_id = $thismapping['cast2_id'];
            $samerecord = $DB->get_record('opencast_uploadedclip', array('ext_id' => $uploadedclip->ext_id));
            if ($samerecord) {
                continue;
            }
            $uploadedclip->opencastid = opencastid_from_switchcastid($uploadedclip->switchcastid);
            if (!$uploadedclip->opencastid) {
                $activity_exists = $DB->count_records('opencast', array('ext_id' => $thismapping['cast2_series_id']));
                if (!$activity_exists) { 
                    continue;
                }
            }
            unset($uploadedclip->id);
            unset($uploadedclip->switchcastid);
            $DB->insert_record('opencast_uploadedclip', $uploadedclip);
        }
        $clipmigrated++;
    }

    if (MIGRATE_CAST_TRY_USING_DB_TRANSACTION) {
        $transaction->allow_commit();
    }

    echo "<p><strong>Migration succesful!</strong> A total of {$activitymigrated} Moodle activities have been migrated successfully.</p>";
    if ($activityalreadymigrated) {
        echo "<p>An extra {$activityalreadymigrated} were already migrated and have been left untouched.</p>";
    }
    if ($activitynotinthiscourse) {
        echo "<p>An extra {$activitynotinthiscourse} were not in the selected course (id={$onlycourse}) and have been left untouched.</p>";
        echo "<p><strong>Do not forget to attempt a full migration after you have made the relevant checks.</strong></p>";
    }
    echo "<hr/>";
    echo "<h1>What to do now?</h1>";

    $fullmigrationtobedone = ($onlycourse) ? ('<li>attempt a full migration (i.e. not of only activities in this one course)</li>') : ('');

    echo <<<EOF
<ol>
    <li>check that the new OpenCast activities created are working properly, in particular :
        <ul>
            <li>that clip owners (when applicable) are set correctly</li>
            <li>that clip invitations (when applicable) are set correctly</li>
        </ul>
    </li>
    {$fullmigrationtobedone}
    <li>deactivate (<em>but don't uninstall</em>) the mod_switchcast (old SWITCHcast1 plugin) in Moodle's plugin administration</li>
    <li>eventually, uninstall the old mod_switchcast plugin</li>
</ol>
EOF;

}

echo $OUTPUT->box_end();
echo $OUTPUT->footer();

// TODO check for errors

// TODO factorize code

function opencastid_from_switchcastid($switchcastid) {
    global $DB, $mappings;
    $switchcast = $DB->get_record('switchcast', array('id' => $switchcastid));
    if (!$switchcast) {
        return false;
    }
    $opencastextid = $mappings['channels'][$switchcast->ext_id]['cast2_id'];
    $opencast = $DB->get_record('opencast', array(
            'ext_id' => $opencastextid,
            'course' => $switchcast->course
    ), 'id');
    if ($opencast) {
        return $opencast->id;
    }
    return false;
}

function get_plugin_version($pluginname = 'opencast') {
    global $CFG;
    $plugin = new stdClass();
    require($CFG->dirroot . '/mod/' . $pluginname . '/version.php');
    $$pluginname = clone $plugin;

    return $$pluginname->version;
}

function stop_if_stop_on_extra($checktobypass = false, $dump = false) {
    if (MIGRATE_CAST_STOP_ON_EXTRA) {
        echo "<p><strong>This should not happen – aborting.</strong></p>";
        if ($dump) {
            echo '<pre>';
            print_r($dump);
            echo '</pre>';
        }
        if ($checktobypass) {
            echo '<p>If this is acceptable, you can <form action="." method="post"><input type="hidden" value="1" name="'.$checktobypass.'" /><input type="submit" value="bypass this check"></form></p>';
        }
        die();
    }
}
