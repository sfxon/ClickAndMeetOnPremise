<?php

class cEventLocationsMaster extends cModule {
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Load Customer groups data by id.
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadById($id) {
				$retval = array();
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('event_locations') . ' WHERE id = :id LIMIT 1;');
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
						'FROM ' . $db->table('event_locations') . ' ' .
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
						'INSERT INTO ' . $db->table('event_locations') . ' ' .
            		'(title,description,user_id,email_address,booking_info_by_mail) ' .
								'VALUES' .
                '(:title,:description,:user_id,:email_address,:booking_info_by_mail) '
				);
                		                		            		$db->bind(':title', $data['title']);
                            		            		$db->bind(':description', $data['description']);
                            		            		$db->bind(':user_id', $data['user_id']);
                            		            		$db->bind(':email_address', $data['email_address']);
                            		            		$db->bind(':booking_info_by_mail', $data['booking_info_by_mail']);
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
						'UPDATE ' . $db->table('event_locations') . ' SET ' .
								'title = :title, description = :description, user_id = :user_id, email_address = :email_address, booking_info_by_mail = :booking_info_by_mail ' .
						'WHERE ' .
								'id = :id'
				);
				                    		$db->bind(':title', $data['title']);
                                		$db->bind(':description', $data['description']);
                                		$db->bind(':user_id', $data['user_id']);
                                		$db->bind(':email_address', $data['email_address']);
                                		$db->bind(':booking_info_by_mail', $data['booking_info_by_mail']);
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
						'DELETE FROM ' . $db->table('event_locations') . ' ' .
						'WHERE ' .
								'id = :id'
				);
				$db->bind(':id', (int)$id);
				$db->execute();
		}
}

?>