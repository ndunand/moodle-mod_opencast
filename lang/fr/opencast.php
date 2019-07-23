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

$string['answered'] = 'Répondu';
$string['channel'] = 'Canal';
$string['channelnew'] = 'Nouveau canal';
$string['channelchoose'] = 'Canal sélectionné';
$string['channelexisting'] = 'Canal existant';
$string['completionsubmit'] = 'Show as complete when user makes a choice';
$string['displayhorizontal'] = 'Affichage horizontal';
$string['displaymode'] = 'Mode d\'affichage';
$string['displayvertical'] = 'Affichage vertical';
$string['opencast:downloadclip'] = 'Pouvoir télécharger des clips';
$string['expired'] = 'Désolé, cette activité est fermée depuis {$a}; elle n\'est plus disponible.';
$string['fillinatleastoneoption'] = 'Vous devez fournir au moins deux réponses possibles.';
$string['full'] = 'Complet';
$string['opencastclose'] = 'Jusqu\'à';
$string['opencastname'] = 'Nom SWITCHcast';
$string['opencastopen'] = 'Ouvert depuis';
$string['opencastoptions'] = 'Options SWITCHcast';
$string['chooseaction'] = 'Choisissez une action ...';
$string['limit'] = 'Limite';
$string['limitanswers'] = 'Limiter le nombre de réponses permises';
$string['modulename'] = 'Canal SWITCHcast';
$string['modulename_help'] =
        'Le module SWITCHcast permet aux enseignants de gérer un canal de vidéos SWITCHchast directement à partir d\'un espace Moodle.';
$string['modulenameplural'] = 'Canaux SWITCHcast';
$string['mustchooseone'] = 'Vous devez choisir une réponse avant d\'enregistrer. Rien a été enregistré.';
$string['noresultsviewable'] = 'Les résultats ne sont pas disponibles actuellement.';
$string['notanswered'] = 'Pas encore répondu';
$string['notopenyet'] = 'Désolé, cette activité n\'est pas disponible avant {$a}.';
$string['pluginadministration'] = 'Administration SWITCHcast';
$string['pluginname'] = 'OpenCast';
$string['timerestrict'] = 'Limiter les réponses à cette période';
$string['viewallresponses'] = 'Visualiser {$a} réponses';
$string['withselected'] = 'Avec la sélection';
$string['yourselection'] = 'Votre sélection';
$string['skipresultgraph'] = 'Ne pas afficher le graphique des résultats';
$string['moveselectedusersto'] = 'Déplacer l\'utilisateur sélectionné vers...';
$string['numberofuser'] = 'Nombre d\'utilisateurs';
$string['uid_field'] = 'SWITCHaai unique ID';
$string['uid_field_desc'] =
        'Profil d\'utilisateur qui contient le SWITCHaai unique ID, sous la forme &lt;fieldname&gt; OR &lt;table::fieldid&gt;.';
$string['switch_api_host'] = 'SWITCHcast API URL';
$string['switch_api_host_desc'] = 'Adresse du service Web SWITCHcast';
$string['switch_admin_host'] = ''; // TODO missing
$string['switch_admin_host_desc'] = ''; // TODO missing
$string['default_sysaccount'] = 'Compte système par défault';
$string['default_sysaccount_desc'] = 'Compte système utilisé pour les appels API SWITCHcast';
$string['sysaccount'] = 'Compte système pour {$a}';
$string['sysaccount_desc'] = 'Compte à utiliser pour les appels API SWITCHcast API de {$a}.';
$string['cacrt_file'] = 'Fichier CA CRT';
$string['cacrt_file_desc'] = 'Certification authority Certificate file';
$string['crt_file'] = 'Certificate file';
$string['crt_file_desc'] = 'x509 Server Certificate file';
$string['castkey_file'] = 'Switchcast key file';
$string['castkey_file_desc'] = 'La clé fournie par SWITCHcast pour signer les appels API';
$string['castkey_password'] = 'Switchcast key file password';
$string['castkey_password_desc'] = 'Le mot de passe nécessaire pour déverrouiller la clé SWITCHCast';
$string['serverkey_file'] = 'Server key File';
$string['serverkey_file_desc'] = 'SSL key File de ce serveur';
$string['serverkey_password'] = 'Server key file password';
$string['serverkey_password_desc'] = 'Le mot de passe nécessaire pour déverrouiller la clé Serveur';
$string['enabled_institutions'] = 'Institutions activées';
$string['enabled_institutions_desc'] =
        'Une liste des institutions activées sur ce serveur Moodle (valeurs séparées par des virgules).';
