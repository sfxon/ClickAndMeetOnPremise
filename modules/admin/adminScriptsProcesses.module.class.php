<?php

class cAdminScriptsProcesses extends cModule {
		var $template = 'admin';
		var $navbar_title = TEXT_MODULE_TITLE__ADMIN_SCRIPTS_PROCESSES;
		var $navbar_id = 0;
		var $errors = array();
		var $errors_description = array();
		var $results = array();
		var $data;
		var $scripts_id;
		var $scripts_data;
		
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
				if(false === cAccount::adminrightCheck('cAdminScriptsProcesses', 'USE_MODULE', (int)$_SESSION['user_id'])) {
						header('Location: index.php?s=cAdmin&error=233');
						die;
				}
				
				//We use the Admin module for output.
				cAdmin::setSmallBodyExecutionalHooks();	
				
				//Now set our own hooks below the CMS hooks.
				$core = core();
				core()->setHook('cCore|process', 'process');
				core()->setHook('cRenderer|content', 'content');
				core()->setHook('cRenderer|footer', 'footer');
		}
	
	
		///////////////////////////////////////////////////////////////////
		// processData
		///////////////////////////////////////////////////////////////////
		function process() {
				$this->initData();
			
				$this->action = core()->getGetVar('action');
				$cAdmin = core()->getInstance('cAdmin');
				$cAdmin->appendBreadcrumb(TEXT_MODULE_TITLE_ADMINSCRIPTS, 'index.php?s=cAdminScripts');
				$cAdmin->appendBreadcrumb($this->scripts_data['title'], '');
				$cAdmin->appendBreadcrumb(TEXT_MODULE_TITLE__ADMIN_SCRIPTS_PROCESSES, 'index.php?s=cAdminScriptsProcesses&amp;scripts_id=' . $this->scripts_id);
				
				switch($this->action) {
						case 'ajax_load_list':
								$this->ajaxLoadList();
								break;
						case 'delete_confirm':
								$this->getContent();
								$this->delete();
								break;
						case 'delete':
								$this->getContent();
								$this->data['url'] = 'index.php?s=cAdminScriptsProcesses&amp;action=delete_confirm&amp;scripts_id=' . $this->scripts_id . '&amp;id=' . (int)$this->data['data']['id'];
								$cAdmin->appendBreadcrumb(TEXT_ADMIN_SCRIPTS_PROCESSES_DELETE_CONTENT, '');
								$this->navbar_title = TEXT_ADMIN_SCRIPTS_PROCESSES_DELETE_CONTENT;
								break;
						case 'edit':
								$this->getContent();
								$this->data['url'] = 'index.php?s=cAdminScriptsProcesses&amp;action=update&amp;scripts_id=' . $this->scripts_id . '&amp;id=' . (int)$this->data['data']['id'];
								$cAdmin->appendBreadcrumb(TEXT_ADMIN_SCRIPTS_PROCESSES_EDIT_CONTENT, '');
								$this->navbar_title = TEXT_ADMIN_SCRIPTS_PROCESSES_EDIT_CONTENT;
								break;
						case 'update':
								$this->getContent();
								$this->update();
								$cAdmin->appendBreadcrumb(TEXT_ADMIN_SCRIPTS_PROCESSES_EDIT_CONTENT, '');
								$this->navbar_title = TEXT_ADMIN_SCRIPTS_PROCESSES_EDIT_CONTENT;
								break;		
						case 'new':
								$this->data['url'] = 'index.php?s=cAdminScriptsProcesses&amp;action=create&amp;scripts_id=' . $this->scripts_id;
								$cAdmin->appendBreadcrumb(TEXT_ADMIN_SCRIPTS_PROCESSES_NEW_CONTENT, '');
								$this->navbar_title = TEXT_ADMIN_SCRIPTS_PROCESSES_NEW_CONTENT;
								break;
						case 'create':
								$this->getContent();
								$this->create();
								$cAdmin->appendBreadcrumb(TEXT_ADMIN_SCRIPTS_PROCESSES_NEW_CONTENT, '');
								$this->navbar_title = TEXT_ADMIN_SCRIPTS_PROCESSES_NEW_CONTENT;
								break;
						default:
								$this->getList();
								$cAdmin->appendBreadcrumb(TEXT_ADMIN_SCRIPTS_PROCESSES_LIST, '');
								$this->navbar_title = TEXT_ADMIN_SCRIPTS_PROCESSES_LIST;
								break;
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Load list.
		///////////////////////////////////////////////////////////////////
		public function ajaxLoadList() {
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$renderer->assign('SCRIPTS_ID', (int)$this->scripts_id);
				$renderer->assign('SCRIPTS_CONTENTS_LIST', cScriptsContents::loadList());
				$content = $renderer->fetch('site/AdminScriptsProcesses/contents_list.html');
				
				echo $content;
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Delete an entry.
		///////////////////////////////////////////////////////////////////
		function delete() {
				$id = (int)core()->getGetVar('id');
				
				if(0 == $id) {
						header('Location: index.php?s=cAdminScriptsProcesses&scripts_id=' . $this->scripts_id . '&error=234');
						die;
				}
				
				$data['id'] = $id;
				
				if(NULL === cScriptsProcesses::loadById((int)$data['id'])) {
						header('Location: index.php?s=cAdminScriptsProcesses&scripts_id=' . $this->scripts_id . '&error=234');
						die;
				}
				
				cScriptsProcesses::deleteFromDB($id);
				
				header('Location: index.php?s=cAdminScriptsProcesses&scripts_id=' . $this->scripts_id . '&success=235');
				die;
		}
				
		
		///////////////////////////////////////////////////////////////////
		// Prepare data for the editor.
		///////////////////////////////////////////////////////////////////
		function initData() {
				$this->data['data']['id'] = 0;
				$this->data['data']['title'] = '';
				$this->data['data']['scripts_contents_id'] = 0;
				$this->data['data']['show_edit_customers_data_button'] = 0;
				
				//Load scritps id.
				$this->scripts_id = (int)core()->getGetVar('scripts_id');
				$this->scripts_data = cScripts::loadById($this->scripts_id);
				
				if(false === $this->scripts_data) {
						header('Location: index.php?s=cAdminScripts&error=241');
						die;
				}
				
				
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
				$renderer->assign('SCRIPTS_ID', (int)$this->scripts_id);
				$renderer->render('site/AdminScriptsProcesses/delete_confirm_dialog.html');
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the editor.
		///////////////////////////////////////////////////////////////////
		function drawEditor() {
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$renderer->assign('SCRIPTS_ID', (int)$this->scripts_id);
				$renderer->assign('SCRIPTS_CONTENTS_LIST', cScriptsContents::loadList());
				$renderer->render('site/AdminScriptsProcesses/editor.html');
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
				$renderer->assign('SCRIPTS_ID', (int)$this->scripts_id);
				$renderer->render('site/AdminScriptsProcesses/list.html');
		}
		
		///////////////////////////////////////////////////////////////////
		// update an entry
		///////////////////////////////////////////////////////////////////
		public function update() {
				$id = (int)core()->getGetVar('id');
				
				if(0 == $id) {
						header('Location: index.php?s=cAdminScriptsProcesses&scripts_id=' . $this->scripts_id . '&error=236');
						die;
				}
				
				$data['id'] = $id;
				$data['title'] = core()->getPostVar('title');
				$data['scripts_contents_id'] = (int)core()->getPostVar('scripts_contents_id');
				$data['scripts_id'] = (int)core()->getGetVar('scripts_id');
				$data['show_edit_customers_data_button'] = (int)core()->getPostVar('show_edit_customers_data_button');
				
				if(NULL === cScriptsProcesses::loadById((int)$data['id'])) {
						header('Location: index.php?s=cAdminScriptsProcesses&scripts_id=' . $this->scripts_id . '&error=236');
						die;
				}
				
				$id = $this->save($data);
				
				if(empty($id)) {
						header('Location: index.php?s=cAdminScriptsProcesses&scripts_id=' . $this->scripts_id . '&error=237');
						die;
				}
	
				header('Location: index.php?s=cAdminScriptsProcesses&action=edit&id=' . $id . '&scripts_id=' . $this->scripts_id . '&success=238');
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Crate a content entry.
		///////////////////////////////////////////////////////////////////
		function create() {
				$data['id'] = 0;
				$data['title'] = core()->getPostVar('title');
				$data['scripts_contents_id'] = (int)core()->getPostVar('scripts_contents_id');
				$data['scripts_id'] = (int)core()->getGetVar('scripts_id');
				$data['show_edit_customers_data_button'] = (int)core()->getPostVar('show_edit_customers_data_button');
				
				$id = $this->save($data);
				
				if(empty($id)) {
						header('Location: index.php?s=cAdminScriptsProcesses&scripts_id=' . $this->scripts_id . '&error=239');
						die;
				}
	
				header('Location: index.php?s=cAdminScriptsProcesses&action=edit&id=' . $id . '&scripts_id=' . $this->scripts_id . '&success=216');
				die;
		}

		//////////////////////////////////////////////////////////////////////////////////
		// Loads a list of content entries.
		//////////////////////////////////////////////////////////////////////////////////
		public function loadList() {
				$list = cScriptsProcesses::loadListByScriptsId($this->scripts_id);
				$list = $this->addContentsDataToListEntries($list);
				
				return $list;
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// 
		/////////////////////////////////////////////////////////////////////////////////
		public function addContentsDataToListEntries($list) {
				foreach($list as $index => $value) {
						$tmp = cScriptsContents::loadById($value['scripts_contents_id']);
						$list[$index]['scripts_contents_data'] = $tmp;
				}
				
				return $list;
		}
			
	
		/////////////////////////////////////////////////////////////////////////////////
		// Save data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public function save($data) {
				$id = (int)$data['id'];
		
				if(0 === $id) {
						return cScriptsProcesses::createInDB($data);
				}
				
				cScriptsProcesses::updateInDB($id, $data);
				
				return $data['id'];
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Loads a content entry.
		/////////////////////////////////////////////////////////////////////////////////
		public function getContent() {
				$id = (int)core()->getGetVar('id');
				$this->data['data'] = cScriptsProcesses::loadById($id);
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the page footer.
		///////////////////////////////////////////////////////////////////
		public function footer() {
				$additional_output = 
						"\n" . '<script src="data/templates/' . $this->template . '/js/admin_scripts_processes.js"></script>' .
						"\n";
				$renderer = core()->getInstance('cRenderer');
				$renderer->renderText($additional_output);
		}
}
?>