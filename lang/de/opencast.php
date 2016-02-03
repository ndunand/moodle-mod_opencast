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

$string['answered'] = 'Beantwortet';
$string['channel'] = 'Kanal';
$string['channelnew'] = 'Neuer Kanal';
$string['channelchoose'] = 'Wählen Sie einen vorhandenen Kanal aus';
$string['channelexisting'] = 'Vorhandener Kanal';
$string['completionsubmit'] = 'Als vervollständigt anzeigen, wenn der/die NutzerIn eine Auswahl trifft';
$string['displayhorizontal'] = 'Horizontale Anzeige';
$string['displaymode'] = 'Anzeigemodus';
$string['displayvertical'] = 'Vertikale Anzeige';
$string['opencast:downloadclip'] = 'Clips können heruntergeladen werden.';
$string['expired'] = 'Diese Aktivität wurde am {$a} beendet und steht nicht mehr länger zur Verfügung';
$string['fillinatleastoneoption'] = 'Bitte erfassen Sie mindestens zwei mögliche Antworten.';
$string['full'] = '(Voll)';
$string['opencastclose'] = 'Bis';
$string['opencastname'] = 'SWITCHcast-Name';
$string['opencastopen'] = 'Öffnen';
$string['opencastoptions'] = 'SWITCHcast-Optionen';
$string['chooseaction'] = 'Wählen Sie eine Aktion ...';
$string['limit'] = 'Beschränkung';
$string['limitanswers'] = 'Beschränken Sie die Anzahl erlaubter Antworten';
$string['modulename'] = 'SWITCHcast-Kanal';
$string['modulename_help'] =
        'Das SWITCHcast-Modul erlaubt Managern und Teachern das Anlegen und Managen eines SWITCHcast-Kanals direkt aus dem Moodle-Kurs.';
$string['modulenameplural'] = 'SWITCHcast-Kanäle';
$string['mustchooseone'] =
        'Vor dem Speichern müssen Sie zuerst eine Auswahl wählen. Die Änderungen wurden nicht gespeichert.';
$string['noresultsviewable'] = 'Die Resultate dürfen zur Zeit nicht angeschaut werden.';
$string['notanswered'] = 'Noch nicht beantwortet';
$string['notopenyet'] = 'Diese Aktivität steht erst per {$a} zur Verfügung';
$string['pluginadministration'] = 'SWITCHcast-Administration';
$string['pluginname'] = 'OpenCast';
$string['timerestrict'] = 'Beschränkt Antworten auf diese Zeitperiode';
$string['viewallresponses'] = '{$a} Antworten zeigen';
$string['withselected'] = 'Mit ausgewählten';
$string['yourselection'] = 'Ihre Auswahl';
$string['skipresultgraph'] = 'Grafik mit den Resultaten überpringen';
$string['moveselectedusersto'] = 'Verschiebe ausgewählte NutzerInnen zu...';
$string['numberofuser'] = 'Die Anzahl NutzerInnen';
$string['uid_field'] = 'AAI unique ID-Feld';
$string['uid_field_desc'] =
        'Feld im Nutzerprofil, welches die AAI Unique ID enthält. Form der Eingabe: &lt;fieldname&gt; ODER &lt;table::fieldid&gt;.';
$string['switch_api_host'] = 'SWITCHcast API URL';
$string['switch_api_host_desc'] = 'URL, unter welcher der SWITCHcast Web-Service kontaktiert wird.';
$string['switch_admin_host'] = ''; // TODO missing
$string['switch_admin_host_desc'] = ''; // TODO missing
$string['default_sysaccount'] = 'Default System Account';
$string['default_sysaccount_desc'] = 'Default Account, welcher für die SWITCHcast API-Calls verwendet wird.';
$string['sysaccount'] = 'System-Account für {$a}';
$string['sysaccount_desc'] = 'Account, welcher für die SWITCHcast API-calls von {$a} verwendet wird.';
$string['cacrt_file'] = 'CA CRT-File';
$string['cacrt_file_desc'] = 'Certification Authority Certificate-File.';
$string['crt_file'] = 'Certificate-File';
$string['crt_file_desc'] = 'x509 Server Certificate-File.';
$string['castkey_file'] = 'SwitchCast Key File';
$string['castkey_file_desc'] =
        'Das SWITCHcast Key File, bereitgestellt durch SWITCHcast, um die API-Calls zu signieren.';
