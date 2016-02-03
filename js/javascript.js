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
 * @copyright  2013 Université de Lausanne
 * @author     Nicolas Dunand <Nicolas.Dunand@unil.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

var modopencast_tbldsplaypar = {};

(function ($) {

    $(document).ready(function () {

        // filter section toggle
        $('a.opencast-filters-toggle').click(function () {
            $(this).toggleClass('expanded');
            $('div.opencast-filters').slideToggle();
            return false;
        });

        // show subtitles checkbox
        $('div#page').on('change', '#clip-show-subtitle', function () {
            console.log('clicked!');
            var $subtitles = $('table.opencast-clips div.cliplabel h3 div.subtitle'),
                table_params = $.toJSON;
            if ($(this).prop('checked')) {
                $subtitles.show();
            }
            else {
                $subtitles.hide();
            }
            modopencast_tbldsplaypar.showsubtitles = $(this).prop('checked');
        });

        // deferred event listener for sort links
        $('div#page').on('click', '.opencast-sortable a', function () {
            var a = $(this),
                newclass;

            if (a.hasClass('asc')) {
                newclass = 'sort desc';
            }
            else if (a.hasClass('desc')) {
                newclass = '';
            }
            else {
                newclass = 'sort asc';
            }
            $('.opencast-sortable a').removeAttr('class');
            a.attr('class', newclass);

            $('.menuopencast-pageno').val('1');
            scast_getclips();
            return false;
        });

    })

})(jQuery)


//var NDY = YUI().use("node", function(Y) {
//    var opencast_memberdisplay_click = function(e) {
//
//        var names = Y.all('div.opencasts-membersnames'),
//            btnShowHide = Y.all('a.opencast-memberdisplay');
//
//        btnShowHide.toggleClass('hidden');
//        names.toggleClass('hidden');
//
//        e.preventDefault();
//
//    };
//    Y.on("click", opencast_memberdisplay_click, "a.opencast-memberdisplay");
//});

