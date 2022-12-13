<?php
//////////////////////////////////////////////////////////////////////////////////////
// Daten abrufen.
// 07.12.2020, Steve Krämer
// Parameter:
//	1. index	-> Offset of the sorted database query.
//////////////////////////////////////////////////////////////////////////////////////
class cApiGetUserImagesList {
		//////////////////////////////////////////////////////////////////////////////////
		// Verarbeitung
		//////////////////////////////////////////////////////////////////////////////////
		public function process() {
				error_reporting(E_ALL ^ E_DEPRECATED);		//Disable deprecated warnings here!
				
				//Controller can only be used by logged in users. Check access rights!
				$iApiAccessToken = new cApiAccessToken();
				$user_id = $iApiAccessToken->checkAccessRightsByRequest();
				
				//Gesamtzahl an Bildern laden.
				$iUserImages = new cUserImages();
				$total_count = $iUserImages->countUserImagesTotal($user_id);
				
				$index = (int)core()->getPostVar('index');
				$count = 6;
				
				if($index == 0) {
						$count = 5;
				}
				
				$user_images = array();
				
				if($total_count > 0) {
						$user_images = $this->loadUserImagesFrom($user_id, $index, $count);
						//$user_images = $this->loadUserImages();
				}
				
				if(is_array($user_images)) {
						foreach($user_images as $tmp_index => $img) {
								$user_images[$tmp_index]['uniqid'] = uniqid("", true);
						}
				}
				
				//HTML laden.
				$site_id = core()->get('site_id');							//ID of site (url based)
				$siteData = cSite::loadSiteData($site_id);	//Load URL by site id
				$templateUrl = cCMS::loadTemplateUrl($site_id);
				$template = 'api';
				
				$core = core();
			
				$renderer = core()->getInstance('cRenderer');
				$renderer->assign('SITE_URL', $siteData['default_protocol'] . '://' . $siteData['url'] . $siteData['path']);
				$renderer->assign('TEMPLATE_URL', $siteData['default_protocol'] . ':' . $templateUrl);
				$renderer->assign('TOTAL_COUNT', (int)$total_count);
				$renderer->assign('TEMPLATE', $template);
				$renderer->setTemplate($template);
				$renderer->assign('USER_IMAGES', $user_images);
				
				if($index == 0) {
						$html = $renderer->fetch('site/api_get_user_images.html');
				} else {
						$html = $renderer->fetch('site/api_get_user_images_more_images.html');
				}
				
				$result_count = 0;
				
				if(is_array($user_images)) {
						$result_count = count($user_images);
				}
				
				//Rückgabe zusammensetzen
				$retval = array(
						'result_count' => count($user_images),
						'total_count' => $total_count,
						//'user_images' => $user_images
						'html' => $html
						
				);
				cApiOutput::sendData($statuscode = 1, $retval);
				die;
		}
		
		/////////////////////////////////////////////////////////////////////////////
		// Load user images
		/////////////////////////////////////////////////////////////////////////////
		public function loadUserImagesFrom($user_id, $index, $count) {
				$userImages = new cUserImages();
				$user_images = $userImages->loadUserImagesFrom($user_id, $index, $count);
				
				return $user_images;
		}
}