$string['external_authority_host'] = 'External authority host';
$string['external_authority_host_desc'] = 'External authority host URL';
$string['external_authority_id'] = 'External authority ID';
$string['external_authority_id_desc'] = 'External authority ID chez SWITCHcast';
$string['metadata_export'] = 'Export des métadonnées';
$string['metadata_export_desc'] = '';
//$string['configuration_id'] = 'Configuration ID';
//$string['configuration_id_desc'] = '';
//$string['streaming_configuration_id'] = 'Streaming configuration ID';
//$string['streaming_configuration_id_desc'] = '';
$string['access'] = 'Accès';
$string['access_desc'] = 'Niveau d\'accès pour les canaux créés';
$string['misconfiguration'] = 'Le plugin n\'est pas configuré correctement, contactez l\'administrateur du site.';
$string['logging_enabled'] = 'Journal activé';
$string['logging_enabled_desc'] =
        'Enregistrer tous les appels et réponses XML à l\'API.<br />Le fichier journal se trouve à {$a}/mod/opencast/opencast_api.log';
$string['display_select_columns'] = 'Afficher seulement les colonnes effectivement utilisées';
$string['display_select_columns_desc'] =
        'Dans la liste des clips, afficher seulement les champs (colonnes) utilisés, comme par exemple Station d\'enregistrement, Propriétaire, Actions. Ce choix a un impact sur la performance, parce que la liste de tous les clips doit être téléchargée pour chaque affichage.';
$string['enabled_templates'] = 'Templates activés';
$string['enabled_templates_desc'] =
        'Listez ici tous les templates SWITCHcast que vous voulez activer pour votre institution; le choix du template est seulement possible au moment de la création d\'un nouveau canal. Une définition par ligne, avec le format suivant : <em>&lt;TEMPLATE_ID&gt;::&lt;TEMPLATE_NAME&gt;</em>.<br />Si vous voulez utiliser pour un template le nom officiel de SWITCH, vous pouvez omettre l\'élément TEMPLATE_NAME (mais pas les séparateurs).';
$string['newchannelname'] = 'Nom du nouveau canal';
$string['license'] = 'Licence';
$string['months'] = '{$a} mois';
$string['years'] = '{$a} années';
$string['department'] = 'Département';
$string['annotations'] = 'Annotations';
$string['annotationsyes'] = 'Avec annotations';
$string['annotationsno'] = 'Sans annotations';
$string['template_id'] = 'Template Switchcast';
$string['template_id_help'] =
        'Template SwitchCast à utiliser pour l\'encodage des vidéos. Ceci définit quels formats vidéo snt disponibles ainsi que d\'autres options éventuelles. Veuillez vous référer aux instructions fournies par votre institution. <strong>Ce réglage ne peut plus être modifié ensuite.</strong>';
$string['is_ivt'] = 'Accès individuel par clip';
$string['is_ivt_help'] = '<strong>Non : </strong> tous les participants peuvent voir tous les clips<br><strong>Oui : </strong>les particpants ne peuvent voir que leur propres clips, les clips qu\'ils ont déposé, les clips auxquels ils ont été invités, et les clips apparetnant aux membres de leur groupe si l\'activité est en mode "groupes séparés".'; // @see \mod_opencast_event::isAllowed()
$string['inviting'] = 'Propriétaire du clip peut inviter';
$string['clip_member'] = 'Participants invités au clip';
$string['channel_teacher'] = 'Enseignant';
$string['untitled_clip'] = '(clip sans titre)';
$string['no_owner'] = '(pas de propriétaire)';
$string['owner_not_in_moodle'] = '(Propriétaire du clip pas enregistré dans Moodle)';
$string['clip_no_access'] = 'Vous n\'avez pas accès au clip';
$string['upload_clip'] = 'Déposer un nouveau clip';
$string['edit_at_switch'] = 'Editer ce canal sur le serveur SWITCHcast';
$string['edit_at_switch_short'] = 'Editer sur SWITCHcast';
$string['opencast:use'] = 'Afficher le contenu du canal SWITCHcast';
$string['opencast:isproducer'] =
        'Enregistré comme producteur du canal SWITCHcast (et donc avec accès à tous les clips)';
$string['opencast:addinstance'] = 'Ajouter une nouvelle activité SWITCHcast';
$string['opencast:seeallclips'] = 'Peut voir tous les clips dans un cours';
$string['opencast:uploadclip'] = 'Peut ajouter un clip via Moodle';
$string['nologfilewrite'] =
        'Impossible d\'écrire le fichier journal : {$a}. Vérifiez les permissions du système de fichiers.';
