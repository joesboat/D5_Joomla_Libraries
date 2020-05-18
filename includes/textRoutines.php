<?php
/**
*	File System Routines.  
* 		Initially for text type files 
* 		Extended to handle folder and file manipulation routines  
* 
* 
* @param undefined $file_name
* @param undefined $dir
* @param undefined $ary
* 
* @return
*/
//********************************************************************************
function create_csv_file_from_array($file_name, $dir, $ary){
	// build a CSV file from $ary
	// All elements in $ary will become columns in file 
	// CSV header row will consist of element (key) namess
	$eol = "\r\n" ;
	//$dir = "C:/Users/Joe/Documents/My Projects/USPS Sail Angle Interface/";
	//$str = date("Ymd", time()-(60*60*6));
	$fh = fopen($dir . $file_name,'w');
	if (!$fh){
		printf("Can't open the session_log.txt file");
		exit(0);
	}
	// OK we have a file, now let's fill it with enrollment data
	$str = date("l F j, Y - g:ia", time()-(60*60*6)); 
	$i = fwrite($fh, $eol.$str.$eol.$eol);
	//$array=$enr->blank_record;
	$i = fwrite($fh, $str.$eol.$eol);
	$str="";
	foreach($ary[0] as $key=>$value){
		$str .=$key.",";
	}
	$i = fwrite($fh, $str.$eol);
	foreach($ary as $i=>$a){
		$str="";
		foreach($a as $key=>$value){
			$str.=$value.",";
		}
		$i = fwrite($fh, $str.$eol);
	}
	fclose($fh);
	return $file_name;
}
//*****************************************************************************
function sspackage_officr_list_csv_file($jobcode, $rows, $fh){
global $vhqab, $sqds, $addr;
	$codes = $vhqab->getJobcodesObject();
	$addr = $vhqab->getD5AddressesObject();
$eol = "\r\n" ;
// writes to file $fh
// gets job description and writes to first record 
// packages member's data into a .csv format 
// gets and puts squadron in first column
// puts member name in second column
// puts email in third column
// puts telephone in fourth column
// puts celphone in fith column 
	$job_name = $codes->get_job_name($jobcode);
	fwrite($fh,$job_name.$eol);
	$str = "'Squadron','Member Name','Email','Home Phone','Cell Phone','address','city','state','zip','ID Expiraton'";
	fwrite($fh,$str.$eol);
	foreach($rows as $row){
		$row = $addr->get_and_add_member_address($row);
		$squadron = $vhqab->getSquadronName($row['squad_no']);
		$name = $vhqab->getMemberName($row['certificate']);
		$email = $row['email'];
		$phone = $row['telephone'];
		$cell = $row['cell_phone'];
		$address = str_replace(',',' ',$row['address_1']);
		$city = $row['city']; 
		$state = $row['state'];
		$zip = $row['zip_code'];
		$idexpr = $row['idexpr'];
		$str = "'$squadron','$name','$email','$phone','$cell','$address','$city','$state','$zip','$idexpr'";
		fwrite($fh,$str.$eol);
	}
}
//*****************************************************************************
function sspackage_csv_file_for_google($rows,$fh){
global $vhqab, $sqds;
	$codes = $vhqab->getJobcodesObject();
	$eol = "\r\n" ;
// writes to file $fh
// packages member's data into a .csv format 
// puts member first name in first column
// puts member last name in second column
// puts email in third column
// puts telephone in fourth column
// puts celphone in fith column 
	//$job_name = $codes->get_job_name($jobcode);
	//fwrite($fh,$job_name.$eol);
	$str = "First Name,Last Name,E-Mail Address,Home Phone,Mobile Phone,Squadron";
	fwrite($fh,$str.$eol);
	foreach($rows as $row){
		$first = $row['first_name'];
		$last = $row['last_name'];
		$email = $row['email'];
		$phone = $row['telephone'];
		$cell = $row['cell_phone'];
		$squad_no = $row['squad_no'];
		//$address = str_replace(',',' ',$row['address_1']);
		//$city = $row['city']; 
		//$state = $row['state'];
		//$zip = $row['zip_code'];
		$str = "$first,$last,$email,$phone,$cell,$squad_no";
		fwrite($fh,$str.$eol);
	}
}
//*****************************************************************************
function read_sql_file(&$fh){
// Ignores lines beginning with '-'
// Ignores data following '/*' until '*/;' 
// Searches for SQL Commands 'INSERT' or 'CREATE'
// When found, reads stream into variable until ';' is found. 
// Returns string
	$command = "";
	$saving = false; 
	while (true){
		if (($input = fgets($fh)) == false) return false;
		$input = trim($input); 
		if ($input=='') continue;
		$c = substr($input,0,1) ;
		switch ($c){
		case '-':
			break;
		case '/':
			return $input; 
		default;
			$x = strlen($input);
			if (strtolower(substr($input,0,3))=='set') break;
			if (strtolower(substr($input,0,4))=='lock') break;
			$ix = stripos($input,';');
			if (substr($input,0,6) == 'INSERT')
				while (substr($input,$ix-1,1) != ')'){
					$ix = stripos($input,';',$ix+1);
				}
			if ($ix){
				if ($saving){
					$saving = false; 
					$command .= $input; 
					return $command; 
				} 
				return $input; 
			} else {
				//  We must continue to build 
				$command .= $input;
				$saving = true;  
			}
		}
	}
}
//********************************************************************************
function extract_single_insert_into(&$str){
	// Extracts and returns the individual records from a INSERT_INTO list
	// Find first (
	$left = stripos($str,'(');
	// Find forst )
	$right = stripos($str, ')') + 1;
	// Copy data before ( to be reused
	while (substr($str,$right,1) != ','){
		$yyy = substr($str,$right,1);
		$right = stripos($str, ')', $right+1) + 1;
	}
				
	$cmd = substr($str,0,$left);
	// Extract contents of () and rebuild $str
	$tmp = $cmd.substr($str,$right+1,strlen($str)-($right+1));
	// Build new command 
	$rtn = $cmd.substr($str,$left,$right-strlen($cmd)).';';
	$str = $tmp; 
	return $rtn;
	
}		 
//********************************************************************************
function storeExtraFile($event_id, $file, $doc_type, $year){
	/*
	// Relocate uploaded file 
		// Create file name 
		// Ensure folder is present 
		// Store file 
	// Return full file name  
Parameters
	$event_id - stored in item_part_no
	$file, 		// An input file array (size, tmp_name, type, name) 
	$doc_type,	// Stored in b_info - the document's use 
	$b_use = '')// Optional special doc type		

*/
	$folder = "events/$year";
	$file_name = formExtraFileName($event_id, $file, $doc_type, $year)	;
	// Relocate uploaded file to /events/$year/$squad_no folder 
	$abs_folder = JPATH_BASE.'/'.$folder ;
	$in_file = $file['tmp_name'];
	$idd = is_dir($abs_folder);
	//log_it("isdir retuns: $idd for $abs_folder");
	$fe = file_exists($abs_folder);
	//log_it("file_exists returns $fe for $abs_folder");
	chmod($in_file,0755);
	if (! file_exists(JPATH_BASE.'/'.$folder)){
		mkdir($abs_folder,0755);
	}
	$abs_file = JPATH_BASE.'/'.$folder.'/'.$file_name;
	copy($in_file,JPATH_BASE.'/'.$folder.'/'.$file_name);
	return $folder.'/'.$file_name ;
}
//********************************************************************************
function formExtraFileName($event_id, $file, $doc_type, $year){
	$file_name = $event_id.'_'.strtolower($doc_type).".".getFileSuffix($file['name']) ;
	return $file_name;

		
}
//********************************************************************************
function deleteExtraFile(){

}
//********************************************************************************
function getFileSuffix($name){
	$a_filename = explode('.',$name);
	$sfx = $a_filename[count($a_filename)-1];
	return $sfx;
}

?>