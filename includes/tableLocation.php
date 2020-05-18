<?php
require_once('tableLocations.php');
//*************************************************************************
class table_USPSlocation extends table_USPSlocations{
// Routines to manage the events table
// Events consist of either a meeting, conference, rendezvous or class 
//********************************* Public Variables **********************
//********************************* Private Variables *********************
public $squad_no ;
public $dist_no ;

//*************************************************************************
function __construct($sqd, $db, $caller=''){
	// Creates the variables to contain identity of data and tables 
	parent::__construct($db, $caller);
	$this->squad_no=$sqd; 
		//$this->cols=$col_list;		
}// constructer
//*********************************************************
/*
function add_record($array){
	$array['squad_no']=$this->squad_no;	
	if ($array['location_url']!='')
		if (strtolower(substr($array['location_url'],0,4))!='http'){
			$array['location_url'] = 'http://'.$array['location_url'];	
		}
	parent::add_record($array);
}
*/
//*********************************************************
function get_location_list(){
	$ary = array();
	$ary[0]='';
	// Called from setup_class.php
	// Queries locations table for list of known locations
	// Returns an array containing location_id and location_name 
	
	$select = "squad_no = '".$this->squad_no."' ";
	$select .= "or squad_no = '".$this->dist_no."' "; 
	$list = 'location_id, location_name';
	$array = $this->search_partial_records_in_order($list,$select,'location_name');
	foreach($array as $entry){
		$ary[$entry['location_id']] = $entry['location_name'];
	}
	return $ary;
}
/*
//*********************************************************
function update_record($col,$array){
	if ($array['location_url']!='')
		if (strtolower(substr($array['location_url'],0,4))!='http'){
			$array['location_url'] = 'http://'.$array['location_url'];	
		}
	$array['squad_no'] = $this->squad_no;
	parent::update_record($col,$array);
}
*/
}
?>