$string['noclipsinchannel'] = 'Ce canal ne contient pas de clips.';
$string['novisibleclipsinchannel'] = 'Ce canal ne contient pas de clips auxquels vous ayez accès.';
$string['user_notaai'] = 'La création d\'un nouveau canal nécessite un compte SWITCHaai.';
$string['user_homeorgnotenabled'] =
        'La création d\'une activité SWITCHcast nécessite l\'activation de votre HomeOrganization ({$a}) au niveau du site ; veuillez contacter l\'administrateur.';
$string['clip'] = 'Clip';
$string['cliptitle'] = 'Clip – Titre';
$string['presenter'] = 'Présentateur-trice';
$string['location'] = 'Lieu';
$string['recording_station'] = 'Station d\'enregistrement';
$string['date'] = 'Date';
$string['owner'] = 'Propriétaire';
$string['actions'] = 'Actions';
$string['editmembers'] = 'Gérer les invitations au clip';
$string['addmember'] = 'Inviter un participant au clip';
$string['editmembers_long'] = 'Gérer les participants invités au clip';
$string['editdetails'] = 'Modifier les métadonnées';
$string['delete_clip'] = 'Supprimer le clip';
$string['flash'] = 'Streaming';
$string['mov'] = 'Desktop';
$string['m4v'] = 'Smartphone';
$string['context'] = 'Contexte';
$string['confirm_removeuser'] = 'Voulez-vous vraiment supprimer cet utilisateur ?';
$string['delete_clip_confirm'] = 'Voulez-vous vraiment supprimer ce clip ?';
$string['back_to_channel'] = 'Retourner à la vue d\'ensemble du canal';
$string['channel_several_refs'] = 'Ce canal est référencé dans d\'autres activités Moodle.';
$string['set_clip_details'] = 'Définir les métadonnées du clip';
$string['owner_no_switch_account'] =
        'Il est impossible de définir &laquo;{$a}&raquo; comme propiétaire de ce clip, parce que cet utilisateur-trice n\'a pas de compte SWITCHaai.';
$string['nomoreusers'] = 'Il n\'y a plus d\'utilisateurs disponibles à ajouter.';
$string['nodepartment'] = 'Vous devez compléter le champ Département.';
$string['setnewowner'] = 'Définir comme nouveau propriétaire';
$string['clip_owner'] = 'Propriétaire du clip';
$string['group_member'] = 'Membre du groupe';
$string['clip_uploader'] = 'A ajouté le clip';
$string['aaiid_vs_moodleid'] = 'Le SWITCHaai Unique Id ne correspond pas à l\'identifiant Moodle !';
$string['error_decoding_token'] = 'Erreur de décodage du jeton: {$a}';
$string['error_opening_privatekey'] = 'Erreur de lecture du fichier de clef privée : {$a}';
$string['error_decrypting_token'] = 'Erreur de déchiffrement du jeton : {$a}';
$string['channelhasotherextauth'] = 'Ce canal est déjà lié à une autre External Authority: <em>{$a}</em>.';
$string['novisiblegroups'] = 'Ce paramètre n\'est pas disponible pour cette activité.';
$string['nogroups_withoutivt'] =
        'L\'option groupes séparés est seulement activé si le paramètre &laquo;Activer l\'accès individuel par clip&raquo; ci-dessus est activé.';
$string['itemsperpage'] = 'Clips par page';
$string['pageno'] = 'Page n° ';
$string['pagination'] =
        'Affichage des clips <span class="opencast-cliprange-from"></span> à <span class="opencast-cliprange-to"></span> sur <span class="opencast-cliprange-of"></span>.';
$string['filters'] = 'Filtres';
$string['resetfilters'] = 'Remettre les filtres à zéro';
$string['title'] = 'Titre';
$string['subtitle'] = 'Sous-titre';
$string['showsubtitles'] = 'afficher les sous-titres';
$string['recordingstation'] = 'Station d\'enregistrement';
$string['withoutowner'] = 'Pas de propriétaire';
$string['notavailable'] = 'Désolé, cette activité est encore en phase de test et n\'est pas disponible pour le moment.';
$string['local_cache_time'] = 'Durée de vie du cache XML';
$string['local_cache_time_desc'] =
        'Pour combien de temps (en secondes) les réponses XML du serveur SWITCHCast doivent-elles être mises en cache ? Une valeur de 0 correspond à la désactivation du cache.';
$string['removeowner'] = 'Retirer le propriétaire';
$string['channel_not_found'] = 'Le canal lié n\'existe pas (plus ?)';
$string['channeldoesnotbelong'] =
        'Le canal lié appartient à une autre organisation ({$a}) ; vous ne pouvez donc pas le modifier. Seul un enseignant de {$a} peut le modifier.';
