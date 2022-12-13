<?php

class cUserImages extends cModule {
		//////////////////////////////////////////////////////////////////////////////////////////////////////
		// Create entry in the database.
		//////////////////////////////////////////////////////////////////////////////////////////////////////
		public function create($user_id, $filename, $age_on_image, $comment_for_visitors) {
				//Daten in der Datenbank überprüfen.
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'INSERT INTO ' . $db->table('user_images') . ' ' .
								'(user_id, datetime_created, filename, checked_by_admin, checked_by_admin_on, is_active, age_on_image, comment_for_visitors, is_banned, average_rating, rate_count) ' .
						'VALUES ' .
								'(:user_id, :datetime_created, :filename, :checked_by_admin, NULL, :is_active, :age_on_image, :comment_for_visitors, :is_banned, :average_rating, :rate_count) '
				);
				$db->bind(':user_id', (int)$user_id);
				$db->bind(':datetime_created', date('Y-m-d H:i:s'));
				$db->bind(':filename', $filename);
				$db->bind(':checked_by_admin', 0);
				$db->bind(':is_active', 1);
				$db->bind(':age_on_image', (int)$age_on_image);
				$db->bind(':comment_for_visitors', $comment_for_visitors);
				$db->bind(':is_banned', 0);
				$db->bind(':average_rating', 0);
				$db->bind(':rate_count', 0);
				$result = $db->execute();
				$result->fetchArrayAssoc();
				
				$id = $db->insertId();
				