$string['castkey_password'] = 'SwitchCast Key File Passwort';
$string['castkey_password_desc'] = 'Das Passwort, sofern benötigt, um das obige SWITCHcast Key File freizuschalten.';
$string['serverkey_file'] = 'Server Key-File';
$string['serverkey_file_desc'] = 'SSL Key-File dieses Servers.';
$string['serverkey_password'] = 'Server Key File-Passwort';
$string['serverkey_password_desc'] = 'Das Passwort, sofern benötigt, um das obige SSL Key-File freizuschalten.';
$string['enabled_institutions'] = 'Freigegebene Institutionen';
$string['enabled_institutions_desc'] =
        'Komma-getrennte Liste der freigegebenen Institutionen auf diesem Moodle-Server.';
$string['external_authority_host'] = 'URL der externen Authorität';
$string['external_authority_host_desc'] = 'URL der externen Authorität';
$string['external_authority_id'] = 'ID der externen Authorität';
$string['external_authority_id_desc'] = 'ID der externen Authorität bei SWITCHcast';
$string['metadata_export'] = 'Export der Metadaten';
$string['metadata_export_desc'] = '';
//$string['configuration_id'] = 'Configuration ID';
//$string['configuration_id_desc'] = '';
//$string['streaming_configuration_id'] = 'Streaming configuration ID';
//$string['streaming_configuration_id_desc'] = '';
$string['access'] = 'Zugang';
$string['access_desc'] = 'Zugangslevel für erstellte Kanäle.';
$string['misconfiguration'] =
        'Das Plugin wurde falsch eingerichtet. Bitte kontaktieren Sie Ihren Moodle-Administrator.';
$string['logging_enabled'] = 'Logs einschalten';
$string['logging_enabled_desc'] =
        'Loggen aller XML API-Calls und Antworten.<br />Die Log-Datei liegt unter {$a}/mod/opencast/opencast_api.log';
$string['display_select_columns'] = 'Nur benutzte Spalten anzeigen';
$string['display_select_columns_desc'] =
        'In der Videoclip-Liste nur die benutzten Spalten wie Besitzer oder Aktionen anzeigen. Dies hat einen Einfluss auf die Ladegeschwindigkeit, weil die Liste aller Videoclips bei jeder Anzeige neu geladen werden muss.';
$string['enabled_templates'] = 'Freigegebene Vorlagen (Templates)';
$string['enabled_templates_desc'] =
        'Führen Sie hier die SWITCHcast-Vorlagen auf, welche Sie für Ihre Institution freigeben möchten (wird nur beim Erstellen von Kanälen erzwungen).<br />Bitte eine Vorlage pro Linie im folgenden Format: <em>&lt;TEMPLATE_ID&gt;::&lt;TEMPLATE_NAME&gt;</em>.<br />Sie können den Namen der Vorlage weglassen (nicht aber die beiden Doppelpunkte), um den offiziellen SWITCHcast-Namen für die Vorlage zu verwenden.';
$string['newchannelname'] = 'Neuer Kanal-Name';
$string['license'] = 'Lizenz';
$string['months'] = '{$a} Monate';
$string['years'] = '{$a} Jahre';
$string['department'] = 'Departement/Fakultät';
$string['annotations'] = 'Annotationen';
$string['annotationsyes'] = 'erlaubt';
$string['annotationsno'] = 'nicht erlaubt';
$string['template_id'] = 'SWITCHcast-Vorlage';
$string['template_id_help'] =
        'SWITCHcast-Vorlage ("Template"), welche zur Kodierung der Videoclips verwendet wird. Die Vorlage definiert unter anderem die nach der Kodierung zur Verfügung stehenden Videoformate. Beachten Sie dazu die Instruktionen Ihrer Institution. <strong>Diese Einstellung kann nachher nicht mehr geändert werden.</strong>';
