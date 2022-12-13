<?php

class cModuleBuilderFields extends cModule {
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Load Customer groups data by id.
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadById($id) {
				$retval = array();
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('module_builder_fields') . ' WHERE id = :id ORDER BY sort_order LIMIT 1;');
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
		public static function loadListByModuleBuilderId($module_builder_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * ' .
						'FROM ' . $db->table('module_builder_fields') . ' ' .
						'WHERE module_builder_id = :module_builder_id ' .
						'ORDER BY sort_order, title;'
				);
				$db->bind(':module_builder_id', (int)$module_builder_id);
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
						'INSERT INTO ' . $db->table('module_builder_fields') . 
								' (module_builder_id, title, field_name, data_type, length, foreign_table_id, sort_order) ' .
						'VALUES(:module_builder_id, :title, :field_name, :data_type, :length, :foreign_table_id, :sort_order)'
				);
				$db->bind(':module_builder_id', $data['module_builder_id']);
				$db->bind(':title', $data['title']);
				$db->bind(':field_name', $data['field_name']);
				$db->bind(':data_type', $data['data_type']);
				$db->bind(':length', $data['length']);
				$db->bind(':foreign_table_id', $data['foreign_table_id']);
				$db->bind(':sort_order', (int)$data['sort_order']);
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
						'UPDATE ' . $db->table('module_builder_fields') . ' SET ' .
								'module_builder_id = :module_builder_id, ' .
								'title = :title, ' .
								'field_name = :field_name, ' .
								'data_type = :data_type, ' .
								'length = :length, ' .
								'foreign_table_id = :foreign_table_id, ' .
								'sort_order = :sort_order ' .
						'WHERE ' .
								'id = :id'
				);
				$db->bind(':module_builder_id', $data['module_builder_id']);
				$db->bind(':title', $data['title']);
				$db->bind(':field_name', $data['field_name']);
				$db->bind(':data_type', $data['data_type']);
				$db->bind(':length', $data['length']);
				$db->bind(':foreign_table_id', $data['foreign_table_id']);
				$db->bind(':sort_order', (int)$data['sort_order']);
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
						'DELETE FROM ' . $db->table('module_builder_fields') . ' ' .
						'WHERE ' .
								'id = :id'
				);
				$db->bind(':id', (int)$id);
				$db->execute();
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Update data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public static function getDefaultFields($module_builder_id) {
				$default_fields = array(
						array(
								'id' => 0,
								'module_builder_id' => (int)$module_builder_id,
								'title' => 'ID',
								'field_name' => 'id',
								'data_type' => 'int',
								'length' => 11,
								'foreign_table_id' => 0,
								'sort_order' => -2
						),
						array(
								'id' => 0,
								'module_builder_id' => (int)$module_builder_id,
								'title' => 'Name',
								'field_name' => 'title',
								'data_type' => 'varchar',
								'length' => 256,
								'foreign_table_id' => 0,
								'sort_order' => -1
						)
				);
				
				return $default_fields;
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Update data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public static function updateLastBuildsFieldDataByModuleBuilderFieldsId($id, $last_builds_field_name, $last_builds_data_type, $last_builds_length) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('module_builder_fields') . ' SET ' .
								'last_builds_field_name = :last_builds_field_name, ' .
								'last_builds_data_type = :last_builds_data_type, ' .
								'last_builds_length = :last_builds_length ' .
						'WHERE ' .
								'id = :id'
				);
				$db->bind(':last_builds_field_name', $last_builds_field_name);
				$db->bind(':last_builds_data_type', $last_builds_data_type);
				$db->bind(':last_builds_length', $last_builds_length);
				$db->bind(':id', (int)$id);
				$db->execute();
		}
}

?>