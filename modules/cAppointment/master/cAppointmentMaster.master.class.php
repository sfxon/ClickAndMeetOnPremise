<?php

use AbmmHasan\Uuid; 

class cAppointmentMaster extends cModule {
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Load Customer groups data by id.
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadById($id) {
				$retval = array();
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('appointment') . ' WHERE id = :id LIMIT 1;');
				$db->bind(':id', $id);
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
						'FROM ' . $db->table('appointment') . ' ' .
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
				// Create uuid;
				$uuid = Uuid::v4();
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'INSERT INTO ' . $db->table('appointment') . ' ' .
            		'(id,title,datetime_of_event,event_location_id,user_unit_id,status,created_by,datetime_checkin,datetime_checkout,visitor_user_id,checkin_by,checkout_by,comment_checkin,comment_checkout,comment_visitor_booking,reminder_user_mail,reminder_active,reminder_user_mail_sent,reminder_user_mail_sent_datetime,duration_in_minutes,firstname,lastname,email_address,customers_number,phone,street_number,plz,city,street,last_save_datetime) ' .
						'VALUES' .
                '(:id,:title,:datetime_of_event,:event_location_id,:user_unit_id,:status,:created_by,:datetime_checkin,:datetime_checkout,:visitor_user_id,:checkin_by,:checkout_by,:comment_checkin,:comment_checkout,:comment_visitor_booking,:reminder_user_mail,:reminder_active,:reminder_user_mail_sent,:reminder_user_mail_sent_datetime,:duration_in_minutes,:firstname,:lastname,:email_address,:customers_number,:phone,:street_number,:plz,:city,:street,:last_save_datetime) '
				);
				$db->bind(':id', $uuid);
				$db->bind(':title', $data['title']);
				$db->bind(':datetime_of_event', $data['datetime_of_event']);
				$db->bind(':event_location_id', $data['event_location_id']);
				$db->bind(':user_unit_id', $data['user_unit_id']);
				$db->bind(':status', $data['status']);
				$db->bind(':created_by', $data['created_by']);
				$db->bind(':datetime_checkin', $data['datetime_checkin']);
				$db->bind(':datetime_checkout', $data['datetime_checkout']);
				$db->bind(':visitor_user_id', $data['visitor_user_id']);
				$db->bind(':checkin_by', $data['checkin_by']);
				$db->bind(':checkout_by', $data['checkout_by']);
				$db->bind(':comment_checkin', $data['comment_checkin']);
				$db->bind(':comment_checkout', $data['comment_checkout']);
				$db->bind(':comment_visitor_booking', $data['comment_visitor_booking']);
				$db->bind(':reminder_user_mail', $data['reminder_user_mail']);
				$db->bind(':reminder_active', $data['reminder_active']);
				$db->bind(':reminder_user_mail_sent', $data['reminder_user_mail_sent']);
				$db->bind(':reminder_user_mail_sent_datetime', $data['reminder_user_mail_sent_datetime']);
				$db->bind(':duration_in_minutes', $data['duration_in_minutes']);
				$db->bind(':firstname', $data['firstname']);
				$db->bind(':lastname', $data['lastname']);
				$db->bind(':email_address', $data['email_address']);
				$db->bind(':customers_number', $data['customers_number']);
				$db->bind(':phone', $data['phone']);
				$db->bind(':street_number', $data['street_number']);
				$db->bind(':plz', $data['plz']);
				$db->bind(':city', $data['city']);
				$db->bind(':street', $data['street']);
				$db->bind(':last_save_datetime', $data['last_save_datetime']);
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
						'UPDATE ' . $db->table('appointment') . ' SET ' .
								'title = :title, datetime_of_event = :datetime_of_event, event_location_id = :event_location_id, user_unit_id = :user_unit_id, status = :status, created_by = :created_by, datetime_checkin = :datetime_checkin, datetime_checkout = :datetime_checkout, visitor_user_id = :visitor_user_id, checkin_by = :checkin_by, checkout_by = :checkout_by, comment_checkin = :comment_checkin, comment_checkout = :comment_checkout, comment_visitor_booking = :comment_visitor_booking, reminder_user_mail = :reminder_user_mail, reminder_active = :reminder_active, reminder_user_mail_sent = :reminder_user_mail_sent, reminder_user_mail_sent_datetime = :reminder_user_mail_sent_datetime, duration_in_minutes = :duration_in_minutes, firstname = :firstname, lastname = :lastname, email_address = :email_address, customers_number = :customers_number, phone = :phone, street_number = :street_number, plz = :plz, city = :city, street = :street, last_save_datetime = :last_save_datetime ' .
						'WHERE ' .
								'id = :id'
				);
				                    		$db->bind(':title', $data['title']);
                                		$db->bind(':datetime_of_event', $data['datetime_of_event']);
                                		$db->bind(':event_location_id', $data['event_location_id']);
                                		$db->bind(':user_unit_id', $data['user_unit_id']);
                                		$db->bind(':status', $data['status']);
                                		$db->bind(':created_by', $data['created_by']);
                                		$db->bind(':datetime_checkin', $data['datetime_checkin']);
                                		$db->bind(':datetime_checkout', $data['datetime_checkout']);
                                		$db->bind(':visitor_user_id', $data['visitor_user_id']);
                                		$db->bind(':checkin_by', $data['checkin_by']);
                                		$db->bind(':checkout_by', $data['checkout_by']);
                                		$db->bind(':comment_checkin', $data['comment_checkin']);
                                		$db->bind(':comment_checkout', $data['comment_checkout']);
                                		$db->bind(':comment_visitor_booking', $data['comment_visitor_booking']);
                                		$db->bind(':reminder_user_mail', $data['reminder_user_mail']);
                                		$db->bind(':reminder_active', $data['reminder_active']);
                                		$db->bind(':reminder_user_mail_sent', $data['reminder_user_mail_sent']);
                                		$db->bind(':reminder_user_mail_sent_datetime', $data['reminder_user_mail_sent_datetime']);
                                		$db->bind(':duration_in_minutes', $data['duration_in_minutes']);
                                		$db->bind(':firstname', $data['firstname']);
                                		$db->bind(':lastname', $data['lastname']);
                                		$db->bind(':email_address', $data['email_address']);
                                		$db->bind(':customers_number', $data['customers_number']);
                                		$db->bind(':phone', $data['phone']);
                                		$db->bind(':street_number', $data['street_number']);
                                		$db->bind(':plz', $data['plz']);
                                		$db->bind(':city', $data['city']);
                                		$db->bind(':street', $data['street']);
                                		$db->bind(':last_save_datetime', $data['last_save_datetime']);
                    				$db->bind(':id', $id);
				$db->execute();
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Delete data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public static function deleteById($id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'DELETE FROM ' . $db->table('appointment') . ' ' .
						'WHERE ' .
								'id = :id'
				);
				$db->bind(':id', $id);
				$db->execute();
		}
}

?>