$string['is_ivt'] = 'Individueller Zugang pro Videoclip';
$string['inviting'] = 'Videoclip-Besitzer kann andere NutzerInnen einladen';
$string['clip_member'] = 'Eingeladene Clip-Mitglieder';
$string['channel_teacher'] = 'Teacher';
$string['untitled_clip'] = '(Videoclip ohne Titel)';
$string['no_owner'] = '(kein Besitzer)';
$string['owner_not_in_moodle'] = '(Der Besitzer ist nicht bekannt in Moodle)';
$string['clip_no_access'] = 'Sie haben keine Zugang zu diesem Videoclip.';
$string['upload_clip'] = 'Einen neuen Videoclip hochladen';
$string['edit_at_switch'] = 'Bearbeiten Sie diesen Kanal auf dem SWITCHcast-Server';
$string['edit_at_switch_short'] = 'Bei SWITCHcast bearbeiten';
$string['opencast:use'] = 'Zeige Inhalt des SWITCHcast-Kanals';
$string['opencast:isproducer'] = 'Als Produzent des SWITCHcast-Kanals registrieren (mit Zugriff auf alle Videoclips)';
$string['opencast:addinstance'] = 'Kann eine neue SWITCHcast-Aktivität hinzufügen';
$string['opencast:seeallclips'] = 'Kann alle Videoclips im Kurs anschauen.';
$string['opencast:uploadclip'] = 'Kann einen Videoclip via Moodle hinzufügen';
$string['nologfilewrite'] = 'Log-Files können nicht geschrieben werden: {$a}. Bitte prüfen Sie die Systemrechte.';
$string['noclipsinchannel'] = 'Dieser Kanal enthält keine Videoclips.';
$string['novisibleclipsinchannel'] = 'Dieser Kanal enthält keine Videoclips, auf welche Sie Zugriff haben.';
$string['user_notaai'] = 'Sie benötigen einen SWITCHaai-Account, um einen neuen Kanal zu erstellen.';
$string['user_homeorgnotenabled'] =
        'Um eine SWICHcast-Aktivität zu erstellen, muss Ihre HomeOrganization ({$a}) in den generellen Moodle-Einstellungen freigeschaltet sein. Bitte kontaktieren Sie Ihren Moodle-Administrator.';
$string['clip'] = 'Videoclip';
$string['cliptitle'] = 'Videoclip-Titel';
$string['presenter'] = 'Referent';
$string['location'] = 'Ort';
$string['recording_station'] = 'Aufnahmeort';
$string['date'] = 'Datum';
$string['owner'] = 'Besitzer';
$string['actions'] = 'Aktionen';
$string['editmembers'] = 'Mitglieder einladen';
$string['addmember'] = 'Mitglied hinzufügen';
$string['editmembers_long'] = 'In diesen Videoclip eingeladene NutzerInnen verwalten';
$string['editdetails'] = 'Metadaten bearbeiten';
$string['delete_clip'] = 'Videoclip löschen';
$string['flash'] = 'Streaming';
$string['mov'] = 'Desktop';
$string['m4v'] = 'Smartphone';
$string['context'] = 'Kontext';
$string['confirm_removeuser'] = 'Möchten Sie diesen Nutzer wirklich entfernen?';
$string['delete_clip_confirm'] = 'Möchten Sie diesen Clip wirklich löschen?';
$string['back_to_channel'] = 'Zur Kanal-Übersicht zurückkehren';
$string['channel_several_refs'] = 'Dieser Kanal wird in anderen Moodle-Aktivitäten verwendet.';
$string['set_clip_details'] = 'Metadaten des Videoclips erfassen';
$string['owner_no_switch_account'] =
        'Es ist nicht möglich, &laquo;{$a}&raquo; als Besitzer dieses Videoclips einzutragen, weil dieser Nutzer keinen SWITCHaai-Account besitzt.';
$string['nomoreusers'] = 'Es stehen keine Nutzer mehr zum Hinzufügen zur Verfügung.';
$string['nodepartment'] = 'Sie müssen ein Departement/eine Fakultät angeben.';
$string['setnewowner'] = 'Als neuen Besitzer festlegen';
$string['clip_owner'] = 'Videoclip-Besitzer';
$string['group_member'] = 'Gruppenmitglied';
$string['clip_uploader'] = 'Videoclip wurde hochgeladen';
$string['aaiid_vs_moodleid'] = 'Die AAI Unique ID zeigt nicht auf die korrekte Moodle-Nutzer-ID!';
$string['error_decoding_token'] = 'Fehler beim Dekodieren des Tokens: {$a}';
$string['error_opening_privatekey'] = 'Fehler beim Öffnen des privaten Key-Files: {$a}';
$string['error_decrypting_token'] = 'Fehler beim Entschlüsseln des Tokens: {$a}';
$string['channelhasotherextauth'] =
        'Dieser Kanal wurde bereits mit einer anderen externen Authorität verbunden: <em>{$a}</em>.';
