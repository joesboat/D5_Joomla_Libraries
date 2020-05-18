<?php
/*
Copyright (C) March 2013, Joseph P. Gibson.

This program is free software to USPS Members developing software for USPS Squadrons or Districts: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program.  If not, see <http://www.gnu.org/licenses/>.

Owner: 	Joseph P. Gibson
		USPS District 5 Webmaster 
*/
//*********************************************************
// require_once('table_access.php');
class tableCourses extends USPStableAccess{
//*********************************************************
function __construct($db, $caller=''){
	// Creates the variables to contain identity of data and tables 
	parent::__construct("usps_courses",$db, $caller);
	//$this->list_cols=$col_subset; 
	//$this->cols=$col_list;		
} // constructer	
//*************************************************************************
function update_course($ary) {
// Assumes the input array contains all fields needed to modify a location record. 
// Called from 'calendar_entry.php' to replace db fields with fields entered on form'
	$this->update_record_changes('course_id',$ary);
}
//*************************************************************************
function add_course($ary){
// Assumes the $_POST array has been loaded with all fields needed for an event record
// Creates a new record in 'cal_event' table 
// Called from 'calendar_entry.php' 
	$this->add_record($ary);
}
//*********************************************************
function format_course_name_with_link($ary){
	// Determine if there's a link to valid course description	
	if ($ary['course_desc_url'] != ''){
		return "<a target='_blank' href='".
				$ary['course_desc_url'].
				"'>".$ary['course_name']."</a>";
	}elseif(false){
		
	}else
		return $ary['course_name'];
}
//*************************************************************************
function get_course_name_with_link($course){
	$link = getSiteUrl()."/courses/course_gen.php?course_id=".$course['course_id'];
	if (trim($course['course_desc_url']) != ''){
		$str = "<a target='_blank' href='$link'>";
		$str .= $course['course_name'];
		$str .= "</a>";	
		return $str;	
	}else
		return $course['course_name'];
		//echo "<a href='http://www.americasboatingcourse.com/index.cfm' >"; 
		//echo "America&acute;s Boating Course </a></span>"
}
//*********************************************************
function get_usps_course_data($ident){
	// Queries usps_courses for all fields for course_id
	// Formats display of course data in HTML format
	// Obtains a record from rsps_classes where $id = class_id 
	if (is_numeric($ident)){
		$array = $this->get_record("course_id",$ident);
	}else{
		$array = $this->get_record('course_short_name',$ident);
	}
	return $array;
}
//*********************************************************
function get_usps_course_names(){
	// Builds an array suitable for use in a list box 
	// Queries usps_courses table for list of known courses
	// Returns an array containing course_id and course_name 
	$array = $this->search_partial_records_in_order("course_id, course_name",'',"course_name");
	foreach($array as $key=>$value){
		$ary[$value['course_id']] = $value['course_name'] ;
	}
	return $ary;
}	
//*********************************************************
function show_course_option_list($id){
	$courses = $this->get_usps_course_names();
	echo "<select name='course_id' width='50'>;" ;
	show_option_list($courses,$id);
	echo "</select>";
}
} // End of Class table_courses
?>