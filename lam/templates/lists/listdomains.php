<?php
/*
$Id$

  This code is part of LDAP Account Manager (http://www.sourceforge.net/projects/lam)
  Copyright (C) 2003 - 2004  Roland Gruber

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more detaexils.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/


/**
* This page displays a list of all Samba domains.
*
* @package tools
* @author Roland Gruber
*/


/** Access to configuration options */
include_once("../../lib/config.inc");
/** Access to LDAP connection */
include_once("../../lib/ldap.inc");
/** Used to print status messages */
include_once("../../lib/status.inc");
/** Basic list functions */
include_once("../../lib/lists.inc");

// start session
session_save_path("../../sess");
@session_start();

setlanguage();

// get sorting column when register_globals is off
$sort = $_GET['sort'];

// copy HTTP-GET variables to HTTP-POST
$_POST = $_POST + $_GET;

$dom_info = $_SESSION['dom_info'];
$dom_units = $_SESSION['dom_units'];

// check if button was pressed and if we have to add/delete a domain
if ($_POST['new_domain'] || $_POST['del_domain']){
	// add new domain
	if ($_POST['new_domain']){
		metaRefresh("../domain.php?action=new");
		exit;
	}
	// delete domain(s)
	if ($_POST['del_domain']){
		// search for checkboxes
		$domains = array_keys($_POST, "on");
		$domainstr = implode(";", $domains);
		if ($domainstr) {
			metaRefresh("../domain.php?action=delete&amp;DN='$domainstr'");
			exit;
		}
	}
}

echo $_SESSION['header'];
echo "<title>listdomains</title>\n";
echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"../../style/layout.css\">\n";
echo "</head><body>\n";
echo "<script src=\"../../lib/functions.js\" type=\"text/javascript\" language=\"javascript\"></script>\n";

// get current page
$page = $_GET["page"];
if (!$page) $page = 1;
// take maximum count of domain entries shown on one page out of session
if ($_SESSION["config"]->get_MaxListEntries() <= 0)
	$max_page_entries = 10;	// default setting, if not yet set
else
	$max_page_entries = $_SESSION["config"]->get_MaxListEntries();


// generate attribute and description tables
$attr_array = array();	// list of LDAP attributes to show
$desc_array = array();	// list of descriptions for the attributes
$attr_array[] = "sambaDomainName";
$attr_array[] = "sambaSID";
$attr_array[] = "dn";
$desc_array[] = strtoupper(_("Domain name"));
$desc_array[] = strtoupper(_("Domain SID"));
$desc_array[] = "DN";

// check search suffix
if ($_POST['dom_suffix']) $dom_suffix = $_POST['dom_suffix'];  // new suffix selected via combobox
elseif ($_SESSION['dom_suffix']) $dom_suffix = $_SESSION['dom_suffix'];  // old suffix from session
else $dom_suffix = $_SESSION["config"]->get_DomainSuffix();  // default suffix

// first time page is shown
if (! $_GET['norefresh']) {
	// configure search filter
	$filter = "(objectClass=sambaDomain)";
	$attrs = $attr_array;
	$sr = @ldap_search($_SESSION["ldap"]->server(), $dom_suffix, $filter, $attrs);
	if (ldap_errno($_SESSION["ldap"]->server()) == 4) {
		StatusMessage("WARN", _("LDAP sizelimit exceeded, not all entries are shown."), _("See README.openldap.txt to solve this problem."));
	}
	if ($sr) {
		$dom_info = ldap_get_entries($_SESSION["ldap"]->server, $sr);
		ldap_free_result($sr);
		if ($dom_info["count"] == 0) StatusMessage("WARN", "", _("No Samba Domains found!"));
		// delete first array entry which is "count"
		unset($dom_info['count']);
		// sort rows by sort column ($sort)
		$dom_info = listSort($sort, $attr_array, $dom_info);
	}
	else StatusMessage("ERROR", _("LDAP Search failed! Please check your preferences."), _("No Samba Domains found!"));
}
// use search result from session
else {
	if (sizeof($dom_info) == 0) StatusMessage("WARN", "", _("No Samba Domains found!"));
	// sort rows by sort column ($sort)
	if ($dom_info) $dom_info = listSort($sort, $attr_array, $dom_info);
}

echo ("<form action=\"listdomains.php\" method=\"post\">\n");

// draw navigation bar if domain accounts were found
if (sizeof($dom_info) > 0) {
listDrawNavigationBar(sizeof($dom_info), $max_page_entries, $page, $sort, $searchFilter, "domain", _("%s Samba domain(s) found"));
echo ("<br>\n");
}

