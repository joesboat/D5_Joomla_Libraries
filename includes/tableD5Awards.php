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
//require_once(JPATH_LIBRARIES ."/usps/tableAccess.php");
//require_once(JPATH_LIBRARIES ."/usps/tableAwards.php");
//*************************************************************************
class tableD5awards extends USPStableAccess{
// Routines that utilize data from the awards table to display information.  
// Only generated and used on pages that display a list of awards
// Called from 'calendar.php' to list district or national awards
// Called from 'squad_awards' to list all squadron awards
// Called from 'booklet routines' to list a variety of awards 
//********************************* Public Variables **********************
//********************************* Private Variables *********************
private $squad_no;
public $display_by;
public $mtg_types;
public $doc_types;
//*************************************************************************
function __construct($db, $caller=''){
global $exc, $jobs, $blob,  $mbr, $sqd;
	// Creates the variables to contain identity of data and tables 
	parent::__construct('d5_awards', $db, $caller);
	//$this->squad_no=$sqd;
	//$this->blank_record['squad_no'] = $this->squad_no;
	//$this->blank_record['start_date'] = date("Y-m-d 19:00:00");
	//$this->blank_record['end_date'] = date("Y-m-d 19:00:00");
	$this->display_by = array(		'date'=>'Date Order',
									'location'=>'Location Order',
									'name'=>'Class Name Order',
									'squadron'=>'Squadron Name Order',
									'classes'=>'Classes',
									'seminars'=>'Seminars'
								);
	$this->award_types = array(		'personal'=>'Personal Awards',
									'squadron'=>'Awards to a Squadron',
									'district'=>'Awards to a District'
								);
	$this->award_sources = array(	'national'=>'National',
									'district'=>'Distict',
									'squadron'=>'Squadron'	
								);
	$this->object_types = $this->excludes = $this->doc_types = array(		
									'cert'=>'Certificate',
									'pic'=>'Photograph',
									'plk'=>'Plaque',
									'mem'=>'Memorial',
									'chr'=>'Charter'
								);
	$this->doc_types['trvl'] = 		'Traveler';		
	$this->doc_types['tplk'] =		'Traveler with Plaque';							
	$this->doc_types['spc']	=		'Special';
	$this->awd_names = array(	
					"Caravelle Award"=>"Caravelle Award",
					"Commanders Trophy Advanced Grades Award"=>"Commanders Trophy Advanced Grades Award",
					"Commanders Trophy Electives Award"=>"Commanders Trophy Electives Award",
					"D/5 Civic Service Award"=>"D/5 Civic Service Award",
					"Distinctive Communicator Award - Newsletter"=>"Distinctive Communicator Award - Newsletter",
					"Distinctive Communicator Award - Web Site"=>"Distinctive Communicator Award - Web Site",
					"Henry E. Sweet Award"=>"Henry E. Sweet Excellence Award",
					"Kenneth Smith Seamanship Award"=>"Kenneth Smith Seamanship Award",
					"Prince Henry Award"=>"Prince Henry Award",
					"USPS Civic Service Award"=>"USPS Civic Service Award",
					"Workboat Award"=>"Workboat Award",
					""=>"Select a standard award of enter new in textbox!");
	
	
	//$this->cols=$col_list;
}// constructer
//****************************************************************************
function addBlankAwardRecord(){
	$rec = $this->blank_record;
	foreach($this->types as $key=>$value){
		if ($value == 'datetime'){
			$rec[$key] = date("Y-m-d 19:00:00");
		}
		if ($value == 'int' or $value == 'smallint'){
			$rec[$key] = 0; 
		}
	}
	// $types = $this->types;
	// unset($rec['award_id']);
	$result = $this->add_record($rec);
	if (! $result ){
		while ($row = $result->fetch_row()) {
        	$ary[] = $row ;
    	}
	}
	$list = "max(award_id)";
	$rec = $this->search_partial_records($list,1);
	$rec = $this->get_record('award_id',$rec[0]['max(award_id)']);
	return $rec;
}
//*******************************************************************************
function getAwards($squad_no, $select=''){
	$query = "";
	if ($squad_no != "")
		$query .= "award_to_squadron = '$squad_no'";
	if ($select != '')
		$query .= " and ( $select ) ";
	$list = $this->search_records_in_order($query,'award_year DESC, award_name, award_place');
	return $list;
}
//****************************************************************************

}// class
//****************************************************************************
?>