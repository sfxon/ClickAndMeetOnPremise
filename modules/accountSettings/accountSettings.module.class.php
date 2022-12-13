<?php

class cAccountSettings extends cModule {
		var $template = 'tellface';
		var $cms_id = 0;
		var $contentData = '';
		var $siteUrl = '';
	
		//////////////////////////////////////////////////////////////////////////////////
		// Hook us into the system
		//////////////////////////////////////////////////////////////////////////////////
		public static function setExecutionalHooks() {
				//If the user is logged in..
				if(!isset($_SESSION['ws_user_id'])) {
						header('Location: http://' . cSite::loadSiteUrl(core()->get('site_id')));
						die;
				}
				
				//We use the CMS module for output.
				cCMS::setExecutionalHooks();		
				
				$core = core();
				
				//Now set our own hooks below the CMS hooks.
				core()->setHook('cCore|process', 'process');
				core()->setHook('cRenderer|content', 'content');
				core()->setHook('cRenderer|end_page', 'footer');
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Verarbeitung..
		/////////////////////////////////////////////////////////////////////////////////
		public function process() {
				$errormessage = '';
				$action = core()->getGetVar('action');
				$this->errors = array();
				$success = (int)core()->getGetVar('success');
				
				$this->initData();
				
				if($action == 'update') {
						$data_changed = false;
						
						//Original Account-Daten laden, falls wir sie gleich noch benötigen um bspw. Mails an die alte Adresse zu versenden.
						$iAccount = new cAccount();
						$account_data = $iAccount->loadById($_SESSION['ws_user_id']);
						
						//Mail Daten ändern.
						$mail = core()->getPostVar('mail');
						$current_password = core()->getPostVar('current_password');
						
						if($mail !== $this->data['user_data']['email']) {
								$this->processMailChange($mail, $current_password);		//Saves the new password in a different field in the database, since we have a security mechanism here. User has to confirm the mail change here..																
						}
						
						//Passwort ändern.
						if($this->errors == array() && count($this->errors) == 0) {		//Nur, wenn bis hierhin kein Fehler aufgetreten ist.
								$new_password = core()->getPostVar('new_password');
								$new_password_repeat = core()->getPostVar('new_password_repeat');
								
								if(strlen($new_password) > 0) {
										$this->processPasswordChange($account_data['email'], $current_password, $new_password, $new_password_repeat);
								}
						}
						
						if(count($this->errors) == 0) {
								//Success. Redirect..
								header('Location: ' . cCMS::loadTemplateUrl(core()->get('site_id')) . 'meine-daten?success=6');
								die;
						}
				}
				
				//Calculate if the mail address has been changed recently.
				$mail_account_changed_recently = false;
				$mail_account_changed_recently_date = '';
				$iAccount = new cAccount();
				
				if(false === $iAccount->isLastMailChangeOlderThanWeeks($_SESSION['ws_user_id'], $week_count = 4)) {
						$mail_account_changed_recently = true;
						$mail_account_changed_recently_date = date('d.m.Y', strtotime($this->data['user_data']['new_mail_request_on'])) . ' um ' . date('H:i', strtotime($this->data['user_data']['new_mail_request_on'])) . ' Uhr';
				}
				
				//Output - if user is not doing action!				
				$cms = core()->getInstance('cCMS');
				$content = $cms->loadContentDataByKey('ACCOUNT_SETTINGS');

				//Load the CMS Entry for the login page.
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('TEMPLATE_URL', cCMS::loadTemplateUrl(core()->get('site_id')));
				$renderer->assign('TEMPLATE', $cms->getTemplate());
				$renderer->assign('ERRORMESSAGE', $errormessage);
				$renderer->assign('ERRORS', $this->errors);
				$renderer->assign('SUCCESS', $success);
				$renderer->assign('MAIL_ACCOUNT_CHANGED_RECENTLY', $mail_account_changed_recently);
				$renderer->assign('MAIL_ACCOUNT_CHANGED_RECENTLY_DATE', $mail_account_changed_recently_date);
				$tmp_content = $renderer->fetch('site/account_settings.html');
				
				$content['text'] = $tmp_content;
				$cms->setContentData($content);
		}
		
		/////////////////////////////////////////////////////////////////////////////
		// Init all needed data.
		/////////////////////////////////////////////////////////////////////////////
		public function initData() {
				$iAccount = new cAccount();
				$user_data = $iAccount->loadById((int)$_SESSION['ws_user_id']);
				
				if(false === $user_data) {
						header('Location: http://' . cSite::loadSiteUrl(core()->get('site_id')));
						die;
				}
				
				$this->data = array(
						'user_data' => $user_data
				);
		}
		
		/////////////////////////////////////////////////////////////////////////////
		// Mail Adresse Änderung verarbeiten.
		/////////////////////////////////////////////////////////////////////////////
		public function processMailChange($mail, $password) {	
				$iAccount = new cAccount();
				
				//Zeitraum prüfen.
				//Die Mail Adresse kann nur alle 4 Wochen geändert werden (aus Sicherheitsgründen). 
				//Wir halten die Mail-Adress Änderungen auch alle für 12 Wochen vor in einem Log. Dazu ein Feld in der Datenbank einfügen.
				if(false === $iAccount->isLastMailChangeOlderThanWeeks($_SESSION['ws_user_id'], $week_count = 4)) {
						//$this->errors['last_mail_change_to_short'] = 'Die E-Mail Adresse kann aus Sicherheitsgründen nur einmal innerhalb von 4 Wochen geändert werden. Bitte wenden Sie sich bei Fragen an unseren Support.';
						return;
				}
				
				//Check mail account.
				$result = $iAccount->isEmailValid($mail);
				
				if(true !== $result) {
						$this->errors = $result;
						return;
				}
				
				//Check current password..
				if(false === $iAccount->checkPasswordByUserId($_SESSION['ws_user_id'], $password)) {
						$this->errors['wrong_password'] = 'Das eingegebene Passwort ist falsch. Bitte geben Sie hier ihr aktuelles Passwort ein.';
						return;
				}
				
				//Prüfen, dass die Mail Adresse nicht schon von jemand anderem verwendet wird.
				$result = $iAccount->isMailAddressInUse($mail);
								
				if(true === $result) {
						$this->errors['mail'] = 'Diese E-Mail Adresse ist bereits in Benutzung.';
						return;
				}
				
				//Originale E-Mail Adresse zwischenspeichern.
				$account = $iAccount->loadById($_SESSION['ws_user_id']);
				$iAccount->saveOldMailAdress($_SESSION['ws_user_id'], $account['email']);
				
				//Neue E-Mail Adresse speichern.
				$iAccount->updateMailInDb($_SESSION['ws_user_id'], $mail);
				//$iAccount->updateUsernameInDb($_SESSION['ws_user_id'], $mail);
				$iAccount->update_newMailRequestOn_InDb($_SESSION['ws_user_id'], date('Y-m-d H:i:s'));
				
				//Token zum Zurücksetzen generieren und in Datenbank speichern.
				$iToken = new cToken();
				$token = $iToken->generate();
				$iAccount->update_newMailResetKey_InDb($_SESSION['ws_user_id'], $token);

				//Mail versenden mit Token an die alte E-Mail Adresse, damit man diese im Zweifelsfall zurücksetzen kann.
				$this->sendMailAdressRecoveryMail($account['email'], $mail, $token);
		}
		
		///////////////////////////////////////////////////////////////////
		// Passwort Änderung verarbeiten.
		///////////////////////////////////////////////////////////////////
		public function processPasswordChange($email, $current_password, $new_password, $new_password_repeat) {
				$iAccount = new cAccount();
				
				//no change..
				if($new_password == $current_password) {
						return;
				}
				
				//If user does not want to change his password..
				if(strlen($new_password) == 0) {
						return;
				}
				
				//Check current password..
				if(false === $iAccount->checkPasswordByUserId($_SESSION['ws_user_id'], $current_password)) {
						$this->errors['wrong_password'] = 'Das eingegebene aktuelle Passwort ist falsch.';
						return;
				}
				
				//Neues Passwort prüfen..
				if(strlen($new_password) < 8) {
						$this->errors['pass'] = 'Das Passwort muss mindestens 8 Zeichen lang sein.';
						return;
				}
				
				//Compare new password and new password repeat
				if($new_password != $new_password_repeat) {
						$this->errors['pass_repeat'] = 'Die Passwörter stimmen nicht überein.';
						return;
				}
				
				//Save new password in database.
				$iAccount = new cAccount();
				$iAccount->updatePassword($_SESSION['ws_user_id'], $new_password);
				
				//Mail versenden mit reiner Info über die Passwort-Änderung
				$this->sendPasswordChangedInformationMail($email);
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the page footer.
		///////////////////////////////////////////////////////////////////
		public function footer() {
				$additional_output = 	
						"\n" . '<script src="//' . cSite::loadSiteUrl(core()->get('site_id')) . 'data/templates/' . $this->template . '/js/accountSettings.jquery.js"></script>' .
						"\n";
				$renderer = core()->getInstance('cRenderer');
				$renderer->renderText($additional_output);
		}
		
		///////////////////////////////////////////////////////////////////
		// Send recovery mail.
		///////////////////////////////////////////////////////////////////
		public function sendMailAdressRecoveryMail($original_mail, $new_mail, $token) {
				$url = 'http:' . cCMS::loadTemplateUrl(core()->get('site_id')) . 'recovermailaccount?tmp1=' . (int)$_SESSION['ws_user_id'] . '&tmp2=' . $token;
				
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
				$url = 'http:' . cCMS::loadTemplateUrl(core()->get('site_id')) . 'recover-password';
				
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
				
				$$mailer->setFrom('mailfrom@mail...', 'Name');
				$mailer->addAddress($mail, $mail);
				$mailer->setSubject(utf8_decode('Deine Passwort bei Tellface wurde geändert'));
				$mailer->setPlainText($mail_text_plain);
				$mailer->setHTML($mail_text_html);
				$status = $mailer->send();
		}
}