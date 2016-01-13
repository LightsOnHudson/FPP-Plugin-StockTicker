<?php

//send response function
function sendResponse($from,$REPLY_TEXT,$GMAIL_ADDRESS,$subject, $mbox) {
	global $DEBUG, $gv, $EMAIL, $RESPONSE_METHOD;

	logEntry("Sending response using: ".$RESPONSE_METHOD);

	switch ($RESPONSE_METHOD) {

		case "SMS":
			$gv->sendSMS($from,$REPLY_TEXT);
				
			break;
				
		case "EMAIL":
			sendMail($GMAIL_ADDRESS, $EMAIL, $subject, $REPLY_TEXT, $mbox);
			break;
	}



}
//sendmail using phpmailer function
function sendMail($to, $from, $subject, $body, $mbox) {
	
	global $DEBUG, $EMAIL, $PASSWORD, $USERNAME,$hostname, $MAIL_HOST, $MAIL_PORT;

	date_default_timezone_set('Etc/UTC');


	//Create a new PHPMailer instance
	$mail = new PHPMailer();

	//Tell PHPMailer to use SMTP
	$mail->isSMTP();
	//$mail->isSendmail();
	//$mail->isMail();
	//$mail->

	//Enable SMTP debugging
	// 0 = off (for production use)
	// 1 = client messages
	// 2 = client and server messages
	if($DEBUG) {
		$mail->SMTPDebug = 2;
	} else {

		$mail->SMTPDebug = 0;
	}

	if($DEBUG)
	logEntry(extension_loaded('openssl')?'SSL loaded':'SSL not loaded');
	
	switch (trim(strtoupper($MAIL_HOST))) {
	
	
		case "IMAP.GMAIL.COM":
			//Set the hostname of the mail server
		$mail->Host = gethostbyname('smtp.gmail.com');
		// use
	
		//Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
		$mail->Port = 587;
		//Username to use for SMTP authentication - use full email address for gmail
		$mail->Username = $EMAIL;
		$mail->SMTPSecure = 'tls';

	
				break;
	
	
		default:
			
			
			
			///TEST HOST
		//	$MAIL_HOST = "mail.incanberra.biz";
		$mail->Host = gethostbyname($MAIL_HOST);
		
		//Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
		//$MAIL_PORT = 587;
		$mail->Port = $MAIL_PORT;
		
			$USERNAME = substr($EMAIL,0,strpos($EMAIL,"@"));
			if($DEBUG)
				logEntry("Username extracted from email: ".$USERNAME);
			
		$mail->Username = $USERNAME."@".$MAIL_HOST;
		//$mail->Username = $EMAIL;
		
		//$mail->Username = "bshaver@mail.incanberra.biz";
		
		//$mail->SMTPSecure = 'tls';
		$mail->SMTPSecure = 'ssl';
		// use
		
		//logEntry("trying to pop3 auth");
		//$pop = POP3::popBeforeSmtp($MAIL_HOST, 110, 30, $USERNAME, $PASSWORD, 1);
		
	
		//print_r($pop);
		break;
	
	}
	//Ask for HTML-friendly debug output
	
		

		//Whether to use SMTP authentication
		$mail->SMTPAuth = true;

		

		//Password to use for SMTP authentication
		$mail->Password = $PASSWORD;

		//Set who the message is to be sent from
		$mail->setFrom($EMAIL, 'Holiday');

		//Set an alternative reply-to address
		$mail->addReplyTo($EMAIL, 'Holiday');

		//Set who the message is to be sent to
		$mail->addAddress($to, $from);

		//Set the subject line
		$mail->Subject = $subject;

		//Read an HTML message body from an external file, convert referenced images to embedded,
		//convert HTML into a basic plain-text alternative body
		$mail->msgHTML($body);

		//Replace the plain text body with one created manually
		$mail->AltBody = $body;
		
	//	$mail->SMTPAutoTLS;
		if($DEBUG) {
			$mail->Debugoutput = 'html';
			
				
				
				
			logEntry("SMTP host: ".$mail->Host);
			logEntry("SMTP port: ".$mail->Port);
			logEntry("SMTP send username: ".$mail->Username);
			logEntry("SMTP send password: ".$mail->Password);
			//logEntry("SMTP send setFrom: ");
			logEntry("SMTP send smtpAuth: ".$mail->SMTPAuth);
			logEntry("SMTP send smtpSEcure: ".$mail->SMTPSecure);
			logEntry("SMTP send subject: ".$mail->Subject);
			//logEntry("SMTP ato tls: ".$mail->SMTPAutoTLS);
		
		}

		//Attach an image file
		//$mail->addAttachment('images/phpmailer_mini.png');

		//send the message, check for errors
	
	
		
		sleep(1);
	//	if (!$mail->send()) {
	//$mail->preSend();
	
		//if(!$mail->postSend()) {//->mail()) {
		//	logEntry( "Mailer Error: " . $mail->ErrorInfo);
		//} else {
	//		logEntry( "Message sent!");
	//	}

	//	$to = "runnable.tests@gmail.com";
	//	$subject = "Test Email";
	//	$body = "This is only a test.";
		$headers = "From: ".$from."\r\n".
				"Reply-To: ".$from."\r\n";
		$cc = null;
		$bcc = null;
		$return_path = $from;
		//send the email using IMAP
		$subject  = "NOREPLY: Automatic response";
		
		logEntry("To: ".$to);
		logEntry("From: ".$from);
		logEntry("Subject: ".$subject);
		logEntry("Body: ".$body);
		//$a = imap_mail($to, $subject, $body, $headers, $cc, $bcc, $return_path);
		logEntry("imap response: ".$a);
		
		if($DEBUG)
			print_r($a);
		
			$mboxSend = imap_open ("{mail.incanberra.biz:993/ssl/novalidate-cert}INBOX","bshaver", "Bshaver12345") or die('connection failed '.imap_last_error());
			
			//$to='find_job@bk.ru';
		//	$siteemail='admin@maydomain.com';
			$siteemail = $EMAIL;
			$subject = "This is subject";
			$headers .= "From: $siteemail<$siteemail>\n";
			$headers .= "X-Sender: <$siteemail>\n";
			$headers .= "X-Mailer: PHP\n";
			$headers .= "X-Priority: 1\n";
			$headers .= "Return-Path: <$siteemail>\n";
			
			$message = "This is the text.";
			
			imap_mail ($to, $subject, $message , $headers) or die('send failed '.imap_last_error());
			
			imap_close($mboxSend);
		
		
}

