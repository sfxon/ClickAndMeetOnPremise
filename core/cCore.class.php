<?php

///////////////////////////////////////////////////////////////////////////////
// @author	Steve Kraemer
// @address	
// @created	2015-12-12
// @info		This is the core of the application. It separates the project
//					into boot and input, processing and output (IPO).
// @license	All rights reserved.
//////////////////////////////////////////////////////////////////////////////

require_once('lib/cModule.class.php');

//////////////////////////////////////////////////////////////////////////////
// This function is a global function that is used to get the pointer
// to the main instance.
// With this pointer, one can use all data (get/set)
//////////////////////////////////////////////////////////////////////////////
function core() {
		global $cCore;
		return $cCore;
}


class cCore {
		var $boot_chain;		//use the boot chain, to load very basic elements.
		var $chain;
		var $coreErrors;
		var $data;
		var $modules;
		var $modulesDependencies;
		var $chainClassInstances;
		
		/////////////////////////////////////////////////////////////////////////
		// @info		Constructor
		/////////////////////////////////////////////////////////////////////////
		public function __construct() {
				$this->boot_chain = new SplDoublyLinkedList();
				$this->chain = new SplDoublyLinkedList();
				$this->coreErrors = array();
				$this->data = array();
				$this->modules = array();
				$this->modulesDependencies = array();
		}
		
		/////////////////////////////////////////////////////////////////////////
		// Destructor
		/////////////////////////////////////////////////////////////////////////
		function __destruct() {
		}
		
		/////////////////////////////////////////////////////////////////////////
		// @info		This function is the main function of the program.
		/////////////////////////////////////////////////////////////////////////
		public function run() {
				global $measure_begin;
			
				//load all modules
				$this->loadModules();
				
				/*
				$measure_end = microtime(true);
				$difference = $measure_end - $measure_begin;
				echo 'after loadModules:';
				var_dump($difference);
				*/
				
				$this->checkModules();
				
				/*
				$measure_end = microtime(true);
				$difference = $measure_end - $measure_begin;
				echo 'after checkModules:';
				var_dump($difference);
				*/
				
				$this->boot();
				
				/*
				$measure_end = microtime(true);
				$difference = $measure_end - $measure_begin;
				echo 'after boot:';
				var_dump($difference);
				*/
				
				$this->buildModuleExecutionList();
				
				/*
				$measure_end = microtime(true);
				$difference = $measure_end - $measure_begin;
				echo 'after buldModuleExecutionList:';
				var_dump($difference);
				*/
				
				$this->executeModules();
				
				/*
				$measure_end = microtime(true);
				$difference = $measure_end - $measure_begin;
				echo 'after execution:';
				var_dump($difference);
				*/
		}
		
		///////////////////////////////////////////////////////////////////////
		// Builds up the boot chain and executes it.
		///////////////////////////////////////////////////////////////////////
		private function boot() {
				do {
						//count modules in execution list
						$old_module_count = $this->boot_chain->count();
					
						foreach($this->modules as $module) {
								$module['class']::setBootHooks();
						}//count new number of modules in execution list.
						$new_module_count = $this->boot_chain->count();
				} while ($old_module_count != $new_module_count);
				
				foreach($this->boot_chain as $boot_module) {
						$boot_module['class']::boot();	
				}
		}
		
		///////////////////////////////////////////////////////////////////////
		// Sets a boot hook.
		///////////////////////////////////////////////////////////////////////
		public function setBootHook($parent_class, $add_before = false) {
				//-- get information about the class that called this function --
				$calling_class = NULL;
				
				//get the trace
				$trace = debug_backtrace();
		
				// Get the class that is asking for who awoke it
				$calling_class = $trace[1]['class'];

				return $this->insertBootHookInChain($parent_class, $calling_class, $add_before);
		}
		
