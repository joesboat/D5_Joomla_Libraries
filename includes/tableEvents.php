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
class table_USPSevents extends USPStableAccess{
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
	parent::__construct('sss_events', $db, $caller);
	//$this->squad_no=$sqd;
	$this->blank_record['squad_no'] = $this->squad_no;
	$this->blank_record['start_date'] = date("Y-m-d 19:00:00");
	$this->blank_record['end_date'] = date("Y-m-d 19:00:00");
	$this->mtg_types = array(	"boat" => "Boat Show",
								"excom" =>"EXCOM Meeting",
								"conf" => "Conference",
								"mtg" => "General Meeting",
								"civic"=> "Civic Service",
								"water" => "On The Water Event",
								"vse" => "Vessel Examiner Event",
								"coop"=> "Cooperative Charting",
								"oth" => "Other"
							);
	$this->tng_types = array(	"class" => "Basic Boating Class",
								"advan" => "Advanced or Elective Class",
								"semin" => "Seminar",
								'spec' => "Special Class"
							);
	$this->evt_types = array_merge($this->mtg_types,$this->tng_types) ;
	$this->display_by = array(
								'date'=>'Date Order',
								'location'=>'Location Order',
								'name'=>'Class Name Order',
								'squadron'=>'Squadron Name Order',
								'classes'=>'Classes',
								'seminars'=>'Seminars'
							);
	
	//$this->cols=$col_list;
}// constructer
//****************************************************************************
function addBlankEventRecord(){
	$this->add_record($this->blank_record);
	$list = "max(event_id)";
	$rec = $this->search_partial_records($list,1);
	$rec = $this->get_record('event_id',$rec[0]['max(event_id)']);
	return $rec;
}
//****************************************************************************
function getFutureSquadronEventsByDate($squad_no){
	$i = $date = 0;
	$new = array();
	$select = " squad_no='$squad_no' and start_date >= curdate()";
	$rows = $this->search_records_in_order($select,'start_date');
	foreach($rows as $row){
		if ($row['start_date'] == $date)
			$i++;
		$new[$row['start_date'].$i] = $row;
		$date = $row['start_date'];
	}
	return $new;
}
//*******************************************************************************
function get_future_events($squad_no, $select=''){
	$select = "start_date >= curdate() and squad_no = '$squad_no'";
	$list = $this->search_records_in_order($select,'start_date');
	return $list;
}}// class
?>