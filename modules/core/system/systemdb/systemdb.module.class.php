<?php

/////////////////////////////////////////////////////////////////////////////////
// The systems database initialisation.
// We use this database information for all our system modules.
// In this database we save things like session_data, languages_data, ...
// It is very essential for all other modules.
/////////////////////////////////////////////////////////////////////////////////
class cSystemdb extends cModule {		
		/////////////////////////////////////////////////////////////////////////
		// Define where to set this module in the boot (hook) chain.
		/////////////////////////////////////////////////////////////////////////
		public static function setBootHooks() {
				core()->setBootHook('cBoot');
		}
		
		////////////////////////////////////////////////////////////////////////
		// This is executed when the system boots and the chain reached this module.
		////////////////////////////////////////////////////////////////////////
		public static function boot() {
				$config = cConfig::loadIniFileAsArray('data/config/systemdb.ini', array('dbmodule', 'instance', 'host', 'db', 'user', 'pw', 'prefix', 'table_quotes'));
				
				$db = new cDB();
				$db->initInstance($config['dbmodule'], $config['instance'], $config['prefix'], $config['table_quotes']);
				$db->connect($config['host'], $config['db'], $config['user'], $config['pw']);
				
				core()->set('db', $db);
		}
		
		//////////////////////////////////////////////////////////////////////////
		// Returns an array of all modules.
		//
		// Return Value Array Shema:
		//		array(
		//				array(
		//						'module' => 'path/and/module/name',
		//						'version' => '1.6'		//Minimum version of dependent module that is needed to run this module.
		//				), 
		//				array(..)
		//		);
		//
		//		The systems core logic checks all dependencies in the auto loader.
		//			
		//////////////////////////////////////////////////////////////////////////
		public static function getDependenciesAsArray() {
				return array(
						array(
								'module' => '/core/config/cConfig'
						)
				);
		}
		
		//////////////////////////////////////////////////////////////////////////
		// Returns the version of a module.
		// Returns 0 (zero) if you define no version for your module.
		//////////////////////////////////////////////////////////////////////////
		public static function getVersion() {
				return 0.1;
		}
}

?>