		///////////////////////////////////////////////////////////////////////
		// Insert a hook in the chain.
		// @params
		//		$parent_class				Klasse vor oder nach der diese Hook installiert wird.
		//		$new_class					Klassenname der Klasse, welche sich einklinkt.
		//		$new_hook						Funktionsname der Funktion der Klasse die sich einklinkt.
		///////////////////////////////////////////////////////////////////////
		private function insertBootHookInChain($parent_class, $new_class, $add_before) {
				//try to find the entry with class and method where this hook is to be entered
				$this->boot_chain->rewind();
				
				//If there is no entry - init this with the boot class. The boot class is the most basic class and does almost nothing, but being a basic anchor.
				if(!$this->boot_chain->valid()) {
						$this->boot_chain->push(
								array(
										'class' => 'cBoot'
								)
						);
						
						$this->boot_chain->rewind();
				}
				
				//Check if this entry already exists in the boot chain
				while($this->boot_chain->valid()) {
						$tmp = $this->boot_chain->current();

						if($tmp['class'] == $new_class) {
								return;		//Do not add an already added one
						}
						
						$this->boot_chain->next();//switch to next list item
				}
				
				$this->boot_chain->rewind();
				
				
				
				// and now - process
				while($this->boot_chain->valid()){
						$tmp = $this->boot_chain->current();
						
						if($tmp['class'] == $parent_class) {
								if($add_before === true) {								
										//Add the new hook before the original hook										
										$key = $this->boot_chain->key();
										$this->boot_chain->add(
												$key, array(
														'class' => $new_class
												)
										);
										break;		//Leave the while loop
								} else {
										//Add the hook after the original hook
										$this->boot_chain->next();
										
										//check if this key is valid.
										if($this->boot_chain->valid()) {
												//Add the new hook before the original hook										
												$key = $this->boot_chain->key();
												$this->boot_chain->add(
														$key, array(
																'class' => $new_class
														)
												);
										} else {
												//Adds a hook at the absolute end of the tree..
												//Add the new hook before the original hook
												$this->boot_chain->push(
														array(
																'class' => $new_class
														)
												);
										}
										
										break;		//Leave the while loop.
								}
						}
						
						$this->boot_chain->next();//switch to next list item
				}
		}
		
		///////////////////////////////////////////////////////////////////////
		// @info		load all modules
		///////////////////////////////////////////////////////////////////////
		private function loadModules() {
				/*
				global $measure_begin;
				
				echo '<hr />LoadModules:<br />';
				*/
				
				//Recursive Directory Iteration
				//We try to load all files with the name module.class.php
				//This are the entry points for modules
				$directory = new RecursiveDirectoryIterator('modules/', RecursiveDirectoryIterator::KEY_AS_FILENAME | RecursiveDirectoryIterator::CURRENT_AS_FILEINFO);
				
				$files = new RegexIterator(
						new RecursiveIteratorIterator($directory),
						'#module\.class\.php$#',
						RegexIterator::MATCH,
						RegexIterator::USE_KEY
				);
				
				
				$defined_classes = get_declared_classes();
				$working_directory = getcwd();
				$working_directory = str_replace('\\', '/', $working_directory);		//This happens on windows platforms. So change it to only one way directory separators.
				
				foreach($files as $file) {
						/*
						$measure_end = microtime(true);
						$difference = $measure_end - $measure_begin;
						echo 'file: ' . $file . '';
						var_dump($difference);
						echo '<br />';
						*/
						
						//process the files
						// each class contains a register function
						// the register function builds the dependency tree.
						// the dependency tree is responsible for the order of the execution of the functions..
						// (we have a base path: input, process and output (IPO system))
						// these are separated in basic sub-functions with a fixed sort order.
						// You can use "hook" the modules functions before or after one of the sub-functions.
						// You can also create own "hook" points.
						
						// You can imagine the processing as execution cycles - with sub-cycles.
						//var_dump($file);
						require_once($file);
						
						$new_classes = get_declared_classes();
						$classes_in_file = array_diff($new_classes, $defined_classes);
						$defined_classes = $new_classes;
						
						//Now check the classes. If they are derived by cModule, they are used as module classes.
						foreach($classes_in_file as $class_name) {								
								$class = new ReflectionClass($class_name);		//Loads information about a class, without instanciating it
								
        				$parent = $class->getParentClass();
								
								if(false !== $parent) {								
										$parent_name = $parent->getName();
										
										$path = $class->getFileName();
										
										$class_filename = basename($path);
										
										$path = str_replace('\\', '/', $path);		//This happens on windows platforms. So change it to only one way directory separators.
										$path = str_replace($working_directory .  '/modules' , '', $path);		//Remove Directory including modules directory
										$path = str_replace( $class_filename, '', $path);		//remove filename of the class
												
										//if($parent_name === 'cModule') {
												//get all dependencies of this module
												$this->modules[] = array(
														'class' => $class_name,
														'path' => $path . $class_name
												);
										//}
								}
						}
				}
		}
		
		///////////////////////////////////////////////////////////////////////
		// Checks all modules.
		///////////////////////////////////////////////////////////////////////
		private function checkModules() {
				foreach($this->modules as $module) {
						//get all dependencies, this module has
						$new_dependencies = $module['class']::getDependenciesAsArray();
						
						//process dependencies ->
						foreach($new_dependencies as $dependency) {
								//1. insert paths in dependency array
								$dependency['required_by'] = $module['path'];
								$this->modulesDependencies[$dependency['module']][] = $dependency;
						}
				}
				
				$this->checkModulesDependencies();
		}
		
