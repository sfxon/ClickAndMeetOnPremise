<?php

class cAccountDelete extends cModule {
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
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Verarbeitung..
		/////////////////////////////////////////////////////////////////////////////////
		public function process() {
				$errormessage = '';
				$action = core()->getGetVar('action');
				$errors = array();
				$success = (int)core()->getGetVar('success');
				
				$this->initData();
				
				if($action == '') {		//Step 1 - Send email
						$this->delete();
				} else if($action == 'confirm') {		//Step 2 - received email - Show final "password" screen.
						$this->confirm();
				} else if($action == 'final') {			//Step 3 - check password -> delete account if password and token are valid.
						$this->finalDeletion();
				} else if($action == 'step-one-success') {		//Step 1 successful (Mail has been sent)
						$this->stepOneSuccess();
				}
		}
		
		/////////////////////////////////////////////////////////////////////////////
		// Fehler anzeigen..
		/////////////////////////////////////////////////////////////////////////////
		public function showError($errormessage) {
				//Output - if user is not doing action!				
				$cms = core()->getInstance('cCMS');
				$content = $cms->loadContentDataByKey('ACCOUNT_DELETE');
			
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('TEMPLATE_URL', cCMS::loadTemplateUrl(core()->get('site_id')));
				$renderer->assign('TEMPLATE', $cms->getTemplate());
				$renderer->assign('ERRORMESSAGE', $errormessage);
				$tmp_content = $renderer->fetch('site/account_delete_error.html');
				
				$content['text'] = $tmp_content;
				$cms->setContentData($content);
		}
		
		/////////////////////////////////////////////////////////////////////////////
		// Erfolgsmeldung f??r den ersten Schritt anzeigen,
		// mit Handlungsanweisung ??ber die weiteren n??tigen Schritte
		// bis zur vollst??ndigen Account-L??schung.
		/////////////////////////////////////////////////////////////////////////////
		public function stepOneSuccess() {
				//Output - if user is not doing action!				
				$cms = core()->getInstance('cCMS');
				$content = $cms->loadContentDataByKey('ACCOUNT_DELETE');
			
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('TEMPLATE_URL', cCMS::loadTemplateUrl(core()->get('site_id')));
				$renderer->assign('TEMPLATE', $cms->getTemplate());
				$tmp_content = $renderer->fetch('site/account_delete_step_one_success.html');
				
				$content['text'] = $tmp_content;
				$cms->setContentData($content);
		}
		
		/////////////////////////////////////////////////////////////////////////////
		// Erfolgsmeldung: Gesamter Prozess..
		/////////////////////////////////////////////////////////////////////////////
		public function success() {
				//Output - if user is not doing action!				
				$cms = core()->getInstance('cCMS');
				$content = $cms->loadContentDataByKey('ACCOUNT_DELETE');
			
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('TEMPLATE_URL', cCMS::loadTemplateUrl(core()->get('site_id')));
				$renderer->assign('TEMPLATE', $cms->getTemplate());
				$tmp_content = $renderer->fetch('site/account_delete_success.html');
				
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
				
				$this->errors = array();
		}
		
		/////////////////////////////////////////////////////////////////////////////
		// Start delete process..
		/////////////////////////////////////////////////////////////////////////////
		public function delete() {
				$iAccount = new cAccount();
				$account_data = $iAccount->loadById($_SESSION['ws_user_id']);
				
				//Zeitraum pr??fen.
				//Wenn die E-Mail Adresse in den letzten 4 Wochen ge??ndert wurde, erlauben wir das L??schen des Accounts nicht..
				if(false === $iAccount->isLastMailChangeOlderThanWeeks($_SESSION['ws_user_id'], $week_count = 4)) {
						$this->showError('Der Account kann nicht gel??scht werden, weil die E-Mail Adresse innerhalb letzten 4 Wochen ge??ndert wurde. Aus Sicherheitsgr??nden ist diese Funktion deswegen deaktiviert.');
						return;
				}
				
				//Token generieren und in Datenbank speichern.
				$iToken = new cToken();
				$token = $iToken->generate();
				$iAccount->update_deleteAccountToken_InDb($_SESSION['ws_user_id'], $token);
				$iAccount->update_deleteAccountOn_InDb($_SESSION['ws_user_id'], date('Y-m-d H:i:s'));
				
				//Mail versenden mit Token an die alte E-Mail Adresse, damit man diese im Zweifelsfall zur??cksetzen kann.
				$this->sendDeleteAccountMail($account_data['email'], $token);
				header('Location: http://' . cSite::loadSiteUrl(core()->get('site_id')) . 'account-loeschen?action=step-one-success');
				die;
		}
		
