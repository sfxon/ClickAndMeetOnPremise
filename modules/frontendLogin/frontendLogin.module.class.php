<?php

class cFrontendLogin extends cModule {
		var $template = 'maxis';
		var $cms_id = 0;
		var $contentData = '';
		var $siteUrl = '';
		var $wrongLoginText = 'Login war nicht erfolgreich. Bitte versuche es erneut!';
		var $accountNotActivated = 'Der Account wurde noch nicht aktiviert. Bitte prüfe dein Mail-Postfach.';
		var $accountIsBanned = 'Der Account wurde aus Sicherheitsgründen gesperrt. Wenn du nicht weißt wieso, wende dich bitte an uns.';
	
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
				core()->setHook('cRenderer|begin_page', 'beginPage');
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Verarbeitung..
		/////////////////////////////////////////////////////////////////////////////////
		public function process() {
				$errormessage = '';
				$action = core()->getGetVar('action');
				$this->errors = array();
				
				$this->initData();
				
				if($action == 'process') {
						$mail = core()->getPostVar('mail');
						$pass = core()->getPostVar('pass');
						$keep_logged_in = (int)core()->getPostVar('keep_logged_in');
						
						$this->data['mail'] = $mail;
						$this->data['keep_logged_in'] = $keep_logged_in;
						
						if(strlen($mail) == 0 || strlen($pass) == 0) {
								$this->errors['wrong_login_data'] = $this->wrongLoginText;
						}
						
						//Login?
						if(count($this->errors) == 0) {
								$this->doLogin($mail, $pass, $keep_logged_in);
						}
				}
				
				//Output - if user is not doing action!				
				$cms = core()->getInstance('cCMS');
				$cms->setHtmlBodyClasses('login scrollable');
				$content = $cms->loadContentDataByKey('FRONTEND_LOGIN');
				
				//Set the site url. We need this for the form to have the right action url!
				$login_form_url = cSeourls::loadSeourlByQueryString('s=cFrontendLogin');
				$login_form_url = ltrim($login_form_url, '/');
				$login_form_url .= '?action=process';
				$login_form_url = '//' . cSite::loadSiteUrl(core()->get('site_id')) . $login_form_url;

				//Load the CMS Entry for the login page.
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('TEMPLATE_URL', cCMS::loadTemplateUrl(core()->get('site_id')));
				$renderer->assign('TEMPLATE', $cms->getTemplate());
				$renderer->assign('LOGIN_FORM_URL', $login_form_url);
				$renderer->assign('ERRORMESSAGE', $errormessage);
				$renderer->assign('ERRORS', $this->errors);
				$tmp_content = $renderer->fetch('site/frontend_login.html');
				
				$content['text'] = $tmp_content;
				$cms->setContentData($content);
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Processing a login.
		/////////////////////////////////////////////////////////////////////////////////
		public function doLogin($mail, $pass, $keep_logged_in) {
				//Select user with this mail address from database.
				$account = new cAccount();
				$user_id = $account->loadAccountIdByEmail($mail);
				
				if(false === $user_id) {
						$this->errors['wrong_login_data'] = $this->wrongLoginText;
						return false;
				}
				
				//Load additional user data..
				$user_data = $account->loadById($user_id);
				
				if(false === $user_data) {
						$this->errors['wrong_login_data'] = $this->wrongLoginText;
						return false;
				}
				
				//Check if user is activated
				if($user_data['account_type'] != 1 && $user_data['account_type'] != 2) {
						$this->errors['wrong_login_data'] = $this->accountNotActivated;
						return false;
				}
				
				//Check if user is banned
				if($user_data['is_banned'] == 1) {
						$this->errors['wrong_login_data'] = $this->accountIsBanned;
						return false;
				}
				
				//Check password..
				$password_hash = $user_data['password'];
				
				if(false === password_verify($pass, $password_hash)) {		//Aaaaaaand - you are logged in!
						$this->errors['wrong_login_data'] = $this->wrongLoginText;
						return false;
				}
				
				//Einloggen..
				$_SESSION['ws_user_id'] = $user_id;
				
				//Save user_id in sessions table, too. We might need this, if we want to logout all instances for a specific user for example.
				//var_dump(session_id());
				
				//cSession::updateUserIdInSession(session_id(), $_SESSION['ws_user_id']);
				
				//Wenn der Langzeit-Cookie gesetzt werden soll.
				if(1 == (int)$keep_logged_in) {
						$long_time_session = new cLongTimeSession();
						$token = $long_time_session->create($user_id);
						
						setcookie('tellface_longtime', $token, $long_time_session->getCookieLifetime());		//Parameter 3 is lifetime of the cookie, and set to 3 weeks right now..
				}
				
				//Redirect to dashboard.
				header('Location: http://' . cSite::loadSiteUrl(core()->get('site_id')) . 'meine-seite');
				die;
		}
		
		/////////////////////////////////////////////////////////////////////////////
		// Init all needed data.
		/////////////////////////////////////////////////////////////////////////////
		public function initData() {
				$this->data = array(
						'mail' => '',
						'pass' => '',
						'keep_logged_in' => 0
				);
		}
		
		/////////////////////////////////////////////////////////////////////////////
		// Init all needed data.
		/////////////////////////////////////////////////////////////////////////////
		public function setLoggedInSession() {
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
