<?php
/*
$Id$

  This code is part of LDAP Account Manager (http://www.sourceforge.net/projects/lam)
  Copyright (C) 2003  Roland Gruber

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA


  confsave saves the new preferences to lam.conf

*/
include_once ('../../lib/config.inc');

// start session
session_save_path("../../sess");
@session_start();

setlanguage();

$conf = new Config($_SESSION['filename']);

// get data from session
if ($_SESSION['passwd']) $passwd = $_SESSION['passwd'];
if ($_SESSION['passwd1']) $passwd1 = $_SESSION['passwd1'];
if ($_SESSION['passwd2']) $passwd2 = $_SESSION['passwd2'];
if ($_SESSION['serverurl']) $serverurl = $_SESSION['serverurl'];
if (isset($_SESSION['serverurl'])) $serverurl = $_SESSION['serverurl'];
if ($_SESSION['admins']) $admins = $_SESSION['admins'];
if ($_SESSION['suffusers']) $suffusers = $_SESSION['suffusers'];
if ($_SESSION['suffgroups']) $suffgroups = $_SESSION['suffgroups'];
if ($_SESSION['suffhosts']) $suffhosts = $_SESSION['suffhosts'];
if ($_SESSION['suffdomains']) $suffdomains = $_SESSION['suffdomains'];
//if ($_SESSION['suffmap']) $suffmap = $_SESSION['suffmap'];
if (isset($_SESSION['minUID'])) $minUID = $_SESSION['minUID'];
if ($_SESSION['maxUID']) $maxUID = $_SESSION['maxUID'];
if (isset($_SESSION['minGID'])) $minGID = $_SESSION['minGID'];
if ($_SESSION['maxGID']) $maxGID = $_SESSION['maxGID'];
if (isset($_SESSION['minMach'])) $minMach = $_SESSION['minMach'];
if ($_SESSION['maxMach']) $maxMach = $_SESSION['maxMach'];
if ($_SESSION['usrlstattr']) $usrlstattr = $_SESSION['usrlstattr'];
if ($_SESSION['grplstattr']) $grplstattr = $_SESSION['grplstattr'];
if ($_SESSION['hstlstattr']) $hstlstattr = $_SESSION['hstlstattr'];
if ($_SESSION['maxlistentries']) $maxlistentries = $_SESSION['maxlistentries'];
if ($_SESSION['lang']) $lang = $_SESSION['lang'];
if ($_SESSION['scriptpath']) $scriptpath = $_SESSION['scriptpath'];
if ($_SESSION['scriptserver']) $scriptserver = $_SESSION['scriptserver'];
if ($_SESSION['samba3']) $samba3 = $_SESSION['samba3'];
if ($_SESSION['filename']) $filename = $_SESSION['filename'];

// check if password is correct
// if not: load login page
if ($passwd != $conf->get_Passwd()) {
	require('conflogin.php');
	exit;
}

echo $_SESSION['header'];

echo "<html><head><title>listusers</title>\n";
echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"../../style/layout.css\">\n";
echo "</head><body>\n";

echo ("<p align=\"center\"><a href=\"http://lam.sf.net\" target=\"new_window\">".
	"<img src=\"../../graphics/banner.jpg\" border=1 alt=\"LDAP Account Manager\"></a></p><hr><br><br>");

