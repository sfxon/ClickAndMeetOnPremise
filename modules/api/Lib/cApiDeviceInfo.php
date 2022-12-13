<?php

class cApiDeviceInfo {
		/////////////////////////////////////////////////////////////////////
		// User-Agent Informationen auslesen.
		/////////////////////////////////////////////////////////////////////
		public function getUserAgentInfo() {
				$user_agent = '';
				
				if(isset($_SERVER['HTTP_USER_AGENT'])) {
						$user_agent = $_SERVER['HTTP_USER_AGENT'];
				}
				
				return $user_agent;
		}
		
		/////////////////////////////////////////////////////////////////////
		// System-Typ auslesen.
		/////////////////////////////////////////////////////////////////////
		public function getSystemType() {
				$system_type = core()->getPostVar('system_type');
				
				switch($system_type) {
						case 'Android':
								break;
						case 'Apple':
								break;
						default:
								$system_type = 'Unknown';
								break;
				}
				
				return $system_type;
		}
}