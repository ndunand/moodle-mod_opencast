
# Licensing information:

http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later


# Prerequisites:

 - Moodle version 2.7+
 - for the full feature set, the SWITCHaai UniqueID of your users must be available as a Moodle user profile field (regular or extended, see below)
 - PHP with CURL module supporting HTTPS protocol


# Preliminary step:

Configure an OpenCast tenant. This will allow the OpenCast system to delegate authority to your Moodle regarding access rights to events belonging to certain series.


# Module installation:

The latest version of the mod_opencast Moodle activity plugin is available on GitHub : https://github.com/ndunand/moodle-mod_opencast .

Install as any other activity module into your Moodle's /mod directory, then visit http://your.moodle/admin/ to proceed with module installation.


# Module setup:

Last, proceed to the module settings (via Site administration -> Plugins -> Activity modules -> OpenCast, or directly at http://your.moodle/admin/settings.php?section=modsettingopencast ). You may leave all defaults settings, but you must fill in the following parameters:

 - switch_api_host : the URL of your tenant's API service location, as provided by SWITCH
 - switch_api_username : the username of your tenant, as provided by SWITCH
 - switch_api_password : the password of your tenant, as provided by SWITCH
 - switch_admin_host : the URL of your tenant, as provided by SWITCH
 - uid_field : the user profile field containing the AAI unique ID, of the form <fieldname> OR <table::fieldid>.