// check new preferences
if (!$serverurl) {
	echo ("<font color=\"red\"><b>" . _("Server Address is empty!") . "</b></font>");
	echo ("\n<br><br><br><a href=\"javascript:history.back()\">" . _("Back to preferences...") . "</a>");
	exit;
}
if (!isset($cache_timeout) || !(is_numeric($cache_timeout)) || !($cache_timeout > -1)) {
	echo ("<font color=\"red\"><b>" . _("Cache timeout is empty!") . "</b></font>");
	echo ("\n<br><br><br><a href=\"javascript:history.back()\">" . _("Back to preferences...") . "</a>");
	exit;
}
if (!$admins || !eregi("^([a-z0-9]|-)+=([a-z0-9]|-)+(,([a-z0-9]|-)+=([a-z0-9]|-)+)+(;([a-z0-9]|-)+=([a-z0-9]|-)+(,([a-z0-9]|-)+=([a-z0-9]|-)+)+)*$", $admins)) {
	echo ("<font color=\"red\"><b>" . _("List of admin users is empty or invalid!") . "</b></font>");
	echo ("\n<br><br><br><a href=\"javascript:history.back()\">" . _("Back to preferences...") . "</a>");
	exit;
}
if (!$suffusers || !eregi("^(([a-z]|-|[0-9])*=([a-z]|-|[0-9])*)(,([a-z]|-|[0-9])*=([a-z]|-|[0-9])*)*$", $suffusers)) {
	echo ("<font color=\"red\"><b>" . _("UserSuffix is invalid!") . "</b></font>");
	echo ("\n<br><br><br><a href=\"javascript:history.back()\">" . _("Back to preferences...") . "</a>");
	exit;
}
if (!$suffgroups || !eregi("^(([a-z]|-|[0-9])*=([a-z]|-|[0-9])*)(,([a-z]|-|[0-9])*=([a-z]|-|[0-9])*)*$", $suffgroups)) {
	echo ("<font color=\"red\"><b>" . _("UserSuffix is invalid!") . "</b></font>");
	echo ("\n<br><br><br><a href=\"javascript:history.back()\">" . _("Back to preferences...") . "</a>");
	exit;
}
if (!$suffhosts || !eregi("^(([a-z]|-|[0-9])*=([a-z]|-|[0-9])*)(,([a-z]|-|[0-9])*=([a-z]|-|[0-9])*)*$", $suffhosts)) {
	echo ("<font color=\"red\"><b>" . _("HostSuffix is invalid!") . "</b></font>");
	echo ("\n<br><br><br><a href=\"javascript:history.back()\">" . _("Back to preferences...") . "</a>");
	exit;
}
if (($samba3 == "yes") && !eregi("^(([a-z]|-|[0-9])*=([a-z]|-|[0-9])*)(,([a-z]|-|[0-9])*=([a-z]|-|[0-9])*)*$", $suffdomains)) {
	echo ("<font color=\"red\"><b>" . _("DomainSuffix is invalid!") . "</b></font>");
	echo ("\n<br><br><br><a href=\"javascript:history.back()\">" . _("Back to preferences...") . "</a>");
	exit;
}
if (!isset($minUID) || !is_numeric($minUID)) {
	echo ("<font color=\"red\"><b>" . _("MinUID is invalid!") . "</b></font>");
	echo ("\n<br><br><br><a href=\"javascript:history.back()\">" . _("Back to preferences...") . "</a>");
	exit;
}
if (!$maxUID || !is_numeric($maxUID)) {
	echo ("<font color=\"red\"><b>" . _("MaxUID is invalid!") . "</b></font>");
	echo ("\n<br><br><br><a href=\"javascript:history.back()\">" . _("Back to preferences...") . "</a>");
	exit;
}
if (!isset($minGID) || !is_numeric($minGID)) {
	echo ("<font color=\"red\"><b>" . _("MinGID is invalid!") . "</b></font>");
	echo ("\n<br><br><br><a href=\"javascript:history.back()\">" . _("Back to preferences...") . "</a>");
	exit;
}
if (!$maxGID || !is_numeric($maxGID)) {
	echo ("<font color=\"red\"><b>" . _("MaxGID is invalid!") . "</b></font>");
	echo ("\n<br><br><br><a href=\"javascript:history.back()\">" . _("Back to preferences...") . "</a>");
	exit;
}
if (!isset($minMach) || !is_numeric($minMach)) {
	echo ("<font color=\"red\"><b>" . _("MinMachine is invalid!") . "</b></font>");
	echo ("\n<br><br><br><a href=\"javascript:history.back()\">" . _("Back to preferences...") . "</a>");
	exit;
}
if (!$maxMach || !is_numeric($maxMach)) {
	echo ("<font color=\"red\"><b>" . _("MaxMachine is invalid!") . "</b></font>");
	echo ("\n<br><br><br><a href=\"javascript:history.back()\">" . _("Back to preferences...") . "</a>");
	exit;
}
if (!$usrlstattr || !eregi("^((#[a-z]*)|([a-z]*:[a-z*]))(;((#[a-z]*)|([a-z]*:[a-z]*)))*$", $usrlstattr)) {
	echo ("<font color=\"red\"><b>" . _("User list attributes are invalid!") . "</b></font>");
	echo ("\n<br><br><br><a href=\"javascript:history.back()\">" . _("Back to preferences...") . "</a>");
	exit;
}
if (!$grplstattr || !eregi("^((#[a-z]*)|([a-z]*:[a-z*]))(;((#[a-z]*)|([a-z]*:[a-z]*)))*$", $grplstattr)) {
	echo ("<font color=\"red\"><b>" . _("Group list attributes are invalid!") . "</b></font>");
	echo ("\n<br><br><br><a href=\"javascript:history.back()\">" . _("Back to preferences...") . "</a>");
	exit;
}
if (!$hstlstattr || !eregi("^((#[a-z]*)|([a-z]*:[a-z*]))(;((#[a-z]*)|([a-z]*:[a-z]*)))*$", $hstlstattr)) {
	echo ("<font color=\"red\"><b>" . _("Host list attributes are invalid!") . "</b></font>");
	echo ("\n<br><br><br><a href=\"javascript:history.back()\">" . _("Back to preferences...") . "</a>");
	exit;
}
if (!$maxlistentries || !is_numeric($maxlistentries)) {
	echo ("<font color=\"red\"><b>" . _("Max list entries is invalid!") . "</b></font>");
	echo ("\n<br><br><br><a href=\"javascript:history.back()\">" . _("Back to preferences...") . "</a>");
	exit;
}

