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

$seriesExtId = required_param('ext_id', PARAM_RAW_TRIMMED);

$sc_user = new mod_opencast_user();

$url = '/series/' . $seriesExtId;

if ($sc_user->getExternalAccount() != '') {
    $runas = true;
}
else {
    $runas = false;
}

$series = new mod_opencast_series();
$series->fetch($seriesExtId, false);

//$series = mod_opencast_apicall::sendRequest($url, 'GET', null, null, null, null, $runas);

$channel_details = [
        'title' => $series->title,
        //    'kind'                  => $channel->kind,
        //    'license'               => $series->getLicense(),
        //    'department'            => $channel->department,
        //    'allow_annotations'     => $channel->allow_annotations,
        //    'template_id'           => mod_opencast_obj::getTemplateIdFromName($channel->template_name),
];

echo json_encode($channel_details);

