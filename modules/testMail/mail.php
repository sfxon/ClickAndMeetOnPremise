<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'data/vendor/PHPMailer/src/Exception.php';
require_once 'data/vendor/PHPMailer/src/PHPMailer.php';
require_once 'data/vendor/PHPMailer/src/SMTP.php';

//mv_send_mail();


function mv_send_mail_2() {
		$smtpUsername = '';
		$smtpPassword = '';
		
		$emailFrom = '';
		$emailFromName = '';
		
		$emailTo = '';
		$emailToName = 'Empfänger';
		
		$mail = new PHPMailer;
		//$mail->isSMTP(); 
		$mail->SMTPDebug = 2; // 0 = off (for production use) - 1 = client messages - 2 = client and server messages
		$mail->Host = '';
		$mail->Port = 587; // TLS only
		$mail->SMTPSecure = 'tls'; // ssl is depracated
		$mail->SMTPAuth = true;
		$mail->Username = $smtpUsername;
		$mail->Password = $smtpPassword;
		$mail->setFrom($emailFrom, $emailFromName);
		$mail->addAddress($emailTo, $emailToName);
		$mail->Subject = 'PHPMailer GMail SMTP test 11elf';
		$mail->msgHTML("test body"); //$mail->msgHTML(file_get_contents('contents.html'), __DIR__); //Read an HTML message body from an external file, convert referenced images to embedded,
		$mail->AltBody = 'HTML messaging not supported';
		// $mail->addAttachment('images/phpmailer_mini.png'); //Attach an image file
		
		/*
		echo '<pre>';
		var_dump($mail);
		echo 'done';
		die;
		*/
		
		if(!$mail->send()){
				echo "Mailer Error: " . $mail->ErrorInfo;
		}else{
				echo "Message sent!";
		}
}

