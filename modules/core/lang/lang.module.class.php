<?php

/////////////////////////////////////////////////////////////////////////////////
// System languages module.
// This is the core for the multi language system.
//
//TODO: check that the module exists - that is tried to be loaded in "loadLanguages"
//
/////////////////////////////////////////////////////////////////////////////////
class cLang extends cModule {		
		var $default_language_id;
		var $language_count;
		var $langs;
		
		////////////////////////////////////////////////////////////////////////////
		// Constructor
		////////////////////////////////////////////////////////////////////////////		
		function __construct() {
				$this->language_count = 0;
				$this->langs = array();
				$this->initialize();
		}
		
		///////////////////////////////////////////////////////////////////////////
		// Start the language engine
		///////////////////////////////////////////////////////////////////////////
		function initialize() {
				//Load default language id - or if it is set - load the language id provided with the get
				if(isset($_GET['set_language'])) {
						$_SESSION['language'] = $_GET['set_language'];
						$this->default_language_id = $_GET['set_language'];
				}
				
				if(!isset($_SESSION['language'])) {
						$_SESSION['language'] = 1;
						$this->default_language_id = 1;
				} else {
						$this->default_language_id = $_SESSION['language'];
				}
				
				// Load language				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT id, name, iso_code_2, iso_code_3 FROM ' . $db->table('languages'));
				$result = $db->execute();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();						
						$this->langs[$result->value('id')] = $tmp;
				}
				
				//check if there are any languages installed - if not - this is a core error!
				if(count($this->langs) == 0) {
						core()->addCoreError(4, 'There are no system languages defined. (cLang->initialize)', print_r($this->langs, true));
						core()->coreError();
						die;		//It already die's in coreError, but this is to make things clear!
				}
				
				$this->loadLanguages();
				
				core()->set('lang', $this);
		}
		
		/////////////////////////////////////////////////////////////////////////
		// Define where to set this module in the boot (hook) chain.
		/////////////////////////////////////////////////////////////////////////
		public static function setBootHooks() {
				core()->setBootHook('cSession');
		}
		
		////////////////////////////////////////////////////////////////////////
		// This is executed when the system boots and the chain reached this module.
		////////////////////////////////////////////////////////////////////////
		public static function boot() {
				$lang = new cLang();		//Creates this class! Execution starts immediately in constructor!
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
								'module' => '/core/session/cSession'
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
		
		//////////////////////////////////////////////////////////////////////////
		// Load languages
		//////////////////////////////////////////////////////////////////////////
		public function loadLanguages() {
				$script_filename =  'data/languages/' . $this->langs[$this->default_language_id]['iso_code_2'] . '.php';

				if(!file_exists($script_filename)) {
						$db = core()->get('db');
						$db->useInstance('systemdb');
						//TODO: set only the needed fields in the select - not longer the * (star symbol to load all)
						$db->setQuery('SELECT * FROM ' . $db->table('text_variables') . ' WHERE filename = :filename AND language_id = :language_id');
						
						$db->bind(':filename', '');
						$db->bind(':language_id', $this->default_language_id);
						$result = $db->execute();
						
						//put the file contents here!!!
						$fp = fopen($script_filename, "w");
						fwrite($fp, '<?php' . "\n");
						
						while($result->next()) {
								fwrite($fp, 'define("' . $result->value('name') . '", \'' . $result->value('item_text') . '\');' . "\n");
						}
						
						fwrite($fp, '?>');
						fclose($fp);
				}

				if(file_exists($script_filename)) {
						require_once($script_filename);
				}

				/*** THIS PART LOADS THE VERY SPECIAL VARIABLES FOR THIS SCRIPT **************************/
				// get main script name extract the install-folder stuff..
				//$install_folder = '/install/';
				$install_folder = '';
				$install_folder_index = 0;
				
				if(!empty($install_folder)) {	
						$install_folder_index = strpos($_SERVER['SCRIPT_NAME'], $install_folder);
						$script_name = '';
						
						if(empty($install_folder_index)) {
								$install_folder_index = 0;
						}
				}
				
				$script_name = '';
				$script_name .= substr($_SERVER['SCRIPT_NAME'], $install_folder_index + strlen($install_folder));
				$script_name = str_replace('.', '_', $script_name);
				$script_name = str_replace('/', '', $script_name);
				$script_name = strtolower($script_name);

				//get script parameters that define sub-scripts
				if(!empty($_GET['s'])) {
						$script_name .= '_' . urlencode(strtolower($_GET['s']));
				}

				$script_filename =  'data/languages/' . $this->langs[$this->default_language_id]['iso_code_2'] . '_' . $script_name . '.php';

				if(!file_exists($script_filename)) {
						$db = core()->get('db');
						$db->useInstance('systemdb');
						//TODO: set only the needed fields in the select - not longer the * (star symbol to load all)
						$db->setQuery('SELECT * FROM ' . $db->table('text_variables') . ' WHERE filename = :filename AND language_id = :language_id');
						$db->bind(':filename', $script_name);
						$db->bind(':language_id', $this->default_language_id);
						$result = $db->execute();
						
						//put the file contents here!!!
						$fp = fopen($script_filename, "w");
						fwrite($fp, '<?php' . "\n");
						
						while($result->next()) {
								fwrite($fp, 'define("' . $result->value('name') . '", \'' . $result->value('item_text') . '\');' . "\n");
						}
				
						fwrite($fp, '?>');
						fclose($fp);
				}

				if(file_exists($script_filename)) {
						require_once($script_filename);
				}
		}
		
		//////////////////////////////////////////////////////////////////////////////
		// Returns the current used language.
		//////////////////////////////////////////////////////////////////////////////
		public function getCurLangId() {
				return $this->default_language_id;
		}
		
		//////////////////////////////////////////////////////////////////////////////
		// Check if a languages id exists
		//////////////////////////////////////////////////////////////////////////////
		public static function languageIdExists($language_id) {
				// Load language				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT id FROM ' . $db->table('languages') . ' WHERE id = :id LIMIT 1');
				$db->bind(':id', (int)$language_id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(false === $tmp) {
						return false;
				}
				
				return true;
		}
}

?>