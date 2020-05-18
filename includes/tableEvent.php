<?php
require_once('tableEvents.php');
//*************************************************************************
class table_USPSevent extends table_USPSevents{
// Routines to manage events table for a specific organization 
// An Event may be either a meeting, conference, rendezvous or class 
//********************************* Public Variables **********************
//********************************* Private Variables *********************
var $squad_no;
//*************************************************************************
function __construct($sqd, $db, $caller=''){
global $exc, $jobs, $blob,  $mbr;
	// Creates the variables to contain identity of data and tables 
	parent::__construct($db, $caller);
	$this->squad_no=$sqd; 
	$this->blank_record['squad_no']=$this->squad_no;
	//$this->cols=$col_list;

}// constructer
//****************************************************************************
function addEvent($ary){

global $crs;
// Assumes the $_POST array has been loaded with all fields needed for an event record
// Creates a new record in 'event' table 
	$ary['squad_no'] = $this->squad_no;
	$temp = $this->blank_record;
	$this->add_record($this->blank_record);
	$list = "max(event_id)";
	$rec = $this->search_partial_records($list,1);
	$x = $rec[0];
	$ary['event_id'] = $rec[0]['max(event_id)'];
	return $ary;
}
//****************************************************************************
function addBlankEventRecord(){
	$this->add_record($this->blank_record);
	$list = "max(event_id)";
	$rec = $this->search_partial_records($list,1);
	$rec = $this->get_record('event_id',$rec[0]['max(event_id)']);
	return $rec;
}
//****************************************************************************
function addLocation($ary) {
// Assumes the $_POST array has been loaded with all fields needed for an event record
// Creates a new record in 'cal_event' table 
// Called from 'calendar_entry.php' 
	$sql = "INSERT INTO locations SET location_id = DEFAULT"  ; 
	foreach($ary as $key=>$value){
		if (substr($key,0,8) == "location"){
			$value = fix_value( $ary[$key]);
			$sql .= ", $key ='$value'";
		}
	}
	if (($result = do_query($sql))===false ){
		printf("Invalid query: %s\nWhole query: %s\n", $result);
		exit;
	}
	header("Location: ../private/mbronly.php");
}
/*
//****************************************************************************
function addStudent($id,$class_id,$enroll_id,$is_guest){
	$sql="insert into students set ".
		"class_id='$class_id', ".
		"enroll_id='$enroll_id',";
	if ($is_guest){
		$sql.="guest_id='$id'";
	}else{
		$sql.="mbr_id='$id'";
	}
	return do_query($sql);
}
//*********************************************************
function build_class_session_list($class) {
	// Creates calendar events for each class session 
	// Class record provided.
	// Must obtain location and instructor records 
	// Simply assumes classes will be conducted each week 
	//	No concern for holidays or other reasons to skip a specific date
	
	$course = get_usps_course_data($class['course_id']);
	$location = get_location_data($class['location_id']); 
	$inst = get_member_record($class['instructor_id']);
	$count = $class['class_sessions'];
	$sd = explode('/',$class['class_start_date']);
	$date = mktime(0,0,0,$sd[1],$sd[2],$sd[0]);
	$increment = 60*60*24*7;		//Number of seconds in a week.
	$ary = array();
	$ses = 1 ;
	
	$ary['cal_event_name_url'] = $course['course_desc_url'];
	$ary['cal_class_id'] = $class['class_id'];
	//store_location_data($ary,$location);
	$ary['cal_poc_id'] = ($inst['certificate']);
	$ary['cal_location_id'] = $location['location_id'];
	//$ary['cal_poc_email'] = $inst['mbr_email'];
	//$ary['cal_poc_phone_c'] = $inst['mbr_cell_phone'];
	//$ary['cal_poc_phone_h'] = $inst['telephone'];
	while ($count > 0) {
		if ($ses==1) {
			$ary['cal_event_type'] = "class" ;
		} else {
			$ary['cal_event_type'] ="ed" ;
		}
		$ary['cal_event_name'] = $class['class_name']." Session " . $ses;
		$s_date = date('Y-m-d',$date) . ' ' . $class['class_start_time']; 	
		$ary['cal_event_date'] = $s_date;
		addEvent($ary) ; 
		$date += $increment;
		$count --;
		$ses ++;
	}
}
//****************************************************************************
function build_full_event_record($row){
global $exc,$cls,$mbr,$loc,$crs;
	
	$me = explode("/",$_SERVER['PHP_SELF']);
	// $crs=new table_courses($me[count($me)-1]);
	// Provided $row is foundation event record 
	// Query data from RSPS_LOCATIONS table & add fields to $row 
	if (!(($row['cal_location_id']=="")||($row['cal_location_id']==0))){
		// OK - we have an external location, lets add it 
		$x = $row['cal_location_id'] ;
		if ($x==""){
			$location = $loc->get_rsps_location_data(0) ;
		}else{
			$location = $loc->get_rsps_location_data($row['cal_location_id']) ;
		}
		$x = $row['cal_event_type'];
		if ($x == "ed" && $row['cal_class_id']<>0){
			$class = $cls->get_record("class_id",$row['cal_class_id']);
			$course = $crs->get_usps_course_data($class['course_id']);
			$row['cal_event_name_url']=$course['course_desc_url'];
		}
		$row['cal_event_venue'] = $location['location_name'];
		$row['cal_event_state'] = $location['location_state']; 
		$row['cal_event_city'] = $location['location_city'];
		$row['cal_event_street'] = $location['location_street']; 
		$row['cal_event_venue_url'] = $location['location_url']; 
		$row['cal_event_zip'] = $location['location_zip']; 
	}
	// Query data from RSPS_MEMBERS table & add fields to $row 
	if ($row['cal_poc_id']<>""){
		$member = $mbr->get_mbr_record($row['cal_poc_id']) ;
		$row['cal_poc'] = $exc->get_d5_member_name($member) ;
		$row['cal_poc_phone_h'] = $member['telephone'] ;
		$row['cal_poc_phone_c'] = $member['cell_phone'] ;
		$row['cal_poc_email'] = $member['email'] ;
	}
	// Return updated $row to caller 
	return $row; 
}
//***************************************************************************
function buildRecord($table_name){
	// creates an empty record for the specified table
	$a=get_column_list($table_name);
	foreach($a as $key=>$name){
		$row[$name]="";
	}
	return $row;
}
//****************************************************************************
function checkSetup_class_setup(){
	if ($_POST['class_id']==""){
		echo "<p>You did not select a class.  Press 'Retry' to return or 'Cancel' to exit.</p>";
		echo '<input type="submit" name="command" value="Retry" />';
		show_setup_class_trailer();
		exit;
	}
}
//****************************************************************************
function createClasslist_csv_file(){
	$eol = "\n" ;
	$str = date("Ymd", time()-(60*60*6));
	$file_name="class_list_".$str.".csv";
	$fh = fopen($file_name,'w');
	if (!$fh){
		printf("Can't open the session_log.txt file");
		exit;
	}
	// OK we have a file, now let's fill it with class data
	$str = date("l F j, Y - g:ia", time()-(60*60*6)); 
	$i = fwrite($fh, $eol.$str.$eol.$eol);
	$array=get_column_list('classes');
	$str="Displayed Name,";
	foreach($array as $ix=>$value)
		$str.=$value.",";
	$i = fwrite($fh, $str.$eol.$eol);
	$classes = get_available_class_list();
	foreach($classes as $class_id=>$date_name){
		$str = format_class_csv($class_id,$date_name); 
		$i = fwrite($fh, $str.$eol);
	}
	$classes = get_closed_class_list();
	foreach($classes as $class_id=>$date_name) 
		$i = fwrite($fh, format_class_csv($class_id,$date_name).$eol);
	fclose($fh);
	return $file_name;
}
//****************************************************************************
function deleteLocation($loc_id){
	$sql = "DELETE FROM locations WHERE location_id = '" . $loc_id . "'" ;
	if ( ($result = do_query($sql))===false ){
		printf("Invalid query: %s\nWhole query: %s\n", $result, $sql);
		exit;
	}
}
//****************************************************************************
function deleteEvent($id) {
// called from 'calendar_entry.php' to effect the deletion of an existing event
	// Set up query	
	$sql = "DELETE FROM cal_event WHERE cal_id = ". $id ;
	// Execute Query	
	if ( ($result = do_query($sql))===false ) {
		printf("Invalid query: %s\nWhole query: %s\n", $result, $sql);
		exit;
	}
	// Close database connection
	header("Location: mbronly.php");
	exit;
}
//*********************************************************
function delete_class($id){
	// Deletes all class events in cal_event table	
	$sql = "DELETE from cal_event where cal_class_id='$id'";
	$result = do_query($sql);
	// Deletes class identifed by $id 
	$sql = "DELETE from classes where class_id='$id'";
	$result = do_query($sql);
}
//****************************************************************************
function format_class_csv($class_id,$date_name){
	$class = get_class_data($class_id);
	$course = get_usps_course_data($class['course_id']);
	$instructor = get_member_record($class['instructor_id']);
	$location = get_location_data($class['location_id']);
	$students = get_student_list($class_id);
	$str=$date_name.",";
	foreach($class as $col=>$data){
		switch($col){
		case "instructor_id":
			$str.=str_replace(",","",get_d5_member_name($instructor)).",";
			break;
		case "location_id":
			$str.=$location['location_name'].',';
			break;
		case "course_id":
			$str.=$course['course_name'].',';
			break;
		default:
			$str.=$data.",";
		}
	}
	return $str;
}
//*********************************************************
function get_class_list(){
	// Obtains a list of current classes from classess
	//	Valid classes will not have the 'class_closed' set
	$query = "select class_id, class_name, class_start_date from classes order by class_start_date;";
	$result = do_query($query) ;
	$ary = array();
	//$array = $result->fetch_all(MYSQLI_ASSOC);
	$array = fetch_all($result);
	foreach($array as $key=>$value){
		$ary[$value['class_id']] = $value['class_start_date']." - ".$value['class_name'];
	}
	return $ary;
}
//*********************************************************
function get_available_class_list(){
	//  Obtains a list of current classes from classess
	//	Valid classes will not have the 'class_closed' set
	$now = date("Y-m-d");
	$query="select class_id, class_name, class_start_date, class_max_students ".
			"from classes " .
			"where class_cutoff_date>"."'".$now."' ". 
			" order by class_start_date;";
	$result = do_query($query);
	$ary = array();
	//$array = $result->fetch_all(MYSQLI_ASSOC);
	$array = fetch_all($result);
	foreach($array as $key=>$value){
		$student_count=count(get_student_list($value['class_id']));
		if ($student_count<$value['class_max_students']) 
			$ary[$value['class_id']] = $value['class_start_date']." - ".$value['class_name'];
	}
	return $ary;
}
//*********************************************************
function get_closed_class_list(){
	// Obtains a list of future classes closed for on-line enrollment!	
	$now = date("Y-m-d"); 
	$query="select class_id, class_name, class_start_date, class_max_students, ".
			'class_cutoff_date from classes where class_start_date > "'.$now.'" '.
			"order by class_start_date;";
	$result = do_query($query);
	$ary = array();
	//$array = $result->fetch_all(MYSQLI_ASSOC);
	$array = fetch_all($result);
	foreach($array as $key=>$value){
		$student_count=count(get_student_list($value['class_id']));
		if (($student_count >= $value['class_max_students'])||
			(($value['class_start_date']>$now)&&($value['class_cutoff_date']<$now)))
			$ary[$value['class_id']] = $value['class_start_date']." - ".$value['class_name'];
	}
	return $ary;
}
//*********************************************************
function get_instructor_list(){
	// Queries members to create a list of instructors
	$query = "select certificate, first_name, last_name from members where certified_instructor = '1'";
	$result = do_query($query) ;
	$ary = array() ; 
	while ($array = $result->fetch_assoc()){
		$ary[$array['certificate']] = get_person_name($array);
	}
	return $ary ;
}
//*********************************************************
function get_class_data($id){
	if ($id==0){
		$query="Stop here to trap it.";
	}
	// Obtains a record from classes where $id = class_id 
	$query = "select * from classes where class_id = '$id' ;";
	$result = do_query($query) ;
	$ary = array() ; 
	//$array = $result->fetch_all(MYSQLI_ASSOC) ;
	$array = fetch_all($result);
	return $array[0] ;
}
*/
//*******************************************************************************
function get_future_events($select=''){
	$select = "start_date >= curdate()";
	$list = $this->search_records_in_order($select,'start_date');
	return $list;
}
/*
//*******************************************************************************
function get_record($key = "id",$value){
// Called to obtain a specific event 
// We will only return a row for the current squadron	
	$select = "squad_no='".$this->squad_no."' and $key='$value'"; 
	return $this->search_for_record($select);
}
//*******************************************************************************
function get_records($col='',$value=''){
	$select = "end_date >= curdate()";
	if ($col != ''){
		$select .= " and $col = '$value'" ;
	}
	$events = $this->search_records_in_order($select,'start_date');
	return $events;
}
//*******************************************************************************
function get_event_list(){
	
}
//*******************************************************************************
function getStudent_list($class_id){
	// finds each student registered for $class_id
	$query = "select * from students where class_id='$class_id'";
	$result=do_query($query);
	$ary=array();
	$names=array();
	//$array = $result->fetch_all(MYSQLI_ASSOC);
	$array = fetch_all($result);
	foreach($array as $key=>$row){
		if ($row['mbr_id'])
			$query="select * from members where certificate = '".$row['mbr_id']."'";
		elseif ($row['guest_id'])
			$query="select * from guests where certificate = '".$row['guest_id']."'";
		else
			return; 
		$result=do_query($query);
		$ary=$result->fetch_assoc();
		$enroll=get_enrollment_record($row['enroll_id']);
		$ary['enroll_id']=$row['enroll_id'];
		$ary['payment_status']=$enroll['payment_status'];
		$names[$key]=$ary;
	}
	return $names;
}
*/
//******************************************************************************
function search_records_in_order($select="",$order=""){
	$rows = array();
	if ($select != "") 
		$select .= " and "; 
	$select .= "squad_no = ".$this->squad_no;
	$rows = parent::search_records_in_order($select,$order);
	return $rows;
}
/* 
//*******************************************************************************
function showAvailable_classes(){
	$s = "";
	// Determine what classes are beginning in the future
	// and have seats available.  
	// Arrange records of available classes from all squadrons 
	// Sequential in S,P,AP,JN,N order, then elective in alpha order.
	$s = "<p>The following classes are available.  Select a course and start date to obtain registration information.</p>";
	echo $s;
	// Create & display links to class display page for that class
	// Link should include name & start date
	// Display page for class should contain all pertinant data.  
}
//*********************************************************
function show_class_info($ary){
	// Creates HTML input fields to display class data
	echo "<ul>" ;
	echo '<li>Class Start Date:  <input type="text" name="class_start_date" value="'.$ary['class_start_date'].'" width="40"/></li>';
	echo '<li>Class Cutoff Date: <input type="text" name="class_cutoff_date" value="'.$ary['class_cutoff_date'].'" width="40" title="Defaults to 7 days before class start.  You may change!"/></li>';
	echo '<li>Class Sessions: <input type="text" name="class_sessions" value="'.$ary['class_sessions'].'" width="5"/></li>';
	echo '<li>Maximum Students: <input type="text" name="class_max_students" value="'.$ary['class_max_students'].'" width="5" title="Based on minimum of location maximum or course maximum.  You may change! " /></li>';
	echo '<li>Member Tuition: <input type="text" name="class_mbr_tuition" value=" '.$ary['class_mbr_tuition'].'" width="40"/></li>';
	echo '<li>Guest Tuition: <input type="text" name="class_guest_tuition" value=" '.$ary['class_guest_tuition'].'" width="40"/></li>';
	echo '<li>Class Start Time: <input type="text" name="class_start_time" value="'."19:00".'" width="5"/></li>';
	echo '<li>Class Session Length: <input type="text" name="class_length" value="'."2.0".'" width="5" title="Enter the number of hours of class time."/></li>';
	echo '</ul>';
}
//*********************************************************
function show_new_class($class, $instructors, $locations){
	echo '<input type="hidden" name="course_id" value="'.$class['course_id'] . '"/>';
	echo "<h2> " . $class['class_name'] . " Class. </h2>";
	echo "<p>The class will be created with the following default parameters based on historical education department data. They are provided as guidance.  You may change any parameter before continuing.  Once satisfied, press the 'Create Class' button to enter the class data and create a schedule entry for each class session.  </p>";
	show_class_info($class);
	echo "<p>Instructor Selected: ";
	echo "<select name='instructor_id' width='50'>;" ;
	show_option_list($instructors,$class['instructor_id']);
	echo "</select>";
	echo "</p>";
	//show_instructor_info($class['instructor_id']);
	echo "<p>Facility Selected: ";
	echo "<select name='location_id' width='50'>;" ;
	show_option_list($locations,$class['location_id']);
	echo "</select>";
	echo "</p>";
	echo "<p>Once created, a class cannot be changed.  If changes are needed after you save, the class must be deleted from the schedule and then re-scheduled.</p>";
}
//*********************************************************
function storeLocation_data($ary,$loc){
	// Saves the location details in an event array 
	$ary['cal_event_venue'] = $loc['location_name'];
	$ary['cal_event_venue_url'] = $loc['location_url'];
	$ary['cal_event_street'] = $loc['location_street'];
	$ary['cal_event_city'] = $loc['location_city'];
	$ary['cal_event_state'] = $loc['location_state'];
	$ary['cal_event_zip'] = $loc['location_zip'];
}
//*********************************************************
function updateClass_info($ary){
	$query = "insert into classes set class_id = DEFAULT" ;
	foreach($ary as $key=>$value){
		$value = fix_value($value);
		if ($key <> 'class_id') $query .= ", $key = '$value'" ;
	}
	$result = do_query($query);
	$query = "select class_id from classes where ";
	$query .= "class_name = '" . $ary['class_name'] ."' and ";
	$query .= "class_start_date = '" . $ary['class_start_date'] ."'";
	$result = do_query($query);
	$ary = $result->fetch_assoc();
	return $ary['class_id'];
}
//*********************************************************
function updateLocation($ary) {
// Assumes the input array contains all fields needed to modify a location record
// Called from 'calendar_entry.php' to replace db fields with fields entered on form'
	$sql = "UPDATE locations SET " ;
	foreach($ary as $key=>$value){
		if (substr($key,0,8) == "location"){
			$value = fix_value($_POST[$key] );
			$sql .= "$key='$value',";
		}
	}
	$i = strrpos($sql, ",");
	$sql = substr($sql,0, $i) ." WHERE location_id = '".$ary['loc_id']."'";
	if ( ($result = do_query($sql))===false ) {
		printf("Invalid query: %s\nWhole query: %s\n", $result, $sql);
		exit;
	}
	// Close database connection
	header("Location: ../private/mbronly.php");
	exit;
}
*/
}// class
?>