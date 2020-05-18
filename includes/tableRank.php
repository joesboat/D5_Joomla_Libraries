<?php
require_once(JPATH_LIBRARIES."/usps/tableAccess.php");
//************************************************************************************
class tableRank extends USPStableAccess{
	// Generic routines to manage a table 
//********************************* Public Variables **********************************
var $loging; 
//********************************* Private Variables *********************************
//********************************************************************************************
function __construct($db, $caller=''){
	// Creates the variables to contain identity of data and tables 
	parent::__construct('ranks', $db, $caller); 
		//$this->cols=$col_list;		
}// constructer
//*************************************************************
function check_squadron_commander($certificate,$squad_no,$rank,$year){
global $jobs;
	$PC = 100;
	$d_rank = 100;
	$query = "certificate = '$certificate' ";
	$query .= " and year = '$year' ";
	$query .= " and jobcode like '3%'";
	$rows = $jobs->search_records_in_order($query,'jobcode');
	if (count($rows) > 0){
		foreach($rows as $row){
			switch($row['jobcode']){
				case '31000':
					return '06';
			}
		}
	}
	return min($d_rank,$PC);
}
//*********************************************************
public function get_d5_rank($row){
global $year;
	if (trim($row['certificate']) == 'E094362' ){
		return 'P/R/C';
	}
	$d_code = $this->get_district_rank($row['certificate'],$year);
	$n_code = $this->get_national_rank($row['certificate'],$row['hq_rank'],$year);
	return $this->get_RankAbbr(min($n_code,$d_code));
}
//**************************************************************************
function get_district_rank($certificate,$year){
global $jobs;
$PDC = false;
	$query = "certificate = '$certificate' ";
	$query .= " and year = '$year' ";
	$query .= " and jobcode like '2%'";
	$rows = $jobs->search_records_in_order($query,'jobcode');
	if (count($rows)>0){
		foreach($rows as $row){
			switch($row['jobcode']){
				case '28070':
					return '17';
				case '21600':
				case '21610':
					return 54;
				case '21630':
					return 60;
				default:
			}
		}
		return '12';
	}
	return 100;
}
//**************************************************************************
function get_national_rank($certificate,$rank,$year){
global $jobs;
	$query = "certificate = '$certificate' ";
	$query .= " and year = '$year' ";
	$query .= " and jobcode like '1%'";
	$rows = $jobs->search_records_in_order($query,'jobcode');
	if (!$rows or (count($rows)!=0))  
		foreach($rows as $row){
			switch($row['jobcode']){
				case '10004':
					return '03';
				case '10005':
					return '05';
				default:
			}
		}
	if (substr($rank,0,1) == 'P')
		return $this->get_RankCode($rank);
	else
		return 100;
}
//*************************************************************
function get_RankCode($rank){
	$rows = $this->get_records('RankAbbr',$rank);
	return $rows[0]['RankCode']; 
}
//*************************************************************
function get_RankAbbr($code){
	$row = $this->get_record('RankCode',$code);
	return $row['RankAbbr']; 
}
//*********************************************************
public function get_nat_rank($row){
global $year;
	if ($row['last_name']=='Venables'){
		$hq = $row['hq_rank'] ;
		$sq = $row['sq_rank'] ;
		$hq = $row['hq_rank'] ;
	}
	$s_code = $this->get_national_rank($row['certificate'],
										$row['hq_rank'],
										$year);
	return $this->get_RankAbbr($s_code);
		//select(true){
		//case:($s_code > )	
}
//*********************************************************
public function get_sq_rank($row){
global $year;
	if ($row['last_name']=='Venables'){
		$hq = $row['hq_rank'] ;
		$sq = $row['sq_rank'] ;
		$hq = $row['hq_rank'] ;
	}
	// Now determine if member is a squadron commander 
	$s_code = $this->get_squadron_rank($row['certificate'],
										$row['squad_no'],
										$row['sq_rank'],
										$year);
	return $this->get_RankAbbr($s_code);
		//select(true){
		//case:($s_code > )
}
//*************************************************************
function get_squadron_rank($certificate,$squad_no,$rank,$year){
global $jobs;
	$PC = 100;
	$d_rank = 100;
	$query = "certificate = '$certificate' ";
	$query .= " and year = '$year' ";
	$query .= " and jobcode like '3%'";
	$rows = $jobs->search_records_in_order($query,'jobcode');
	if (count($rows) > 0){
		foreach($rows as $row){
			switch($row['jobcode']){
				case '31000':
					return '06';
				case '32000':
				case '33000':
				case '34000':
				case '35000':
				case '36000':
					$PC = min('21',$PC);
					break;
				case '32001':
				case '33001':
				case '34001':
				case '35001':
				case '36001':
					$PC = min('22',$PC);
					break;
				case '31600':
				case '31610':
					$PC = min('61',$PC);
					// return '61';
					break;
				case '21600': 
					return '54';
					break;
				default:
			}
		}
		if ($PC < 100)
			return $PC ;
		return '23';
	}
	if (substr($rank,0,1) == 'P')
		$d_rank = $this->get_RankCode($rank);
	return min($d_rank,$PC);
}
//**************************************************************
}// class
?>