// print domain table header
echo "<table rules=\"all\" class=\"domainlist\" width=\"100%\">\n";
echo "<tr class=\"domainlist-head\"><th width=22 height=34></th><th></th>";
// table header
for ($k = 0; $k < sizeof($desc_array); $k++) {
	if (strtolower($attr_array[$k]) == $sort) {
		echo "<th class=\"domainlist-sort\"><a href=\"listdomains.php?".
			"sort=" . strtolower($attr_array[$k]) . "&amp;norefresh=y" . "\">" . $desc_array[$k] . "</a></th>";
	}
	else echo "<th><a href=\"listdomains.php?".
		"sort=" . strtolower($attr_array[$k]) . "&amp;norefresh=y" . "\">" . $desc_array[$k] . "</a></th>";
}
echo "</tr>\n";

// calculate which rows to show
$table_begin = ($page - 1) * $max_page_entries;
if (($page * $max_page_entries) > sizeof($dom_info)) $table_end = sizeof($dom_info);
else $table_end = ($page * $max_page_entries);

// print domain list
for ($i = $table_begin; $i < $table_end; $i++) {
	echo("<tr class=\"domainlist\" onMouseOver=\"domain_over(this, '" . $dom_info[$i]["dn"] . "')\"" .
								" onMouseOut=\"domain_out(this, '" . $dom_info[$i]["dn"] . "')\"" .
								" onClick=\"domain_click(this, '" . $dom_info[$i]["dn"] . "')\"" .
								" onDblClick=\"parent.frames[1].location.href='../domain.php?action=edit&amp;DN=" . $dom_info[$i]["dn"] . "'\">" .
								" <td height=22 align=\"center\"><input onClick=\"domain_click(this, '" . $dom_info[$i]["dn"] . "')\" type=\"checkbox\" name=\"" . $dom_info[$i]["dn"] . "\"></td>" .
								" <td align='center'><a href=\"../domain.php?action=edit&amp;DN='" . $dom_info[$i]["dn"] . "'\">" . _("Edit") . "</a></td>");
	for ($k = 0; $k < sizeof($attr_array); $k++) {
		echo ("<td>");
		// print all attribute entries seperated by "; "
		if (sizeof($dom_info[$i][strtolower($attr_array[$k])]) > 0) {
			// delete first array entry which is "count"
			if (is_array($dom_info[$i][strtolower($attr_array[$k])])) unset($dom_info[$i][strtolower($attr_array[$k])]['count']);
			if (is_array($dom_info[$i][strtolower($attr_array[$k])])) echo implode("; ", $dom_info[$i][strtolower($attr_array[$k])]);
			else echo $dom_info[$i][strtolower($attr_array[$k])];
		}
		echo ("</td>");
	}
	echo("</tr>\n");
}
echo ("</table>");

echo ("<br>");

// draw navigation bar if domain accounts were found
if (sizeof($dom_info) > 0) {
listDrawNavigationBar(sizeof($dom_info), $max_page_entries, $page, $sort, $searchFilter, "domain", _("%s Samba domain(s) found"));
echo ("<br>\n");
}

if (! $_GET['norefresh']) {
	// generate list of possible suffixes
$dom_units = $_SESSION['ldap']->search_units($_SESSION["config"]->get_DomainSuffix());
}

// print combobox with possible sub-DNs
if (sizeof($dom_units) > 1) {
	echo ("<p align=\"left\">\n");
	echo ("<b>" . _("Suffix") . ": </b>");
	echo ("<select size=1 name=\"dom_suffix\">\n");
	for ($i = 0; $i < sizeof($dom_units); $i++) {
		if ($dom_suffix == $dom_units[$i]) echo ("<option selected>" . $dom_units[$i] . "</option>\n");
		else echo("<option>" . $dom_units[$i] . "</option>\n");
	}
	echo ("</select>\n");
	echo ("<input type=\"submit\" name=\"refresh\" value=\"" . _("Change Suffix") . "\">");
	echo ("</p>\n");
	echo ("<p>&nbsp;</p>\n");
}

echo ("<p align=\"left\">\n");
echo ("<input type=\"submit\" name=\"new_domain\" value=\"" . _("New Domain") . "\">\n");
if (sizeof($dom_info) > 0) echo ("<input type=\"submit\" name=\"del_domain\" value=\"" . _("Delete Domain(s)") . "\">\n");
echo ("</p>\n");

echo ("</form>\n");
echo "</body></html>\n";



// save variables to session
$_SESSION['dom_info'] = $dom_info;
$_SESSION['dom_units'] = $dom_units;
$_SESSION['dom_suffix'] = $dom_suffix;

?>
