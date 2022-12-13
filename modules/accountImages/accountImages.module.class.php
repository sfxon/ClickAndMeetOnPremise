<?php

class cAccountImages extends cModule {
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
				
				switch($action) {
						case 'ajax-load-more-images':
								$this->ajaxLoadMoreImages();
								break;
				}
				
				$user_images = $this->loadUserImages();
				
				//Output - if user is not doing action!				
				$cms = core()->getInstance('cCMS');
				$content = $cms->loadContentDataByKey('ACCOUNT_IMAGES');

				//Load the CMS Entry for the login page.
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('TEMPLATE_URL', cCMS::loadTemplateUrl(core()->get('site_id')));
				$renderer->assign('TEMPLATE', $cms->getTemplate());
				//$renderer->assign('LOGIN_FORM_URL', $login_form_url);
				$renderer->assign('ERRORMESSAGE', $errormessage);
				$renderer->assign('ERRORS', $errors);
				$renderer->assign('SUCCESS', $success);
				$renderer->assign('USER_IMAGES', $user_images);
				$tmp_content = $renderer->fetch('site/account_images.html');
				
				$content['text'] = $tmp_content;
				$cms->setContentData($content);
		}
		
		/////////////////////////////////////////////////////////////////////////////
		// HTML mit weiteren Bilddaten laden..
		/////////////////////////////////////////////////////////////////////////////
		public function ajaxLoadMoreImages() {
				$index = (int)core()->getGetVar('index');
				
				$iUserImages = new cUserImages();
				$user_images = $this->loadUserImagesFrom($index);
				$user_images_count = 0;
				
				if(is_array($user_images)) {
						$user_images_count = count($user_images);
				}
				
				if($user_images_count > 0) {
						$cms = core()->getInstance('cCMS');
						$content = $cms->loadContentDataByKey('ACCOUNT_IMAGES');
		
						//Load the CMS Entry for the login page.
						$renderer = core()->getInstance('cRenderer');
						$renderer->setTemplate($this->template);
						$renderer->assign('DATA', $this->data);
						$renderer->assign('TEMPLATE_URL', cCMS::loadTemplateUrl(core()->get('site_id')));
						$renderer->assign('TEMPLATE', $cms->getTemplate());
						//$renderer->assign('LOGIN_FORM_URL', $login_form_url);
						//$renderer->assign('ERRORMESSAGE', $errormessage);
						//$renderer->assign('ERRORS', $errors);
						//$renderer->assign('SUCCESS', $success);
						$renderer->assign('USER_IMAGES', $user_images);
						$html = $renderer->fetch('site/account_images_more_images.html');
				} else {
						$html = '<div id="account-images-no-more-images">Schön dich hier bei Tellface zu sehen. :)</div>';
				}
				
				$retval = array(
						'status' => 'success',
						'count' => $user_images_count,
						'count_images_total' => $iUserImages->countUserImagesTotal($_SESSION['ws_user_id']),
						'html' => $html
				);
				$retval = json_encode($retval);
				echo $retval;
				die;
		}
		
		/////////////////////////////////////////////////////////////////////////////
		// Load user images
		/////////////////////////////////////////////////////////////////////////////
		public function loadUserImages() {
				$userImages = new cUserImages();
				$user_images = $userImages->loadUserImages($_SESSION['ws_user_id'], 5);
				
				return $user_images;
		}
		
		/////////////////////////////////////////////////////////////////////////////
		// Load user images
		/////////////////////////////////////////////////////////////////////////////
		public function loadUserImagesFrom($index) {
				$userImages = new cUserImages();
				$user_images = $userImages->loadUserImagesFrom($_SESSION['ws_user_id'], $index, 6);
				
				return $user_images;
		}
		
		/////////////////////////////////////////////////////////////////////////////
		// Init all needed data.
		/////////////////////////////////////////////////////////////////////////////
		public function initData() {
				$this->data = array(
						/*
						'mail' => '',
						'pass' => '',
						'keep_logged_in' => 0
						*/
				);
		}
		
		///////////////////////////////////////////////////////////////////
		// Add code to the beginning of the page.
		// We use this to add custom css!
		///////////////////////////////////////////////////////////////////
		public function beginPage() {
				$additional_output = 	
						"\n" . '<link rel="stylesheet" href="//' . cSite::loadSiteUrl(core()->get('site_id')) . '/data/templates/tellface/css/dropzone.min.css" />' .
						"\n" . '<link rel="stylesheet" href="//' . cSite::loadSiteUrl(core()->get('site_id')) . '/data/templates/tellface/css/cropper.min.css" />' .
						"\n";
				$renderer = core()->getInstance('cRenderer');
				$renderer->renderText($additional_output);
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the page footer.
		///////////////////////////////////////////////////////////////////
		public function footer() {
				$additional_output = 	
						"\n" . '<script src="//' . cSite::loadSiteUrl(core()->get('site_id')) . 'data/templates/' . $this->template . '/js/dropzone.min.js"></script>' .
						"\n" . '<script src="//' . cSite::loadSiteUrl(core()->get('site_id')) . 'data/templates/' . $this->template . '/js/cropper.min.js"></script>' .
						"\n" . '<script src="//' . cSite::loadSiteUrl(core()->get('site_id')) . 'data/templates/' . $this->template . '/js/accountImages.jquery.js"></script>' .
						"\n";
				$renderer = core()->getInstance('cRenderer');
				$renderer->renderText($additional_output);
		}

}