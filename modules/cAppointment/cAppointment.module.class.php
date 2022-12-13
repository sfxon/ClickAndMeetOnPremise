<?php

require_once('master/cAppointmentMaster.master.class.php');

///////////////////////////////////////////////////////////////////////////////////////
// Use this class to implement your own code to enhance the class.
///////////////////////////////////////////////////////////////////////////////////////
class cAppointment extends cAppointmentMaster {
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Alle Einträge laden
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadListByUserId($user_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * ' .
						'FROM ' . $db->table('appointment') . ' ' .
						'WHERE user_id = :user_id ' .
						'ORDER BY title;'
				);
				$db->bind('user_id', $user_id);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$retval[] = $tmp;
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Load by Date from and to and user_id
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadListByDateRangeAndUserId($date_from, $date_to, $user_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * ' .
						'FROM ' . $db->table('appointment') . ' ' .
						'WHERE ' .
								'datetime_of_event >= :datetime_of_event_from AND ' .
								'datetime_of_event <= :datetime_of_event_to AND ' .
								'created_by = :user_id ' .
						'ORDER BY datetime_of_event ASC;'
				);
				$db->bind('datetime_of_event_from', $date_from);
				$db->bind('datetime_of_event_to', $date_to);
				$db->bind('user_id', (int)$user_id);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$retval[] = $tmp;
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Load by Date from and to and Event Location
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadListByDateRangeAndUserIdAndEventLocation($event_location_id, $date_from, $date_to, $user_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * ' .
						'FROM ' . $db->table('appointment') . ' ' .
						'WHERE ' .
								'event_location_id = :event_location_id AND ' .
								'datetime_of_event >= :datetime_of_event_from AND ' .
								'datetime_of_event <= :datetime_of_event_to AND ' .
								'created_by = :user_id ' .
						'ORDER BY datetime_of_event ASC;'
				);
				$db->bind('event_location_id', (int)$event_location_id);
				$db->bind('datetime_of_event_from', $date_from);
				$db->bind('datetime_of_event_to', $date_to);
				$db->bind('user_id', (int)$user_id);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$retval[] = $tmp;
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Load by Date from and to and user_unit
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadListByDateRangeAndUserIdAndUserUnitId($user_unit_id, $date_from, $date_to, $user_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * ' .
						'FROM ' . $db->table('appointment') . ' ' .
						'WHERE ' .
								'user_unit_id = :user_unit_id AND ' .
								'datetime_of_event >= :datetime_of_event_from AND ' .
								'datetime_of_event <= :datetime_of_event_to AND ' .
								'created_by = :user_id ' .
						'ORDER BY datetime_of_event ASC;'
				);
				$db->bind('user_unit_id', (int)$user_unit_id);
				$db->bind('datetime_of_event_from', $date_from);
				$db->bind('datetime_of_event_to', $date_to);
				$db->bind('user_id', (int)$user_id);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$retval[] = $tmp;
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Anzahl der Termine in einem Zeitraum laden.
		// Gruppiert anhand der Tage in diesem Zeitraum.
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public function countAppointmentsByDays($from, $to) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT DAY(datetime_of_event) as day, COUNT(id) as anzahl, status ' .
						'FROM ' . $db->table('appointment') . ' ' .
						'WHERE ' .
								'datetime_of_event >= :datetime_of_event_from AND ' .
								'datetime_of_event <= :datetime_of_event_to ' .
						'GROUP BY DAY(datetime_of_event), status; ' .
						'ORDER BY DAY(datetime_of_event), status;'
				);
				$db->bind('datetime_of_event_from', $from);
				$db->bind('datetime_of_event_to', $to);
				/*$db->bind('user_id', (int)$user_id);*/
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$retval[] = $tmp;
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Anzahl der Termine in einem Zeitraum laden.
		// Gruppiert anhand der Tage in diesem Zeitraum.
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public function countAppointmentsByDaysAndEventLocation($event_location_id, $from, $to) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT DAY(datetime_of_event) as day, COUNT(id) as anzahl, status ' .
						'FROM ' . $db->table('appointment') . ' ' .
						'WHERE ' .
								'event_location_id = :event_location_id AND ' .
								'datetime_of_event >= :datetime_of_event_from AND ' .
								'datetime_of_event <= :datetime_of_event_to ' .
						'GROUP BY DAY(datetime_of_event), status; ' .
						'ORDER BY DAY(datetime_of_event), status;'
				);
				$db->bind('event_location_id', (int)$event_location_id);
				$db->bind('datetime_of_event_from', $from);
				$db->bind('datetime_of_event_to', $to);
				/*$db->bind('user_id', (int)$user_id);*/
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$retval[] = $tmp;
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Anzahl der Termine in einem Zeitraum laden.
		// Gruppiert anhand der Tage in diesem Zeitraum.
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public function countAppointmentsByDaysAndUserUnit($user_unit_id, $from, $to) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT DAY(datetime_of_event) as day, COUNT(id) as anzahl, status ' .
						'FROM ' . $db->table('appointment') . ' ' .
						'WHERE ' .
								'user_unit_id = :user_unit_id AND ' .
								'datetime_of_event >= :datetime_of_event_from AND ' .
								'datetime_of_event <= :datetime_of_event_to ' .
						'GROUP BY DAY(datetime_of_event), status; ' .
						'ORDER BY DAY(datetime_of_event), status;'
				);
				$db->bind('user_unit_id', (int)$user_unit_id);
				$db->bind('datetime_of_event_from', $from);
				$db->bind('datetime_of_event_to', $to);
				/*$db->bind('user_id', (int)$user_id);*/
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$retval[] = $tmp;
				}
				
				return $retval;
		}	
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Load by Date from and to and user_id
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadListByDateRangeAndStatus($date_from, $date_to, $status) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * ' .
						'FROM ' . $db->table('appointment') . ' ' .
						'WHERE ' .
								'datetime_of_event >= :datetime_of_event_from AND ' .
								'datetime_of_event <= :datetime_of_event_to AND ' .
								'status = :status ' .
						'ORDER BY datetime_of_event ASC;'
				);
				$db->bind('datetime_of_event_from', $date_from);
				$db->bind('datetime_of_event_to', $date_to);
				$db->bind('status', (int)$status);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$retval[] = $tmp;
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Load by Date from and to and Event Location
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadListByDateRangeAndStatusAndEventLocation($event_location_id, $date_from, $date_to, $status) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * ' .
						'FROM ' . $db->table('appointment') . ' ' .
						'WHERE ' .
								'event_location_id = :event_location_id AND ' .
								'datetime_of_event >= :datetime_of_event_from AND ' .
								'datetime_of_event <= :datetime_of_event_to AND ' .
								'status = :status ' .
						'ORDER BY datetime_of_event ASC;'
				);
				$db->bind('event_location_id', (int)$event_location_id);
				$db->bind('datetime_of_event_from', $date_from);
				$db->bind('datetime_of_event_to', $date_to);
				$db->bind('status', (int)$status);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$retval[] = $tmp;
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Load by Date from and to and user_unit
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadListByDateRangeAndStatusAndUserUnitId($user_unit_id, $date_from, $date_to, $status) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * ' .
						'FROM ' . $db->table('appointment') . ' ' .
						'WHERE ' .
								'user_unit_id = :user_unit_id AND ' .
								'datetime_of_event >= :datetime_of_event_from AND ' .
								'datetime_of_event <= :datetime_of_event_to AND ' .
								'status = :status ' .
						'ORDER BY datetime_of_event ASC;'
				);
				$db->bind('user_unit_id', (int)$user_unit_id);
				$db->bind('datetime_of_event_from', $date_from);
				$db->bind('datetime_of_event_to', $date_to);
				$db->bind('status', (int)$status);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$retval[] = $tmp;
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Anzahl der Termine in einem Zeitraum laden.
		// Gruppiert anhand der Tage in diesem Zeitraum.
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public function countAppointmentsByDaysAndStatus($from, $to, $status) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT DAY(datetime_of_event) as day, COUNT(id) as anzahl, status ' .
						'FROM ' . $db->table('appointment') . ' ' .
						'WHERE ' .
								'datetime_of_event >= :datetime_of_event_from AND ' .
								'datetime_of_event <= :datetime_of_event_to AND ' .
								'status = :status ' .
						'GROUP BY DAY(datetime_of_event), status; ' .
						'ORDER BY DAY(datetime_of_event), status;'
				);
				$db->bind('datetime_of_event_from', $from);
				$db->bind('datetime_of_event_to', $to);
				$db->bind('status', (int)$status);
				/*$db->bind('user_id', (int)$user_id);*/
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$retval[] = $tmp;
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Anzahl der Termine in einem Zeitraum laden.
		// Gruppiert anhand der Tage in diesem Zeitraum.
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public function countAppointmentsByDaysAndEventLocationAndStatus($event_location_id, $from, $to, $status) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT DAY(datetime_of_event) as day, COUNT(id) as anzahl, status ' .
						'FROM ' . $db->table('appointment') . ' ' .
						'WHERE ' .
								'event_location_id = :event_location_id AND ' .
								'datetime_of_event >= :datetime_of_event_from AND ' .
								'datetime_of_event <= :datetime_of_event_to AND ' .
								'status = :status ' .
						'GROUP BY DAY(datetime_of_event), status; ' .
						'ORDER BY DAY(datetime_of_event), status;'
				);
				$db->bind('event_location_id', (int)$event_location_id);
				$db->bind('datetime_of_event_from', $from);
				$db->bind('datetime_of_event_to', $to);
				$db->bind('status', (int)$status);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$retval[] = $tmp;
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Anzahl der Termine in einem Zeitraum laden.
		// Gruppiert anhand der Tage in diesem Zeitraum.
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public function countAppointmentsByDaysAndUserUnitAndStatus($user_unit_id, $from, $to, $status) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT DAY(datetime_of_event) as day, COUNT(id) as anzahl, status ' .
						'FROM ' . $db->table('appointment') . ' ' .
						'WHERE ' .
								'user_unit_id = :user_unit_id AND ' .
								'datetime_of_event >= :datetime_of_event_from AND ' .
								'datetime_of_event <= :datetime_of_event_to AND ' .
								'status = :status ' .
						'GROUP BY DAY(datetime_of_event), status; ' .
						'ORDER BY DAY(datetime_of_event), status;'
				);
				$db->bind('user_unit_id', (int)$user_unit_id);
				$db->bind('datetime_of_event_from', $from);
				$db->bind('datetime_of_event_to', $to);
				$db->bind('status', (int)$status);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$retval[] = $tmp;
				}
				
				return $retval;
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Delete data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public static function deleteByDateFromAndDateToAndAndStatus($between_from, $between_to, $status) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'DELETE FROM ' . $db->table('appointment') . ' ' .
						'WHERE ' .
								'datetime_of_event >= :between_from AND ' .
								'datetime_of_event <= :between_to AND ' .
								'status = :status'
				);
				$db->bind(':between_from', $between_from);
				$db->bind(':between_to', $between_to);
				$db->bind(':status', (int)$status);
				$db->execute();
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Delete data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public static function deleteByDateFromAndDateToAndAndStatusAndUserUnit($between_from, $between_to, $status, $user_unit_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'DELETE FROM ' . $db->table('appointment') . ' ' .
						'WHERE ' .
								'datetime_of_event >= :between_from AND ' .
								'datetime_of_event <= :between_to AND ' .
								'user_unit_id = :user_unit_id AND ' .
								'status = :status'
				);
				$db->bind(':between_from', $between_from);
				$db->bind(':between_to', $between_to);
				$db->bind(':user_unit_id', $user_unit_id);
				$db->bind(':status', (int)$status);
				$db->execute();
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Delete data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public static function deleteByDateFromAndDateToAndAndStatusAndEventLocation($between_from, $between_to, $status, $event_location_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'DELETE FROM ' . $db->table('appointment') . ' ' .
						'WHERE ' .
								'datetime_of_event >= :between_from AND ' .
								'datetime_of_event <= :between_to AND ' .
								'event_location_id = :event_location_id AND ' .
								'status = :status'
				);
				$db->bind(':between_from', $between_from);
				$db->bind(':between_to', $between_to);
				$db->bind(':event_location_id', $event_location_id);
				$db->bind(':status', (int)$status);
				$db->execute();
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Load by Date from and to and user_id
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function adminLoadListByDateRange($date_from, $date_to, $sql_additional_where) {
				$additional_where = '';
				
				if(isset($sql_additional_where['where'])) {
						$additional_where = $sql_additional_where['where'];
				}
				
				//Build query
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * ' .
						'FROM ' . $db->table('appointment') . ' ' .
						'WHERE ' .
								'datetime_of_event >= :datetime_of_event_from AND ' .
								'datetime_of_event <= :datetime_of_event_to ' .
								$additional_where .
						'ORDER BY datetime_of_event ASC;'
				);
				$db->bind('datetime_of_event_from', $date_from);
				$db->bind('datetime_of_event_to', $date_to);
				
				if(isset($sql_additional_where['params']) && is_array($sql_additional_where['params']) && count($sql_additional_where['params']) > 0) {
						foreach($sql_additional_where['params'] as $param) {
								$db->bind($param['query_marker'], $param['data']);
						}
				}
				
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$retval[] = $tmp;
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Load by Date from and to and Event Location
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function adminLoadListByDateRangeAndEventLocation($event_location_id, $date_from, $date_to, $sql_additional_where) {
				$additional_where = '';
				
				if(isset($sql_additional_where['where'])) {
						$additional_where = $sql_additional_where['where'];
				}
				
				//Build query
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * ' .
						'FROM ' . $db->table('appointment') . ' ' .
						'WHERE ' .
								'event_location_id = :event_location_id AND ' .
								'datetime_of_event >= :datetime_of_event_from AND ' .
								'datetime_of_event <= :datetime_of_event_to ' .
								$additional_where .
						'ORDER BY datetime_of_event ASC;'
				);
				$db->bind('event_location_id', (int)$event_location_id);
				$db->bind('datetime_of_event_from', $date_from);
				$db->bind('datetime_of_event_to', $date_to);
				
				if(isset($sql_additional_where['params']) && is_array($sql_additional_where['params']) && count($sql_additional_where['params']) > 0) {
						foreach($sql_additional_where['params'] as $param) {
								$db->bind($param['query_marker'], $param['data']);
						}
				}
				
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$retval[] = $tmp;
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Load by Date from and to and user_unit
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function adminLoadListByDateRangeAndUserUnitId($user_unit_id, $date_from, $date_to, $sql_additional_where) {
				$additional_where = '';
				
				if(isset($sql_additional_where['where'])) {
						$additional_where = $sql_additional_where['where'];
				}
				
				//Build query
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * ' .
						'FROM ' . $db->table('appointment') . ' ' .
						'WHERE ' .
								'user_unit_id = :user_unit_id AND ' .
								'datetime_of_event >= :datetime_of_event_from AND ' .
								'datetime_of_event <= :datetime_of_event_to ' .
								$additional_where .
						'ORDER BY datetime_of_event ASC;'
				);
				$db->bind('user_unit_id', (int)$user_unit_id);
				$db->bind('datetime_of_event_from', $date_from);
				$db->bind('datetime_of_event_to', $date_to);
				
				if(isset($sql_additional_where['params']) && is_array($sql_additional_where['params']) && count($sql_additional_where['params']) > 0) {
						foreach($sql_additional_where['params'] as $param) {
								$db->bind($param['query_marker'], $param['data']);
						}
				}
				
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$retval[] = $tmp;
				}
				
				return $retval;
		}

	///////////////////////////////////////////////////////////////////////////////////////////////////
	// Ermitteln, ob es Termine für eine bestimmte Event-Location gibt.
	///////////////////////////////////////////////////////////////////////////////////////////////////
	public function hasEventLocationsAppointments($eventLocationId) {
		$db = core()->get('db');
		$db->useInstance('systemdb');
		$db->setQuery(
			'SELECT * ' .
			'FROM ' . $db->table('appointment') . ' ' .
			'WHERE ' .
				'event_location_id = :event_location_id ' .
			'LIMIT 1;'
		);
		$db->bind('event_location_id', $eventLocationId);
		$result = $db->execute();
		
		$retval = array();
		
		while($result->next()) {
			$tmp = $result->fetchArrayAssoc();
			$retval[] = $tmp;
		}

		if(count($retval) > 0) {
			return true;
		}
		
		return false;
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////
	// Ermitteln, ob es Termine für eine bestimmte UserUnit gibt.
	///////////////////////////////////////////////////////////////////////////////////////////////////
	public function hasUserUnitAppointments($user_unit_id) {
		$db = core()->get('db');
		$db->useInstance('systemdb');
		$db->setQuery(
			'SELECT * ' .
			'FROM ' . $db->table('appointment') . ' ' .
			'WHERE ' .
				'user_unit_id = :user_unit_id ' .
			'LIMIT 1;'
		);
		$db->bind('user_unit_id', $user_unit_id);
		$result = $db->execute();
		
		$retval = array();
		
		while($result->next()) {
			$tmp = $result->fetchArrayAssoc();
			$retval[] = $tmp;
		}

		if(count($retval) > 0) {
			return true;
		}
		
		return false;
	}
}

?>