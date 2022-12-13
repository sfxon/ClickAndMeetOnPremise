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
class cSession extends cModule {		
		private $alive = true;
		private $timeout = 4200;
		private $debug = false;
		
		////////////////////////////////////////////////////////////////////////
		// Constructor.
		// Does some settings and installes the session handlers.
		////////////////////////////////////////////////////////////////////////
		function __construct() {
				if($this->debug == true) {
						echo '__construct';
				}
			
				$timeout = 0;
				$config = cConfig::loadIniFileAsArray('data/config/session.ini');
				
				if(isset($config['timeout'])) {
						$timeout = (int)$config['timeout'];
				}
				
				
				if(!empty($timeout)) {
						$this->timeout = $timeout;
				}
				
				$result = ini_set('session.gc_maxlifetime', $this->timeout);
				
				if(false === $result) {
						core()->addCoreError(7, 'Session lifetime could not be set (in cSession->__construct). Maybe you are not allowed to use ini_set? Session lifetime will be the servers default value.', '', 'warning');
				}
								
				$tmp = session_set_save_handler(
						array($this, 'open'),
						array($this, 'close'),
						array($this, 'read'),
						array($this, 'write'),
						array($this, 'destroy_me'),
						array($this, 'clean')
				);
				
				if(!$tmp) {
						die('Could not set session save handler. Check ini Setting session.save_handler - can you switch that to "user" please?');
				}
	 
				session_start();
				
				$this->checkLongTimeSession();
		}
		
		//////////////////////////////////////////////////////////////////////////
		// Check long time session for users.
		//////////////////////////////////////////////////////////////////////////
		public function checkLongTimeSession() {
				if(isset($_COOKIE['tellface_longtime'])) {
						$long_time_session = new cLongTimeSession();
					
						if(!isset($_SESSION['ws_user_id'])) {
								//$long_time_session->checkBrowser();			//if browser is not the same as the current, send mail to user!
								
								//Load cookie data from database.
								$cookie_data = $long_time_session->loadById($_COOKIE['tellface_longtime']);
								
								if(false === $cookie_data) {
										unset($_COOKIE['tellface_longtime']); 
    								setcookie('tellface_longtime', null, -1, '/'); 
										header('Location: ' . cCMS::loadTemplateUrl(core()->get('site_id')));
										die;
								}
						
								$_SESSION['ws_user_id'] = $cookie_data['user_id'];
						}
						
						//Token aktualisieren! Bei jedem Seitenaufruf wird die ID des Tokens aus Sicherheitsgründen geändert!
						//$token = $long_time_session->update($_COOKIE['tellface_longtime'], $_SESSION['ws_user_id']);
						//setcookie('tellface_longtime', $token, $long_time_session->getCookieLifetime());		//Parameter 3 is lifetime of the cookie, and set to 3 weeks right now..
				}
		}
		
		//////////////////////////////////////////////////////////////////////////
		// Desctruct.
		//////////////////////////////////////////////////////////////////////////
		function __destruct() {
				global $db;
				
				if($this->debug == true) {
						echo '__destruct';
				}
				
				if($this->alive) {
						session_write_close();
						$this->alive = false;
				}
		}
		
		/////////////////////////////////////////////////////////////////////////
		// Define where to set this module in the boot (hook) chain.
		/////////////////////////////////////////////////////////////////////////
		public static function setBootHooks() {
				core()->setBootHook('cSystemdb');
		}
		