//fork a non blocking fppd process

function forkExec($cmd) {
	global $DEBUG;

	
	//$safe_arg["arg_2"] = escapeshellarg($arg_2);
	$pid = pcntl_fork();

	if ( $pid == -1 ) {
		// Fork failed
		if($DEBUG)
			logEntry("fork failed");

			exit(1);
	} else if ( $pid ) {
		// We are the parent
		if($DEBUG) {
			logEntry("------------");
			logEntry("fork parent");
			logEntry("------------");
		}
		return "Parent";

		// Can no longer use $db because it will be closed by the child
		// Instead, make a new MySQL connection for ourselves to work with
	} else {
		if($DEBUG){
			logEntry("------------");
			logEntry("fork child");
			logEntry("------------");
		}
		//logEntry("sleeping 5 seconds, processing, thensleeping agin");

		exec($cmd);
		
		return "Child";
	}
}
//fork a non blocking fppd process

function fork($argv) {
	global $DEBUG;

	$safe_arg = escapeshellarg($argv[4]);
	//$safe_arg["arg_2"] = escapeshellarg($arg_2);
	$pid = pcntl_fork();

	if ( $pid == -1 ) {
		// Fork failed
		if($DEBUG)
			logEntry("fork failed");

			exit(1);
	} else if ( $pid ) {
		// We are the parent
		if($DEBUG) {
			logEntry("------------");
			logEntry("fork parent");
			logEntry("------------");
		}
		return "Parent";

		// Can no longer use $db because it will be closed by the child
		// Instead, make a new MySQL connection for ourselves to work with
	} else {
		if($DEBUG){
			logEntry("------------");
			logEntry("fork child");
			logEntry("------------");
		}
		//logEntry("sleeping 5 seconds, processing, thensleeping agin");

		processCallback($argv);
		return "Child";
	}
}
//get the string between two characters
function get_string_between ($str,$from,$to) {

	$string                                         = substr($str,strpos($str,$from)+strlen($from));

	if (strstr ($string,$to,TRUE) != FALSE) {

		$string                                     =   strstr ($string,$to,TRUE);

	}

	return $string;

}
//update plugin