		///////////////////////////////////////////////////////////////////////
		// Get's all hooks and sets them in an order.
		///////////////////////////////////////////////////////////////////////
		private function buildModuleExecutionList() {
				//set the main hooks: configure, init, input, process, output, end
				$this->setCoreHook('init');
				$this->setCoreHook('input');
				$this->setCoreHook('process');
				$this->setCoreHook('render');
				$this->setCoreHook('output');
				$this->setCoreHook('end');
				
				//Load core modules hooks
				$this->buildCoreHooks();
				
				//we try to load the main class over the passed parameter s. 
				//If there is no main class given - use the cms class.
				$main_class = $this->getMainClassPath();
				$main_class::setExecutionalHooks();
				
				//Build all advanced modules hooks.
				$this->buildAdditionalHooks();
		}
		
		///////////////////////////////////////////////////////////////////////
		// Execute the modules - Walks through the Chain!
		///////////////////////////////////////////////////////////////////////
		private function executeModules() {
				$this->chain->rewind();
				
				$required_classes = array();

				//get all required classes
				while($this->chain->valid()){
						$tmp =  $this->chain->current();
						
						$required_classes[$tmp['class']] = $tmp['class'];
						
						$this->chain->next();
				}
				
				//instanciate all required classes
				foreach($required_classes as $class) {
						$this->chainClassInstances[$class] = new $class();
				}
				
				//execute
				$this->chain->rewind();
				
				while($this->chain->valid()) {
						$tmp = $this->chain->current();
						
						$tmp_class = $tmp['class'];
						$tmp_hook = $tmp['hook'];
						$tmp_chain_class_instance = $this->chainClassInstances[$tmp_class];
						
						//not sure if we leave it like this..
						//maybe we output an error in the future, if a function cannot be found.
						if(method_exists($tmp_chain_class_instance, $tmp_hook)) {
								$tmp_chain_class_instance->$tmp_hook();		//execution of the class method
						}
						
						$this->chain->next();
				}
		}
		
		///////////////////////////////////////////////////////////////////////
		// This function builds all additional hooks.
		// See definition of function cModule::setAdditionalHooks in file
		// core/lib/cModule.class.php for brief description,
		// how this system works.
		///////////////////////////////////////////////////////////////////////
		function buildAdditionalHooks() {
				do {
						//count modules in execution list
						$old_module_count = $this->chain->count();
					
						foreach($this->modules as $module) { 
								$module['class']::setAdditionalHooks();
						}
						
						//count new number of modules in execution list.
						$new_module_count = $this->chain->count();
				} while ($old_module_count != $new_module_count);
				
				//echo 'end';
		}
		
		///////////////////////////////////////////////////////////////////////
		// This function builds all core hooks.
		///////////////////////////////////////////////////////////////////////
		function buildCoreHooks() {
				//check if this class is availlable
				foreach($this->modules as $module) {
						$module['class']::setCoreHooks();
				}
		}

		
		///////////////////////////////////////////////////////////////////////
		// Load the main class.
		///////////////////////////////////////////////////////////////////////
		public function getMainClassPath() {
				$main_class = 'cCMS';
				
				if(isset($_GET['s'])) {
						$tmp_class = $_GET['s'];
						
						//check if this class is availlable
						foreach($this->modules as $module) {
								if($module['class'] == $tmp_class) {
										$main_class = $tmp_class;
								}
						}
						
						//TODO - we should check if this was successful - or set an error instead.
				}
				
				return $main_class;
		}
		
		///////////////////////////////////////////////////////////////////////
		// Set core hooks. This are needed, to add other hooks.
		///////////////////////////////////////////////////////////////////////
		private function setCoreHook($name) {
				$this->chain->push(
						array(
								'hook' => $name,
								'class' => 'cCore'
						)
				);
		}
		
		///////////////////////////////////////////////////////////////////////
		// Set a hook point in the module execution list.
		///////////////////////////////////////////////////////////////////////
		public function setHook($hook_path, $new_hook, $add_before = false) {
				//-- get information about the class that called this function --
				$calling_class = NULL;
				
				//get the trace
				$trace = debug_backtrace();
		
				// Get the class that is asking for who awoke it
				$calling_class = $trace[1]['class'];
				
				$hook_path_parts = explode('|', $hook_path);
				
				if(count($hook_path_parts) != 2) {
						return false;
				}
				
				//Check that this hook isn't already installed
				if(true === $this->isHookInstalled($calling_class, $new_hook)) {
						return false;
				}
				
				$hook_path_class = $hook_path_parts[0];
				$hook_path_method = $hook_path_parts[1];
				
				//TODO: Here we could do a check, to see if this hook is already defined.
				
				return $this->insertHookInChain($hook_path_class, $hook_path_method, $calling_class, $new_hook, $add_before);
		}
		
