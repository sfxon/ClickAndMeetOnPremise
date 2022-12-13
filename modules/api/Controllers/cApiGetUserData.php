<?php

//////////////////////////////////////////////////////////////////////////////////
// User Image über die API aktualisieren in der Datenbank.
// Erwartete Parameter:
// image_id -> ID des Bildes!
// age -> Alter größer 17 und kleiner 151
// user_message -> Textnachricht an den Bewerter -> darf auch eine leere Zeichenkette sein.
//////////////////////////////////////////////////////////////////////////////////
class cApiGetUserData {
		//////////////////////////////////////////////////////////////////////////////////
		// Verarbeitung
		//////////////////////////////////////////////////////////////////////////////////
		public function process() {
				//Controller can only be used by logged in users. Check access rights!
				$iApiAccessToken = new cApiAccessToken();
				$user_id = $iApiAccessToken->checkAccessRightsByRequest();
				
				$user_data = cAccount::loadById($user_id);
				
				if(false === $user_data) {
						cApiOutput::sendError($errorcode = 22, $errormessage = 'user not found');
						die;
				}
				
				//Calculate if the mail address has been changed recently.
				$mail_account_changed_recently = false;
				$mail_account_changed_recently_date = '';
				$iAccount = new cAccount();
				
				if(false === $iAccount->isLastMailChangeOlderThanWeeks($user_id, $week_count = 4)) {
						$mail_account_changed_recently = true;
						//$mail_account_changed_recently_date_formated = date('d.m.Y', strtotime($user_data['new_mail_request_on'])) . ' um ' . date('H:i', strtotime($user_data['new_mail_request_on'])) . ' Uhr';
						$mail_account_changed_recently_date = $user_data['new_mail_request_on'];
				}
				
				//Rückgabe zusammensetzen
				$retval = array(
						'status' => 'success',
						'email' => $user_data['email'],
						'mail_account_changed_recently' => $mail_account_changed_recently,
						'mail_account_changed_recently_date' => $mail_account_changed_recently_date
				);
				cApiOutput::sendData($statuscode = 1, $retval);
				die;
		}
}