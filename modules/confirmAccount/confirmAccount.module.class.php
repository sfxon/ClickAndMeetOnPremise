<?php

class cConfirmAccount extends cModule {
		var $template = 'tellface';
		var $cms_id = 0;
		var $contentData = '';
		var $siteUrl = '';
	
		//////////////////////////////////////////////////////////////////////////////////
		// Hook us into the system
		//////////////////////////////////////////////////////////////////////////////////
		public static function setExecutionalHooks() {
				//If the user is logged in..
				if(isset($_SESSION['frontend_session_id_live'])) {
						die('forward to user dashboard');
						
						//header('Location: http://' . cSite::loadSiteUrl(core()->get('site_id')) . 'mein-logo-shop.html');
						//die;
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
				$this->initData();
				
				//Daten abrufen.
				$tmp = core()->getGetVar('tmp');
				$user_id = core()->getGetVar('user_id');
				
				if(strlen(trim($tmp)) == '') {
						header('Location: ' . cCMS::loadTemplateUrl(core()->get('site_id')));
						die;
				}
				
				//Daten in der Datenbank überprüfen.
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('accounts') . ' WHERE ' .
								'id = :user_id AND ' .
								'registration_key = :tmp;'
				);
				$db->bind(':user_id', (int)$user_id);
				$db->bind(':tmp', $tmp);
				$result = $db->execute();
				$data = $result->fetchArrayAssoc();
				
				//Wenn der User nicht gefunden wurde..
				if(!isset($data['id'])) {
						header('Location: ' . cCMS::loadTemplateUrl(core()->get('site_id')));
						die;
				}
				
				$this->data['mail'] = $data['email'];
				
				//Wenn der User gefunden wurde..
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('accounts') . ' SET ' .
								'registration_key = "", ' .
								'account_type = 2 ' . 	//0 = Gesperrt, 1 = Admin, 2 = User
						' WHERE ' .
								'id = :user_id AND ' .
								'registration_key = :tmp;'
				);
				$db->bind(':user_id', (int)$user_id);
				$db->bind(':tmp', $tmp);
				$result = $db->execute();
				$result->fetchArrayAssoc();
				
				//Set the site url. We need this for the form to have the right action url!
				$login_form_url = cSeourls::loadSeourlByQueryString('s=cFrontendLogin');
				$login_form_url = ltrim($login_form_url, '/');
				$login_form_url .= '?action=process';
				$login_form_url = '//' . cSite::loadSiteUrl(core()->get('site_id')) . $login_form_url;
				
				//Load the CMS Entry for the login page.
				$cms = core()->getInstance('cCMS');
				$content = $cms->loadContentDataByKey('REGISTRATION_COMPLETED');
				
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('TEMPLATE_URL', cCMS::loadTemplateUrl(core()->get('site_id')));
				$renderer->assign('TEMPLATE', $cms->getTemplate());
				$renderer->assign('LOGIN_FORM_URL', $login_form_url);
				//$renderer->assign('SESSION_KEY', $session_key);
				$renderer->assign('ERRORMESSAGE', $errormessage);
				$renderer->assign('ERRORS', $errors);
				$tmp_content = $renderer->fetch('site/registration_completed.html');
				
				
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
						'pass' => ''
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
}

?>