<?php
require_once(JPATH_LIBRARIES."/usps/includes/routines.php");
//************************************************************************************
class tableAddresses extends USPStableAccess{
	// Generic routines to manage a table 
//********************************* Public Variables **********************************
var $address_columns = array();
var $loging = false; 
//********************************* Private Variables *********************************
//private $manager ;
//********************************************************************************************
function __construct($db, $caller){
		// Creates the variables to contain identity of data and tables 
	parent::__construct('d5_addresses', $db, $caller);
		//$this->list_cols=$col_subset; 
		//$this->cols=$col_list;	
	$this->build_address_columns();		
} // constructer
//************************************************************************************
function add_or_update_address($input){
	$vhqab = JoeFactory::getLibrary("USPSd5tableVHQAB"); 
	$mbr = $vhqab->getD5MembersObject();
	// Provided member record from DB2000 csv or VHQAB.mbftp
	// Field names updated to match d5_members table
	switch ($input['mbrstatus']){
		case 'AC10':		// Active member without family members.
		case 'AC15':		// Active member with family members.
		case 'AC20':		// Sustaining Member without family members
		case 'AC25':
		case 'AC50':		// Life member without family members
		case 'AC55':		// Life member with family members.
		case 'AC70':		// Life and Sustaining member without family members
		case 'AC75':		// Life and Sustaining member with family members
			// Use & update previous address 
			if ($input['address_1'] == 'Address Unknown'){
				$xxx = $input['address_1'];
			}
			$row = $mbr->get_record("certificate",$input['certificate']);
			if ($row['address_id'] == 0 or $input['address_1'] == 'Address Unknown'){
				// It's new
				if (! $address = $this->find_address($input)){
					$address = $this->add_record($input);
				}
				if ($address['address_id'] == 0){
					$my_address_is_0 = $address['address_id'];
				}
				
				$input['address_id'] = $address['address_id'];
				$mbr->update_record_changes('certificate',$input);
				$adress_id = $input['address_id'];
				// Now make sure any family members are using the same address
				// Search for other members who may be linked to this prime certificate
				$others = $mbr->get_mbr_records('primcert',$row['certificate']);
				foreach($others as $other){
						$other['address_id'] = $input['address_id'];
						$mbr->update_record_changes('certificate',$other);
				}
				// Adopt that record as primary and update with this address data g
				// Simply add a new address record 
				// Otherwise create a new address.
			} else {
				// OK, we have an existing address_id
				// Just update the address  
				$input['address_id'] = $row['address_id'];
				$address = $this->get_record('address_id',$row['address_id']);
				$this->update_record('address_id',$input);
			}	
			break;
		case 'WC10':		// Woman certificate holder
			$primary = $mbr->get_record('certificate',$input['spo_cert']);
		case 'AC11':		// Additional active member.
		case 'AC12':		// Junior family member
			// Find Primary Member Record & use that address id
			if (strtoupper(substr($input['mbrstatus'],0,2)) == 'AC')
				$primary = $mbr->get_record('certificate',$input['primcert']);
			if ($primary and $primary['address_id'] != 0){
				$input['address_id'] = $primary['address_id'];
				$mbr->update_record_changes('certificate',$input);
			} else {
				// If Primary Member not found create and link address record
				if (! ($address = $this->find_address($input))){
					$address = $this->add_record($input);
				} 
				$input['address_id'] = $address['address_id'];
				$mbr->update_record_changes('certificate',$input);				
			} 
			break;
		case 'SC10':		// Active Sea Scout
		case 'AP10':		// Apprentice
			// Not considered family members 
			$member = $mbr->get_record('certificate',$input['certificate']);
			if ($member['address_id'] != 0){
				$input['address_id'] = $member['address_id'];
				$this->update_record('address_id',$input);	
			}else{
				$address = $this->add_record($input);
				$input['address_id'] =  $address['address_id'];
				$mbr->update_record_changes('certificate',$input);				
			}
			break;
			// Look for existing address_id 
			// use spouce search to find a primary.  If found use that address_id 
		default:
			break;
	}
	return $input;
}
//************************************************************************************
function add_record($rec){
	$rec['hc_mark_5'] = 1;
	parent::add_record($rec);
	$address = $this->find_address($rec);
	if (! $address){
		// Somthing must be wrong.  We just added this adddress 
	}
	return $address; 
}
//************************************************************************************
function build_address_columns(){
	$ary = array();
	$ary[] = 'name';
	foreach($this->cols as $column ){
		//$y = $input[$column];
		if ($column == 'address_2') continue;
		if ($column == 'address_id') continue;
		if ($column == 'hc_mark_5') continue;
		if ($column == 'protect_addr') continue;
		
		$ary[] = $column;
	}
	$this->address_columns = $ary;
}
//************************************************************************************
function find_address($input){
// search table for this address 
// if found insert id in address_id field 
	$query = "zip_code = '".$input['zip_code']."'";
	foreach($this->cols as $name ){
		//$y = $input[$name];
		if ($name == 'address_id') continue;
		if ($name == 'hc_mark_5') continue;
		if ($name == 'protect_addr') continue;
		if ($name == 'zip_code') continue;
		$query .= " and $name = '".fix_value($input[$name])."'";
	}
	$addresses = $this->search_records_in_order($query,'address_id');
	switch (count($addresses)){
		case 0:
			return false;
		case 1: 
			return $addresses[0];
		default:
			return $this->merge_addresses($addresses);
	}
}
//**************************************************************************
function get_address_fields($member){
	// queries table for address data using address_id
	// merges address fields into $member record 
	return $member;	
}
//************************************************************************************
function get_and_add_member_address($row){
	// Obtains addres using member record addrss_id field
	// Merges address fields into member record 
	$address = $this->get_record('address_id',$row['address_id']);
	if ($address){
		$row = array_merge($row,$address);
	}else{
		$row = array_merge($row,$this->blank_record);
		$xxx = "This is the error record.";
	}
	// Returns row 	
	return $row;
}
//************************************************************************************
function get_hardcopy_addresses(){
	$result = $this->select_partial_records_in_order('*','hc_mark_5 = 1','address_id');
	return $result;
}
//************************************************************************************
function merge_addresses($list){
	$vhqab = JoeFactory::getLibrary("USPSd5tableVHQAB"); 
	$mbr = $vhqab->getD5MembersObject();
	$members = array();
	$address = $list[0];
	unset($list[0]);
	// Called when we have identified a duplicate address.
	foreach($list as $this_address){
		// Evaluates each list entry find any member record linked to the address
		$mbr_list = $mbr->get_mbr_records('address_id',$this_address['address_id']);
		$members = array_merge($members,$mbr_list);
		// Deletes all but one of the address records
		$this->delete_records('address_id',$this_address['address_id']);
	// builds a list of member records 
	}
	foreach($members as $member){
		$member['address_id'] = $address['address_id'];
		$mbr->update_record_changes('certificate',$member);
	}
	// Replaces the remaining address_id in member records.  
	// Returns the remaining address record.  
	return $address; 
}
//************************************************************************************
function select_addresses(){
	$result = $this->select_partial_records_in_order('*','','address_id');
	return $result;
}
//************************************************************************************
function update_record($field,$input){
	$differences = array();
	$name = get_person_name($input);
	$certificate = $input['certificate'];
	$curr = $this->get_record($field,$input['address_id']);
	if ($curr['protect_addr'] == 0){
		$changed = parent::update_record_changes($field,$input);
		unset($changed['protect_addr']);
		if (count($changed) > 0)
			if ($this->loging) write_log_array($changed,"Member $name ($certificate) has changed:");
		return;
	}
	$same = true ;
	foreach($curr as $fld=>$value){
		if ($fld == 'protect_addr')	continue;
		if ($fld == 'hc_mark_5') continue;
		if ($curr[$fld] != $input[$fld]){
			$same = false;
			$differences["USPS - $fld"] = $input[$fld];
			$differences["D5 - $fld"] = $curr[$fld];
		}
	}
	if ($same){
		// OK - No need to protect it
		$input['protect_addr'] = 0; 
		parent::update_record($field,$input);
		if ($this->loging) log_it("Address for member $name ($certificate) synchronized.  'protect_addr' is reset. " );
	} else {
		if ($this->loging) write_log_array($differences,"Address for member $name ($certificate) remains protected:");
	}
}
} // End of class Table
?>