		/////////////////////////////////////////////////////////////////////////////
		// Start confirm process..
		/////////////////////////////////////////////////////////////////////////////
		public function confirm() {
				$iAccount = new cAccount();
				
				//Zeitraum der letzten Mail-??nderung pr??fen.
				//Wenn die E-Mail Adresse in den letzten 4 Wochen ge??ndert wurde, steht diese Funktion nicht zur Verf??gung!
				if(false === $iAccount->isLastMailChangeOlderThanWeeks($_SESSION['ws_user_id'], $week_count = 4)) {
						$this->showError('Der Account kann nicht gel??scht werden, weil die E-Mail Adresse innerhalb letzten 4 Wochen ge??ndert wurde. Aus Sicherheitsgr??nden ist diese Funktion deswegen deaktiviert.');
						return;
				}
				
				//Get data.
				$token = core()->getGetVar('tmp2');
				$account_id = (int)$_SESSION['ws_user_id'];
				
				//Load Account data
				$account_data = $iAccount->loadById($account_id);
				
				//Token pr??fen (Vergleich Token in Datenbank mit ??bermitteltem Token)
				if($account_data['delete_account_token'] != $token) {
						$this->unsetDeleteProcessForAccount((int)$account_id);
						$this->showError('Es ist ein Fehler aufgetreten. Bitte versuche es erneut.');
						return;
				}
				
				//Zeitdauer pr??fen - wenn der Zeitraum zu lang her ist, brechen wir den Vorgang hier ab. Der Benutzer hat 30 Minuten Zeit, um die ??nderung zu best??tigen.
				$delete_account_on = $account_data['delete_account_on'];
						
				//Wenn keine aktive Account-L??schung im System vermerkt ist.
				if($delete_account_on === '0000-00-00 00:00:00' || $delete_account_on === NULL) {
						$this->unsetDeleteProcessForAccount((int)$account_id);
						$this->showError('Es ist ein Fehler aufgetreten. Bitte versuche es erneut.');
						return;
				}
				
				//Check timestamp - the link is only valid for 30 minutes..
				$history_timestamp = strtotime('-30minutes');
				$current_timestamp = strtotime($delete_account_on);
						
				//Wenn die Anforderung mehr als 30 Minuten zur??ckliegt.
				if($current_timestamp < $history_timestamp) {
						$this->unsetDeleteProcessForAccount((int)$account_id);
						$this->showError('Die Anfrage ist ??lter als 30 Minuten. Nachdem du eine Anfrage zum L??schen deines Passwortes erstellt hast, hast du nur 30 Minuten Zeit, um die L??schung zu best??tigen. Bitte versuche es erneut.');
						return;
				}
				
				$this->showAccountDeleteConfirmPage($token);
		}
		
		/////////////////////////////////////////////////////////////////////////////
		// Schritt 2: Best??tigungs-Seite anzeigen.
		/////////////////////////////////////////////////////////////////////////////
		public function showAccountDeleteConfirmPage($token) {
				//Set the site url. We need this for the form to have the right action url!
				$account_delete_form_url = cSeourls::loadSeourlByQueryString('s=cAccountDelete');
				$account_delete_form_url = ltrim($account_delete_form_url, '/');
				$account_delete_form_url .= '?action=final';
				$account_delete_form_url = '//' . cSite::loadSiteUrl(core()->get('site_id')) . $account_delete_form_url;
				$account_delete_form_url .= '&amp;tmp2=' . htmlentities($token);
				
				//Output - if user is not doing action!				
				$cms = core()->getInstance('cCMS');
				$content = $cms->loadContentDataByKey('ACCOUNT_DELETE');
			
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('TEMPLATE_URL', cCMS::loadTemplateUrl(core()->get('site_id')));
				$renderer->assign('TEMPLATE', $cms->getTemplate());
				$renderer->assign('ACCOUNT_DELETE_FORM_URL', $account_delete_form_url);
				$renderer->assign('ERRORS', $this->errors);
				$tmp_content = $renderer->fetch('site/account_delete_confirm.html');
				
				$content['text'] = $tmp_content;
				$cms->setContentData($content);
		}
		
