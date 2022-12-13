<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
// This is step 2 of the process.
// Go to cRecoverPassword Controller for step 1.
///////////////////////////////////////////////////////////////////////////////////////////////////
class cResetPassword extends cModule {
		var $template = 'maxis';
		var $cms_id = 0;
		var $contentData = '';
		var $siteUrl = '';
		//var $wrongLoginText = 'Login war nicht erfolgreich. Bitte versuche es erneut!';
		//var $accountNotActivated = 'Der Account wurde noch nicht aktiviert. Bitte prüfe dein Mail-Postfach.';
		//var $accountIsBanned = 'Der Account wurde aus Sicherheitsgründen gesperrt. Wenn du nicht weißt wieso, wende dich bitte an uns.';
	
		//////////////////////////////////////////////////////////////////////////////////
		// Hook us into the system
		//////////////////////////////////////////////////////////////////////////////////
		public static function setExecutionalHooks() {
				//If the user is logged in..
				if(isset($_SESSION['ws_user_id'])) {
						header('Location: http://' . cSite::loadSiteUrl(core()->get('site_id')) . 'meine-seite');
						die;
				}
				
				//We use the CMS module for output.
				cCMS::setExecutionalHooks();		
				
				$core = core();
				
				//Now set our own hooks below the CMS hooks.
				//core()->setHook('cCMS|init', 'init');
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

				//Get Parameter abfragen und überprüfen.
				$account_id = (int)core()->getGetVar('tmp');
				$token = core()->getGetVar('tmp2');
				
				//User Account prüfen.
				$iAccount = new cAccount();
				$account_data = $iAccount->loadById((int)$account_id);
				
				if(false === $account_data) {
						$this->showErrorPage();
						return;
				}
				
				//Prüfen, dass der User Account nicht gesperrt ist..
				if($account_data['is_banned'] != 0) {
						$this->unsetResetProcessForAccount((int)$account_id);
						$this->showErrorPage();
						return;
				}
				
				//Länge des Tokens prüfen..
				if(strlen($token) < 5) {
						$this->unsetResetProcessForAccount((int)$account_id);
						$this->showErrorPage();
						return;
				}
				
				//Token prüfen (Vergleich Token in Datenbank mit übermitteltem Token).
				if($account_data['recover_password_token'] != $token) {
						$this->unsetResetProcessForAccount((int)$account_id);
						$this->showErrorPage();
						return;
				}
				
				//Zeitdauer prüfen - wenn der Zeitraum zu lang her ist, brechen wir den Vorgang hier ab. Der Benutzer hat 30 Minuten Zeit, um die Änderung zu bestätigen.
				$recover_password_on = $account_data['recover_password_on'];
						
				//Wenn keine aktive Mail Änderung im System vermerkt ist.
				if($recover_password_on === '0000-00-00 00:00:00' || $recover_password_on === NULL) {
						$this->unsetResetProcessForAccount((int)$account_id);
						$this->showErrorPage();
						return;
				}
						
				//timestamp 30 minutes back in time.
				$history_timestamp = strtotime('-30minutes');
				$current_timestamp = strtotime($recover_password_on);
						
				//Wenn die Anforderung mehr als 30 Minuten zurückliegt.
				if($current_timestamp < $history_timestamp) {
						$this->unsetResetProcessForAccount((int)$account_id);
						$this->showErrorPage();
						return true;
				}
				
				//Wenn Verarbeitung stattfinden soll:
				if($action == 'process') {
						//Eingegebene Daten entgegennehmen (Neues Passwort und die Wiederholung des selben).
						$password = core()->getPostVar('password');
						$password_repeat = core()->getPostVar('password_repeat');
						
						//Daten prüfen wie im Javascript!!
						if(strlen($password) < 8) {
								$this->errors['password_length'] = 'Das Passwort muss mindestens 8 Zeichen lang sein.';
						}
						
						if($password != $password_repeat) {
								$this->errors['password_not_equal'] = 'Die Passwörter stimmen nicht überein.';
						}
						
						//Wenn kein Fehler aufgetreten ist.
						if(count($this->errors) == 0) {
								//Token entfernen, Zeitstempel entfernen..
								$iAccount->updateRecoverPasswordOn((int)$account_id, '0000-00-00 00:00:00');
								$iAccount->updateRecoverPasswordToken((int)$account_id, '');
								
								//Passwort ändern
								$iAccount->updatePassword((int)$account_id, $password);
								
								//Logout all current users, because they might be the intruders..
								$iLongTimeSession = new cLongTimeSession();
								$iLongTimeSession->logoutAllInstancesForUser((int)$account_id);
								
								cSession::destroySessionsByUserId((int)$account_id);
								unset($_SESSION['ws_user_id']);
								unset($_SESSION['user_id']);
								
								//Auf Erfolgsseite mit Login-Formular weiterleiten.
								header('Location: ' . cCMS::loadTemplateUrl(core()->get('site_id')) . 'reset-password-success');
								die;
						}
				}
				
				//Output - if user is not doing action!				
				$cms = core()->getInstance('cCMS');
				$content = $cms->loadContentDataByKey('RESET_PASSWORD');
				
				//Set the site url. We need this for the form to have the right action url!
				$reset_password_form_url = cSeourls::loadSeourlByQueryString('s=cResetPassword');
				$reset_password_form_url = ltrim($reset_password_form_url, '/');
				$reset_password_form_url .= '?action=process';
				$reset_password_form_url = '//' . cSite::loadSiteUrl(core()->get('site_id')) . $reset_password_form_url;
				$reset_password_form_url .= '&amp;tmp=' . (int)$account_id . '&amp;tmp2=' . htmlentities($token);

				//Load the CMS Entry for the login page.
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('TEMPLATE_URL', cCMS::loadTemplateUrl(core()->get('site_id')));
				$renderer->assign('TEMPLATE', $cms->getTemplate());
				$renderer->assign('RESET_PASSWORD_FORM_URL', $reset_password_form_url);
				$renderer->assign('ERRORMESSAGE', $errormessage);
				$renderer->assign('ERRORS', $this->errors);
				
				//Ausgabe
				$tmp_content = $renderer->fetch('site/reset_password.html');
				
				$content['text'] = $tmp_content;
				$cms->setContentData($content);
		}
		
