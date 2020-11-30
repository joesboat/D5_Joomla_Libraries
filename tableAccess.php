<?php
/**
 * @package        USPS Libraries 
 * @subpackage    tableAccess.php.
 * @purpose        Database table interface routines
 * @copyright    Copyright (C) 2015 Joseph P. Gibson. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */
//require_once('connect_mysql_database.php');
require_once('connectMysqliDatabase.php');
require_once('includes/formRoutines.php');
//class table_access extends connect_mysql_database{
class USPStableAccess extends connectMysqliDatabase
{
    //********************************* Public Variables ******************************
    public $cols; // Associative array holding column names 
    public $members; // Associative array where <col name>=><col type(col width)
    public $table_name;
    public $blank_record;
    public $thisdb;
    //********************************* Private Variables *****************************
    public $manager;
    //*********************************************************************************
    function __construct($table, $db, $caller = '')
    {
        //include "includes/db_usps.php";
        parent::__construct($db, "");
        // Creates the variables to contain identity of data and tables 
        $this->thisdb = $db;
        $this->table_name = $table;
        // $this->table_name=$table;
        $this->manager    = $caller; //$this->manager=$caller;
        $rows  = $this->get_columns();
        $this->cols       = $this->get_column_names($rows);
        $this->types      = $this->get_column_types($rows);
        if (!$this->cols or !$this->types)
            return false;
        $this->blank_record = $this->make_blank_record($rows);
        //$this->set_CharSet($this->charSetName);
        //$this->set_CharSet('utf8');
        //$this->list_cols=$col_subset; 
        //$this->cols=$col_list;        
    } // constructer
    //******************************************************************************
    protected function add_column($name, $size)
    {
        // Adds a new column to this table 
        $sql    = "ALTER TABLE " . $this->table_name . " ADD COLUMN $name VARCHAR($size)";
        $result = $this->do_query($sql);
        if (!$result) {
            return false;
        } else {
            $this->cols = $this->get_column_names();
            return true;
        }
    }
    //******************************************************************************
    function add_record($row)
    {
        // Confirms each element in row is a column in table
        // Creates a new record 
        $ok    = FALSE;
        $cols  = $this->cols;
        $query = "INSERT INTO " . $this->table_name . " SET ";
        foreach ($row as $key => $value) {
        	if (in_array($key, $this->cols, TRUE)) {
                $value = fix_value($value);
                $query .= "$key = '$value',";
                $ok = true;
            }
        }
        $query = substr($query, 0, strlen($query) - 1);
        if ($ok) {
            $result = $this->do_query($query);
            return $result;
        }
        return false;
    }

