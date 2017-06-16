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

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_heading('opencast/operationsettings',
            get_string('operationsettings', 'opencast'), ''));

    $settings->add(new admin_setting_configtext('opencast/moreinfo_url', get_string('moreinfo_url', 'opencast'),
            get_string('moreinfo_url_desc', 'opencast'), '', PARAM_URL, 50));

    $settings->add(new admin_setting_configcheckbox('opencast/display_select_columns',
            get_string('display_select_columns', 'opencast'),
            get_string('display_select_columns_desc', 'opencast', $CFG->dataroot), '0'));

    $settings->add(new admin_setting_configcheckbox('opencast/allow_userupload',
            get_string('allow_userupload', 'opencast'), get_string('allow_userupload_desc', 'opencast'), '0'));

    $settings->add(new admin_setting_configselect('opencast/userupload_maxfilesize',
            get_string('userupload_maxfilesize', 'opencast'), get_string('userupload_maxfilesize_desc', 'opencast'),
            10 * 1024 * 1024, mod_opencast_series::getMaxfilesizes()));

    $settings->add(new admin_setting_configtext('opencast/uploadfile_extensions',
            get_string('uploadfile_extensions', 'opencast'), get_string('uploadfile_extensions_desc', 'opencast'),
            'mov, mp4, m4v, avi, mpg, mpe, mpeg, mts, vob, flv, mkv, dv, mp3, aac, wav, wma, wmv, divx', PARAM_RAW,
            50));

    $settings->add(new admin_setting_heading('opencast/adminsettings', get_string('adminsettings', 'opencast'),
            ''));

    $settings->add(new admin_setting_configtext('opencast/switch_api_host',
            get_string('switch_api_host', 'opencast'), get_string('switch_api_host_desc', 'opencast'),
            'https://api.cast.switch.ch/api/v2', PARAM_URL, 50));

    $settings->add(new admin_setting_configtext('opencast/switch_api_username', get_string('switch_api_username', 'opencast'),
            get_string('switch_api_username_desc', 'opencast'), '', PARAM_RAW));

    $settings->add(new admin_setting_configpasswordunmask('opencast/switch_api_password',
            get_string('switch_api_password', 'opencast'), get_string('switch_api_password_desc', 'opencast'), '',
            PARAM_RAW_TRIMMED));

    $settings->add(new admin_setting_configtext('opencast/switch_admin_host',
            get_string('switch_admin_host', 'opencast'), get_string('switch_admin_host_desc', 'opencast'),
            'https://cast.switch.ch/', PARAM_URL, 50));

    $settings->add(new admin_setting_configtext('opencast/import_workflow',
            get_string('import_workflow', 'opencast'),
            '', 'switchcast-import-api-1.0', PARAM_RAW_TRIMMED, 50));

    $settings->add(new admin_setting_configtext('opencast/pubchannel_videoplayer',
            get_string('pubchannel_videoplayer', 'opencast'),
            '', 'switchcast-player', PARAM_RAW_TRIMMED, 50));

    $settings->add(new admin_setting_configtext('opencast/pubchannel_download',
            get_string('pubchannel_download', 'opencast'),
            '', 'switchcast-api', PARAM_RAW_TRIMMED, 50));

    $settings->add(new admin_setting_configtext('opencast/pubchannel_annotate',
            get_string('pubchannel_annotate', 'opencast'),
            '', 'switchcast-annotate', PARAM_RAW_TRIMMED, 50));

    $settings->add(new admin_setting_configtext('opencast/thumbnail_flavors',
            get_string('thumbnail_flavors', 'opencast'),
            '', 'presenter/search+preview, presentation/search+preview', PARAM_RAW_TRIMMED, 50));

    $settings->add(new admin_setting_configtext('opencast/local_cache_time',
            get_string('local_cache_time', 'opencast'), get_string('local_cache_time_desc', 'opencast'), '1200',
            PARAM_INT));

    $settings->add(new admin_setting_configcheckbox('opencast/logging_enabled',
            get_string('logging_enabled', 'opencast'),
            get_string('logging_enabled_desc', 'opencast', $CFG->dataroot), '0'));

    $settings->add(new admin_setting_configtext('opencast/uid_field', get_string('uid_field', 'opencast'),
            get_string('uid_field_desc', 'opencast'), 'username', PARAM_RAW));

    $settings->add(new admin_setting_configtext('opencast/curl_proxy', get_string('curl_proxy', 'opencast'),
            get_string('curl_proxy_desc', 'opencast'), '', PARAM_URL, 50));

    $settings->add(new admin_setting_configtext('opencast/curl_timeout', get_string('curl_timeout', 'opencast'),
            get_string('curl_timeout_desc', 'opencast'), '50', PARAM_INT));

    $settings->add(new admin_setting_configcheckbox('opencast/use_ipaddr_restriction',
            get_string('use_ipaddr_restriction', 'opencast'),
            get_string('use_ipaddr_restriction_desc', 'opencast'), '1'));

}

