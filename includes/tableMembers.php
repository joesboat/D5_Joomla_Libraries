<?php
/*
Copyright (C) March 2013, Joseph P. Gibson.

This program is free software to USPS Members developing software for USPS Squadrons or Districts: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program.  If not, see <http://www.gnu.org/licenses/>.

Owner: 	Joseph P. Gibson
		USPS District 5 Webmaster 
*/
require_once(JPATH_LIBRARIES."/usps/includes/routines.php");
require_once('tablePeople.php');
require_once('tableAddresses.php');
//*********************************************************
class tableMembers extends tablePeople{
public $convert;
public $certificate_column;
var $loging;
//*********************************************************
function __construct($db, $caller=''){
	// Creates the variables to contain identity of data and tables
	parent::__construct('d5_members', $db, $caller);
		//$this->list_cols=$col_subset;
		//$this->cols=$col_list;
} // constructer
//*********************************************************
function add_or_update($rowin){
global $addr;
// $rowin must be an array where index names match d5_members column names.
// Updates an existing member record or adds a new record. 
	$rowin['active'] = 1;
	$certificate = $rowin['certificate'];
	$rowin['squad_no'] = sprintf("%04d",$rowin['squad_no']);
	if ($certificate == 'F052106'){
		$xxx = $certificate;
		$yyy = $rowin ;
	}
	if ($row = $this->get_record('certificate',$rowin['certificate'])){
		//$rowin['address_id'] = $row['address_id'];
		$this->update_record_changes('certificate',$rowin);
	}else{
		if (!$this->add_record($rowin))
			die("Database access failed: " . mysql_error());
		$name = get_person_name($rowin);
		if ($this->loging) write_log_array($rowin,"New Member $name Added:");
	}
	return $rowin;
}
//*********************************************************
function build_mark5_mailing_list(){
global $addr, $mbr;
	$eol = "\r\n" ;
	$result = $addr->get_hardcopy_addresses();
	$members = array();
	$file_name = date("Ymd")."_Member_Addresses.csv";
	if (! $fh = fopen($file_name,'w'))
		return false;
	$header = '';
	foreach($addr->address_columns as $ckey=>$cname){
		$header .= "$cname,";
	}
	$i = fwrite($fh, $header.$eol);
	while ($address = $addr->get_next_record($result)){
		// for each address get the member record with the lowest certificate number  
		$line = '';
		$address_id = $address['address_id'];
		$family = $mbr->get_records_in_order('address_id',$address_id,'certificate');
		$member = $family[0];
		foreach($addr->address_columns as $ckey=>$cname){
			// Create csv file record here
			if ($ckey == 'address_1' and $member['address_2'] != ''){
				$member[$ckey] .= " ".$member['address_2'];
			}
			$line .= str_replace(',',' ',$member[$ckey]).',';
		}
		$i = fwrite($fh, $line.$eol);	
	}
	fclose($fh);
	return $file_name;
}
//******************************************************************
function build_mbr_content($aCol,$aLine,$update){
// Converts a numerically indexed array ($aLine) into 
// an associative array.  
//	Two parameters:
//		$aCol - 	Array containing column name to numeric index conversion
//		$aLine 		Array containing numerically indexed columns 
//	Function will:
//		Build and return an associative array to represent the record to be created 
//	if $update ignore protected records 
	$row = array(); 
	foreach($aCol as $ixxx=>$array){
		//if ($array['input_col_index'] == 0) continue;
		if ($update and $array['protected']==1) continue;  
		$col_index = $array['input_col_index'];
		switch ($array['type']){
			case 'datetime':
				$date = 0;
				if (($dt = $aLine[$array['input_col_index']])!='')
					$date = make_standard_date($dt);
				else 
					$date = "0000-00-00 00:00:00";
				$row[$array['db_col_id']] = $date ;
				break;	
			case 'varchar(4)':
				$row[$array['db_col_id']] = sprintf("%04d",trim($aLine[$array['input_col_index']]));
				break;
			default:
				if ($array['special']){
					$s = trim($aLine[$array['input_col_index']]);
					$s = str_replace("'",'&apos;',$s);	
					$s = str_replace('"',"",$s);
					$row[$array['db_col_id']] = $s;
				}else{
					if ($array['db_col_id']=='mmsi' and $aLine[$array['input_col_index']] != ''){
						$mmsi = $aLine[$array['input_col_index']];
					}
					$row[$array['db_col_id']] = trim($aLine[$array['input_col_index']]); 
				}
		}
	}
	return $row;
}
//*********************************************************
function delete($cert){
	// Reset the 'active' flag to eliminate the member from roster lists 
	$rec = $this->get_record('certificate',$cert);
	if ($rec['protected'] != 1)
		$rec['active'] = 0;
	$this->update_record_changes('certificate', $rec);
}
//*********************************************************
function get_certificate_column($line){
	$lx = str_replace('"','',$line);
	$ln = explode(',',$lx);
	foreach($ln as $ix=>$value){
		$value = strtolower($value);
		if ($value=='certificate') return $ix; 
	}
	return -1;
}
//*********************************************************
function get_d5_or_squad_member_list($squadron){
global $exc;
// Obtains a list of members
	$ary = array();
	$search = "active=1 ";
	if ($squadron != "")
		$search .= " and squad_no = '$squadron'";
	$array = $this->search_records_in_order($search,"last_name");
	foreach($array as $key=>$value){
		$ary[$value['certificate']] = get_person_name($value);
	}
	return $ary;
}
//*********************************************************
function get_d5_member_count(){
	$result = $this->select_members();
	return $result->num_rows;
}
//*********************************************************
public function get_email_addr($m){
// Returns members e-mail address 
	$row=$this->get_record('certificate',$m);
	return $row['email'];	
}
//*********************************************************
function get_email_addr_list(){
// Build a standard list of addresses separated by ';'
	$lst = '';
	$result = $this->select_members("email != '' and no_email = 0");
	while ($row = $this->get_next_record($result)){
		$lst .= $row['email'].";";
	}
	return $lst;
}
//*********************************************************
function get_member_by_name($name){
	$nm_ary = explode(' ',$name);
	// try first last order first
	$query = "active=1 and " ;
	$query .= "first_name like '".$nm_ary[0]."%' and last_name like '".$nm_ary[1]."%'";
	$recs = $this->search_records_in_order($query);
	switch (count($recs)){
		case 0:
			return false;
		case 1:
			return $recs[0];
		default:
			return false;
	}
}
//*********************************************************
function get_member_names_for_newsletter_address($address_id, $squad_no = '6243'){
	$str = $last = '';
	$junior = $and = $first = array();
	// Uses address_link table to find members living at an address
	// Formats a too address using primary member then additional members  
	$query = "address_id=$address_id and active=1";
	if ($squad_no != '6243'){
		$query .= " and squad_no = '$squad_no'";		
	}
	$family = $this->search_records_in_order($query,'mbrstatus DESC');
	//foreach ($family as $member)
	if (count($family) > 0){
		$member = $family[0];
		switch($member['mbrstatus']){
			case 'AC10':		// Active member without family members.
			case 'AC15':		// Active member with family members.
			case 'AC20':		// Sustaining Member without family members
			case 'AC25':		// Sustaining Member with family members
			case 'AC50':		// Life member without family members
			case 'AC55':		// Life member with family members.
			case 'AC70':		// Life and Sustaining member without family members
			case 'AC75':		// Life and Sustaining member with family members
				$first[] = $member['first_name'];	 
				$last = $member['last_name'];
				break;
			case 'AC11':		// Additional active member.
				if ($member['last_name'] == $last){
					$first[] = $member['first_name']; 
				} else {
					$and[] = $member['first_name'].' '.$member['last_name'] ;
				}
				break;
			case 'WC10':		// Woman certificate holder
				if ($member['last_name'] == $last){
					$first[] = $member['first_name']; 
				} else {
					$first[] = $member['first_name'];
					$last = $member['last_name'];
				}				
				break; 
			case 'AC12':		// Junior family member
				//$junior[] = $member['first_name'].' '.$member['last_name'] ;					 
				break;
			case 'SC10':		// Active Sea Scout
			case 'AP10':		// Apprentice
				$junior[] = $member['first_name'].' '.$member['last_name'] ;	
				break;
		}
	}
	foreach($first as $fnx=>$fn){
		if ($fnx == (count($first)-1)) $str .= "$fn ";
		elseif ($fnx == (count($first)-2)) $str .= "$fn and ";
		else $str .= "$fn, " ;
	}
	$str .= "$last ";
	foreach($and as $fn){
		$str .= " and $fn";
	}
	foreach($junior as $fn){
		$str .= " and $fn"; 
	}
	if (substr($str,0,6) == '  and '){
		$str = substr($str,6,strlen($str)-6);
	}
	return $str;
}
//*********************************************************
function get_mbr_record($cert){
	$rec = $this->get_record("certificate",$cert);
	if ($rec['active'] == 1)
		return $rec;
	return false;
}
//*********************************************************
function get_mbr_record_by_email($email){
	$rec = $this->get_record('email',$email);
	if ($rec['active'] == 1)
		return $rec;
	return false;
}
//*********************************************************
function get_mbr_records($col='',$value='',$order=''){
	$rows = array();
	$query = '';
	if ($col != ''){
		$query .= "$col = '$value'";
	} else $query = '';
	$result = $this->select_members($query);
	while ($row = $this->get_next_record($result)){
		$rows[] = $row;
	}
	return $rows;
}
//*********************************************************
function get_squad_member_count($squad_no,$type=''){
	$select = "squad_no = $squad_no "; 
	if (is_array($type)){
		$select .= ' and (';
		foreach ($type as $tp){
			$select .= " mbrstatus like '$tp%' or "  ;
		}
		$select = substr($select,0,strlen($select)-3) . ") ";
	} elseif($type != ''){
		$select .= " and mbrstatus like '$type%' ";
	}
	$result = $this->select_members($select);
	return $result->num_rows   ;
}
//*********************************************************
function get_squad_ss_count($squad_no){
	$select = "( mbrstatus like 'SC%' or mbrstatus like 'AP%' ) ";
	$select .= " and squad_no = $squad_no"; 
	$result = $this->select_members($select);
	return $result->num_rows   ;
} 	
//*********************************************************
function process_deleted_records($curr){
	global $gst;
	$deleted=array();
	$result = $this->select_partial_records_in_order();
	while($row = $this->get_next_record($result)){
		if (!in_array($row['certificate'],$curr))
			$deleted[count($deleted)] = $row;
	}
	log_deleted_member_records($deleted);
	foreach($deleted as $row){
		$row['contact_date'] = $row['cert_date'];
		//$gst->add_record($row);
		$this->delete($row['certificate']);
	}
}
//*********************************************************
function search_members($select){
	$query = "active=1";
	if ($select != '')
		$query .= " and ($select) ";
	return $this->search_records_in_order($query,'last_name, first_name');
}
//*********************************************************
function search_records_in_order($query="",$order=""){
	if (! strpos(strtolower($query),"active=1")){
		$query = "(active=1) and ".$query; 
	}
	return parent::search_records_in_order($query,$order);
}
//*********************************************************
function select_members($select=''){
	//  Called when only active members are to be selected
	$query = "active=1";
	if ($select != '')
		$query .= " and ($select) ";
	return $this->select_partial_records_in_order('*',$query,'last_name, first_name');
}
//*********************************************************
function select_members_and_associates($squad){
	include (JPATH_LIBRARIES.'/USPSaccess/dbUSPS.php');
	$select_base = " active=1 ";
	$asoc = new tableAssociates($squad, $db_d5, '');
	$list = $asoc->get_assoc_certificates();
	$select_assoc = '';
	foreach($list as $m){
		$crt = $m['certificate'];
		$select_assoc .= " or certificate='$crt'";
	}
	$select_squad = "squad_no='$squad'";
	$select = "$select_base and ($select_squad $select_assoc)";
	$result = $this->select_partial_records_in_order('*',$select,'last_name, first_name');
	return $result;
}
//*********************************************************
function select_member_for_usps_event_record($usps){
global $jobs, $year;
	if ($member = $this->get_member_by_name($usps['cname']) and 
				($member['squad_no'] == $usps['account'])){
		// member name matches a D5 member and members squadron matches squadron
		$poc_id = $member['certificate'];
	}elseif($member = $this->get_mbr_record_by_email($usps['cemail'])){
		$poc_id = $member['certificate'];
	}else{
		$poc_id = $jobs->get_squadron_ed_officer_certificate($usps['squad_no'],$year);
	} 
	return $poc_id;
}
//*********************************************************
function show_member_list_box($members,$size){
	echo "<select name='member_cert' size='$size' id='member_cert' width='50'>;" ;
	show_option_list($members,"");
	echo "</select>";
}
//*********************************************************
function update_record_changes($col,$array,$log='' ){
	$changed = parent::update_record_changes($col,$array,$log);
	$certificate = $array['certificate'];
	if (count($changed) > 0){
		$name = get_person_name($array);
		if ($this->loging) write_log_array($changed,"Member $name ($certificate) has updated: " );
	}
	return $changed; 
}
} // End of Class table_members
?>