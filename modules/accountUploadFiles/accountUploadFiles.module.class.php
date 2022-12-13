<?php

class cAccountUploadFiles extends cModule {
		var $template = 'tellface';
		var $cms_id = 0;
		var $contentData = '';
		var $siteUrl = '';
	
		//////////////////////////////////////////////////////////////////////////////////
		// Hook us into the system
		//////////////////////////////////////////////////////////////////////////////////
		public static function setExecutionalHooks() {
				//If the user is logged in..
				if(!isset($_SESSION['ws_user_id'])) {
						header('Location: http://' . cSite::loadSiteUrl(core()->get('site_id')) . 'mein-logo-shop.html');
						die;
				}
				
				//We use the CMS module for output.
				cCMS::setExecutionalHooks();		
				
				$core = core();
				
				//Now set our own hooks below the CMS hooks.
				core()->setHook('cCore|process', 'process');
				core()->setHook('cRenderer|content', 'content');
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Verarbeitung..
		/////////////////////////////////////////////////////////////////////////////////
		public function process() {
				$ds = DIRECTORY_SEPARATOR;
				$storeFolder = 'data/usrimg';
				$age = (int)core()->getPostVar('mv_age');
				$message = strip_tags(core()->getPostVar('mv_message'));
				
				//Check age
				if($age < 18 || $age > 300) {
						$this->dieAndOutputJson(
									$status = 'error', 
									$status_id = 1, 
									$message = 'Fehler: Das angegebene Alter ist ungültig in ' . __FILE__ . ', Zeile: ' . __LINE__
							);
							die;
				}
				
 				//Check file upload..
				if (!empty($_FILES)) {
						$tempFile = $_FILES['file']['tmp_name']; 
						$filename = $_SESSION['ws_user_id'] . '.' . date('Y-m-d-H-i-s') . '.' . uniqid('', true);         
      			//$targetPath =  $storeFolder . $ds;
     				//$targetFile =  $targetPath. $filename;
						
						$finfo = finfo_open(FILEINFO_MIME_TYPE);
						$info = finfo_file($finfo, $tempFile);
						$file_extension = '';
						
						switch($info) {
								case 'image/jpeg':
										$file_extension = '.jpg';
										break;
						}
						
						if($file_extension == '') {
								//Output error, which the ... api understands..
								$this->dieAndOutputJson(
										$status = 'error', 
										$status_id = 1, 
										$message = 'Unbekanntes Dateiformat ' . print_r($info, true) . ' in ' . __FILE__ . ', Zeile: ' . __LINE__
								);
								die;
						}
						
						//Move file, and output success..
						$dst = $this->addPathAndCreate($storeFolder, '/' . date('Y'));		//Füge Ordner für Jahr hinzu.
						$dst = $this->addPathAndCreate($dst, '/' . date('m'));							//Füge Ordner für Monat hinzu
						$dst = $this->addPathAndCreate($dst, '/' . date('d'));							//Füge Ordner für Tag hinzu
						
						$dst .= '/';								//Add slash
						$dst .= $filename;					//Add filename
						$dst .= $file_extension;		//Add file extension
						
						//Move file..
						$status = move_uploaded_file($_FILES["file"]["tmp_name"], $dst);
						
						if(false === $status) {
								//Output error, which the ... api understands..
								$this->dieAndOutputJson(
										$status = 'error', 
										$status_id = 1, 
										$message = 'Fehler beim Datei-Upload ' . __FILE__ . ', Zeile: ' . __LINE__
								);
								die;
						}
						
						$userImages = new cUserImages();						
						$userImages->create($_SESSION['ws_user_id'], $dst, $age, $message);
						
						$this->dieAndOutputJson(
								$status = 'success', 
								$status_id = 1, 
								$message = cCMS::loadTemplateUrl(core()->get('site_id')) . 'meine-fotos/?success=1'
						);
						die;
				}
				
				$this->dieAndOutputJson(
						$status = 'error', 
						$status_id = 1, 
						$message = 'Fehler beim Datei-Upload ' . __FILE__ . ', Zeile: ' . __LINE__
				);
				die;
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
						$this->dieAndOutputJson(
								$status = 'error', 
								$status_id = 1, 
								$message = 'Server-Fehler. Der Pfad ' . $dst . ' konnte nicht angelegt werden in ' . __FILE__ . ', Zeile: ' . __LINE__
						);
				}
				
				return $dst;
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Add path and create..
		/////////////////////////////////////////////////////////////////////////////////
		public function dieAndOutputJson($status, $status_id, $message) {
				$array = array(
						'status' => $status,
						'status_id' => $status_id,
						'message' => $message
				);
				
				$array = json_encode($array, JSON_PRETTY_PRINT);
				echo $array;
				die;
		}
		
}