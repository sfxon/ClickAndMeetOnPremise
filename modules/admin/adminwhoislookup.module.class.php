<?php

class cAdminwhoislookup extends cModule {
		var $template = 'admin';
		var $navbar_title = TEXT_MODULE_TITLE_ADMINWHOISLOOKUP;
		var $navbar_id = 0;
		var $errors = array();
		var $errors_description = array();
		var $results = array();
		
		//////////////////////////////////////////////////////////////////////////////////
		// Hook us into the system
		//////////////////////////////////////////////////////////////////////////////////
		public static function setExecutionalHooks() {
				//If the user is not logged in..
				if(!isset($_SESSION['user_id'])) {
						header('Location: index.php/account');
						die;
				}
				
				//check the rights..
				if(false === cAccount::adminrightCheck('cAdminwhoislookup', 'USE_MODULE', (int)$_SESSION['user_id'])) {
						header('Location: index.php?s=cAdmin&error=198');
						die;
				}
				
				//We use the Admin module for output.
				cAdmin::setSmallBodyExecutionalHooks();	
				
				//Now set our own hooks below the CMS hooks.
				$core = core();
				core()->setHook('cCore|process', 'process');
				core()->setHook('cRenderer|content', 'content');
		}
	
	
		///////////////////////////////////////////////////////////////////
		// processData
		///////////////////////////////////////////////////////////////////
		function process() {
				$this->action = core()->getGetVar('action');
				$cAdmin = core()->getInstance('cAdmin');
				$cAdmin->appendBreadcrumb(TEXT_MODULE_TITLE_ADMINWHOISLOOKUP, 'index.php?s=cAdminwhoislookup');
				
				switch($this->action) {
						case 'csv_upload_process':
								$this->initData();
								$this->processCSVUpload();
								$this->data['url'] = 'index.php?s=cAdminwhoislookup&amp;action=csv_upload_process';
								$cAdmin->appendBreadcrumb(TEXT_ADMINWHOISLOOKUP_CSV_UPLOAD, '');
								$this->navbar_title = TEXT_ADMINWHOISLOOKUP_CSV_UPLOAD;
								break;
						case 'csv_upload':
								$this->initData();
								$this->data['url'] = 'index.php?s=cAdminwhoislookup&amp;action=csv_upload_process';
								$cAdmin->appendBreadcrumb(TEXT_ADMINWHOISLOOKUP_CSV_UPLOAD, '');
								$this->navbar_title = TEXT_ADMINWHOISLOOKUP_CSV_UPLOAD;
								break;
						/*
						case 'edit':
								$this->initData();
								$this->getContent();
								$this->data['url'] = 'index.php?s=cAdminwhoislookup&amp;action=update&amp;id=' . (int)$this->data['data']['id'];
								$cAdmin->appendBreadcrumb(TEXT_ADMINWHOISLOOKUP_EDIT_CONTENT, '');
								$this->navbar_title = TEXT_ADMINWHOISLOOKUP_EDIT_CONTENT;
								break;
						/*		
						case 'update':
								$this->initData();
								$this->getContent();
								$this->update();
								$cAdmin->appendBreadcrumb(TEXT_ADMINBUSINESSSECTORS_EDIT_CONTENT, '');
								$this->navbar_title = TEXT_ADMINBUSINESSSECTORS_EDIT_CONTENT;
								break;
						*/		
						case 'single_process':
								$this->lookup();
								//$cAdmin->appendBreadcrumb(TEXT_ADMINWHOISLOOKUP_NEW_CONTENT, '');
								//$this->navbar_title = TEXT_ADMINWHOISLOOKUP_NEW_CONTENT;
								//break;
						
						case 'single':
						default:
								$this->initData();
								$this->data['url'] = 'index.php?s=cAdminwhoislookup&amp;action=single_process';
								//$cAdmin->appendBreadcrumb(TEXT_ADMINWHOISLOOKUP_NEW_CONTENT, '');
								//$this->navbar_title = TEXT_ADMINWHOISLOOKUP_NEW_CONTENT;
								break;
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Check..
		///////////////////////////////////////////////////////////////////
		private function lookup() {
				$ip = core()->getPostVar('ip');
				
				if(empty(trim($ip))) {
						$this->errors = 'Bitte geben Sie eine IP Adresse ein.';
				}
				
				$this->whois($ip);
				
				var_dump($this->results);
		}
		
		///////////////////////////////////////////////////////////////////
		// Whois.
		///////////////////////////////////////////////////////////////////
		function whois($ip) {
				$result = array();
				$country = '';
				
				//remove port..
				if(strpos($ip, ':')) {
						$ip = explode(':', $ip);
						$ip = $ip[0];
				}
			
				exec('whois ' . $ip, $result);
				
				//var_dump($result);
				
				//echo '<hr />';
				
				foreach($result as $res) {
						$startposition = strpos($res, 'country:');
						
						if($startposition < 5 && $startposition !== false) {
								if(strlen($country) > 0) {
										$country .= ', ';
								}
								
								$country .= substr($res, $startposition + 8);
								break;
						}
				}
				
				$this->results[] = array(
						'ip' => $ip,
						'country' => $country
				);
		}
				
				
		
		///////////////////////////////////////////////////////////////////
		// Process file upload.
		///////////////////////////////////////////////////////////////////
		function processCSVUpload() {
				//Check CSV File..
				/*if(false === $this->checkCSVFile()) {
						return false;
				}*/
				
				//Process the file line by line.
				$fp = fopen($_FILES['csv_file']['tmp_name'], 'r');
				
				if(false === $fp) {
						$this->errors[] = 191;
						return false;
				}
				
				$imported_lines = 0;
				
				while($tmp = fgetcsv($fp, 1000, ';')) {
						if(count($tmp) == 0) {
								continue;
						}
						
						/*if(count($tmp) != 3) {		//Here we skip lines, that are too long. We already should have checked this file..
								continue;	
						}*/
						
						$ip = $tmp[0];
						
						$this->whois($ip);
						/*$data['title'] = $tmp[1];
						$data['status'] = (int)$tmp[2];
						
						if($data['status'] != 1) {
								$data['status'] = 0;
						}
						*/
						
						//Check if an entry with this id exists.
						/*if(0 !== $data['id'] && NULL !== cBusinesssectors::loadById($data['id'])) {
								//Update existing entry.
								cBusinesssectors::updateInDB($data['id'], $data);
						} else {
								//Create new entry.
								cBusinesssectors::createInDB($data);
						}
						
						$imported_lines++;*/
				}
				
				//header('Location: index.php?s=cAdminbusinesssectors&action=csv_upload&success=64&imported_lines=' . $imported_lines);
				//die;
							
								
		}
		
		///////////////////////////////////////////////////////////////////
		// Check csv file.
		///////////////////////////////////////////////////////////////////
		/*
		function checkCSVFile() {
				if(!isset($_FILES['csv_file']) || !isset($_FILES['csv_file']['tmp_name']) || empty($_FILES['csv_file']['tmp_name'])) {
						$this->errors[] = 190;
						return false;
				}
				
				$fp = fopen($_FILES['csv_file']['tmp_name'], 'r');
				
				if(false === $fp) {
						$this->errors[] = 191;
						return false;
				}
				
				$line = 0;
				
				while($tmp = fgetcsv($fp, 1000, ';')) {
						$line++;
						
						//Skip empty lines..
						if(count($tmp) == 0) {
								continue;
						}
						
						//If there are more than 3 columns in this row..
						if(count($tmp) != 3) {
								fclose($fp);
								$this->errors[] = 192;
								$this->errors_description[192] = 'Fehler in Zeile ' . $line . '. Zu viele Elemente wurden gefunden. Befindet sich eine Semikolon im Fließtext?';
								return false;
						}
				}
				
				fclose($fp);
				
				return true;
		}
		*/
		
		///////////////////////////////////////////////////////////////////
		// Prepare data for the editor.
		///////////////////////////////////////////////////////////////////
		function initData() {
				//$this->data['data']['id'] = 0;
				$this->data['data']['ip'] = '';
				//$this->data['data']['status'] = '';
		}
		
		///////////////////////////////////////////////////////////////////
		// getList
		///////////////////////////////////////////////////////////////////
		/*
		function getList() {
				$this->data['list'] = $this->loadList();
		}
		*/
		///////////////////////////////////////////////////////////////////
		// Suche
		///////////////////////////////////////////////////////////////////
		function search() {
				die( 'search' );
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the page content.
		///////////////////////////////////////////////////////////////////
		public function content() {
				switch($this->action) {
						case 'csv_upload_process':
						case 'csv_upload':
								$this->drawEditorCSVUpload();
								break;
						/*case 'new':
								$this->drawEditor();
								break;
						case 'edit':
								$this->drawEditor();
								break;*/
						default:
								$this->drawList();
								break;
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw editor for csv upload.
		///////////////////////////////////////////////////////////////////
		function drawEditorCSVUpload() {
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$renderer->assign('ERRORS', $this->errors);
				$renderer->assign('ERRORS_DESCRIPTION', $this->errors_description);
				$renderer->assign('SUCCESS', '');
				
				$renderer->assign('RESULTS', $this->results);
				
				/*$success = (int)core()->getGetVar('success');
				$imported_lines = (int)core()->getGetVar('imported_lines');
				$renderer->assign('SUCCESS', $success);
				$renderer->assign('IMPORTED_LINES', $imported_lines);*/
				
				$renderer->render('site/adminwhoislookup/editor_csv_upload.html');
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the editor.
		///////////////////////////////////////////////////////////////////
		/*function drawEditor() {
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$renderer->render('site/adminbusinesssectors/editor.html');
		}*/
		///////////////////////////////////////////////////////////////////
		// Draw the list view.
		///////////////////////////////////////////////////////////////////
		function drawList() {
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$renderer->assign('RESULTS', $this->results);
				$renderer->render('site/adminwhoislookup/list.html');
		}
		
		///////////////////////////////////////////////////////////////////
		// update an entry
		///////////////////////////////////////////////////////////////////
		/*public function update() {
				$id = (int)core()->getGetVar('id');
				
				if(0 == $id) {
						header('Location: index.php?s=cAdminbusinesssectors&error=187');
						die;
				}
				
				$data['id'] = $id;
				$data['title'] = core()->getPostVar('title');
				$data['status'] = (int)core()->getPostVar('status');
				
				if(NULL === cBusinesssectors::loadById((int)$data['id'])) {
						header('Location: index.php?s=cAdminbusinesssectors&error=187');
						die;
				}
				
				$id = $this->save($data);
				
				if(empty($id)) {
						header('Location: index.php?s=cAdminbusinesssectors&error=188');
						die;
				}
	
				header('Location: index.php?s=cAdminbusinesssectors&action=edit&id=' . $id . '&success=62');
				die;
		}*/
		
		///////////////////////////////////////////////////////////////////
		// Crate a content entry.
		///////////////////////////////////////////////////////////////////
		/*function create() {
				$data['id'] = 0;
				$data['title'] = core()->getPostVar('title');
				$data['status'] = (int)core()->getPostVar('status');
				
				$id = $this->save($data);
				
				if(empty($id)) {
						header('Location: index.php?s=cAdminbusinesssectors&error=189');
						die;
				}
	
				header('Location: index.php?s=cAdminbusinesssectors&action=edit&id=' . $id . '&success=63');
				die;
		}*/

		///////////////////////////////////////////////////////////////////
		// Loads a list of content entries.
		///////////////////////////////////////////////////////////////////
		/*public function loadList() {
				return cBusinesssectors::loadList();
		}*/
	
		/////////////////////////////////////////////////////////////////////////////////
		// Save data in database.
		/////////////////////////////////////////////////////////////////////////////////
		/*public function save($data) {
				$id = (int)$data['id'];
		
				if(0 === $id) {
						return cBusinesssectors::createInDB($data);
				}
				
				cBusinesssectors::updateInDB($id, $data);
				
				return $data['id'];
		}*/
		
		/////////////////////////////////////////////////////////////////////////////////
		// Loads a content entry.
		/////////////////////////////////////////////////////////////////////////////////
		/*public function getContent() {
				$id = (int)core()->getGetVar('id');
				$this->data['data'] = cBusinesssectors::loadById($id);
		}*/
}
?>