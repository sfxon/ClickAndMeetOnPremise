<?php

//////////////////////////////////////////////////////////////////////////////////
// User Image über die API aktualisieren in der Datenbank.
// Erwartete Parameter:
// image_id -> ID des Bildes!
// age -> Alter größer 17 und kleiner 151
// user_message -> Textnachricht an den Bewerter -> darf auch eine leere Zeichenkette sein.
//////////////////////////////////////////////////////////////////////////////////
class cApiUpdateUserSettings {
		var $errors = array();
		
		//////////////////////////////////////////////////////////////////////////////////
		// Verarbeitung
		//////////////////////////////////////////////////////////////////////////////////
		public function process() {
				$email_address_changed = false;
				$password_changed = false;
				
				//Controller can only be used by logged in users. Check access rights!
				$iApiAccessToken = new cApiAccessToken();
				$user_id = $iApiAccessToken->checkAccessRightsByRequest();
				
				$data_changed = false;
						
				//Original Account-Daten laden, falls wir sie gleich noch benötigen um bspw. Mails an die alte Adresse zu versenden.
				$iAccount = new cAccount();
				$account_data = $iAccount->loadById($user_id);
						
				//Mail Daten ändern.
				$mail = core()->getPostVar('email');
				$current_password = core()->getPostVar('current_password');
				
				if(strlen($mail) > 0) {		
						if($mail !== $account_data['email']) {
								$email_address_changed = $this->processMailChange($user_id, $mail, $current_password);		//Saves the new password in a different field in the database, since we have a security mechanism here. User has to confirm the mail change here..																
						}
				}
				
				//Passwort ändern.
				if($this->errors == array() && count($this->errors) == 0) {		//Nur, wenn bis hierhin kein Fehler aufgetreten ist.
						$new_password = core()->getPostVar('new_password');
								
						if(strlen($new_password) > 0) {
								$password_changed = $this->processPasswordChange($user_id, $account_data['email'], $current_password, $new_password, $new_password);
						}
				}
						
				if(count($this->errors) == 0) {
						//Success. 
						$retval = array(
								'status' => 'success',
								'email_address_changed' => $email_address_changed,
								'password_changed' => $password_changed
						);
						cApiOutput::sendData($statuscode = 1, $retval);
						die;
				}
				
				//If we are here, an error occured.
				//Success. 
				$retval = array(
						'status' => 'error',
						'details' => $this->errors
				);
				cApiOutput::sendData($statuscode = 1, $retval);
				die;
		}
		
		/////////////////////////////////////////////////////////////////////////////
		// Mail Adresse Änderung verarbeiten.
		/////////////////////////////////////////////////////////////////////////////
		public function processMailChange($user_id, $mail, $password) {	
				$iAccount = new cAccount();
				
				//Zeitraum prüfen.
				//Die Mail Adresse kann nur alle 4 Wochen geändert werden (aus Sicherheitsgründen). 
				//Wir halten die Mail-Adress Änderungen auch alle für 12 Wochen vor in einem Log. Dazu ein Feld in der Datenbank einfügen.
				if(false === $iAccount->isLastMailChangeOlderThanWeeks($user_id, $week_count = 4)) {
						//$this->errors['last_mail_change_to_short'] = 'Die E-Mail Adresse kann aus Sicherheitsgründen nur einmal innerhalb von 4 Wochen geändert werden. Bitte wenden Sie sich bei Fragen an unseren Support.';
						return false;
				}
				
				//Check mail account.
				$result = $iAccount->isEmailValid($mail);
				
				if(true !== $result) {
						$this->errors = $result;
						return false;
				}
				
				//Check current password..
				if(false === $iAccount->checkPasswordByUserId($user_id, $password)) {
						$this->errors['wrong_password'] = 'Das eingegebene Passwort ist falsch. Bitte geben Sie hier ihr aktuelles Passwort ein.';
						return false;
				}
				
				//Prüfen, dass die Mail Adresse nicht schon von jemand anderem verwendet wird.
				$result = $iAccount->isMailAddressInUse($mail);
								
				if(true === $result) {
						$this->errors['mail'] = 'Diese E-Mail Adresse ist bereits in Benutzung.';
						return false;
				}
				
				//Originale E-Mail Adresse zwischenspeichern.
				$account = $iAccount->loadById($user_id);
				$iAccount->saveOldMailAdress($user_id, $account['email']);
				
				//Neue E-Mail Adresse speichern.
				$iAccount->updateMailInDb($user_id, $mail);
				$iAccount->update_newMailRequestOn_InDb($user_id, date('Y-m-d H:i:s'));
				
				//Token zum Zurücksetzen generieren und in Datenbank speichern.
				$iToken = new cToken();
				$token = $iToken->generate();
				$iAccount->update_newMailResetKey_InDb($user_id, $token);

				//Mail versenden mit Token an die alte E-Mail Adresse, damit man diese im Zweifelsfall zurücksetzen kann.
				$this->sendMailAdressRecoveryMail($user_id, $account['email'], $mail, $token);
				
				return true;
		}
		
