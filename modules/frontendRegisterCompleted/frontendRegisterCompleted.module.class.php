<?php

class cFrontendRegisterCompleted extends cModule {
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
				//core()->setHook('cRenderer|end_page', 'footer');
				core()->setHook('cRenderer|begin_page', 'beginPage');
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Verarbeitung..
		/////////////////////////////////////////////////////////////////////////////////
		public function process() {
				//Set the site url. We need this for the form to have the right action url!
				$register_form_url = cSeourls::loadSeourlByQueryString('s=cFrontendRegisterCompleted');
				$register_form_url = ltrim($register_form_url, '/');
				$register_form_url .= '?action=process';
				$register_form_url = '//' . cSite::loadSiteUrl(core()->get('site_id')) . $register_form_url;
				
				//Load the CMS Entry for the login page.
				$cms = core()->getInstance('cCMS');
				$content = $cms->loadContentDataByKey('REGISTRATION');
				
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('TEMPLATE_URL', cCMS::loadTemplateUrl(core()->get('site_id')));
				$renderer->assign('TEMPLATE', $cms->getTemplate());
				//$renderer->assign('ERRORMESSAGE', $errormessage);
				//$renderer->assign('ERRORS', $errors);
				$tmp_content = $renderer->fetch('site/registration_completed.html');
				
				
				$content['text'] = $tmp_content;
				$cms->setContentData($content);
		}
		
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
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the page footer.
		///////////////////////////////////////////////////////////////////
		/*
		public function footer() {
				$additional_output = 	
						"\n" . '<script src="//' . cSite::loadSiteUrl(core()->get('site_id')) . 'data/templates/' . $this->template . '/js/frontendRegister.jquery.js"></script>' .
						"\n";
				$renderer = core()->getInstance('cRenderer');
				$renderer->renderText($additional_output);
		}
		*/
		
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