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
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class mod_opencast_log {

    /**
     * Writes timestamped data into log file
     *
     * @param string $data data to be written
     */
    static function write($data) {
        global $CFG;

        if (!mod_opencast_series::getValueForKey('logging_enabled')) {
            return;
        }

        $logdir = $CFG->dataroot . '/mod/opencast';
        if (!file_exists($logdir)) {
            mkdir($logdir, 0755, true);
        }

        $logfile = $logdir . '/opencast_api.log';
        if (!is_writable($logdir)) {
            print_error('nologfilewrite', 'opencast', null, $logfile);
        }

        $date = date("Y-m-d H:i:s (T)");
        $logged = error_log($date . "\n" . $data . "\n\n", 3, $logfile);
        if (!$logged) {
            print_error('nologfilewrite', 'opencast', null, $logfile);
        }
    }
}

