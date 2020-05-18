<?php
/*
Copyright (C) March 2013, Joseph P. Gibson.

This program is free software to USPS Members developing software for USPS Squadrons or Districts: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program.  If not, see <http://www.gnu.org/licenses/>.

Owner: 	Joseph P. Gibson
		USPS District 5 Webmaster 
*/
//require_once("/home/content/82/7781582/html/libraries/usps/tableAccess.php");
//require_once("c:/users/joe/websites/uspsd5/libraries/usps/tableAccess.php");
//require_once("/web/joomla/libraries/usps/tableAccess.php");
require_once(JPATH_LIBRARIES ."/usps/tableAccess.php");
//*************************************************************************
class tableD5Attendees extends USPStableAccess{
// Routines that utilize data from the events table to display information.  
// Only generated and used on pages that display a list of events
// Called from 'calendar.php' to list district or national events
// Called from 'squad_events' to list all squadron events
// Called from 'booklet routines' to list a variety of events 
//********************************* Public Variables **********************
//********************************* Private Variables *********************
private $squad_no;
public $display_by;
public $mtg_types;
//*************************************************************************
function __construct($db, $caller=''){
global $exc, $jobs, $blob,  $mbr, $sqd;
	// Creates the variables to contain identity of data and tables 
	parent::__construct('d5_attendees', $db, $caller);
	//$this->squad_no=$sqd;
}// constructer
//****************************************************************************
//****************************************************************************
//****************************************************************************
function addGuests($event_id,$reg_id,$guests){
	$select = "event_id='$event_id' and reg_id='$reg_id' and guest = 1";
	$ok = $this->delete_range($select);
	foreach($guests as $key=>$guest){
		$rec['event_id'] = $event_id;
		$rec['reg_id'] = $reg_id;
		$rec['certificate'] = $event_id."_".$reg_id."_".$key;
		$rec['guest'] = 1;
		if ($guest['guestFirsttimer'] == 'Y')
			$rec['attFirstTimer'] = 1;
		foreach($guest as $nm=>$val){
			$rec[$nm] = $val ;
		}
		$this->add_record($rec);
	}	
}
//****************************************************************************
function addMembers($event_id,$reg_id,$members){
	$select = "event_id='$event_id' and reg_id='$reg_id' and guest=0";
	$ok = $this->delete_range($select);
	foreach($members as $mbr){
		$rec['event_id'] = $event_id;
		$rec['reg_id'] = $reg_id;
		$rec['certificate'] = $mbr['certificate'] ;
		$rec['guest'] =0;
		$rec['attFirstTimer'] = $mbr['firstTimer']; 
		$this->add_record($rec);
	}
}
//****************************************************************************
function getMembers($event_id){
	$list = $this->get_records("event_id",$event_id);
	return $list;
} 
//****************************************************************************
function getMembersForReg($event_id, $reg_id){
	$select = "event_id='$event_id' and reg_id='$reg_id'";
	$list = $this->search_records($select);
	return $list;
}
//****************************************************************************
function getGuests($event_id){
	// A complex certificate number will be created for each guest record that 
	// will consist of event_id + '_' + id if registration + '_' #  
	// 		
}
//****************************************************************************
//****************************************************************************
//****************************************************************************
//****************************************************************************



}// class
?>