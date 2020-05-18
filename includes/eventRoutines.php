<?php
/**
 * @package		USPS  Libraries 
 * @subpackage	eventRoutines.php - .
 * @copyright	Copyright (C) 2015 Joseph P. Gibson. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
*/

//****************************************************************
function get_absolute_url($url){
		$a_url = explode("/",$url);
	switch ($a_url[0]){
		case 'http':
		case 'https':
			return $url;
		case 'applications':	
		case 'courses':
		case 'events':
			$site_url = "http://".$_SERVER['SERVER_NAME'].$_SERVER['CONTEXT_PREFIX'];
			return "$site_url/$url";
		default:
			return $url;
	}
}
//****************************************************************
function get_city_line($event){
    return "<strong>".$event['event_name']."</strong>";
}
//****************************************************************
function get_event_name($event){
	return "<strong>".$event['event_name']."</strong>";
}
//****************************************************************
function get_event_name_for_popover($event){
	if ($event['event_name_url'] == '')
		return get_event_name($event);
	$str = "{modal ";  
	$str .= $event['event_name_url'];
	$str .= "}";
	$str .= get_event_name($event);
	$str .= "{/modal} ";
	return $str;
}
//****************************************************************
function get_event_data_list($event){
	//  Return a simple list of event elements in list format 
	$str = "<ul>";
	$str .= "<li> Contact: ".$event['full_name']."</li>";
	$str .= "<li> Location: ".$event['location']['loc_name']."</li>";
	$str .= "<li> Address: ".$event['location']['address_1']."</li>";
	$str .= "<li> City:	".get_city_line($event['location'])."</li>";
	$str = "</ul>";
	return $str;
}
//****************************************************************
function get_event_location($event){
global $loc;
	$location = $event['location'];
	if ($location['location_url'] != ''){
		$url = $location['location_url'];
		if (strtolower(substr($url,0,4))!='http')
			$url = 'http://'.$url;
		$str = "<a target='_blank' href='$url'> ". 
				$location['location_name']. 
				"</a>";
	}else
		$str = $location['location_name'];
	$str .= "<br />";
	if (trim($location['location_street']) != ''){
		$str .= $location['location_street'] .
				"<br />";
	}
	if (trim ($location['location_city'].$location['location_state'].$location['location_zip'])!=''){
		$str .= $location['location_city'].
				", ".$location['location_state'].
				" ".$location['location_zip'];
	}
	return $str;
}
//*****************************************************************************
function get_date_range($event){
// Return Date Range string

	$single_day = false; 
	$start_date = strtotime($event['start_date']);
	$end_date = strtotime($event['end_date']);
	//$date = strtotime($event["start_date"]);
	if ($start_date > $end_date)
		$end_date = $start_date;		//  Ilegal - end must be equal to or after start.
	// Handle 1st line 
	switch(date("n",$end_date) - date("n",$start_date)){
		case 0:		// Start and End same month - normal
			switch (date("j",$end_date) - date("j",$start_date)){
				case 0: 		// Single Day Event
					$single_day = true;
					$dates = date("j M",$start_date);
					break;
				default:		// 		
					$dates = date("j-",$start_date).date("j M",$end_date);
					break;
			}
			break;
		default:	// Different Month - Must abreviate month name 
			$dates = date("j M - ",$start_date).date("j M",$end_date);			
			break;	
	}
	return $dates;
}
//*****************************************************************************
function getDateTime2($event){
// Show Date Range on 1st Line
// Show line break 
// Show Time Range on 2nd Line
// Show line break 
	$single_day = false; 
	$start_date = strtotime($event['start_date']);
	$end_date = strtotime($event['end_date']);
	$date = strtotime($event["start_date"]);
	if ($start_date > $end_date)
		$end_date = $start_date;		//  Ilegal - end must be equal to or after start.
	// Handle 1st line 
	switch(date("n",$end_date) - date("n",$start_date)){
		case 0:	// Start and End same month - normal
			switch (date("j",$end_date) - date("j",$start_date)){
				case 0: // Single Day Event
					$single_day = true;
					$dates = date("j M",$start_date);
					break;
				default:		// 		
					$dates = date("j-",$start_date).date("j M",$end_date);
					break;
			}
			break;
		default:	// Different Month - Must abreviate month name 
			//break;	
			$dates = date("j M - ",$start_date).date("j M",$end_date);			
	}
	if (date("Gis",$start_date) == "00000")
		$times = '';
	elseif (date("A",$start_date) != date("A",$end_date))
		$times = date("g:i A",$start_date)." - ".date("g:i A",$end_date);
	elseif (date("Gi",$end_date) == date("Gi",$start_date))
		$times = date("g:i A",$start_date);
	else
		$times = date("g:i-",$start_date).date("g:i A",$end_date);
	return "$dates<br />$times ";
}
?>