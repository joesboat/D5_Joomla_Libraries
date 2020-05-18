<?php
require_once('eventRoutines.php');
require_once('emailRoutines.php');
require_once('formRoutines.php');
require_once('squadRoutines.php');
require_once('loginRoutines.php');
require_once('loggingRoutines.php');
$xyz = JPATH_LIBRARIES;
require_once(JPATH_LIBRARIES."/usps/Joes_factory.php");
require_once(JPATH_LIBRARIES."/usps/tableAccess.php");
require_once(JPATH_LIBRARIES."/usps/includes/textRoutines.php");
require_once(JPATH_LIBRARIES."/usps/includes/tableD5Awards.php");
require_once(JPATH_LIBRARIES."/usps/includes/tableCourses.php");
require_once(JPATH_LIBRARIES."/usps/includes/tableConvert.php");
require_once(JPATH_LIBRARIES."/usps/includes/tableD5Blobs.php");
require_once(JPATH_LIBRARIES."/usps/includes/tableD5Events.php");
require_once(JPATH_LIBRARIES."/usps/includes/tableD5Locations.php");
require_once(JPATH_LIBRARIES."/usps/includes/tableD5Squadrons.php");
require_once(JPATH_LIBRARIES."/usps/includes/tableJobs.php");
require_once(JPATH_LIBRARIES."/usps/includes/tableJobcodes.php");
require_once(JPATH_LIBRARIES."/usps/includes/tableExcom.php");
require_once(JPATH_LIBRARIES."/usps/includes/tableListOrder.php");
require_once(JPATH_LIBRARIES."/usps/includes/tableMembers.php");
require_once(JPATH_LIBRARIES."/usps/includes/tableRank.php");
require_once(JPATH_LIBRARIES."/usps/includes/tableAddresses.php");
require_once(JPATH_LIBRARIES."/usps/includes/tablePeople.php");
require_once(JPATH_LIBRARIES."/usps/includes/tableAssociates.php");
require_once(JPATH_LIBRARIES."/usps/includes/tableRegistrations.php");
require_once(JPATH_LIBRARIES."/usps/includes/tableAttendees.php");


