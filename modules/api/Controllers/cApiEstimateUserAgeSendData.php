<?php

class cApiEstimateUserAgeSendData {
		//////////////////////////////////////////////////////////////////////////////////
		// Verarbeitung
		//////////////////////////////////////////////////////////////////////////////////
		public function process() {
    		//Controller can only be used by logged in users. Check access rights!
				$iApiAccessToken = new cApiAccessToken();
				$user_id = $iApiAccessToken->checkAccessRightsByRequest();


        $age_rating = (int)core()->getPostVar('age');
				$image_id = (int)core()->getPostVar('image_id');

				//Check if age is valid..
				if($age_rating < 18) {
						cApiOutput::sendError($errorcode = 8, $errormessage = 'User-Input of age is too low');
						die;
				}

				//Check if age is valid..
				if($age_rating > 150) {
						cApiOutput::sendError($errorcode = 9, $errormessage = 'User-Input of age is too high');
						die;
				}

				//Load image data.
				$iUserImages = new cUserImages();
				$user_image_data = $iUserImages->loadByIdOnly((int)$image_id);

				//Check if this image exists and is not banned or blocked or something..
				if(!$this->checkIfImageIsValid($user_image_data)) {
						cApiOutput::sendError($errorcode = 10, $errormessage = 'invalid');
						die;
				}

				//Check if this image belongs to this user - if so: do not allow rating
				if((int)$user_image_data['user_id'] == (int)$user_id) {
						cApiOutput::sendError($errorcode = 11, $errormessage = 'invalid');
						die;
				}

				//Check if this user has already rated this image
				if($this->hasUserRatedThisImage((int)$user_id, (int)$image_id)) {
						cApiOutput::sendError($errorcode = 12, $errormessage = 'invalid');
						die;
				}

				//Save rating.
				$date_of_rating = date('Y-m-d H:i:s');		//MySQL conform datetime.

				$iUserImagesRating = new cUserImagesRating();
				$iUserImagesRating->create($user_id, $image_id, $age_rating, $date_of_rating);

				//Update average rating and rate-count for this image
				$this->updateAvgRatingAndRateCount($image_id, $age_rating, $user_image_data['rate_count'], $user_image_data['rating_sum']);
				
				//Fantastic - the image was rated.
				//No send back some information about the image to please our user. :)
				$retval = array(
						'comment_for_visitors' => $user_image_data['comment_for_visitors'],
						'age_on_image' => $user_image_data['age_on_image']
				);
				
				cApiOutput::sendData($statuscode = 1, $retval);
				die;
		}

		/////////////////////////////////////////////////////////////////////////////
		// Durchschnittliches Rating und Rate-Count in Datenbank aktualisieren.
		/////////////////////////////////////////////////////////////////////////////
		public function updateAvgRatingAndRateCount($image_id, $age_rating, $rate_count, $rating_sum) {
				$rate_count += 1;
				$rating_sum += $age_rating;

				$average_rating = floor($rating_sum / $rate_count);

				$iUserImages = new cUserImages();
				$iUserImages->updateRateCountAndRatingSumAndAverageRatingById($image_id, $rate_count, $rating_sum, $average_rating);
		}
	
		/////////////////////////////////////////////////////////////////////////////
		// Prüfen, ob das Bild valide ist..
		/////////////////////////////////////////////////////////////////////////////
		public function checkIfImageIsValid($user_image_data) {
				if(false === $user_image_data) {
						return false;
				}

				if($user_image_data['is_active'] != 1) {
						return false;
				}

				if($user_image_data['is_banned'] == 1) {
						return false;
				}

				return true;			
		}

		/////////////////////////////////////////////////////////////////////////////
		// Prüfen, ob dieser Benutzer dieses Bild schon bewertet hat..
		/////////////////////////////////////////////////////////////////////////////
		public function hasUserRatedThisImage($user_id, $image_id) {
				$iUserImagesRating = new cUserImagesRating();
				$entry = $iUserImagesRating->loadByUserIdAndUserImageId($user_id, $image_id);

				if(false !== $entry) {
						return true;
				}

				return false;
		}
}