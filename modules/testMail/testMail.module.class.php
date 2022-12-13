<?php

class cTestMail extends cModule {
		//////////////////////////////////////////////////////////////////////////////////
		// Hook us into the system
		//////////////////////////////////////////////////////////////////////////////////
		public static function setExecutionalHooks() {
				//We use the CMS module for output.
				cCMS::setExecutionalHooks();		
				
				$core = core();
				
				//Now set our own hooks below the CMS hooks.
				//core()->setHook('cCMS|init', 'init');
				core()->setHook('cCore|process', 'process');
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Verarbeitung..
		/////////////////////////////////////////////////////////////////////////////////
		public function process() {
				$iMvPhpMailer = new mvPHPMailer();
				$iMvPhpMailer->init();

				$mailer->setFrom('mailfrom@mail...', 'Name');
				
				$iMvPhpMailer->addAddress('mail', utf8_decode('name'));
				
				
				$iMvPhpMailer->setSubject('testet php mail');
				$iMvPhpMailer->setHTML('Test erfolgreich!'); //$mail->msgHTML(file_get_contents('contents.html'), __DIR__); //Read an HTML message body from an external file, convert referenced images to embedded,
				$iMvPhpMailer->setPlainText('HTML messaging not supported');
				// $mail->addAttachment('images/phpmailer_mini.png'); //Attach an image file
				
				$iMvPhpMailer->send();
		}
}

