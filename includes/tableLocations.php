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
class table_USPSlocations extends USPStableAccess{
// Routines to manage the events table
// Events consist of either a meeting, conference, rendezvous or class 
//********************************* Public Variables **********************
//********************************* Private Variables *********************
//*************************************************************************
function __construct($db, $caller=''){
	// Creates the variables to contain identity of data and tables 
	parent::__construct('sss_locations', $db, $caller);
		//$this->cols=$col_list;		
}// constructer
//*********************************************************
function createLocationFromHQ800($usps){
	$rec['location_name'] = $usps['place'];
	$rec['location_city'] = $usps['city'];
	$rec['location_state'] = $usps['state'];
	$rec['location_zip'] = $usps['zip'];
	$rec['location_street'] = $usps['adr'];
	$rec['location_url'] = '';
	return $rec;
}
/*
function get_full_address($location_id,$break=false){
	$location = $this->get_record('location_id',$location_id);
	if (! $location) return "";
	$str = $location['location_street'];
	if ($str != '')
		if ($break)
			$str .= "<br>";
		else
			$str .= ", ";
	$str .= $location['location_city']." ";
	$str .= $location['location_state']." ";
	$str .= $location['location_zip'];
	return $str;
}
*/
//*********************************************************
function get_location_data($id){
	// Obtains a record from locations where $id = location_id 
	if ($id==0){
		$query="Stop here to trap it.";
	}
	$array = $this->get_record('location_id',$id);
	return $array;
}
//*********************************************************
function get_location_list($squad_no){
	$ary = array();
	$ary[0]='';
	// Called from setup_class.php
	// Queries locations table for list of known locations
	// Returns an array containing location_id and location_name 
	
	$select = "squad_no = '".$squad_no."' ";
	$list = 'location_id, location_name';
	$array = $this->search_partial_records_in_order($list,$select,'location_name');
	foreach($array as $entry){
		$ary[$entry['location_id']] = $entry['location_name'];
	}
	return $ary;
}
/*
//*********************************************************
function get_location_name($location_id, $link = 0){
	$location = $this->get_record('location_id',$location_id);
	if (! $location) return "";
	$str = '';
	$url=$location['location_url'];
	if ($link and $url != '')
		$str .= "<a target='blank' href='$url'>";
	$str .= $location['location_name'];
	if (($url=$location['location_url']) != '')
		$str .= "</a>";
	return $str;
}
//*********************************************************
function match_usps_to_location($usps,$squad){
// Return the location ID that that matches the usps location
// Create a new location if none exists
	//$xyz = $usps['place'];
	//if (strpos($usps['place'],"'") or strpos($usps['place'],'"')){
	//	$xyz = $usps['place'];
	//}
	$loc_name = fix_value($usps['place']);
	if (strtolower($loc_name) == 'tbd' or strtolower($loc_name) == 'to be determined'){
		$usps['place'] = 'Call for location!';
		$usps['city'] = $squad['city'];
		$usps['zip'] = $squad['zip'];
		$usps['state'] = $squad['state'];
	}
	$places = $this->search_records_in_order("location_name = '$loc_name' and location_zip = '".$usps['zip']."'");
	if ($places and count($places) != 0 )
		return $places[0]['location_id'];
	$rec['location_name'] = $usps['place'];
	if (isset($usps['address'])){
		$rec['location_street'] = $usps['address'];
	}
	$rec['location_city'] = $usps['city'];
	$rec['location_state'] = $usps['state'];
	$rec['location_zip'] = $usps['zip'];
	$rec['squad_no'] = $squad['squad_no'];
	$this->add_record($rec);
	$query = "location_name='".fix_value($usps['place'])."' and location_zip='".$usps['zip']."'";
	$place = $this->search_record($query);
	return $place['location_id'];
}
//*********************************************************
function update_record($col,$array){
	if ($array['location_url']!='')
		if (strtolower(substr($array['location_url'],0,4))!='http'){
			$array['location_url'] = 'http://'.$array['location_url'];	
		}
	parent::update_record($col,$array);
}
*/
}
?>