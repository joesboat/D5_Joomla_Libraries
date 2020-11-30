<?php
//require_once("/home/content/82/7781582/html/libraries/usps/tableAccess.php");
//require_once("c:/users/joe/websites/uspsd5/libraries/usps/tableAccess.php");
//require_once("/web/joomla/libraries/usps/tableAccess.php");
require_once(JPATH_LIBRARIES ."/usps/tableAccess.php");
//require_once(JPATH_LIBRARIES ."/usps/tableBlobs.php");
//************************************************************************************
class tableD5blobs extends USPStableAccess{
	// Generic routines to manage a table
//********************************* Public Variables **********************************
//********************************* Private Variables *********************************
//private $manager ;
/*
CREATE TABLE IF NOT EXISTS `blobs` (
	`id` int(11) 
  	`item_part_no` varchar(20)	- Often used as key to other tables 
  	`squad_no` varchar(4)		- Obvious
  	`certificate` varchar(7) 	- Obvious
  	`year` varchar(4)			- Obvious
  	`flag` varchar(15) 
  	`b_type` varchar(3)		Specifies the file type of b_data
  	`b_info` text			Metadata - maybe filename  
  	`b_use` varchar(25)		Main Key - The records use - See List Below 
  	`title` varchar(40)		2nd Key - Provides info or structure of b_data
  	`b_data` mediumblob		Typically file or picture data 	

	b_use 

		b_award_document
			item_part_no:award_id
			b_type: document/pdf, document/jpg, document/png 
			b_info: $file array in string format
			title: folder/file name in url format  
			b_data = not used
		b_award_name  (Use function store_aware_name or get_award_names)
			title: 			Award Name 
			b_type:			Award from.  Can be Squadron (squad), District (dist) or National (nat)
			item_part_no:	Award to.  Can be squad or mbr 
			squad_no:		Needed for Squadron Defined Awards, blank for district awards 
			b_info:			Award Description - optional 
			  
		b_cmd_msg
		b_custom_1
		b_custom_2
		b_event_description  
			item_part_no:event_id  
			b_type:text 
			b_data:description
		b_event_document
			item_part_no:event_id
			b_type: document/pdf, document/msword 
			b_info: $file array in string format
			title: folder/file name in url format  
			b_data = not used
		b_female_uniform
		b_flag
		b_front_page
		b_male_uniform
		b_membership_1
		b_membership_2
		b_small_image
		b_session
			item_part_no = ip address of remote 
			squad_no = squad_no
			title = 'stack'
			b_data = stack contents
		b_signature_image
		b_roster_last_update 
			flag = date string 
		item_150
		item_640		
		mbr_picture_60
		mbr_picture_80
		mbr_picture_640
		y_manage			
		y_display

*/
//****************************************************************************
function __construct($db, $caller=''){
	// Creates the variables to contain identity of data and tables 
	parent::__construct('d5_blobs', $db, $caller='');
	//$this->list_cols=$col_subset; 
	//$this->cols=$col_list;		
}// constructer
//**************************************************************************
function add_session_stack($squad_no,$ip,$stack,$title='stack'){
	//	b_session
	//		item_part_no = ip address of remote 
	//		squad_no = squad_no
	//		title = name of calling program
	//		b_info = stack contents
	$select = "b_use='b_session' and squad_no='$squad_no' and item_part_no='$ip' and title='$title' " ;
	$rec = $this->search_record($select);
	if (! $rec){
		$s_stack = serialize($stack);
		$array = array("b_info"=>$s_stack,
				"b_use"=>'b_session', 
				"squad_no"=>$squad_no, 
				"item_part_no"=>$ip, 
				"title"=>$title);
		$this->add_record($array);
		return;
	}
	$this->store_session_stack($squad_no,$ip,$stack,$title='stack');
}
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
//************************************************************************
function delete_event_description($event_id){
	$b_use = 'b_event_description';
	$query = "item_part_no = '$event_id' and b_use='$b_use'";
	$this->delete_range($query);
}
//**************************************************************************
function delete_event_documents($event_id){
	$query = "iten_part_no = '$event_id' and b_use = 'b_event_document' "; 
	$this->delete_range($query);
}
//**************************************************************************
function delete_session_stack($squad_no,$ip,$title='stack' ){
	// Overwrites existing stack string
	//	b_session
	//		item_part_no = ip address of remote 
	//		squad_no = squad_no
	//		title = 'stack'
	//		b_info = stack contents
	$select = "b_use='b_session' and squad_no='$squad_no' and item_part_no='$ip' and title='$title' " ;
	$this->delete_range($select);
}
//**************************************************************************
function get_advertising_page($squad_no, $nbr){
	$name = "b_adv_$nbr" ;		
	$query = "squad_no='$squad_no' and b_use='$name'";
	$row = $this->search_for_record($query);
	if (!$row) return false; 
	$a = array();
	$a['image'] = base64_decode($row['b_data']);
	$a['name'] = $name.'.'.$row['b_type'];
	$a['info'] = unserialize($row['b_info']);
	return $a;
}
//**************************************************************************
function get_award_documents($award_id){
	$query = "b_use = 'b_award_document' and item_part_no = '$award_id'";
	$documents = $this->search_records_in_order($query,"b_info, b_type");
	foreach ($documents as $x=>&$doc){
		$doc["title"] = $this->use_this_url('awards',$doc['title']);
	}
	
	return $documents;
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
function get_cdr_message($squad_no,$year=''){
	$query = "squad_no='$squad_no' and b_use='b_cmd_msg'";
	if ($squad_no == '6243'){
		$query .= " and year = '$year'";
	}
	$row = $this->search_for_record($query);
	if (!$row) return false; 
	$text = $row['b_data'];
	return $text;		
}
//**************************************************************************
function get_custom_page($squad_no, $nbr){
	$name = "b_custom_" . $nbr;		
	$query = "squad_no='$squad_no' and b_use='$name'";
	$row = $this->search_for_record($query);
	if (! $row) return false;
	//$text = $row['b_data'];
	//return $text;
	return $row;			
}
//**************************************************************************
function get_display_year(){
	$row = $this->get_record('b_use','y_display');
	return $row['title'];
}
//**************************************************************************
function get_document($id){
	$rec = $this->get_record('id',$id);
	// $rec['data'] = base64_decode($rec['b_data']);
	return $rec;
}
//**************************************************************************
function get_event_description($event_id){
	$b_use = 'b_event_description';
	$query = "item_part_no='$event_id' and b_use='$b_use'";
	$row = $this->search_for_record($query);
	if (!$row) return ''; 
	return $row['b_data'];
}
//**************************************************************************
function get_event_documents($event_id,$public = false){
	$query = "item_part_no = '$event_id' and (b_use = 'b_event_document' or b_use = 'b_award_document') ";
	if ($public){
		$query .= " and flag = ''";
	}
	$documents = $this->search_records_in_order($query,"b_info, b_type");
	return $documents;
}
//**************************************************************************
function get_flag_image($flag){
	$query = "flag='$flag' and b_use='b_flag'";
	$row = $this->search_for_record($query);
	if (!$row) return false; 
	$a = array();
	$a['image'] = base64_decode($row['b_data']);
	$a['name'] = 'FP_image'.'.'.$row['b_type'];
	$a['info'] = unserialize($row['b_info']);
	return $a;
}
//**************************************************************************
function get_fp_image($squad_no, $year=''){
	$query = "squad_no='$squad_no' and b_use='b_front_page'";
	if ($squad_no == '6243'){
		$query .= " and year = '$year'";
	}
	$row = $this->search_for_record($query);
	if (!$row) return false; 
	$a = array();
	$a['image'] = base64_decode($row['b_data']);
	$a['name'] = 'FP_image'.'.'.$row['b_type'];
	$a['info'] = unserialize($row['b_info']);
	return $a;
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
function get_manage_year(){
	$row = $this->get_record('b_use','y_manage');
	return $row['title'];
}
//**************************************************************************
function get_mbr_picture($cert,$width=80){
	$query = "certificate='$cert' and b_use='mbr_picture_$width'";
	$row = $this->search_for_record($query);
	if (!$row) return false; 
	$a = array();
	$a['image'] = base64_decode($row['b_data']);
	$a['name'] = 'FP_image'.'.'.$row['b_type'];
	$a['info'] = unserialize($row['b_info']);
	return $a;
}
//**************************************************************************
function get_pdf_document($id){
	$rec = $this->get_record('id',$id);
	if ($rec['b_type'] == "application/pdf"){
		$dat['data'] = base64_decode($rec['b_data']);
		$dat['size'] = $rec["b_size"];
		$a_name = explode('/',$rec['title']);
		$dat['name'] = $a_name[sizeof($a_name)-1];
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
//**************************************************************************
function get_session_stack($ip, $squad_no, $title='stack'){
	//	b_session
	//		item_part_no = ip address of remote 
	//		squad_no = squad_no
	//		title = 'stack'
	//		b_info = stack contents
	$select = "b_use='b_session' and item_part_no='$ip' and title='$title' and squad_no='$squad_no'" ;
	$rec = $this->search_record($select);
	$stack = unserialize($rec['b_info']);
	return $stack;
}
//**************************************************************************
function get_signature_image($squad_no,$year=''){
	$query = "squad_no='$squad_no' and b_use='b_signature_image'";
	if ($squad_no == '6243'){
		$query .= " and year = '$year'";
	}
	$row = $this->search_for_record($query);
	if (!$row) return false; 
	$a = array();
	$a['image'] = base64_decode($row['b_data']);
	$a['name'] = 'Commanders Signature'.'.'.$row['b_type'];
	$a['info'] = unserialize($row['b_info']);
	return $a;	
}
//**************************************************************************
function get_small_image($squad_no, $year=''){
	$query = "squad_no='$squad_no' and b_use='b_small_image'";
	if ($squad_no == '6243'){
		$query .= " and year = '$year'";
	}
	$row = $this->search_for_record($query);
	if (!$row) return false; 
	$a = array();
	$a['image'] = base64_decode($row['b_data']);
	$a['name'] = 'Small_image'.'.'.$row['b_type'];
	$a['info'] = unserialize($row['b_info']);
	return $a;	
}
//**************************************************************************
function get_text_message($squad_no,$name,$year){
	$query = "squad_no='$squad_no' ";
	$query .= "and b_use='$name'";
	$query .= "and year='$year' ";
	$row = $this->search_for_record($query);
	if (!$row) return false; 
	$text = $row['b_data'];
	return $text;		
}
//**************************************************************************
function get_uniform_image($code){
	$query = "b_use='$code'";
	$row = $this->search_for_record($query);
	if (!$row) return false; 
	$a = array();
	$a['image'] = base64_decode($row['b_data']);
	$a['name'] = $code.'.'.$row['b_type'];
	$a['info'] = unserialize($row['b_info']);
	return $a;
}
//************************************************************************
function store_award_document($award_id, $rel_file_name, $mime, $doc_type, $year, $file){
/*
 * 
 * @var 
 * 
 Build a blobs entry to remember $file data 
	// Built a filename in URL format to directly call the file 
	// Build a blobs entry to remember $file data 
	// Build a filename in URL format to directly call the file 
	b_use - b_award_document
		item_part_no:award_id
		b_type: .pdf .doc .xls .csv 
		b_info: award document type - desc, reg, sch or (if spc then spc_name);
		b_size: document size in bytes - from $file array
		title: special (spc) document name 
		b_data = document 
*/
	if (! isset($site_url))
		$site_url = getSiteUrl();
	$url = "$site_url/$rel_file_name";

	$b_type = $file['type'];
	$query = "b_use = 'b_award_document' ";
	$query .= "and item_part_no = '$award_id' "; 
	$query .= "and b_info = '$doc_type' ";
	$query .= "and b_type = '$mime' " ;
	$rec = $this->search_for_record($query); 
	//$rec['b_data'] = base64_encode(file_get_contents($file['tmp_name']));
	if (isset($rec['id'])){
		$rec['title'] = $url;
		$this->store_record($rec,'id');
	}else{
		$rec['b_use'] = 'b_award_document';
 		$rec['item_part_no'] = $award_id;
		$rec['b_info'] = $doc_type;
		$rec['title'] = $url;
 		$rec['b_type'] = $mime  ;
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
function store_bklt_picture($squad_no,$b_use,$fname,$year=''){
	$fh = fopen($fname,"rb") ;
	//$fsize = filesize($fname) ;
	//$img = fread($fh,$fsize) ;
	//$img = base64_encode($img) ;
	$query = "squad_no='$squad_no' and b_use='$b_use' and year='$year'";
	$cur = $this->search_for_record($query); 
	$rec['squad_no']=$squad_no;
	$rec['b_use']=$b_use;
	$rec['b_data']=base64_encode(file_get_contents($fname)) ;
	$rec['b_type']='jpg';
	$rec['b_info']=serialize(getimagesize($fname));
	$rec['year']=$year;
	if ($cur){
		$rec['id']=$cur['id'];
		$this->store_record($rec,'id');
	}else{
		$this->add_record($rec);
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
function store_event_description($event_id, $txt){
	$b_use = 'b_event_description';
	$query = "item_part_no = '$event_id' and b_use='$b_use'";
	$rec = $this->search_for_record($query); 
	$rec['b_data']=$txt;
	$rec['b_type']='txt';
	if (isset($rec['id'])){
		$this->store_record($rec,'id');
	}else{
		$rec['b_use']=$b_use;
		$rec['item_part_no'] = $event_id;
		$this->add_record($rec);
	}
}
//************************************************************************
function store_event_document($event_id, 
								$rel_file_name, 
								$mime, 
								$doc_type, 
								$year, 
								$file, 
								$private = false){
/*
 * 
 * @var 
 * 
 Build a blobs entry to remember $file data 
	// Built a filename in URL format to directly call the file 
	// Build a blobs entry to remember $file data 
	// Build a filename in URL format to directly call the file 
	b_use - b_event_document
		item_part_no:event_id
		b_type: .pdf .doc .xls .csv 
		b_info: event document type - desc, reg, sch or (if spc then spc_name);
		b_size: document size in bytes - from $file array
		title: special (spc) document name 
		b_data = document 
*/
	if (! isset($site_url))
		$site_url = getSiteUrl();
	$url = "$site_url/$rel_file_name";

	$file_name = JPATH_BASE."/$rel_file_name";
	$fh = fopen($file_name,"rb") ;
	$fsize = filesize($file_name) ;
//  Read entire file into variable 
	$txt = base64_encode(fread($fh,$fsize)) ;
	
	
	
	$b_type = $file['type'];
	$query = "b_use = 'b_event_document' ";
	$query .= "and item_part_no = '$event_id' "; 
	$query .= "and b_info = '$doc_type' ";
	$query .= "and b_type = '$mime' " ;
	$rec = $this->search_for_record($query); 
	//$rec['b_data'] = base64_encode(file_get_contents($file['tmp_name']));
	$rec['b_data'] = $txt;
	$rec['b_size'] = $fsize;
	$rec['flag'] = '';
	if ($private){
		$rec['flag'] = 'private';
	}
	$rec['title'] = $url;
	if (isset($rec['id'])){
		$result = $this->store_record($rec,'id');
	}else{
		$rec['b_use'] = 'b_event_document';
 		$rec['item_part_no'] = $event_id;
		$rec['b_info'] = $doc_type;
		$rec['b_type'] = $mime  ;
		$result = $this->add_record($rec);
	}
	if ($result) {			
		return ''; 
	} else {
		return "Did not store or update Blobs table.";
	}
}
//************************************************************************
function store_flag_picture($fname,$rank,$width=180){
require_once('../php/simpleimage.php');

	$image = new SimpleImage();
	$image->si_load($fname);
	$image->si_resizeToWidth($width);
	$image->si_save('temp.jpg');
	$rec = $this->build_record_with_jpg_image('temp.jpg');
	$rec['flag']=$rank;
	$rec['b_use']='b_flag';
	$cur = $this->search_for_record("flag='$rank' and b_use='b_flag'"); 
	if ($cur){
		$rec['id']=$cur['id'];
		$this->store_record($rec,'id');
	}else{
		$this->add_record($rec);
	}
}
//************************************************************************
function store_item_picture($array,$fname,$width){
require_once('../php/simpleimage.php');
	// Reduce pixel size $width
	// Store pictures in database
	// b_use names of item_150x and item_640x 
	// 'item_part_no' col identifies item
	$image = new SimpleImage();
	$image->si_load($fname);
	$image->si_resizeToWidth($width);
	$n_name = $array['dir'].'/'."temp.jpg";
	//$n_name = $array['dir'].'/'.$array['item_part_no'].".jpg";
	$image->si_save($n_name);
	$row = $this->build_record_with_jpg_image($n_name);
	$row['b_use'] = $b_use = "item_$width";
	$row['squad_no'] = $array['squad_no'];
	$row['item_part_no'] = $item_part_no = $array['item_part_no'];
	$query = "item_part_no = '$item_part_no' and b_use = '$b_use'";
	$rec = $this->search_for_record($query);
	if ($rec){
		$row['id'] = $rec['id'];
		$this->update_record('id',$row);
	}else{
		$this->add_record($row);
	}
}
//************************************************************************
function store_mbr_picture($cert,$b_use,$fname,$width=60){
require_once('simpleimage.php');

	$image = new SimpleImage();
	$image->si_load($fname);
	$image->si_resizeToWidth($width);
	$image->si_save('temp.jpg');
	$rec = $this->build_record_with_jpg_image('temp.jpg');
	//$rec['b_type']='jpg';
	//$image_info = $image->si_image_info;
	//$image_info[0] = imagesx ($image->si_image);
	//$image_info[1] = imagesy ($image->si_image);
	//$image_info[3] = 'width="'.imagesx($image->si_image).'" ' ;
	//$image_info[3] .= 'height="'.imagesy($image->si_image).'" ' ;
	//$rec['b_info'] = serialize($image_info);
	//imagejpeg($image->si_image,NULL,100);
	//$rec['b_data']=base64_encode(ob_get_contents()) ;
	$rec['certificate']=$cert;
	$rec['b_use']=$b_use;
	$cur = $this->search_for_record("certificate='$cert' and b_use='$b_use'"); 
	if ($cur){
		$rec['id']=$cur['id'];
		$this->store_record($rec,'id');
	}else{
		$this->add_record($rec);
	}
}
//**************************************************************************
function store_roster_update_date(){
	$rec = $this->blank_record;
	// write the current date to the b_roster_last_update 
	$rec['b_use'] = 'b_roster_last_update';
	$rec['flag'] = date("Y-m-d");
	$this->add_record($rec);
}
//**************************************************************************
function store_txt_image($squad_no,$b_use,$fname,$title="",$year=''){
	$fh = fopen($fname,"rb") ;
	$fsize = filesize($fname) ;
//  Read entire file into variable 
	$txt = fread($fh,$fsize) ;
	$query = "squad_no='$squad_no' and b_use='$b_use'";
	$query .= " and year = '$year'";
	$rec = $this->search_for_record($query); 
	$rec['b_data']=$txt;
	$rec['b_type']='txt';
	$rec['title']=$title;
	if (isset($rec['id'])){
		$this->store_record($rec,'id');
	}else{
		$rec['year']=$year;
		$rec['squad_no']=$squad_no;
		$rec['b_use']=$b_use;
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
function store_session_stack($squad_no,$ip,$stack,$title='stack'){
	// Overwrites existing stack string
	//	b_session
	//		item_part_no = ip address of remote 
	//		squad_no = squad_no
	//		title = 'stack'
	//		b_info = stack contents
	$select = "b_use='b_session' and squad_no='$squad_no' and item_part_no='$ip' and title='$title' " ;
	$s_stack = serialize($stack);
	$array = array("b_info"=>$s_stack);
	$this->update($select,$array);
}
//**************************************************************************
function use_this_url($folder,$url){
	// strip off old url and replace for this site 
	$a_url = explode($folder,$url);
	return getSiteUrl()."/$folder".$a_url[1];
}
//**************************************************************************
}// class
?>