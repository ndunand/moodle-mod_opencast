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

function xmldb_opencast_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2013120100) {

        // Define fields to be added to table opencast
        $table = new xmldb_table('opencast');
        $field1 = new xmldb_field('userupload', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'inviting');
        $field2 = new xmldb_field('userupload_maxfilesize', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0',
                'userupload');

        // Conditionally launch add fields
        if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }
        if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }

        upgrade_mod_savepoint(true, 2013120100, 'mod', 'opencast');
    }

    if ($oldversion < 2013121600) {

        $table2 = new xmldb_table('opencast_uploadedclip');

        if (!$dbman->table_exists($table2)) {
            $dbman->install_one_table_from_xmldb_file($CFG->dirroot . '/mod/opencast/db/install.xml',
                    'opencast_uploadedclip');
        }

        upgrade_mod_savepoint(true, 2013121600, 'mod', 'opencast');
    }

    if ($oldversion < 2015070100) {

        // Define table matrix to be created
        $table = new xmldb_table('opencast');

        // Adding fields to table matrix
        $newField = $table->add_field('allow_annotations', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '1');
        $dbman->add_field($table, $newField);

        upgrade_plugin_savepoint(true, 2015070100, 'mod', 'opencast');
    }


    return true;
}

