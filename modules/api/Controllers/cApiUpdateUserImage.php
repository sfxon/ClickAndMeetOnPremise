<?php

//////////////////////////////////////////////////////////////////////////////////
// User Image über die API aktualisieren in der Datenbank.
// Erwartete Parameter:
// image_id -> ID des Bildes!
// age -> Alter größer 17 und kleiner 151
// user_message -> Textnachricht an den Bewerter -> darf auch eine leere Zeichenkette sein.
//////////////////////////////////////////////////////////////////////////////////
class cApiUpdateUserImage {
		//////////////////////////////////////////////////////////////////////////////////
		// Verarbeitung
		//////////////////////////////////////////////////////////////////////////////////
		public function process() {
				//Controller can only be used by logged in users. Check access rights!
				$iApiAccessToken = new cApiAccessToken();
				$user_id = $iApiAccessToken->checkAccessRightsByRequest();
				
				//Get additional values
				$image_id = (int)core()->getPostVar('image_id');
        $age = (int)core()->getPostVar('age');
        $user_message = core()->getPostVar('user_message');
				
				//Check that image belongs to user..
				$iUserImages = new cUserImages();
				$image_data = $iUserImages->loadById($image_id, $user_id);
				
				if(false == $image_data) {
						cApiOutput::sendError($errorcode = 19, $errormessage = 'image not found');
						die;
				}
				
				//Check age
				if($age < 18 || $age > 150) {
						cApiOutput::sendError($errorcode = 20, $errormessage = 'invalid age');
						die;
				}
								
				$iUserImages->updateAgeAndMessage($image_id, $user_id, $age, $user_message);
				
				//Rückgabe zusammensetzen
				$retval = array(
						'status' => 'success'
				);
				cApiOutput::sendData($statuscode = 1, $retval);
				die;
		}
}