		///////////////////////////////////////////////////////////////////////
		// Checks if a hook is installed in the chain.
		///////////////////////////////////////////////////////////////////////
		function isHookInstalled($class, $method) {
				//try to find the entry with class and method where this hook is to be entered
				$this->chain->rewind();

				while($this->chain->valid()){
						$tmp =  $this->chain->current();
						
						if($tmp['class'] == $class && $tmp['hook'] == $method) {
								return true;
						}
						
						$this->chain->next();
				}
				
				return false;
		}
		
		///////////////////////////////////////////////////////////////////////
		// Insert a hook in the chain.
		// @params
		//		$hook_path_class		Klasse,in welche eingeklinkt werden soll.
		//		$hook_path_method		Funktion der Klasse, in welche eingklingt werden soll.
		//		$new_class					Klassenname der Klasse, welche sich einklinkt.
		//		$new_hook						Funktionsname der Funktion der Klasse die sich einklinkt.
		///////////////////////////////////////////////////////////////////////
		private function insertHookInChain($hook_path_class, $hook_path_method, $new_class, $new_hook, $add_before) {
				//try to find the entry with class and method where this hook is to be entered
				$this->chain->rewind();

				while($this->chain->valid()){
						$tmp =  $this->chain->current();
						
						if($tmp['class'] == $hook_path_class && $tmp['hook'] == $hook_path_method) {
								if($add_before === true) {								
										//Add the new hook before the original hook										
										$key = $this->chain->key();
										$this->chain->add(
												$key, array(
														'hook' => $new_hook,
														'class' => $new_class
												)
										);
										break;		//Leave the while loop
								} else {
										//Add the hook after the original hook
										$this->chain->next();
										
										//check if this key is valid.
										if($this->chain->valid()) {
												//Add the new hook before the original hook										
												$key = $this->chain->key();
												$this->chain->add(
														$key, array(
																'hook' => $new_hook,
																'class' => $new_class
														)
												);
										} else {
												//Adds a hook at the absolute end of the tree..
												//Add the new hook before the original hook
												$this->chain->push(
														array(
																'hook' => $new_hook,
																'class' => $new_class
														)
												);
										}
										
										break;		//Leave the while loop.
								}
						}
						
						$this->chain->next();//switch to next list item
				}
		}
		
		///////////////////////////////////////////////////////////////////////
		// @info		Check the dependency arrays..
		///////////////////////////////////////////////////////////////////////
		private function checkModulesDependencies() {
				//This sorts all modules in the way they depend on one another
				$sorted_modules = array();
				
				foreach($this->modules as $modules_index => $module) {
						if(isset($this->modulesDependencies[$module['path']])) {
								//Check currents modules version against all required versions
								$module_version = $module['class']::getVersion();
								
								foreach($this->modulesDependencies[$module['path']] as $dependency_index => $dependency) {
										if(isset($dependency['version'])) {
												if($dependency['version'] > $module_version) {														
														$this->coreErrors[] = array(
																'error_code' => 1,
																'error_message' => 'A module requires a higher version of another module.',
																'additional_data' => array(
																		'module' => $dependency['required_by'],
																		'module_version' => $module_version,
																		'needed_module' => $dependency['module'],
																		'needed_version' => $dependency['version']
																)
														);
														
														$this->coreError();
														die;		//It dies in core error either - this is only to make things clear - no processing if a needed library is missing!
												} else {
														//unset - version is not needed..
														unset($this->modulesDependencies[$module['path']][$dependency_index]);
												}
										} else {		//END if(isset($dependency['version'])) {
												//unset - version is not needed..
												unset($this->modulesDependencies[$module['path']][$dependency_index]);
										}
								}
						}
				}
				
				//clean all empty parts of modulesDependencies
				if(is_array($this->modulesDependencies)) {				
						foreach($this->modulesDependencies as $index => $dependency) {
								if(!is_array($dependency) || 0 === count($dependency)) {
										unset($this->modulesDependencies[$index]);
								}
						}
				}
				
				//Check modules that still exist in the dependencies array.
				//They were not found.. (This could lead to an error..)
				if(0 !== count($this->modulesDependencies)) {
						foreach($this->modulesDependencies as $module) {	
								foreach($module as $dependency) {					
										$this->coreErrors[] = array(
												'error_code' => 2,
												'error_message' => 'A required module was not found!',
												'additional_data' => array(
														'module' => $dependency['required_by'],
														'module_version' => 'module not found',
														'needed_module' => $dependency['module'],
														'needed_version' => $dependency['version']
												)
										);
								}
						}
						
						$this->coreError();
						die;		//It dies in core error either - this is only to make things clear - no processing if a needed library is missing!
				}
		}
		
