<?php

class cAccountImageDelete extends cModule {
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
				
				if($action == 'delete') {
						//Bild löschen (auf Festplatte).
						$filename = $this->data['user_image']['filename'];
						unlink($filename);
						
						//Bild in Datenbank löschen.
						$userImages = new cUserImages();
						$userImages->deleteImage($this->data['user_image']['id'], $_SESSION['ws_user_id']);
						
						//Redirect to overview with success message.
						header('Location: http://' . cSite::loadSiteUrl(core()->get('site_id')) . 'meine-fotos/?success=2');
						die();
				}
				
				//$userImages = new cUserImages();
				//$user_images = $userImages->loadUserImages($_SESSION['ws_user_id'], 5);
				
				//Output - if user is not doing action!				
				$cms = core()->getInstance('cCMS');
				$content = $cms->loadContentDataByKey('ACCOUNT_IMAGE_DELETE');
				
				//Load the CMS Entry for the login page.
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('TEMPLATE_URL', cCMS::loadTemplateUrl(core()->get('site_id')));
				$renderer->assign('TEMPLATE', $cms->getTemplate());
				$renderer->assign('ERRORMESSAGE', $errormessage);
				$renderer->assign('ERRORS', $errors);
				$renderer->assign('SUCCESS', $success);
				$tmp_content = $renderer->fetch('site/account_image_delete.html');
				
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
		/*
		public function footer() {
				$additional_output = 	
						"\n" . '<script src="//' . cSite::loadSiteUrl(core()->get('site_id')) . 'data/templates/' . $this->template . '/js/accountImageSettings.jquery.js"></script>' .
						"\n";
				$renderer = core()->getInstance('cRenderer');
				$renderer->renderText($additional_output);
		}
		*/
}