if (!$lang) {
	echo ("<font color=\"red\"><b>" . _("Language is not defined!") . "</b></font>");
	echo ("\n<br><br><br><a href=\"javascript:history.back()\">" . _("Back to preferences...") . "</a>");
	exit;
}

if (!$samba3) {
	echo ("<font color=\"red\"><b>" . _("Samba version is not defined!") . "</b></font>");
	echo ("\n<br><br><br><a href=\"javascript:history.back()\">" . _("Back to preferences...") . "</a>");
	exit;
}

if ($scriptpath && !eregi("^/[a-z0-9_\\-]+(/[a-z0-9_\\.\\-]+)+$", $scriptpath)) {
	echo ("<font color=\"red\"><b>" . _("Script path is invalid!") . "</b></font>");
	echo ("\n<br><br><br><a href=\"javascript:history.back()\">" . _("Back to preferences...") . "</a>");
	exit;
}

if ($scriptserver && !is_string($scriptserver)) {
	echo ("<font color=\"red\"><b>" . _("Script server is invalid!") . "</b></font>");
	echo ("\n<br><br><br><a href=\"javascript:history.back()\">" . _("Back to preferences...") . "</a>");
	exit;
}

// set new preferences
$conf->set_ServerURL($serverurl);
$conf->set_cacheTimeout($cache_timeout);
$conf->set_Adminstring($admins);
$conf->set_UserSuffix($suffusers);
$conf->set_GroupSuffix($suffgroups);
$conf->set_HostSuffix($suffhosts);
$conf->set_DomainSuffix($suffdomains);
$conf->set_minUID($minUID);
$conf->set_maxUID($maxUID);
$conf->set_minGID($minGID);
$conf->set_maxGID($maxGID);
$conf->set_minMachine($minMach);
$conf->set_maxMachine($maxMach);
$conf->set_userlistAttributes($usrlstattr);
$conf->set_grouplistAttributes($grplstattr);
$conf->set_hostlistAttributes($hstlstattr);
$conf->set_MaxListEntries($maxlistentries);
$conf->set_defaultLanguage($lang);
$conf->set_samba3($samba3);
$conf->set_scriptpath($scriptpath);
$conf->set_scriptserver($scriptserver);



// check if password was changed
if ($pass1 != $pass2) {
	echo ("<b>" . _("Passwords are different!") . "</b>");
	exit;
}
// set new password
if ($pass1 != "") {
	$conf->set_Passwd($pass1);
	echo ("<b>" . _("Password changed!") . "</b><br><br>");
}
// save settings and display new settings
$conf->save();
echo ("<b>" . _("The following settings were saved to profile:") . " </b>" . $filename . "<br><br>");
$conf->printconf();
echo ("<br><br><br><br><br><a href=\"../login.php\" target=\"_top\">" . _("Back to Login") . "</a>");

echo("</body></html>");

// remove settings from session
unset($_SESSION['passwd']);
unset($_SESSION['passwd1']);
unset($_SESSION['passwd2']);
unset($_SESSION['serverurl']);
unset($_SESSION['cache_timeout']);
unset($_SESSION['admins']);
unset($_SESSION['suffusers']);
unset($_SESSION['suffgroups']);
unset($_SESSION['suffhosts']);
unset($_SESSION['suffdomains']);
unset($_SESSION['minUID']);
unset($_SESSION['maxUID']);
unset($_SESSION['minGID']);
unset($_SESSION['maxGID']);
unset($_SESSION['minMach']);
unset($_SESSION['maxMach']);
unset($_SESSION['usrlstattr']);
unset($_SESSION['grplstattr']);
unset($_SESSION['hstlstattr']);
unset($_SESSION['maxlistentries']);
unset($_SESSION['lang']);
unset($_SESSION['scriptpath']);
unset($_SESSION['scriptserver']);
unset($_SESSION['samba3']);
unset($_SESSION['filename']);

?>
