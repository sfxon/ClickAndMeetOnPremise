<?php

require_once('master/cUserUnitMaster.master.class.php');

///////////////////////////////////////////////////////////////////////////////////////
// Use this class to implement your own code to enhance the class.
///////////////////////////////////////////////////////////////////////////////////////
class cUserUnit extends cUserUnitMaster {
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

	///////////////////////////////////////////////////////////////////////////////////////////////////
	// Ermitteln, ob es Termine für eine bestimmte UserUnit gibt.
	///////////////////////////////////////////////////////////////////////////////////////////////////
	public function hasEventLocation($event_location_id) {
		$db = core()->get('db');
		$db->useInstance('systemdb');
		$db->setQuery(
			'SELECT * ' .
			'FROM ' . $db->table('user_unit') . ' ' .
			'WHERE ' .
				'event_location_id = :event_location_id ' .
			'LIMIT 1;'
		);
		$db->bind('event_location_id', $event_location_id);
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