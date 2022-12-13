<?php

class cAccountImageSettings extends cModule {
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
				core()->setHook('cRenderer|begin_page', 'beginPage');
				core()->setHook('cRenderer|end_page', 'footer');
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
				
				if($action == 'update') {
						$age = (int)core()->getPostVar('age');
						$message = core()->getPostVar('message');
						
						if($age < 18 || $age > 300) {
								die('error');
						}
						
						//Update image meta data.
						$userImages = new cUserImages();
						$userImages->updateAgeAndMessage($this->data['user_image']['id'], $_SESSION['ws_user_id'], $age, $message);
						header('Location: http://' . cSite::loadSiteUrl(core()->get('site_id')) . 'bild-einstellungen/?img_id=' . (int)$this->data['user_image']['id'] . '&success=3');
						die;
				}
				
				//Output - if user is not doing action!				
				$cms = core()->getInstance('cCMS');
				$content = $cms->loadContentDataByKey('ACCOUNT_IMAGE_SETTINGS');
				
				//Load the CMS Entry for the login page.
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('TEMPLATE_URL', cCMS::loadTemplateUrl(core()->get('site_id')));
				$renderer->assign('TEMPLATE', $cms->getTemplate());
				$renderer->assign('ERRORMESSAGE', $errormessage);
				$renderer->assign('ERRORS', $errors);
				$renderer->assign('SUCCESS', $success);
				$tmp_content = $renderer->fetch('site/account_image_settings.html');
				
				$content['text'] = $tmp_content;
				$cms->setContentData($content);
		}
		
		/////////////////////////////////////////////////////////////////////////////
		// Init all needed data.
		/////////////////////////////////////////////////////////////////////////////
		public function initData() {
				$user_image_id = (int)core()->getGetVar('img_id');
				
				$userImages = new cUserImages();
				$user_image = $userImages->loadById($user_image_id, $_SESSION['ws_user_id']);
				
				if(false === $user_image) {
						header('Location: http://' . cSite::loadSiteUrl(core()->get('site_id')) . 'meine-fotos/?error=2');
				}
				
				$this->data = array(
						'user_image' => $user_image
				);
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the page footer.
		///////////////////////////////////////////////////////////////////
		public function footer() {
				$additional_output = 	
						"\n" . '<script src="//' . cSite::loadSiteUrl(core()->get('site_id')) . 'data/templates/' . $this->template . '/js/accountImageSettings.jquery.js"></script>' .
						"\n";
				$renderer = core()->getInstance('cRenderer');
				$renderer->renderText($additional_output);
		}
}