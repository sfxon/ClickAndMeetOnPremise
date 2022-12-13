<?php

class cAdminScripts extends cModule {
		var $template = 'admin';
		var $navbar_title = TEXT_MODULE_TITLE_ADMINSCRIPTS;
		var $navbar_id = 0;
		var $errors = array();
		var $errors_description = array();
		var $results = array();
		var $data;
		
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
				if(false === cAccount::adminrightCheck('cAdminScripts', 'USE_MODULE', (int)$_SESSION['user_id'])) {
						header('Location: index.php?s=cAdmin&error=199');
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
				$cAdmin->appendBreadcrumb(TEXT_MODULE_TITLE_ADMINSCRIPTS, 'index.php?s=cAdminScripts');
				
				switch($this->action) {
						case 'delete_confirm':
								$this->initData();
								$this->getContent();
								$this->delete();
								break;
						case 'delete':
								$this->initData();
								$this->getContent();
								$this->data['url'] = 'index.php?s=cAdminScripts&amp;action=delete_confirm&amp;id=' . (int)$this->data['data']['id'];
								$cAdmin->appendBreadcrumb(TEXT_ADMINSCRIPTS_DELETE_CONTENT, '');
								$this->navbar_title = TEXT_ADMINSCRIPTS_DELETE_CONTENT;
								break;
						case 'edit':
								$this->initData();
								$this->getContent();
								$this->data['url'] = 'index.php?s=cAdminScripts&amp;action=update&amp;id=' . (int)$this->data['data']['id'];
								$cAdmin->appendBreadcrumb(TEXT_ADMINSCRIPTS_EDIT_CONTENT, '');
								$this->navbar_title = TEXT_ADMINSCRIPTS_EDIT_CONTENT;
								break;
						case 'update':
								$this->initData();
								$this->getContent();
								$this->update();
								$cAdmin->appendBreadcrumb(TEXT_ADMINSCRIPTS_EDIT_CONTENT, '');
								$this->navbar_title = TEXT_ADMINSCRIPTS_EDIT_CONTENT;
								break;		
						case 'new':
								$this->initData();
								$this->data['url'] = 'index.php?s=cAdminScripts&amp;action=create';
								$cAdmin->appendBreadcrumb(TEXT_ADMINSCRIPTS_NEW_CONTENT, '');
								$this->navbar_title = TEXT_ADMINSCRIPTS_NEW_CONTENT;
								break;
						case 'create':
								$this->initData();
								$this->getContent();
								$this->create();
								$cAdmin->appendBreadcrumb(TEXT_ADMINSCRIPTS_NEW_CONTENT, '');
								$this->navbar_title = TEXT_ADMINSCRIPTS_NEW_CONTENT;
								break;
						default:
								$this->getList();
								$cAdmin->appendBreadcrumb(TEXT_ADMINSCRIPTS_LIST, '');
								$this->navbar_title = TEXT_ADMINSCRIPTS_LIST;
								break;
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Delete an entry.
		///////////////////////////////////////////////////////////////////
		function delete() {
				$id = (int)core()->getGetVar('id');
				
				if(0 == $id) {
						header('Location: index.php?s=cAdminScripts&error=200');
						die;
				}
				
				$data['id'] = $id;
				
				if(NULL === cScripts::loadById((int)$data['id'])) {
						header('Location: index.php?s=cAdminScripts&error=200');
						die;
				}
				
				cScripts::deleteFromDB($id);
				
				header('Location: index.php?s=cAdminScripts&success=201');
				die;
		}
				
		
		///////////////////////////////////////////////////////////////////
		// Prepare data for the editor.
		///////////////////////////////////////////////////////////////////
		function initData() {
				$this->data['data']['id'] = 0;
				$this->data['data']['title'] = '';
				$this->data['data']['status'] = 0;
				$this->data['data']['scripts_contents_id'] = 0;
				$this->data['data']['sort_order'] = 0;
				$this->data['data']['initial_processes_id'] = 0;
				$this->data['data']['show_edit_customers_data_button'] = 0;
				$this->data['data']['show_on_customers_screen'] = 0;
				
				$this->loadLayoutList();
		}
		
		///////////////////////////////////////////////////////////////////
		// getList
		///////////////////////////////////////////////////////////////////
		function getList() {
				$this->data['list'] = $this->loadList();
		}
		
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
						case 'delete':
								$this->drawDeleteConfirmDialog();
								break;
						case 'new':
								$this->drawEditor();
								break;
						case 'edit':
								$this->drawEditor();
								break;
						default:
								$this->drawList();
								break;
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw delete confirm dialog.
		///////////////////////////////////////////////////////////////////
		public function drawDeleteConfirmDialog() {
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$renderer->render('site/AdminScripts/delete_confirm_dialog.html');
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the editor.
		///////////////////////////////////////////////////////////////////
		function drawEditor() {
				$scripts_processes_list = array();
				
				if($this->data['data']['id'] != 0) {
						$scripts_processes_list = cScriptsProcesses::loadListByScriptsId($this->data['data']['id']);
				}
						
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$renderer->assign('SCRIPTS_PROCESSES_LIST', $scripts_processes_list);
				$renderer->render('site/AdminScripts/editor.html');
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the list view.
		///////////////////////////////////////////////////////////////////
		function drawList() {
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$renderer->assign('RESULTS', $this->results);
				$renderer->assign('INFO_MESSAGES', array());
				$renderer->assign('SUCCESS_MESSAGES', array());
				$renderer->render('site/AdminScripts/list.html');
		}
		
		///////////////////////////////////////////////////////////////////
		// update an entry
		///////////////////////////////////////////////////////////////////
		public function update() {
				$id = (int)core()->getGetVar('id');
				
				if(0 == $id) {
						header('Location: index.php?s=cAdminScripts&error=202');
						die;
				}
				
				$data['id'] = $id;
				$data['title'] = core()->getPostVar('title');
				$data['status']= (int)core()->getPostVar('status');
				$data['sort_order'] = (int)core()->getPostVar('sort_order');
				$data['initial_processes_id'] = (int)core()->getPostVar('initial_processes_id');
				$data['layout'] = core()->getPostVar('layout');
				$data['show_edit_customers_data_button'] = (int)core()->getPostVar('show_edit_customers_data_button');
				$data['show_on_customers_screen'] = (int)core()->getPostVar('show_on_customers_screen');
				
				if(NULL === cScripts::loadById((int)$data['id'])) {
						header('Location: index.php?s=cAdminScripts&error=202');
						die;
				}
				
				$id = $this->save($data);
				
				if(empty($id)) {
						header('Location: index.php?s=cAdminScripts&error=203');
						die;
				}
	
				header('Location: index.php?s=cAdminScripts&action=edit&id=' . $id . '&success=204');
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Crate a content entry.
		///////////////////////////////////////////////////////////////////
		function create() {
				$data['id'] = 0;
				$data['title'] = core()->getPostVar('title');
				$data['status'] = (int)core()->getPostVar('status');
				$data['sort_order'] = (int)core()->getPostVar('sort_order');
				$data['initial_processes_id'] = (int)core()->getPostVar('initial_processes_id');
				$data['layout'] = core()->getPostVar('layout');
				$data['show_edit_customers_data_button'] = (int)core()->getPostVar('show_edit_customers_data_button');
				$data['show_on_customers_screen'] = (int)core()->getPostVar('show_on_customers_screen');
				
				$id = $this->save($data);
				
				if(empty($id)) {
						header('Location: index.php?s=cAdminScripts&error=205');
						die;
				}
	
				header('Location: index.php?s=cAdminScripts&action=edit&id=' . $id . '&success=206');
				die;
		}

		///////////////////////////////////////////////////////////////////
		// Loads a list of content entries.
		///////////////////////////////////////////////////////////////////
		public function loadList() {
				$list = cScripts::loadList();
				$list = $this->addScriptsProcessesDataToListEntries($list);
				
				return $list;
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Add scripts processes data to list entries.
		/////////////////////////////////////////////////////////////////////////////////
		public function addScriptsProcessesDataToListEntries($list) {
				foreach($list as $index => $item) {
						$tmp = cScriptsProcesses::loadById($item['initial_processes_id']);
						$list[$index]['initial_processes_data'] = $tmp;
				}
				
				return $list;
		}
	
		/////////////////////////////////////////////////////////////////////////////////
		// Save data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public function save($data) {
				$id = (int)$data['id'];
		
				if(0 === $id) {
						return cScripts::createInDB($data);
				}
				
				cScripts::updateInDB($id, $data);
				
				return $data['id'];
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Loads all layouts.
		/////////////////////////////////////////////////////////////////////////////////
		public function loadLayoutList() {
				$folder = 'data/merlin/layouts/call-assist/';
				$files = scandir($folder);
				
				foreach($files as $file) {
						if($file == '.' || $file == '..') {
								continue;
						}
						
						$this->data['layouts'][] = array(
								'id' => $file,
								'name' => $file
						);
				}
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Loads a content entry.
		/////////////////////////////////////////////////////////////////////////////////
		public function getContent() {
				$id = (int)core()->getGetVar('id');
				$this->data['data'] = cScripts::loadById($id);
		}
}
?>