$string['switch_api_down'] = 'Le serveur SwitchCast ne répond pas.';
$string['api_fail'] = 'Erreur de communication avec le serveur SwitchCast.';
$string['api_404'] = 'La ressource demandée n\'a pas été trouvée.';
$string['badorganization'] = 'L\'organisation liée à ce canal n\'est pas configurée correctement.';
$string['curl_proxy'] = 'curl proxy';
$string['curl_proxy_desc'] =
        'Si curl doit passer par un proxy, le spécifier ici sous la forme <em>proxyhostname:port</em>';
$string['moodleaccessonly'] = 'Ce clip n\'est accessible que depuis une activité Moodle.';
$string['loggedout'] = 'Vous avez été déconnecté ; veuillez rafraîchir la page.';
$string['redirfailed'] = 'La redirection a échoué.';
$string['allow_userupload'] = 'Autoriser les utilisateurs à ajouter des vidéos';
$string['allow_userupload_desc'] =
        'Autoriser les utilisateurs à ajouter des vidéos via l\'activité Moodle. L\'option correspondante doit être activée dans les activités SWITCHcast en question.';
$string['userupload_maxfilesize'] = 'Taille maximale de chaque fichier utilisateur';
$string['userupload_maxfilesize_desc'] = 'Taille maximale de chaque fichier vidéo déposé.';
$string['userupload_error'] = 'Une erreur est survenue pendant le transfert du fichier ; veuillez essayer à nouveau.';
$string['fileis_notavideo'] = 'Ce fichier n\'est pas de type vidéo ! Le type MIME du fichier est : {$a}';
$string['pendingclips'] = 'Il y a {$a} clips en cours de traitement dans ce canal';
$string['mypendingclips'] = 'Vous avez {$a} clips en cours de traitement dans ce canal';
$string['uploadedclips'] = '{$a} clips ont été ajoutés à ce canal';
$string['myuploadedclips'] = 'Vous avez ajouté {$a} clips à ce canal';
$string['clipready_subject'] = 'Votre nouveau clip est prêt';
$string['clipready_body'] = 'Le clip "{$a->cliptitle}" ({$a->filename}) est prêt, vous le trouverez dans l\'activité Moodle suivante :

{$a->link}
';
$string['clipstale_subject'] = 'Problème avec votre nouveau clip';
$string['clipstale_subject_admin'] = 'Problème avec un clip déposé';
$string['clipstale_body'] = 'Le clip "{$a->filename}" n\'a pas pu être converti correctement. Nous vous suggérons de tenter à nouveau le transfert dans l\'activité Moodle suivante :

{$a->link}
';
$string['clipstale_body_admin'] = 'Le clip "{$a->filename}" n\'a pas pu être converti correctement.

    Activité: {$a->link}
    Utilisateur: {$a->userfullname} {$a->userlink}
';
$string['view_useruploads'] = 'Afficher les clips déposés';
$string['uploaded_clips'] = 'Clips déposés par les utilisateurs';
$string['nouploadedclips'] = 'Aucun clip ajouté pour l\'instant.';
$string['feature_forbidden'] = 'Vous ne pouvez pas utiliser cette fonctionnalité';
$string['video_file'] = 'Ajoutez ici votre fichier vidéo';
$string['video_title'] = 'Titre du clip vidéo';
$string['video_subtitle'] = 'Sous titre du clip vidéo';
$string['video_presenter'] = 'Présentateur-trice du clip vidéo';
$string['video_location'] = 'Lieu de captation du clip vidéo';
$string['scast_upload_form_hdr'] = 'Déposez ici votre clip vidéo';
$string['uploader'] = 'Déposé par';
$string['license_EVENTS.LICENSE.ALLRIGHTS'] = 'All rights reserved'; // TODO translate
$string['license_EVENTS.LICENSE.CCBY'] = 'Creative Commons Attribution';
$string['license_EVENTS.LICENSE.CCBYNC'] = 'Creative Commons Attribution-NonCommercial';
$string['license_EVENTS.LICENSE.CCBYNCND'] = 'Creative Commons Attribution-NonCommercial-NoDerivs';
$string['license_EVENTS.LICENSE.CCBYNCSA'] = 'Creative Commons Attribution-NonCommercial-ShareAlike';
$string['license_EVENTS.LICENSE.CCBYND'] = 'Creative Commons Attribution-NoDerivs';
$string['license_EVENTS.LICENSE.CCBYSA'] = 'Creative Commons Attribution-ShareAlike';
$string['license_EVENTS.LICENSE.CC0'] = 'Creative Commons';
$string['moreinfo_url'] = 'Lien vers plus d\'informations';
$string['moreinfo_url_desc'] =
        'Si renseigné, ce lien sera affiché lors de la création/modification d\'une activité SWITCHcast.';
