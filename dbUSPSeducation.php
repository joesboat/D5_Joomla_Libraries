<?php
/*
Copyright (C) March 2013, Joseph P. Gibson.

This program is free software to USPS Members developing software for USPS Squadrons or Districts: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program.  If not, see <http://www.gnu.org/licenses/>.

Owner: 	Joseph P. Gibson
		USPS District 5 Webmaster 
*/
require_once("includes/tableBlobs.php");
require_once("includes/tableEvents.php");
require_once("includes/tableLocations.php");
//require_once("c:/users/joe/websites/uspsd5/libraries/usps/tableAccess.php");
//*************************************************************************
class USPSdbEducation {
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
private $course;
//*************************************************************************
function __construct($caller=''){
//include ( "/web/joomla/libraries/USPSaccess/dbUSPS.php");
include (JPATH_LIBRARIES . "/USPSaccess/dbUSPS.php");
	$this->db = $db_joomla;
	if (! $this->course = new USPStableAccess('course',$db_joomla)) 
		log_it("Did not open courses table");
}// constructer
//*********************************************************
function getCourse($id){
	$row = $this->course->get_record('coursecode',$id);
	return $row;
}
//*********************************************************
//*********************************************************
//*********************************************************
//*********************************************************
//*********************************************************
}// class
?>