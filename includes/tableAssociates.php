<?php
require_once(JPATH_LIBRARIES."/usps/includes/routines.php");
//************************************************************************************
class tableAssociates extends USPStableAccess{
	// Generic routines to manage a table 
//********************************* Public Variables **********************************
//********************************* Private Variables *********************************
private $squad_no ;
//********************************************************************************************
function __construct($sqd, $db, $caller){
	// Creates the variables to contain identity of data and tables 
	parent::__construct('associates', $db, $caller);
	$this->squad_no=$sqd; 
		//$this->cols=$col_list;		
}// constructer
//**************************************************************************
function add_d5_associate($cert){
// Check to see if this member is already registered as an associate	
	$sel = "certificate='$cert' and squad_no='".$this->squad_no."'";
	$is_curr = $this->search_partial_records_in_order('*',$sel);
	if ($is_curr) return; 
// Add this current D5 Member as a squadron associate
	$row['certificate']=$cert;
	$row['squad_no']=$this->squad_no;
	$this->add_record($row);
}
function add_usps_associate(){
// Display a form where squadron treasurer may fill in members contact data.

// Use the roster form to eliminate duplicate entries
	
}
function delete_associate($cert){
// Removes an entry from the association table
	$sel = "certificate='$cert' and squad_no='".$this->squad_no."'";
	$this->delete_range($sel);
}
function get_assoc_certificates(){
	$list = $this->get_records('squad_no',$this->squad_no);	
	return $list;
}
function get_associates(){
// Optains and returns a list of associates by name and grade
global $sqds, $mbr, $exc;
	$out = array();
	$list = $this->get_records('squad_no',$this->squad_no);	
	foreach($list as $asoc){
		$row = $mbr->get_mbr_record($asoc['certificate']);
		$sqd = $sqds->get_record('squad_no',$row['squad_no']);
		$asoc['name']=$exc->get_d5_member_name(true,$row);
		$asoc['squad_name']=$sqd['squad_name'];
		$out[$row['last_name'].$row['certificate']]=$asoc;
	}
	ksort($out);
	return $out;
}
function show_associates_list(){
	$list = $this->get_associates();
	foreach($list as &$lst){
		$str="<p>".$lst['name']." from - ".$lst['squad_name']."</p> "; 
		echo $str;
	}
}
function show_add_USPS_member_form(){
	echo "<h1>Under Construction</h1>";
}
function show_delete_associates_form(){
	$list = $this->get_associates();
	foreach($list as &$lst){
		$cert = $lst['certificate'];
		$str="<p><input type='checkbox' name='$cert' value='Delete'> ".$lst['name']." - from ".$lst['squad_name']."</p>"; 
		echo $str;
	}
}
}// class
?>