//*********************************************************
function build_link($name,$email){
	if ($email=='')
		return $name;
	$str = "<a href='mailto:$email'>";
	$str .= $name;
	$str .= "</a>";
	return $str;	
}
//*********************************************************
function getGradeBOC($row){
	
	$boc_levels = array(
		'BOC-IN'=>"Inland Navigator",
		'BOC-CN'=>"Coastal Navigator",
		'BOC-ACN'=>"Advanced Coastal Navigator",
		'BOC-ON'=>"Offshore Navigator",
		'BOC-SA'=>"",
		'BOC-CAN'=>"",
		'BOC-IW'=>"",
		'BOC-PAD'=>""
	);
	$boc_fields = array(
		0=>'BOC_IN',
		1=>'BOC_CN',
		2=>'BOC_ACN',
		3=>'BOC_ON'
	);
	if (!$row['grade'])
		return "";
	$str = ", ".$row['grade'];
	$s = '';
	foreach($boc_fields as $fld){
		if ($row[$fld] != ''){
			$sa = explode('BOC_',$fld);
			$s = '-'.$sa[1];
		}	
	}
	$str .= $s;	
	return $str;
	
//	IN	 Inland Navigator 
//	CN	 Coastal Navigator 
//	ACN	 Advanced Coastal Navigator 
//	ON	 Ocean Navigator 
	
}
//*********************************************************
function get_citystatezip_line($mbr){
	return $mbr['city'].', '.$mbr['state'].' '.$mbr['zip_code'];
}
//*********************************************************
function get_excom_member_data($jobcode,$year){
global $vhqab ;
$exc = $vhqab->getExcomObject();
	// Queries excom to find cert. number for a jobcode and then 
	// obtains that members record.
	$query = "jobcode='$jobcode' and year='$year'" ;
	$ex_row = $exc->search_record($query);
	$cert = $ex_row['certificate'];
	$row=$vhqab->getD5member($cert);
	return $row ; 
}
//*********************************************************
function getMemberNameAndGrade($row, $with_link = false){
	$str = "";
	$with_link = $with_link and ($row['email']!="");
	if ($with_link){
		$str .= "<a href='mailto:".$row['email']."'>";
	}
	if (($name = get_person_name($row)) == ''){
		return '';
	} else {
		$str .= $name ;
	}
	if ($with_link) 
		$str .= "</a>";
	if ($row['grade'])
		$str .= ", ".$row['grade'];
	return $str;
}
//*********************************************************
function get_nat_member_name($row,$b = false){
global $vhqab;
	$rnk = $vhqab->getRankObject();
	$str = "";
	$rank = $rnk->get_nat_rank($row);
	if ($rank != '')
		$str .= "$rank  ";
	$str .= getMemberNameAndGrade($row,$b); 
	return $str;		
	
	
}
//*********************************************************
function get_person_sirname($row){
	if (isset($row['last_name'])){
		if ($row['last_name'] == '') return '';
		$last = $row["last_name"];			// D5 Name Format
		$first = $row["first_name"];
		$nick = $row['nickname'];
		$nickprf = $row['nn_prf'];
	} elseif(isset($row['last'])){
		if ($row['last'] == '') return '';
		$last = $row["last"];				// USPS Name Format
		$first = $row["first"];
		$nick = $row["nick"];
		$nickprf = ($row['nickpref'] == 'Y');
	} else {
		return "";
	}
	if ($nickprf == 1){
		return   $last.", ".$nick ;
	}else{
		return $last.", ".$first  ;
	}	
}
//*********************************************************
function get_person_name($row){
	if (isset($row['last_name'])){
		if ($row['last_name'] == '') return '';
		$last = $row["last_name"];			// D5 Name Format
		$first = $row["first_name"];
		$nick = $row['nickname'];
		$nickprf = $row['nn_prf'];
	} elseif(isset($row['last'])){
		if ($row['last'] == '') return '';
		$last = $row["last"];				// USPS Name Format
		$first = $row["first"];
		$nick = $row["nick"];
		$nickprf = ($row['nickpref'] == 'Y');
	} else {
		return "";
	}
	if ($nickprf == 1){
		return $nick ." ". $last;
	}else{
		return $first ." ". $last;
	}
}
//*********************************************************
function get_person_name_with_link($row){
	return build_link(get_person_name($row), $row['email']);
/*	- Original Code 
	if ($row['email']!=''){
		$str = "<a href='mailto:".$row['email']."'>";
		$str .= get_person_name($row);
		$str .= "</a>";
		return $str;
	}else 
		return get_person_name($row);
*/
}
//*********************************************************
function get_phone_number($row){
	if (strlen($row['phone']) != 7)
		return "";
	$area = $row['area'];
	$phone3 = substr($row['phone'],0,3);
	$phone4 = substr($row['phone'],3,4);
	return "($area)$phone3-$phone4" ;
}
//*************************************************************************
function getSiteUrl(){
	return "http://".$_SERVER['SERVER_NAME'].$_SERVER['CONTEXT_PREFIX'];
}
//*************************************************************************
function getDestFromConfiguration(){
	$config = file_get_contents(JPATH_BASE."/configuration.php");
	// if ($config) log_it ("Configuration.php file opened.",__LINE__);
	$a_config = explode("public",$config);
	foreach ($a_config as $setting){
		$setting = trim($setting);
		if ($setting == '') continue;
		if (substr($setting,0,1)=='<') continue;
		$a_set = explode(" = ",$setting);
		$a_param = explode("$",$a_set[0]);
		$param = trim($a_param[1]);
		$a_value = explode("'",$a_set[1]);
		$dest[$param] = trim($a_value[1]); 
		if ($param == 'secret') break;
	}
	$desti["dbname"] = $dest["db"];
	$desti["username"] = $dest["user"];
	$desti["password"] = $dest["password"];
	$desti["dbprefix"] = $dest["dbprefix"];
	if ($dest["host"] == 'localhost'){
		$desti["hostname"] = '127.0.0.1';
	} else {
		$desti["hostname"] = $dest["host"];
	}
	// write_log_array($desti, "desti array");
	return $desti;
	
}
//*********************************************************
function make_standard_date($str){
	$date = strtotime($str) ; 
	$date_str = date("Y-m-d h:i:s",$date);
	return $date_str ;
}
//*************************************************************************
function setup_curl(){
	$ch = curl_init();
	if(! $ch){
		return false;
	}
	$hdr = array('Content-type: application/x-www-form-urlencoded');
	$xx = "-//W3C//DTD XHTML 1.0 Transitional//EN";
	$agnt = "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; Win64; x64; Trident/4.0; GTB7.4; .NET CLR 2.0.50727; SLCC2; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; HPNTDF; Tablet PC 2.0; .NET4.0E; .NET4.0C)";
	// $agnt = "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; WOW64; Trident/4.0; GTB7.3; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; HPNTDF; .NET4.0C; .NET4.0E";
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT,$agnt);	
	curl_setopt($ch, CURLOPT_HTTP_VERSION,'CURL_HTTP_VERSION_1_1');
	curl_setopt($ch, CURLOPT_HTTPHEADER,$hdr); 
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_AUTOREFERER, true);
	curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
	curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
	return $ch;
}
//*************************************************************************
function send_get($ch,$url){
	$string = "";
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, false);
	curl_setopt($ch, CURLOPT_HTTPGET, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,TRUE );
	curl_setopt($ch, CURLOPT_POSTFIELDS, "");
	if (! $output = curl_exec($ch)){
		log_it($output);
		return false;
	}
	return $output;
}
?>