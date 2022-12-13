<?php

class cApiCreateAccount {
		/*
		var $wrongLoginText = 'Login war nicht erfolgreich. Bitte versuche es erneut!';
		var $accountNotActivated = 'Der Account wurde noch nicht aktiviert. Bitte prüfe dein Mail-Postfach.';
		var $accountIsBanned = 'Der Account wurde aus Sicherheitsgründen gesperrt. Wenn du nicht weißt wieso, wende dich bitte an uns.';
		var $appIdErrorText = 'Die App ID ist ungültig';
		var $accessTokenPubKeyErrorText = 'Der Pub Key für das Access Token ist ungültig';
		var $refreshTokenPubKeyErrorText = 'Der Pub Key für das Refresh Token ist ungültig';
		*/
		var $errors = array();
		
		public function process() {
				$iAccount = new cAccount();
				
				//We have no authentication routine yet - return 403 if not authenticated and an error json..
				$email = core()->getPostVar('email');
				$password = core()->getPostVar('password');
				
				//Check mail
				if(strlen($email) < 5 || strpos($email, '@') < 1) {
						$this->errors['mail'] = 'invalid';
				}  else {
						$result = $iAccount->isMailAddressInUse($email);
						
						if(false !== $result) {
								$this->errors['mail'] = 'address_in_use';
						}
				}
				
				//Passwort prüfen.
				if(strlen($password) < 8) {
						$this->errors['password'] = 'invalid';
				}
				
				//Platform prüfen.
				$platforms = cAccount::getRegistrationPlatformTypesFromDb();
				$registration_platform = core()->getPostVar('registration_platform');
				
				if(!in_array($registration_platform, $platforms)) {
						$registration_platform = "";		//This will use the default value in database, which should be "cracked".
				}
				
				//Wenn kein Fehler aufgetreten ist, weiter..
				if(count($this->errors) == 0) {
						$site_id = core()->get('site_id');							//ID of site (url based)
						$siteData = cSite::loadSiteData($site_id);	//Load URL by site id
						
						//Zufälligen Key erzeugen.
						$registration_key = uniqid('', true);
						$url = $siteData['default_protocol'] . ':' . cCMS::loadTemplateUrl(core()->get('site_id')) . 'confirmaccount?tmp=' . $registration_key;
								
						//Account erstellen.
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'INSERT INTO ' . $db->table('accounts') . ' ' .
										'(account_type, email, password, created_on, registration_key, registration_platform) ' .
								'VALUES ' .
										'(:account_type, :email, :password, :created_on, :registration_key, :registration_platform)'
						);
						$db->bind(':account_type', 0);		//0 = deaktiviert, 1 = Admin, 2 = Kunde
						$db->bind(':email', $email);
						$db->bind(':password', password_hash($password, PASSWORD_BCRYPT));
						$db->bind(':created_on', date('Y-m-d H:i:s'));
						$db->bind('registration_key', $registration_key);
						$db->bind('registration_platform', $registration_platform);
						$db->execute();
						$user_id = $db->insertId();
								
						//User ID an URL anhängen.
						$url .= '&user_id=' . (int)$user_id;
						
						//Bestätigungs-Mail versenden.
						$mail_text_plain = file_get_contents('data/mail_templates/registration.txt');
						$mail_text_html= file_get_contents('data/mail_templates/registration.html');
								
						//Make it non utf8 for the mail programs!
						$mail_text_plain = utf8_decode($mail_text_plain);
						$mail_text_html = utf8_decode($mail_text_html);
						
						//URL ersetzen
						$mail_text_plain = str_replace('{$URL}', $url, $mail_text_plain);
						$mail_text_html = str_replace('{$URL}', $url, $mail_text_html);								
						
						//Send Mail to user about account creation
						$mailer = new mvPhpMailer();
						$mailer->init();
						
						$mailer->setFrom('mailfrom@mail...', 'Name');
						$mailer->addAddress($email, $email);
						$mailer->setSubject('Deine Registrierung bei Tellface');
						$mailer->setPlainText($mail_text_plain);
						$mailer->setHTML($mail_text_html);
						$status = $mailer->send();
						
						$data = array(
								'status' => 'success'
						);
						cApiOutput::sendData($statuscode = 1, $data);
						die;
				}

				//When we get here - one ore more errors occured..				
				$data = array(
						'status' => 'error',
						'errors' => $this->errors
				);
				
				cApiOutput::sendData($statuscode = 1, $data);
				die;
		}
}