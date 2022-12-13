<?php

class cDashboard extends cModule {
		var $template = 'maxis';
		var $cms_id = 0;
		var $contentData = '';
		var $siteUrl = '';
	
		//////////////////////////////////////////////////////////////////////////////////
		// Hook us into the system
		//////////////////////////////////////////////////////////////////////////////////
		public static function setExecutionalHooks() {
				//If the user is logged in..
				if(!isset($_SESSION['ws_user_id'])) {
						header('Location: http://' . cSite::loadSiteUrl(core()->get('site_id')) . 'index.php?s=cFrontendCm');
						die;
				}
				
				//We use the CMS module for output.
				cCMS::setExecutionalHooks();		
				
				$core = core();
				
				//Now set our own hooks below the CMS hooks.
				core()->setHook('cCore|process', 'process');
				core()->setHook('cRenderer|content', 'content');
				core()->setHook('cRenderer|end_page', 'footer');
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Verarbeitung..
		/////////////////////////////////////////////////////////////////////////////////
		public function process() {
				$errormessage = '';
				$action = core()->getGetVar('action');
				$errors = array();
				
				$this->initData();
				
				if($action == 'rate') {
						$this->rate();
				}
				
				if($action == 'rated') {
						$this->rated();
						return;
				}
				
				//Output - if user is not doing action!				
				$cms = core()->getInstance('cCMS');
				$content = $cms->loadContentDataByKey('FRONTEND_LOGIN');
				
				$iUserImages = new cUserImages();
				$image = $iUserImages->loadImage_WhereUserIsNotOwner_And_NotRatedByUser($_SESSION['ws_user_id']);
				
				//Load the CMS Entry for the login page.
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('TEMPLATE_URL', cCMS::loadTemplateUrl(core()->get('site_id')));
				$renderer->assign('TEMPLATE', $cms->getTemplate());
				$renderer->assign('ERRORMESSAGE', $errormessage);
				$renderer->assign('ERRORS', $errors);
				
				if(false === $image) {
						$tmp_content = $renderer->fetch('site/dashboard_no_images.html');
				} else {
						$renderer->assign('IMAGE', $image);
						$tmp_content = $renderer->fetch('site/dashboard.html');
				}
				
				$content['text'] = $tmp_content;
				$cms->setContentData($content);
		}
		
		/////////////////////////////////////////////////////////////////////////////
		// Wenn eine Einschätzung abgegeben wurde..
		/////////////////////////////////////////////////////////////////////////////
		public function rated() {
				$age_rating = (int)core()->getGetVar('age_rating');
				$image_id = (int)core()->getGetVar('image_id');
				
				//Load image data.
				$iUserImages = new cUserImages();
				$user_image_data = $iUserImages->loadByIdOnly((int)$image_id);
				
				//Check if this image exists and is not banned or blocked or something..
				if(!$this->checkIfImageIsValid($user_image_data)) {
						//Forward to rating page...
						header('Location: http://' . cSite::loadSiteUrl(core()->get('site_id')) . 'meine-seite?error=1');
						die;
				}
				
				//Check if this image belongs to this user - if so: do not allow rating
				if((int)$user_image_data['user_id'] == (int)$_SESSION['ws_user_id']) {
						//Forward to rating page...
						header('Location: http://' . cSite::loadSiteUrl(core()->get('site_id')) . 'meine-seite?error=2');
						die;
				}
				
				//Check if this user has already rated this image
				if(!$this->hasUserRatedThisImage((int)$_SESSION['ws_user_id'], (int)$image_id)) {
						//Forward to rating page...
						header('Location: http://' . cSite::loadSiteUrl(core()->get('site_id')) . 'meine-seite?error=4');
						die;
				}
				
				//Output - if user is not doing action!				
				$cms = core()->getInstance('cCMS');
				$content = $cms->loadContentDataByKey('FRONTEND_LOGIN');
				
				//$iUserImages = new cUserImages();
				//$image = $iUserImages->loadImage_WhereUserIsNotOwner_And_NotRatedByUser($_SESSION['ws_user_id']);
				
				//Load the CMS Entry for the login page.
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('TEMPLATE_URL', cCMS::loadTemplateUrl(core()->get('site_id')));
				$renderer->assign('TEMPLATE', $cms->getTemplate());
				//$renderer->assign('ERRORMESSAGE', $errormessage);
				//$renderer->assign('ERRORS', $errors);
				$renderer->assign('AGE_RATING', $age_rating);
				
				$renderer->assign('IMAGE', $user_image_data);
				$tmp_content = $renderer->fetch('site/dashboard_rated.html');
				
				$content['text'] = $tmp_content;
				$cms->setContentData($content);
		}
		
		/////////////////////////////////////////////////////////////////////////////
		// Einschätzung abgeben.
		/////////////////////////////////////////////////////////////////////////////
		public function rate() {
				$age_rating = (int)core()->getPostVar('age-rating');
				$image_id = (int)core()->getPostVar('image_id');

				//Check if age is valid..
				if($age_rating < 18) {
						$this->errors['age_to_low'] = 'Auf Tellface dürfen nur Personen ab 18 Jahren teilnehmen.<br />Wenn du denkst, die Person ist tatsächlich noch jünger, wende dich bitte an uns. Ansonsten wähle bitte ein Alter ab 18 Jahren.';
						return;
				}

				//Check if age is valid..
				if($age_rating > 150) {
						$this->errors['age_too_high'] = 'Das maximale Alter ist 150 Jahre.';
						return;
				}

				//Load image data.
				$iUserImages = new cUserImages();
				$user_image_data = $iUserImages->loadByIdOnly((int)$image_id);

				//Check if this image exists and is not banned or blocked or something..
				if(!$this->checkIfImageIsValid($user_image_data)) {
						//Forward to rating page...
						header('Location: http://' . cSite::loadSiteUrl(core()->get('site_id')) . 'meine-seite?error=1');
						die;
				}

				//Check if this image belongs to this user - if so: do not allow rating
				if((int)$user_image_data['user_id'] == (int)$_SESSION['ws_user_id']) {
						//Forward to rating page...
						header('Location: http://' . cSite::loadSiteUrl(core()->get('site_id')) . 'meine-seite?error=2');
						die;
				}

				//Check if this user has already rated this image
				if($this->hasUserRatedThisImage((int)$_SESSION['ws_user_id'], (int)$image_id)) {
						//Forward to rating page...
						header('Location: http://' . cSite::loadSiteUrl(core()->get('site_id')) . 'meine-seite?error=3');
						die;
				}

				//Save rating.
				$date_of_rating = date('Y-m-d H:i:s');		//MySQL conform datetime.

				$iUserImagesRating = new cUserImagesRating();
				$iUserImagesRating->create($_SESSION['ws_user_id'], $image_id, $age_rating, $date_of_rating);

				//Update average rating and rate-count for this image
				$this->updateAvgRatingAndRateCount($image_id, $age_rating, $user_image_data['rate_count'], $user_image_data['rating_sum']);


				header('Location: http://' . cSite::loadSiteUrl(core()->get('site_id')) . 'meine-seite?action=rated&age_rating=' . $age_rating . '&image_id=' . $image_id);
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
						
		/////////////////////////////////////////////////////////////////////////////
		// Init all needed data.
		/////////////////////////////////////////////////////////////////////////////
		public function initData() {
				$this->data = array(
						/*
						'mail' => '',
						'pass' => '',
						'keep_logged_in' => 0
						*/
				);
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the page footer.
		///////////////////////////////////////////////////////////////////
		public function footer() {
				$additional_output = 	
						"\n" . '<script src="//' . cSite::loadSiteUrl(core()->get('site_id')) . 'data/templates/' . $this->template . '/js/dashboard.jquery.js"></script>' .
						"\n";
				$renderer = core()->getInstance('cRenderer');
				$renderer->renderText($additional_output);
		}
}