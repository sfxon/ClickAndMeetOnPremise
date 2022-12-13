<?php

require_once('master/cCmSettingsMaster.master.class.php');

class cCmSettings extends cCmSettingsMaster {
		//////////////////////////////////////////////////////////////////////
		// Einstellungen indexiert laden.
		//////////////////////////////////////////////////////////////////////
		function loadSettingsIndexed() {
				$settings = $this->loadList();
				
				$retval = array();
				
				foreach($settings as $s) {
						$retval[$s['field_title']] = $s;
				}
				
				return $retval;
		}
		
		//////////////////////////////////////////////////////////////////////
		// Einstellungen indexiert laden.
		//////////////////////////////////////////////////////////////////////
		function loadIndexedList() {
				$settings = $this->loadList();
				
				$retval = array();
				
				foreach($settings as $s) {
						$retval[$s['field_title']] = $s['field_value'];
				}
				
				return $retval;
		}
}

?>