		/////////////////////////////////////////////////////////////////////////////
		// Finale L??sch-Funktion.
		// Wenn hier alle Daten korrekt sind, wird der Account
		// und alle dazu geh??rigen Bilder gel??scht!
		/////////////////////////////////////////////////////////////////////////////
		public function finalDeletion() {
				$iAccount = new cAccount();
				
				//Zeitraum der letzten Mail-??nderung pr??fen.
				//Wenn die E-Mail Adresse in den letzten 4 Wochen ge??ndert wurde, steht diese Funktion nicht zur Verf??gung!
				if(false === $iAccount->isLastMailChangeOlderThanWeeks($_SESSION['ws_user_id'], $week_count = 4)) {
						$this->showError('Der Account kann nicht gel??scht werden, weil die E-Mail Adresse innerhalb letzten 4 Wochen ge??ndert wurde. Aus Sicherheitsgr??nden ist diese Funktion deswegen deaktiviert.');
						return;
				}
				
				//Get data.
				$token = core()->getGetVar('tmp2');
				$account_id = (int)$_SESSION['ws_user_id'];
				
				//Load Account data
				$account_data = $iAccount->loadById($account_id);
				
				//Token pr??fen (Vergleich Token in Datenbank mit ??bermitteltem Token)
				if($account_data['delete_account_token'] != $token) {
						$this->unsetDeleteProcessForAccount((int)$account_id);
						$this->showError('Es ist ein Fehler aufgetreten. Bitte versuche es erneut.');
						return;
				}
				
				//Zeitdauer pr??fen - wenn der Zeitraum zu lang her ist, brechen wir den Vorgang hier ab. Der Benutzer hat 30 Minuten Zeit, um die ??nderung zu best??tigen.
				$delete_account_on = $account_data['delete_account_on'];
						
				//Wenn keine aktive Account-L??scung im System vermerkt ist.
				if($delete_account_on === '0000-00-00 00:00:00' || $delete_account_on === NULL) {
						$this->unsetDeleteProcessForAccount((int)$account_id);
						$this->showError('Es ist ein Fehler aufgetreten. Bitte versuche es erneut.');
						return;
				}
				
				//Check timestamp - the link is only valid for 30 minutes..
				$history_timestamp = strtotime('-30minutes');
				$current_timestamp = strtotime($delete_account_on);
						
				//Wenn die Anforderung mehr als 30 Minuten zur??ckliegt.
				if($current_timestamp < $history_timestamp) {
						$this->unsetDeleteProcessForAccount((int)$account_id);
						$this->showError('Die Anfrage ist ??lter als 30 Minuten. Nachdem du eine Anfrage zum L??schen deines Passwortes erstellt hast, hast du nur 30 Minuten Zeit, um die L??schung zu best??tigen. Bitte versuche es erneut.');
						return;
				}
				
				//??berpr??fe das eingegebene Passwort
				$password = core()->getPostVar('password');
				
				if(false === $iAccount->checkPasswordByUserId($_SESSION['ws_user_id'], $password)) {
						$this->errors['wrong_password'] = 'Das eingegebene aktuelle Passwort ist falsch.';
						$this->showAccountDeleteConfirmPage($token);
						return;
				}
				
				//Hier stimmen alle Daten - Alle Bilder des Accounts l??schen - User-Account l??schen.
				$this->deleteAllImagesForAccount((int)$_SESSION['ws_user_id']);
				
				//User Account l??schen.
				$iAccount->deleteById((int)$_SESSION['ws_user_id']);
				
				//Mail an E-Mail Adresse senden, dass der Account gel??scht wurde..
				$this->sendAccountDeletedMail($account_data['email']);
				
				//Long-Time Sessions f??r diesen User aufl??sen..
				$iLongTimeSession = new cLongTimeSession();
				$iLongTimeSession->logoutAllInstancesForUser((int)$account_id);
				
				cSession::destroySessionsByUserId((int)$account_id);
				unset($_SESSION['ws_user_id']);
				unset($_SESSION['user_id']);
				
				//User ausloggen (Cookie l??schen)
				//Destroy the cookie, if it is set..
				if(isset($_COOKIE['tellface_longtime'])) {
						unset($_COOKIE['tellface_longtime']); 
    				setcookie('tellface_longtime', null, -1, '/');
				}
				
				//Destroy the session: The officially implemented handler keeps track on also deleting everything in $_SESSION..
				//Be careful - if you ever change it!
				session_destroy();
				
				header('Location: http://' . cSite::loadSiteUrl(core()->get('site_id')) . 'account-geloescht?tmp=' . urlencode($token));
				die;
		}
		
