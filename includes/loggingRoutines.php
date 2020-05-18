<?php
/**
 * @package		USPS  Libraries 
 * @subpackage	loggingRoutines.php - .
 * @copyright	Copyright (C) 2015 Joseph P. Gibson. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
*/
//*********************************************************
function log_column_changes($fh,$table,$table_key,$row,$dif){
	$eol = "\r\n" ;
	foreach($dif as $col=>$new){
		$old = $row[$col];
		fwrite($fh,"$table,$table_key,$col,$old,$new".$eol);
	}
}
//*********************************************************
function log_continue($fh,$str){
	$eol = "\r\n" ;
	$i = fwrite($fh,"    $str $eol");
}
//*********************************************************
function log_deleted_member_records($rows,$file_name="logs/session_log.txt"){
	$eol = "\n" ;
	$fh = write_log();	
	$str = "Deleting the following member records:";
	$i = fwrite($fh,$str.$eol);
	foreach($rows as $row){
		$str = "    ".$row['certificate']." ".get_person_name($row)." ".$row['squad_code']; 
		$i = fwrite($fh,$str.$eol);
	}
}
//*********************************************************
function log_it($str,$line=''){
	$eol = "\r\n" ;
	$fh = write_log();
	if ($line != '')
		$str = "$str  - on line $line "	;
	$i = fwrite($fh,$str.$eol);
	return $fh;
}
//*********************************************************
function log_paypal_return($data){
	$eol = "\n" ;
	$fh = write_log("paypal/paypal_log.txt");
	$str = "";
	foreach($data as $key=>$value){
		$str = "    ".$key . " = " . $value . "   " ;
		$i = fwrite($fh, "    ". $str.$eol);	//$gd = gd_info() ;
	}
	fclose($fh);
}
//*********************************************************
function log_record_updates($obj,$col,$key,$array,$file_name="logs/session_log.txt"){
	$eol = "\n" ;
	// Create a log entry to record a database change
	// Parameters:
	//	$obj	The object managing the database table 
	//	$col	The controlling column ($key) of table 
	//	$key	The $key
	//	$array	The array listing changed columns 
	$fh = write_log();
	//fwrite($fh,"Table ".$obj->table_name." has been updated for $col=$key as follows:".$eol);
	foreach($array as $k=>$v){
		fwrite($fh,"    $k is set to '$v'".$eol);		
	}
}
//*********************************************************
function write_connect_record($row,$file_name="logs/session_log.txt"){
	// Called to report automatic user log-in 
	;
	$eol = "\n";
	if (isset($_SESSION['debug'])){
		if ($_SESSION['debug'] == true)
			$fh=write_special_log($_SERVER,$_SESSION,$_POST,$_GET,$_REQUEST,$file_name);
		else
		 	$fh=write_log();
	}else
		$fh=write_log();
	$i = fwrite($fh, "Member " . $row['certificate'] . " returned using session cookie." . $eol);
	$i = fwrite($fh, get_person_name($row) . $eol);
}
//*********************************************************
function write_log(){
//	- Stores information about the session in main log file.   	
	$eol = "\r\n" ;
	$filename = JPATH_BASE ."/logs/session_log.txt";
	//$filename = "/web/joomla/logs/session_log.txt";
	$fh = fopen($filename, 'a') ; 
	if (!$fh){
		printf("Can't open the $filename file") ;
		exit(0) ;
	}
	// OK we have a file, now let's append the data
	date_default_timezone_set("America/New_York");
	$str = date("l F j, Y - g:i:s.ua"); 
	$i = fwrite($fh, $eol) ;
	$i = fwrite($fh, $str . $eol) ; 
	return ($fh) ;
}
//*********************************************************
function write_log_array($ary,$str='',$line=''){
	$eol = "\r\n" ;
	$fh = write_log();
	If ($line != '')
		$str = "$str  - on line $line";
	if ($str != '')
		log_continue($fh,$str.$eol );
	write_array($fh, $ary);
}
//*********************************************************
function write_array($fh,$ary,$indent=''){
	$eol = "\r\n" ;
	foreach ($ary as $key=>$str){
		if (is_array($str)){
			fwrite($fh,$indent."Element $key is an array.$eol");
			write_array($fh,$str,'    ');
		} else 
			fwrite($fh,"$indent    $key => $str.$eol");
	}
}
//*********************************************************
function write_login_record($row,$file_name="logs/session_log.txt"){
global $me;
	$eol = "\n" ;
	$fh = write_log() ;
	$name = get_person_name($row);
	$cert = $row['certificate'];
	$i = fwrite($fh, "Member $name ($cert) validated using routine $me." . $eol) ; 	
	$i = fwrite($fh, get_person_name($row) . $eol) ; 	
}
//*********************************************************
function write_password_change_record($row,$file_name="logs/session_log.txt"){
	$eol = "\n" ;
	$fh = write_log() ;
	$i = fwrite($fh, "Member " . $row['certificate'] . " changed password." . $eol) ; 	
	$i = fwrite($fh, get_person_name($row) . $eol) ; 	
}
//*********************************************************
function write_password_log($pw, $row,$file_name="logs/session_log.txt"){
	$eol = "\n" ;
	$fh = write_log() ;
	$i = fwrite($fh, "Password Issued" . $eol) ; 	
	$str = "    " ;
	$str .= get_person_name($row) . ", "; 
	$str .= $row['certificate'] . " = " . $pw ; 
	$i = fwrite($fh, $str . $eol) ; 
	fclose($fh) ;
}
//*********************************************************
function write_password_request_log($row,$response_file,$file_name="logs/session_log.txt"){
	$eol = "\n" ;
	$fh = write_log() ;
	$i = fwrite($fh, "Password EMail Request Sent." . $eol) ; 	
	$i = fwrite($fh, "Response File is $response_file.$eol") ; 	
	$str = "    " ;
	$str .= get_person_name($row) . ", "; 
	$str .= $row['certificate'] . " = " . $pw ; 
	$i = fwrite($fh, $str . $eol) ; 
	fclose($fh) ;
}
//*********************************************************
function write_session_login ($ser, $ses, $post,$file_name="logs/session_log.txt"){
	$eol = "\n" ;
	$fh = write_log();
	$i = fwrite($fh, "Session Data:" . $eol) ;
	$str = "    " ;
	foreach($ses as $key=>$value) {
		if (($key == 'user_id') || 	
			($key == 'height') ||
			($key == 'insideHeight') ||
			($key == 'width') ||
			($key == 'aspect') ||
			($key == 'browser') ||
			($key == 'CSS_Table') ){
			$str .= $key . " = " . $value ."   " ;
		}
	}
	$i = fwrite($fh, $str . $eol);
	//$i = fwrite($fh, "Screen Size & Browser Window Data:" . $eol) ;
	//foreach($post as $key=>$value) {
	//	$str = "    " . $key . " = " . $value ;
	//	$i = fwrite($fh, $str . $eol) ;
	//}
	fwrite($fh, "Selected Server Data:" . $eol) ;
	$str = "    " ;
	foreach($ser as $key=>$value){
		if (	($key == "HTTP_USER_AGENT") ||
				($key == "HTTP_COOKIE" ) ||
				//($key == "SERVER_SIGNATURE" ) ||
				//($key == "SERVER_SOFTWARE" ) ||
				//($key == "SERVER_NAME" ) ||
				// ($key == "REMOTE_PORT" ) ||
				($key == "REMOTE_ADDR" ) ) {
			$str .= $key . " = " . $value . "   " ;
		}
	}
	$i = fwrite($fh, $str . $eol) ;	//$gd = gd_info() ;
	//foreach($gd as $key=>$value){
	//	fwrite($fh, '     ' . $key . "  =  " . $value . $eol) ;
	//}
	fclose($fh) ; 
}//*********************************************************
function write_special_log($ser, $ses, $pst, $get, $req){
	$eol = "\n" ;
	$fh = write_log("logs/session_log.txt");
	$str = "";
	$i = fwrite($fh, $eol."Special Trace Log showing all Global Data".$eol.$eol) ;
	$i = fwrite($fh, "Server Array Data:" . $eol);
	foreach($ser as $key=>$value){
		$str = "    ".$key . " = " . $value ;
		$i = fwrite($fh,"    ". $str.$eol);	//$gd = gd_info() ;
	}
	$i = fwrite($fh, "Session Data:" . $eol) ;
	foreach($ses as $key=>$value) {
		$str = "    ".$key . " = " . $value;
		$i = fwrite($fh, "    ". $str.$eol);
	}
	$i = fwrite($fh, $eol."Post Array Data:".$eol) ;
	foreach($pst as $key=>$value){
		$str = "    ".$key . " = " . $value . "   " ;
		$i = fwrite($fh, "    ". $str.$eol);	//$gd = gd_info() ;
	}
	$i = fwrite($fh, $eol."Get Array Data:".$eol) ;
	foreach($get as $key=>$value){
		$str = "    ".$key . " = " . $value . "   " ;
		$i = fwrite($fh, "    ". $str.$eol);	//$gd = gd_info() ;
	}
		$i = fwrite($fh, $eol."Request Array Data:".$eol) ;
	foreach($req as $key=>$value){
		$str = "    ".$key . " = " . $value . "   " ;
		$i = fwrite($fh, "    ". $str.$eol);	//$gd = gd_info() ;
	}
	// fclose($fh) ; 
	return $fh ;
}

?>