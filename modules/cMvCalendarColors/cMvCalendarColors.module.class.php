<?php

require_once('master/cMvCalendarColorsMaster.master.class.php');

///////////////////////////////////////////////////////////////////////////////////////
// Use this class to implement your own code to enhance the class.
///////////////////////////////////////////////////////////////////////////////////////
class cMvCalendarColors extends cMvCalendarColorsMaster {
		//////////////////////////////////////////////////////////////////////
		// Indexierte Liste laden.
		//////////////////////////////////////////////////////////////////////
		function loadIndexedList() {
				$data = $this->loadList();
				
				$retval = array();
				
				foreach($data as $d) {
						if(strlen($d['field_value']) > 0) {
								$retval[$d['internal_identifier']] = $d['field_value'];
						}
				}
				
				return $retval;
		}
}

?>