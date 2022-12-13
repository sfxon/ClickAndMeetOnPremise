<?php

//////////////////////////////////////////////////////////////////////////////////
// Abfragen, ob der User-Account gelöscht werden kann.
//////////////////////////////////////////////////////////////////////////////////
class cApiGetAccountDeleteStatus {
		var $errors = array();
		
		//////////////////////////////////////////////////////////////////////////////////
		// Verarbeitung
		//////////////////////////////////////////////////////////////////////////////////
		public function process() {
				//Controller can only be used by logged in users. Check access rights!
				$iApiAccessToken = new cApiAccessToken();
				$user_id = $iApiAccessToken->checkAccessRightsByRequest();
				
				$iAccount = new cAccount();
				$account_data = $iAccount->loadById($user_id);
				
				//Zeitraum prüfen.
				//Wenn die E-Mail Adresse in den letzten 4 Wochen geändert wurde, erlauben wir das Löschen des Accounts nicht..
				if(true === $iAccount->isLastMailChangeOlderThanWeeks($user_id, $week_count = 4)) {
						//Success. 
						$retval = array(
								'delete_status' => 'possible',
						);
						cApiOutput::sendData($statuscode = 1, $retval);
						die;
				}
						
				//If we are here, it is not possible.
				//Success. 
				$retval = array(
						'delete_status' => 'impossible'
				);
				cApiOutput::sendData($statuscode = 1, $retval);
				die;
		}
}

