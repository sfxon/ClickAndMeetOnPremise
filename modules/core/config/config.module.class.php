<?php

/////////////////////////////////////////////////////////////////////////////////
// Configuration manager.
// This module can create, edit, load and/or delete configuration files,
// in the [root]/config folder of the project.
// It is one of the most core parts of the project, since it is used
// by some module to instanciate themself, with initial settings.
/////////////////////////////////////////////////////////////////////////////////
class cConfig extends cModule {		
		/////////////////////////////////////////////////////////////////////////////
		// Init variables.
		/////////////////////////////////////////////////////////////////////////////
		public function __construct() {
		
		}
		
		/////////////////////////////////////////////////////////////////////////////
		// Load Configuration as array from ini file.
		// Parameter 1 defines the files path.
		// Parameter 2 defines, which variables need to be there.
		// Not found parameters are returned as NULL.
		/////////////////////////////////////////////////////////////////////////////
		public static function loadIniFileAsArray($path, $expected_indexes = array()) {
				$data = parse_ini_file($path);
				
				//return as is.
				if(!is_array($expected_indexes) || count($expected_indexes) === 0) {
						return $data;
				}
				
				//return NULL for not found parameters.
				$retval = array();
				
				foreach($expected_indexes as $index) {
						if(!isset($data[$index])) {
								$retval[$index] = NULL;
						} else {
								$retval[$index] = $data[$index];
						}
				}
				
				return $retval;
			
		}
}

?>