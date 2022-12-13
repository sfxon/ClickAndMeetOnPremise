<?php

//////////////////////////////////////////////////////////////////////////////////
// Logout eines Users von der API.
//////////////////////////////////////////////////////////////////////////////////
class cApiLogout {
		//////////////////////////////////////////////////////////////////////////////////
		// Verarbeitung
		//////////////////////////////////////////////////////////////////////////////////
		public function process() {
				//Controller can only be used by logged in users. Check access rights!
				$iApiAccessToken = new cApiAccessToken();
				$user_id = $iApiAccessToken->checkAccessRightsByRequest();
				
				$access_token = core()->getPostVar('a');
				$refresh_token = core()->getPostVar('r');
				
				$iApiTokens = new cApiTokens();
				$iApiTokens->invalidateByTokensAndUserId($access_token, $refresh_token, $user_id);		//This is the logout..
				
				//Rückgabe zusammensetzen
				$retval = array(
						'status' => 'success'
				);
				cApiOutput::sendData($statuscode = 1, $retval);
				die;
		}
}