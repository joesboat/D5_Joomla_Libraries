<?php
//require_once("/home/content/82/7781582/html/libraries/usps/tableAccess.php");
//require_once("c:/users/joe/websites/uspsd5/libraries/usps/tableAccess.php");
//require_once("/web/joomla/libraries/usps/tableAccess.php");
require_once(JPATH_LIBRARIES ."/usps/tableAccess.php");
//************************************************************************************
class table_USPSblobs extends USPStableAccess{
	// Generic routines to manage a table 
//********************************* Public Variables **********************************
//********************************* Private Variables *********************************
//private $manager ;
/*
	b_use 
		y_manage			
		y_display
		mbr_picture_60
		mbr_picture_80
		mbr_picture_640
		item_150
		item_640
		b_cmd_msg
		b_custom_1
		b_custom_2
		b_event_description
		b_female_uniform
		b_flag
		b_front_page
		b_male_uniform
		b_membership_1
		b_membership_2
		b_small_image
		b_signature_image
		
*/
//********************************************************************************************
function __construct($db, $caller=''){
		// Creates the variables to contain identity of data and tables 
	parent::__construct('sss_blobs', $db, $caller);
		//$this->list_cols=$col_subset; 
		//$this->cols=$col_list;		
}// constructer
/*
//**************************************************************************
function build_record_with_jpg_image($fname){
	$rec['b_data']=base64_encode(file_get_contents($fname)) ;
	$rec['b_type']='jpg';
	$rec['b_info']=serialize(getimagesize($fname));
	return $rec;
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
function get_cdr_message($squad_no,$year=''){
	$query = "squad_no='$squad_no' and b_use='b_cmd_msg'";
	if ($squad_no == '6410'){
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
*/
//**************************************************************************
function get_event_description($event_id){
	$b_use = 'b_event_description';
	$query = "item_part_no='$event_id' and b_use='$b_use'";
	$row = $this->search_for_record($query);
	if (!$row) return ''; 
	switch($row['b_type']){
		case 'txt':
			return $row['b_data'];
		default:
			return "";
	}
}
/*
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
	if ($squad_no == '6410'){
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
function get_signature_image($squad_no,$year=''){
	$query = "squad_no='$squad_no' and b_use='b_signature_image'";
	if ($squad_no == '6410'){
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
	if ($squad_no == '6410'){
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
//**************************************************************************
function store_bklt_picture($squad_no,$b_use,$fname,$year=''){
	$fh = fopen($fname,"rb") ;
	//$fsize = filesize($fname) ;
	//$img = fread($fh,$fsize) ;
	//$img = base64_encode($img) ;
	$query = "squad_no='$squad_no' and b_use='$b_use'";
	if ($squad_no == '6410'){
		$query .= " and year = '$year'";
	}
	$cur = $this->search_for_record($query); 
	$rec['squad_no']=$squad_no;
	$rec['b_use']=$b_use;
	$rec['b_data']=base64_encode(file_get_contents($fname)) ;
	$rec['b_type']='jpg';
	$rec['b_info']=serialize(getimagesize($fname));
	if ($squad_no == '6410'){
		$rec['year']=$year;
	}
	if ($cur){
		$rec['id']=$cur['id'];
		$this->store_record($rec,'id');
	}else{
		$this->add_record($rec);
	}
}
*/
//************************************************************************
function delete_event_description($event_id){
	$b_use = 'b_event_description';
	$query = "item_part_no = '$event_id' and b_use='$b_use'";
	$this->delete_range($query);
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
/*
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
require_once('../php/simpleimage.php');

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
function store_txt_image($squad_no,$b_use,$fname,$title="",$year=''){
	$fh = fopen($fname,"rb") ;
	$fsize = filesize($fname) ;
//  Read entire file into variable 
	$txt = fread($fh,$fsize) ;
	$query = "squad_no='$squad_no' and b_use='$b_use'";
	if ($squad_no == '6410'){
		$query .= " and year = '$year'";
	}
	$rec = $this->search_for_record($query); 
	$rec['b_data']=$txt;
	$rec['b_type']='txt';
	$rec['title']=$title;
	if (isset($rec['id'])){
		$this->store_record($rec,'id');
	}else{
		if ($squad_no == '6410'){
			$rec['year']=$year;
		}
		$rec['squad_no']=$squad_no;
		$rec['b_use']=$b_use;
		$this->add_record($rec);
	}
}
*/
//**************************************************************************
}// class
?>