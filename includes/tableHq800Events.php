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
class table_USPSHq800events extends USPStableAccess{
// Routines will provide read access to one of the three HQ800 events tables.  
// Called from 'dbUSPSWebSites.php routines.
// Converts the HQ800 format into the standard event format
//********************************* Public Variables **********************
//********************************* Private Variables *********************
private $courses;  // variable to store object to csc codes table in events database 
//*************************************************************************
function __construct($table, $db, $db_ed, $caller=''){
global $exc, $jobs, $blob,  $mbr, $sqd;
	// Creates the variables to contain identity of data and tables 
	parent::__construct($table, $db, $caller);
	if (! $this->courses = new USPStableAccess('csccodes', $db_ed, $caller)) log_it("Did not open csccodes table");
}// constructer
//*************************************************************************
function convert_usps_hq800_to_event($array){
	$event['usps_id'] = $array['id'];
	$event['start_date'] = date("Y-m-d H:i:s", strtotime($array['date']." ".$array['time']));
	if ($array['edate'] == "0000-00-00") 
		$array['edate'] = $array['date'];
	$event['end_date'] =   date("Y-m-d H:i:s", strtotime($array['edate']." ".$array['time']));
	if ($event['start_date'] < date("Y-m-d H:i:s"))
		return false;
	$event['place'] = $array['place'];
	$event['city'] = $array['city'];
	$event['state'] = $array['state'];
	$event['zip'] = $array['zip'];
	$event['adr'] = $array['adr'];
	$event['c_date'] = date("Ymd", strtotime($array['date']));
	// Use the course data 
	$event['course_id'] = $array['type'];
	$event['event_type'] = strtolower($array['type']);
	$event['event_name'] = $this->getCourseName($array);
	$event['event_name_url'] = APPLICATION_HOST.'/'.$this->getClassLink($event['event_name'],$array);
	$event['event_name_w_link'] = $this->getClassNameWithLink($event['event_name'],$event['event_name_url']);
	// $event['event_name_url'] = "http://new-dev.usps.org/applications/events/helloworld.html";
	$event['poc_id'] = $array['ccertno'];
	$event['poc_name'] = $array['name'];
	$event['poc_phone'] = $array['number'];
	$event['poc_email'] = $array['email'];
	$event['squad_no'] = $array['sqnumber'];
	$dt = date("Ymd H:i:s",strtotime($array['date']." ".$array['time']));
	// $event['location_id'] = $locs->match_usps_to_location($array,$squadron);
	$event['price'] = $array['cost'];
	return $event;
}
//*************************************************************************
function getClassLink($name, $event, $base=''){
	$str = $window = "";
	$crsid = '';
	if ($event['oreg']=='Y') 
		$crsid = "&crsid=".$event['id'];
	$type = $event['type'];
	$squad_no = "&squad_no=".$event['sqnumber'];
	$name_url = "events/sss_course_gen.php?type=$type$squad_no$crsid"; 
	return $name_url;
}
//*************************************************************************
function getClassNameWithLink($name,$url, $base=''){
	$full = "<a href='$url' $base>$name</a>";
	return $full;
}
//*********************************************************
function getCourseName($event){
	switch($event['type']){
		case 'A16':
			return "America's Boating Course - 16 Hours";
		case 'A12':
			return "America's Boating Course - 12 Hours";
		case 'A08':
			return "America's Boating Course";
		case 'S':
			$event['type'] = 'SE';
			break; 
		case 'P':
			$event['type'] = 'PI';
			break; 
		case 'N':
			$event['type'] = 'NA';
			break; 
		default:
	}	
	$query = "code = '".$event['type']."' and active = 'Y'";
	$course = $this->courses->search_for_record($query);
	return $course['description'];
}
//*********************************************************
function getCourseRecord($event){
	switch($event['type']){
		case 'A12':
			return "America's Boating Course - 12 Hours";
		case 'A08':
			return "America's Boating Course";
		case 'S':
			$event['type'] = 'SE';
			break; 
		case 'P':
			$event['type'] = 'PI';
			break; 
		case 'N':
			$event['type'] = 'NA';
			break; 
		default:
	}	
	$query = "code = '".$event['type']."' and active = 'Y'";
	$course = $this->courses->search_for_record($query);
	return $course;
}
//****************************************************************************
function getFutureSquadronEventsByDate($squad_no){
	$i = $date = 0;
	$new = array();
	$select = " sqnumber='$squad_no' and date >= curdate()";
	$rows = $this->search_records_in_order($select,'date');
	$type = substr($this->table_name,0,1);
	foreach($rows as $hq800_row){
		$row = $this->convert_usps_hq800_to_event($hq800_row);	
		if ($row['start_date'] == $date)
			$i++	;
		$new[$row['start_date'].$type.$i] = $row;
		$date = $row['start_date'];
	}
	return $new;
}
//***********************************************************
}// class
?>