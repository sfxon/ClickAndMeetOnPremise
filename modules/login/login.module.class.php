<?php

class cLogin extends cModule {
		var $template = 'maxis';
		var $cms_id = 0;
		var $contentData = '';
		var $siteUrl = '';
	
		//////////////////////////////////////////////////////////////////////////////////
		// Hook us into the system
		//////////////////////////////////////////////////////////////////////////////////
		public static function setExecutionalHooks() {
				//If the user is logged in..
				if(isset($_SESSION['user_id'])) {
						//if the user is an admin - redirect him to the admin area!
						if(true === cAccount::adminrightCheck('cAdmin', 'USE_MODULE', (int)$_SESSION['user_id'])) {
								header('Location: //' . cSite::loadSiteUrl(core()->get('site_id')) . 'index.php?s=cAdmin');
								die;
						}
						
						header('Location: //' . cSite::loadSiteUrl(core()->get('site_id')) . 'meine-seite');
						die;
				}
				
				//We use the CMS module for output.
				cCMS::setExecutionalHooks();		
				
				$core = core();
				
				//Now set our own hooks below the CMS hooks.
				//core()->setHook('cCMS|init', 'init');
				core()->setHook('cCore|process', 'process');
				core()->setHook('cRenderer|begin_page', 'beginPage');
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Verarbeitung..
		/////////////////////////////////////////////////////////////////////////////////
		public function process() {				
				$errormessage = '';
				$action = core()->getGetVar('action');

				if($action == 'process') {
						$username = trim(core()->getPostVar('login_name'));
						$password = trim(core()->getPostVar('login_password'));
						
						if(strlen($username) < 3) {
								$renderer = core()->getInstance('cRenderer');
								$renderer->setTemplate($this->template);
								$renderer->assign('ERRORMESSAGE', ERROR_TEXT_LOGIN_INCORRECT);
								$errormessage = $renderer->fetch('site/errormessage.html');		
						} else if (strlen($password) < 3) {
								$renderer = core()->getInstance('cRenderer');
								$renderer->setTemplate($this->template);
								$renderer->assign('ERRORMESSAGE', ERROR_TEXT_LOGIN_INCORRECT);
								$errormessage = $renderer->fetch('site/errormessage.html');
						} else {
								if(false === $this->doLogin($username, $password)) {
										$renderer = core()->getInstance('cRenderer');
										$renderer->setTemplate($this->template);
										$renderer->assign('ERRORMESSAGE', ERROR_TEXT_LOGIN_INCORRECT);
										$errormessage = $renderer->fetch('site/errormessage.html');								
								}
						}
				}

				//Load the CMS Entry for the login page.
				$cms = core()->getInstance('cCMS');
				$cms->setHtmlBodyClasses('login scrollable');
				$content = $cms->loadContentDataByKey('ADMIN_LOGIN');
				$content = str_replace('{$ERRORMESSAGE}', $errormessage, $content);
				$cms->setContentData($content);
				
				//Set the site url. We need this for the form to have the right action url!
				$site_url = cSeourls::loadSeourlByQueryString('s=cLogin');
				$site_url = ltrim($site_url, '/');
				$cms->setSiteUrl(cSite::loadSiteUrl(core()->get('site_id')) . $site_url);
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Processing a login.
		/////////////////////////////////////////////////////////////////////////////////
		public function doLogin($email, $password) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT id, password FROM ' . $db->table('accounts') . ' WHERE LOWER(email) = LOWER(:email) LIMIT 1');
				$db->bind(':email', $email);
				
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(isset($tmp['id'])) {
						$password_hash = $tmp['password'];
						
						if(true === password_verify($password, $password_hash)) {		//Aaaaaaand - you are logged in!
								$_SESSION['user_id'] = (int)$tmp['id'];
								
								//Save user_id in sessions table, too. We might need this, if we want to logout all instances for a specific user for example.
								//cSession::updateUserIdInSession(session_id(), $_SESSION['user_id']);
								
								header('Location: http://' . cSite::loadSiteUrl(core()->get('site_id')) . 'admin');
								die;
						}
				}
				
				core()->set('user_id', '');
				return false;
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