<?php
require_once(JPATH_LIBRARIES ."/usps/tableAccess.php");
//************************************************************************************
class tableD5documents extends USPStableAccess{
	// Generic routines to manage a table
//********************************* Public Variables **********************************
//********************************* Private Variables *********************************
//private $manager ;
/*
	Container for Event, Award or Object Document 
	Data Items
	  	`id` int(11) 		Autoincrement 
	  	`event_id` int(11) 	Link to event record. 
  		`award_id` int(11) 	Link to award record. 
  		`object_id` int(11) Link to object's record
  		`type` varchar(50) 	MMME type - string like application/pdg 
  		`date` datetime 	date of storage or last update()  		
  		`file`				Relative address of file - typically /events/...
  		`name` varchar(50) 	The file name or assigned name 
  		`size`				Value captured from the file uploaded
  		`protected`  		Only true when the file is restricted to officers...
  		`in-db`				true when contents in field 'data' 
  		`data` longblob 	base64_encode bytes from  the document in 
	
*/
//****************************************************************************
function __construct($db, $caller=''){
	// Creates the variables to contain identity of data and tables 
	parent::__construct('d5_documents', $db, $caller='');
	//$this->list_cols=$col_subset; 
	//$this->cols=$col_list;		
}// constructer
//**************************************************************************
function build_record_with_jpg_image($fname){
	$rec['b_data']=base64_encode(file_get_contents($fname)) ;
	$rec['b_type']='jpg';
	$rec['b_info']=serialize(getimagesize($fname));
	return $rec;
}
//**************************************************************************
function delete_award_documents($award_id){
	$query = "iten_part_no = '$award_id' and b_use = 'b_award_document' "; 
	$this->delete_range($query);
}
//**************************************************************************
function delete_event_documents($event_id){
	$query = "iten_part_no = '$event_id' and b_use = 'b_event_document' "; 
	$this->delete_range($query);
}
//**************************************************************************
function get_award_documents($award_id){
	$query = "award_id = '$award_id'";
	$documents = $this->search_records_in_order($query,"name");
	$docs = array();
	foreach ($documents as $x=>&$doc){
		//$docs[$doc['name']] = getSiteUrl()."/php/get_doc.php?item=".$doc['id'];
		unset($doc['data']); 	//  Data is only needed when the document is displayed
		$docs[$doc['name']] = $doc;
	}
	
	return $docs;
}
//**************************************************************************
function  get_award_names($from=''){
/* Searches to find records where b_use field is set to b_award_name 
// Converts data to return an array with the following indexes:  
	award_name 		copied from title field 
	award from		copied from b_type field  
	award_to		copied from item_part_no field  
	squad_no		copied from squad_no field  
	description		copied from b_info field			

		title: 	  (Use function store_aware_name or get_award_names)
			title: 			Award Name 
			b_type:			Award from.  Can be Squadron (squad), District (dist) or National (nat)
			item_part_no:	Award to.  Can be squad or mbr 
			squad_no:		Needed for Squadron Defined Awards, blank for district awards 
			b_info:			Award Description - optional 
	
	The input parameter $from designates the type of awards:
		"" for the district and national awards list 
		#### for awards created by a specific squadron
		 
*/			  

	$query ="b_use = 'b_award_name' and (squad_no='' or squad_no='$from')";
	$recs = $this->search_records_in_order($query,'title');
	$list = array();
	foreach($recs as $rec){
		$list[$rec['id']]['award_name'] = $rec['title']; 
		$list[$rec['id']]['awarded_by'] = $rec['b_type']; 
		$list[$rec['id']]['awarded_to'] = $rec['item_part_no']; 
		$list[$rec['id']]['squad_no'] = $rec['squad_no']; 
		$list[$rec['id']]['description'] = $rec['b_info']; 
	}
	return $list ;
}
//**************************************************************************
function get_document($id){
	$rec = $this->get_record('id',$id);
	$dat['type'] = $rec['type'];
	$dat['data'] = base64_decode($rec['data']);
	$dat['size'] = $rec["size"];
	$dat['name'] = $rec["name"];
	return $dat;
}
//**************************************************************************
function get_event_documents($event_id,$public = false){
	$query = "event_id = '$event_id'";
	if ($public){
		$query .= " and protected = 0";
	}
	$documents = $this->search_records_in_order($query,"name");
	return $documents;
}
//**************************************************************************
function get_item_picture($squad_no,$item_part_no,$b_use){
	$query = "item_part_no='$item_part_no' and b_use='$b_use' and squad_no=$squad_no";
	$row = $this->search_for_record($query);
	if (!$row) return false; 
	$a = array();
	$a['image'] = base64_decode($row['b_data']);
	$a['name'] = 'FP_image'.'.'.$row['b_type'];
	$a['info'] = unserialize($row['b_info']);
	return $a;
}
//**************************************************************************
function get_jpg_image($squad_no,$b_use){
	$cert = $_REQUEST['p_exec'] ;
	$query = "squad_no='$squad_no' and b_use='$b_use'";
	$row = $this->search_for_record($query); 
	$img = base64_decode($row['b_data']);
	$img = imagecreatefromstring($img) ;
	header("Content-Type:image/jpg") ;
	echo imagejpeg($img) ;
}
//**************************************************************************
function get_pdf_document($id){
	$rec = $this->get_record('id',$id);
	if ($rec['type'] == "application/pdf"){
		$dat['data'] = base64_decode($rec['data']);
		$dat['size'] = $rec["size"];
		$dat['name'] = $rec["name"];
		return $dat;
	} else 
		return "";
}
//**************************************************************************
function get_roster_update_date(){
	// get and return the date from the b_roster_last_update record 
	$rec = $this->get_record('b_use','b_roster_last_update');
	return $rec['flag'];
}
//************************************************************************
function store_award_document($award_id, $mime, $doc_type, $year, $file){

	$type = $file['type'];
	$query = "award_id = '$award_id' ";
	$query .= "and name = '$doc_type' ";
	$query .= "type = '$mime'";
	$rec = $this->search_for_record($query); 
	$rec['data'] = base64_encode(file_get_contents($file['tmp_name']));
	$rec['size'] = $file['size']; 
	$rec['in_db'] = TRUE;
	if (isset($rec['id'])){
		$this->store_record($rec,'id');
	}else{
		$rec['award_id'] = $award_id;
		$rec['name'] = $doc_type;
		$rec['type'] = $mime  ;
		$this->add_record($rec);
	}
	return '';
}
//************************************************************************
function store_award_name($award){
/*
	Creates a blog record with "b_award_name" in the b_use field
	Moves the fields from the $award parameter into the blog record per the following convert table  
		award_name to title  	  
		award_from to b_type
		award_to to item_part_no 
		squad_no  
		description to 	b_info 
*/	
	$rec = array('b_use'=>"b_award_name");		  
	$query = "b_use = 'b_award_name'";
	$query .= " title = '".$award['award_name']."'";
	$query .= " squad_no = '".$award['squad_no']."'"; 
	$exist = $this->search_record($query);
	$rec['title'] = $award['award_name'] ; 
	$rec['b_type'] = $award['awarded_by'];
	$rec['item_part_no'] = $award['awarded_to'] ; 
	$rec['squad_no'] = $award['squad_no']; 
	$rec['b_info'] = $award['description']; 
	if ($exist){
		 $result = $this->update_record($exist['id'],$rec);
	} else {
		$result = $this->add_record($rec);
	}
	
}
//**************************************************************************
function store_doc_image($squad_no,$b_use,$fname,$title="",$year=''){
	$fh = fopen($fname,"rb") ;
	$fsize = filesize($fname) ;
//  Read entire file into variable 
	$txt = fread($fh,$fsize) ;
	$query = "squad_no='$squad_no' and b_use='$b_use'";
	if ($squad_no == '6243'){
		$query .= " and year = '$year'";
	}
	$rec = $this->search_for_record($query); 
	$rec['b_data']=$txt;
	$rec['b_type']='txt';
	$rec['title']=$title;
	if (isset($rec['id'])){
		$this->store_record($rec,'id');
	}else{
		if ($squad_no == '6243'){
			$rec['year']=$year;
		}
		$rec['squad_no']=$squad_no;
		$rec['b_use']=$b_use;
		$this->add_record($rec);
	}
}
//************************************************************************
function store_event_document($event_id, 
								$rel_file_name, 
								$mime, 
								$name, 
								$year, 
								$file, 
								$private = false){

/*
		if NULL file contents stored in database 
		otherwise the url to access the file is stored in database  
	  	`id` int(11) 		Autoincrement 
	  	`event_id` int(11) 	Link to event record. 
  		`award_id` int(11) 	Link to award record. 
  		`object_id` int(11) Link to object's record
  		`type` varchar(50) 	MMME type - string like application/pdg 
  		`date` datetime 	date of storage or last update()  		
  		`file`				Relative address of file - typically /events/...
  		`name` varchar(50) 	The file name or assigned name 
  		`size`				Value captured from the file uploaded
  		`protected`  		Only true when the file is restricted to officers...
  		`in-db`				true when contents in field 'data' 
  		`data` longblob 	base64_encode bytes from  the document in */
  		
	$a_name = explode('.',$file['name']);
	$suffix = $a_name[count($a_name)-1];
	$name .= ".$suffix";
	if ($rec = $this->search_for_record("event_id = $event_id and name = '$name'"))
	{
		$update = true; 
		// OK, we only need to upload the file 
	} else {
		$update = false;
		$rec['event_id'] = $event_id;
		$rec['name'] = $name;
	}
	
  	if ($rel_file_name){
		$url = "$rel_file_name";		
		$rec['in_db'] = 0;	
		$rec['file'] = $rel_file_name;
		$rec['type'] = $mime;
	} else {
		$rec['in_db'] = 1;
		$rec['data'] = base64_encode(file_get_contents($file['tmp_name']));
		$rec['size'] = $file['size'];
		$rec['type'] = $mime;
	}
	if ($update){
		$this->update_record('id',$rec['id']);
	} else {
		$this->add_record($rec);
	}

}
//**************************************************************************
function store_pdf_image($file, $b_use, $b_type, $item_part_no ){
	$fh = fopen($fname,"rb") ;
	$fsize = filesize($fname) ;
//  Read entire file into variable 
	$txt = fread($fh,$fsize) ;
	$query = "squad_no='$squad_no' and b_use='$b_use'";
	if ($squad_no == '6243'){
		$query .= " and year = '$year'";
	}
	$rec = $this->search_for_record($query); 
	$rec['b_data']=$txt;
	$rec['b_type']='txt';
	$rec['title']=$title;
	if (isset($rec['id'])){
		$this->store_record($rec,'id');
	}else{
		if ($squad_no == '6243'){
			$rec['year']=$year;
		}
		$rec['squad_no']=$squad_no;
		$rec['b_use']=$b_use;
		$this->add_record($rec);
	}
}
//**************************************************************************
function store_xls_image($squad_no,$b_use,$fname,$title="",$year=''){
	$fh = fopen($fname,"rb") ;
	$fsize = filesize($fname) ;
//  Read entire file into variable 
	$txt = fread($fh,$fsize) ;
	$query = "squad_no='$squad_no' and b_use='$b_use'";
	if ($squad_no == '6243'){
		$query .= " and year = '$year'";
	}
	$rec = $this->search_for_record($query); 
	$rec['b_data']=$txt;
	$rec['b_type']='txt';
	$rec['title']=$title;
	if (isset($rec['id'])){
		$this->store_record($rec,'id');
	}else{
		if ($squad_no == '6243'){
			$rec['year']=$year;
		}
		$rec['squad_no']=$squad_no;
		$rec['b_use']=$b_use;
		$this->add_record($rec);
	}
}
//**************************************************************************
}// class
?>