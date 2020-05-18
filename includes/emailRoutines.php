<?php
//*************************************************************
function build_email_address($r){
	return get_person_name($r)."<".$r['email'].">";
}
//*********************************************************
function send_inquiry_message($array){

// Send a message to several EXCOM members notifying them of new entry.

	$subject = 'Member Database Update';
	
$message = <<<_END

The following person has requested additional information about RSPS   
{$array['first_name']} {$array['last_name']}  
E-mail: {$array['email']} Telephone: {$array['telephone']} Cell Phone: {$array['cell_phone']} 
Additional information may be available in guest records. 
{$array['comments']}
_END;

	$mail_head= 'From: webmaster@abc-midatlantic.org' . chr(13) . chr(10)  .
				'Reply-To: webmaster@abc-midatlantic.org' . chr(13) . chr(10)  .
				'X-Mailer: PHP/' . phpversion();

	$sent = mail($to, $subject, $message, $mail_head);
	if (!$sent){	
		log_it("Did not send e-mail message.");	
	}
	return $sent; 	
}

//*********************************************************
function send_education_message($array, $class){

// Send a message to several EXCOM members notifying them of new entry.

	$cc = $array['email'];
	$subject = 'Guest Interest in '.$class['class_start_date']." ".$class['class_name'];
	
$message = <<<_END

The following person has requested information about this RSPS Training Class: 
{$array['first_name']} {$array['last_name']} E-mail: {$array['email']} 
Telephone: {$array['telephone']} Cell Phone: {$array['cell_phone']} 
Please make contact at your earliest convenience to arrange enrollment. 
_END;
	$mail_head=	'From: webmaster@abc-midatlantic.org' . chr(13) . chr(10)  .
				'Reply-To: webmaster@abc-midatlantic.org' . chr(13) . chr(10)  .
				'X-Mailer: PHP/' . phpversion();
	
	$sent = mail($to, $subject, $message, $mail_head);
	if (!$sent){	
		log_it("Did not send e-mail message.");	
	}
	return $sent; 
}

//*********************************************************
function send_update_message($dif,$member,$to){
	if ($_SERVER['REMOTE_ADDR']=='127.0.0.1') return true;
	$subject = 'Member Database Update';
	$message =	"The following user had made updates " . 
				"to the District 5 Member Database: " . chr(13) . chr(10)  .
				get_person_name($member) . ' ' . 
				"Certificate Number " . $member['certificate'] . chr(13) . chr(10);
	foreach($dif as $key => $value){
		$message .= $key . " changed to " . $value   . chr(13) . chr(10);	
	}				
	$message .= "Please make these updates to your Squadron's DB2000 Database!"; 
	$mail_head =	'From: webmaster@abc-midatlantic.org' . chr(13) . chr(10)  .
					'Reply-To: webmaster@abc-midatlantic.org' . chr(13) . chr(10)  .
					'X-Mailer: PHP/' . phpversion();
	
	$sent = mail($to, $subject, $message, $mail_head);
	if (!$sent){	
		log_it("Did not send e-mail message.");	
	}
	return $sent; 
}

