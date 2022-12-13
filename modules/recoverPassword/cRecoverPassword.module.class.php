<?php

class cRecoverPassword extends cModule {
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
				$success = (int)core()->getGetVar('success');
				
				$this->initData();
				
				//Verarbeitung..
				if($action == 'process') {
						$mail = core()->getPostVar('mail');
						
						$iRecoverPasswordService = new cRecoverPasswordService();
						$status = $iRecoverPasswordService->step1($mail);
						
						header('Location: ' . '//' . cSite::loadSiteUrl(core()->get('site_id')) . 'recover-password?success=1');
						die;
				}
				
				//Output - if user is not doing action!				
				$cms = core()->getInstance('cCMS');
				$content = $cms->loadContentDataByKey('RECOVER_PASSWORD');
				
				//Set the site url. We need this for the form to have the right action url!
				$recover_password_form_url = cSeourls::loadSeourlByQueryString('s=cRecoverPassword');
				$recover_password_form_url = ltrim($recover_password_form_url, '/');
				$recover_password_form_url .= '?action=process';
				$recover_password_form_url = '//' . cSite::loadSiteUrl(core()->get('site_id')) . $recover_password_form_url;

				//Load the CMS Entry for the login page.
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('TEMPLATE_URL', cCMS::loadTemplateUrl(core()->get('site_id')));
				$renderer->assign('TEMPLATE', $cms->getTemplate());
				$renderer->assign('RECOVER_PASSWORD_FORM_URL', $recover_password_form_url);
				$renderer->assign('ERRORMESSAGE', $errormessage);
				$renderer->assign('ERRORS', $this->errors);
				
				
				//Ausgabe
				if($success == 1) {
						$tmp_content = $renderer->fetch('site/recover_password_success.html');
				} else {
						$tmp_content = $renderer->fetch('site/recover_password.html');
				}
				
				$content['text'] = $tmp_content;
				$cms->setContentData($content);
		}
		
		/////////////////////////////////////////////////////////////////////////////
		// Init all needed data.
		/////////////////////////////////////////////////////////////////////////////
		public function initData() {
				$this->data = array(
						'mail' => ''//,
						//'pass' => '',
						//'keep_logged_in' => 0
				);
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
