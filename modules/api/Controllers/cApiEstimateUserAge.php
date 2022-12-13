<?php

class cApiEstimateUserAge {
		//////////////////////////////////////////////////////////////////////////////////
		// Verarbeitung
		//////////////////////////////////////////////////////////////////////////////////
		public function process() {
				//Controller can only be used by logged in users. Check access rights!
				$iApiAccessToken = new cApiAccessToken();
				$user_id = $iApiAccessToken->checkAccessRightsByRequest();
				
				//Get data if nothing was offered
				$iUserImages = new cUserImages();
				$image = $iUserImages->loadImage_WhereUserIsNotOwner_And_NotRatedByUser($user_id);
				
				//Ergebnis-Daten zusammenstellen
				$result_count = 0;
				$id = 0;
				$filename = '';
				
				if(false !== $image) {
						$result_count = 1;
						$id = $image['id'];
						
						//Build full url for image filename
						$filename = $image['filename'];
						$iApiUrl = new cApiUrl();
						$site_url = $iApiUrl->getSystemUrl();
						$filename = $site_url . $filename;
				};
				
				//Rückgabe zusammensetzen
				$retval = array(
						'result_count' => $result_count,
						'id' => $id,
						'filename' => $filename
				);
				cApiOutput::sendData($statuscode = 1, $retval);
				die;
		}
}