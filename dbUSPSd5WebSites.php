<?php
/*
Copyright (C) March 2013, Joseph P. Gibson.
This program is free software to USPS Members developing software for USPS Squadrons or Districts: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or any later version.
This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
You should have received a copy of the GNU General Public License along with this program.  If not, see <http://www.gnu.org/licenses/>.
Owner: 	Joseph P. Gibson - USPS District 5 Webmaster 
*/
//jimport('usps.Joes_factory');
//jimport('usps.includes.tableD5Awards');
//jimport('usps.includes.tableD5Events');
//jimport('usps.includes.tableD5Locations');
//jimport('usps.includes.routines');
//*************************************************************************
class USPSd5dbWebSites { 		// extends USPSdbWebSites {
// Routines that utilize data from the events table to display information.  
// Only generated and used on pages that display a list of events
// Called from 'calendar.php' to list district or national events
// Called from 'squad_events' to list all squadron events
// Called from 'booklet routines' to list a variety of events 
//********************************* Public Variables **********************
//********************************* Private Variables *********************
private $attendees;
private $blank_event;
private $blobs;
//private $classes;
private $courses;
private $db;
public $display_by;
private $dist;
private $awds;
public $evts;
private $locs;
//private $mbrclasses;
private $registrations;
//private $seminars; 
private $squad;				// The local copy of a squadron record
private $squad_no;			// The account number for a squadron
var $loging;
//*************************************************************************
function __construct($dbConnect='local', $caller=''){
//include ( "/web/joomla/libraries/USPSaccess/dbUSPS.php");
include (JPATH_LIBRARIES . "/USPSaccess/dbUSPS.php");
	switch(strtolower(trim($dbConnect))){
		case "local":
			$db = $db_d5;
			break;
		case "remote":
			$db = $db_d5_remote;
			break;
		default:
			$db = $db_d5;
	}
	$this->db = $db;

}// constructer
//*********************************************************
function addEvent($event){
	$this->loadDB_events();
	$ok = $this->loadDB_blobs();
	$rec = $this->evts->addBlankEventRecord($event);
	$event_id = $event['event_id'] = $rec['event_id'];
	// Consider calling updateEvent 
	$this->evts->update_record_changes('event_id',$event);
	if ($event['event_description'] != ''){
		$this->blobs->store_event_description($event['event_id'],$event['event_description']);
	}
	return $event;
}
//*********************************************************
function addLocation($rec){
	$this->loadDB_locations();
	$this->locs->add_record($rec);
}
//*********************************************************
function close(){
	if (isset($this->awds)) $this->awds->close(); 
	if (isset($this->evts)) $this->evts->close(); 
	if (isset($this->locs)) $this->locs->close(); 
	if (isset($this->blobs)) $this->blobs->close(); 
	if (isset($this->registrations)) $this->registrations->close(); 
	if (isset($this->attendees)) $this->attendees->close(); 
}
//*********************************************************
function deleteAward($award_id){
	$ok = $this->loadDB_awards();
	// Delete all knowledge of this award identifier
	$this->awds->delete_records("award_id",$_POST['award_id']);	
	}
//*********************************************************
function deleteEvent($event_id){
	// Delete all knowledge of this event identifier
	$this->loadDB_events();
	$ok = $this->loadDB_blobs();
	$this->evts->delete_records("event_id",$_POST['event_id']);
	$this->blobs->delete_event_description($_POST['event_id']);
}
//*********************************************************
function deleteExtraFile($url){
	// $rel_file_name includes folders relative to JPATH_BASE.'/'
	$ok = $this->loadDB_blobs();
	if (isset($loging) and $loging==1) log_it("The url parameter is: $url");
	$siteUrl = getSiteUrl();
	if (isset($loging) and $loging==1) log_it("The siteUrl is: $siteUrl");
	$a_name = explode($siteUrl,$url);
	if (isset($loging) and $loging==1) write_log_array($a_name, "The a_name array is:");
	$rel_file_name = $a_name[count($a_name)-1];
	if (isset($loging) and $loging==1) log_it("The rel_file_name is $rel_file_name");
	$abs_file_name = JPATH_BASE.$rel_file_name;
	if (unlink($abs_file_name)){
		$this->blobs->delete_records('title',$url);
		} else {
		if (isset($loging) and $loging==1) log_it("Did not delete file or blobs entry!");
		$aljfalsj = $rel_file_name;
	} 
}
//*********************************************************
function deleteLocation($location_id){
	$this->loadDB_locations();
	$this->locs->delete_records("location_id",$location_id);
}
//*********************************************************
function getAlleventLinks($event_id){
	//  Searches sss-blobs for links 
	// Builds and returns an array in the form of 
	// b_use, title, link, 
	
}
//*********************************************************
function getAllFutureEventsWithLocation($org_nos){
// Merges future training and meeting events into an array by date 
// Adds a location array in each event. 
	if (! $org_nos )	return array();
	if ($this->logging) log_it("Entering ".__FUNCTION__,__LINE__);
	$ary1 = $this->getFutureTrainingEventsWithLocation($org_nos);
	$ary2 = $this->getFutureMeetingEventsWithLocation($org_nos);	
	$list = array_merge($ary1,$ary2);	
	ksort($list) ;
	if ($this->logging) log_it("Leaving ".__FUNCTION__,__LINE__);
	return $list ;
}
//*********************************************************
function getAddressFromEvent($event){
	$str ='';
	$address = trim($event['adr']);
	$city = trim($event['city']);
	$state = trim($event['state']);
	$zip = trim($event['zip']);
	if ($address != '')
		$str .= "$address<br/>";
	return "$str$city $state $zip";
	
}
//*********************************************************
function getAttendeesObject(){
	$ok = $this->loadDB_attendees();
	return $this->attendees;
}
//*********************************************************
function getAward($award_id){
	$ok = $this->loadDB_awards();
	$array = $this->awds->get_record('award_id',$award_id);
	if (! $array) return false;
	$array['extras'] = $this->getAwardDocuments($award_id);
	return $array;
}
//*********************************************************
function getAwardBlank(){
	$ok = $this->loadDB_awards();
	return $this->awds->blank_record;
}
//*********************************************************
function getAwardDocuments($award_id){
	$ok = $this->loadDB_blobs();
	// Build a multi-diminsion array where each base element is the document type
	// t 
	// Document types in b_info can be 'spc', 'desc', 'reg' or 'sch'
	// IF b_info is 'desc', 'reg' or 'sch' that becomes the array name
	// If b_info is spc use the array name is in the title column 
	// Array indexes are obtained from the b_type column 
	$list = array();
	$doc_list = $this->blobs->get_award_documents($award_id);
	foreach($doc_list as $doc){
		$list[$doc['b_info']][$doc['b_type']] = $doc['title'];
	}
	return $list;
}
//*********************************************************
function getAwardsObject(){
	$ok = $this->loadDB_awards();
	return $this->awds;
}
//*********************************************************
function getAwards($squad_no){
	$ok = $this->loadDB_awards();
	return $this->awds->getAwards($squad_no);
}
//*********************************************************
function getBlobsObject(){
	$ok = $this->loadDB_blobs();
	return $this->blobs;
}
//*********************************************************
function getCoursesObject(){
	$ok = $this->loadDB_courses();
	return $this->courses;
}
//*********************************************************
function getDistNumberFromAcct($dist_no){
	$this->loadDistrict($dist_no);
	return $this->dist['Name'];
}
//*********************************************************
function getDistrictName($dist_no){
	$this->loadDistrict($dist_no);
	$str = "District ". $this->dist['Name'];
	return $str;
}
//*********************************************************
function getDistrictLatLon($dist_no){
	$this->loadDistrict($dist_no);
	$ary['zoom'] = $this->dist['zoom'];
	$ary['lat'] = $this->dist['plat'];
	$ary['lon'] = '-'.$this->dist['plon'];
	return $ary;
}
//*********************************************************
function getDistrictUrl($dist_no){
	$this->loadDistrict($dist_no);
	$url = "http://".$this->dist['url'];
	return $url;
}
//*********************************************************
function getDocTypes(){
	$this->loadDB_events();
	$ok = $this->loadDB_awards();
	return array_merge($this->evts->doc_types, $this->awds->doc_types);
	//return $this->evts->doc_types;
}
//*********************************************************
function get_event_blank(){
	$this->loadDB_events();
	return $this->blank_event;
}
//*********************************************************
function get_event_columns(){
	$this->loadDB_events();
	return $this->evts->cols;
}
//*********************************************************
function getEvent($event_id, $public = FALSE){
	$this->loadDB_events();
	$array = $this->evts->get_record('event_id',$event_id);
	if (! $array) return false;
	$array['event_description'] =  $this->getEventDescription($event_id); 
	$array['extras'] = $this->getEventDocuments($event_id, $public);
	return $array;
}
//*********************************************************
function getEventColors(){
	$this->loadDB_events();
	return $this->evts->evt_colors;	
}
//*********************************************************
function getEventDescription($event_id){
	$ok = $this->loadDB_blobs();
	return $this->blobs->get_event_description($event_id);
}
//*********************************************************
function getEventDocuments($event_id,$public = FALSE){
	$ok = $this->loadDB_blobs();
	// Build a multi-diminsion array where each base element is the document type
	// t 
	// Document types in b_info can be 'spc', 'desc', 'reg' or 'sch'
	// IF b_info is 'desc', 'reg' or 'sch' that becomes the array name
	// If b_info is spc use the array name is in the title column 
	// Array indexes are obtained from the b_type column 
	// If $public do not display document if flag field has the word private
	$list = array();
	$doc_list = $this->blobs->get_event_documents($event_id,$public);
	foreach($doc_list as $doc){
		$list[$doc['b_info']][$doc['b_type']] = $doc['title'];
	}
	return $list;
}
//*********************************************************
function getEventName($event, $link=false){
	$url=$event['event_name_url'];
	if (!$link or $url == '') return $event['event_name'];
	$str = "<a target='blank' href='$url'>";
	$str .= $event['event_name'];
	$str .= "</a>";
	return $str;
}
//*********************************************************
function getEventsObject(){
	$this->loadDB_events();
	return $this->evts;
}
//*********************************************************
function getEvents($source, $select, $desc=''){
	$this->loadDB_events();
	return $this->evts->get_events($source, $select, $desc);
}
//*********************************************************
function getEventTypes(){
	$this->loadDB_events();
	return $this->evts->evt_types;	
}
//*********************************************************
function getFutureEvents($squad_no, $select=''){
	$this->loadDB_events();
	$squads = '';
	if ($squad_no != '') 
		$squads = "squad_no='$squad_no'";
	$events = $this->evts->getFutureEvents($squads, $select);
	foreach($events as &$evt){
		$evt['location'] = $this->getLocationData($evt['location_id']);
	}
	return $events;
}
//*********************************************************
function getLocation($loc_id){
	$this->loadDB_locations();
	return $this->locs->get_location_data($loc_id);
}
//*********************************************************
function getLocationName($location, $link=false){
	$url=$location['location_url'];
	if (!$link or $url == ''){
		$str = "<span class='location' >". $location['location_name'] ."</span>";
		return $str;
	}
	$str = "<a target='blank' class='location' href='$url'>";
	$str .= $location['location_name'];
	$str .= "</a>";
	return $str;
}
//*********************************************************
function get_location_blank(){
	$this->loadDB_locations();
	return $this->locs->blank_record;
}
//*********************************************************
function get_location_columns(){
	$this->loadDB_locations();
	return $this->locs->cols;
}
//*********************************************************
function getLocationData($loc_id){
	$this->loadDB_locations();
	return $this->locs->get_location_data($loc_id);
}
//*********************************************************
function getLocationList($squad_no){
	$this->loadDB_locations();
	return $this->locs->get_location_list($squad_no);
}
//*********************************************************
function getLocationsObject(){
	if ($this->loadDB_locations())
		return $this->locs;
	else
		return false;
}
function get_meeting_types(){
	$this->loadDB_events();
	return $this->evts->mtg_types;
}
//*********************************************************
function getRegistrationByUser($certno,$event_id){
	$ok = $this->loadDB_registrations();
	$query = "certificate='$certno' and event_id='$event_id'";
	$reg = $this->registrations->search_record($query);
	return $reg;
}
//*********************************************************
function getRegistrationMembers($reg){
	// Return an array of certificate numbers including primary 
	
}
//*********************************************************
function getRegistrationGuests($reg){
	// Return an array of guesst records
	
}
//*********************************************************
function getRegistratonsObject(){
	$ok = $this->loadDB_registrations();
	return $this->registrations;
}
//*********************************************************
function getSpecificDocument($id){
	$ok = $this->loadDB_blobs();
	return $this->blobs->get_document($id);
}
//*********************************************************
private function loadDB_attendees(){
	$zya = true;
	if (!isset($this->attendees)){
		if (!$this->attendees = new tableD5Attendees($this->db)){
			log_it("Did not open tableD5Attendees table");
			return false;
		}
		return true;
	}
	return true;
}
//*********************************************************
private function loadDB_blobs(){
	$zya = true;
	if (!isset($this->blobs)){
		if (!$this->blobs = JoeFactory::getTable("tableD5Blobs",$this->db)){
			log_it("Did not open tableD5blobs table");
			return false;
		}
		return true;
	}
	return true;
}
//*********************************************************
private function loadDB_awards(){
	$zya = true;
	if (!isset($this->awds)){
		if (!$this->awds = new tableD5awards($this->db)){
			log_it("Did not open tableD5awards table");
			return false;
		}
		return true;
	}
	return true;
}
//*********************************************************
private function loadDB_courses(){
	$zya = true;
	if (!isset($this->awds)){
		if (!$this->courses = new tableCourses($this->db)){
			log_it("Did not open library tableCourses & usps_courses table");
			return false;
		}
		return true;
	}
	return true;
}
//*********************************************************
private function loadDB_events(){
	$zya = true;
	if (!isset($this->evts)){
		if (!$this->evts = JoeFactory::getTable("tableD5events",$this->db)){
			log_it("Did not open library tableEvents & events table");
			return false;
		}
		$this->blank_event = $this->evts->blank_record;
		$this->blank_event['event_description'] = '';
		return true;
	}
	return true;

}
//*********************************************************
private function loadDB_locations(){
	$zya = true;
	if (!isset($this->locs)){
		if (!$this->locs = JoeFactory::getTable("table_D5locations",$this->db)){
			log_it("Did not open library table_D5Locations & locations table");
			return false;
		}
		return true;
	}
	return true;
}
//*********************************************************
//*********************************************************
//*********************************************************
//*********************************************************
//*********************************************************
//*********************************************************
private function loadDB_registrations(){
	$zya = true;
	if (!isset($this->registrations)){
		if (!$this->registrations = new tableD5Registrations($this->db)){
			log_it("Did not open tableD5Registrations table");
			return false;
		}
		return true;
	}
	return true;
}
//*********************************************************
function loadDistrict($dist_no){
	$this->loadDBwebsites();
	if (isset($this->dist))
		if ($this->dist['sqnumber'] == $dist_no)
			return true;
	$this->dist = $this->websites->get_record("sqnumber",$dist_no);
	return true; 
}
//*********************************************************
function storeEventLink($event_id,$b_use,$URL){
	// stores link data in sss_blobs 
}
//*********************************************************
function updateEvent($event){
	$ok = $this->loadDB_blobs();
	$this->loadDB_events();
	$year = date('Y',strtotime($event['start_date']));
	$return = '';
	$this->evts->update_record_changes('event_id',$event);
	if (trim($event['event_description']) != ''){
		$this->blobs->store_event_description(
			$event['event_id'],
			$event['event_description']);
	}
	if (isset($event['event_extra'])){
		if ($event['doc_type'] == ''){
			return 'You must choose a document type!';
		}
		if ($event['doc_type'] == 'spc' and $event['doc_special'] == ''){
			return 'You must specify a special document type!';
		}
	// Call File System Routine to store file 
	$doc_types = $this->getDocTypes(); 
	if ($event['doc_type'] == 'spc'){
		$type = $event['doc_special'];
	} else {
		$type = $doc_types[$event['doc_type']];
	}
	$rel_file_name = storeExtraFile(			
		$event['event_id'],
		$event['event_extra'],	// The $_FILE array
		$type,
		$year) ;
	if ($event['event_extra']['type'] == "application/vnd.openxmlformats-officedocument.wordprocessingml.document")
		$event['event_extra']['type'] = "application/msword";
			
	// Call Blobs Routine to associate file name with event 	
		$return = $this->blobs->store_event_document(
			$event['event_id'],
			$rel_file_name,
			$event['event_extra']['type'], //$mime
			$type,
			$year,
			$event['event_extra'],
			(isset($event['doc_private']) and $event['doc_private']=='on')
		);				// Used for file prefix 
	}
	return $return ;
}
//*********************************************************
function updateLocation($rec){
	$this->loadDB_locations();
	$this->locs->update_record("location_id",$rec);
}
//*********************************************************
}// class
?>