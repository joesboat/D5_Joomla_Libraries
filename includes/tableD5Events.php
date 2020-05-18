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
//jimport("usps.tableAccess");
//*************************************************************************
class tableD5events extends USPStableAccess{
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
public $doc_types;
//*************************************************************************
function __construct($db, $caller=''){
	// Creates the variables to contain identity of data and tables 
	parent::__construct('events', $db, $caller);
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
	$this->mtg_colors = array(	"boat" => "#0000ff",
								"excom" =>"#00ff00",
								"conf" => "#00ff00",
								"mtg" => "#00ff00",
								"civic"=> "#fcef03",
								"water" => "#0000ff",
								"vse" => "#fcef03",
								"coop"=> "#fcef03",
								"oth" => "#000000"
							);
	$this->tng_colors = array(	"class" => "#ff0000",
								"advan" => "#ff0000",
								"semin" => "#ff0000",
								'spec' => "#ff0000"
							);
	$this->evt_colors = array_merge($this->mtg_colors,$this->tng_colors) ;
	$this->display_by = array(
								'date'=>'Date Order',
								'location'=>'Location Order',
								'name'=>'Class Name Order',
								'squadron'=>'Squadron Name Order',
								'classes'=>'Classes',
								'seminars'=>'Seminars'
							);
	$this->doc_types = array(	'sch'=>'Schedule',
								'reg'=>'Registration',
								'desc'=>'Description',	
								'spc'=>'Special'
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
//*************************************************************************
function build_event_name_url($course){
	if (
		//$course['course_picture_file_name'] != '' and 
		$course['course_description'] != ''
		)
			return "courses/course_gen.php?course_id=".$course['course_id'];
	return "";
}
//*************************************************************************
function convertUSPShq800ToEvent($array){
global $mbr, $sqds, $locs, $jobs, $year, $crs;
	$event['usps_id'] = $array['id'];
	$event['start_date'] = date("Y-m-d H:i:s", strtotime($array['date']." ".$array['time']));
	if ($array['edate'] == "0000-00-00") 
		$array['edate'] = $array['date'];
	$event['end_date'] =   date("Y-m-d H:i:s", strtotime($array['edate']." ".$array['time']));
	if ($event['start_date'] < date("Y-m-d H:i:s"))
		return false;
	$course = $crs->get_usps_course_data($array['type']);
	if (! $course){
		return false;
	}
	$event['c_date'] = date("Ymd", strtotime($array['date']));
	$event['course_id'] = $course['course_id'];
	$event['event_name'] = $course['course_name'];
	$event['event_name_url'] = $this->build_event_name_url($course);
	$event['event_type'] = strtolower($course['course_type']);
	$squadron = $sqds->get_record('squad_no',$array['sqnumber']);
	$member = $mbr->get_mbr_record($array['ccertno']);
	if (! $member){
		if (strtolower(substr($event['cemail'],0,3)=='seo')){
			$event['poc_id'] = $jobs->get_squadron_ed_officer_certificate($squadron['squad_no'],$year);
		}else
			$event['poc_id'] = $jobs->get_squadron_ed_officer_certificate($squadron['squad_no'],$year);
	}else
		$event['poc_id'] = $member['certificate'];
	if (!$squadron or !isset($event['poc_id'])){
		$xyz = $squadron;
		$abc = $member;
		$xxx = $abc;
	}
	$event['squad_no'] = $squadron['squad_no'];
	$dt = date("Ymd H:i:s",strtotime($array['date']." ".$array['time']));
	$event['location_id'] = $locs->match_usps_to_location($array,$squadron);
	$event['price'] = $array['cost'];
	if ($cur = $this->get_record("usps_id",$event['usps_id'])){
		$event['event_id'] = $cur['event_id'];
		$this->update_record_changes('event_id',$event);
	} else {
		$this->add_record($event);
		$name = $event['event_name'];
		$squad_no = $event['squad_no'];
		$date = $event['start_date'];
		log_it("Added $name starting $date squadron $squad_no",__LINE__);
	}
}
//*************************************************************************
function get_avail_tng_events_by_date($squad_no = ''){
	$query = 'start_date >= curdate() and (';
	foreach($this->tng_types as $key=>$value){
		$query .= "event_type = '$key' or " ;
	}
	$query = substr($query,0,strlen($query)-3).")";
	if ($squad_no != ''){
		$squad_no = sprintf("%04d",$_REQUEST['squad_no']);
		$query  .= "and squad_no = '$squad_no' ";
	}
	return $this->search_records_in_order($query,'start_date'); 	
}
//*******************************************************************************
function get_events($squads, $select='', $desc=''){
	$query ="";
	if ($select != '')
		$query .= " ( $select ) ";
	if ($squads != ''){
		if ($query != '') $query .= " and ";
		$query .= " ( $squads )";
	}
	$list = $this->search_records_in_order($query,"start_date $desc");
	return $list;
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
function getFutureEvents($squads='', $select=''){
	$query = "end_date >= curdate() ";
	if ($select != '')
		$query .= " and ( $select ) ";
	if ($squads != '') 
		$query .= " and ( $squads )";
	$list = $this->search_records_in_order($query,'start_date');
	return $list;
}
//*************************************************************************
function get_tng_class_events_by_date($squad_no = ''){
	$query = 'start_date >= curdate() and (';
	foreach($this->tng_types as $key=>$value){
		if ($key != 'semin')
			$query .= "event_type = '$key' or " ;
	}
	$query = substr($query,0,strlen($query)-3).")";
	if ($squad_no != ''){
		$squad_no = sprintf("%04d",$_REQUEST['squad_no']);
		$query  .= "and squad_no = '$squad_no' ";
	}
	return $this->search_records_in_order($query,'start_date'); 	
}
//*************************************************************************
function get_tng_seminar_events_by_date($squad_no = ''){
	$query = 'start_date >= curdate() and (';
	$query .= "event_type = 'semin' )" ;
	if ($squad_no != ''){
		$squad_no = sprintf("%04d",$_REQUEST['squad_no']);
		$query  .= "and squad_no = '$squad_no' ";
	}
	return $this->search_records_in_order($query,'start_date'); 	
}
//*************************************************************************
function get_avail_tng_events_by_location(){
	$query = 'start_date >= curdate() and (';
	foreach($this->tng_types as $key=>$value){
		$query .= "event_type = '$key' or " ;
	}
	$query = substr($query,0,strlen($query)-3).")";
	return $this->search_locations_with_join($query,"locations.location_state, locations.location_city");
}
//*************************************************************************
function get_avail_tng_events_by_name($squad_no = ''){
	$query = 'start_date >= curdate() and (';
	foreach($this->tng_types as $key=>$value){
		$query .= "event_type = '$key' or " ;
	}
	$query = substr($query,0,strlen($query)-3).")";
	if ($squad_no != ''){
		$squad_no = sprintf("%04d",$_REQUEST['squad_no']);
		$query  .= "and squad_no = '$squad_no' ";
	}
	return $this->search_records_in_order($query,'event_name, start_date'); 	
}
//*************************************************************************
function get_avail_tng_events_by_squad(){
	$query = 'start_date >= curdate() and (';
	foreach($this->tng_types as $key=>$value){
		$query .= "event_type = '$key' or " ;
	}
	$query = substr($query,0,strlen($query)-3).")";
	return $this->search_records_in_order($query,'squad_no, event_name, start_date');	
}
//*************************************************************************
function search_locations_with_join($select,$order){
	$table = $this->table_name;
	$join = " join locations ON ($table.location_id = locations.location_id )";
	$list = $this->search_with_join($select,$order,$join);
	return $list;
}

//****************************************************************************
}// class
//****************************************************************************
?>