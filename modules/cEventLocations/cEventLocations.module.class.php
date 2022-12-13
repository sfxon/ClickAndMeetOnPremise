<?php

require_once('master/cEventLocationsMaster.master.class.php');

///////////////////////////////////////////////////////////////////////////////////////
// Use this class to implement your own code to enhance the class.
///////////////////////////////////////////////////////////////////////////////////////
class cEventLocations extends cEventLocationsMaster {
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Alle Einträge laden
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadListByUserId($user_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * ' .
						'FROM ' . $db->table('event_locations') . ' ' .
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
		
		//////////////////////////////////////////////////////////////////////
		// Indexierte Liste laden.
		//////////////////////////////////////////////////////////////////////
		function loadIndexedList() {
				$data = $this->loadList();
				
				$retval = array();
				
				foreach($data as $d) {
						$retval[$d['id']] = $d;
				}
				
				return $retval;
		}
}

?>