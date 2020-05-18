<?php
require_once(JPATH_LIBRARIES."/usps/tableAccess.php");
class tableJobcodes extends USPStableAccess{
	// Generic routines to manage a table 
//********************************* Public Variables **********************************
//********************************* Private Variables *********************************
//private $manager ;
//********************************************************************************************
function __construct($database, $caller=''){
		// Creates the variables to contain identity of data and tables 
	parent::__construct("jobcodes", $database, $caller);
		//$this->list_cols=$col_subset; 
		//$this->cols=$col_list;		
} // constructer
//*********************************************************
//*********************************************************
function format_committee_position($name,$ary){
	global $exc;
	$empty=false;
	$i = 0;
	$str = "<tbody id='cmte_$name'>";
	foreach($ary as $a){
		$id = $name."_".$i;
		$s =  	"<tr id='r_$id'>";
		$str .= $s; 
		$s =  	"<th id='rc1_$id'>".$name.": "; 
		$s .= 	"<input id='cert_$id' type='hidden' name='cert_$id' value='".$a['certificate']."'>";
		$s .= 	"</th>";
		$str .= $s; 
		$s =  	"<td id='rc2_$id'><input type='text' id='name_$id' size='50' value='";
		if ($empty) $s .=""; else $s .=  $exc->get_d5_member_name(false,$a);
		$s .=  	"'></td>";
		$str .= $s;
		$s =  	"<td id='rc3_$id'><button type='button' id='b_".$id."' this member from the committee.' onclick='btn_del_cmte_mbr(this);'><img src='../images/close.gif' ></button></td>";
		$str .= $s;
		$s = 	"</tr>";
		$str .= $s; 
		$i ++;
	}
	return $str."</tbody>"; 
}
//*************************************************************
function get_active_d5_committes($dept,$order="jdesc"){
	// join jobdesc, jobs, members
	// omit jobdesc ending in 2 (committee members)
	// 
	$query = '';
	$rows=array();
	if ($dept != ""){
		$query .= "(";
		$r = substr($dept,4,1);	
		if ($r == '0'){
			$query .= "jobcodes.department = '";
			$query .= substr($dept,0,4)."1'";
			$query .= " or ";	
		}
		$query .= "jobcodes.department = '$dept'";
		$query .= ") and ";
	}
	$query .= "("; 
	$query .= "jobcode LIKE '2____' ";
	$query .= ")";
	$query .= "and d5_job = 1";
	$jcs = $this->search_records_in_order($query,$order);
	return $jcs; 
}
//*************************************************************
function get_squad_assignments($order){
	//global $mbr;
	// join jobdesc, jobs, members
	// omit jobdesc ending in 2 (committee members)
	// 
	if ($order == 'display_order'){
		$query = 'display_order > 0';
	}else{
		$query = "jobcode LIKE '3___0'";
		$query .= " or jobcode LIKE '3___5'";
		$query .= " or jobcode LIKE '3___6'";
		$query .= " or jobcode LIKE '3___1'";
	}
	return $this->search_records_in_order($query,$order);
}
//*********************************************************
function get_job_name($jobcode){
	$row = $this->get_record('jobcode',$jobcode);
	return $row['jdesc'];	
}
//*********************************************************
function get_new_jobcode($base){
	// Generates a new jobcode in 80000 range.
	$rows=$this->search_partial_records_in_order("jobcode","jobcode LIKE '$base'","jobcode");
	if ($rows){
		$last=$rows[count($rows)-1];
		return $last['jobcode'] + 10;		
	}else
		return "28020";
}
//*********************************************************
function make_cmte_transfer_button($type,$message){
	$str = "<button type='button' id='b_cmte_".$type."' ";
	$str1 = 'onclick="'."btn_add_cmte_mbr('".$type."');".'" '; 
	$str2 = "title='Add entry from D5 Member List to this committee position.  You may add multiple entries to each position.  We suggest you add names to a list before deleting existing names.'>$message</button>";
	return $str.$str1.$str2; 
}
//*********************************************************
function show_committee_dd_list($committees,$cc){
	echo "<select name='committee_code' width='50'>;" ;
	show_option_list($committees,$cc);
	echo "</select>";
}
//****************************************************
function show_squad_jobs($squad_no, $year){
global $mbr, $exc, $join, $codes;
	$id = 0;
	// Page Lists Squadron Officers and Committees  
	//		Officers are directly managed on this page. 
	//		Committee member jobcodes are ignored.  They are managed in the committee page
	//		Committee chair job description is listed with links to another page.  
	//			When link is activated current assignments are used to update database and 
	//				then the user is transferred to 'Show Committee' page 
	//		For remaining jobs the following columns are listed. 
	//			Job Description as TH 
	//	 		Member assigned as TD
	//			Icon to delete currently assigned member
	//			Icon to transfer highlighted name from members column to this job.  
	// Contents of table rows are obtained from jobdesc and jobs tables 
	// 
	// Ignore any jobcode ending in 2 (Committee Member)
	// Display link to 'Show Committee' for each jobdesc with committee in name 
	// For other jobs display the job description followed by member assigned 
	// Display blank area when no member assigned
	// Display multiple lines when more than one member assigned.  
	// Table row For each displayed line consists of:
	// Remember what we are doing
	echo "<input type='hidden' name='updating' value='jobs' >";  
	// Always plan to return here after update 
	echo "<input type='hidden' name='next' id='next' value='jobs' >";  
	echo "<input type='hidden' name='squad_no' id='squad_no' value='$squad_no' >";  
	// Begin display
	echo "<table id='tbl2'>";
	echo "<tr id='tr_$id'>";
	echo "<input type='hidden' id='jobcode_$id' value='".$this->get_new_jobcode()."' />";
	echo "<td id='c1_$id'>'Create a new job description'</td>";
	echo "<td id='c2_$id'>";
	echo "<td id='c3_$id'>";
	echo "<td id='c4_$id'>";
	echo "<td id='c5_$id'><button type='button' id='define_$id' onclick='btn_define_jobcode($id);' ";
	echo "title='Switches focus to the Setup New Jobcode Description Page where you may crete the name and parameters.'><img src='../images/down.gif' /button></td>";
	echo "</tr>";
	$list = $this->get_squad_assignments('jobcode');
	foreach($list as $jc){
		$rows = $codes->get_squad_jobs_for_list($jc,$squad_no,$year);
		foreach($rows as $row){
			$id+=1;
			$jobcode = $row['jobcode'];
			$cert = $row['certificate'];
			echo "<tr id='tr_$id'>";
			// Hide the jobcode of this job or committee
			echo "<input type='hidden' id='jobcode_$id' value='$jobcode' />";
			// Display the job or committee name
			echo "<td id='c1_$id'>".$row['jdesc']."</td>";
			if ($row['committee'] == 0){
			// Hide the certificate of current job holder
				echo "<input type='hidden' id='cert_$id' value='$cert' />";
				// Display column 2 for D5 Jobs 
				echo "<td id='c2_$id'>";
				// Display the full squadron name of member holding this job 
				// echo "<input type='text' id='mbr_$id' readonly border='0' value='";
				if ($row['certificate']!='')
					echo $exc->get_d5_member_name(false, $row); 
				// echo "' size='30' maxlength='50' border='0' />";
				echo "</td>";
				// Display column 3 
				echo "<td id='c3_$id' ><button type='button' id=del_$id onclick='btn_del_job_assignment($id);' ";
				echo "title='Delete the member shown from this position' >";
				echo "<img src='../images/close.gif'></button></td>";
				// Display column 4 
				echo "<td id='c4_$id' ><button type='button' id=add_$id onclick='btn_add_job_assignment($id);' ";
				echo "title='Assign this job to a D5 Member.  First select the member from the D5 Roster.  A new job entry will be created if a member is already assigned to the job.' ";
				echo "><img src='../images/right.gif'></button></td>";
			}elseif ($row['committee']==3){
				echo "<td id='c2_$id'></td>";
				echo "<td id='c3_$id'></td>";
				echo "<td id='c4_$id'></td>";
			}else{
				echo "<td id='c2_$id'>";
				if ($row['committee']==1)
					echo $exc->get_d5_member_name(false, $row);
				else
					echo "Select 'Show' to list."; 	
				echo "</td>";
				// Display a button that will link to the 'show_committee' page
				echo "<td id='c3_$id'><button type='button' id='cmte_$id' onclick='btn_show_committee($id);' ";
				echo "title='Switches focus to the Specify Committee Assignments Page. ' ";
				echo " >Show</button></td>";
				echo "<td id='c4_$id'></td>";
			}
		// Display column 5  
		echo "<td id='c5_$id'><button type='button' id='define_$id' onclick='btn_define_jobcode($id);' ";
		echo "title='Switches focus to the Setup Jobcode Description Page where you may modify the name and parameters of this jobcode. ' ";
		echo " ><img src='../images/down.gif' /button></td>";
		echo "</tr>";
	}}
	echo "</table>";
}
//****************************************************
function update_committee($pst, $year){
	//  Store all committee data and return 
	global $jobs;
	// Delete existing committee jobs 
	if (isset($pst['jobcode_chair'])){
		$range = "jobcode=".$pst['jobcode_chair'];
		$range .= " and year='$year'";
		$jobs->delete_range($range);
	} 
	if (isset($pst['jobcode_asst'])){
		$range = "jobcode=".$pst['jobcode_asst'];
		$range .= " and year='$year'";
		$jobs->delete_range($range);
	} 
	if (isset($pst['jobcode_member'])){
		$range = "jobcode=".$pst['jobcode_member'];
		$range .= " and year='$year'";
		if (isset($pst['squad_no']))
			$range .= " and squad_no=".$pst['squad_no'];
		$jobs->delete_range($range);
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
				$jobs->add_record($row);
			}
		}
	}
}
//****************************************************
function update_jobcode($pst){
	// Updates a jobcode entry from the $_POST array contents
	if ($pst['committee']==1 and substr($pst['jobcode'],4,1)!=0){
		return "A jobcode for a 'Traditional Committee' must end in 0.  Suggest you contact webmaster for assistance,";
	}
	if (isset($pst['d5_job'])) 
		$pst['d5_job'] = 1; 
	else 
		$pst['d5_job'] = 0;
	if ($pst['committee'])
		$pst['committee_code'] = $pst['jobcode'];
	$check = $this->get_record('jobcode',$jc=$pst['jobcode']);
	if ($check){
		$this->update_record('jobcode',$pst);
	}else{
		$this->add_record($pst);
	}
	if ($pst['committee']==1){
		//  We must update Asst & Member records
		$pst['d5_job'] = 0;
		$pst['committee'] = 3;
		$names = array(1=>'Asst. ', 2=>'Member ');
		foreach($names as $key=>$pfx ){
			$ary = $pst;
			$ary['jobcode'] = $pst['jobcode'] + $key;
			$ary['jdesc'] = $pfx . $pst['jdesc'] ;
			$check = $this->get_record('jobcode',$ary['jobcode']);
			if ($check){
				$this->update_record('jobcode',$ary);
			}else{
				$this->add_record($ary);
			}		
		}
	} else {
		//  We must delete Asst & Member records
		$jc = $pst['jobcode'];
		if (substr($jc,4,1)==0){
			$this->delete_records('jobcode',$jc+=1);
			$this->delete_records('jobcode',$jc+=1);
		}
	}
}
} // End of class Table
?>