		///////////////////////////////////////////////////////////////////////
		// Outputs all error messages in the stack and then dies.	
		///////////////////////////////////////////////////////////////////////
		public function coreError() {
				foreach($this->coreErrors as $error) {
						echo '<hr />';
						echo 'Error (Code ' . $error['error_code'] . '): ' . $error['error_message'];
						echo '<br />';
						echo '<pre>';
						var_dump($error['additional_data']);
						echo '</pre>';
				}
				
				die;
		}
		
		/////////////////////////////////////////////////////////////////////
		// Adds an error to the core error stack.
		/////////////////////////////////////////////////////////////////////
		public function addCoreError($error_code, $error_message, $additional_data, $level = 'error') {
				$this->coreErrors[] = array(
						'error_code' => $error_code,
						'error_message' => $error_message,
						'additional_data' => $additional_data,
						'level' => $level
				);
		}
		
		/////////////////////////////////////////////////////////////////////
		// Adds data for later use.
		/////////////////////////////////////////////////////////////////////
		public function set($identifier, $data) {
				$this->data[$identifier] = $data;
		}
		
		/////////////////////////////////////////////////////////////////////
		// Gets data for later use.
		/////////////////////////////////////////////////////////////////////
		public function get($identifier) {
				if(isset($this->data[$identifier])) {
						return $this->data[$identifier];
				}
				
				return NULL;
		}
		
		/////////////////////////////////////////////////////////////////////
		// Gets a instanciated chains modules instance..
		/////////////////////////////////////////////////////////////////////
		public function getInstance($module) {
				if(!isset($this->chainClassInstances[$module])) {
						return false;
				}
				
				return $this->chainClassInstances[$module];
		}
		
		////////////////////////////////////////////////////////////////////
		// Get a get variable.
		// This function is so basic, that we decided to put it into the core.
		////////////////////////////////////////////////////////////////////
		function getGetVar($index) {
				if(isset($_GET[$index])) {
						return $_GET[$index];
				}
				
				return NULL;
		}
		
		////////////////////////////////////////////////////////////////////
		// Get a get variable.
		// This function is so basic, that we decided to put it into the core.
		////////////////////////////////////////////////////////////////////
		function getPostVar($index) {
				if(isset($_POST[$index])) {
						return $_POST[$index];
				}
				
				return NULL;
		}
		
		////////////////////////////////////////////////////////////////////
		// Get all posts..
		// This function is so basic, that we decided to put it into the core.
		////////////////////////////////////////////////////////////////////
		function getAllPosts() {
				return $_POST;
		}
		
		function getAllGets() {
				return $_GET;
		}
		
		///////////////////////////////////////////////////////////////////
		// Get domain without anything around it..
		// Thanks to 
		//		antitoxic on stackoverflow
		// Who provided this peace of code in this discussion:
		//		http://stackoverflow.com/questions/1459739/php-serverhttp-host-vs-serverserver-name-am-i-understanding-the-ma
		// TODO:
		//		We might want to parse this in the beginning of core in
		//		the future. This code can be costly in execution time,
		//		so there should be a buffered (prepared) variable
		// 		for this..
		///////////////////////////////////////////////////////////////////
		public static function getCurrentDomain() {
				$possibleHostSources = array('HTTP_X_FORWARDED_HOST', 'HTTP_HOST', 'SERVER_NAME', 'SERVER_ADDR');
				$sourceTransformations = array(
						"HTTP_X_FORWARDED_HOST" => function($value) {
								$elements = explode(',', $value);
								return trim(end($elements));
						}
				);
				$host = '';
				foreach ($possibleHostSources as $source)
				{
						if (!empty($host)) break;
						if (empty($_SERVER[$source])) continue;
						$host = $_SERVER[$source];
						if (array_key_exists($source, $sourceTransformations))
						{
								$host = $sourceTransformations[$source]($host);
						} 
				}
		
				// Remove port number from host
				$host = preg_replace('/:\d+$/', '', $host);
		
				return trim($host);
		}
}


?>