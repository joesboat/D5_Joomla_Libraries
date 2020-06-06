<?php
/*
 * @package		USPS Libraries 
 * @subpackage	tableD5VHQAB.php.
 * @purpose		Interface to USPSd5 and USPS.org database tables
 * @copyright	Copyright (C) 2015 Joseph P. Gibson. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt

This program is free software to USPS Members developing software for USPS Squadrons or Districts: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or any later version.
This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
You should have received a copy of the GNU General Public License along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
defined('_JEXEC') or die;
//defined('JPATH_PLATFORM') or die;
//require_once(JPATH_LIBRARIES."/usps/tableAccess.php");
require_once('includes/routines.php');
//jimport('usps.includes.routines');

require_once ("Joes_factory.php");
// include (JPATH_LIBRARIES.'/usps/tableVHQAB.php');
//*********************************************************
class USPSd5tableVHQAB {
var $Addresses;
var $Convert;
var $d5_squadrons;
var $D5mbr;
var $db;
var $dist;
var $exc;
var $hon;
var $jobs;
var $jobcodes;
var $Members;
var $rank;
var $squad;
var $year;
//*********************************************************
function __construct($dbConnect = 'local'){
	include(JPATH_LIBRARIES . '/USPSaccess/dbUSPS.php');		
	switch(strtolower(trim($dbConnect))){
		case "local":
			$this->db = $db_d5;
			break;
		case "remote":
			$this->db = $db_d5_remote;
			break;
		default:
			$this->db = $db_d5;
	}	
} // constructer
//*********************************************************
function close(){
	if ($this->Addresses) $this->Addresses->close();
	if ($this->Members)  $this->Members->close();
	if ($this->d5_squadrons)  $this->d5_squadrons->close();
	if ($this->exc)  $this->exc->close();
	if ($this->hon) $this->hon->close();
	if ($this->jobs)  $this->jobs->close();
	if ($this->jobcodes) $this->jobcodes->close();
}
//*********************************************************
function form_district_phrase($squad_no){
	$this->loadSquadron($squad_no);
	$dstno = $this->squad['dist'];
	$distno = sprintf("%02d", $dstno);
	$this->loadDistrict($distno);
	$str = "A squadron of District $dstno";
	return $str;
}
//*********************************************************
function form_squadron_name($squad_no){
	$this->loadSquadron($squad_no);
	$str = $this->squad['squad_short_name']; 
	if ($this->squad['SP'] != ''){
		$str .= " Sail and";
	}
	$str .= " Power Squadron";
	return $str;
}
//*************************************************************
function get_all_squadron_jobs($jobcode,$year){
	$this->loadTable("d5_squadrons");
	$this->loadTable("jobs");
	$rows=array();
	$s_list = $this->d5_squadrons->search_records_in_order('','squad_name');
	foreach($s_list as $a_squad){
		if ($a_squad['status'] != '') continue;
		$cert = $this->jobs->getCertificateOfJobHolder($a_squad['squad_no'],$jobcode,$year);
		if (! $cert) continue;
		$a_squad[$jobcode] = $cert;
		$rows[] = $a_squad;
	}
	return $rows;
}
//*********************************************************
function getCommitteeList($group){
	$this->loadDBjobcodes();
	// get list and look for $code 
	$select = "committee='1' and jobcode like '$group'";
	$array = $this->jobcodes->search_records_in_order($select,'jdesc');
	$ary=array();
	$ary[0]='NONE';
	foreach($array as $key=>$value){
		$ary[$value['committee_code']] = $value['jdesc'];
	}
	//$ary[$key + 1] = $value[''];
	return $ary;
}
//*********************************************************
function getDepartmentJobAssignments($dept, $year, $link=FALSE, $squad_no=''){
$this->loadDBd5_excom();
$this->loadDBjobs();
$this->loadDBjobcodes();
	// Create an associative array structure to identify and staff all dept. jobs 
	// 1st level index is:
	// 		0 index to the department name 
	//		Jobcode index to the Job, group or committee
	// Each jobcode entry will be an array with the following elements
	//		title - the title of the Job
	//		type - a number found in the jobcodes committee field - designates Job, Group or Committee
	//		mbrs, asst, chrs or emeritus as indexes to an array of jobholder names 
	//		the index to each jobholder name is the members certificate
	
	// First identify the department and find all department jobs  		
	$excom = $this->exc->search_record("jobcode=$dept and year='$year'");
	if ($excom){
		// It's a normal department 
		$this->loadD5Member($excom['certificate']);
		$officer = $this->D5mbr;	
		// Convert job title to department name 
		$list[0]['title'] = build_department_name($excom);
		$list[0]['jobcode'] = $dept;
		$list[0]['addpage'] = TRUE;
		$list[$dept]['title'] = $excom['excom_position'];
		$list[$dept]['name'] = getMemberNameAndGrade($this->D5mbr,$link);
		$list[$dept]['certificate'] = $this->D5mbr['certificate'];
		// Setup Assistant Officer  
		$asst_dept = $dept + 1;
		$query = "jobcode=$asst_dept and year='$year'";
		$excom = $this->exc->search_record($query);
		if ($excom){
			$this->loadD5Member($excom['certificate']);
			$asst_officer = $this->D5mbr;	
			$list[$dept+1]['title'] = $excom['excom_position'];
			$list[$dept+1]['name'] = getMemberNameAndGrade($this->D5mbr,$link);
			$list[$dept+1]['certificate'] = $this->D5mbr['certificate'];
		}
	} else {
		// It's a set of committees (general or standing)
		$list[$dept]['title'] = '';
		$list[$dept]['name'] = '';		
	}
	$query = "department = '$dept' "; // and committee > '0'";
	$query .= "and committee != '3' ";
	// Obtain a list of all department level job names 
	$cmtes = $this->jobcodes->search_records_in_order($query,'display_order');

	if (!$cmtes) return;
	
	// Now call a separate function to populate job assignments  
	$list2 = $this->populateJobAssignments($cmtes,$year,$squad_no,$link);
	return  array_replace($list,$list2);
}	
function populateGroupOrCommittee($jobcode,$year,$squad_no='',$link=FALSE){
$this->loadDBjobcodes();
	$query = "jobcode = '$jobcode' "; // and committee > '0'";
	$query .= "and committee != '3' ";
	// Obtain a list of all department level job names 
	$cmtes = $this->jobcodes->search_records_in_order($query,'display_order');
	return $this->populateJobAssignments($cmtes,$year,$squad_no,$link);
}
function populateJobAssignments($cmtes,$year,$squad_no='',$link=FALSE){
$this->loadDBjobs();
	// $cmtes is is an array of the jobcodes table records  
	// returns an array containing data on a job assignment
	// Each job assignment is an array where: 
	// 		The array index is a jobcode.  The index points to an array with the following elements:
	//			title - the title of the Job
	//			type - a number found in the jobcodes committee field - designates Job, Group or Committee
	//			mbrs, asst, chrs or emeritus as indexes to an array of jobholder names 
	//				the index to each jobholder name is the members certificate	
	
	foreach($cmtes as $c){
		if (is_array($c))
			$ary = $c; 
		else{
			// $c parameter is a jobcode.  We must get committee record
			$ary = $codes->get_record('jobcode',$c);	
		}
		switch($c['committee']){
			case 2:			// This is a group - No Chairperson
				$list[$ary['jobcode']] = $this->getGroupMembers($ary,$year,$link);
				break;
			case 3:
			case 4:
				continue 2;
				break;
			case 0:			// Named Job 
				if ($c['committee_code']!=0)
					continue 2;
				if ($c['skip'] == 1)
					continue 2;
				$display_name = trim(str_replace('Chairman,','',$c['jdesc']));
				$display_name = strtoupper(trim(str_replace('Chair,','',$display_name)));				
				// Named Jobs not associated with a committee 			
				$mbrs = $this->getJobAssignments($c['jobcode'],$year);
				if (count($mbrs)>0){
					$list[$c['jobcode']]['title'] = strtoupper($display_name);
					foreach ($mbrs as $m){
						$list[$c['jobcode']]['named'][] = getMemberNameAndGrade($m,$link);	
					}
					$list[$c['jobcode']]['type'] = 0;
				}				
				break;
			default:	// We are getting data for the committee
				$list[$ary['jobcode']] = $this->getCommitteeMembers($ary,$squad_no,$year,$link);
		}
	}
	return $list;
}
function getGroupMembers($c, $year, $link){
	$display_name = trim(str_replace('Chairman,','',$c['jdesc']));
	$display_name = strtoupper(trim(str_replace('Chair,','',$display_name)));
	$mbrs = $this->getJobAssignments($c['jobcode'],$year);
	$x['title'] = strtoupper($display_name);
	$x['type'] = 2;
	foreach ($mbrs as $m){
		$x['mbrs'][] = getMemberNameAndGrade($m,$link);		
	}
	return $x;
}
function getCommitteeMembers($c, $squad_no, $year, $link){
	$display_name = trim(str_replace('Chairman,','',$c['jdesc']));
	$display_name = strtoupper(trim(str_replace('Chair,','',$display_name)));
	$query="jobcode=".$c['jobcode']." and year='$year'";
	// Show committee chairs
	$chr = $this->getJobAssignments($c['jobcode'],$year,$squad_no,$link);
	$asst = $this->getJobAssignments($c['jobcode']+1,$year,$squad_no,$link);
	$mbrs = $this->getJobAssignments($c['jobcode']+2,$year,$squad_no,$link);
	$emeritus = $this->getJobAssignments($c['jobcode']+9,$year,$squad_no,$link);
	$named = $this->get_named_job_assignments($c['jobcode'],false,$year,$link);
	$array['type'] = 1;
	$array['title'] = strtoupper($display_name);
	$array['Chair'] = $array['Chair Emeritus'] = $array['asst'] = $array['mbrs'] = $array['named'] = array();
	if (count($chr)==0 and count($asst)==0 and count($mbrs)==0 and count($named)==0)
		return $array;
	foreach ($chr as $m){
		$array['Chair'][$m['certificate']] = getMemberNameAndGrade($m,$link);
	}
	// show emeritus
	foreach ($emeritus as $m)
		$array['emeritus'][$m['certificate']] = getMemberNameAndGrade($m,$link);	
	// Show committee assistant
	foreach ($asst as $m)
		$array['asst'][$m['certificate']] = getMemberNameAndGrade($m,$link);
	// Show members 
	foreach ($mbrs as $m)
		$array['mbrs'][$m['certificate']] = getMemberNameAndGrade($m,$link);
	// Show Named Jobs
	foreach($named as $ix=>$n)
		$array['named'][$ix] = $n;
	return $array ;
}
//*********************************************************
function getConvertObject(){
	$this->loadTable("Convert");
	return $this->Convert;
}
//*********************************************************
function getD5AddressesObject(){
	$this->loadTable("Addresses");
	return $this->Addresses;
}
//*********************************************************
function getD5ExcomMembers($year){
	$this->loadDBd5_excom();
	$rows = $this->exc->get_officers($year);
	foreach($rows as &$row){
		$array[] = $this->getD5Member($row['certificate']);
	}
	return $array;
}
//*********************************************************
function getD5Member($certificate){
	$this->loadD5Member($certificate);
	return $this->D5mbr;
}
//*********************************************************
function get_d5_member_count(){
	$this->loadTable("Members");
	return $this->Members->get_d5_member_count();
}
//*********************************************************
function getD5Members(){
	$this->loadTable("Members");
	return $this->Members->get_records();
}
//*********************************************************
function getD5MembersPrimary(){
// Gets members who are single or primary in a family unit	
	$this->loadTable("Members");
	$select = '';
	$primary = array('AC10','AC15','AC20','AC25','AC50','AC55','AC70','AC75');
	foreach($primary as $str){
		$select .= "mbrstatus='$str' ";
		if ($str != 'AC75') $select .= "or ";
	}
	$list = $this->Members->search_members($select);
	return $list;
}
//*********************************************************
function getD5MembersObject(){
	$this->loadTable("Members");
	return $this->Members;
}
//*********************************************************
function getD5SquadronNewsletterFileName($squad_no){
	$this->loadSquadron($squad_no);
	return $this->squad['newsletter_file_name'];
}
//*********************************************************
function getD5SquadronNewsletterName($squad_no){
	$this->loadSquadron($squad_no);
	return $this->squad['newsletter_name'];
}
//*********************************************************
function getSquadronState($squad_no){
	$this->loadSquadron($squad_no);
	return $this->squad['state'];	
}
//*********************************************************
function getD5Squadrons(){
	$this->loadTable("d5_squadrons");
	$list = $this->d5_squadrons->get_squadrons();
	return $list;
}
//*********************************************************
function getDistrictJobs($certno,$year){
	$this->loadTable("jobs");
	$this->loadTable("jobcodes");
	$query = "certificate='$certno' and jobcode like '2%' and year = '$year'";
	$jobs = $this->jobs->search_records_in_order($query,"jobcode" );
	$list = array();
	foreach($jobs as $job){
		$jobcode = $job['jobcode'];
		$desc = $this->jobcodes->get_record('jobcode',$jobcode);
		if ($desc['jdesc'] == '') continue;
		$list[$jobcode] = abreviate_job_description($desc['jdesc']);	
	}
	return $list;
}
//*********************************************************
function getDistrictMembers($distno){
	$this->loadTable("Members");
	return $this->Members->get_mbr_records();
}
//*********************************************************
function getD5SquadronList($link = FALSE){
	$this->loadTable("d5_squadrons");
	return $this->d5_squadrons->get_new_squadron_list($link);
}
//*********************************************************
function getDistrictNumber($certno){
	return 5;
	$this->loadD5Member($certno);
	$dnum = sprintf("%02d",$this->mbr['distno']);
	$squad = $this->websites->get_record('Name',$dnum);
	$name = "District ". $this->mbr['distno'] ;
	$url = "http://".$squad['url'];
	return "<a href='$url' target='_blank'>$name</a>";
}
//*********************************************************
function get_district_url($squad_no){
	$this->loadSquadron($squad_no);
	$dstno = $this->squad['dist'];
	$this->loadDistrict($dstno);
	$url = "http://".$this->dist['url'];
	return $url;
}
//*********************************************************
function getExcomMember($jobcode,$year){
$this->loadDBd5_excom();
	// Queries excom to find cert. number for a jobcode and then 
	// obtains that members record.
	$query = "jobcode='$jobcode' and year='$year'" ;
	$ex_row = $this->exc->search_record($query);
	//$ex_row = $this->get_record("jobcode",$jobcode);
	$cert = $ex_row['certificate'];
	$row = $this->getD5Member($cert);
	return $row ; 
}
//*********************************************************
function getExcomMemberName($jobcode ,$year, $link=FALSE){
	$row = $this->getExcomMember($jobcode,$year);
	return getMemberNameAndGrade($row,$link);
}
//*********************************************************
function getExcomObject(){
	$this->loadDBd5_excom();
	return $this->exc;
}
//*************************************************************
function getJobAssignments($code, $year, $squad_no = '',$check_emeritus=FALSE){
	$this->loadTable("jobs");
	// Called from show_committee & display_committee
	// Obtains member records assigned to $code 
	$rows=array();
	if ($check_emeritus){
		$ecode=$code + 9;
		$select = "(jobcode='$code' or jobcode='$ecode')";
	} else 
		$select = "jobcode='$code'";
	if ($year != '') 
		$select = "$select and year='$year'";
	if ($squad_no != '' and $squad_no != 6243){
		$squad_no = sprintf("%04d",$squad_no);
		$select = "$select and squad_no='$squad_no'";
	}
	$list = $this->jobs->search_records_in_order($select);
	foreach($list as $j){
		$m = $this->getD5Member($j['certificate']);
		if (is_array($m)){
			$m = array_merge($j,$m);
			$rows[$m["last_name"].$m["first_name"]] = $m;	
		}
	}
	ksort($rows);
	$sss = array();
	foreach($rows as $x){
		$sss[] = $x;
	}
	return $sss;
}
//*************************************************************
function getJobAssignmentNames($code, $year, $link = FALSE){
	$names = array();
	$list = $this->getJobAssignments($code, $year);
	foreach($list as $key=>$mbr){
		$names[] = getMemberNameAndGrade($mbr, $link);
	}
	return $names;
}
//*************************************************************
function getUSPSJobAssignmentData($code, $year, $link = FALSE){
	$names = array();
	$this->loadDBjobs();
	$this->loadDBjobcodes(); 
	$list = $this->getJobAssignments($code, $year);
	foreach($list as $key=>$mbr){
		$member_name = getMemberNameAndGrade($mbr, $link);
		$this->loadSquadron($mbr['squad_no']);
		$squadron_name = get_short_name($this->squad);
		$query = 	"certificate='".$mbr['certificate']."'";
		$query .= 	"and (jobcode like '1%0' or jobcode like '1%1' ) ";
		$query .= 	"and year ='$year' ";
		$jbs = $this->jobs->search_records_in_order($query,'jobcode');
//		foreach($jbs as $job){
		if (count($jbs) == 0) continue;
		$job = $jbs[0];	
			$jc = $job['jobcode'];
			$jd = $this->jobcodes->get_record('jobcode',$job['jobcode']);
			$j['name']= $member_name;
			$j['squadron_name'] = $squadron_name;
			$j['job_name'] = abreviate_job_description($jd['jdesc']);
			$names[] = $j;
		
	}
	return $names;
}
//*********************************************************
public function getJobsObject(){
	// Returns the jobs object 
	$this->loadTable("jobs");
	return $this->jobs; 
}
//*********************************************************
public function getJobcodesObject(){
	// Returns the jobs object 
	$this->loadDBJobcodes();
	return $this->jobcodes; 
}
//*********************************************************
function get_jobs($certno){
	$ary = array("USPS Member");
	$this->loadTable("d5_squadrons");
	$year = $this->getSquadronDisplayYear(); 
	// Check for Squadron Officer 
	if ($this->isSquadronOfficer($certno,$year)){
		$ary[] = "Squadron Officer";
	}
	if ($this->isSEO($certno,$year)){
	 	$ary[] = "SEO";
	}
	// Check for Editor
	if ($this->isEditor($certno,$year)){
		$ary[] = "Newsletter Editors";
	}
	// Check for District Officer 
	if ($this->isDistrictOfficer($certno,$year)) {
		$ary[] = "District Officer";
	}
	if ($this->isDistrictWebmaster($certno,$year)) 
	{
		$ary[] = "Webmaster";
	}
	// Check for National Officer 
	return $ary;
}
//*********************************************************
function getLinkToDistrictMembers($distno=5, $fields='*'){
	$select = " active = '1' ";
	if ($this->loadTable("Members")){
		//return $this->Members->get_records_in_order('','','last_name , first_name');
		$link = $this->Members->select_partial_records_in_order($fields, $select = "", 'last_name , first_name');
		return $link ;		
	}
}
//*********************************************************
function getLinkToSquadronMembers($sq_number, $fields){
	$select = " active = '1' ";
	if ($this->loadTable("Members")){
		$select .= " and squad_no = '$sq_number'";
		$link = $this->Members->select_partial_records_in_order($fields, $select, 'last_name , first_name');
		//$link = $this->Members->select_members_and_associates($sq_number);
		return $link ;		
	}
}
//*********************************************************
function getMbrDdList($id, $squad_no='', $hidden = '', $cert, $title=''){
	// Generates a html select element with a list of usps members 
	// $id will be placed in html id and name attributes
	$this->loadTable("Members");
	$query = '';
	$cert = strtolower($cert);
// Get an associative array of member names where each record contains
//		certificate, first_name, last_name
	$list = " certificate, first_name, last_name, nickname, nn_prf "; 
	if ($squad_no != '')
		$query = "squad_no='$squad_no'" ; 	
	$mbrs = $this->Members->search_partial_records_in_order($list, $query, 'last_name');	
	$sel = '';
	if ($cert == '') $sel = 'selected' ;
	$options = "<option value='' $sel >Select from list.</option>" ;
	foreach($mbrs as $mbr){
		$sel = '';
		$certificate = $mbr['certificate'];
		$name = get_person_name($mbr);
		if (strtolower($mbr['certificate']) == $cert) {
			$sel = 'selected' ;
		}
		$options .= "<option $sel value='$certificate'>$name</option>" ; 
	}
	$str = "<select id='$id' name='$id' $hidden class='sel' width='100' title='$title'>" ;
	$str .= "$options";
	$str .= "</select>";
	return $str;
}
//*********************************************************
function getMembersBoatData($certno){
	$this->loadD5Member($certno);
	$row['boat_name'] = $this->D5mbr['boat_name'];
	$row['home_port'] = $this->D5mbr['home_port'];
	$row['boat_type'] =$this->D5mbr['boat_type'];
	$row['mmsi'] =$this->D5mbr['mmsi'];
	return $row;
}
//*********************************************************
function getMembersWithGmailAccounts(){
	$this->loadTable("Members");
	$query = "email like '%gmail%'";
	$list = $this->Members->search_members($query);
	return $list;
}
//*********************************************************
function getMembersWithD5Jobs($year){
	$list = 'jobs.certificate, first_name, last_name, jobs.jobcode, jobcodes.jdesc, d5_members.email, d5_members.telephone, d5_members.cell_phone, spouse,d5_addresses.address_1, d5_addresses.address_2, d5_addresses.city, d5_addresses.state, d5_addresses.zip_code, d5_members.squad_no, d5_members.nickname, d5_members.nn_prf, d5_members.grade, d5_members.hq_rank      ';
	$join = "INNER JOIN d5_members ON jobs.certificate = d5_members.certificate 
			INNER JOIN d5_addresses on d5_members.address_id = d5_addresses.address_id
			INNER JOIN jobcodes on jobs.jobcode = jobcodes.jobcode 
			";
	$where = "jobs.jobcode < 30000 and year=$year and jobcodes.skip != 1 and d5_members.active=1";
	$order = 'last_name, first_name, jobs.jobcode';
	$rows = $this->jobs->search_partial_with_join($list,$where,$order,$join);
	return $rows;
}
//*********************************************************
function getMembersWithJobs($no,$year){
$this->loadTable("jobs");
$this->loadDBjobcodes();
$this->loadTable("Members");
$this->loadTable("Addresses");
$this->loadDBd5_hon();
$pstjs = array(array(
				'certificate'=>'',
				'jdesc'=>'Past D5 Commanders',
				'jobcode'=>'21600', 
				'org'=>'',
				'squad_no'=>'6243',
				'year'=>'2017' ));
	$count = $count1 = 0;
	if ($no == ""){
		$use_honorary = true;
		// It's for all D5 Members so get honorary members
		$result = $this->Members->select_members('');
		$list = "certificate,last_name,first_name,nickname,nn_prf,spouse,spo_cert,rank,hq_rank,sq_rank,grade,address_1,address_2,city,state,zip_code,telephone,cell_phone,email,squad_no"; 
		$honorary = $this->hon->search_partial_records_in_order($list,'','last_name, first_name');
	} else {
		$use_honorary = false;
		$result = $this->Members->select_members_and_associates($no);
	}
	$cnt = 0;
	$rows = array();
	while ($row = $this->Members->get_next_record($result)){
		$count ++;
		$select = "certificate = '".$row['certificate']."'";
		$select .= " and year='$year'";
		$select .= " and jobcode > 0 ";
		$select .= " and jobcode < '30000' ";
		$js = $this->jobs->search_records_in_order($select,'jobcode');
		if ((count($js) == 0) and ($no == '')){
			continue;
		}
		if ($row['certificate']=='E218020'){
			foreach ($js as $jjjjj){
				$xxxx = $jjjjj ;				
			}
		}
		$x = count($js);
		for ($i=count($js);$i--;$i<0){
			$cde = $js[$i]['jobcode'];	
			$org = substr($cde,0,1);
			switch ($org){
			case 3:
				if ($cde != 31000 and $no==''){
					unset($js[$i]);
					continue 2;
				}
				$job = $this->jobcodes->get_record('jobcode',$js[$i]['jobcode']);
				$js[$i]['jdesc'] = abreviate_job_description($job['jdesc']);
				//$js[$i]['jdesc'] = "Commander";
				$js[$i]['org'] = 'SQ';
				break ;
			default:
				if ($no != ''){
					unset($js[$i]);
					$k = 0;
					$ks = array();
					foreach($js as $jjj){
						$ks[$k]=$jjj;
						$k++;
					}
					$js = $ks;
					continue 2;
				}
				switch ($org){
				case 1:
					$js[$i]['org'] = 'USPS ';
					break;
				case 2:
					$js[$i]['org'] = '';
					break;
				default:
					$yyy = array();
					$yyy = $js[$i];
				}	
				$job = $this->jobcodes->get_record('jobcode',$js[$i]['jobcode']);
				$js[$i]['jdesc'] = abreviate_job_description($job['jdesc']);
			}
		}
		if (count($js)==0 and $no==''){
			continue;
		}
		$row['js']=$js;
		$count1++;
		$row['count']= 6;
		$row['count'] = max(count($js),$row['count']);
		$row = $this->Addresses->get_and_add_member_address($row);
		if ($use_honorary and isset($honorary[0])){
			if (($honorary[0]['last_name']) < $row['last_name']){
				// $js['squad_no'] = $honorary[0]['squad_no'];
				$js['certificate'] = $honorary[0]['certificate'];
				$honorary[0]['js'] = $pstjs;
				$honorary[0]['count'] = 6;
				$rows[] = $honorary[0];
				unset($honorary[0]);
				$honorary = array_merge($honorary);
			}
		}
		if (($xx = $count % 10) == 0){
			$xyz = $xx;
		}
		$rows[] = $row;
	}
	return $rows;
}
//*********************************************************
function getMemberAddress($certno){
        $this->loadD5Member($certno);
        return $this->D5mbr['address_1'] . ' ' . $this->D5mbr['address_2'];
}
//*********************************************************
function getMemberBlank(){
	$this->loadTable("Members"); 
	$this->loadTable("Addresses");
	return array_merge($this->Addresses->blank_record,$this->Members->blank_record);
}
//*********************************************************
function getMemberCellPhone($certno, $c=false){
    $this->loadD5Member($certno);
    $str = $this->D5mbr['cell_phone'];
    if ($c and $str != '') $str = "C: " . $str ;
    return $str;
}
//*********************************************************
function getMemberCityStateZip($certno){
	$this->loadD5Member($certno);
	return $this->D5mbr['city'].', '.$this->D5mbr['state'].' '.$this->D5mbr['zip_code'];
}
//*********************************************************
function getMemberEmail($certno){
	if ($certno == '') return "";
    if ($this->loadD5Member($certno))
        return $this->D5mbr['email'];
}
//*********************************************************
function getMemberField($certno,$field){
	if ($certno == '') return "";
    if ($this->loadD5Member($certno))
        return $this->D5mbr[$field];
}
//*********************************************************
function getMemberFromEmail($email){
	if ($this->D5mbr['email'] == $email)
		return $this->D5mbr; 
	$this->loadDBD5_members();
	$mbr = $this->Members->get_mbr_record_by_email($email);
	if (! $mbr){
		return false;
	}
	$this->D5mbr = $mbr;
	return $this->D5mbr;
}
//*********************************************************
function getMemberName($certno, $link = false){
    if ($certno == '') return "";
    $this->loadD5Member($certno);
    if ($link)
        return get_person_name_with_link($this->D5mbr);
    else
    	return get_person_name($this->D5mbr);
}
//*********************************************************
function getMemberNameAndGrade($certno, $link = false){
    if ($certno == '') return "";
    if (! $this->loadD5Member($certno)) return "";
    if ($link )
    	$mem = get_person_name_with_link($this->D5mbr);
    else
    	$mem = get_person_name($this->D5mbr);
	$mem .= getGradeBOC($this->D5mbr);
	return $mem;
}
//*********************************************************
function getMemberNameAndRank($certno, $link = false){
    if ($certno == '') return "";
    if (! $this->loadD5Member($certno)){
		return "";	
	}
    if (! isset($this->D5mbr['rank'])){
		$sss = $this->D5mbr['rank'];
	}
    $rank 		= $this->D5mbr['rank'];
    $grade    = getGradeBOC($this->D5mbr);
    $email    = $this->D5mbr['email'];
    if ($link)
        $name = get_person_name_with_link($this->D5mbr);
    else
        $name = get_person_name($this->D5mbr);
    // return "$rank $name$grade";
    return "$name$grade";
}
//*********************************************************
function getMemberPhone($certno,$r = false){
        $this->loadD5Member($certno);
        $str = $this->D5mbr['telephone'];
        if ($r and $str != '') $str = "R: " . $str ;
        return $str;
}
//*********************************************************
function getMemberSpouse($certno){
        $this->loadD5Member($certno);
        return $this->D5mbr['spouse'];
}
//*********************************************************
function getMembersWithGrade($grade,$squad_no){
	$select = "grade='$grade' and squad_no='$squad_no' ";
	return $this->Members->search_records_in_order($select);
}
//*********************************************************
function get_named_job_assignments($c,$email,$year){
$this->loadDBjobcodes();
$this->loadTable("Members");
$this->loadTable("Addresses");
$this->loadDBd5_excom();
// Evaluates the committee code to find associated named jobs 
// Identifies the asigned member and obtains the member record 
// builds and returns a list of named jobs and assignments
// if $committee code provided restrict search to this committee
// otherwise list all named jobs 
// jobs listed in alphetical order 
	$ms = array();
	$s = "committee_code='$c'";
	$jcs = $this->jobcodes->search_records_in_order($s,'jdesc');
	//$j = $codes->get_records('committee_code',$ary['jobcode']);
	foreach ($jcs as $ix=>$p){
		if (($p['jobcode']!=$c) and ($p['jobcode']!=($c+1)) and ($p['jobcode']!=($c+2))){
			$this->loadDBd5_excom();
			$this->loadTable("jobs");
			$query = "jobcode='".$p['jobcode']."' ";
			$query .= " and year='$year'";
			$j = $this->jobs->search_records_in_order($query,'');
			foreach($j as $x){
				$r = $this->getD5Member($x['certificate']);
				$m = array();
				$m[0]=$p['jdesc'];
				$m[1]=$this->getMemberName($r['certificate']);
				$ms[$x['certificate']]=$m;
			}
		}
	}
	return $ms; 
}
//*********************************************************
function getNationalJobs($certno,$year){
	$this->loadTable("jobs");
	$this->loadTable("jobcodes");
	$query = "certificate='$certno' and jobcode like '1%' and year = '$year'";
	$jobs = $this->jobs->search_records_in_order($query,"jobcode" );
	$list = array();
	foreach($jobs as $job){
		$jobcode = $job['jobcode'];
		$query = "jobcode='$jobcode' and skip != 1";
		$desc = $this->jobcodes->search_record($query);
		if ($desc == '' or count($desc)==0) continue;
		if ($desc['jdesc'] == '') continue;
		$list[$jobcode] = abreviate_job_description($desc['jdesc']);	
	}
	return $list;
}
//*********************************************************
function getRankObject(){
	$this->loadDBrank();
	return $this->rank;
}
//*************************************************************
function getSquadronBurgeeFileName($squad_no){
	$this->loadSquadron($squad_no);
	return $this->squad['burgee_file_name'];
}
//*************************************************************
function getSquadronDisplayYear($squad_no='6243'){
	$this->loadSquadron($squad_no);
	// Not used by booklet routines
	// Field cow_date determines which year to to return 
	// If year field in cow_date < current year return prior year 
	$cow = strtotime($this->squad["cow_date"]);
	$now = time();
	if (date("Y",strtotime($this->squad["cow_date"])) < date("Y")){
		return date("Y") - 1;
	}
	// Else if current date > cow_date return current year
	if (time() > strtotime($this->squad["cow_date"])){
		return date("Y");
	}
	// Otherwise return prior year
	return date("Y") - 1;
}
//*********************************************************
function getSquadronJobs($certno,$year){
	$this->loadTable("jobs");
	$this->loadTable("jobcodes");
	$query = "certificate='$certno' and jobcode like '3%' and year = '$year'";
	$jobs = $this->jobs->search_records_in_order($query,"jobcode" );
	$list = array();
	foreach($jobs as $job){
		$jobcode = $job['jobcode'];
		$desc = $this->jobcodes->get_record('jobcode',$jobcode);
		if ($desc['jdesc'] == '') continue;
		$list[$jobcode] = abreviate_job_description($desc['jdesc']);	
	}
	return $list;
}
//*************************************************************
function getSquadronJobsForList($jc,$squad_no,$year){
	// get each valid assignment for top level officer and committee list
	// always returns an array which may contain only one array entry 
	$this->loadTable("jobs");
	$array = $jc;
	$description = $jc['jdesc'];
	$rows = array();
	switch ($jc['committee']){
		case 0:
		case 1:	
			$js = $this->jobs->getJobHolders(sprintf("%04d",$squad_no),$jc['jobcode'], $year);
			return $js ;
			if (count($js) > 0){
				foreach($js as $j){
					if (is_array($j)){
						$j['jc'] = $jc ;
						$j['cert'] = $j['certificate'];
						$rows[] = $j;			
					}			
				}
			}
			break;			
		case 2:
		case 3:
			//$mb = $this->Members->blank_record;
			//$rows[count($rows)] = array_merge($mb,$jc);
			//$rows[] = $jc;
			break;
	}
	if (count($rows)==0){
		$rows[] = $jc;
	}	
	return $rows;
}
//*********************************************************
function getSquadronLatAndLon($squad_no){
	//returns an array
	$this->loadSquadron($squad_no);
	return array('slat'=>$this->squad['slat'],'slon'=>$this->squad['slon']);
}
//*********************************************************
function getSquadronMembers($squad_no){
	$this->loadTable("Members");
	return $this->Members->get_mbr_records('squad_no',$squad_no);
}
//*********************************************************
function getSquadronMembersPrimary($squad_no){
	$this->loadTable("Members");
	$select = "squad_no='$squad_no' and ( ";
	$primary = array('AC10','AC15','AC20','AC25','AC50','AC55','AC70','AC75');
	foreach($primary as $str){
		$select .= "mbrstatus='$str' ";
		if ($str != 'AC75') $select .= "or ";
	}
	$select .= ")";
	$list = $this->Members->search_members($select);
	return $list;
}
//*********************************************************
function getSquadronObject(){
	$this->loadTable("d5_squadrons");
	return $this->d5_squadrons;
}
//*********************************************************
function getSquadronPublicContact($squad_no, $year){
	/* 
	Identify the Public Contact:  Search jobcodes in the order ode 35610, 31000.  
	Return the certificate number of the first assignment found.  
	*/
	$this->loadTable("jobs");
	if ($certificate = $this->jobs->get_job_holder($squad_no,'35610',$year)){
		return $certificate; 
	} else {
		return $this->jobs->get_squadron_commander($squad_no,$year);	
	}	
    return false;
}
//*********************************************************
function get_squadron_state($squad_no){
	$this->loadSquadron($squad_no);
	return $this->squad['state'];	
}
//*********************************************************
function getSquadronCOW($squad_no){
	$this->loadTable("d5_squadrons");
	$this->loadSquadron($squad_no);
	return $this->squad['cow_date'];
}
//*********************************************************
function getSquadronName($squad_no, $link=false){
	$this->loadTable("d5_squadrons");
	$this->loadSquadron($squad_no);
	return $this->d5_squadrons->getSquadronName($squad_no,$link);
}
//*********************************************************
function getSquadronShortName($squad_no, $link=false){
	$this->loadTable("d5_squadrons");
	return $this->d5_squadrons->getSquadShortName($squad_no,$link);
}
//*********************************************************
function getSquadronNameFromMember($certno){
        $this->loadD5Member($certno);
        return $this->getSquadronName($this->D5mbr['squad_no']);
}
//*********************************************************
function getSquadronNameWithLink($squad_no){
	$this->loadSquadron($squad_no);
	$name = $this->d5_squadrons->getSquadronName($squad_no);
	$url = "http://".$this->squad['url'];
	return "<a href='$url' target='_blank'>$name</a>";	
}
//*********************************************************
function getSquadNumber($certno){
    $this->loadD5Member($certno);
    $no = sprintf("%04d", $this->D5mbr['squad_no']);
    return $no;
}
//*********************************************************
function getSquadronOfficerList($squad_no, $year=''){
    $jobList = $this->getSquadronOfficers($squad_no, $year);
    $ary = array();
    foreach ($jobList as $job) {
        $this->loadD5Member($job['certificate']);
        $ary[$job['certificate']] = get_person_name($this->D5mbr);
    }
    return $ary;
}
//*********************************************************
function getSquadronOfficerRecords($squad_no, $year){
	$this->loadTable("jobs");
   	$jobList = $this->jobs->get_squadron_officer_list($squad_no, $year);    $ary = array();
    foreach ($jobList as $job) {
       $this->loadD5Member($job['certificate']);
       $ary[$job['certificate']] = $this->D5mbr;
    }
    return $ary;
}
//*********************************************************
function getSquadronOfficerNameList($squad_no, $year){
	$this->loadTable("jobs");
   	$jobList = $this->jobs->get_squadron_officer_list($squad_no, $year);
    $ary = array();
    foreach ($jobList as $job) {
        $this->loadD5Member($job['certificate']);
        $ary[$job['certificate']] = get_person_name($this->D5mbr);
    }
    return $ary;
}
//*********************************************************
function getSquadronOfficers($squad_no, $year=''){
        // Get all officers and committee chairs.  
        $this->loadTable("jobs");
        $squad_no = sprintf("%04d", $squad_no);
        // First get the officers listed in jobcode order
        if ($squad_no == '6243'){
			$search  = "(jobcode like '2_00_' or jobcode = '21610' )";
		} else {
	        $search  = "((jobcode like '3_00_' ) or (jobcode = '31610' ))";
    	    $search .= " and squad_no = $squad_no " ;
 		}
       	$search .= " and year=$year ";
        $list = $this->jobs->search_records_in_order($search, 'jobcode');
        return $list;
    }