		/////////////////////////////////////////////////////////////////////////////
		// Init all needed data.
		/////////////////////////////////////////////////////////////////////////////
		public function initData() {
				$this->data = array(
						//'mail' => ''//,
						//'pass' => '',
						//'keep_logged_in' => 0
				);
		}
		
		/////////////////////////////////////////////////////////////////////////////
		// Seite mit Fehlermeldung anzeigen..
		/////////////////////////////////////////////////////////////////////////////
		private function showErrorPage() {
				//Output - if user is not doing action!				
				$cms = core()->getInstance('cCMS');
				$content = $cms->loadContentDataByKey('RECOVER_PASSWORD');
				
				//Set the site url. We need this for the form to have the right action url!
				$recover_password_page_url = cSeourls::loadSeourlByQueryString('s=cRecoverPassword');
				$recover_password_page_url = ltrim($recover_password_page_url, '/');
				//$recover_password_form_url .= '?action=process';
				$recover_password_page_url = '//' . cSite::loadSiteUrl(core()->get('site_id')) . $recover_password_page_url;

				//Load the CMS Entry for the login page.
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('TEMPLATE_URL', cCMS::loadTemplateUrl(core()->get('site_id')));
				$renderer->assign('TEMPLATE', $cms->getTemplate());
				$renderer->assign('RECOVER_PASSWORD_PAGE_URL', $recover_password_page_url);
				//$renderer->assign('ERRORMESSAGE', $errormessage);
				//$renderer->assign('ERRORS', $this->errors);
				
				//Ausgabe
				$tmp_content = $renderer->fetch('site/reset_password_error.html');
				
				$content['text'] = $tmp_content;
				$cms->setContentData($content);
				
		}
		
		/////////////////////////////////////////////////////////////////////////////
		// Zurücksetzen des Passwort-Reset Vorganges in der Datenbank.
		/////////////////////////////////////////////////////////////////////////////
		private function unsetResetProcessForAccount($account_id) {
				$iAccount = new cAccount();
				$iAccount->updateRecoverPasswordOn((int)$account_id, '0000-00-00 00:00:00');
				$iAccount->updateRecoverPasswordToken((int)$account_id, '');
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the page footer.
		///////////////////////////////////////////////////////////////////
		public function footer() {
				$additional_output = 	
						"\n" . '<script src="//' . cSite::loadSiteUrl(core()->get('site_id')) . 'data/templates/' . $this->template . '/js/resetPassword.jquery.js"></script>' .
						"\n";
				$renderer = core()->getInstance('cRenderer');
				$renderer->renderText($additional_output);
		}
}
