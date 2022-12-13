<?php

//////////////////////////////////////////////////////////////////////////////////
// User Image über die API hochladen und Datenbankeintrag dazu erstellen.
// Erwartete Parameter:
// image_data	-> Base64 encodierte Zeichenkette mit den Bilddaten.
// age -> Alter größer 17 und kleiner 151
// image_type -> Bildtyp (aktuell wird nur jpg akzeptiert).
// user_message -> Textnachricht an den Bewerter -> darf auch eine leere Zeichenkette sein.
//////////////////////////////////////////////////////////////////////////////////
class cApiGetUserImageDetail {
		//////////////////////////////////////////////////////////////////////////////////
		// Verarbeitung
		//////////////////////////////////////////////////////////////////////////////////
		public function process() {
				//Controller can only be used by logged in users. Check access rights!
				$iApiAccessToken = new cApiAccessToken();
				$user_id = $iApiAccessToken->checkAccessRightsByRequest();
				
				//Load image data
				$user_image_id = (int)core()->getGetVar('image_id');
				
				$userImages = new cUserImages();
				$user_image = $userImages->loadById($user_image_id, $user_id);
				
				if(false === $user_image) {
						cApiOutput::sendError($errorcode = 18, $errormessage = 'Error loading image by image_id and user_id.');
						die;
				}
				
				//Rückgabe zusammensetzen
				$retval = array(
						'status' => 'success',
						'user_image' => $user_image
				);
				cApiOutput::sendData($statuscode = 1, $retval);
				die;
		}
}