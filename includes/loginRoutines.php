<?php
/**
 * @package		USPS  Libraries 
 * @subpackage	loginRoutines.php - .
 * @copyright	Copyright (C) 2015 Joseph P. Gibson. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
*/
//*********************************************************
function build_uspscert_cookie($creds){
//	HQ     |STAFF	
	$usr = strtoupper(trim($creds['username']));
	if (strlen($usr) < 7){
		if (strlen($usr) == 2)
			$usr = trim($creds['username']) . "|||||" ;
		if (strlen($usr) == 6)
			$usr .= "|" ;
	}
	$value = 	$usr.    
				'|'.
				strtoupper(trim($creds['password']));
	return $value;
}
//*********************************************************
function build_USPSd5_cookie($row){
	$string = $row['certificate'];
	$string .= '|';
	$string .= 1;
	$string .= '|';
	$string .= $row['squad_no'] ;
	$string .= '|';
	return $string;
}
//*********************************************************
function encrypt_password($pw){
	// $pw assumed to be an unecrypted password provided by user 
	$temp_pw = htmlentities(mysql_fix_string($pw));
	// Salts $pw with a prefix and suffix 
	$temp_pw = "jmlp" . $temp_pw . "rosa" ;
	// Encrypts the salted $pw using _____ //
	$enc_pw = md5($temp_pw) ;
	// Returns the encrypted data to caller 
	return $enc_pw ;
}
//******************************************************************
function get_cert_from_session_cookie(){
//global $joom;
require_once(JPATH_LIBRARIES."/usps/dbUSPSjoomla.php");
//$joom = new USPSdbJoomla();	// Get the main value from all cookies.
	$list = get_values_from_cookies();
	// Search joomla table __session to match values with the session_id field. 
	foreach ($list as $cookie){
		if ($rec = $joom->getSession($cookie)){
			// write_log_array($rec, "The Session is: ",__LINE__);
			if ($rec['guest'] == 0 and $rec['client_id'] == 0){
				return $rec['username'];
			}
		}
	}
	return false;
}
//*********************************************************
function getGroupId($groupName){
   $db = JFactory::getDbo();
   $select = "select id from #__usergroups where title='".$groupName."'";
   $db->setQuery($select);
   $db->query();
   $data = $db->loadObject();
   if (! $data) 
   		return null;
   $groupId = $data->id;
   return $groupId;
}
//*********************************************************
function get_session_record(){
include (JPATH_LIBRARIES.'/USPSaccess/dbUSPS.php');	
	$ses = new USPStableAccess('usps_session',$db_joomla);
	// Obtains the session identity from the cookie
	foreach ($_COOKIE as $name=>$value){
		if (strtolower(substr($name,0,4)) == 'usps' ) continue;
		//  ok, we have an unnamed cookie 
		// Finds the session record
		$session = $ses->get_record('session_id',$value); 
		if (! $session) continue;
		if ($session['guest'] != 0) continue; 
		if ($session['client_id'] == 0) return $session;
	}
	return false; 
	// Returns false if session not found
	// Returns the session record
}
//******************************************************************
function get_values_from_cookies(){
	$ary = array();
	if (!isset($_SERVER['HTTP_COOKIE'])) 
		return $ary;
	$cookies = explode(';', $_SERVER['HTTP_COOKIE']);
    foreach($cookies as $cookie) {
        $parts = explode('=', $cookie);
        if (isset($parts[1])){
        	$ary[] = $parts[1];
        }
	}
	return $ary;
}
//*********************************************************
function mysql_fix_string($string){
	if (get_magic_quotes_gpc()) 
		$string = stripslashes($string);
	return $string ;

}
//*********************************************************
function set_uspscert_cookie($creds,$remember=false){
	if ($remember){
		$timeout = time()+60*60*24*180;
	} else {
		//$timeout = time() + 86400/24;
		$timeout = time() - 42000;
	} 
	setrawcookie(	"uspscert",
					build_uspscert_cookie($creds),
					$timeout,
					'/');
} 
//*********************************************************
function set_uspsd5_cookie($row,$remember=false){
	if ($remember){
		$timeout = time()+60*60*24*180;
	} else {
		$timeout = time() - 42000;
	} 
	setrawcookie(	"uspsd5_Session",
					build_USPSd5_cookie($row),
					$timeout,
				'/');
}
//*********************************************************
function validate_user($hash, $pw){
	if (encrypt_password($pw) == $hash) 
		return true ;
	else
		return false ;
}

?>