		/////////////////////////////////////////////////////////////////////////////
		// L??scht alle Bilder f??r einen bestimmten User-Account.
		/////////////////////////////////////////////////////////////////////////////
		public function deleteAllImagesForAccount($account_id) {
				//Alle Bilder dieses Benutzers laden.
				$iUserImages = new cUserImages();
				$images = $iUserImages->loadAllImagesByUser((int)$account_id);
				
				foreach($images as $image) {
						//Bild l??schen (auf Festplatte).
						$filename = $image['filename'];
						unlink($filename);
						
						//Bild in Datenbank l??schen.
						$iUserImages->deleteImage($image['id'], $_SESSION['ws_user_id']);
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Send Delete Account Mail.
		///////////////////////////////////////////////////////////////////
		public function sendDeleteAccountMail($original_mail, $token) {
				$url = 'http:' . cCMS::loadTemplateUrl(core()->get('site_id')) . 'account-loeschen?action=confirm&tmp2=' . $token;
				
				//Best??tigungs-Mail versenden.
				$mail_text_plain = file_get_contents('data/mail_templates/delete-account-confirm.txt');
				$mail_text_html= file_get_contents('data/mail_templates/delete-account-confirm.html');
				
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
				$mailer->addAddress($original_mail, $original_mail);
				$mailer->setSubject(utf8_decode('Best??tige die L??schung deines Accounts!'));
				$mailer->setPlainText($mail_text_plain);
				$mailer->setHTML($mail_text_html);
				$status = $mailer->send();
		}
		
		///////////////////////////////////////////////////////////////////
		// Send "Account deleted mail"
		///////////////////////////////////////////////////////////////////
		public function sendAccountDeletedMail($original_mail) {
				$url = 'http:' . cCMS::loadTemplateUrl(core()->get('site_id'));
				
				//Best??tigungs-Mail versenden.
				$mail_text_plain = file_get_contents('data/mail_templates/account-deleted-mail.txt');
				$mail_text_html= file_get_contents('data/mail_templates/account-deleted-mail.html');
				
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
				$mailer->addAddress($original_mail, $original_mail);
				$mailer->setSubject(utf8_decode('Dein Tellface Account wurde gel??scht'));
				$mailer->setPlainText($mail_text_plain);
				$mailer->setHTML($mail_text_html);
				$status = $mailer->send();
		}
		
		/////////////////////////////////////////////////////////////////////////////
		// Zur??cksetzen des Account-L??schen Vorganges in der Datenbank.
		/////////////////////////////////////////////////////////////////////////////
		private function unsetDeleteProcessForAccount($account_id) {
				$iAccount = new cAccount();
				$iAccount->update_deleteAccountOn_InDb((int)$account_id, '0000-00-00 00:00:00');
				$iAccount->update_deleteAccountToken_InDb((int)$account_id, '');
		}
}