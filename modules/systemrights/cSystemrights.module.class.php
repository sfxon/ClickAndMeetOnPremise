<?php

class cSystemrights extends cModule {
		////////////////////////////////////////////////////////////////////////////////////////////
		// Check existence by module and rightskey.
		////////////////////////////////////////////////////////////////////////////////////////////
		public static function getByModuleAndRightsKey($module, $rightskey) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT id FROM ' . $db->table('systemrights') . ' WHERE module = :module AND rightskey = :rightskey LIMIT 1;');
				$db->bind(':module', $module);
				$db->bind(':rightskey', $rightskey);
				$result = $db->execute();
				
				$retval = array();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(isset($tmp['id'])) {
						return $tmp['id'];
				}
				
				return false;
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////
		// Check if a systemright exists by it's ID.
		////////////////////////////////////////////////////////////////////////////////////////////
		public static function checkExistenceById($id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT id FROM ' . $db->table('systemrights') . ' WHERE id = :id LIMIT 1;');
				$db->bind(':id', (int)$id);
				$result = $db->execute();
				
				$retval = array();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(isset($tmp['id'])) {
						return true;
				}
				
				return false;
		}
		
		///////////////////////////////////////////////////////////////////
		// Loads a list of content entries.
		///////////////////////////////////////////////////////////////////
		public static function loadList() {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * ' .
						'FROM ' . $db->table('systemrights') . ' ' .
						'ORDER BY id ASC;'
				);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$retval[] = $tmp;
				}
				
				return $retval;
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Create content data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public static function createInDB($data) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'INSERT INTO ' . $db->table('systemrights') . 
								' (module, rightskey, created_on) ' .
						'VALUES(:module, :rightskey, NOW())'
				);
				$db->bind(':module', $data['module']);
				$db->bind(':rightskey', $data['rightskey']);
				$db->execute();
				
				return $db->insertId();
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Update navbar data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public static function updateInDB($id, $data) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('systemrights') . ' SET ' .
								'module = :module, ' .
								'rightskey = :rightskey ' .
						'WHERE ' .
								'id = :id'
				);
				$db->bind(':module', $data['module']);
				$db->bind(':rightskey', $data['rightskey']);
				$db->bind(':id', (int)$id);
				$db->execute();
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Loads an entry from the database.
		/////////////////////////////////////////////////////////////////////////////////
		public static function getDataById() {
				$id = (int)core()->getGetVar('id');
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * ' .
						'FROM ' . $db->table('systemrights') . ' ' .
						'WHERE ' .
								'id = :id'
				);
				$db->bind(':id', (int)$id);
				$result = $db->execute();
				
				return $result->fetchArrayAssoc();
		}
}
		
?>