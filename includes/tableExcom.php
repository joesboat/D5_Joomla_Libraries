<?php
require_once(JPATH_LIBRARIES."/usps/tableAccess.php");
class tableExcom extends USPStableAccess{
//*********************************************************
function __construct($db, $caller=''){
		// Creates the variables to contain identity of data and tables 
	parent::__construct("d5_excom",$db, $caller);
		//$this->list_cols=$col_subset; 
		//$this->cols=$col_list;		
} // constructer

//*********************************************************
function excom_member($cert, $col, $year){
/*	Confirms the calling member is authorized to access a form! 
	If member is in 'excom': 
		if $col = "" return true 
		Otherwise return value of column $col
*/
	// if ($cert == 'E208009') return true ;
	$rows = array();
	$query="certificate='$cert' and year='$year'";
	$rows = $this->search_records_in_order($query);
	if ($rows == NULL){
		return false;
		exit(0);
	}
	if ($col == "") return true;
	foreach($rows as $row){
		if ($row[$col] == 1){
			return true;
			exit(0);  
		}
	}
	return false;
}
//*********************************************************
function format_excom_data_row($pos){ 
// Queries excom & members to format an entry
// in the Executive Committee Positions table 
//
//	Use $pos (excom_pos column) to obtain the position rcord
//	Follow the 'certificate' relation to obtain the member record
//	Format the table row entry as follow: 
//	<tr><td> 'picture' </td>
//		<td> 'excom/excom_position'</td>
//		<td> 'sq_rank' 'first_name' 'last_name' 'grade' </td>
//		<td> 'excom/excom_email'</td>
//		<td> 'telephone'</td></tr>		
//	Returns entry to calling page using PHP echo. 
	global $mbr; 
	$xcom = $this->get_record("excom_pos",$pos);
	$cert = $xcom['certificate'] ;
	$member=$mbr->get_mbr_record($xcom['certificate']);
	
	// Make sure "rank" is correct
	if (($member['rank'] == "P/C")||
		($member['hq_rank'] == "P/C" )||
		($member['sq_rank'] == "P/C" )){
			$rank = "P/C" ;
		} elseif($excom['excom_rank']="Lt" && $member['rank'] <> NULL){
			$rank = $member['rank'];
		} else {
			$rank = $xcom['excom_rank'];
		}
	if ($member['certificate'] == NULL){
		$str = "<tr><td></td>" ; 
		$str .= "<td>" . $xcom['excom_position'] . "</td>" ;
		$str .= "<td> Vacant </td>" ; 
		$str .= "<td> </td>" ; 
		$str .= "<td> </td>" ; 
	}
	if ($member['picture'] == NULL){
		$str = "<tr><td></td>" ; 
	}else{
		$str = "<tr><td><img class=\"t10img\" src=\"php/get_pic.php?" ;
		$str .= "p_exec=" . $cert . "\"/></td>" ; 
	}
	$str .= "<td>" . $xcom['excom_position'] . "</td>" ;
	$str .= "<td>" . $this->get_d5_member_name(false,$member) . "</td>" ;  
	$str .= "<td><a class='lnk_normal' href='mailto:".$xcom['excom_email']."@D5online.org'>".$xcom['excom_email']."</a></td>" ;
	// $str .= "<td>" . $xcom['excom_email'] . "</td>" ;
	$str .= "<td>" . $member['telephone'] . "</td></tr>" ; 
	echo $str ;
}
//*********************************************************
function get_department_list($year){
	// Obtains a list of members
	// Don't select webmaster or immediate past commander
	//$select = "jobcode != '25710' and jobcode != '21610'";
	//$array = $this->search_records_in_order($select,"excom_position");
	$array = $this->get_excom_list($year);
	foreach($array as $key=>$value){
		if ($value['jobcode']=='25710') continue;	// Webmaster 
		if ($value['jobcode']=='21610') continue;	// Immediate Past Commander  
		$ary[$value['jobcode']] = $value['excom_position'];
	}
	return $ary;
}
//*********************************************************
function get_excom_jobcode($cert,$year){
// Evaluate EXCOM list for officer or asst officer 
	$query = "certificate = '$cert' and year='$year'";
	$query = "$query and (jobcode like '__00_')";
	$excom_row = $this->search_record($query);
	if ($excom_row)
		return $excom_row['jobcode'];
	else
		return FALSE;	
}
//*********************************************************
function get_excom_list($year){
	// returns all records from excom table 
	return $this->search_records_in_order("year='$year'",'excom_rank_order');	
}
//*********************************************************
function get_highest_permission($cert,$year){
global $jobs;
	$query = "certificate='$cert'"; 
	$query .= " and year='$year'";   //  and jobcode != '25710'";
	$rows = $this->search_records_in_order($query,'jobcode');	
	//if (!$rows){
	//	$squad_no = sprintf("%04d",$_SESSION['squad_no']);
	//	if ($jobs->check_squadron_seo($cert, $squad_no, $year))
	//		return true;
	//	else
	//		return false;
	//}
	return $rows[count($rows)-1];
}
//*********************************************************
function get_d5_member_name($b,$row){
global $vhqab;
if (! isset($row['certificate'])){
	$xyz = $row;
}
	$rnk = $vhqab->getRankObject();
	$str = "";
	if (!($rank = $this->get_excom_rank($row['certificate']))){
		$rank = $rnk->get_d5_rank($row);
	}
	if ($rank != '')
		$str .= "$rank  ";
	$str = getMemberNameAndGrade($row,$b); 
	return $str;	
}
//*********************************************************
function get_excom_rank($cert){
global $year;
	$query = "certificate='$cert' and year = '$year'";
	$rows = $this->search_records_in_order($query,'year');	
	if ($rows){
		$r = $rows[count($rows)-1];
		return $r['excom_rank'];
	}
	return '';
}
//*********************************************************
function get_permission($row){
	// Returns department number of officer or false 
	// $row is assumed to be an excom row. 
	switch ($row['jobcode']){
		case "21000":			// Commander
		case "22000":			// Executive Officer
		case "23000":			// Educational Officer
		case '24000':			// Administrative Officer
		case '24001':			// Asst. Administrative Officer
		case "25000":			// Secretary
		case '25001':			// Asst. Secretary
		case "26000":			// Treasurer
		case "25710":			// Webmaster
			return $row['jobcode'];
			break;
		default:
			return false;
			break;
	}
	return false;
}
//*********************************************************
function get_officers($year){
global $vhqab;
	$ary=array();
	$array = $this->get_excom_list($year);
	foreach($array as $key=>$value){
		if ($value['year'] != $year) continue;
		if ($value['jobcode']=='25710') continue; 
		if ($value['jobcode']=='20300') continue; 
		if ($value['jobcode']=='20400') continue; 
		if ($value['jobcode']=='20600') continue; 
		if ($value['jobcode']=='28050') continue; 
		$ary[] = $value;
	}
	return $ary;
}
//*********************************************************
function is_commander(){
global $year;
	$webmaster = $this->excom_member($_SESSION['user_id'],"webmaster",$year);
	$jobcode = $this->get_excom_jobcode($_SESSION['user_id'],$year);
	$allowed = ($jobcode == '21000' or $jobcode == '22000');
	if (($allowed or $webmaster))
		return true;
	else
		return false;
}
//*********************************************************
function is_webmaster($cert){
global $year;
	$webmaster = $this->excom_member($cert,"webmaster",$year);
	$jobcode = $this->get_excom_jobcode($cert,$year);
	$allowed = ($jobcode == '21000' or $jobcode == '22000');
	if (($allowed or $webmaster))
		return true;
	else
		return false;
}
//*********************************************************
function show_d5_department_dd_list_box($departments,$dept){
	echo "<select name='department' id='department' width='50'>;" ;
	show_option_list($departments,$dept);
	echo "</select>";
}
//*********************************************************
function show_d5_member_dd_list_box($officers,$cert){
	echo "<select name='certificate' id='certificate' width='50'>;" ;
	show_option_list($officers,$cert);
	echo "</select>";
}
//*********************************************************
public function show_full_bridge($year){
global $mbr; 
	echo "<input type='text' name='year' value='$year'><br/>"; 
	$rows=$this->search_records_in_order("excom_position!='Webmaster'","jobcode"); 
	echo "<table>";
	echo "<tr><th>Position:</th><th>Certificate:</th><th>Name:</th></tr>";
	foreach($rows as $row){
		echo "<tr>";
		echo "<td>".$row['excom_position']."</td>";
		echo "<td><input type='text' name='".$row['jobcode']."' value='".$row['certificate']."'></td>";
		echo "<td>".
			$this->get_d5_member_name(true,$mbr->get_mbr_record($row['certificate'])).
			"</td>";
		echo "</tr>";
	}
	echo "</table>";
}
//*********************************************************
public function update_full_bridge($pst){
global $jobs;
	// Passed the $_POST array as $pst
	// Updates table
	// $pst fields corrospond to table setup in show_full_bridge()
	foreach($_POST as $key=>$item){
		$item = strtolower($item);
		if ($key == 'command') continue;
		if ($key == 'year') continue;
		//  Update jobs table
		$jobs->add_member($key,$item,$_POST['year']);
		//  Update d5_excom 
		$ary['certificate']=$item;
		$ary['jobcode']=$key;
		$this->update_record('jobcode',$ary);
	}
}

} // end of table_excom 
?>