$string['novisiblegroups'] = 'Diese Gruppeneinstellung steht für diese Aktivität nicht zur Verfügung.';
$string['nogroups_withoutivt'] =
        'Getrennte Gruppen sind nur möglich, wenn die Einstellung &laquo;Individueller Zugang pro Videoclip&raquo; weiter oben aktiviert ist.';
$string['itemsperpage'] = 'Videoclips pro Seite';
$string['pageno'] = 'Seite #';
$string['pagination'] =
        'Angezeigte Videoclips: <span class="opencast-cliprange-from"></span> bis <span class="opencast-cliprange-to"></span> von <span class="opencast-cliprange-of"></span>.';
$string['filters'] = 'Filter';
$string['resetfilters'] = 'Filter zurücksetzen';
$string['title'] = 'Titel';
$string['subtitle'] = 'Untertitel';
$string['showsubtitles'] = 'zeige Untertitel';
$string['recordingstation'] = 'Aufnahmeort';
$string['withoutowner'] = 'Ohne Besitzer';
$string['notavailable'] =
        'Dieser Aktivitätstyp wird zur Zeit getestet und steht noch nicht für produktive Zwecke zur Verfügung.';
$string['local_cache_time'] = 'XML-Cache Gültigkeitsdauer';
$string['local_cache_time_desc'] =
        'Wie viele Sekunden soll die XML-Antwort des SWITCHcast-Servers im Cache behalten werden? 0 bedeutet kein Caching.';
$string['removeowner'] = 'Besitzer entfernen';
$string['channel_not_found'] = 'Der verlinkte Kanal existiert nicht (mehr?).';
$string['channeldoesnotbelong'] =
        'Der verlinkte Kanal gehört zu einer anderen Organisation ({$a}), weswegen Sie ihn nicht modifizieren können. Nur ein Teacher von {$a} kann den Kanal modifizieren.';
$string['switch_api_down'] = 'Der SWITCHcast-Server antwortet nicht.';
$string['api_fail'] = 'Fehler bei der Kommunikation mit dem SWITCHcast-Server.';
$string['api_404'] = ''; // TODO
$string['badorganization'] = 'Die Organisation dieses Kanals ist nicht korrekt konfiguriert.';
$string['curl_proxy'] = 'Curl-Proxy';
$string['curl_proxy_desc'] =
        'Falls Curl einen Proxy benutzen muss, tragen Sie ihn in der Form <em>proxyhostname:port</em> ein';
$string['moodleaccessonly'] = 'Dieser Videoclip ist nur von innerhalb der entsprechenden Moodle-Aktivität zugänglich.';
$string['loggedout'] = 'Sie wurden ausgeloggt. Bitte laden Sie die Webseite neu.';
$string['redirfailed'] = 'Das Weiterleiten ist gescheitert.';
$string['allow_userupload'] = 'Uploads durch NutzerInnen erlauben';
$string['allow_userupload_desc'] =
        'Upload von Videoclips durch NutzerInnen direkt aus der Moodle-Aktivität in den SWITCHcast-Kanal. Die entsprechende Option innerhalb der spezifischen Aktivätät muss ebenfalls aktiviert werden.';
$string['userupload_maxfilesize'] = 'Maximale Dateigrösse pro Video';
$string['userupload_maxfilesize_desc'] =
        'Maximale Grösse pro Video, die ein Nutzer pro Upload hochladen kann. Grössere Dateien bis 2 GB laden Sie bitte direkt im SWITCHcast-Kanal hoch.';
$string['userupload_error'] =
        'Während dem Hochladen der Datei ist ein unerwarteter Fehler aufgetreten. Bitte versuchen Sie es nochmals.';
