<?php
/*
Copyright (C) March 2013, Joseph P. Gibson.

This program is free software to USPS Members developing software for USPS Squadrons or Districts: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program.  If not, see <http://www.gnu.org/licenses/>.

Owner: 	Joseph P. Gibson
		USPS District 5 Webmaster 
*/
require_once(JPATH_LIBRARIES."/usps/tableAccess.php");
class tableJobs extends USPStableAccess{
	// Generic routines to manage a table 
//********************************* Public Variables **********
//********************************* Private Variables *********
//private $manager ;
//*************************************************************
function __construct($database, $caller = ''){
		// Creates the variables to contain identity of data and tables 
	parent::__construct("jobs", $database, $caller);
		//$this->list_cols=$col_subset; 
		//$this->cols=$col_list;		
} // constructer
//*************************************************************
function add_member($code,$cert,$yr){
	// Check to see if this entry exists
	// If not, Creates a new table entry 
	// Check to see if this entry exists
	if ($this->search_record("jobcode='$code' and certificate='$cert' and year='$yr'"))
		return;
	$ary = array();
	$ary['jobcode']=$key;
	$ary['certificate']=$item;
	$ary['year']=$_POST['year'];
	$this->add_record($ary);
}
//*************************************************************
function check_d5_ship_store_chair($cert,$year){
global $exc;
	$select = "year='$year' and jobcode like '2___0'";
	$list = $this->search_records_in_order($select);
	foreach ($list as $j){
		if ($j['jobcode']=='25500' and $j['certificate'] == $cert)
			return true;
	}
	return false;
}
//*************************************************************
function check_d5_newsletter_editor($cert,$year){
global $exc;
	$select = "year='$year' and (jobcode = '25300' or jobcode = '25303')";
	$list = $this->search_records_in_order($select);
	foreach ($list as $j){
		if ($j['certificate'] == $cert)
			return true;
	}
	return false;
}
//*************************************************************
function check_permissions($type,$cert,$year,$squad_no='6243'){
global $exc;
$squad_no = sprintf("%04d",$squad_no);
// Global routine for permissions
// Called from within a private page to confirm the member is authorized 
// Webmaster had permission on all pages 
// Otherwise the $type parameter deterines the permission groups
// Returns true if authorized
	if ($exc->excom_member($cert,"webmaster",$year)) 
		return true;
	switch ($type){
		case 20000:		// Any D5 Bridge Officer  
			$list = $exc->excom_member($cert,'',$year);
			break;	
		case 30000:		// Any Squadron Bridge Officer 
			return $this->check_squadron_officer($cert,$squad_no,$year);
			break;
		case 25000:		// Member Secretary's Department
			break;
		case 23000; 	// D5 Educational Dept. Officer 
			break;
		case 33000:		// Any Squadron Educational Officer 
			break;
		default:
	}
	return false;
}
//*************************************************************
function check_squadron_commander($cert,$squad_no,$year){
global $exc;
	$list = $this->get_squadron_officer_list(sprintf("%04d",$squad_no),$year);
	foreach ($list as $j){
		if ($j['jobcode']=='31000' and $j['certificate'] == $cert)
			return true;
	}
	return false;
}
//*************************************************************
function check_squadron_officer($cert,$squad_no,$year){
global $exc;
// returns true if member is a squadron officer or webmaster
	$squad_no = sprintf("%04d",$squad_no);
	$list = $this->get_squadron_officer_list($squad_no,$year);
	if(! $list){
		log_it("Function get_squadron_officer_list returned empty.  squad = $squad_no, year = $year"); 
		return false;
	}
		
	foreach ($list as $j){
		if (strtolower($j['certificate']) == strtolower($cert))
			return true;
	}
	log_it("Certificate $cert is not an officer of squadron $quad_no");
	foreach ($list as $job){
		write_log_array($job);
	}
	return false;
}
//*************************************************************
function check_squadron_treasurer($cert,$squad_no,$year){
global $exc;
	$list = $this->get_squadron_officer_list($squad_no,$year);
	foreach ($list as $j){
		if ($j['jobcode']=='36000' and $j['certificate'] == $cert)
			return true;
	}
	return false;
}
//*************************************************************
function check_squadron_secretary($cert,$squad_no,$year){
global $exc;
	$list = $this->get_squadron_officer_list($squad_no,$year);
	foreach ($list as $j){
		if ($j['jobcode']=='35000' and $j['certificate'] == $cert)
			return true;
	}
	return false;
}
//*************************************************************
function check_squadron_roster_chair($cert,$squad_no,$year){
global $exc;
	$list = $this->get_squadron_officer_list(sprintf("%04d",$squad_no),$year);
	foreach ($list as $j){
		if ($j['jobcode']=='35650' and $j['certificate'] == $cert)
			return true;
	}
	return false;
}
//*************************************************************
function check_squadron_seo($cert,$squad_no,$year){
global $exc;
	$list = $this->get_squadron_officer_list($squad_no,$year);
	foreach ($list as $j){
		if ($j['jobcode']=='33000' and $j['certificate'] == $cert)
			return true;
	}
	return false;	
}
//*************************************************************
function check_squadron_it_chair($cert,$squad_no,$year){
global $exc;
	$list = $this->get_squadron_officer_list($squad_no,$year);
	foreach ($list as $j){
		if ($j['jobcode']=='35700' and $j['certificate'] == $cert){
			return true;
		}
	}
	return false;
}
//*************************************************************
function check_squadron_newsletter_editor($cert,$squad_no,$year){
global $exc;
	$list = $this->get_squadron_officer_list($squad_no,$year);
	foreach ($list as $j){
		if ($j['jobcode']=='35300' and $j['certificate'] == $cert)
			return true;
	}
	return false;
}
//*************************************************************
function check_squadron_ship_store_chair($cert,$squad_no,$year){
global $exc;
	$list = $this->get_squadron_officer_list($squad_no,$year);
	foreach ($list as $j){
		if ($j['jobcode']=='35500' and $j['certificate'] == $cert)
			return true;
	}
	return false;
}
//*************************************************************
function display_committee_table($code,$id,$class,$year,$width=0){
// Inserts a table into calling page 
// Parameter is committee code 
// Member Names formatted with e-mail link 
// Table positioned controlled by $id 
	if ($width == 0) $width = 500;
	echo "<div id='$id'>";
	echo "<table width='$width' border='0' cellspacing='0' cellpadding='0'>";
	$width1 = $width * .4;
	$width2 = $width * .6;
    echo "<colgroup><col style{width:$width1 px}><col style{width:$width2 px}></colgroup>";
	echo "<tr>";
	$this->display_committee($code,$class,true,$year);
	echo "</table>";
	echo "</div>";
}
//*************************************************************
function display_named_job($c,$class,$year){
global $mbr, $vhqab,  $exc;
	$codes = $vhqab->getJobcodesObject();
	if (is_array($c))
		$ary = $c; 
	else{
		// $c parameter is a jobcode.  We must get committee record
		$ary = $codes->get_record('jobcode',$c);	
	}
	$query = 'jobcode='.$ary['jobcode'].' ';
	$query .= "and year='$year'";
	$js = $this->search_records_in_order($query);
	foreach($js as $i => $pos){
		$m = $GLOBALS['mbr']->get_mbr_record($pos['certificate']);
		show_d5_hdr_row(strtoupper($ary['jdesc']),
				$GLOBALS['vhqab']->getMemberName($m['certificate'],TRUE),
				$class);
	}
}
//*************************************************************
function duplicate_jobs($code,$year,$next_year){
	// Finds all job entries for $year and duplicates for $next_year
	$query = "jobcode like '$code' ";
	$query .= " and year='$year'";
	$list = $this->search_records_in_order($query);
	if ($list)
		foreach($list as $row){
			$row['year']=$next_year;
			$this->add_record($row);
		}
}
//*************************************************************
function get_d5_job_holder_list($year){
global  $exc;
	$list = $this->get_members_with_d5_jobs($year);	
	foreach($list as $key=>$member){
		$cert = strtoupper($member['certificate']);
		$ary[$cert] = $str = get_person_name($member,0);
	}
	return $ary;
}
//*************************************************************
function get_excom_members_for_booklet($squad_no,$year){
global $vhqab, $sqds, $addr;
	$codes = $vhqab->getJobcodesObject();
	$query = "(jobcode like '3_000' or jobcode like '3_001')";
	$query .= " and squad_no='$squad_no'";
	$query .= " and year = '$year'";
	$rows = $this->search_records_in_order($query,'jobcode');
	$query = "jobcode = '31610' and squad_no='$squad_no'";
	$query .= " and year = '$year'";
	$ipcs = $this->search_records_in_order($query,'jobcode');
	if ($ipcs)
		foreach($ipcs as $ipc) 
			$rows[count($rows)] = $ipc;
	$members = array();
	foreach($rows as $i=>$row){
		$member = $vhqab->getD5Member($row['certificate']);
		if (! $member) continue;
		$member['full_name'] = $vhqab->getMemberNameAndRank($row['certificate']);
		$squad = $sqds->get_record('squad_no',$member['squad_no']);
		$member['squadron'] = $squad['squad_name'];	
		$desc = $codes->get_record('jobcode',$row['jobcode']);
		$member['excom_position']=strtoupper($desc['jdesc']);
		$members[count($members)]=$member;
	}
	return $members;
}
//*************************************************************
function get_job_holder($squad_no,$jobcode,$year){
	$squad_no = sprintf("%04d",$squad_no );
	$array = array();
	$search = "jobcode='$jobcode' and squad_no='$squad_no' and year='$year'";
	$j = $this->search_record($search);
	if (! $j) return false;
	return $j['certificate'];
}
//*************************************************************
function getCertificateOfJobHolder($squad_no,$jobcode,$year){
	// Return the certificate of the jobholder or supervisor
	$j = $this->get_job_holder($squad_no,$jobcode,$year);
	if ($j) return $j;
	$jc = $jobcode;
	$jc = substr($jc,0,2)."000";
	$j = $this->get_job_holder($squad_no,$jc,$year);
	if ($j) return $j;
	$j = $this->get_job_holder($squad_no,'31000',$year);
	if ($j) return $j;
	return false;
}
//*************************************************************
function getJobHolders($squad_no,$jobcode,$year){
	// Now only returns records from the jobs table...  
global $vhqab;
	$squad_no = sprintf("%04d",$squad_no);
	$rows = array();
	$search = "jobcode='$jobcode' and squad_no='$squad_no' and year='$year'";
	$js = $this->search_records_in_order($search);
	return $js;
	foreach($js as $j){
		if (is_array($j)){
			$rows[] = $j;
		}
	}
	return $rows;
}
//*************************************************************
function get_members_with_d5_jobs($year){
global $vhqab;
	$array = array();
	$select = "jobcode > '19999' and jobcode < '30000' and year='$year'";
	$rows = $this->search_distinct($select,'certificate');
	foreach ($rows as $row){
		$m = $vhqab->getd5Member($row['certificate']);
		$sx = $m['last_name'].$m['first_name'].$m['certificate']; 
		$array[$sx] = $m;
		//$array[count($array)] = $m;
	}
	ksort($array) ;
	return $array;
}
//*************************************************************
function get_squadron_commander($squad_no,$year){
	$list = $this->get_squadron_excom($squad_no,$year);
	foreach($list as $jc){
		if ($jc['jobcode']=='31000'){
			return $jc['certificate'];
		}
	}
	return 0;	
}
//*************************************************************
function get_squadron_ed_officer_certificate($squad_no,$year){
	$squad_no = sprintf("%04d",$squad_no);
	$search = "jobcode = '33000' ";
	$search .= "and squad_no = '$squad_no' ";
	$search .= "and year = '$year' ";
	$list = $this->search_record($search);
	if ($list) 
		return $list['certificate'];
	else	
		return false;
}
//*************************************************************
function get_squadron_excom($squad_no,$year){
	$squad_no = sprintf("%04d",$squad_no);
	$search = "(jobcode like '3_000' ";
	$search .= "or jobcode like '3_001' ";
	$search .= "or jobcode = '35700') ";
			// Members at Large
			// Membership Chair
			// Safety Chair
			// Cooperative Charting Chair
			// VSE Chair 
			// 
			
	$search .= "and squad_no = '$squad_no' ";
	$search .= "and year = '$year' ";
	$list = $this->search_records_in_order($search,'jobcode');
	return $list;
	
}
//*************************************************************
function get_squadron_job_holder_list($squad_no,$year){
	$squad_no = sprintf("%04d",$squad_no);
	$search = "(jobcode like '3____' ";
	$search .= ") ";
	$search .= "and squad_no = '$squad_no' ";
	$search .= "and year = '$year' ";
	$list = $this->search_records_in_order($search,'jobcode');
	return $list;
}
//*************************************************************
function get_squadron_officer_list($squad_no,$year){
	$squad_no = sprintf("%04d",$squad_no);
	$search = "(jobcode like '3_00_' ";
	$search .= "or jobcode = '32700' ";		// Vessel Examiner Chair
	$search .= "or jobcode = '34100' ";		// Chair Boating Activities
	$search .= "or jobcode = '34500' ";
	$search .= "or jobcode = '35300' ";
	$search .= "or jobcode = '35500' ";
	$search .= "or jobcode = '35650' ";
	$search .= "or jobcode = '35700' ";
	$search .= "or jobcode = '31610' ";		// Imediate Past Commander
	$search .= "or jobcode = '38040' ";
	$search .= ") ";
	$search .= "and squad_no = $squad_no ";
	$search .= "and year = '$year' ";
	$list = $this->search_records_in_order($search,'jobcode');
	return $list;
}
//*************************************************************
function filter_chair($str){
	$str = str_replace('CHAIRMAN, ','',$str);
	return $str;
}
//*************************************************************
function replace_district_excom_jobs($year){
$exc = $GLOBALS['exc'];
	// Deletes all district excom level job assignments from Jobs Table 
	// replaces with fresh data from excom table
	$query = "(jobcode like '2_00_' ";
	$query .= "and year = '$year') ";
	$query .= "or jobcode = '28070' ";
	$query .= "or jobcode = '21610' ";
	$this->delete_range($query);
	$rows = $exc->get_officers($year);
	foreach($rows as $row){
		$this->add_record($row);
	} 
}
//****************************************************
function update_committee($pst, $year){
	//  Store all committee data and return 
	// Delete existing committee jobs 
	if (isset($pst['jobcode_emeritus'])){
		$range = "jobcode='".$pst['jobcode_emeritus']."'";
		$range .= " and year='$year'";
		$this->delete_range($range);
	}
	if (isset($pst['jobcode_chair'])){
		$range = "jobcode='".$pst['jobcode_chair']."'";
		$range .= " and year='$year'";
		$this->delete_range($range);
	}
	if (isset($pst['jobcode_asst'])){
		$range = "jobcode='".$pst['jobcode_asst']."'";
		$range .= " and year='$year'";
		$this->delete_range($range);
	}
	if (isset($pst['jobcode_member'])){
		$range = "jobcode='".$pst['jobcode_member']."'";
		$range .= " and year='$year'";
		if (isset($pst['squad_no']))
			$range .= " and squad_no=".$pst['squad_no'];
		$this->delete_range($range);
	}
	foreach($pst as $key=>$value){
		$a = explode('_',$key);
		if ($a[0]=='cert'){
			if ($value != ""){
				$row=array();
				$row['jobcode']=$pst['jobcode_'.strtolower($a[1])];
				$row['certificate']=$value;
				$row['year']=$pst['year'];
				if (isset($pst['squad_no']))
					$row['squad_no'] = $pst['squad_no'];
				$this->add_record($row);
			}
		}
	}
}
//*************************************************************
function update_jobs($pst,$year){
	//	Cycles twice through the $pst array to find each job 
	//	assignment entry
	//		Array key is jobcode 
	//		Array value is certificate number 
	//	First pass is to delete existing job assignments
	//	Second pass is to add the new assignment 
		if (isset($pst['squad_no'])) //   and $pst['squad_no']!='6243')
			$squadron = $pst['squad_no'];
		else
			$squadron = ""; 
		foreach($pst as $key=>$value){
			switch (strtolower($key)){
				case 'year':
					$year = $pst[$key];
				case 'command':
				case 'update':
				case 'updating':
				case 'member_cert':
				case 'next':
				case 'committee_code':
				case 'department_code':
				case 'jobcode':
				case 'squad_no':
				case 'new_order':
				case 'cow_date':
					unset($pst[$key]);
					break;
				default;
			}
		}
		foreach($pst as $key=>$value){
			// First delete all occurances of jobcode 
			$x = explode('_',$key);
			// strip suffix from jobcode
			$k = $x[0]; 
			$query = "jobcode='$k'";
			if ($squadron != 6243)
				$query .= " and squad_no='$squadron'";
			$query .= " and year='$year'";
			$this->delete_range($query);
		}
		foreach($pst as $key=>$value){
			// only new job assignments left 
			$x = explode('_',$key);
			// strip suffix from jobcode
			$k = $x[0]; 
			if ($value == "") continue; 
			$row['year']=$year;
			$row['jobcode']=$k;
			$row['certificate']=$value; 
			if ($squadron != 6243)
				$row['squad_no']=$squadron;
			$this->add_record($row);
		}
}
} // End of class Table
?>