//*********************************************************
function getSquadronWebSite($squad_no){
	$this->loadSquadron($squad_no);
	return $this->squad['web_site'];
}
//*********************************************************
function isDistrictOfficer($certno,$yr){
	$this->loadTable("jobs");
	$count = $this->jobs->count("certificate = '$certno' and year='$yr' and ( 
		( jobcode like '2_000') or 
		( jobcode like '2_001') or 
		( jobcode like '25300') or 
		( jobcode like '25310') or 
		( jobcode like '25710') or 
		( jobcode like '25700') ) "
		);
	if ($count)
		return true;
	else
		return false;
}
//*********************************************************
function isDistrictWebmaster($certno,$yr){
	$this->loadTable("jobs");
	$count = 
	
	$this->jobs->count("certificate = '$certno' and year = '$yr' and ( 
		( jobcode like '25710') or 
		( jobcode like '25700') ) "
		);
	if ($count)
		return true;
	else
		return false;
}
//*********************************************************
function isEditor($certno,$year){
	// Returns true for any member holding a squadron or district newsletter  editor job.  
	$this->loadTable("jobs");
	$count =  $this->jobs->count("certificate = '$certno' and year = '$year' and ( 
		( jobcode like '353__') or 
		( jobcode like '253__') ) "
		);
	if ($count)
		return true;
	else
		return false;	
}
//*********************************************************
function isSEO($certno, $yr){
 	$this->loadTable("jobs");
	$count = $this->jobs->count("certificate = '$certno' and year='$yr' and (
            ( jobcode like '33000') or 
            ( jobcode like '33001') or 
            ( jobcode like '23000') or 
            ( jobcode like '23001')	) "
    );
	if ($count)
		return true;
	else
		return false;

}
//*********************************************************
function isSquadronOfficer($certno,$yr){
	$this->loadTable("jobs");
	$count = $this->jobs->count("certificate = '$certno' and year='$yr' and ( 
		( jobcode like '3_000') or 
		( jobcode like '3_001') or 
		( jobcode like '35300') or 
		( jobcode like '35700') ) "
		);
	if ($count)
		return true;
}
//*********************************************************
function isValidD5Member($certno,$password){
	$this->loadD5Member($certno);
	$hashed_pw = $this->D5mbr['password'];
	if (validate_user($hashed_pw,$password))
		return true; 
	else
		return false;
}
//*********************************************************
private function loadD5Member($certificate){
include (JPATH_LIBRARIES.'/USPSaccess/dbUSPS.php');
	if ($this->loadTable("Members") and $this->loadTable("Addresses")){
		if (isset($this->D5mbr['certificate'])) 
			if (($this->D5mbr['certificate'] == $certificate))
				return true;
		$cols = $this->Members->cols;
		$mbr = $this->Members->get_record('certificate',$certificate);
		if (! $mbr or $mbr['active'] == 0){
			$this->D5mbr = FALSE;
			return false;
		}
		$adr = $this->Addresses->get_record('address_id',$mbr['address_id']);
		if (is_array($mbr)){
			$row = array_merge($mbr,$adr);
		} else {
			$row = "" ;
		}
		$this->D5mbr = $row;
		return true;
	}	
}
//*********************************************************
private function loadTable($lib){
require (JPATH_LIBRARIES.'/USPSaccess/dbUSPS.php');
	$zya = true;
	if (!isset($this->$lib)){
		$table = "table".$lib;
		$this->$lib = JoeFactory::getTable($table,$this->db); 
		if (!$this->$lib)
			{
				log_it("Did not open $lib table");
				return false;
			}
		return true;
	}
	return true;
}
//*********************************************************
private function loadDBd5_addresses(){
require (JPATH_LIBRARIES.'/USPSaccess/dbUSPS.php');
	$zya = true;
	if (!isset($this->d5_addresses)){
		if (!$this->d5_addresses= new tableAddresses($this->db, ''))
			{
				log_it("Did not open d5_addresses table");
				return false;
			}
		return true;
	}
	return true;
}
//*********************************************************
private function loadDBd5_excom(){
require (JPATH_LIBRARIES.'/USPSaccess/dbUSPS.php');
	$zya = true;
	if (!isset($this->exc)){
		if (! $this->exc = new tableExcom($this->db)){
			log_it("Did not open d5_squadrons table");
			return false;
		}
		return true;
	}
	return true;	
}
//*********************************************************
private function loadDBd5_hon(){
require (JPATH_LIBRARIES.'/USPSaccess/dbUSPS.php');
	$zya = true;
	if (!isset($this->hon)){
		if (! $this->hon = new USPStableAccess("honorary_members",$this->db)){
			log_it("Did not open d5_squadrons table");
			return false;
		}
		return true;
	}
	return true;	
}
//*********************************************************
private function loadDBjobs(){
    include(JPATH_LIBRARIES . '/USPSaccess/dbUSPS.php');
    if (!isset($this->jobs))
        if (!$this->jobs = new tableJobs($this->db)) {
            log_it("Did not open jobs table");
            return false;
        }
    return true;
}
//*********************************************************
private function loadDBjobcodes(){
    include(JPATH_LIBRARIES . '/USPSaccess/dbUSPS.php');
    if (is_null($this->jobcodes)){
    	$this->jobcodes = new tableJobcodes($this->db);
         if (! $this->jobcodes ) {
            log_it("Did not open jobcodes table");
            return false;
        }
	}
    return true;
}
//*********************************************************
private function loadDBD5_members(){
require (JPATH_LIBRARIES.'/USPSaccess/dbUSPS.php');
	$zya = true;
	if (!isset($this->Members)){
		if (!$this->Members = new tableMembers($this->db)){
			log_it("Did not open Members table");
			return false;
		}
		return true;
	}
	return true;
}
//*********************************************************
private function loadDBd5_squadrons(){
require (JPATH_LIBRARIES.'/USPSaccess/dbUSPS.php');
	$zya = true;
	if (!isset($this->d5_squadrons)){
		if (!$this->d5_squadrons = new tableSquadrons($this->db)){
			log_it("Did not open d5_squadrons table");
			return false;
		}
		return true;
	}
	return true;
}
//*********************************************************
private function loadDBrank(){
    include(JPATH_LIBRARIES . '/USPSaccess/dbUSPS.php');
    if (!isset($this->rank))
        if (!$this->rank = new tableRank($this->db)) {
            log_it("Did not open rank table");
            return false;
        }
    return true;
}
//*********************************************************
private function loadDistrict($dist_no){
	if (!isset($this->dist) or $this->dist['Name'] != $dist_no){
		$this->dist = $this->websites->get_record('name',$dist_no );
	}
}
//*********************************************************
private function loadSquadron($squad_no){
	if ($this->loadTable("d5_squadrons")){
		if (!isset($this->squad) or $this->squad['squad_no'] != $squad_no){
			$this->squad = $this->d5_squadrons->get_record("squad_no",$squad_no);
		}
	}
}
//*********************************************************
function setMember($mbr){
    // Used when calling routines already have a mbftp record
    $this->mbr = $mbr;
    return $this->mbr['certificate'];
}
//**********************************************************
function searchMembers($select){
	return $this->Members->search_members($select);
}
//**********************************************************
function updateMember($pst){
	$dif = $dif_a = array();
	if ($this->loadTable("Members") and $this->loadTable("Addresses")){
		$dif = $this->Members->update_record_changes("certificate",$pst);
		if (isset($pst['address_1']))
			$dif_a = $this->Addresses->update_record_changes('address_id', $pst);
		$this->D5mbr = array();
	}
	$this->loadD5Member($pst['certificate']);
	return array_merge($dif, $dif_a);
}
//********************************************************
function updatePassword($certno,$password){
	$this->loadD5Member($certno);
	$this->D5mbr['password'] = encrypt_password($password);
	$dif = $this->Members->update_record_changes("certificate",$this->D5mbr);
	if (is_array($dif)) return true; 
	return false; 
}
//*********************************************************
function updateSquadronNewsletterEdition($squad_no,$edition){
	$this->loadSquadron($squad_no);
	$this->squad['newsletter_edition'] = $edition;
	$this->d5_squadrons->update_record('squad_no',$this->squad);
	return true;
}

}//*******************  End of Library  *************************************
//********************************************************************
function build_department_name($excom){
	// User job title from parameter and convert to department name	
	$a_jobdesc = explode(" ",$excom['excom_position']);
	switch($a_jobdesc[0]){
		case "Commander":
			return "Commander's Department";
		case "Executive":
			return "Executive Department";
		case "Educational":
			return "Educational Department";
		case "Administrative":
			return "Administrative Department";
		case "Secretary":
			return "Secretary's Department";
		case "Treasurer": 
			return "Treasurer's Department";
		case "Asst.":
			return "Asst. Administrative Department";
	}
}
?>