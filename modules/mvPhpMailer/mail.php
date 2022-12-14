<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'data/vendor/PHPMailer/src/Exception.php';
require_once 'data/vendor/PHPMailer/src/PHPMailer.php';
require_once 'data/vendor/PHPMailer/src/SMTP.php';

function mv_send_mail() {
		$smtpUsername = '';
		$smtpPassword = '';
		
		$emailFrom = '';
		$emailFromName = '';
		
		$emailTo = '';
		$emailToName = '';
		
		$mail = new PHPMailer;
		//$mail->isSMTP(); 
		$mail->SMTPDebug = 2; // 0 = off (for production use) - 1 = client messages - 2 = client and server messages
		$mail->Host = ''; // use $mail->Host = gethostbyname('smtp.gmail.com'); // if your network does not support SMTP over IPv6
		$mail->Port = 587; // TLS only
		$mail->SMTPSecure = 'tls'; // ssl is depracated
		$mail->SMTPAuth = true;
		$mail->Username = $smtpUsername;
		$mail->Password = $smtpPassword;
		$mail->setFrom($emailFrom, $emailFromName);
		$mail->addAddress($emailTo, $emailToName);
		$mail->Subject = 'PHPMailer GMail SMTP test';
		$mail->msgHTML("test body"); //$mail->msgHTML(file_get_contents('contents.html'), __DIR__); //Read an HTML message body from an external file, convert referenced images to embedded,
		$mail->AltBody = 'HTML messaging not supported';
		// $mail->addAttachment('images/phpmailer_mini.png'); //Attach an image file
		
		echo '<pre>';
		var_dump($mail);
		die;
		
		
		if(!$mail->send()){
				echo "Mailer Error: " . $mail->ErrorInfo;
		}else{
				echo "Message sent!";
		}
}