		///////////////////////////////////////////////////////////////////
		// Passwort Änderung verarbeiten.
		///////////////////////////////////////////////////////////////////
		public function processPasswordChange($user_id, $email, $current_password, $new_password, $new_password_repeat) {
				$iAccount = new cAccount();
				
				//no change..
				if($new_password == $current_password) {
						return false;
				}
				
				//If user does not want to change his password..
				if(strlen($new_password) == 0) {
						return false;
				}
				
				//Check current password..
				if(false === $iAccount->checkPasswordByUserId($user_id, $current_password)) {
						$this->errors['wrong_password'] = 'Das eingegebene aktuelle Passwort ist falsch.';
						return false;
				}
				
				//Neues Passwort prüfen..
				if(strlen($new_password) < 8) {
						$this->errors['pass'] = 'Das Passwort muss mindestens 8 Zeichen lang sein.';
						return false;
				}
				
				//Save new password in database.
				$iAccount = new cAccount();
				$iAccount->updatePassword($user_id, $new_password);
				
				//Mail versenden mit reiner Info über die Passwort-Änderung
				$this->sendPasswordChangedInformationMail($email);
				
				return true;
		}
		
		///////////////////////////////////////////////////////////////////
		// Send recovery mail.
		///////////////////////////////////////////////////////////////////
		public function sendMailAdressRecoveryMail($user_id, $original_mail, $new_mail, $token) {
				$site_id = core()->get('site_id');							//ID of site (url based)
				$siteData = cSite::loadSiteData($site_id);	//Load URL by site id
				
				$url = $siteData['default_protocol'] . ':' . cCMS::loadTemplateUrl(core()->get('site_id')) . 'recovermailaccount?tmp1=' . (int)$user_id . '&tmp2=' . $token;
				
				//Bestätigungs-Mail versenden.
				$mail_text_plain = file_get_contents('data/mail_templates/mail_address_recovery.txt');
				$mail_text_html= file_get_contents('data/mail_templates/mail_address_recovery.html');
				
				//Make it non utf8 for the mail programs!
				$mail_text_plain = utf8_decode($mail_text_plain);
				$mail_text_html = utf8_decode($mail_text_html);
				
				//URL ersetzen
				$mail_text_plain = str_replace('{$URL}', $url, $mail_text_plain);
				$mail_text_html = str_replace('{$URL}', $url, $mail_text_html);
				
				$mail_text_plain = str_replace('{$ORIGINAL_MAIL}', $original_mail, $mail_text_plain);
				$mail_text_html = str_replace('{$ORIGINAL_MAIL}', $original_mail, $mail_text_html);
				
				$mail_text_plain = str_replace('{$NEW_MAIL}', $new_mail, $mail_text_plain);
				$mail_text_html = str_replace('{$NEW_MAIL}', $new_mail, $mail_text_html);
				
				//Send Mail to user about account creation
				$mailer = new mvPhpMailer();
				$mailer->init();
				
				$mailer->setFrom('mailfrom@mail...', 'Name');
				$mailer->addAddress($original_mail, $original_mail);
				$mailer->setSubject(utf8_decode('Deine E-Mail Adresse bei Tellface wurde geändert'));
				$mailer->setPlainText($mail_text_plain);
				$mailer->setHTML($mail_text_html);
				$status = $mailer->send();
		}
		
		///////////////////////////////////////////////////////////////////
		// Send password changed mail.
		///////////////////////////////////////////////////////////////////
		public function sendPasswordChangedInformationMail($mail) {				
				$site_id = core()->get('site_id');							//ID of site (url based)
				$siteData = cSite::loadSiteData($site_id);	//Load URL by site id
				
				$url = $siteData['default_protocol'] . ':' . cCMS::loadTemplateUrl(core()->get('site_id')) . 'recover-password';
				
				//Bestätigungs-Mail versenden.
				$mail_text_plain = file_get_contents('data/mail_templates/password_changed_mail.txt');
				$mail_text_html= file_get_contents('data/mail_templates/password_changed_mail.html');
				
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
				$mailer->addAddress($mail, $mail);
				$mailer->setSubject(utf8_decode('Deine Passwort bei Tellface wurde geändert'));
				$mailer->setPlainText($mail_text_plain);
				$mailer->setHTML($mail_text_html);
				$status = $mailer->send();
		}
}

