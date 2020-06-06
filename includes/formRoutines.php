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
function abreviate_address($s){
	$adr = str_replace('Drive','Dr',$s);
	$adr = str_replace('Village','Vlg.',$adr);
	return $adr;
}
//*********************************************************
function abreviate_sqd_name($s){
	$sqd = str_replace('Sail','S',$s);
	$sqd = str_replace('Power','P',$sqd);
	$sqd = str_replace('Americas Boating Club','ABC',$sqd );
	$sqd = str_replace('Squadron','',$sqd);
	$sqd = str_replace('S & P', 'S&P', $sqd);
	$sqd = str_replace('Mountain','Mntn.',$sqd);
	$sqd = str_replace('River','Rvr',$sqd);
	$sqd = str_replace('Lake','Lk',$sqd);
	$sqd = str_replace('Virginia','VA',$sqd);
	$sqd = str_replace('Maryland','MD',$sqd);
	$sqd = str_replace('Pennsylvania','PA',$sqd);
	$sqd = str_replace('Beach','Bch',$sqd);
	$sqd = str_replace('Valley','Vly.',$sqd);
	$sqd = str_replace('Provisional','Prov.',$sqd);
	$sqd = str_replace("America's Boating Club",'ABC',$sqd);
	$sqd = str_replace("&reg;",'',$sqd);
	
	return $sqd;
}
//*********************************************************
function abreviate_towns($s){
	$str = str_replace('Valley','Vly',$s);
	$str = str_replace('Market','Mkt',$str);
	return $str;
}
//*********************************************************
function abreviate_job_description($n){
	$n = str_replace('','',$n);
	$n = str_replace('','',$n);
	$n = str_replace('Aides','Aide',$n);
	$n = str_replace('Delaware','DL',$n);
	$n = str_replace('Support Team','',$n);
	$n = str_replace('Government','Gvmt.',$n);
	$n = str_replace('Partner','Part.',$n);
	$n = str_replace('Relations','Rel.',$n);	
	$n = str_replace('Poster Contest Youth Activities','Post. Ctst. Youth Activ.',$n);
	$n = str_replace('Boating Authority Liaisian','Boat. Auth. Lias.',$n);	
	$n = str_replace('Boating','Boat.',$n);
	$n = str_replace('Vessel Safety Check','VSC',$n);	
	$n = str_replace('Public Boating Course','Pub. Boat. Crs.',$n);	
	$n = str_replace('Coordinator','Crd.',$n);	
	$n = str_replace('Snyder Award Committee','Snyder Awd.',$n);	
	$n = str_replace('Offshore Navigation','Offshore Nav.',$n);
	$n = str_replace('Meetings','Mtg.',$n);	
//	$n = str_replace('Assistant,','Asst.',$n);
	$n = str_replace('Assistant','Asst.',$n);
//	$n = str_replace('Educational','Ed.',$n);
//	$n = str_replace('Education','Ed.',$n);
//	$n = str_replace('Membership','Mbrshp.',$n);
	$n = str_replace('Member', 'Mbr.',$n);
	$n = str_replace('Chairman,','Chr.',$n);
	$n = str_replace('Chairman','Chr.',$n);
	//$n = str_replace('Squadron','Sqdn',$n);
	$n = str_replace('Publications','Pub',$n);
	$n = str_replace('Committee','Cmte',$n);
	$n = str_replace('Systems','Sys',$n);
	$n = str_replace(' and ','&',$n);
	$n = str_replace(' - ',' ',$n);
	$n = str_replace('Technology','Tech.',$n);
	$n = str_replace('Cooperative','Coop.',$n);
	$n = str_replace('Mechanical','Mech.',$n);
	$n = str_replace('District','D5',$n);
	$n = str_replace('Certification','Cert.',$n);
	$n = str_replace('On the Water','OTW',$n);
	$n = str_replace('Training','Tng.',$n);
	$n = str_replace('Student','Sdnt.',$n);
	$n = str_replace('Support','Supt.',$n);
	$n = str_replace('Announcement','Ansmt.',$n);
	$n = str_replace('Completion','Compl.',$n);
	$n = str_replace("Operator",'Opr',$n);
	$n = str_replace('Programs','Prgms.',$n);
	$n = str_replace('Leadership','Ldrshp.',$n);
	$n = str_replace('Development','Devl.',$n);
	$n = str_replace('New Jersey','NJ',$n);
	$n = str_replace('Pennsylvania','PN',$n);
	$n = str_replace('Inland','Inl.',$n);
	$n = str_replace('Advanced','Adv.',$n);
	$n = str_replace('Administrative','Admn.',$n);
	$n = str_replace('Maintenance','Maint.',$n);
	$n = str_replace('Scheduling','Sch.',$n);
	$n = str_replace('Summer','Smr.',$n);
	$n = str_replace('Provisional','Prov.',$n);
	$n = str_replace('','',$n);
	$n = str_replace('','',$n);
	$n = str_replace('','',$n);
	$n = str_replace('','',$n);
	$n = str_replace('','',$n);
	$n = str_replace('','',$n);
	$n = str_replace('','',$n);
	return $n;	
}
//*********************************************************
function abreviate_job($n){
	$n = str_replace('Chairman,','',$n);
	return trim($n);
}
//*********************************************************
function fix_value($value){
	if (is_array($value)){
		$xxx = $value;
	}
	$value = stripcslashes($value);
	$value = trim($value);
	$value = fix_single_quote($value);
	$value = fix_double_quote($value);
	return $value;
}
//*********************************************************
function fix_double_quote($str){
$str = str_replace('"','\"',$str);
return $str;
}
//*********************************************************
function fix_single_quote($str){
/*	$i = strpos($str,"'");
	$s = "";
	while ($i){
		// OK, we have to add delimeters 
		$s.=substr($str,0,$i) ."&acute;";
		$str=substr($str,$i+1,strlen($str)-$i-1);
		$i=strpos($str,"'");
	}
	$s=$s.$str;
	return $s;
*/
$str = str_replace("'","\'",$str);
return $str;
}
//*********************************************************
function format_telephone_number($area,$phone){
	// Corrects the format of phone numbers 
	// Assumed called when a number is stored to database
	if ($area == 0) return "";
	$nn = format_to_number($area).format_to_number($phone); 
	if (strlen($nn) < 7) return "";
	$nn = substr($nn,0,3)." ".substr($nn,3,3)."-".substr($nn,6,4); 
	return $nn; 
	// first nutralizes number to a string of 10 numbers
}
//*********************************************************
function format_to_number ($str){
	$s = str_replace(')','',$str); 
	$s = str_replace('(','',$s);
	$s = str_replace(' ','',$s);
	$s = str_replace('-','',$s);
	return $s;
}
//*********************************************************
function show_head_data($style='', $script=''){
// Displays headings for routines in 'private' directory 
$document = JFactory::getDocument();
?>
<meta http-equiv="content-type" content="text/html; charset=utf8" / >
<META name="description" content="The United States Power Squadrons (America's Boating Club), stressing: Community service, continuing education, and social activities among members" / >
<META name="keywords" content="education, boating, safety, outdoors, recreation, sailing, community, service, boater, boating, courses, safety, laws, license,groups, organizations, associations, fun, usps, fishing, freshwater, saltwater, united states power squadrons, america's boating club" />
<META name="copyright" content="1999-2015 United States Power Squadrons" />

<!--<link rel="stylesheet" href="<?php echo getSiteUrl() ;?>/templates/usps-site/css/bootstrap.css"/>
<link rel="stylesheet" href="<?php echo getSiteUrl() ;?>/plugins/system/t3/base/bootstrap/css/bootstrap-responsive.css"/>
<link rel="stylesheet" href="<?php echo getSiteUrl() ;?>/templates/usps-site/css/bootstrap-datepicker3.css"/>

<script type="text/javascript" src="<?php echo getSiteUrl() ;?>/plugins/system/t3/base/js/jquery-1.11.2.js"></script>
<script type="text/javascript" src="<?php echo getSiteUrl() ;?>/plugins/system/t3/base-bs3/bootstrap/js/bootstrap.js"></script>
<script type="text/javascript" src="<?php echo getSiteUrl() ;?>/templates/usps-site/js/bootstrap-datepicker.js"></script>-->
<script type="text/javascript" src="<?php echo getSiteUrl() ;?>/scripts/JoesSlideShow.js"></script>

<?php 
}
//*********************************************************
function get_head_data(){
$str=<<<EOT
	<meta http-equiv="content-type" content="text/html; charset=utf8" />
	<meta http-equiv="content-type" content="text/html; charset=utf8" />
	<meta http-equiv="content-type" content="text/html; charset=utf8" />
	<META name="description" content="The United States Power Squadrons (America's Boating Club), stressing: Community service, continuing education, and social activities among members">
	<META name="keywords" content="education, boating, safety, outdoors, recreation, sailing, community service, boater education, boating education, public boating courses, safe boating courses, state boating laws, boating safety, boating groups, boating organizations, boating associations, boating fun, usps, fishing, freshwater, saltwater, united states power squadrons, america's boating club">
	<META name="copyright" content="1999-2002 United States Power Squadrons">
	<link type="text/css" rel="Stylesheet" media="screen" href="../css/d5.css" ></LINK>
	<SCRIPT type="text/javascript" src="../scripts/dhtmlgoodies_calendar.js"></script>
	<script type="text/javascript" src="../scripts/JoesSlideShow.js"></script>
EOT;
return $str;	
}
//*********************************************************
function is_ie_10(){
	$a_agent = explode(';',$_SERVER['HTTP_USER_AGENT']);
	foreach($a_agent as $compat){
		if (substr(trim(strtolower($compat)),0,8) == 'msie 10.' )
			return true;
	}
	return false;
}
//*********************************************************
function print_d5_officer_name($obj,$dept,$year,$line=true){
global $exc, $vhqab;
	$query = "jobcode=$dept and year='$year'";
	$excom = $exc->search_record($query);
	$officer = $vhqab->getD5Member($excom['certificate']);
	// Save the current font data 
	$family = $obj->FontFamily;
	$size = $obj->FontSizePt;
	$style = $obj->FontStyle;
	// $obj->Cell(0,ROW_HEIGHT,"$family    $style    $size",0,1,'C');
	$obj->SetFont($family,'B',$size+1);
	$obj->Ln(ROW_SEPERATOR);
	$y = $obj->GetY();
	if($line)
		$obj->Line(.25,$y,5.25,$y);
	$obj->Ln(ROW_SEPERATOR / 2);
	$obj->print_d5_hdr_row(strtoupper($excom['excom_position']),
							$exc->get_d5_member_name(false,$officer));
	$obj->Ln(ROW_SEPERATOR);
	$obj->SetFont($family,$style,$size);
}
//*********************************************************
function show_blank_rows($n){
	while ($n > 0 ){
		echo '<tr class="table"><td colspan="2">&nbsp;</td></tr>' ;
		$n --;
	}
}
///********************************************************
function show_char_form_header($heading, $action){
	echo '<html>';
	echo '<head>';
	echo show_head_data();
	echo '<style>body{overflow:auto;}.btn{width:200px;}</style>';
	echo '</head>';
	echo '<body>';
	echo '<form method="post" name="fh1" id="fh1" action="'.$action.'" enctype="application/x-www-form-urlencoded">';
	echo "<h2>$heading</h2>";
}
//*********************************************************
function show_d5_banner($title,$lev,$sb_count = 1){
	echo "<div id='banner'></div>";
	echo "<div id='template_logo'><img src='";
	echo $lev."images/template_logo.gif' width='284' height='56'></div>";
	echo "<div id='d5_flag'>";
	echo "<img src='$lev images/d5_flag.gif' width='80' ></div>";
	echo "<div id='burgees'>";
	while ($sb_count > 0 ){
		echo "<img src='$lev images/sq_burgees.jpg' width='70' height='630'>";
		$sb_count --;
	}
	echo "</div>";
	echo "<div id='title'>";
  	echo "<div align='center' class='style18b color_white'>$title</div>";
	echo "</div>";
}
//*********************************************************
function show_d5_hdr_row($fun,$name,$class){
	echo "<tr>";
	if ($name == ""){
	    echo "<td class='$class' style='font-weight: bold;' colspan='2'>$fun</td>";
	} else {
	    echo "<td class='$class' style='font-weight: bold;'>$fun</td>";
	    echo "<td class='$class'>$name</td>";
	}
    echo "</tr>";
}
//*********************************************************
function show_d5_popup_calls($dir, $ss = false){
//	if (is_ie_10()){
		echo "<div id='Menus'>";
		show_menus(); 
		echo "</div>";
		return 0;
//	}
$str = <<<EOT
<!-- Begin of Pop Up Code -->
<!-- Pop-Up DHTML menu is © by Anoxy Software -->
<!-- |4.8.0|V|AV|NHD|NL|ANM|NFX|ND&D|NSND|FL|ASW|NASZ|NSRL| -->
<script language="JavaScript" src="popblank.js" type="text/javascript"></script>
<script language="JavaScript" type="text/javascript">
<!--
mpx = 20;
mpy = 150;
EOT;
echo $str; 
echo 'popbasedir = "'.$dir.'";';  
$str = <<<EOT
peXt = (navigator.userAgent.indexOf("Opera")!=-1 && document.getElementById) ? ((document.body.insertAdjacentHTML) ? "mo" : "op") : (navigator.userAgent.indexOf("Konqueror")!=-1 && document.getElementById) ? "ko" : (document.all) ? "ie" : (document.layers) ? "nn" : (!document.all && document.getElementById) ? "mo" : "";
if (peXt!="")
document.write('<scr'+'ipt language="JavaScript" src="'+popbasedir+'popup'+peXt+'.js"></scr'+'ipt>');
window.onload = onloevha;
popXURLV = "";
function onloevha(){
if (peXt=="nn")
popmcreate();
if ($ss)
	set_D5_Store();
}
////-->
</script>
<layer Name="DboX" style="position:absolute"></layer>
<!-- End of Pop Up Code -->
EOT;
echo $str; 
echo "<script type='text/javascript' src='scripts/JoesSlideShow.js'></script>";
}
//*********************************************************
function showHeader($heading, $action, $style='',$script=''){
	
	//echo "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>";
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<?php show_head_data(); ?>
	<style>
		body{overflow:auto; }
		.btn{width:200px;}
		<?php echo $style; ?>
	</style>
	<?php echo $script; ?>
</head>
<body>
	<form 	class ='form-inline'
			method='post' 
			id='fh1' 
			action='<?php echo $action; ?>'
			enctype='multipart/form-data'>
	<h3><?php echo $heading;?></h3>
<?php
}
//*********************************************************
function showTrailer($cncl = false){
if ($cncl) {
?>
	Press <strong>Cancel</strong> to return without making a change - 	
		<input type="submit" id="cancel_command" name="command" value="Cancel" />
<?php } ?>	
	</form>
	</body>
	</html>
<?php
}
//*********************************************************
function show_option_list($ary, $sel){
	// The supplied array contains a list of items of the format:
	//	ID => NAME 
	// Function will build an option list in the following format:
	//	<option value="ID">NAME</option>
	// If $sel <> "" and is = to an ID:
	//	add the value "selected" following ID "
	// Otherwise add the following at end of list
	//	<option value="new" selected>Select a new item</select>
	$found=FALSE;
	foreach($ary as $key=>$value){
		$str = '<option value="' . $key . '"' ; 
		if (strtoupper($key) == strtoupper($sel)) {
			$str .= ' selected ' ; 
			$found = true ;
		}
		$str .= ">" . $value . '</option>' ; 
		echo $str ; 
	}
	if (!$found){
		$str = '<option value="" selected>Select from list.</select>' ;
		echo $str ; 
	}
}
//*********************************************************
function show_popup_form_header($heading,$action,$style=''){
	global $update_ok;
	echo '<html>';
	echo '<head>';
	echo show_head_data($style);
	echo '<style>body{overflow:auto;}.btn{width:200px;}</style>';
	echo '</head>';
	if ($update_ok){
		echo '<body onload="opener.window.location.reload();self.close();return false;">';
	} else {
		echo '<body onload="setsize(400,400);">' ;
	}
	echo "<form method='post' action='$action' >";
	echo "<h2>$heading</h2>";
}
?>