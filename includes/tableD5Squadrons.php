<?php
/*
Copyright (C) March 2013, Joseph P. Gibson.

This program is free software to USPS Members developing software for USPS Squadrons or Districts: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program.  If not, see <http://www.gnu.org/licenses/>.

Owner: 	Joseph P. Gibson
		USPS District 5 Webmaster 
*/
//jimport('usps.tableAccess');
//jimport('usps.tableVHQAB');
//jimport('usps.includes.routines');
//jimport('USPSaccess.dbUSPS');
//require_once('tableAccess.php');
require_once('routines.php');
//*********************************************************
class tabled5_Squadrons extends USPStableAccess{
//*********************************************************
function __construct($db, $caller = ''){
		// Creates the variables to contain identity of data and tables 
	parent::__construct("d5_squadrons", $db, $caller);
		//$this->list_cols=$col_subset; 
		//$this->cols=$col_list;		
} // constructer	
//*************************************************************
function get_display_year($squad_no = '6243'){
	$row_squad = $this->get_record('squad_no',$squad_no);
	$row_dist = $this->get_record('squad_no','6243');
	return max($row_squad['d_year'],$row_dist['d_year']);
}
//*************************************************************
function get_maint_year($squad_no){
	$row = $this->get_record('squad_no',$squad_no);
	return $row['m_year'];
}
//*************************************************************
function getSquadronName($squad_no, $link=0){
	if ($squad_no == '')
		return "";
	$sq = $this->get_record('squad_no',$squad_no);
	$url = $sq['web_site'];
	$str = '';
	if ($link and $url != ''){
		$str .= "<a target='_blank' href='$url'>";
	}
	if ($sq['abc_short_name']){
		// This squadron had adopted an ABC- namespace
		$str .= str_replace("ABC","America's Boating Club&reg;",$sq["abc_short_name"]);
	} else {
		$str .= $sq['squad_name'];
	}
	if ($link and $url != ''){
		$str .= "</a>";
	}
	return $str;
}
//*************************************************************
function getSquadShortName($squad_no, $link=0){
	if ($squad_no == '')
		return "";
	$sq = $this->get_record('squad_no',$squad_no);
	$url = $sq['web_site'];
	$str = '';
	if ($link and $url != ''){
		$str .= "<a target='_blank' href='$url'>";
	}
	$str .= get_short_name($sq);
	if ($link and $url != ''){
		$str .= "</a>";
	}
	return $str;
}
//*************************************************************
function get_squadron_list(){
global $exc;
	$array = $this->get_squadrons();
	foreach($array as $key=>$value){
		//$ary[$value['certificate']] = $exc->get_d5_member_name(false,$value);
		$ary[$value['squad_no']] = $value['squad_name'];
	}
	return $ary;
}
//*************************************************************
function get_new_squadron_list($link = false){

	$list = $this->get_squadron_accounts();
	//foreach($array as $key=>$value){
		//$ary[$value['certificate']] = $exc->get_d5_member_name(false,$value);
	//	$ary[$value['squad_no']] = $value['squad_name'];
	//}
	foreach($list as $no){
		$sqds[$no['squad_no']] = $this->getSquadShortName($no['squad_no'],$link); 
	}
	return $sqds;	//return $ary;
}
//*************************************************************
function get_squadron_state($squad_no){
	$row = $this->get_record('squad_no',$squad_no);
	return $row['state'];	
}
//*************************************************************
function get_squadrons(){
	// Returns records for our currenly chartered or provisional squadrons
	// Status Codes:
	//		D - Record for a district (Not Returned)
	//		P - Provisional Squadron (Returned)
	//		R - Requested permission to return charter (Returned)
	//		S - Terminating - Still has members
	//		T -	Terminated - Record is kept (Not Returned)
	//		U - USPS 
	return $this->search_records_in_order("status = '' or status = 'P' ",
			'squad_short_name');
}
//*************************************************************
function get_squadron_accounts($link=FALSE){
	$list = $this->search_partial_records_in_order("squad_no","status = '' or status = 'P' ",
			'squad_short_name');
	return $list;
}
//*************************************************************
function get_squadrons_by_state($state){
	return $this->search_records_in_order(
			"state='$state' and (status = '' or status = 'P')",
			'squad_short_name');
	
}
//*************************************************************
function get_squadron_link($a_squad){
	$squad_name = $a_squad['squad_short_name'];
	$squad_no = sprintf("%04d",$a_squad['squad_no']);
	$url = "show_squadrons.php?squad_no=$a_squad_no";
	$lat = $a_squad['slat'];
	$lon = $a_squad['slon'];
	$str = "<a href='$url' onmouseover='addIcon($lat,$lon);' onmouseout='clearMarkers();' >$squad_name</a><br/>";
	return $str	;	
}
//*************************************************************
function get_squadron_link_for_index($a_squad){
	$squad_name = $a_squad['squad_short_name'];
	$squad_no = sprintf("%04d",$a_squad['squad_no']);
	$url = "squadron_site.php?squad_no=$squad_no";
	$lat = $a_squad['slat'];
	$lon = $a_squad['slon'];
	//$str = "<a href='$url' onmouseover='addInfo();' onmouseout='clearMarkers();' >$squad_name</a><br/>";
	$str = "<a href='$url' onmouseover='addIcon($lat,$lon);' onmouseout='clearMarkers();' >$squad_name</a><br/>";
	return $str	;	
}
//*************************************************************
function get_web_or_our_data($a_squad){
//  return the squadron WEB URL or link to our_squadrons page 
	$squad_name = $a_squad['squad_short_name'];
	$squad_no = sprintf("%04d",$a_squad['squad_no']);
	$url = "j_squadron_site.php?squad_no=$squad_no";
	$str = "<a href='$url'>$squad_name</a><br/>";
	return $str	;
}
//*************************************************************
function get_orgs_with_events(){
	return $this->search_records_in_order("status = '' or status = 'D' or status = 'U' ",'squad_no');
}
//*************************************************************
function showSquadronListBox($name, $squad_no,$squadrons, $count=5){
	echo "<select name='$name' size='$count' id='$name' width='50'>;" ;
	show_option_list($squadrons,$squad_no);
	echo "</select>";
}
//*************************************************************
function toggle_print_booklet($squad_no){
	$row = $this->get_record('squad_no',$squad_no);
	$row['print_booklet'] = ! $row['print_booklet']; 
	$this->update_record('squad_no',$row);
	return $row['print_booklet'];
}
} // end of class 