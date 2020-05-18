<?php
/**
 * @package		USPS  Libraries 
 * @subpackage	squadRoutines.php - .
 * @copyright	Copyright (C) 2015 Joseph P. Gibson. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
*/
//*****************************************************************
function get_long_name($a_sqd, $sbl = TRUE){
// caller must set $sbl to false if we pring a chr(174) instead of &reg; symbol 
$vhqab = $GLOBALS['vhqab'] ;
	if ($sbl){
		$abc = "America's Boating Club&reg;";
	} else {
		$abc = "America's Boating Club".chr(174);
	}
	if ($a_sqd['abc_short_name']){
		return str_replace('ABC',$abc,$a_sqd['abc_short_name']);
	} else {
		return $vhqab->getSquadronName($a_sqd['squad_no']); 
	}
}
//*****************************************************************
function get_short_name($a_sqd){
	if ($a_sqd['abc_short_name']){
		return $a_sqd['abc_short_name'];
	} else {
		return $a_sqd['squad_short_name']; 
	}
}

?>