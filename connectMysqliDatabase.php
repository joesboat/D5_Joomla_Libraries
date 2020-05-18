<?php
/**
 * @package		USPS Libraries 
 * @subpackage	connectMysqliDatabase.php
 * @purpose		Low level PHP Library routines to interface with MySql database
 * @copyright	Copyright (C) 2015 Joseph P. Gibson. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
*/
require_once ("Joes_factory.php");
class connectMysqliDatabase{	// All access 
//********************************* Public Variables *****************************************
public $db_name;
//********************************* Private Variables ****************************************

//********************************************************************************************
function __construct($db){
	$this->connection = JoeFactory::getDatabase($db);
	$this->db_name = $db["dbname"];
} // constructer
//******************************************************************************
function close(){
	//parent::close($this->thisdb);
	// mysqli_close($this->connection);
	return;
}
//*********************************************************************
protected function do_query($query){
//Read records from table to determine column order

	$result = $this->connection->query($query);
	if (!$result){
		printf("Invalid query: %s\nWhole query: %s\n", 
					$this->connection->error, $query);
	}
	return $result;
}
//****************************************************************
protected function do_safe_query($query){
//Read records from table to determine column order.
//Only returns to caller if query is valid.

	// Execute Query
	$result = $this->connection->query($query);
	if (!$result){
		printf("Invalid query: %s\nWhole query: %s\n", 
					$this->connection->error, $query);
		exit(0);
	}
	return $result;
}
//******************************************************************************
function get_next_record($x){
	$row = $x->fetch_assoc();
	return $row;
}
//******************************************************************************
function get_query_count($xxx){
	return $xxx->num_rows;
}
//*********************************************************************
function get_tables(){
	$rows = array();
	$query = "SHOW TABLES";
	$result = $this->connection->do_query($query);
	if (!$result){
		return false;
	}
	while ($row = $mbr->get_next_record($result)){
		$rows[count($rows)] = $row;
	}
	return $rows;
}
//******************************************************************************
function setup_table($query){
	return $this->connection->do_query($query);
}
//******************************************************************************
function table_exists($table){
	$nm = $this->db_name;
	$result = $this->connection->do_query('show tables');
	while ($row = $this->get_next_record($result)){
		$tbl = $row["Tables_in_$nm"];
		if  (trim(strtolower($tbl)) == trim(strtolower($table)))
			return true;
	}
	return false;
}
}
?>