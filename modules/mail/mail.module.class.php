<?php

class cMail extends cModule {
		var $data = array();
		var $mail_from = '';
		var $mail_subject = '';
		var $mail_cms_key = '';
		var $mail_reply_to = '';
		var $mail_to = '';
		var $mail_text = '';
		var $mail_additional_header = '';
		var $mail_cc = array();
		var $attachments = array();
		
		/////////////////////////////////////////////////////////////////////////////////////////
		// Add additional header.
		/////////////////////////////////////////////////////////////////////////////////////////
		public function addAdditionalHeader($text) {
				$this->mail_additional_header = $text . "\r\n";
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////
		// Set the mail to.
		/////////////////////////////////////////////////////////////////////////////////////////
		public function setMailText($mail_text) {
				$this->mail_text = $mail_text;
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////
		// Set the mail to.
		/////////////////////////////////////////////////////////////////////////////////////////
		public function setMailTo($mail_to) {
				$this->mail_to = $mail_to;
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////
		// Set the mail CC receivers
		/////////////////////////////////////////////////////////////////////////////////////////
		public function setMailCC($mail_cc) {
				$this->mail_cc[] = $mail_cc;
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////
		// Set the mail CC receivers
		/////////////////////////////////////////////////////////////////////////////////////////
		public function addAttachment($filename, $data) {
				$this->attachments[$filename] = $data;
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////
		// Set the mail reply to field.
		/////////////////////////////////////////////////////////////////////////////////////////
		public function setMailReplyTo($reply_to) {
				$this->mail_reply_to = $reply_to;
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////
		// Set the mail from field.
		/////////////////////////////////////////////////////////////////////////////////////////
		public function setMailFrom($mail_from) {
				$this->mail_from = $mail_from;
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////
		// Set some data for template parsing.
		/////////////////////////////////////////////////////////////////////////////////////////
		public function addData($key, $data) {
				$this->data[$key] = $data;
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////
		// Set the subject. (Overrides the heading subject).
		/////////////////////////////////////////////////////////////////////////////////////////
		public function setSubject($subject) {
				$this->mail_subject = $subject;
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////
		// Set the cms key. By the cms key, it loads the content that is to be set as email text.
		/////////////////////////////////////////////////////////////////////////////////////////
		public function setCmsKey($cms_key) {
				$this->mail_cms_key = $cms_key;
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////
		// Render E-Mail content.
		/////////////////////////////////////////////////////////////////////////////////////////
		public function renderContent() {
				$content_data = cCMS::loadContentDataByCmsKeyStatic($this->mail_cms_key);
				
				//Render content
				$renderer = core()->getInstance('cRenderer');
				$renderer->assign('DATA', $this->data);
				$this->mail_text = $renderer->fetchFromString($content_data['text']);
				
				//Render subject
				$this->mail_subject = $renderer->fetchFromString($this->mail_subject);
				
				//Decode. This is, because mail in utf8 is shown wrong in some clients.
				$this->mail_text = $this->mail_text;
				$this->mail_subject = utf8_decode($this->mail_subject);
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////
		// Send the contact email.
		/////////////////////////////////////////////////////////////////////////////////////////
		public function send() {			
				if(strlen($this->mail_cms_key) > 0) {
						$this->renderContent();
				}
				
				//Build the headers..
				$mail_header = 'From: ' . $this->mail_from . "\r\n";
				
				//Set mail CC receivers..
				if(count($this->mail_cc) > 0) {
						$mail_header .= 'CC: ' . implode(', ', $this->mail_cc) . "\r\n";
				}
				
				//Set mail from..
				if($this->mail_from != '') {		//Ad reply to address, if the address is different from our setup address..
						$mail_header .= 'Reply-To: ' . $this->mail_from . "\r\n";
				}
				
				$mail_header .= 'X-Mailer: PHP/' . phpversion() . "\r\n";
				
				//mime boundary 
				$semi_rand = md5(time()); 
				$mime_boundary = "==Multipart_Boundary_x{$semi_rand}x"; 
				
				//headers for attachment 
				$mime_headers = "MIME-Version: 1.0\r\n" . "Content-Type: multipart/mixed;\r\n" . " boundary=\"{" . $mime_boundary . "}\""; 
				
				$mail_header .= $mime_headers;
				
				//Nachricht zusammensetzen. Zuerst der Inhalt:
				$message = '';
				
				$message .= 
						"--{" . $mime_boundary . "}\r\n" . "Content-Type: text/html; charset=\"UTF-8\"\r\n" .
						"Content-Transfer-Encoding: 7bit\r\n\r\n" . $this->mail_text . "\r\n\r\n"; 
				
				//Nachricht zusammensetzen: Anhänge beifügen:
				foreach($this->attachments as $filename => $attachment) {
						$message .= "--{" . $mime_boundary . "}\r\n";
						
						$data = chunk_split(base64_encode($attachment));
						$message .= 
								"Content-Type: application/octet-stream; name=\"".basename($filename)."\"\r\n" . 
								"Content-Description: ".basename($filename)."\r\n" .
								"Content-Disposition: attachment;\r\n" . " filename=\"".basename($filename)."\"; size=".strlen($attachment).";\r\n" . 
								"Content-Transfer-Encoding: base64\r\n\r\n" . $data . "\r\n\r\n";
				}
				
				$message .= "--{" . $mime_boundary . "}--";
				
				//var_dump($message);
				//die;
				
				//$mail_header .= "\r\n" . 'MIME-Version: 1.0';
				//$mail_header .= "\r\n" . 'Content-type: text/html; charset=iso-8859-1';
				
				if($this->mail_additional_header != '') {
						$mail_header .= "\r\n";
						$mail_header .= $this->mail_additional_header;
				}
				
				//Send the email
				return mail($this->mail_to, mb_encode_mimeheader($this->mail_subject), $message, $mail_header);
		}
		
		///////////////////////////////////////////////////////////////////
		// validate the email address.
		// pregmatch as of http://stackoverflow.com/questions/201323/using-a-regular-expression-to-validate-an-email-address
		//
		// Context:
		//	A valid e-mail address is a string that matches the ABNF production […].
		//	Note: This requirement is a willful violation of RFC 5322, which defines a syntax for e-mail addresses that is simultaneously too strict (before the "@" character), too vague (after the "@" character), and too lax (allowing comments, whitespace characters, and quoted strings in manners unfamiliar to most users) to be of practical use here.
		//	The following JavaScript- and Perl-compatible regular expression is an implementation of the above definition.
		//
		// TODO:
		//	There is a copy of this function in cAdminaccounts.
		//	All calls of the other function should be changed to be done by this module (cMail).
		//	Then remove the function in the other module.
		///////////////////////////////////////////////////////////////////
		public static function validateEmailAddress($email_address) {
				$output_array = array();
				preg_match("/^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/", $email_address, $output_array);
				
				if(count($output_array) > 0) {
						return true;
				}
				
				return false;
		}
}

?>