$string['fileis_notavideo'] = 'Die Datei ist keine Video-Datei. Der MIME-Typ ist: {$a}';
$string['pendingclips'] = 'Es werden zur Zeit {$a} Videoclips in diesem Kanal verarbeitet.';
$string['mypendingclips'] = 'Es werden zur Zeit {$a} Videoclips von Ihnen in diesem Kanal verarbeitet.';
$string['uploadedclips'] = '{$a} Videoclips wurden in diesem Kanal hochgeladen';
$string['myuploadedclips'] = 'Sie haben {$a} Videoclips in diesem Kanal hochgeladen';
$string['clipready_subject'] = 'Ihr Videoclip ist bereit';
$string['clipready_body'] = 'Ihr hochgeladener Videoclip "{$a->cliptitle}" ({$a->filename}) ist bereit. Sie finden ihn in der folgenden Moodle-Aktivität:

{$a->link}
';
$string['clipstale_subject'] = 'Ihr hochgeladener Videoclip hatte ein Problem.';
$string['clipstale_subject_admin'] = 'Ein hochgeladener Videoclip hatte ein Problem.';
$string['clipstale_body'] = 'Ihr hochgeladener Videoclip "{$a->filename}" wurde nicht kodiert. Versuchen Sie den Videoclip in der folgenden Moodle-Aktivität erneut hochzuladen:

{$a->link}
';
$string['clipstale_body_admin'] = 'Ein hochgeladener Videoclip "{$a->filename}" wurde nicht kodiert.

Moodle-Aktivität: {$a->link}
Moodle-Nutzer: {$a->userfullname} {$a->userlink}
';
$string['view_useruploads'] = 'Zeige Nutzer-Uploads an';
$string['uploaded_clips'] = 'Von Nutzern hochgeladene Videoclips';
$string['nouploadedclips'] = 'Bisher wurden keine Videoclips hochgeladen.';
$string['feature_forbidden'] = 'Sie können diese Funktion nicht verwenden';
$string['video_file'] = 'Fügen Sie hier Ihren Videoclip hinzu';
$string['video_title'] = 'Titel des Videclips';
$string['video_subtitle'] = 'Untertitel des Videoclips';
$string['video_presenter'] = 'Referent des Videoclips';
$string['video_location'] = 'Aufnahmeort des Videoclips';
$string['scast_upload_form_hdr'] = 'Laden Sie Ihren Videoclip hier hoch';
$string['uploader'] = 'Hochgeladen von';
$string['license_EVENTS.LICENSE.ALLRIGHTS'] = 'All rights reserved'; // TODO translate
$string['license_EVENTS.LICENSE.CCBY'] = 'Creative Commons Attribution';
$string['license_EVENTS.LICENSE.CCBYNC'] = 'Creative Commons Attribution-NonCommercial';
$string['license_EVENTS.LICENSE.CCBYNCND'] = 'Creative Commons Attribution-NonCommercial-NoDerivs';
$string['license_EVENTS.LICENSE.CCBYNCSA'] = 'Creative Commons Attribution-NonCommercial-ShareAlike';
$string['license_EVENTS.LICENSE.CCBYND'] = 'Creative Commons Attribution-NoDerivs';
$string['license_EVENTS.LICENSE.CCBYSA'] = 'Creative Commons Attribution-ShareAlike';
$string['license_EVENTS.LICENSE.CC0'] = 'Creative Commons';
$string['moreinfo_url'] = 'Link für Zusatzinformationen';
$string['moreinfo_url_desc'] =
        'Beim Hinzufügen/Modifizifieren einer SWITCHcast-Aktivität wird dieser Link angezeigt, sofern dieses Feld ausgefüllt ist.';
$string['miscellaneoussettings_help'] = 'Zusätzliche Informationen über diese Einstellungen finden Sie unter';
$string['operationsettings'] = 'Plugin-Einstellungen';
$string['adminsettings'] = 'Administrative Einstellungen';
$string['uploadfile_extensions'] = 'Akzeptierte Datei-Typen';
$string['uploadfile_extensions_desc'] =
        'Von SWITCHcast akzeptierte Dateitypen zum Hochladen. Ändern Sie diese Einstellungen, wenn sich die von SWITCHcast akzeptierten Dateitypen ändern.';