//*********************************************************
function send_password_message($pw,$member){
global $exc;
// Notify the member they may update to a new password and send a one time use link that they may use to start the process.  
	$siteUrl = getSiteUrl();
	$eol = PHP_EOL;
	$toname = get_person_name($member); 
	$to = build_email_address($member);
	$subject = 'Private USPS District 5 Web Site Data';
	$message = "USPS District 5 Member - " ;
	$message .= $exc->get_d5_member_name(false,$member)."\r\n\r\n";
	$message .= "Hello $toname:$eol$eol" ;
	$msg = "The new private 'Members Only' area of the USPS District 5 WEB site is now available.  You may access it through this link - '$siteUrl/private/member_control.php' - or through the 'Members Only' link on many D5 site pages. All communications to and from this area of the D5 site are encrypted for privacy. $eol" ;
	$message .= wordwrap($msg, 70); 
	$msg = "Only recognized D5 members have access to the Roster and other information in the members only section.  You must identify yourself (Select the 'Log-in' option and enter data in the appropriate field.} with your USPS Certificate number and the unique password provided below.  After validation you will be returned to the base Member's' page. $eol " ;
	$message .= wordwrap($msg, 70); 
	$msg = "There are several features immediately available, the full D5 Roster, your squadron roster, a D5 Committee Maintenance tool and a squadron job maintenance tool. Information on their use is being sent through separate communications.$eol"; 
	$message .= wordwrap($msg, 70); 
	$msg = "The roster is available to all D5 members. It displays the members contact data and lists Squadron, District and National Jobs assignments.  You may modify the contact data in your member record. $eol";
	$message .= wordwrap($msg, 70); 
	$msg = "The job assignment tools are only available to Officers.$eol $eol";
	$message .= wordwrap($msg, 70); 
	$msg = "Your initial password is:    $pw   It can be used permanently, however we suggest you change it to a unique phrase (no spaces) only you know.  $eol $eol";
	$message .= wordwrap($msg, 70); 
	$msg = "We ask that you access the roster and review your contact information.  We display the current data known to USPS.  You may make updates directly in the Data Record screen if needed.  Please call me (301-977-3058) or send a message (webmaster@abc-midatlantic.org) if you have any difficulties. $eol";
	$message .= wordwrap($msg, 70); 
	$message .= "Sincerely, $eol $eol" ;
	$message .= "P/C Joseph P. Gibson $eol" ;
	$message .= "District 5 Webmaster 	$eol";
	//$mail_head =	' From: webmaster@abc-midatlantic.org' . chr(13) . chr(10)  .
	//				' Reply-To: webmaster@abc-midatlantic.org' . chr(13) . chr(10)  .
	//				' X-Mailer: PHP/' . phpversion();
	$mail_head =	" From: webmaster@abc-midatlantic.org $eol"  .
					" Reply-To: webmaster@abc-midatlantic.org $eol"  .
					" X-Mailer: PHP/" . phpversion();
	$header = "From: webmaster@abc-midatlantic.org \n";
	$header .= "Reply-To: webmaster@abc-midatlantic.org \n";
	$header .= "MIME-Version: 1.0\n";	
	if ($_SERVER['REMOTE_ADDR']=='127.0.0.1') return true;
	$sent = mail($to, $subject, $message, $header);
	if (!$sent){	
		log_it("to is $to"); 
		log_it("subject is $subject"); 
		log_it("mail_head is $mail_head"); 
		log_it("Did not send e-mail message.");	
	}
	return $sent; 
}
//*********************************************************
function send_password_update_request_message($member,$url){
global $exc;
	$toname = get_person_name($member); 
	$to = build_email_address($member);
	$subject = 'Private USPS District 5 Web Site Data';
	$message = "USPS District 5 Member - " ;
	$message .= get_mbr_name_and_grade($member)."\r\n\r\n";
	$message .= "Hello $toname:\r\n\r\n" ;
	$msg = "We believe you have requested that your password to the www.uspsd5.org Members Only tools be updated.  If this is correct you may complete the process by accessing $url and following the instructions.  Please note this is a one-time-use action that will expire within 24 hours. \r\n" ;
	$message .= wordwrap($msg, 70); 
	$msg = "If you did not request a new password please forward this email to webmaster@uspsd.org.  \r\n";
	$message .= wordwrap($msg, 70); 

	$mail_head =	' From: webmaster@abc-midatlantic.org' . chr(13) . chr(10)  .
					' Reply-To: webmaster@abc-midatlantic.org' . chr(13) . chr(10)  .
					' X-Mailer: PHP/' . phpversion();
	if ($_SERVER['REMOTE_ADDR']=='127.0.0.1') return true;
	$sent = mail($to, $subject, $message, $mail_head);
	if (!$sent){	
		log_it("Did not send e-mail message.");	
	}
	return $sent; 
}
//*********************************************************
function send_note_with_attachments($member, $subject, $message, $files){
	$toname = get_person_name($member); 
	$to = build_email_address($member);
	$random_hash = md5(date('r', time())); 
	$mime_boundary = '==MIME_BOUNDARY_' . md5(time());
	
	$mail_head =	' From: webmaster@abc-midatlantic.org' . chr(13) . chr(10)  .
					' Reply-To: webmaster@abc-midatlantic.org' . chr(13) . chr(10)  .
					' X-Mailer: PHP/' . phpversion(). chr(13) . chr(10)  .
					' MIME-Version: 1.0 ' . chr(13) . chr(10)  .
					' Content-Type: multipart/mixed; boundary="' . $mime_boundary . '"' . chr(13) . chr(10)  ;

	// Message Body
	$message_string  = '--' . $mime_boundary;

	$message_string .= "\r\n";
	$message_string .= 'Content-Type: text/plain; charset="iso-8859-1"';
	$message_string .= "\r\n";
	$message_string .= 'Content-Transfer-Encoding: 7bit';
	$message_string .= "\r\n";
	$message_string .= "\r\n";
	$message_string .= $message;
	$message_string .= "\r\n";
	$message_string .= "\r\n";

	// Add attachments to message body
	foreach($attachments as $local_filename => $attachment_filename) {
		if(is_file($local_filename)) {
			$message_string .= '--' . $mime_boundary;
			$message_string .= "\r\n";
			$message_string .= 'Content-Type: application/octet-stream; name="' . $attachment_filename . '"';
			$message_string .= "\r\n";
			$message_string .= 'Content-Description: ' . $attachment_filename;
			$message_string .= "\r\n";

			$fp = @fopen($local_filename, 'rb'); // Create pointer to file
			$file_size = filesize($local_filename); // Read size of file
			$data = @fread($fp, $file_size); // Read file contents
			$data = chunk_split(base64_encode($data)); // Encode file contents for plain text sending

			$message_string .= 'Content-Disposition: attachment; filename="' . $attachment_filename . '"; size=' . $file_size.  ';';
			$message_string .= "\r\n";
			$message_string .= 'Content-Transfer-Encoding: base64';
			$message_string .= "\r\n\r\n";
			$message_string .= $data;
			$message_string .= "\r\n\r\n";
		}
	}

	// Signal end of message
	$message_string .= '--' . $mime_boundary . '--';

	// Send the e-mail.
	return mail($to, $subject, $message_string, $headers_string, $additional_parameters);

}
//*********************************************************

?>