<?php

require_once('dbresult.class.php');

/////////////////////////////////////////////////////////////////////////////////
// Abstract Database Handler, that can handle all kinds of databases.
// Uses it's own functions to make database handling abstract,
// and to fasten up writing database queries.
/////////////////////////////////////////////////////////////////////////////////
class cDB extends cModule {
		var $db_instances;					//Array of Instances of active database connections.
		var $last_used_instance;		//The last used database instance.
		
		/////////////////////////////////////////////////////////////////////////////
		// Init variables.
		/////////////////////////////////////////////////////////////////////////////
		public function __construct() {
				$this->db_instances = array();
				$this->last_used_instance = '';
		}
		
		/////////////////////////////////////////////////////////////////////////////
		// Initiates a new instance. This has to be done, bevore connecting to the database.
		// Here you set an instance name, and a module name.
		// The instance name is used, to select the database you want to operate on.
		// So it is possible, to hold multiple database connections in one module.
		/////////////////////////////////////////////////////////////////////////////
		public function initInstance($db_modules_name, $instance_name, $prefix = '', $table_quotes = '') {
				//Check if the instance already exists. If so: die. User would have to close the instance, before instanciating with the same name. This will make it easier to find errors,
				//because so it is not possible to override an instance by accident.
				if(isset($this->db_instances[$instance_name])) {
						core()->addCoreError(4, 'There is already a database instance with this instance name. (cDB->initInstance)', print_r($instance_name, true));
						core()->coreError();
						die;		//It already die's in coreError, but this is to make things clear!
				}
				
				//Try to load the database module and instanciate it.
				$b_module_exists = class_exists($db_modules_name);
				
				if(false === $b_module_exists) {
						//Fehler: System-Modul existiert nicht.
						core()->addCoreError(3, 'Database Module has not been found. (cDB->initInstance)', 'Tried to load module "' . print_r($db_modules_name, true) . '" as an instance with the instance-name "' . print_r($instance_name, true) . '".' );
						core()->coreError();
						die;		//It already die's in coreError, but this is to make things clear!
				}
				
				$module_instance = array();
				$module_instance['instance_name'] = $instance_name;
				$module_instance['instance'] = new $db_modules_name();
				$module_instance['connected'] = false;
				$module_instance['prefix'] = $prefix;
				$module_instance['table_quotes'] = $table_quotes;
				
				//Save instance under it's new name.
				$this->db_instances[$instance_name] = $module_instance;
				
				$this->last_used_instance = $instance_name;
		}
		
		/////////////////////////////////////////////////////////////////////////////
		// Set instance to used (e.g. that was last used)
		/////////////////////////////////////////////////////////////////////////////
		public function useInstance($instance_name) {
				//Check if instance exists
				if(!isset($this->db_instances[$instance_name])) {
						core()->addCoreError(5, 'No database instance with this name has been found (cDB->useInstance). See additional info for instance name: ', print_r($instance_name, true));
						core()->coreError();
						die;		//It already die's in coreError, but this is to make things clear!
				}
				
				$this->last_used_instance = $instance_name;
		}
		
		/////////////////////////////////////////////////////////////////////////////
		// Gets the instance.
		/////////////////////////////////////////////////////////////////////////////
		public function getInstance($instance_name) {
				if(!isset($this->db_instances[$instance_name])) {
						return false;
				}
				
				return $this->db_instances[$instance_name];
		}
		
		/////////////////////////////////////////////////////////////////////////////
		// Connect to the database.
		/////////////////////////////////////////////////////////////////////////////
		public function connect($host, $database, $user, $password, $instance_name = '') {
				//Check if we got a last used instance
				if($instance_name == '') {
						$instance_name = $this->last_used_instance;
				}
				
				//Check if instance exists
				if(!isset($this->db_instances[$instance_name])) {
						core()->addCoreError(5, 'No database instance with this name has been found (cDB->connect). See additional info for instance name: ', print_r($instance_name, true));
						core()->coreError();
						die;		//It already die's in coreError, but this is to make things clear!
				}
				
				//Try to connect to the database.
				$result = $this->db_instances[$instance_name]['instance']->connect($host, $database, $user, $password);
				
				if(false === $result) {
						core()->addCoreError(6, 'Connection attempt to database failed. (cDB->connect). See additional info for instance name:', print_r($instance_name, true));
						core()->coreError();
						die;		//It already die's in coreError, but this is to make things clear!
				}
		}
		
