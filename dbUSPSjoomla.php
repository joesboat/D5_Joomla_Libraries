<?php
/*
Copyright (C) March 2013, Joseph P. Gibson.

This program is free software to USPS Members developing software for USPS Squadrons or Districts: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program.  If not, see <http://www.gnu.org/licenses/>.

Owner: 	Joseph P. Gibson
		USPS District 5 Webmaster 
*/
jimport('usps.includes.routines');
//include(JPATH_LIBRARIES."/usps/includes/routines.php");
//require_once("includes/tableEvents.php");
//require_once("includes/tableLocations.php");
//require_once("c:/users/joe/websites/uspsd5/libraries/usps/tableAccess.php");
//*************************************************************************
class USPSdbJoomla {
// Routines that utilize data from the events table to display information.  
// Only generated and used on pages that display a list of events
// Called from 'calendar.php' to list district or national events
// Called from 'squad_events' to list all squadron events
// Called from 'booklet routines' to list a variety of events 
//********************************* Public Variables **********************
//********************************* Private Variables *********************
private $blank_event;
private $db;
public $display_by;
private $squad_no;
private $content;
private $menu;
private $session;
private $users;
var $loging = false;
//*************************************************************************
function __construct($caller=''){
//include ( "/web/joomla/libraries/USPSaccess/dbUSPS.php");
include (JPATH_LIBRARIES . "/USPSaccess/dbUSPS.php");
	$db_joomla = getDestFromConfiguration();
	$this->db = $db_joomla;
	$dbprefix = $db_joomla['dbprefix'];
	if (! $this->content = new USPStableAccess($dbprefix.'content',$db_joomla)) log_it("Did not open courses table",__LINE__);
	if (! $this->session = new USPStableAccess($dbprefix.'session',$db_joomla)) log_it("Did not open session table",__LINE__);
	if (! $this->menu = new USPStableAccess($dbprefix.'menu',$db_joomla)) log_it("Did not open usps_menu table",__LINE__);
	if (! $this->users = new USPStableAccess($dbprefix.'users',$db_joomla)) log_it("Did not open usps_users table",__LINE__);
}// constructer
//******************************************************************************
function close(){
	//parent::close($this->thisdb);
	//mysqli_close($this->content);
	//mysqli_close($this->session);
	//mysqli_close($this->menu);
	//mysqli_close($this->users);
	return;
}
//*********************************************************
function getContent($id){
	$row = $this->content->get_record('alias',$id);
	return $row;
}
function getMenuObject(){
	return $this->menu;
}
//*********************************************************
function getSessionIdFromUserId($user_id){
	$rows = $this->session->get_records('username',$user_id);
	foreach($rows as $row){
		if ($row['guest'] == 0){
			$id = $row['session_id'];
			return $id;
		}
	}
}
//*********************************************************
function getSession($session_id){
	$row = $this->session->get_record('session_id',$session_id);
	return $row;
}
//*********************************************************
function getUsersObject(){
	return $this->users;
}
//*********************************************************
//*********************************************************
//*********************************************************
}// class
?>