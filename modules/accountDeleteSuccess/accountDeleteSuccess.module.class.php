<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
// Account wurde erfolgreich gelöscht.
///////////////////////////////////////////////////////////////////////////////////////////////////
class cAccountDeleteSuccess extends cModule {
		var $template = 'tellface';
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
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Verarbeitung..
		/////////////////////////////////////////////////////////////////////////////////
		public function process() {
				$this->initData();
				
				//Output - if user is not doing action!				
				$cms = core()->getInstance('cCMS');
				$content = $cms->loadContentDataByKey('ACCOUNT_DELETE_SUCCESS');
				
				//Set the site url. We need this for the form to have the right action url!
				/*
				$login_form_url = cSeourls::loadSeourlByQueryString('s=cFrontendLogin');
				$login_form_url = ltrim($login_form_url, '/');
				$login_form_url .= '?action=process';
				$login_form_url = '//' . cSite::loadSiteUrl(core()->get('site_id')) . $login_form_url;
				*/

				//Load the CMS Entry for the login page.
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('TEMPLATE_URL', cCMS::loadTemplateUrl(core()->get('site_id')));
				$renderer->assign('TEMPLATE', $cms->getTemplate());
				//$renderer->assign('LOGIN_FORM_URL', $login_form_url);
				$renderer->assign('ERRORS', array());
				$tmp_content = $renderer->fetch('site/account_delete_success.html');
				$content['text'] = $tmp_content;
				$cms->setContentData($content);
		}
		
		/////////////////////////////////////////////////////////////////////////////
		// Init all needed data.
		/////////////////////////////////////////////////////////////////////////////
		public function initData() {
				$this->data = array();
		}
}
