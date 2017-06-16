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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * replacement for insufficient user_files_form class
 */
class mod_opencast_upload_form extends moodleform {

    function definition() {
        global $USER;

        $mform = $this->_form;

        $data = $this->_customdata['data'];
        $options = $this->_customdata['options'];

        $mform->addElement('header', 'mod_opencast_upload_form_hdr',
                get_string('scast_upload_form_hdr', 'opencast'));

        $mform->addElement('filemanager', 'files_filemanager', get_string('video_file', 'opencast'), null, $options);
        $mform->addRule('files_filemanager', get_string('required'), 'required', null, 'client');

        $mform->addElement('hidden', 'returnurl', $data->returnurl);
        $mform->setType('returnurl', PARAM_LOCALURL);

        $mform->addElement('text', 'cliptitle', get_string('video_title', 'opencast'), ['size' => 50]);
        $mform->setType('cliptitle', PARAM_RAW_TRIMMED);
        $mform->addRule('cliptitle', get_string('required'), 'required', null, 'client');

        $mform->addElement('text', 'clipsubtitle', get_string('video_subtitle', 'opencast'), ['size' => 50]);
        $mform->setType('clipsubtitle', PARAM_RAW_TRIMMED);

        $mform->addElement('text', 'clippresenter', get_string('video_presenter', 'opencast'));
        $mform->setType('clippresenter', PARAM_RAW_TRIMMED);
        $mform->setDefault('clippresenter', fullname($USER));

        $mform->addElement('text', 'cliplocation', get_string('video_location', 'opencast'), ['size' => 50]);
        $mform->setType('cliplocation', PARAM_RAW_TRIMMED);

        $this->add_action_buttons(true, get_string('savechanges'));

        $this->set_data($data);
    }

    function validation($data, $files) {

        $errors = [];

        $draftitemid = $data['files_filemanager'];
        if (file_is_draft_area_limit_reached($draftitemid, $this->_customdata['options']['areamaxbytes'])) {
            $errors['files_filemanager'] = get_string('userquotalimit', 'error');
        }

        if (!trim($data['cliptitle'])) {
            $errors['cliptitle'] = get_string('video_title_mandatory', 'opencast');
        }

        return $errors;
    }
}