    //******************************************************************************
    function count($select)
    {
        $table  = $this->table_name;
        $query  = "select COUNT(0) from $table where $select ";
       	$result = $this->do_query($query);
        if (!$result)
            return false;
        $row = $this->get_next_record($result);
        //$count = $result->num_rows;
        $count = $row["COUNT(0)"];
        return $count;
    }
    //******************************************************************************
    function delete_range($select)
    {
        $rows   = array();
        $query  = "DELETE FROM " . $this->table_name . " WHERE $select";
        $result = $this->do_query($query);
        if (!$result) {
            return false;
        }
        return true;
    }
     //******************************************************************************
    function delete_records($col, $value)
    {
        $query  = "DELETE FROM " . $this->table_name . " WHERE $col = '$value' ";
        // Execute Query    
        $result = $this->do_query($query);
        if (!$result)
            printf("Invalid query: %s\nWhole query: %s\n", $result, $sql);
        else
            return true;
        // Close database connection
    }
	//******************************************************************************
    function delete_this_record($rec)
    {
        $query = '';
        foreach ($rec as $key => $value) {
            $query .= "$key = '$value' and ";
        }
        $ix    = strripos($query, ' and ');
        $query = substr($query, 0, $ix);
        $this->delete_range($query);
        return true;
    }
    //******************************************************************************
    function empty_table()
    {
        $query  = "TRUNCATE TABLE " . $this->table_name;
        $result = $this->do_query($query);
        if (!$result) {
            return false;
        }
        return true;
    }
    //******************************************************************************
function get_duplicates($field){
	// Finds and returns all records that have duplicated data in fiedl 
	$query = "SELECT * FROM ";
	$query .= $this->table_name." ";
	$query .= "GROUP BY $field HAVING count(*) >= 2 " ;
	$query .= "ORDER BY $field";
    $result = $this->do_query($query);
    if (!$result) {
        return false;
    }
    while ($row = $this->get_next_record($result)) {
		if ($row[$field] != '' )
		$rows[] = $row;
    }
    return $rows;
}
     //******************************************************************************
   function get_record($col, $value)
    {
        //     Assumes a specific record will be found
        $rows = $this->get_records($col, $value);
        if (!$rows)
            return false;
        if (count($rows) == 0)
            return false;
        if (count($rows) > 1)
            return false;
        return $rows[0];
    } // End of get_record - Searches and returns records matching $col $value
    //******************************************************************************
    function get_records($col = '', $value = '')
    {
        //    Searches and returns records matching $col $value
        //  Assumes several records will be found 
        $s    = fix_value($value);
        $rows = array();
        if ($col == "") {
            $query = "SELECT * FROM " . $this->table_name;
        } else {
            $query = "SELECT * FROM " . $this->table_name . " WHERE " . $col . "='$s'";
        }
        $result = $this->do_query($query);
        if (!$result) {
            return false;
        }
        while ($row = $this->get_next_record($result)) {
            $rows[count($rows)] = $row;
        }
        return $rows;
    }
    //******************************************************************************
    function get_partial_record($list, $col, $value)
    {
        $rows = $this->get_partial_records($list, $col, $value);
        return $rows[0];
    }
    //*****************************************************************************
    function get_partial_records($list, $col, $value)
    {
        //    Searches and returns records matching $col $value
        //     Assumes a specific record will be found
        $s    = trim($value);
        $rows = array();
        if ($col == "") {
            $query = "SELECT $list FROM " . $this->table_name;
        } else
            $query = "SELECT $list FROM " . $this->table_name . " WHERE " . $col . "='$s'";
        $result = $this->do_query($query);
        if (!$result) {
            return false;
        }
        while ($row = $this->get_next_record($result)) {
            $rows[count($rows)] = $row;
        }
        return ($rows);
    }
    // End of get_record - Searches and returns records matching $coll
    //******************************************************************************
    function get_records_in_order($col = '', $value = '', $order = '')
    {
        $rows  = array();
        $s     = fix_value($value);
        $rows  = array();
        $query = "SELECT * FROM " . $this->table_name;
        if ($col != "") {
            $query = "$query WHERE $col = '$s' ";
        }
        if ($order != '')
            $query = "$query ORDER BY $order";
        // Open Database connection
        // Execute Query
        $result = $this->do_query($query);
        //return $result;
        if (!$result) {
            return false;
        }
        while ($row = $this->get_next_record($result)) {
            $rows[count($rows)] = $row;
        }
        return $rows;
    }
    // end of get_records_in_order - Returns the full contents of table
    //******************************************************************************
    protected function get_column_names($rows)
    {
        // returns an array containing all column names
 //       $rows  = array();
        $names = array();
//        $rows  = $this->get_columns();
        if (!$rows)
            return FALSE;
        foreach ($rows as $row) {
            $names[count($names)] = $row['Field'];
        }
        return $names;
    }
    //******************************************************************************
    protected function get_column_types($rows)
    {
        // returns an array containing all column names
        //$rows = array();
        $tps  = array();
//        $rows = $this->get_columns();
//        if (!$rows)
//            return FALSE;
        foreach ($rows as $row) {
            $tpe                = explode('(', $row['Type']);
            $tps[$row['Field']] = $tpe[0];
        }
        return $tps;
    }
    //******************************************************************************
    protected function get_columns()
    {
        // Returns an array containing column data for all columns  
        $rows   = array();
        $query  = "show columns from $this->table_name";
        $result = $this->do_query($query);
        if (!$result) {
            return FALSE;
        }
        while ($row = $this->get_next_record($result)) {
            $rows[count($rows)] = $row;
        }
        return $rows;
    }
    //******************************************************************************
    protected function make_blank_record($rows)
    {
        // $rows  = $this->get_columns();
        $blank = array();
        foreach ($rows as $key => $row) {
        	if ($row['Extra'] == 'auto_increment') continue; 
        	switch(substr($row['Type'],0,3)){
				case 'int':
				case 'tin':
					$blank[$row['Field']] = 0;
					break ;
				case 'dat':
					$blank[$row['Field']] = "00/00/00";
					break; 
				case "var":
				default:
					$blank[$row['Field']] = "";
			}
        		
           
        }
        return $blank;
    }
    //******************************************************************************
    function search_distinct($select, $field = '*', $order = '')
    {
        $rows  = array();
        $query = "SELECT DISTINCT ";
        if ($field == '')
            $query .= '* ';
        else
            $query .= "$field ";
        $query .= " FROM " . $this->table_name;
        if ($select != "")
            $query .= " WHERE $select ";
        if ($order != "")
            $query .= " ORDER BY $order ";
        $result = $this->do_query($query);
        if (!$result) {
            return false;
        }
        while ($row = $this->get_next_record($result)) {
            $rows[count($rows)] = $row;
        }
        return $rows;
    }
    //******************************************************************************
    function search_first_record($select, $order)
    {
        $rs    = $this->search_records_in_order($select, $order);
        if (count($rs)==0) return $rs;
        $XX[0] = $rs[0];
        return $XX;
    }
    //******************************************************************************
    function search_for_record($select)
    {
        $rs = $this->search_records_in_order($select, "");
        if (count($rs) != 1)
            return false;
        return $rs[0];
    }
    //******************************************************************************
    function search_partial_record($list = ' * ', $select)
    {
        $rows = $this->search_partial_records($list, $select);
        if (count($rows) == 1)
            return $rows[0];
        else
            return false;
    }
    //******************************************************************************
    function search_partial_records($list = ' * ', $select = '')
    {
        $rows = array();
        if ($select == "") {
            $query = "SELECT $list FROM " . $this->table_name;
        } else
            $query = "SELECT $list FROM " . $this->table_name . " WHERE $select";
        $result = $this->do_query($query);
        if (!$result) {
            return false;
        }
        while ($row = $this->get_next_record($result)) {
            $rows[count($rows)] = $row;
        }
        return $rows;
    }
    //******************************************************************************
    function search_partial_records_in_order($list = '*', $select = "", $order = "")
    {
        $rows = array();
        if (!($result = $this->select_partial_records_in_order($list, $select, $order)))
           // return false;
           return array();
        while ($row = $this->get_next_record($result)) {
            $rows[] = $row;
        }
        return $rows;
    }
    //******************************************************************************
    function search_record($select)
    {
        // Caller knows there will only be one record.
        $rows = $this->search_records_in_order($select, "");
        if ($rows and count($rows) == 1)
        // if (count($rows) == 1)
            return $rows[0];
        else
            return false;
    }
    //******************************************************************************
    function old_search_records_in_order($select, $order = '')
    {
        $rows  = array();
        $query = "SELECT * FROM " . $this->table_name;
        if ($select != "")
            $query .= " WHERE $select ";
        if ($order != "")
            $query .= " ORDER BY $order ";
        $result = $this->do_query($query);
        if (!$result) {
            return false;
        }
        while ($row = $this->get_next_record($result)) {
            $rows[count($rows)] = $row;
        }
        return $rows;
    }
    //******************************************************************************
    function search_records_in_order($select = '', $order = '')
    {
        return $this->search_partial_records_in_order('*', $select, $order);
    }
     //******************************************************************************
    function search_partial_with_join($list='*', $select = '', $order = '', $join)
    {
        $rows  = array();
        $query = "SELECT DISTINCT $list FROM " . $this->table_name . " ";
        $query .= "$join ";
        if ($select != '')
            $query .= "WHERE $select ";
        if ($order != '')
            $query .= "ORDER BY $order ";
        $result = $this->do_query($query);
        if (!$result) {
            return false;
        }
        while ($row = $this->get_next_record($result)) {
            $rows[count($rows)] = $row;
        }
        return $rows;
    }
   //******************************************************************************
    function search_with_join($select = '', $order = '', $join)
    {
        $rows  = array();
        $query = "SELECT * FROM " . $this->table_name . " ";
        $query .= " $join ";
        if ($select != '')
            $query .= "WHERE $select ";
        if ($order != '')
            $query .= "ORDER BY $order ";
        $result = $this->do_query($query);
        if (!$result) {
            return false;
        }
        while ($row = $this->get_next_record($result)) {
            $rows[count($rows)] = $row;
        }
        return $rows;
    }
    //******************************************************************************
    function select_partial_records_in_order($list = '*', $select = "", $order = "")
    {
        //     Used when it is assumed a large number of records will be found
        //    Searches and returns a 'result' pointer
        $rows  = array();
        $query = "SELECT $list FROM " . $this->table_name;
        if ($select != "")
            $query .= " WHERE $select ";
        if ($order != '')
            $query .= " ORDER BY $order";
        $result = $this->do_query($query);
        if (!$result) {
            return false;
        }
        return $result;
    }
    //******************************************************************************
    function store_record($row, $col)
    {
        // $row must be a modified record obtained throug this->get_record
        // confirms record exists in db before update
        // overwrites existing record with $row
        $x = $this->get_record($col, $row[$col]);
        if ($x) {
            $query = "UPDATE $this->table_name SET ";
            foreach ($row as $key => $value) {
                if ($key <> $col) {
                	if (is_null($value)){
						if ($this->types[$key] == 'int'){
							$value = 0;
						} else {
							$value = '';
						}
					} else {
						$value = fix_value($value);
					}
					$query .= "$key='$value',";
                }
            }
            $query = substr($query, 0, strlen($query) - 1);
            $query .= " WHERE $col = '" . $row[$col] . "'";
        }
        $result = $this->do_query($query);
        return $result;
    }
    //*********************************************************************
    function sum($field,$select = ""){
        $table  = $this->table_name;
        $query  = "select SUM($field) from $table";
        if ($select != '') 
        	$query .= " where $select ";
        $result = $this->do_query($query);
        if (!$result)
            return false;
        $row   = $this->get_next_record($result);
        //$count = $result->num_rows;
        $count = $row["SUM($field)"];
        return $count;	
    }
    //*********************************************************************
    function update($select, $array)
    {
        // Updates the fields and values contained in array
        // Array elements consist of field_name => value records 
        // $select designates the record(s) to be updated
        $values = '';
        $i      = 0;
        foreach ($array as $key => $value) {
            if ($i != 0)
                $values .= ',';
            $values .= "$key='$value'";
        }
        $query  = "UPDATE " . $this->table_name . " SET $values WHERE $select";
        $result = $this->do_query($query);
        if (!$result)
            die("Database access failed: " . mysql_error());
        else
            return TRUE;
    }
    //*********************************************************************
    protected function update_field($col, $value, $key, $sel)
    {
        $value = "'" . fix_value($value) . "'";
        if ($key == "") {
            $query = "UPDATE " . $this->table_name . " SET $col=$value";
        } else
            $query = "UPDATE " . $this->table_name . " SET $col=$value WHERE $key='$sel'";
        $result = $this->do_query($query);
        if (!$result)
            die("Database access failed: " . mysql_error());
        else
            return TRUE;
    }
    //*********************************************************************
    function update_record($col, $array)
    {
        // When called $row is loaded with the new data 
        // $row = $this->get_record($col,$array[$col]);
        // We now have two arrays, $array and $row 
        // $dif = array() ;
        // Compare each field in $_REQUEST array with db row.
        $str = "";
        $sel = $array[$col];
        foreach ($array as $key => $value) {
            if (in_array($key, $this->cols) && ($key != $col)) {
                $value = fix_value($value);
                $str .= " $key='$value',";
            }
        }
        if (strlen($str) > 1) {
            $str    = substr($str, 0, strlen($str) - 1);
            $query  = "UPDATE " . $this->table_name . " SET $str WHERE $col='$sel'";
            $result = $this->do_query($query);
            if (!$result)
                die("Database access failed: " . mysql_error());
            else
                return TRUE;
        }
    }
    //*********************************************************************
    function update_record_changes($col, $array, $log_handle = '')
    {
        // When called $row is loaded with the new data 
        $row = $this->get_record($col, $array[$col]);
        // We now have two arrays, $array and $row 
        $dif = array();
        // Compare each field in $_REQUEST array with db row.
		if ($array['event_id'] == '') 
			$array['event_id'] = 0;
        foreach ($array as $key => $value) {
            if (in_array($key, $this->cols)) {
                if (strlen($array[$key]) == strlen($row[$key]) and $array[$key] == $row[$key])
                    continue;
                if ($array[$key] === $row[$key])
                    continue;
                $dif[$key] = $value;
            }
        }
        if (count($dif) > 0) {
            foreach ($dif as $key => $value) {
                // Update any db field found different
                $value = trim($value, " \\\t'");
                $this->update_field($key, $value, $col, $row[$col]);
            }
            if ($log_handle != '')
                log_column_changes($log_handle, $this->table_name, $row[$col], $row, $dif);
        }
        return $dif;
    }
    //*********************************************************************
}
?>