<?php

/////////////////////////////////////////////////////////////////////////////////
// The systems database initialisation.
// We use this database information for all our system modules.
// In this database we save things like session_data, languages_data, ...
// It is very essential for all other modules.
/////////////////////////////////////////////////////////////////////////////////
//
// TODO: Check if it would be a better idea, to define a class variable
// for the database instance. So one could use another instance than the systems one.
//
/////////////////////////////////////////////////////////////////////////////////
class cRenderer extends cModule {
		var $parts = array();
		
		var $Smarty;
		var $smarty_template_dir;
		var $smarty_compile_dir;
		var $smarty_config_dir;
		var $smarty_cache_dir;
		
		var $template_files_overwrites = array();
		
		var $templateName;
		
		////////////////////////////////////////////////////////////////////////
		// Constructor.
		// Does some settings and installes the session handlers.
		////////////////////////////////////////////////////////////////////////
		function __construct() {
				$this->templateName = '';	
				$this->smarty_template_dir = 'data/templates/';			
				
				$this->startSmarty();
		}
		
		////////////////////////////////////////////////////////////////////////
		// Desctruct.
		////////////////////////////////////////////////////////////////////////
		function __destruct() {
		}
		
		/////////////////////////////////////////////////////////////////////////
		// StartSmarty
		/////////////////////////////////////////////////////////////////////////
		function startSmarty() {
				//$smarty_dir = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'smarty' . DIRECTORY_SEPARATOR;
				$smarty_dir = 'data/vendor/smarty/';
				require($smarty_dir . 'Smarty.class.php');
				
				$this->Smarty = new Smarty();
				$this->Smarty->template_dir 	= $smarty_dir . 'templates';
				$this->Smarty->compile_dir   	= 'data/tmp/smarty/templates_c';
				$this->Smarty->config_dir    	= $smarty_dir . 'config';
				$this->Smarty->cache_dir     	= $smarty_dir . 'cache';
				
				$this->Smarty->caching				= false;	//Disable caching as long as we develop.
		}
		
		//////////////////////////////////////////////////////////////////////////
		// Set core hooks. This hooks are executed always!
		//////////////////////////////////////////////////////////////////////////
		public static function setCoreHooks() {
				core()->setHook('cCore|render', 'header');
				core()->setHook('cRenderer|header', 'begin_page');
				core()->setHook('cRenderer|begin_page', 'header_bar');
				core()->setHook('cRenderer|header_bar', 'content');
				core()->setHook('cRenderer|content', 'footer');
				core()->setHook('cRenderer|footer', 'end_page');
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
								'module' => '/core/lang/cLang'
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
		// Set the current to use template folder.
		//////////////////////////////////////////////////////////////////////////
		public function setTemplate($template_name) {
				$this->templateName = $template_name;
		}
		
		//////////////////////////////////////////////////////////////////////////
		// Renders one site. Adds the result to the end of the output queue.
		//////////////////////////////////////////////////////////////////////////
		public function render($template_file) {
				$output = $this->Smarty->fetch($this->smarty_template_dir . $this->templateName . '/' . $template_file);
				$this->parts[] = $output;
		}
		
		//////////////////////////////////////////////////////////////////////////
		// Renders one site. Adds the result to the end of the output queue.
		//////////////////////////////////////////////////////////////////////////
		public function fetchFreeFile($template_file) {
				$output = $this->Smarty->fetch($template_file);
				return $output;
		}
		
		//////////////////////////////////////////////////////////////////////////
		// Renders one site. Adds the result to the end of the output queue.
		//////////////////////////////////////////////////////////////////////////
		public function renderText($text) {
				$this->parts[] = $text;
		}
		
		//////////////////////////////////////////////////////////////////////////
		// Renders one site, but returns the result.
		//////////////////////////////////////////////////////////////////////////
		public function fetch($template_file) {
				$render_string = $this->smarty_template_dir . $this->templateName . '/' . $template_file;
			
				//build extend tree...
				if(isset($this->template_files_overwrites[$template_file])) {
						$render_string = 'extends:' . $render_string;
						
						foreach($this->template_files_overwrites[$template_file] as $file) {
								$render_string .= '|' . $file;
						}
				}
				
				$output = $this->Smarty->fetch($render_string);

				return $output;
		}
		
		//////////////////////////////////////////////////////////////////////////
		// Renders an input string, but returns the result.
		//////////////////////////////////////////////////////////////////////////
		public function fetchFromString($input) {
				$output = $this->Smarty->fetch('string:' . $input);
				return $output;
		}
		
		//////////////////////////////////////////////////////////////////////////
		// Outputs all contents in parts to the default device.
		//////////////////////////////////////////////////////////////////////////
		public function display() {
				foreach($this->parts as $part) {
						echo $part;
				}
		}
		
		/////////////////////////////////////////////////////////////////////////
		// Assign a variable to the template.
		/////////////////////////////////////////////////////////////////////////
		public function assign($variable_name, $value) {
				$this->Smarty->assign($variable_name, $value);
		}
		
		/////////////////////////////////////////////////////////////////////////
		// Set smarty template dir.
		/////////////////////////////////////////////////////////////////////////
		public function setTemplatePath($path) {
				$this->smarty_template_dir = $path;
		}
		
		/////////////////////////////////////////////////////////////////////////
		// Get smarty template dir.
		/////////////////////////////////////////////////////////////////////////
		public function getTemplatePath() {
				return $this->smarty_template_dir;
		}
		
		/////////////////////////////////////////////////////////////////////////////
		// Register a template file that extends the original set template file.
		// Template files are ordered by name, and then -
		// in another dimension by an numeric index, so they can be ordered.
		/////////////////////////////////////////////////////////////////////////////
		public function registerTemplateFile($file, $extension_file, $index = 0) {
				if($index === 0) {
						//get highest overwrite index and add + 100 to add another one.
						if(isset($this->template_files_overwrites[$file])) {
								end($this->template_files_overwrites[$file]);         // move the internal pointer to the end of the array
								$key = key($this->template_files_overwrites[$file]);
								reset($this->template_files_overwrites[$file]);
								
								$index = $key + 10;
						} else {
								$index = 10;
						}
				}
			
				$this->template_files_overwrites[$file][$index] = $extension_file;
				
				ksort($this->template_files_overwrites[$file]);
		}
}

?>