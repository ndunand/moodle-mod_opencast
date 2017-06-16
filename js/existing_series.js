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

(function ($) {

    $(document).ready(function () {

        var setChannelLink = M.cfg.wwwroot + '/mod/opencast/get_series_info.php';


        // changing selected existing channel
        $("#id_ext_id").change(function () {
            $('input[type=submit]').prop('disabled', true);
            setChannel();
        })

        $("#id_channelnew").change(function () {
            if ($("#id_channelnew option:selected").val() == 'new channel') {
                // selected "new channel"
                unsetChannel();
            }
            else {
                // selected "existing channel"
                $('input[type=submit]').prop('disabled', true);
                setChannel();
            }
        })


        var setChannel = function () {
            console.log('trying to set channel info');
            $.ajax({
                url:     setChannelLink,
                data:    {
                    ext_id: $("#id_ext_id option:selected").val()
                },
                success: function (data) {
                    console.log('filling channel info');
                    fillChannel(data);
                }
            });
        }


        var unsetChannel = function () {
            $('#id_newchannelname').val('');
//            $('#id_license').val('');
            $('#id_department').val('');
            $('#id_annotations').val('');
//            $('#id_template_id').val('');
            $('input[type=submit]').prop('disabled', false);
        }


        var fillChannel = function (data) {
            console.log('fill 1');
            try {
                var json = JSON.parse(data);
            }
            catch (e) {
                // Logged out of LMS
                document.location.href = M.cfg.wwwroot;
            }
//            console.log('fill 2 : '+json.license);
            $("#id_newchannelname").val(json.title);
//            $('#id_license').setSelect(json.license);
            console.log('fill 3');
//            $("#id_department").val(json.department["0"]);
//            $("#id_annotations").val(json.allow_annotations["0"]);
//            $("#id_template_id").val(json.template_id);
            $('input[type=submit]').prop('disabled', false);
            console.log('fill 4');
        }

        $.fn.extend({
            setSelect: function (value) {
                var $options = $(this).find('option');
                $options.prop('selected', false);
                $options.each(function () {
                    if ($(this).val() == value) {
                        $(this).prop('selected', true);
                    }
                });
                return $(this);
            }
        });


        if ($("#id_channelnew option:selected").val() != 'new channel') {
            $('input[type=submit]').prop('disabled', true);
            setChannel();
        }

    });

})(jQuery)
