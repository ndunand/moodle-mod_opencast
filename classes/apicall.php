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
 * @author     Fabian Schmid <fabian.schmid@ilub.unibe.ch>
 * @author     Martin Studer <ms@studer-raimann.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class mod_opencast_apicall {

    /**
     * Sends an API request to the NEW Matterhorn server
     *
     * @param         $url
     * @param string  $request_type   request type
     * @param array   $data           input data for POST/PUT
     * @param boolean $return_rawdata return raw XML?
     * @param boolean $usecache       try to use cache?
     * @param string  $file           video file to upload
     * @param bool    $runas          run the API request as the current logged in user?
     * @param bool    $haltonerror    halt on error, or just return FALSE
     *
     * @return bool|stdClass|array result object or false if error
     * @throws moodle_exception
     */
    static function sendRequest($url, $request_type, $data = null, $return_rawdata = false, $usecache = true,
                                $file = null, $runas = true, $haltonerror = true) {

        global $CFG, $USER;

        $request_url = mod_opencast_series::getValueForKey('switch_api_host') . $url;
        $cache_time = mod_opencast_series::getValueForKey('local_cache_time');
        $cache_dir = $CFG->dataroot . '/cache/mod_opencast';

        if ($request_type !== 'GET') {
            // a modification has been made, clear the cache for consistency
            self::clear_cache($cache_dir, $request_type, $request_url);
        }
        if (!file_exists($cache_dir)) {
            mod_opencast_log::write("CACHE : initializing empty cache");
            mkdir($cache_dir);
        }

        if (is_array($data) || is_object($data)) {
            $data_json = json_encode($data);
        }

        mod_opencast_log::write("REQUEST " . $request_type . " " . $request_url);
        mod_opencast_log::write("INPUT " . print_r($data, true));

        $cache_filename = $cache_dir . '/' . self::hashfilename($request_url);

        if ($usecache && (string)$request_type === 'GET' && $cache_time && $cache_dir && file_exists($cache_filename) && (time() - filemtime($cache_filename) < $cache_time)) {
            // use the appropriate cached file
            mod_opencast_log::write("CACHE : using cached file " . $cache_filename);
            $output = file_get_contents($cache_filename);
        }
        else {
            // no cache for this request
            mod_opencast_log::write("CACHE : no cached file " . $cache_filename);

            libxml_use_internal_errors(true);

            $curl_request = curl_init();
            curl_setopt($curl_request, CURLOPT_SAFE_UPLOAD, true);
            curl_setopt($curl_request, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl_request, CURLOPT_CUSTOMREQUEST, $request_type);

            if ($runas) {
                $role_user = 'ROLE_AAI_USER_' . mod_opencast_user::getExtIdFromMoodleUserId($USER->id);
                curl_setopt($curl_request, CURLOPT_HTTPHEADER, ['X-RUN-WITH-ROLES: ' . $role_user]);
            }
            else {
                curl_setopt($curl_request, CURLOPT_HTTPHEADER, ['X-RUN-WITH-ROLES: ROLE_EXTERNAL_APPLICATION']);
            }

            if (isset($file)) {
                if (!filesize($file) || !is_readable($file)) {
                    mod_opencast_log::write("CURL UPLOAD ERROR : empty or unreadable file");
                    if ($haltonerror) {
                        throw new moodle_exception('uploaderror', 'opencast');
                    }

                    return false;
                }

                $curl_file = new CURLFile($file);

                curl_setopt($curl_request, CURLOPT_TIMEOUT_MS, 300000);
                //                curl_setopt($curl_request, CURLOPT_PUT, true); // must be set, elsewise the multipart info will also be sent
                //                curl_setopt($curl_request, CURLOPT_BINARYTRANSFER, 1);
                //                curl_setopt($curl_request, CURLOPT_VERBOSE, (bool)mod_opencast_obj::getValueForKey('logging_enabled'));
            }
            else {
                curl_setopt($curl_request, CURLOPT_TIMEOUT_MS,
                        (int)mod_opencast_series::getValueForKey('curl_timeout') * 1000);
            }

            curl_setopt($curl_request, CURLOPT_SSL_VERIFYPEER, false);

            curl_setopt($curl_request, CURLOPT_USERPWD, mod_opencast_series::getValueForKey('switch_api_username').':'.mod_opencast_series::getValueForKey('switch_api_password'));

            curl_setopt($curl_request, CURLOPT_URL, $request_url);
            if (mod_opencast_series::getValueForKey('curl_proxy')) {
                curl_setopt($curl_request, CURLOPT_PROXY, mod_opencast_series::getValueForKey('curl_proxy'));
            }
            if (is_array($data)) {
                $postfields = [];
                if ($file) {
                    $postfields['presentation'] = $curl_file;
                }
                foreach ($data as $key => $value) {
                    $postfields[$key] = $value; // htmlentities($value, ENT_NOQUOTES); // not necessary anymore
                }
                curl_setopt($curl_request, CURLOPT_POSTFIELDS, $postfields);
                curl_setopt($curl_request, CURLOPT_HTTPHEADER, ['Accept: application/v1.0.0+json']);
            }

            static $totaldeltatime = 0;
            $timebeforecurlexec = time();
            $output = curl_exec($curl_request);
            $deltatime = time() - $timebeforecurlexec;
            $totaldeltatime += $deltatime;
            $curl_errno = curl_errno($curl_request); // 0 if fine
            $response_details = curl_getinfo($curl_request);

            if (isset($file)) {
                if ($curl_errno) {
                    curl_close($curl_request);
                    mod_opencast_log::write("CURL UPLOAD ERROR : no. " . $curl_errno);
                    mod_opencast_log::write("                  : " . $output);
                    if ($haltonerror) {
                        throw new moodle_exception('uploaderror', 'opencast');
                    }

                    return false;
                }
            }

            curl_close($curl_request);

            if ($output && (string)$request_type === 'GET' && (isset($response_details) && $response_details['http_code'] < 400) && ($cache_time && $cache_dir && is_writable($cache_dir))) {
                // write cache to file, only if response is not an error
                mod_opencast_log::write("CACHE : writing output (curl took $deltatime s - $totaldeltatime) to cache file " . $cache_filename);
                $fh_w = fopen($cache_filename, 'w');
                fwrite($fh_w, $output);
                fclose($fh_w);
            }
        }

        if ($return_rawdata) {
            return $output;
        }

        if ($output === false) {
            if ($curl_errno) {
                mod_opencast_log::write("CURL REQUEST ERROR : no. " . $curl_errno);
            }
            if ($haltonerror) {
                print_error('switch_api_down', 'opencast');
            }

            return false;
        }

        mod_opencast_log::write("OUTPUT " . $output);

//        $output = html_entity_decode($output, ENT_NOQUOTES); // not necessary anymore

        try {
            $return = json_decode($output);
        }
        catch (Exception $e) {
            if ($haltonerror) {
                print_error('api_fail', 'opencast', null, $e->getMessage() . $e->getCode());
            }

            return false;
        }

        if (isset($response_details) && $response_details['http_code'] >= 400) {
            if ($response_details['http_code'] == 404) {
                if ($haltonerror) {
                    print_error('api_404', 'opencast', null, $response_details['http_code']);
                }
            }
            if ($haltonerror) {
                print_error('api_fail', 'opencast', null, $response_details['http_code']);
            }

            return false;
        }

        return $return;
    }

    /**
     * @param $dirname
     * @param $request_type
     * @param $request_url
     *
     * @return bool
     */
    static function clear_cache($dirname, $request_type, $request_url) {
        $request_url = str_replace(mod_opencast_series::getValueForKey('switch_api_host'), '', $request_url);
        $request_url = rtrim($request_url, '/');
        switch ($request_type) {
            case 'DELETE':
                // DELETE'ing an event -> clear all series' list of events cache
                $filter = 'events_filter_series_';
                break;
            case 'POST':
                if ($request_url == '/events') {
                    // adding an event -> clear all series' list of events cache
                    $filter = 'events_filter_series_';
                }
                else if ($request_url == '/series') {
                    // adding a series -> clear global series list
                    $filter = 'series_';
                    return unlink($dirname . DIRECTORY_SEPARATOR . $filter); // only delete this very file
                }
                break;
            case 'PUT':
                if (preg_match('/\/events\/([0-9a-zA-Z\-]+)/', $request_url, $matches)) {
                    // ipdating an event's ACLs or metadata
                    $filter = 'events_' . self::hashfilename($matches[1]);
                }
                else if (preg_match('/\/series\/([0-9a-zA-Z\-]+)/', $request_url, $matches)) {
                    // updating a series 'producer'
                    $filter = 'series_' . self::hashfilename($matches[1]) . '_acl';
                }
                break;
            default:
                return false;
        }
        // make sure we're filtering and that cache is set up correctly
        if (!isset($filter)) {
            return false;
        }
        if (!file_exists($dirname)) {
            return false;
        }
        if (is_file($dirname) || is_link($dirname)) {
            return unlink($dirname);
        }
        // proceed to deleting relevant file(s)
        $reason = $request_type . ' ' . $request_url;
        $dir = dir($dirname);
        while (false !== $entry = $dir->read()) {
            if ($entry == '.' || $entry == '..') {
                continue;
            }
            if (strstr($entry, $filter) !== false) {
                unlink($dirname . DIRECTORY_SEPARATOR . $entry);
                mod_opencast_log::write("CACHE : deleting cache file $entry because $reason");
            }
        }
        $dir->close();

        return true;
    }

    /**
     * @param string $url
     *
     * @return mixed
     */
    static function hashfilename($url = '') {
        $f = str_replace(mod_opencast_series::getValueForKey('switch_api_host'), '', $url);
        $f = str_replace(mod_opencast_series::getValueForKey('default_sysaccount'), '', $f);
        $f = preg_replace('/[^a-zA-Z0-9]/', '_', $f);
        $f = preg_replace('/^(_)+/', '', $f);

        //        return sha1($f);
        return $f;
    }
}

