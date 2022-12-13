<?php

class cClearTemplateCache extends cModule {
		var $template = 'smartseller';
		var $cms_id = 0;
		var $contentData = '';
		var $siteUrl = '';
		var $action = '';
		var $errors = array();
		var $curl_retval = '';
		var $required_fields = array();
		var $error_fields = array();
		var $data = array();
		
		//////////////////////////////////////////////////////////////////////////////////
		// Hook us into the system
		//////////////////////////////////////////////////////////////////////////////////
		public static function setExecutionalHooks() {
				//We use the CMS module for output.
				cCMS::setExecutionalHooks();		
				
				$core = core();
				
				//Now set our own hooks below the CMS hooks.
				core()->setHook('cCore|process', 'process');
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Verarbeitung..
		/////////////////////////////////////////////////////////////////////////////////
		public function process() {
				$path = 'data/tmp/smarty/templates_c';
				
				$this->deleteDirectory($path);
				
				die('done');
		}
		
		function deleteDirectory($dir) {
				if (!file_exists($dir)) {
						return true;
				}
		
				if (!is_dir($dir)) {
						return unlink($dir);
				}
		
				foreach (scandir($dir) as $item) {
						if ($item == '.' || $item == '..') {
								continue;
						}
		
						if (!$this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
								return false;
						}
				}
		
				return rmdir($dir);
		}
}