$string['miscellaneoussettings_help'] = 'Pour plus d\'informations sur ces paramètres, voir';
$string['operationsettings'] = 'Réglages du plugin';
$string['adminsettings'] = 'Réglages techniques';
$string['uploadfile_extensions'] = 'extensions de fichier acceptées';
$string['uploadfile_extensions_desc'] =
        'Les extensions de fichier suivantes sont acceptées par SWITCHcast. A modifier si le service SWITCHcast change.';
$string['fileis_notextensionallowed'] =
        'Type de fichier non supporté : {$a->yours}. Les types de fichier supportés sont : {$a->allowed}';
$string['upload_clip_info'] =
        'Vérifiez que le format de votre fichier vidéo est l\'un des suivants : <strong>{$a}</strong>.<br />Si ce n\'est pas le cas, veuillez suivre les instructions fournies par le bouton d\'aide suivant avant de déposer votre fichier vidéo.';
$string['upload_clip_misc'] = 'Convertir un fichier vidéo';
$string['upload_clip_misc_help'] = 'Afin de s\'assurer que votre fichier vidéo soit accepté par le système, veuillez utiliser un des formats recommandés. Si votre format de fichier n\'est pas supporté ou dans le doute, utilisez le logiciel Handbrake pour convertir votre fichier vidéo :<br />
<ul>
    <li>Télécharger le logiciel Handbrake sur http://handbrake.fr</li>
    <li>Installer et ouvrir le logiciel</li>
    <li>Sélectionner le fichier vidéo</li>
    <li>Choisir le réglage "Universal"</li>
    <li>Cliquer sur le bouton vert "Start".</li>
</ul>';
$string['event:clip_viewed'] = 'Clip affiché';
$string['event:clip_viewed_desc'] =
        'L\'utilisateur id \'{$a->userid}\' a affiché un clip dans l\'activité opencast module id \'{$a->contextinstanceid}\'.';
$string['event:member_invited'] = 'Membre invité';
$string['event:member_invited_desc'] =
        'L\'utilisateur id \'{$a->userid}\' a invité l\'utilisateur id \'{$a->relateduserid}\' a voir un clip dans l\'activité opencast module id \'{$a->contextinstanceid}\'.';
$string['event:member_revoked'] = 'Membre supprimé';
$string['event:member_revoked_desc'] =
        'L\'utilisateur id \'{$a->userid}\' a annulé l\'invitation à l\'utilisateur id \'{$a->relateduserid}\' a voir un clip dand l\'activité opencast module id \'{$a->contextinstanceid}\'.';
$string['event:clip_uploaded'] = 'Clip déposé';
$string['event:clip_uploaded_desc'] =
        'L\'utilisateur id \'{$a->userid}\' a déposé un clip dans l\'activité opencast module id \'{$a->contextinstanceid}\'.';
$string['event:clip_deleted'] = 'Clip supprimé';
$string['event:clip_deleted_desc'] =
        'L\'utilisateur id \'{$a->userid}\' a supprimé un clip dans l\'activité opencast module id \'{$a->contextinstanceid}\'.';
$string['event:clip_editdetails'] = 'Clip métadonnées modifiées';
$string['event:clip_editdetails_desc'] =
        'L\'utilisateur id \'{$a->userid}\' a modifié les métadonnées d\'un clip dans l\'activité opencast module id \'{$a->contextinstanceid}\'.';
$string['set_clip_details_warning'] =
        'Attention : la modification de ces détails n\'affecte pas les éventuels titres et autres métadonnées présents dans la vidéo elle-même, car ces éléments sont encodés dans la vidéo lors de son dépôt initial.';
$string['curl_timeout'] = 'Timeout cURL';
$string['curl_timeout_desc'] =
        'Combien de temps attendre une réponse du serveur API SWITCHcast, en secondes. Augmentez cette value si vous avez des activités contenant beaucoup de clips et que le serveur SWITCHcast peine à répondre dans les temps.';
$string['import_workflow'] = 'Workflow';
$string['import_workflow_desc'] = 'ask your back-end administrator about this parameter'; // TODO translate
$string['use_ipaddr_restriction'] = 'Use IP address restriction'; // TODO translate
$string['use_ipaddr_restriction_desc'] = 'Use IP address restriction to further protect videos links. Test thoroughly if users access Moodle via a reverse proxy. Disable this if you encounter issues.'; // TODO translate
