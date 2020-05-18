<?php
//*************************************************************
function displayNationalJobs($year){
global $vhqab, $exc;
	$codes = $vhqab->getJobcodesObject();
	echo "<table width='650' border='0' cellspacing='0' cellpadding='0'>";
	echo "<colgroup><col style='width:200px;'}><col style='width:150px;'><col style='width:300px;'></colgroup>";
	echo "<tr class='table'><td class='table'>&nbsp;</td><td class='table'>&nbsp;</td><td class='table'>&nbsp;</td></tr>";
	echo "<tr class='table'><td class='table' colspan='3'><div class='style16b' align='center'>NATIONAL OFFICERS FROM DISTRICT 5</div></td></tr>";
	echo "<tr class='table'><td class='table' colspan='3'><div class='style14' align='center'>Rear Commanders</div></td></tr>";
	$rows = $vhqab->getJobAssignments(10004,$year);
	foreach($rows as $row){
		$this->display_national_job_assignment_row($row, $year);
	}
	echo "<tr class='table'><td class='table' colspan='3'><div class='style14' align='center'>Staff Commanders</div></td></tr>";
	$rows = $vhqab->getJobAssignments(10005,$year);
	foreach($rows as $row){
		$this->display_national_job_assignment_row($row, $year);
	}
	show_blank_rows(1);
	echo "</table>";
}
//*************************************************************
function displayD5Trusts($year){
global  $exc;
	// Obtain mbr data for Henry E Sweet Trust 
	// Obtain mbr data for Seavester Trust 
	// Display two columns listing 
	//		HES Trust Members in left Colunm
	//		Seaverster Trust Members in Right Column
	echo "<table width='650' border='0' cellspacing='0' cellpadding='0'>";
	echo "<colgroup><col style='width:325px;'><col style='width:325px;'></colgroup>";
	echo "<tr><td colspan='2'><div class='style16b' align='center'>DISTRICT 5 TRUSTS</div></td></tr>";
	$hes = $vhqab->getJobAssignments(28020,$year);
	$hes_mbr = $vhqab->getJobAssignments(28022,$year);	
	$hes = array_merge($hes,$hes_mbr);
	$sea = $vhqab->getJobAssignments(28030,$year);
	$sea_mbr = $vhqab->getJobAssignments(28032,$year);
	$sea = array_merge($sea,$sea_mbr);	
	echo "<tr><td class='table'>";
	echo "<div align='center'><div class='style14b' align='center'>HENRY E. SWEET TRUST, TRUSTEES*</div></div>";
	echo "</td><td class='table'>";
	echo "<div align='center'><div class='style14b' align='center'>SEAVESTER FUNDS COMMITTEE*</div></div>";
	echo "</td></tr>";
	display_two_sets_in_two_columns($hes,$sea);
	show_blank_rows(1);
	echo "<tr><td colspan='4' class='table'><div align='center'>*To make a contribution to these trusts, contact District 5 <a href='mailto:mcloud@yahoo.com'>Treasurer </a></div></td></tr>";
	show_blank_rows(1);
	echo "</table>";
}
//*************************************************************
function displayGoverningBoardRows($year){
	global $mbr, $vhqab,  $exc, $blob;
	$codes = $vhqab->getJobcodesObject();
	echo "<table width='600' border='0' cellspacing='0' cellpadding='0'>";
	echo "<tr class='table'><td colspan='2'>";
	echo "<div align='center' class='style16b'>USPS GOVERNING BOARD MEMBERS </div>";
	echo "</td></tr>";
	show_blank_rows(1);
	echo "<tr class='table'><td colspan='2'>";
	echo "<div align='center' class='style14b'>District Officers</div>";
	echo "</td></tr>";
 	$excom = get_excom_member_data('21000',$year);
	echo "<tr class='table'><td colspan='2'><div align='center'>";
	echo $exc->get_d5_member_name(true,$mbr->get_mbr_record($excom['certificate']));
	echo "</div></td></tr>";
	$excom = get_excom_member_data('23000',$year);
 	echo "<tr class='table'><td colspan='2'><div align='center'>";
	echo $exc->get_d5_member_name(true,$mbr->get_mbr_record($excom['certificate']));
	echo "</div></td></tr>";
	show_blank_rows(1);
    echo "<tr><td colspan='2' class='table'>";
	echo "<div align='center' class='style14b'>Squadron Officers</div>";
	echo "</td></tr>";
    echo "<tr><td colspan='2' class='table'>";
	echo "<div align='center'><a href='squadron_commanders.php'>All Squadron Commanders</a></div>";
	echo "</td></tr>";
	show_blank_rows(1);
    echo "<tr><td colspan='2' class='table'>";
	echo "<div align='center' class='style14b'>Members Emeritus</div>";
	echo "</td></tr>";
	$rows = $vhqab->getJobAssignments(10002,$year);
	display_one_set_in_two_columns($rows,'center'); 
	show_blank_rows(1);
    echo "<tr><td colspan='2' class='table'>";
	echo "<div align='center' class='style14b'>General Members</div>";
	echo "</td></tr>";
	$rows = $vhqab->getJobAssignments(10001,$year);
	display_one_set_in_two_columns($rows,'center'); 
	show_blank_rows(1);
	echo "</table>";
}
//*************************************************************
function getD5JobsForList($jc,$year){
global $mbr, $jobs;
	// get each valid assignment for top level committee list
	// always returns an array which may contain only one array entry 
	$rows = array();
	switch ($jc['committee']){
		case 0:
		case 1:
			$js = $vhqab->getJobAssignments($jc['jobcode'], $year);
			foreach($js as $j){
				if (is_array($j)){
					$j = array_merge($j,$jc);
					$rows[count($rows)] = $j;			
				}			
			}
			break;
		case 2:
		case 3:
			$mb = $mbr->blank_record;
			$rows[count($rows)] = array_merge($mb,$jc);
			break;
	}
	return $rows;
}
//*********************************************************
function showCommittee($code, $dept, $year){
	//	Called from specify jobs 
	//  Displays a committee.
	global $jobs, $join, $exc, $mbr, $wkr, $codes; 
	$new = false;
	// Remember what we are doing to inform PHP in $_POST array
	echo "<input type='hidden' name='updating' value='committee' />";
	// Always assume we will return focus to the higher level 'jobs' display
	// Hidden value may be changed by script return focus to different display 
	echo "<input type='hidden' name='next' id='next' value='jobs' />";
	if ($committee=$this->get_record("jobcode",$code)){
		$i =$code;
		if ($committee['committee'] == '1'){
			$chairs=$vhqab->getJobAssignments($code,$year);
			echo "<input type='hidden' name='jobcode_chair' id='jobcode_chair' value='$i'>";
			$i += 1;
			$asst_chairs=$vhqab->getJobAssignments($code+1,$year);
			echo "<input type='hidden' name='jobcode_asst' id='jobcode_asst' value='$i'>";
			$i += 1;
		}
		$members=$vhqab->getJobAssignments($i,$year);
		echo "<input type='hidden' name='jobcode_member' id='jobcode_member' value='$i'>";
	}
	echo "<p>Fill out the committee roster by adding or deleting names in this form.  Use the <img src='../images/close.gif'> button besides the name to delete a member from the committee.  To add, first select the member's name from the 'All D5 Members' list.  Then use one of the three committee position buttons to add that member to the form. You must press the 'Update' button to save the changes.</p>";
	echo "District Year: <input type='text' name='year' size='8' value='$year'>";
	if ($committee['committee'] == '1'){
		echo $this->make_cmte_transfer_button('Chair','Add Chairman');
		echo $this->make_cmte_transfer_button('Asst','Add Asst');
	}
	echo $this->make_cmte_transfer_button('Member','Add Member');
	echo "<br/>";
	echo "<table id='cmte'>";
	echo "<tbody id='cmte_hd'>";
	echo "<tr id='cmte_r1'>";
	echo "<th id='cmte_r1c1'>Committee Name: </th>";
	echo "<td id='cmte_r1c2'>";
	echo "<input id='cmte_name' type='text' name='committee_name' ";
	if (!$new) echo "value='".$committee['jdesc']."' readonly ";
	echo "size='50'>";
	echo "</td></tr>";
	if ($dept == 25710) echo "<tr><th>Committee Code:</th><td>$code</td></tr>";
	if ($dept == 21000 or $dept == 25710){
		//  This is the commander, we must have the option of specifying department
		echo "<th>Department: </th>";
		echo "<td>";
		$exc->show_d5_department_dd_list_box($exc->get_department_list($year),$committee['department']);
		echo "</td>";
	}
	echo "</tr></tbody>";
	echo "<br/>";
	if ($committee['committee'] == '1'){
		echo $this->format_committee_position("Chair",$chairs);
		echo $this->format_committee_position("Asst",$asst_chairs);
	}
	echo $this->format_committee_position("Member",$members);
	$named = $this->get_named_job_assignments($code,false,$year);
	foreach($named as $n){
		echo "<tr><td>". $n[0]. "</td>";
		echo "<td>".$n[1]."</td></tr>";	
	} 
	echo "</table>";
}
//*********************************************************
function showSquadCommittee($code, $squad_no, $year){
global $jobs, $join, $exc, $mbr, $wkr, $codes; 
	//	Called from specify jobs 
	//  Displays a committee.
	$new = false;
	// Remember what we are doing to inform PHP in $_POST array
	echo "<input type='hidden' name='updating' value='committee' />";
	// Always assume we will return focus to the higher level 'jobs' display
	// Hidden value may be changed by script return focus to different display 
	echo "<input type='hidden' name='next' id='next' value='jobs' />";
	echo "<input type='hidden' name='squad_no' id='squad_no' value='$squad_no' />";
	if ($committee=$this->get_record("jobcode",$code)){
		$i =$code;
		if ($committee['committee'] == '1'){
			$chairs=$vhqab->getJobAssignments($code,$year,$squad_no); 
			echo "<input type='hidden' name='jobcode_chair' id='jobcode_chair' value='$i'>";
			$i += 1;
			$asst_chairs=$vhqab->getJobAssignments($code+1,$year,$squad_no);
			echo "<input type='hidden' name='jobcode_asst' id='jobcode_asst' value='$i'>";
			$i += 1;
		}
		$members=$vhqab->getJobAssignments($i,$year,$squad_no);
		echo "<input type='hidden' name='jobcode_member' id='jobcode_member' value='$i'>";
	}
	echo "<p>Fill out the committee roster by adding or deleting names in this form.  Use the <img src='../images/close.gif'> button besides the name to delete a member from the committee.  To add, first select the member's name from the 'All D5 Members' list.  Then use one of the three committee position buttons to add that member to the form. You must press the 'Update' button to save the changes.</p>";
	echo "District Year: <input type='text' name='year' size='8' value='$year'>";
	if ($committee['committee'] == '1'){
		echo $this->make_cmte_transfer_button('Chair','Add Chairman');
		echo $this->make_cmte_transfer_button('Asst','Add Asst');
	}
	echo $this->make_cmte_transfer_button('Member','Add Member');
	echo "<br/>";
	echo "<table id='cmte'>";
	echo "<tbody id='cmte_hd'>";
	echo "<tr id='cmte_r1'>";
	echo "<th id='cmte_r1c1'>Committee Name: </th>";
	echo "<td id='cmte_r1c2'>";
	echo "<input id='cmte_name' type='text' name='committee_name' ";
	if (!$new) echo "value='".$committee['jdesc']."' readonly ";
	echo "size='50'>";
	echo "</td></tr>";
	if ($committee['committee'] == '1'){
		echo $this->format_committee_position("Chair",$chairs);
		echo $this->format_committee_position("Asst",$asst_chairs);
	}
	echo $this->format_committee_position("Member",$members);
	$named = $this->get_named_job_assignments($code,false,$year);
	foreach($named as $n){
		echo "<tr><td>". $n[0]. "</td>";
		echo "<td>".$n[1]."</td></tr>";	
	} 
	echo "</table>";
}

?>