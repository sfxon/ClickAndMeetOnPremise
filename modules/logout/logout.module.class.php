<?php

class cLogout extends cModule {
		var $template = 'blitz2016';
		var $cms_id = 0;
		var $contentData = '';
		var $siteUrl = '';
	
		//////////////////////////////////////////////////////////////////////////////////
		// Hook us into the system
		//////////////////////////////////////////////////////////////////////////////////
		public static function setExecutionalHooks() {
				if(!isset($_SESSION['user_id']) && !isset($_SESSION['ws_user_id'])) {
						header('Location: index.php');		//Goto login screen..
						die;
				}
				
				cCMS::setExecutionalHooks();		//We use the CMS module for output.
				
				$core = core();
				
				//Now set our own hooks below the CMS hooks.
				//core()->setHook('cCMS|init', 'init');
				core()->setHook('cCore|process', 'process');
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Verarbeitung..
		/////////////////////////////////////////////////////////////////////////////////
		public function process() {
				//Destroy the cookie, if it is set..
				if(isset($_COOKIE['tellface_longtime'])) {
						unset($_COOKIE['tellface_longtime']); 
    				setcookie('tellface_longtime', null, -1, '/');
				}
				
				//Destroy the session: The officially implemented handler keeps track on also deleting everything in $_SESSION..
				//Be careful - if you ever change it!
				$b = session_destroy();
				
				header('Location: http://' . cSite::loadSiteUrl(core()->get('site_id')));
				die;
		}
}

?>