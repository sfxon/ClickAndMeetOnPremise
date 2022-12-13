<?php

class cEnumTest extends cModule {
		var $template = 'tellface';
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
				//core()->setHook('cRenderer|content', 'content');
				//core()->setHook('cRenderer|end_page', 'footer');
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Verarbeitung..
		/////////////////////////////////////////////////////////////////////////////////
		public function process() {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SHOW COLUMNS FROM ' . $db->table('accounts') . ' LIKE \'registration_platform\'');
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				$option_array = explode("','",preg_replace("/(enum|set)\('(.+?)'\)/","\\2", $tmp['Type']));
				
				var_dump($option_array);
				
				
				echo '***';
				die;
		}


}

?>