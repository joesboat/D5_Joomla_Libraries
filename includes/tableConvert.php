<?php
//jimport("usps/includes/routines");
require_once(JPATH_LIBRARIES."/usps/tableAccess.php");
//*********************************************************
class tableConvert extends USPStableAccess{
//*********************************************************
function __construct($db, $caller){
		// Creates the variables to contain identity of data and tables 
	parent::__construct("db_conversion",$db,$caller);
		//$this->list_cols=$col_subset; 
		//$this->cols=$col_list;		
} // constructer	
//*************************************************************
function build_convert_table($line,$file_col,$db_col){
	$lx = str_replace('"',"",$line);
	$ln = explode(',',$lx);
	$cols = array();
	// For each entry in the .csv input $line 
	foreach($ln as $ix=>$value){
	// Find the record where entry in the $file_col matches input value
		$r = $this->get_record($file_col,$value);		
	// If $db_col record entry is not "" build a conversion entry 

		if ($r[$db_col]!=""){
			if ($r[$db_col]=='mmsi'){
				$xx = $r[$db_col];
			}
			$col['db_col_id']= $r[$db_col];
			$col['input_col_index']=$ix;
			$col['input_col_id']=$value;
			$col['protected']=$r['import_protect'];
			$col['type']=$r['type'];
			$col['special']=$r['handle_special'];
	// Package conversion entriew into array and return  
			$cols[count($cols)]=$col; 
		}
	}
	return $cols; 
}
//*************************************************************
function check_conversions($db, $line,$file_type,$add_column){
	// Unique to non-member people tables 
	// confirms db_conversions contains line for each column in $line
	// $line is expected to be header line from spreadsheet based .csv file 
	// $file identifies the source of .csv file.  Currently:
		//	"db2k" represents DB2000 generated membership files 
		//	"membership" represents locally created data files
	// Field names in reps_db_conversions match $file_type values
	$line = str_replace('"','',$line);
	$line_array = explode(",",$line); 
	foreach($line_array as $line_value){
		$line_value = trim($line_value);
		$entry = $this->get_record($file_type,$line_value);
		if ($entry){
			$t = $db->table_name;
			$name = $entry[$t];
			if ($name <> ""){
				if (!in_array($name,$db->cols)){
					$entry[$db->table_name]="";
					$this->store_record($entry,"id");
				}
			}
		}else{
			$db_column = $db->membership_col_to_db_col($line_value) ; 
			if (!in_array($db_column,$db->cols)){
				if ($add_column) $db->add_column($db_column,"20");
			}			// Search all columns for this value.
			$col_found = "" ;
			// $record = array();
			foreach($this->cols as $col){				
				$rec = $this->get_record($col,$line_value);
				if ($rec){
					$col_found=$col;
					break;
				}
			}
			if ($col_found <> ""){
				// The value exists in in the $col_found column 
				// $record contains the existing record 
				// Obtain column name from convert routing 
				$record[$db->table_name]=$db_column;
				$record[$file_type]=$line_value;
				$this->store_record($record,"id");
			}else{
				// There's no entry for this type - we must create a new record'
				// Create data for this db field that matches this column 
				$record = $this->blank_record;
				$record[$file_type]=$line_value;
				if ($add_column) $record[$db->table_name]=$db_column ;  
				$this->add_record($record);
			}
		}
	} 
}
//*************************************************************
function get_usps_column_conversions(){
	return $this->get_partial_records("members, mbftp_prefix, mbftp","","");
}
//*************************************************************
function get_usps_column_list(){
	// Build and return a list of columns needed from USPS Members table 
	$cols="";
	$col_list = $this->get_partial_records("d5_members, mbftp_prefix, mbftp","","");
	foreach($col_list as $r){
		if ($r["mbftp"] != "") $cols.=$r['mbftp'] . ",";
	}
	foreach($col_list as $r){
		if ($r["mbftp_prefix"] != "") $cols.=$r['mbftp_prefix'] . ",";
	}
	if ($cols != "") $cols = substr($cols,0,strlen($cols)-1); 
	return $cols; 
}
//*********************************************************
protected function setup_column_name_table($cols, $line){
	// Enter the input_col_index value in each $cols entry
	// Entries in array: 
	//	['db_col_id'] provided by caller 
	//	['input_col_id'] provided by caller 
	//	['input_col_index'] added by this function 
	$val="";
	$line_array=explode(",",$line);
	$cert = -1;
	foreach($line_array as $line_index=>$line_col_name){
		$line_col_converted = trim(strtolower(str_replace('"','',$line_col_name)));
		$line_col_converted = strtolower(str_replace('.','',$line_col_converted));
		foreach($cols as $col_index=>$col_data){
		//foreach($cols as $col){
			// Find this input_col_id in $cols
			$id=$col_data['input_col_id'];
			if ($col_data['input_col_id']==$line_col_converted){
				// Insert $i in input_col_index 
				$col_data['input_col_index'] = $line_index;
				if ($line_col_converted == "certificate") 
					$cert = $line_index;
				$cols[$col_index]=$col_data;
				break;			}
		}
	}
	$cols['cert'] = $cert;
	return $cols;
}
//*********************************************************
function setup_columns($line,$cols){
	// Creates a conversion array from table 
	//$cols = $this->get_columns();
	$col_list=array();
	foreach($cols as $array){
		$column = array();
		$column['db_col_id']=$array['Field'];	// pure name of column in this->table_name
		$column['input_col_id']=str_replace("mbr_","",$array['Field']);
		$column['input_col_id']=str_replace("_"," ",$column['input_col_id']);
		if($column['input_col_id']=="email") $column['input_col_id']="e-mail" ; 
		$column['input_col_index']=-1;
		$column['type']=$array['Type'];
		$col_list[count($col_list)]=$column;
	}
	$columns = $this->setup_column_name_table($col_list, $line);
	$this->certificate_column=$columns['cert'];
	unset($columns['cert']);
	$this->convert=$columns; 
	return $columns;
}
} // end of class table_convert