function updatePluginFromGitHub($gitURL, $branch="master", $pluginName) {
	
	
	global $settings;
	logEntry ("updating plugin: ".$pluginName);
	
	logEntry("settings: ".$settings['pluginDirectory']);
	
	//create update script
	//$gitUpdateCMD = "sudo cd ".$settings['pluginDirectory']."/".$pluginName."/; sudo /usr/bin/git git pull ".$gitURL." ".$branch;

	$pluginUpdateCMD = "/opt/fpp/scripts/update_plugin ".$pluginName;

	logEntry("update command: ".$pluginUpdateCMD);


	exec($pluginUpdateCMD, $updateResult);

	//logEntry("update result: ".print_r($updateResult));

	//loop through result	
	return;// ($updateResult);
	
	
	
}
//create script to randmomize
function createScriptFile($scriptFilename,$scriptCMD) {


	global $scriptDirectory,$pluginName;

	$scriptFilename = $scriptDirectory."/".$scriptFilename;

	logEntry("Creating  script: ".$scriptFilename);
	
	$ext = pathinfo($scriptFilename, PATHINFO_EXTENSION);

	
	$data = "";

	$data .="#!/bin/sh\n";

	
	$data .= "\n";
	$data .= "#Script to run randomizer\n";
	$data .= "#Created by ".$pluginName."\n";
	$data .= "#\n";
	$data .= "/usr/bin/php ".$scriptCMD."\n";
	
	logEntry($data);


	$fs = fopen($scriptFilename,"w");
	fputs($fs, $data);
	fclose($fs);

}
//return the next event file available for use

//get the next available event filename
function getNextEventFilename() {

	$MAX_MAJOR_DIGITS=2;
	$MAX_MINOR_DIGITS=2;
	global $eventDirectory;

	//echo "Event Directory: ".$eventDirectory."<br/> \n";

	$MAJOR=array();
	$MINOR=array();

	$MAJOR_INDEX=0;
	$MINOR_INDEX=0;

	$EVENT_FILES = directoryToArray($eventDirectory, false);
	//print_r($EVENT_FILES);

	foreach ($EVENT_FILES as $eventFile) {

		$eventFileParts = explode("_",$eventFile);

		$MAJOR[] = (int)basename($eventFileParts[0]);
		//$MAJOR = $eventFileParts[0];

		$minorTmp = explode(".fevt",$eventFileParts[1]);

		$MINOR[] = (int)$minorTmp[0];

		//echo "MAJOR: ".$MAJOR." MINOR: ".$MINOR."\n";
		//print_r($MAJOR);
		//print_r($MINOR);

	}

	$MAJOR_INDEX = max(array_values($MAJOR));
	$MINOR_INDEX = max(array_values($MINOR));

	//echo "Major max: ".$MAJOR_INDEX." MINOR MAX: ".$MINOR_INDEX."\n";



	if($MAJOR_INDEX <= 0) {
		$MAJOR_INDEX=1;
	}
	if($MINOR_INDEX <= 0) {
		$MINOR_INDEX=1;

	} else {

		$MINOR_INDEX++;
	}

	$MAJOR_INDEX = str_pad($MAJOR_INDEX, $MAX_MAJOR_DIGITS, '0', STR_PAD_LEFT);
	$MINOR_INDEX = str_pad($MINOR_INDEX, $MAX_MINOR_DIGITS, '0', STR_PAD_LEFT);
	//for now just return the next MINOR index up and keep the same Major
	$newIndex=$MAJOR_INDEX."_".$MINOR_INDEX.".fevt";
	//echo "new index: ".$newIndex."\n";
	return $newIndex;
}


function directoryToArray($directory, $recursive) {
	$array_items = array();
	if ($handle = opendir($directory)) {
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != "..") {
				if (is_dir($directory. "/" . $file)) {
					if($recursive) {
						$array_items = array_merge($array_items, directoryToArray($directory. "/" . $file, $recursive));
					}
					$file = $directory . "/" . $file;
					$array_items[] = preg_replace("/\/\//si", "/", $file);
				} else {
					$file = $directory . "/" . $file;
					$array_items[] = preg_replace("/\/\//si", "/", $file);
				}
			}
		}
		closedir($handle);
	}
	return $array_items;
}


//check all the event files for a string matching this and return true/false if exist
function checkEventFilesForKey($keyCheckString) {
	global $eventDirectory;

	$keyExist = false;
	$eventFiles = array();

	$eventFiles = directoryToArray($eventDirectory, false);
	foreach ($eventFiles as $eventFile) {

		if( strpos(file_get_contents($eventFile),$keyCheckString) !== false) {
			// do stuff
			$keyExist= true;
			break;
			// return $keyExist;
		}
	}

	return $keyExist;

}
?>