		////////////////////////////////////////////////////////////////////////
		// This is executed when the system boots and the chain reached this module.
		////////////////////////////////////////////////////////////////////////
		public static function boot() {
				//Load the session!
				$session = new cSession();
				core()->set('session', $session);
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
								'module' => '/core/system/systemdb/cSystemdb'
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
		// Open
		//////////////////////////////////////////////////////////////////////////
		function open() {
				if($this->debug == true) {
						echo 'open';
				}
				
				return true;
		}
		
		//////////////////////////////////////////////////////////////////////////
		// Close
		//////////////////////////////////////////////////////////////////////////
		function close() {
				if($this->debug == true) {
						echo 'close';
				}
			
				return true;
		}
	 
	 	/////////////////////////////////////////////////////////////////////////
		// Delete
		/////////////////////////////////////////////////////////////////////////
		function delete() {
				if($this->debug == true) {
						echo 'delete';
				}
			
				if(ini_get('session.use_cookies')) {
						$params = session_get_cookie_params();
						setcookie(session_name(), '', time() - $this->timeout,
								$params['path'], $params['domain'],
								$params['secure'], $params['httponly']
						);
				}
		 
				session_destroy();
		 
				$this->alive = false;
				
				return true;
		}
		
		///////////////////////////////////////////////////////////////////////
		// Read
		///////////////////////////////////////////////////////////////////////
		private function read($sid) {
				if($this->debug == true) {
						echo 'read (' . $sid . ')';
				}
			
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT data FROM ' . $db->table('sessions') . ' WHERE id = :id LIMIT 1');
				$db->bind(':id', $sid);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(isset($tmp['data'])) {
						return $tmp['data'];
				}
				
				return '';
		}
		
		//////////////////////////////////////////////////////////////////////
		// Write
		//////////////////////////////////////////////////////////////////////
		public function write($sid, $data) {
				if($this->debug == true) {
						echo 'write(' . $sid . ')';
				}
				
				$destructable = true;
				
				$update = array(
						'id' => $sid,
						'data' => $data
				);
				
				if(isset($_SESSION['ws_user_id'])) {
						$update['account_id'] = $_SESSION['ws_user_id'];
				} else if(isset($_SESSION['user_id'])) {
						$update['account_id'] = $_SESSION['user_id'];
				}
				
				$core = core();
				
				if(NULL != $core) {						//Fix - if the project stops the main object maybe destroyed before this one..		
						$db = $core->get('db');
						
						if(NULL != $db) {					//Fix - if the project stops - the database object may be destroyed before this one..				
								$instance = $db->getInstance('systemdb');
								
								if(false != $instance) {
										$db->useInstance('systemdb');
										$db->setQuery('REPLACE INTO ' . $db->table('sessions') . $db->buildInsert($update));
										$db->bindValues($update);
										$result = $db->execute();
										//return $db->affectedRows();
										return true;
								} else {
										$destructable = false;
								}
						} else {
								$destructable = false;
						}
				} else {
						$destructable = false;
				}
				
				
				//////////////////
				// if the session is not desctructible because of desctructed objects - this is the fix!
				// try to connect to systemdb without using the core..
				//////////////////
				if(false === $destructable) {
						//Another destructor fix here: Get the configuration file.
						//The current working directory can be different in the constructor from the real path..
						//This is annoying, but we can probably fix it with this workaround.
						$realpath = realpath(dirname(__FILE__));
						$realpath = dirname($realpath);
						$realpath = dirname($realpath);
						$realpath = dirname($realpath);

						$config = cConfig::loadIniFileAsArray($realpath . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'systemdb.ini', array('dbmodule', 'instance', 'host', 'db', 'user', 'pw', 'prefix', 'table_quotes'));
				
						$db = new cDB();
						$db->initInstance($config['dbmodule'], $config['instance'], $config['prefix'], $config['table_quotes']);
						$db->connect($config['host'], $config['db'], $config['user'], $config['pw']);
						
						$db->useInstance('systemdb');
						$db->setQuery('REPLACE INTO ' . $db->table('sessions') . $db->buildInsert($update));
						$db->bindValues($update);
						$result = $db->execute();
						return true;
				}

				return false;
		}
		
		////////////////////////////////////////////////////////////////////
		// Destroy
		////////////////////////////////////////////////////////////////////
		public function destroy_me($sid) {
				if($this->debug == true) {
						echo 'destroy(' . $sid . ')';
				}
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('DELETE FROM ' . $db->table('sessions') . ' WHERE id = :id'); 
				$db->bind(':id', $sid);
				$result = $db->execute();
			
				$_SESSION = array();
				
				$affectedRows = $db->affectedRows();
				//return $db->affectedRows();
				return true;
		}
		
		///////////////////////////////////////////////////////////////////
		// Clean
		///////////////////////////////////////////////////////////////////
		private function clean($expire) {
				if($this->debug == true) {
						echo 'clean(' . $expire . ')';
				}
			
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('DELETE FROM ' . $db->table('sessions') . ' WHERE DATE_ADD(`last_accessed`, INTERVAL '.  (int)$expire . ' SECOND) < NOW()');
				$result = $db->execute();
	 
				//return $db->affectedRows();
				return true;
		}
		
		///////////////////////////////////////////////////////////////////
		// Session Tabelle aktualisieren -> User_id hinzufügen
		///////////////////////////////////////////////////////////////////
		public static function updateUserIdInSession($session_id, $account_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('sessions') . ' SET ' .
								'account_id = :account_id ' .
						'WHERE ' .
								'id = :session_id'			
				);
				$db->bind(':account_id', (int)$account_id);
				$db->bind(':session_id', $session_id);
				$db->execute();
		}
		
		///////////////////////////////////////////////////////////////////
		// Session Tabelle aktualisieren -> User_id hinzufügen
		///////////////////////////////////////////////////////////////////
		public static function destroySessionsByUserId($account_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'DELETE FROM ' . $db->table('sessions') . 'WHERE ' .
								'account_id = :account_id'			
				);
				$db->bind(':account_id', (int)$account_id);
				$result = $db->execute();
		}
}

?>