		/////////////////////////////////////////////////////////////////////////
		// Get an escaped and prefixed table name.
		/////////////////////////////////////////////////////////////////////////
		public function table($table_name, $instance_name = '') {
				//Check if we got a last used instance
				if($instance_name == '') {
						$instance_name = $this->last_used_instance;
				}
				
				//Check if instance exists
				if(!isset($this->db_instances[$instance_name])) {
						core()->addCoreError(5, 'No database instance with this name has been found (cDB->table). See additional info for instance name: ', print_r($instance_name, true));
						core()->coreError();
						die;		//It already die's in coreError, but this is to make things clear!
				}
				
				$table_name = $this->db_instances[$instance_name]['prefix'] . $table_name;		//Set prefix
				
				//Quote the table
				$quotes = $this->db_instances[$instance_name]['table_quotes'];
				
				$table_name = $quotes . $table_name . $quotes;
				
				return $table_name;
		}
		
		//////////////////////////////////////////////////////////////////////
		// Set a database query.
		// This is done in preparation for later execution of the query.
		//////////////////////////////////////////////////////////////////////
		public function setQuery($query, $instance_name = '') {
				//Check if we got a last used instance
				if($instance_name == '') {
						$instance_name = $this->last_used_instance;
				}
				
				//Check if instance exists
				if(!isset($this->db_instances[$instance_name])) {
						core()->addCoreError(5, 'No database instance with this name has been found (cDB->setQuery). See additional info for instance name: ', print_r($instance_name, true));
						core()->coreError();
						die;		//It already die's in coreError, but this is to make things clear!
				}
				
				$this->db_instances[$instance_name]['instance']->setQuery($query);
		}
		
		////////////////////////////////////////////////////////////////////
		// Parameter binding for a set database query.
		////////////////////////////////////////////////////////////////////
		public function bind($key, $value, $instance_name = '') {
				//Check if we got a last used instance
				if($instance_name == '') {
						$instance_name = $this->last_used_instance;
				}
				
				//Check if instance exists
				if(!isset($this->db_instances[$instance_name])) {
						core()->addCoreError(5, 'No database instance with this name has been found (cDB->bind). See additional info for instance name: ', print_r($instance_name, true));
						core()->coreError();
						die;		//It already die's in coreError, but this is to make things clear!
				}
				
				$this->db_instances[$instance_name]['instance']->bind($key, $value);
		}
		
		//////////////////////////////////////////////////////////////////
		// Execute a query. (Returns an result object!)
		//////////////////////////////////////////////////////////////////
		public function execute($instance_name = '') {
				//Check if we got a last used instance
				if($instance_name == '') {
						$instance_name = $this->last_used_instance;
				}
				
				//Check if instance exists
				if(!isset($this->db_instances[$instance_name])) {
						core()->addCoreError(5, 'No database instance with this name has been found (cDB->execute). See additional info for instance name: ', print_r($instance_name, true));
						core()->coreError();
						die;		//It already die's in coreError, but this is to make things clear!
				}
				
				return $this->db_instances[$instance_name]['instance']->execute();
		}
		
		////////////////////////////////////////////////////////////////
		// Binds an array of values.
		////////////////////////////////////////////////////////////////
		public function bindValues($array, $instance_name = '') {
				//Check if we got a last used instance
				if($instance_name == '') {
						$instance_name = $this->last_used_instance;
				}
				
				//Check if instance exists
				if(!isset($this->db_instances[$instance_name])) {
						core()->addCoreError(5, 'No database instance with this name has been found (cDB->bindValues). See additional info for instance name: ', print_r($instance_name, true));
						core()->coreError();
						die;		//It already die's in coreError, but this is to make things clear!
				}
				
				$this->db_instances[$instance_name]['instance']->setParamsFromArray($array);
		}
		
