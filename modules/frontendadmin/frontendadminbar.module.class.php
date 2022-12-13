<?php

class cFrontendadminbar extends cModule {
		var $template = 'maxis';
		var $cms_id = 0;
		var $contentData = '';
		var $siteUrl = '';
	
		//////////////////////////////////////////////////////////////////////////////////
		// Hook us into the system
		//////////////////////////////////////////////////////////////////////////////////
		public static function setAdditionalHooks() {
				if(isset($_SESSION['user_id'])) {
						//check the rights..
						if(true === cAccount::adminrightCheck('cFrontendadminbar', 'USE_MODULE', (int)$_SESSION['user_id'])) {
								core()->setHook('cRenderer|header_bar', 'header_bar');
						}
				}
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Render the Header Bar (for example: logo, header navigation)
		// e.g.: Things that are always of the same style on every page.
		/////////////////////////////////////////////////////////////////////////////////
		public function header_bar() {
				$lang = core()->get('lang');
				
				$liveEditMode = '1';
				
				if(isset($_SESSION['liveEditMode'])) {
						$liveEditMode = '2';
				}
				
				$domain = core()->getCurrentDomain();
				$path = cSite::getCurrentSitesPath();
				
				$data = array(
						array(
								'url' => 'http://' . $domain . $path . 'index.php?s=cFrontendadmincms&amp;action=toggleEditMode',
								'title' => 'Live-Edit',
								'status' => $liveEditMode
						), array(
								'url' => 'http://' . $domain . $path . 'index.php?s=cAdmin',
								'title' => 'Admin',
								'status' => 1
						)
				);
				
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $data);
				$renderer->render('site/frontendadmin/header_bar.html');
		}
}

?>