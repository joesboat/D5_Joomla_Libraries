<?php
require_once(JPATH_LIBRARIES."/usps/includes/routines.php");
//*******************************************************************************
class tablePeople extends USPStableAccess{
	// Generic routines to manage a table 
//********************************* Public Variables *****************************
//********************************* Private Variables ****************************
//private $manager ;
//********************************************************************************
function __construct($database, $db, $caller=''){
		// Creates the variables to contain identity of data and tables 
	parent::__construct($database, $db, $caller='');
		//$this->list_cols=$col_subset; 
		//$this->cols=$col_list;		
} // constructer
//**************************************************************************
function membership_col_to_db_col($input_col){
	//  Creates a this table column name
	//  $input_col is a column heading in import file
	$temp = trim(strtolower(str_replace('"','',$input_col)));
	$temp = str_replace(" ","_",$temp); 
	if ($temp == "e-mail") $temp = "email";
	return $temp ;
}
} // End of class Table
?>