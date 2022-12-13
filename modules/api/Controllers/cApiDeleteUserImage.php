<?php

//////////////////////////////////////////////////////////////////////////////////
// User Image über die API aktualisieren in der Datenbank.
// Erwartete Parameter:
// image_id -> ID des Bildes!
// age -> Alter größer 17 und kleiner 151
// user_message -> Textnachricht an den Bewerter -> darf auch eine leere Zeichenkette sein.
//////////////////////////////////////////////////////////////////////////////////
class cApiDeleteUserImage {
		//////////////////////////////////////////////////////////////////////////////////
		// Verarbeitung
		//////////////////////////////////////////////////////////////////////////////////
		public function process() {
				//Controller can only be used by logged in users. Check access rights!
				$iApiAccessToken = new cApiAccessToken();
				$user_id = $iApiAccessToken->checkAccessRightsByRequest();
				
				//Get additional values
				$image_id = (int)core()->getPostVar('image_id');
				
				//Check that image belongs to user..
				$iUserImages = new cUserImages();
				$image_data = $iUserImages->loadById($image_id, $user_id);
				
				if(false == $image_data) {
						cApiOutput::sendError($errorcode = 21, $errormessage = 'image not found');
						die;
				}
				
				unlink($image_data['filename']);		//Bild vom Server löschen
				$iUserImages->deleteImage($image_id, $user_id);		//Datenbank-Eintrag löschen.
				
				//Rückgabe zusammensetzen
				$retval = array(
						'status' => 'success'
				);
				cApiOutput::sendData($statuscode = 1, $retval);
				die;
		}
}