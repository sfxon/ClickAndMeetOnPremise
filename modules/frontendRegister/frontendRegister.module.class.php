<?php

class cFrontendRegister extends cModule {
		var $template = 'maxis';
		var $cms_id = 0;
		var $contentData = '';
		var $siteUrl = '';
	
		//////////////////////////////////////////////////////////////////////////////////
		// Hook us into the system
		//////////////////////////////////////////////////////////////////////////////////
		public static function setExecutionalHooks() {
				//If the user is logged in..
				if(isset($_SESSION['ws_user_id'])) {
						header('Location: http://' . cSite::loadSiteUrl(core()->get('site_id')) . 'meine-seite');
						die;
				}
				
				//Disable frontend login..
				header('Location: http://' . cSite::loadSiteUrl(core()->get('site_id')) . 'admin');
				die;
				
				//We use the CMS module for output.
				cCMS::setExecutionalHooks();		
				
				$core = core();
				
				//Now set our own hooks below the CMS hooks.
				//core()->setHook('cCMS|init', 'init');
				core()->setHook('cCore|process', 'process');
				core()->setHook('cRenderer|content', 'content');
				core()->setHook('cRenderer|end_page', 'footer');
				core()->setHook('cRenderer|begin_page', 'beginPage');
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Verarbeitung..
		/////////////////////////////////////////////////////////////////////////////////
		public function process() {
				$errormessage = '';
				$action = core()->getGetVar('action');
				$errors = array();
				$this->initData();
				
				$iAccount = new cAccount();
				
				//Processing input
				if($action == 'process') {
						$mail = core()->getPostVar('mail');
						$mail_repeat = core()->getPostVar('mail_repeat');
						$pass = core()->getPostVar('pass');
						$accept_agb = core()->getPostVar('accept_agb');
						
						$this->data = array(
								'mail' => $mail,
								'mail_repeat' => $mail_repeat,
								'pass' => $pass,
								'accept_agb' => (int)$accept_agb
						);
						
						//Check mail
						if(strlen($mail) < 5 || strpos($mail, '@') < 1 /*|| strpos($mail, '.') < 3*/) {
								$errors['mail'] = 'Bitte gib eine gültige E-Mail Adresse ein.<br /><br />';
						}  else {
								$result = $iAccount->isMailAddressInUse($mail);
								
								if(false !== $result) {
										$errors['mail'] = 'Diese E-Mail Adresse ist bereits vergeben. Falls du dein Passwort vergessen hast, kannst du es hier zurücksetzen: <a href="' . cCMS::loadTemplateUrl(core()->get('site_id')) . 'recover-password">Passwort zurücksetzen</a><br /><br />';
								}
						}
						
						//Check mail repeat
						if($mail != $mail_repeat) {
								$errors['mail_repeat'] = 'Die E-Mail Adressen stimmen nicht überein.<br /><br />';
						}
						
						//Check pass
						if(strlen($pass) < 8) {
								$errors['pass'] = 'Das Passwort muss mindestens 8 Zeichen lang sein.<br /><br />';
						}
						
						//Check accept_agb
						if(1 !== (int)$accept_agb) {
								$errors['accept_agb'] = 'Wir können den Account für dich nur erstellen, wenn du mit unseren Nutzungsbedingungen einverstanden bist.<br /><br />';
						}
						
						//Wenn kein Fehler aufgetreten ist, weiter..
						if(count($errors) == 0) {
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
												'(:account_type, :email, :password, :created_on, :registration_key, \'web\')'
								);
								$db->bind(':account_type', 0);		//0 = deaktiviert, 1 = Admin, 2 = Kunde
								//$db->bind(':username', $mail);
								$db->bind(':email', $mail);
								$db->bind(':password', password_hash($pass, PASSWORD_BCRYPT));
								$db->bind(':created_on', date('Y-m-d H:i:s'));
								$db->bind('registration_key', $registration_key);
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
								$mailer->addAddress($mail, $mail);
								$mailer->setSubject('Deine Registrierung bei Tellface');
								$mailer->setPlainText($mail_text_plain);
								$mailer->setHTML($mail_text_html);
								$status = $mailer->send();
								
								header('Location: ' . cCMS::loadTemplateUrl(core()->get('site_id')) . 'benutzer-registriert');
								die;
						}
						
				}
				
				//Set the site url. We need this for the form to have the right action url!
				$register_form_url = cSeourls::loadSeourlByQueryString('s=cFrontendRegister');
				$register_form_url = ltrim($register_form_url, '/');
				$register_form_url .= '?action=process';
				$register_form_url = '//' . cSite::loadSiteUrl(core()->get('site_id')) . $register_form_url;
				
				//Load the CMS Entry for the login page.
				$cms = core()->getInstance('cCMS');
				$content = $cms->loadContentDataByKey('REGISTRATION');
				
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('TEMPLATE_URL', cCMS::loadTemplateUrl(core()->get('site_id')));
				$renderer->assign('TEMPLATE', $cms->getTemplate());
				$renderer->assign('REGISTER_FORM_URL', $register_form_url);
				//$renderer->assign('SESSION_KEY', $session_key);
				$renderer->assign('ERRORMESSAGE', $errormessage);
				$renderer->assign('ERRORS', $errors);
				$tmp_content = $renderer->fetch('site/register.html');
				
				
				$content['text'] = $tmp_content;
				$cms->setContentData($content);
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Processing a login.
		/////////////////////////////////////////////////////////////////////////////////
		/*
		public function doWsLogin($live_session_id, $session_id, $webseller_session_key) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT id FROM ' . $db->table('webseller_sessions') . ' ' .
						'WHERE ' .
								'id = :id AND ' .
								'webseller_session_key = :webseller_session_key ' .
						'LIMIT 1');
				$db->bind(':id', (int)$session_id);
				$db->bind(':webseller_session_key', $webseller_session_key);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(isset($tmp['id'])) {
						$_SESSION['wscustomer_session_id_live'] = (int)$live_session_id;
						$this->logLoginAttempt('login_successful');
						$this->setLoggedInSession();
						
						header('Location: http://' . cSite::loadSiteUrl(core()->get('site_id')) . 'mein-logo-shop.html');
						die;
				}
				
				core()->set('wscustomer_session_id_live', '');
				return false;
		}
		*/
		
		/////////////////////////////////////////////////////////////////////////////
		// Init all needed data.
		/////////////////////////////////////////////////////////////////////////////
		public function initData() {
				$this->data = array(
						'mail' => '',
						'mail_repeat' => '',
						'pass' => '',
						'accept_agb' => 0
				);
				
				/*
				$this->data['cws'] = array(
						'id' => $session_id,
						'session_id' => $session_id,
						'webseller_session' => cWscustomer::loadSessionData($session_id)
				);
				
				$this->data['cws']['customers_data'] = cAccount::loadUserData($this->data['cws']['webseller_session']['user_id']);
				$this->data['cws']['logo_image_url'] = cWscustomer::getLogoImageUrl($this->data['cws']);
				$this->data['cws']['webseller_machines_data'] = cWebsellermachines::loadEntryById( $this->data['cws']['webseller_session']['webseller_machines_id'] );
				
				//if this is not a seller - do a log entry and update the status.
				*/
		}
		
		/////////////////////////////////////////////////////////////////////////////
		// Init all needed data.
		/////////////////////////////////////////////////////////////////////////////
		public function logLoginAttempt($action) {
				/*
				if(!isset($_SESSION['seller_id'])) {		//Only log, if this is not a seller!
						$state_json = cWebsellersessionslive::makeStateJson('customer_login', $action);
						cWebsellersessionslive::updateByCustomer($this->data['live_session_id'], $state_json);
				}
				*/
		}
		
		/////////////////////////////////////////////////////////////////////////////
		// Init all needed data.
		/////////////////////////////////////////////////////////////////////////////
		public function setLoggedInSession() {
				/*
				$state_json = cWebsellersessionslive::makeStateJson('customer_logged_in', array());
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('webseller_sessions_live') . ' SET ' .
								'state = :state ' .
						'WHERE ' .
								'id = :id'
				);
				$db->bind(':state', $state_json);
				$db->bind(':id', (int)$this->data['live_session_id']);
				$result = $db->execute();
				*/
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the page footer.
		///////////////////////////////////////////////////////////////////
		public function footer() {
				$additional_output = 	
						"\n" . '<script src="//' . cSite::loadSiteUrl(core()->get('site_id')) . 'data/templates/' . $this->template . '/js/frontendRegister.jquery.js"></script>' .
						"\n";
				$renderer = core()->getInstance('cRenderer');
				$renderer->renderText($additional_output);
		}
		
		///////////////////////////////////////////////////////////////////
		// Add code to the beginning of the page.
		// We use this to add custom css!
		///////////////////////////////////////////////////////////////////
		public function beginPage() {
				$additional_output = 	
						"\n" . '<link rel="stylesheet" href="//' . cSite::loadSiteUrl(core()->get('site_id')) . '/data/templates/' . $this->template . '/css/login.css" />' .
						"\n";
				$renderer = core()->getInstance('cRenderer');
				$renderer->renderText($additional_output);
		}
}

?>