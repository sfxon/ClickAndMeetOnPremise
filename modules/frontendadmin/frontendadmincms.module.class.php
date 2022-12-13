<?php

class cFrontendadmincms extends cModule {
		var $template = 'maxis';
		var $cms_id = 0;
		var $contentData = '';
		var $siteUrl = '';
	
		//////////////////////////////////////////////////////////////////////////////////
		// Hook us into the system
		//////////////////////////////////////////////////////////////////////////////////
		public static function setExecutionalHooks() {
				if(!isset($_SESSION['user_id'])) {
						header('Location: index.php?s=notallowed');
						die;
				}
				
				//check the rights..
				if(true === cAccount::adminrightCheck('cFrontendadmincms', 'USE_MODULE', (int)$_SESSION['user_id'])) {
						core()->setHook('cCore|process', 'process');
				} else {
						header('Location: index.php?s=notallowed');
						die;
				}
				
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Hook into the CMS Module output.
		/////////////////////////////////////////////////////////////////////////////////
		public static function setAdditionalHooks() {
				if(!isset($_SESSION['user_id'])) {
						return;
				}
				
				//Do only execute, if user is allowed to use this module!
				if(true === cAccount::adminrightCheck('cFrontendadmincms', 'USE_MODULE', (int)$_SESSION['user_id'])) {
						if(isset($_SESSION['liveEditMode'])) {
								core()->setHook('cCMS|content', 'content', true);		//Set a content hook before the CMS modules content hook.
						}
				}
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Process this request
		/////////////////////////////////////////////////////////////////////////////////
		public function process() {
				$action = core()->getGetVar('action');
				
				switch($action) {
						case 'toggleEditMode':
								$this->toggleLiveEditMode();
								break;
						case 'ajax_save_cms_entry':
								$this->ajaxSaveCmsEntry();
								break;
				}
		}
		
		///////////////////////////////////////////////////////////////////////////////
		// Save a cms entry.
		///////////////////////////////////////////////////////////////////////////////
		public function ajaxSaveCmsEntry() {
				//check if user is allowed to save content entries.
				if(false === cAccount::adminrightCheck('cAdmincms', 'SAVE_CONTENT_DATA', (int)$_SESSION['user_id'])) {
						cAjax::returnErrorAndQuit(9, 'The user is not allowed to save content entries.');
						die;		//Die's without any warning..
				}
				
				$core = core();
				$content_id = (int)$core->getGetVar('content_id');
				$language_id = (int)$core->getGetVar('language_id');
				$text = $core->getPostVar('text');
				
				//1st -> Check content id
				if(!cCMS::contentIdExists((int)$content_id)) {
						cAjax::returnErrorAndQuit(10, 'There is no content with this id in the database table.');
						die;
				}
				
				//2nd -> Check languages id
				if(!cLang::languageIdExists((int)$language_id)) {
						cAjax::returnErrorAndQuit(11, 'There is no language with this id in the database table.');
						die;
				}
				
				//3rd -> Save now!
				cAdmincms::saveContentText((int)$content_id, (int)$language_id, $text);
				
				cAjax::returnSuccessAndQuit(1, 'The cms item has been updated successful.');
				die;
		}
		
		///////////////////////////////////////////////////////////////////////////////
		// Toggle the live edit mode in the session.
		///////////////////////////////////////////////////////////////////////////////
		public function toggleLiveEditMode() {
				if(isset($_SESSION['user_id'])) {
						if(true === cAccount::adminrightCheck('cFrontendadminbar', 'USE_MODULE', (int)$_SESSION['user_id'])) {
								if(isset($_SESSION['liveEditMode'])) {
										unset($_SESSION['liveEditMode']);
								} else {
										$_SESSION['liveEditMode'] = 1;
								}
						}
				}
				
				//get calling page..
				header('Location: ' . $_SERVER['HTTP_REFERER']);
				die;
		}
		
		/////////////////////////////////////////////////////////////////////////////
		// Modify content, before it get's rendered.
		/////////////////////////////////////////////////////////////////////////////
		public function content() {
				/*$cCMS = core()->getInstance('cCMS');
				$contentData = $cCMS->getContentData();
				
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $contentData);
				$renderer->assign('LANGUAGE', core()->get('lang'));
				$output = $renderer->fetch('site/frontendadmin/content_editor.html');
				
				$contentData['text'] = $output;
				
				$cCMS->setContentData($contentData);*/
		}
}

?>