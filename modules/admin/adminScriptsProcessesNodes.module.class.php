<?php

class cAdminScriptsProcessesNodes extends cModule {
		var $template = 'admin';
		var $navbar_title = TEXT_MODULE_TITLE__ADMIN_SCRIPTS_PROCESSES_NODES;
		var $navbar_id = 0;
		var $errors = array();
		var $errors_description = array();
		var $results = array();
		var $data;
		var $scripts_id;
		var $scripts_data;
		var $processes_id;
		var $processes_data;
		
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
				if(false === cAccount::adminrightCheck('cAdminScriptsProcessesNodes', 'USE_MODULE', (int)$_SESSION['user_id'])) {
						header('Location: index.php?s=cAdmin&error=242');
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
				$cAdmin->appendBreadcrumb($this->processes_data['title'], '');
				$cAdmin->appendBreadcrumb(TEXT_MODULE_TITLE__ADMIN_SCRIPTS_PROCESSES_NODES, 'index.php?s=cAdminScriptsProcessesNodes&amp;scripts_id=' . $this->scripts_id . '&amp;processes_id=' . $this->processes_id);
				
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
								$this->data['url'] = 'index.php?s=cAdminScriptsProcessesNodes&amp;action=delete_confirm&amp;scripts_id=' . $this->scripts_id . '&amp;processes_id=' . $this->processes_id . '&amp;id=' . (int)$this->data['data']['id'];
								$cAdmin->appendBreadcrumb(TEXT_ADMIN_SCRIPTS_PROCESSES_NODES_DELETE_CONTENT, '');
								$this->navbar_title = TEXT_ADMIN_SCRIPTS_PROCESSES_NODES_DELETE_CONTENT;
								break;
						case 'edit':
								$this->getContent();
								$this->data['url'] = 'index.php?s=cAdminScriptsProcessesNodes&amp;action=update&amp;scripts_id=' . $this->scripts_id . '&amp;processes_id=' . $this->processes_id . '&amp;id=' . (int)$this->data['data']['id'];
								$cAdmin->appendBreadcrumb(TEXT_ADMIN_SCRIPTS_PROCESSES_NODES_EDIT_CONTENT, '');
								$this->navbar_title = TEXT_ADMIN_SCRIPTS_PROCESSES_NODES_EDIT_CONTENT;
								break;
						case 'update':
								$this->getContent();
								$this->update();
								$cAdmin->appendBreadcrumb(TEXT_ADMIN_SCRIPTS_PROCESSES_NODES_EDIT_CONTENT, '');
								$this->navbar_title = TEXT_ADMIN_SCRIPTS_PROCESSES_NODES_EDIT_CONTENT;
								break;		
						case 'new':
								$this->data['url'] = 'index.php?s=cAdminScriptsProcessesNodes&amp;action=create&amp;scripts_id=' . $this->scripts_id . '&amp;processes_id=' . $this->processes_id;
								$cAdmin->appendBreadcrumb(TEXT_ADMIN_SCRIPTS_PROCESSES_NODES_NEW_CONTENT, '');
								$this->navbar_title = TEXT_ADMIN_SCRIPTS_PROCESSES_NODES_NEW_CONTENT;
								break;
						case 'create':
								$this->getContent();
								$this->create();
								$cAdmin->appendBreadcrumb(TEXT_ADMIN_SCRIPTS_PROCESSES_NODES_NEW_CONTENT, '');
								$this->navbar_title = TEXT_ADMIN_SCRIPTS_PROCESSES_NODES_NEW_CONTENT;
								break;
						default:
								$this->getList();
								$cAdmin->appendBreadcrumb(TEXT_ADMIN_SCRIPTS_PROCESSES_NODES_LIST, '');
								$this->navbar_title = TEXT_ADMIN_SCRIPTS_PROCESSES_NODES_LIST;
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
				$renderer->assign('SCRIPTS_PROCESSES_LIST', cScriptsProcesses::loadListByScriptsId($this->scripts_id));
				$content = $renderer->fetch('site/AdminScriptsProcessesNodes/processes_list.html');
				
				echo $content;
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Delete an entry.
		///////////////////////////////////////////////////////////////////
		function delete() {
				$id = (int)core()->getGetVar('id');
				
				if(0 == $id) {
						header('Location: index.php?s=cAdminScriptsProcessesNodes&scripts_id=' . $this->scripts_id . '&processes_id=' . $this->processes_id . '&error=243');
						die;
				}
				
				$data['id'] = $id;
				
				if(NULL === cScriptsProcessesNodes::loadById((int)$data['id'])) {
						header('Location: index.php?s=cAdminScriptsProcessesNodes&scripts_id=' . $this->scripts_id . '&processes_id=' . $this->processes_id . '&error=243');
						die;
				}
				
				cScriptsProcessesNodes::deleteFromDB($id);
				
				header('Location: index.php?s=cAdminScriptsProcessesNodes&scripts_id=' . $this->scripts_id . '&processes_id=' . $this->processes_id . '&success=244');
				die;
		}
				
		
		///////////////////////////////////////////////////////////////////
		// Prepare data for the editor.
		///////////////////////////////////////////////////////////////////
		function initData() {
				$this->data['data']['id'] = 0;
				$this->data['data']['button_title'] = '';
				//$this->data['data']['processes_id'] = $this->processes_id;
				$this->data['data']['next_processes_id'] = 0;
				$this->data['data']['sort_order'] = 0;
				$this->data['data']['scripts_button_styles_id'] = 0;
				$this->data['data']['scripts_button_areas_id'] = 0;
				$this->data['data']['type'] = 'site';
				$this->data['data']['open_as_customer_edit'] = 0;
				$this->data['data']['replace_customers_edit_action'] = 0;
				
				$this->scripts_id = (int)core()->getGetVar('scripts_id');
				
				$this->scripts_data = cScripts::loadById($this->scripts_id);
				
				if(false === $this->scripts_data) {
						header('Location: index.php?s=cAdminScripts&error=245');
						die;
				}
				
				//Load processes_id
				$this->processes_id = (int)core()->getGetVar('processes_id');
				
				$this->processes_data = cScriptsProcesses::loadById($this->processes_id);
				
				if(false === $this->processes_data) {
						header('Location: index.php?s=cAdminScripts&scripts_id=' . $this->scripts_id . '&error=246');
						die;
				}
				
				$this->data['data']['processes_id'] = (int)$this->processes_id;
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
				$renderer->assign('PROCESSES_ID', (int)$this->processes_id);
				$renderer->render('site/AdminScriptsProcessesNodes/delete_confirm_dialog.html');
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
				$renderer->assign('PROCESSES_ID', (int)$this->processes_id);
				$renderer->assign('SCRIPTS_PROCESSES_LIST', cScriptsProcesses::loadListByScriptsId($this->scripts_id));
				$renderer->assign('SCRIPTS_BUTTON_STYLES_LIST', cScriptsButtonStyles::loadList());
				$renderer->assign('SCRIPTS_BUTTON_AREAS_LIST', cScriptsButtonAreas::loadList());
				$renderer->render('site/AdminScriptsProcessesNodes/editor.html');
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
				$renderer->assign('PROCESSES_ID', (int)$this->processes_id);
				$renderer->render('site/AdminScriptsProcessesNodes/list.html');
		}
		
		///////////////////////////////////////////////////////////////////
		// update an entry
		///////////////////////////////////////////////////////////////////
		public function update() {
				$id = (int)core()->getGetVar('id');
				
				if(0 == $id) {
						header('Location: index.php?s=cAdminScriptsProcessesNodes&scripts_id=' . $this->scripts_id . '&processes_id=' . $this->processes_id . '&error=248');
						die;
				}
				
				$data['id'] = $id;
				$data['button_title'] = core()->getPostVar('button_title');
				$data['processes_id'] = (int)core()->getPostVar('processes_id');
				$data['next_processes_id'] = (int)core()->getPostVar('next_processes_id');
				$data['sort_order'] = (int)core()->getPostVar('sort_order');
				$data['scripts_button_styles_id'] = (int)core()->getPostVar('scripts_button_styles_id');
				$data['scripts_button_areas_id'] = (int)core()->getPostVar('scripts_button_areas_id');
				$data['type'] = core()->getPostVar('type');
				$data['open_as_customer_edit'] = (int)core()->getPostVar('open_as_customer_edit');
				$data['replace_customers_edit_action'] = (int)core()->getPostVar('replace_customers_edit_action');
				
				
				if(NULL === cScriptsProcessesNodes::loadById((int)$data['id'])) {
						header('Location: index.php?s=cAdminScriptsProcessesNodes&scripts_id=' . $this->scripts_id . '&processes_id=' . $this->processes_id . '&error=248');
						die;
				}
				
				$id = $this->save($data);
				
				if($data['type'] !== 'popup') {
						$data['type'] = 'site';
				}
				
				if(empty($id)) {
						header('Location: index.php?s=cAdminScriptsProcessesNodes&scripts_id=' . $this->scripts_id . '&processes_id=' . $this->processes_id . '&error=249');
						die;
				}
	
				header('Location: index.php?s=cAdminScriptsProcessesNodes&scripts_id=' . $this->scripts_id . '&processes_id=' . $this->processes_id . '&action=edit&id=' . $id . '&success=250');
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Crate a content entry.
		///////////////////////////////////////////////////////////////////
		function create() {
				$data['id'] = 0;
				$data['button_title'] = core()->getPostVar('button_title');
				$data['processes_id'] = (int)core()->getPostVar('processes_id');
				$data['next_processes_id'] = (int)core()->getPostVar('next_processes_id');
				$data['sort_order'] = (int)core()->getPostVar('sort_order');
				$data['scripts_button_styles_id'] = (int)core()->getPostVar('scripts_button_styles_id');
				$data['scripts_button_areas_id'] = (int)core()->getPostVar('scripts_button_areas_id');
				$data['type'] = core()->getPostVar('type');
				$data['open_as_customer_edit'] = (int)core()->getPostVar('open_as_customer_edit');
				$data['replace_customers_edit_action'] = (int)core()->getPostVar('replace_customers_edit_action');
				
				if($data['type'] !== 'popup') {
						$data['type'] = 'site';
				}
				
				$id = $this->save($data);
				
				if(empty($id)) {
						header('Location: index.php?s=cAdminScriptsProcessesNodes&scripts_id=' . $this->scripts_id . '&processes_id=' . $this->processes_id . '&error=251');
						die;
				}
	
				header('Location: index.php?s=cAdminScriptsProcessesNodes&scripts_id=' . $this->scripts_id . '&processes_id=' . $this->processes_id . '&action=edit&id=' . $id . '&success=252');
				die;
		}

		///////////////////////////////////////////////////////////////////
		// Loads a list of content entries.
		///////////////////////////////////////////////////////////////////
		public function loadList() {
				$list = cScriptsProcessesNodes::loadListByProcessesId($this->processes_id);
				$list = $this->addProcessesDataToListEntries($list);
				$list = $this->addButtonStylesDataToListEntries($list);
				$list = $this->addButtonAreasDataToListEntries($list);
				
				return $list;
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Informationen über die Process- Datenbankverknüpfungen hinzufügen.
		/////////////////////////////////////////////////////////////////////////////////
		public function addProcessesDataToListEntries($list) {
				foreach($list as $index => $value) {
						$tmp = cScriptsProcesses::loadById($value['processes_id']);
						$list[$index]['processes_data'] = $tmp;
				}
				
				return $list;
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Informationen über die Button Styles- Datenbankverknüpfungen hinzufügen.
		/////////////////////////////////////////////////////////////////////////////////
		public function addButtonStylesDataToListEntries($list) {
				foreach($list as $index => $value) {
						$tmp = cScriptsButtonStyles::loadById($value['scripts_button_styles_id']);
						$list[$index]['scripts_button_styles_data'] = $tmp;
				}
				
				return $list;
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Informationen über die Button Areas- Datenbankverknüpfungen hinzufügen.
		/////////////////////////////////////////////////////////////////////////////////
		public function addButtonAreasDataToListEntries($list) {
				foreach($list as $index => $value) {
						$tmp = cScriptsButtonAreas::loadById($value['scripts_button_areas_id']);
						$list[$index]['scripts_button_areas_data'] = $tmp;
				}
				
				return $list;
		}
	
		/////////////////////////////////////////////////////////////////////////////////
		// Save data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public function save($data) {
				$id = (int)$data['id'];
		
				if(0 === $id) {
						return cScriptsProcessesNodes::createInDB($data);
				}
				
				cScriptsProcessesNodes::updateInDB($id, $data);
				
				return $data['id'];
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Loads a content entry.
		/////////////////////////////////////////////////////////////////////////////////
		public function getContent() {
				$id = (int)core()->getGetVar('id');
				$this->data['data'] = cScriptsProcessesNodes::loadById($id);
				
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the page footer.
		///////////////////////////////////////////////////////////////////
		public function footer() {
				$additional_output = 
						"\n" . '<script src="data/templates/' . $this->template . '/js/admin_scripts_processes_nodes.js"></script>' .
						"\n";
				$renderer = core()->getInstance('cRenderer');
				$renderer->renderText($additional_output);
		}
}
?>