				return $id;
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////////////////
		// User Bilder laden.
		//////////////////////////////////////////////////////////////////////////////////////////////////////
		public function loadUserImages($user_id, $count) {
				//Daten in der Datenbank überprüfen.
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('user_images') . ' ' .
						'WHERE ' .
								'user_id = :user_id AND ' .
								'is_active = 1 AND ' .
								'is_banned = 0 ' .
						'ORDER BY ' .
								'datetime_created DESC ' .
								'LIMIT ' . (int)$count
				);
				$db->bind(':user_id', (int)$user_id);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$retval[] = $tmp;
				}
				
				return $retval;
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////////////////
		// User Bilder laden.
		//////////////////////////////////////////////////////////////////////////////////////////////////////
		public function loadUserImagesFrom($user_id, $index, $count) {
				//Daten in der Datenbank überprüfen.
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('user_images') . ' ' .
						'WHERE ' .
								'user_id = :user_id AND ' .
								'is_active = 1 AND ' .
								'is_banned = 0 ' .
						'ORDER BY ' .
								'datetime_created DESC ' .
								'LIMIT ' . (int)$index . ', ' . (int)$count
				);
				$db->bind(':user_id', (int)$user_id);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$retval[] = $tmp;
				}
				
				return $retval;
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////////////////
		// Gesamtzahl an Bildern laden..
		//////////////////////////////////////////////////////////////////////////////////////////////////////
		public function countUserImagesTotal($user_id) {
				//Daten in der Datenbank überprüfen.
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT count(*) as anzahl FROM ' . $db->table('user_images') . ' ' .
						'WHERE ' .
								'user_id = :user_id AND ' .
								'is_active = 1 AND ' .
								'is_banned = 0 '
				);
				$db->bind(':user_id', (int)$user_id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(isset($tmp['anzahl'])) {
						return (int)$tmp['anzahl'];
				}
				
				return 0;
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////////////////
		// User Bilder laden.
		//////////////////////////////////////////////////////////////////////////////////////////////////////
		public function loadAllImagesByUser($user_id) {
				//Daten in der Datenbank überprüfen.
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('user_images') . ' ' .
						'WHERE ' .
								'user_id = :user_id'
				);
				$db->bind(':user_id', (int)$user_id);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$retval[] = $tmp;
				}
				
				return $retval;
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////////////////
		// User Bilder laden.
		//////////////////////////////////////////////////////////////////////////////////////////////////////
		public function loadById($id, $user_id) {
				//Daten in der Datenbank überprüfen.
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('user_images') . ' ' .
						'WHERE ' .
								'id = :id AND ' .
								'user_id = :user_id AND ' .
								'is_banned = 0 ' . 
						'LIMIT 1'
				);
				$db->bind(':id', (int)$id);
				$db->bind(':user_id', (int)$user_id);
				$result = $db->execute();
				
				$retval = $result->fetchArrayAssoc();
					
				return $retval;
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////////////////
		// User Bilder laden.
		//////////////////////////////////////////////////////////////////////////////////////////////////////
		public function loadByIdOnly($id) {
				//Daten in der Datenbank überprüfen.
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('user_images') . ' ' .
						'WHERE ' .
								'id = :id AND ' .
								'is_active = 1 AND ' .
								'is_banned = 0 ' . 
						'LIMIT 1'
				);
				$db->bind(':id', (int)$id);
				$result = $db->execute();
				
				$retval = $result->fetchArrayAssoc();
					
				return $retval;
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////////////////
		// Bild löschen.
		// For security reasons, we take the image id and the user id to delete the image.
		//////////////////////////////////////////////////////////////////////////////////////////////////////
		public function deleteImage($img_id, $user_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'DELETE FROM ' . $db->table('user_images') . ' ' .
						'WHERE ' .
								'id = :id AND ' .
								'user_id = :user_id;'
				);
				$db->bind(':id', (int)$img_id);
				$db->bind(':user_id', (int)$user_id);
				$db->execute();
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////////////////
		// Einige Daten aktualisieren.
		//////////////////////////////////////////////////////////////////////////////////////////////////////
		public function updateAgeAndMessage($img_id, $user_id, $age_on_image, $comment_for_visitors) {
				//Daten in der Datenbank überprüfen.
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('user_images') . ' SET ' .
								'age_on_image = :age_on_image, ' .
								'comment_for_visitors = :comment_for_visitors ' .
						'WHERE ' .
								'id = :img_id AND ' .
								'user_id = :user_id'
				);
				$db->bind(':age_on_image', (int)$age_on_image);
				$db->bind(':comment_for_visitors', $comment_for_visitors);
				$db->bind(':img_id', (int)$img_id);
				$db->bind(':user_id', (int)$user_id);
				$db->execute();
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////////////////
		// Bild laden
		//////////////////////////////////////////////////////////////////////////////////////////////////////
		public function loadImage_WhereUserIsNotOwner_And_NotRatedByUser($user_id) {
				//Daten aus Datenbank abrufen..
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT ui.* FROM ' . $db->table('user_images') . ' ui ' .
						'LEFT JOIN ' . $db->table('user_images_rating') . ' uir ON ui.id = uir.user_image_id AND uir.user_id = :user_id ' .
						'WHERE ' .
								'ui.user_id != :user_id2 AND ' .
								'ui.is_active = 1 AND ' .
								'ui.is_banned = 0 AND ' .
								'uir.user_image_id IS NULL ' .
						'ORDER BY ' .
								'ui.datetime_created DESC ' .
								'LIMIT 1'
				);
				
				$db->bind(':user_id', (int)$user_id);
				$db->bind(':user_id2', (int)$user_id);
				
				$result = $db->execute();
				
				$retval = $result->fetchArrayAssoc();
				return $retval;
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////////////////
		// Bilddaten aktualisieren.
		//////////////////////////////////////////////////////////////////////////////////////////////////////
		public function updateRateCountAndRatingSumAndAverageRatingById($image_id, $rate_count, $rating_sum, $average_rating) {
				//Daten aus Datenbank abrufen..
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('user_images') . ' ' .
						'SET ' .
								'rate_count = :rate_count, ' .
								'rating_sum = :rating_sum, ' .
								'average_rating = :average_rating ' .
						'WHERE ' .
								'id = :id '
				);
				$db->bind(':rate_count', (int)$rate_count);
				$db->bind(':rating_sum', (int)$rating_sum);
				$db->bind(':average_rating', (int)$average_rating);
				$db->bind(':id', (int)$image_id);
				$db->execute();
		}
}