$string['fileis_notextensionallowed'] =
        'Dateityp nicht erlaubt: {$a->yours}. Akzeptierte Dateitypen sind: {$a->allowed}';
$string['event:clip_viewed'] = 'Videoclip angeschaut';
$string['event:clip_viewed_desc'] =
        'NutzerIn mit der ID \'{$a->userid}\' hat einen Videclip in der SWITCHcast-Aktivität (Modul-ID) \'{$a->contextinstanceid}\' angeschaut.';
$string['event:member_invited'] = 'Mitglied eingeladen';
$string['event:member_invited_desc'] =
        'NutzerIn mit der ID \'{$a->userid}\' hat NutzerIn mit der ID \'{$a->relateduserid}\' die Erlaubnis zum Anschauen eines Videoclips in der SWITCHcast-Aktivität mit der Modul-ID \'{$a->contextinstanceid}\' gegeben.';
$string['event:member_revoked'] = 'Mitglied entfernt';
$string['event:member_revoked_desc'] =
        'NutzerIn mit der ID \'{$a->userid}\' hat NutzerIn mit der ID \'{$a->relateduserid}\' die Erlaubnis zum Anschauen eines Videoclips in der SWITCHcast-Aktivität mit der Modul-ID \'{$a->contextinstanceid}\' entzogen.';
$string['event:clip_uploaded'] = 'Videoclip hochgeladen';
$string['event:clip_uploaded_desc'] =
        'Der Nutzer mit der ID \'{$a->userid}\' hat einen Videoclip in der SWITCHcast-Aktivität mit der Modul-ID \'{$a->contextinstanceid}\' hochgeladen.';
$string['event:clip_deleted'] = 'Videoclip gelöscht';
$string['event:clip_deleted_desc'] =
        'Der Nutzer mit der ID \'{$a->userid}\' hat einen Videoclip in der SWITCHcast-Aktivität mit der Modul-ID \'{$a->contextinstanceid}\' gelöscht.';
$string['event:clip_editdetails'] = 'Metadaten des Videoclips wurden aktualisiert';
$string['event:clip_editdetails_desc'] =
        'Der Nutzer mit der ID \'{$a->userid}\' hat die Metadaten eines Videoclips in der SWITCHcast-Aktivität mit der Modul-ID \'{$a->contextinstanceid}\' aktualisiert.';
$string['upload_clip_info'] =
        'Bitte stellen Sie sicher, dass die Videodatei eines der folgenden Formate aufweist: <strong>{$a}</strong>.<br />Wenn dies nicht der Fall ist, folgen Sie bitte den Instruktionen des folgenden Hilfe-Buttons, bevor Sie den Videoclip hochladen.';
$string['upload_clip_misc'] = 'Eine Video-Datei wird konvertiert';
$string['upload_clip_misc_help'] = 'Bitte verwenden Sie die empfohlenen Videoformate. Falls Ihr Videoformat nicht unterstützt wird, benutzen Sie bitte die Software "Handbrake", um Ihren Videoclip zu konvertieren:<br />
<ul>
<li>Laden Sie die Software "Handbrake" von http://handbrake.fr herunter.</li>
<li>Installieren und starten Sie die Software</li>
<li>Wählen Sie die Video-Datei aus</li>
<li>Wählen Sie die "Universal"-Einstellung</li>
<li>Wählen Sie den grünen Startknopf.</li>
</ul>';
$string['set_clip_details_warning'] =
        'Warnung: Wenn Sie hier die Details des Clips ändern, werden  Titel und andere Metadaten, welche direkt innerhalb des Clips erscheinen, nicht geändert, da sie beim erstmaligen Upload direkt in die Clips hart-codiert werden.';
$string['curl_timeout'] = 'cURL Timeout';
$string['curl_timeout_desc'] =
        'Zeit in Sekunden, um auf eine Antwort des SWITCHcast API-Servers zu warten. Vergrössern Sie diesen Wert, wenn Sie Kanäle mit einer grossen Anzahl von Videos haben und der SWITCHcast-Server nicht rechtzeitig antwortet.';
$string['import_workflow'] = 'Workflow'; // TODO translate
$string['import_workflow_desc'] = 'ask your back-end administrator about this parameter'; // TODO translate

