<?php

class cModuleBuilder extends cModule {
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Load Customer groups data by id.
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadById($id) {
				$retval = array();
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('module_builder') . ' WHERE id = :id LIMIT 1;');
				$db->bind(':id', (int)$id);
				$result = $db->execute();
				
				$data = $result->fetchArrayAssoc();
				
				if(empty($data)) {
						return NULL;
				}
		
				return $data;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Alle Einträge laden
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadList() {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * ' .
						'FROM ' . $db->table('module_builder') . ' ' .
						'ORDER BY title;'
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
		// Create data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public static function createInDB($data) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'INSERT INTO ' . $db->table('module_builder') . 
								' (title, data_module, admin_module, database_table) ' .
						'VALUES(:title, :data_module, :admin_module, :database_table)'
				);
				$db->bind(':title', $data['title']);
				$db->bind(':data_module', $data['data_module']);
				$db->bind(':admin_module', $data['admin_module']);
				$db->bind(':database_table', $data['database_table']);
				$db->execute();
				
				return $db->insertId();
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Update data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public static function updateInDB($id, $data) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('module_builder') . ' SET ' .
								'title = :title, ' .
								'data_module = :data_module, ' .
								'admin_module = :admin_module, ' .
								'database_table = :database_table ' .
						'WHERE ' .
								'id = :id'
				);
				$db->bind(':title', $data['title']);
				$db->bind(':data_module', $data['data_module']);
				$db->bind(':admin_module', $data['admin_module']);
				$db->bind(':database_table', $data['database_table']);
				$db->bind(':id', (int)$id);
				$db->execute();
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Update data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public static function deleteById($id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'DELETE FROM ' . $db->table('module_builder') . ' ' .
						'WHERE ' .
								'id = :id'
				);
				$db->bind(':id', (int)$id);
				$db->execute();
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Update data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public static function updateGenerationTimeInDB($id, $data) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('module_builder') . ' SET ' .
								'generation_time = :generation_time ' .
						'WHERE ' .
								'id = :id'
				);
				$db->bind(':generation_time', $data['generation_time']);
				$db->bind(':id', (int)$id);
				$db->execute();
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Update data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public static function updateLastBuildsDatabaseTableNameById($id, $last_builds_database_table) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('module_builder') . ' SET ' .
								'last_builds_database_table = :last_builds_database_table ' .
						'WHERE ' .
								'id = :id'
				);
				$db->bind(':last_builds_database_table', $last_builds_database_table);
				$db->bind(':id', (int)$id);
				$db->execute();
		}
}

?>