<?php

require_once('master/cSystemsettingsMaster.master.class.php');

///////////////////////////////////////////////////////////////////////////////////////
// Use this class to implement your own code to enhance the class.
///////////////////////////////////////////////////////////////////////////////////////
class cSystemsettings extends cSystemsettingsMaster {
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Ssytemeinstellungen laden - indiziert..
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadListIndexed() {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * ' .
						'FROM ' . $db->table('systemsettings') . ' ' .
						'ORDER BY title;'
				);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$retval[$tmp['setting_key']] = $tmp;
				}
				
				return $retval;
		}
}

?>