<?php

class cAdminScriptsFixedProcessesNodes extends cModule {
		var $template = 'admin';
		var $navbar_title = TEXT_MODULE_TITLE__ADMIN_SCRIPTS_FIXED_PROCESSES_NODES;
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
				if(false === cAccount::adminrightCheck('cAdminScriptsFixedProcessesNodes', 'USE_MODULE', (int)$_SESSION['user_id'])) {
						header('Location: index.php?s=cAdmin&error=262');
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
				$cAdmin->appendBreadcrumb(TEXT_MODULE_TITLE__ADMIN_SCRIPTS_FIXED_PROCESSES_NODES, 'index.php?s=cAdminScriptsFixedProcessesNodes&amp;scripts_id=' . $this->scripts_id);
				
				switch($this->action) {
						case 'delete_confirm':
								$this->getContent();
								$this->delete();
								break;
						case 'delete':
								$this->getContent();
								$this->data['url'] = 'index.php?s=cAdminScriptsFixedProcessesNodes&amp;action=delete_confirm&amp;scripts_id=' . $this->scripts_id . '&amp;id=' . (int)$this->data['data']['id'];
								$cAdmin->appendBreadcrumb(TEXT_ADMIN_SCRIPTS_FIXED_PROCESSES_NODES_DELETE_CONTENT, '');
								$this->navbar_title = TEXT_ADMIN_SCRIPTS_FIXED_PROCESSES_NODES_DELETE_CONTENT;
								break;
						case 'edit':
								$this->getContent();
								$this->data['url'] = 'index.php?s=cAdminScriptsFixedProcessesNodes&amp;action=update&amp;scripts_id=' . $this->scripts_id . '&amp;id=' . (int)$this->data['data']['id'];
								$cAdmin->appendBreadcrumb(TEXT_ADMIN_SCRIPTS_FIXED_PROCESSES_NODES_EDIT_CONTENT, '');
								$this->navbar_title = TEXT_ADMIN_SCRIPTS_FIXED_PROCESSES_NODES_EDIT_CONTENT;
								break;
						case 'update':
								$this->getContent();
								$this->update();
								$cAdmin->appendBreadcrumb(TEXT_ADMIN_SCRIPTS_FIXE_PROCESSES_NODES_EDIT_CONTENT, '');
								$this->navbar_title = TEXT_ADMIN_SCRIPTS_FIXED_PROCESSES_NODES_EDIT_CONTENT;
								break;		
						case 'new':
								$this->data['url'] = 'index.php?s=cAdminScriptsFixedProcessesNodes&amp;action=create&amp;scripts_id=' . $this->scripts_id;
								$cAdmin->appendBreadcrumb(TEXT_ADMIN_SCRIPTS_FIXED_PROCESSES_NODES_NEW_CONTENT, '');
								$this->navbar_title = TEXT_ADMIN_SCRIPTS_FIXED_PROCESSES_NODES_NEW_CONTENT;
								break;
						case 'create':
								$this->getContent();
								$this->create();
								$cAdmin->appendBreadcrumb(TEXT_ADMIN_SCRIPTS_FIXED_PROCESSES_NODES_NEW_CONTENT, '');
								$this->navbar_title = TEXT_ADMIN_SCRIPTS_FIXED_PROCESSES_NODES_NEW_CONTENT;
								break;
						default:
								$this->getList();
								$cAdmin->appendBreadcrumb(TEXT_ADMIN_SCRIPTS_FIXED_PROCESSES_NODES_LIST, '');
								$this->navbar_title = TEXT_ADMIN_SCRIPTS_FIXED_PROCESSES_NODES_LIST;
								break;
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Delete an entry.
		///////////////////////////////////////////////////////////////////
		function delete() {
				$id = (int)core()->getGetVar('id');
				
				if(0 == $id) {
						header('Location: index.php?s=cAdminScriptsFixedProcessesNodes&scripts_id=' . $this->scripts_id . '&error=263');
						die;
				}
				
				$data['id'] = $id;
				
				if(NULL === cScriptsFixedProcessesNodes::loadById((int)$data['id'])) {
						header('Location: index.php?s=cAdminScriptsFixedProcessesNodes&scripts_id=' . $this->scripts_id . '&error=263');
						die;
				}
				
				cScriptsFixedProcessesNodes::deleteFromDB($id);
				
				header('Location: index.php?s=cAdminScriptsFixedProcessesNodes&scripts_id=' . $this->scripts_id . '&success=264');
				die;
		}
				
		
		///////////////////////////////////////////////////////////////////
		// Prepare data for the editor.
		///////////////////////////////////////////////////////////////////
		function initData() {
				$this->data['data']['id'] = 0;
				$this->data['data']['button_title'] = '';
				$this->data['data']['next_processes_id'] = 0;
				$this->data['data']['sort_order'] = 0;
				$this->data['data']['scripts_button_styles_id'] = 0;
				$this->data['data']['scripts_button_areas_id'] = 0;
				$this->data['data']['type'] = 'site';
				
				$this->scripts_id = (int)core()->getGetVar('scripts_id');
				
				$this->scripts_data = cScripts::loadById($this->scripts_id);
				
				if(false === $this->scripts_data) {
						header('Location: index.php?s=cAdminScripts&error=265');
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
				$renderer->render('site/AdminScriptsFixedProcessesNodes/delete_confirm_dialog.html');
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
				$renderer->render('site/AdminScriptsFixedProcessesNodes/editor.html');
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
				$renderer->render('site/AdminScriptsFixedProcessesNodes/list.html');
		}
		
		///////////////////////////////////////////////////////////////////
		// update an entry
		///////////////////////////////////////////////////////////////////
		public function update() {
				$id = (int)core()->getGetVar('id');
				
				if(0 == $id) {
						header('Location: index.php?s=cAdminScriptsFixedProcessesNodes&scripts_id=' . $this->scripts_id . '&error=266');
						die;
				}
				
				$data['id'] = $id;
				$data['scripts_id'] = $this->scripts_id;
				$data['button_title'] = core()->getPostVar('button_title');
				$data['next_processes_id'] = (int)core()->getPostVar('next_processes_id');
				$data['sort_order'] = (int)core()->getPostVar('sort_order');
				$data['scripts_button_styles_id'] = (int)core()->getPostVar('scripts_button_styles_id');
				$data['scripts_button_areas_id'] = (int)core()->getPostVar('scripts_button_areas_id');
				$data['type'] = core()->getPostVar('type');
				
				if(NULL === cScriptsProcessesNodes::loadById((int)$data['id'])) {
						header('Location: index.php?s=cAdminScriptsFixedProcessesNodes&scripts_id=' . $this->scripts_id . '&error=266');
						die;
				}
				
				$id = $this->save($data);
				
				if($data['type'] !== 'popup') {
						$data['type'] = 'site';
				}
				
				if(empty($id)) {
						header('Location: index.php?s=cAdminScriptsFixedProcessesNodes&scripts_id=' . $this->scripts_id . '&error=267');
						die;
				}
	
				header('Location: index.php?s=cAdminScriptsFixedProcessesNodes&scripts_id=' . $this->scripts_id . '&action=edit&id=' . $id . '&success=268');
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Crate a content entry.
		///////////////////////////////////////////////////////////////////
		function create() {
				$data['id'] = 0;
				$data['scripts_id'] = $this->scripts_id;
				$data['button_title'] = core()->getPostVar('button_title');
				$data['processes_id'] = (int)core()->getPostVar('processes_id');
				$data['next_processes_id'] = (int)core()->getPostVar('next_processes_id');
				$data['sort_order'] = (int)core()->getPostVar('sort_order');
				$data['scripts_button_styles_id'] = (int)core()->getPostVar('scripts_button_styles_id');
				$data['scripts_button_areas_id'] = (int)core()->getPostVar('scripts_button_areas_id');
				$data['type'] = core()->getPostVar('type');
				
				if($data['type'] !== 'popup') {
						$data['type'] = 'site';
				}
				
				$id = $this->save($data);
				
				if(empty($id)) {
						header('Location: index.php?s=cAdminScriptsFixedProcessesNodes&scripts_id=' . $this->scripts_id . '&error=269');
						die;
				}
	
				header('Location: index.php?s=cAdminScriptsFixedProcessesNodes&scripts_id=' . $this->scripts_id . '&action=edit&id=' . $id . '&success=252');
				die;
		}

		///////////////////////////////////////////////////////////////////
		// Loads a list of content entries.
		///////////////////////////////////////////////////////////////////
		public function loadList() {
				$list = cScriptsFixedProcessesNodes::loadListByScriptsId($this->scripts_id);
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
						$tmp = cScriptsProcesses::loadById($value['next_processes_id']);
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
						return cScriptsFixedProcessesNodes::createInDB($data);
				}
				
				cScriptsFixedProcessesNodes::updateInDB($id, $data);
				
				return $data['id'];
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Loads a content entry.
		/////////////////////////////////////////////////////////////////////////////////
		public function getContent() {
				$id = (int)core()->getGetVar('id');
				$this->data['data'] = cScriptsFixedProcessesNodes::loadById($id);
				
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the page footer.
		///////////////////////////////////////////////////////////////////
		public function footer() {
				$additional_output = 
						/*"\n" . '<script src="data/templates/' . $this->template . '/js/admin_scripts_processes_nodes.js"></script>' .*/
						"\n";
				$renderer = core()->getInstance('cRenderer');
				$renderer->renderText($additional_output);
		}
}
?>