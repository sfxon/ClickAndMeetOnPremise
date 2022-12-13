<?php

//////////////////////////////////////////////////////////////////////////////////
// User Image über die API hochladen und Datenbankeintrag dazu erstellen.
// Erwartete Parameter:
// image_data	-> Base64 encodierte Zeichenkette mit den Bilddaten.
// age -> Alter größer 17 und kleiner 151
// image_type -> Bildtyp (aktuell wird nur jpg akzeptiert).
// user_message -> Textnachricht an den Bewerter -> darf auch eine leere Zeichenkette sein.
//////////////////////////////////////////////////////////////////////////////////
class cApiCreateUserImage {
		//////////////////////////////////////////////////////////////////////////////////
		// Verarbeitung
		//////////////////////////////////////////////////////////////////////////////////
		public function process() {
				//Controller can only be used by logged in users. Check access rights!
				$iApiAccessToken = new cApiAccessToken();
				$user_id = $iApiAccessToken->checkAccessRightsByRequest();

        //TODO: Test that image_data is set in POST!!!
				if(!isset($_POST['image_data'])) {
						cApiOutput::sendError($errorcode = 13, $errormessage = 'no image data');
						die;
				}

				$non_base64_image_data = base64_decode($_POST['image_data']);
				
				//Check image data.
				$retval = $this->validateImage($non_base64_image_data);
				
				if(true !== $retval) {
						cApiOutput::sendError($errorcode = 14, $errormessage = 'invalid image data: ' . $retval);
						die;
				}
				
				//Get additional values
				$image_format = core()->getPostVar('image_format');
        $age = (int)core()->getPostVar('age');
        $user_message = core()->getPostVar('user_message');
				
				//Check age
				if($age < 18 || $age > 150) {
						cApiOutput::sendError($errorcode = 15, $errormessage = 'invalid age');
						die;
				}
				
				//Save the file.
				$filename = $this->saveImageFile($user_id, $non_base64_image_data, '.jpg');
				
				if(false === $filename) {
						cApiOutput::sendError($errorcode = 16, $errormessage = 'Error while saving file');
						die;
				}

				//Create the database entry.
				$userImages = new cUserImages();						
				$userImages->create($user_id, $filename, $age, $user_message);
				
				//Rückgabe zusammensetzen
				$retval = array(
						'status' => 'success'
				);
				cApiOutput::sendData($statuscode = 1, $retval);
				die;
		}
		
		//////////////////////////////////////////////////////////////////////////////////
		// Bild validieren.
		// Bild muss ladbar sein.
		// Bild muss vom Typ jpg sein.
		// Bild muss ein Seitenverhältnis von 3/4 haben.
		//////////////////////////////////////////////////////////////////////////////////
		private function validateImage($img_data) {
				//Versuche, eine Instanz des Bildes zu erzeugen.
				$tmp = imagecreatefromstring($img_data);
				
				if (false === $tmp) {
						return -1;
				}
				
				//Versuche Mime-Typ zu ermitteln.
				$image_size = getimagesizefromstring($img_data);
				
				if(false === $image_size) {
						return -2;
				}
				
				//Teste Mime Typ
				if(!isset($image_size['mime'])) {
						return -3;
				}
				
				if($image_size['mime'] !== 'image/jpeg') {
						return -4;
				}
				
				//Teste Seitenverhältnis
				//Das Seitenverhältnis sollte 3/4 sein -> also hier in etwa 75 ergeben.
				//Wir erlauben aber eine Abweichung, weil diese vorkommen können - je nach eingesetztem Modell.
				//Selbst kleine Pixel-Abweichungen wirken sich hier sonst schon negativ aus und sorgen für Fehler.
				$seitenverhaeltnis = (int)($image_size[0] / $image_size[1] * 100);
				
				if($seitenverhaeltnis < 60 || $seitenverhaeltnis > 90) {
						return -5;
				}
				
				return true;
		}
		
		//////////////////////////////////////////////////////////////////////////////////
		// Bild speichern
		//////////////////////////////////////////////////////////////////////////////////
		private function saveImageFile($user_id, $data, $file_extension) {
				$ds = DIRECTORY_SEPARATOR;
				$storeFolder = 'data/usrimg';
				
				$filename = 'api-v1.0-' . $user_id . '.' . date('Y-m-d-H-i-s') . '.' . uniqid('', true);
				
				//Create folder
				$dst = $this->addPathAndCreate($storeFolder, '/' . date('Y'));		//Füge Ordner für Jahr hinzu.
				$dst = $this->addPathAndCreate($dst, '/' . date('m'));							//Füge Ordner für Monat hinzu
				$dst = $this->addPathAndCreate($dst, '/' . date('d'));							//Füge Ordner für Tag hinzu
				
				$dst .= '/';								//Add slash
				$dst .= $filename;					//Add filename
				$dst .= $file_extension;		//Add file extension
				
				//Speichern
				$tmp = file_put_contents($dst, $data);
				
				if(false === $tmp) {
						return false;
				}
				
				return $dst;
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Add path and create..
		/////////////////////////////////////////////////////////////////////////////////
		public function addPathAndCreate($path, $add) {
				$dst = $path . $add;
				
				if(!is_dir($dst)) {
						mkdir($dst);
				}
				
				//Check if creation was successfull..
				if(!is_dir($dst)) {
						cApiOutput::sendError($errorcode = 17, $errormessage = 'Error creating directory.');
						die;
				}
				
				return $dst;
		}
				
}