		/////////////////////////////////////////////////////////////
		// Prepare an update query.
		/////////////////////////////////////////////////////////////
		public function buildUpdate($params, $instance_name = '') {
				//Check if we got a last used instance
				if($instance_name == '') {
						$instance_name = $this->last_used_instance;
				}
				
				//Check if instance exists
				if(!isset($this->db_instances[$instance_name])) {
						core()->addCoreError(5, 'No database instance with this name has been found (cDB->buildUpdate). See additional info for instance name: ', print_r($instance_name, true));
						core()->coreError();
						die;		//It already die's in coreError, but this is to make things clear!
				}
				
				return $this->db_instances[$instance_name]['instance']->buildUpdate($params);
		}
		
		/////////////////////////////////////////////////////////////
		// Prepare an insert query.
		/////////////////////////////////////////////////////////////
		public function buildInsert($params, $instance_name = '') {
				//Check if we got a last used instance
				if($instance_name == '') {
						$instance_name = $this->last_used_instance;
				}
				
				//Check if instance exists
				if(!isset($this->db_instances[$instance_name])) {
						core()->addCoreError(5, 'No database instance with this name has been found (cDB->buildInsert). See additional info for instance name: ', print_r($instance_name, true));
						core()->coreError();
						die;		//It already die's in coreError, but this is to make things clear!
				}
				
				return $this->db_instances[$instance_name]['instance']->buildInsert($params);
		}
		
		///////////////////////////////////////////////////////////
		// Get the numbers of rows that where changed or added in an update or insert query.
		///////////////////////////////////////////////////////////
		public function affectedRows($instance_name = '') {
				//Check if we got a last used instance
				if($instance_name == '') {
						$instance_name = $this->last_used_instance;
				}
				
				//Check if instance exists
				if(!isset($this->db_instances[$instance_name])) {
						core()->addCoreError(5, 'No database instance with this name has been found (cDB->affectedRows). See additional info for instance name: ', print_r($instance_name, true));
						core()->coreError();
						die;		//It already die's in coreError, but this is to make things clear!
				}
				
				return $this->db_instances[$instance_name]['instance']->affectedRows();
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////////////
		// Insert id abrufen
		/////////////////////////////////////////////////////////////////////////////////////////////////
		public function insertId($instance_name = '') {
				//Check if we got a last used instance
				if($instance_name == '') {
						$instance_name = $this->last_used_instance;
				}
				
				//Check if instance exists
				if(!isset($this->db_instances[$instance_name])) {
						core()->addCoreError(5, 'No database instance with this name has been found (cDB->affectedRows). See additional info for instance name: ', print_r($instance_name, true));
						core()->coreError();
						die;		//It already die's in coreError, but this is to make things clear!
				}
				
				return $this->db_instances[$instance_name]['instance']->insertId();
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////////////
		// Check tables existence by table name.
		/////////////////////////////////////////////////////////////////////////////////////////////////
		public function checkTableExistence($table_name, $instance_name = '') {
				//Check if we got a last used instance
				if($instance_name == '') {
						$instance_name = $this->last_used_instance;
				}
				
				//Check if instance exists
				if(!isset($this->db_instances[$instance_name])) {
						core()->addCoreError(5, 'No database instance with this name has been found (cDB->checkTableExistence). See additional info for instance name: ', print_r($instance_name, true));
						core()->coreError();
						die;		//It already die's in coreError, but this is to make things clear!
				}
				
				return $this->db_instances[$instance_name]['instance']->checkTableExistence($table_name);
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////////////
		// Get table prefix.
		/////////////////////////////////////////////////////////////////////////////////////////////////
		public function getTablePrefix($instance_name = '') {
				if($instance_name == '') {
						$instance_name = $this->last_used_instance;
				}
				
				//Check if instance exists
				if(!isset($this->db_instances[$instance_name])) {
						core()->addCoreError(5, 'No database instance with this name has been found (cDB->getTablePrefix). See additional info for instance name: ', print_r($instance_name, true));
						core()->coreError();
						die;		//It already die's in coreError, but this is to make things clear!
				}
				
				return $this->db_instances[$instance_name]['prefix'];
		}
}

?>