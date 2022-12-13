<?php

class cTestMaster extends cModule {
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Load Customer groups data by id.
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadById($id) {
				$retval = array();
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('test') . ' WHERE id = :id LIMIT 1;');
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
						'FROM ' . $db->table('test') . ' ' .
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
						'INSERT INTO ' . $db->table('test') . ' ' .
            		'(title,testfeld1) ' .
								'VALUES' .
                '(:title,:testfeld1) '
				);
                		                		            		$db->bind(':title', $data['title']);
                            		            		$db->bind(':testfeld1', $data['testfeld1']);
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
						'UPDATE ' . $db->table('test') . ' SET ' .
								'title = :title, testfeld1 = :testfeld1 ' .
						'WHERE ' .
								'id = :id'
				);
				                    		$db->bind(':title', $data['title']);
                                		$db->bind(':testfeld1', $data['testfeld1']);
                    				$db->bind(':id', (int)$id);
				$db->execute();
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Delete data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public static function deleteById($id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'DELETE FROM ' . $db->table('test') . ' ' .
						'WHERE ' .
								'id = :id'
				);
				$db->bind(':id', (int)$id);
				$db->execute();
		}
}

?>