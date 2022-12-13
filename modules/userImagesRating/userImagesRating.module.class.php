<?php

class cUserImagesRating extends cModule {
		//////////////////////////////////////////////////////////////////////////////////////////////////////
		// Create entry in the database.
		//////////////////////////////////////////////////////////////////////////////////////////////////////
		public function create($user_id, $user_image_id, $age, $date_of_rating) {
				//Daten in der Datenbank überprüfen.
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'INSERT INTO ' . $db->table('user_images_rating') . ' ' .
								'(user_id, user_image_id, age, date_of_rating) ' .
						'VALUES ' .
								'(:user_id, :user_image_id, :age, :date_of_rating) '
				);
				$db->bind(':user_id', (int)$user_id);
				$db->bind(':user_image_id', (int)$user_image_id);
				$db->bind(':age', (int)$age);
				$db->bind(':date_of_rating', $date_of_rating);
				$db->execute();
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////////////////
		// Eintrag laden..
		//////////////////////////////////////////////////////////////////////////////////////////////////////
		public function loadByUserIdAndUserImageId($user_id, $user_image_id) {
				//Daten in der Datenbank überprüfen.
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('user_images_rating') . ' ' .
						'WHERE ' .
								'user_id = :user_id AND ' .
								'user_image_id = :user_image_id ' . 
						'LIMIT 1'
				);
				$db->bind(':user_id', (int)$user_id);
				$db->bind(':user_image_id', (int)$user_image_id);
				$result = $db->execute();
				
				$retval = $result->fetchArrayAssoc();
					
				return $retval;
		}
}