<?php

class cAdmincontactform extends cModule {
		var $template = 'admin';
		var $navbar_title = TEXT_MODULE_TITLE_ADMINNCONTACTFORM;
		var $navbar_id = 0;
		
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
				if(false === cAccount::adminrightCheck('cAdmincontactform', 'USE_MODULE', (int)$_SESSION['user_id'])) {
						header('Location: index.php?s=cAdmin&error=26');
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
				$cAdmin->appendBreadcrumb(TEXT_MODULE_TITLE_ADMINNCONTACTFORM, 'index.php?s=cAdmincontactform');
				
				switch($this->action) {
						/////////////////////////////// Detail Menu ///////////////////////////////////////////////////////////////////////////
						case 'detail_update':
								$this->initData();
								$this->getData();
								$this->initDetailData();
								$this->getDetailData();
								$this->updateDetail();
								$cAdmin->appendBreadcrumb($this->data['data']['title'], 'index.php?s=cAdmincontactform&amp;action=list_details&amp;id=' . (int)$this->data['data']['id']);
								$cAdmin->appendBreadcrumb(TEXT_ADMINCONTACTFORM_EDIT_DETAIL, '');
								$this->navbar_title = TEXT_ADMINCONTACTFORM_EDIT_DETAIL;
								break;
								
						
						case 'edit_detail':
								$this->initData();
								$this->getData();
								$this->initDetailData();
								$this->getDetailData();
								$this->data['url'] = 'index.php?s=cAdmincontactform&amp;action=detail_update&amp;id=' . (int)$this->data['data']['id'] . '&amp;detail_id=' . $this->data['detail_data']['id'];
								$cAdmin->appendBreadcrumb($this->data['data']['title'], 'index.php?s=cAdmincontactform&amp;action=list_details&amp;id=' . (int)$this->data['data']['id']);
								$cAdmin->appendBreadcrumb(TEXT_ADMINCONTACTFORM_EDIT_DETAIL, '');
								$this->navbar_title = TEXT_ADMINCONTACTFORM_EDIT_DETAIL;
								break;
						
						case 'detail_create':
								$this->initData();
								$this->getData();
								$this->createDetail();
								$cAdmin->appendBreadcrumb($this->data['data']['title'], 'index.php?s=cAdmincontactform&amp;action=list_details&amp;id=' . (int)$this->data['data']['id']);
								$cAdmin->appendBreadcrumb(TEXT_ADMINCONTACTFORM_NEW_DETAIL, '');
								$this->navbar_title = TEXT_ADMINCONTACTFORM_NEW_DETAIL;
								break;
						
						case 'detail_new':
								$this->initData();
								$this->getData();
								$this->initDetailData();
								$this->data['url'] = 'index.php?s=cAdmincontactform&amp;action=detail_create&amp;id=' . (int)$this->data['data']['id'];
								$cAdmin->appendBreadcrumb($this->data['data']['title'], 'index.php?s=cAdmincontactform&amp;action=list_details&amp;id=' . (int)$this->data['data']['id']);
								$cAdmin->appendBreadcrumb(TEXT_ADMINCONTACTFORM_NEW_DETAIL, '');
								$this->navbar_title = TEXT_ADMINCONTACTFORM_NEW_DETAIL;
								break;
						
						case 'list_details':
								$this->initData();
								$this->getData();
								$this->getDetailList();
								$cAdmin->appendBreadcrumb($this->data['data']['title'], 'index.php?s=cAdmincontactform&amp;action=list_details&amp;id=' . (int)$this->data['data']['id']);
								$cAdmin->appendBreadcrumb(TEXT_ADMINCONTACTFORM_EDIT_LIST_DETAILS, '');
								$this->navbar_title = TEXT_ADMINCONTACTFORM_LIST_DETAILS;
								break;
								
						/////////////////////////////// Erstes Menü (Outer Menu) ///////////////////////////////////////////////////////////////						
						case 'edit':
								$this->initData();
								$this->getData();
								$this->data['url'] = 'index.php?s=cAdmincontactform&amp;action=update&amp;id=' . (int)$this->data['data']['id'];
								$cAdmin->appendBreadcrumb(TEXT_ADMINCONTACTFORM_EDIT_CONTENT, '');
								$this->navbar_title = TEXT_ADMINCONTACTFORM_EDIT_CONTENT;
								break;
								
						case 'update':
								$this->initData();
								$this->getData();
								$this->update();
								$cAdmin->appendBreadcrumb(TEXT_ADMINCONTACTFORM_EDIT_CONTENT, '');
								$this->navbar_title = TEXT_ADMINCONTACTFORM_EDIT_CONTENT;
								break;
								
						case 'create':
								$this->create();
								$cAdmin->appendBreadcrumb(TEXT_ADMINCONTACTFORM_NEW_CONTENT, '');
								$this->navbar_title = TEXT_ADMINCONTACTFORM_NEW_CONTENT;
								break;
						
						case 'new':
								$this->initData();
								$this->data['url'] = 'index.php?s=cAdmincontactform&amp;action=create';
								$cAdmin->appendBreadcrumb(TEXT_ADMINCONTACTFORM_NEW_CONTENT, '');
								$this->navbar_title = TEXT_ADMINCONTACTFORM_NEW_CONTENT;
								break;
						default:
								$this->getList();
								break;
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Prepare data for the editor.
		///////////////////////////////////////////////////////////////////
		function initData() {
				$this->data['data']['id'] = 0;
				$this->data['data']['title'] = '';
				$this->data['data']['template'] = 0;
				$this->data['data']['template_processing_successful'] = 0;
				$this->data['data']['mail_subject'] = '';
				$this->data['data']['mail_from'] = '';
				$this->data['data']['mail_to'] = '';
				
		}
		
		///////////////////////////////////////////////////////////////////
		// Prepare Detail Data for the editor.
		///////////////////////////////////////////////////////////////////
		private function initDetailData() {
				$this->data['detail_data']['id'] = 0;
				$this->data['detail_data']['contactform_id'] = 0;
				$this->data['detail_data']['status'] = 1;
				$this->data['detail_data']['title'] = '';
				$this->data['detail_data']['name'] = '';
				$this->data['detail_data']['value'] = '';
				$this->data['detail_data']['description'] = '';
				$this->data['detail_data']['required'] = 0;
				$this->data['detail_data']['error_text_variable'] = '';
				$this->data['detail_data']['input_type'] = 0;
				$this->data['detail_data']['contactform_details_parent_id'] = 0;
				$this->data['detail_data']['sort_order'] = $this->getHighestDetailSortOrder() + 10;
				$this->data['detail_data']['input_processing'] = '';
		}
		
		///////////////////////////////////////////////////////////////////
		// Get the highest detail sort order..
		///////////////////////////////////////////////////////////////////
		private function getHighestDetailSortOrder() {
				if(!isset($this->data) || !isset($this->data['data']) || !isset($this->data['data']['id'])) {
						return 0;
				}
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT MAX(sort_order) AS max_sort_order ' .
						'FROM ' . $db->table('contactform_details') . ' ' .
						'WHERE ' .
								'contactform_id = :contactform_id ' .
						'LIMIT 1'
				);
				$db->bind(':contactform_id', (int)$this->data['data']['id']);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(isset($tmp['max_sort_order'])) {
						return (int)$tmp['max_sort_order'];
				}
				
				return 0;
		}
		
		///////////////////////////////////////////////////////////////////
		// getList
		///////////////////////////////////////////////////////////////////
		function getList() {
				$this->data['list'] = $this->loadList();
		}
		
		///////////////////////////////////////////////////////////////////
		// getList
		///////////////////////////////////////////////////////////////////
		function getDetailList() {
				$this->data['detail_list'] = $this->loadDetailList();
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
						/////////////////////////////// Detail Menu ///////////////////////////////////////////////////////////////////////////
						case 'edit_detail':
								$this->drawDetailEditor();
								break;
						case 'detail_new':
								$this->drawDetailEditor();
								break;
						case 'list_details':
								$this->drawDetailList();
								break;
						
						/////////////////////////////// Outer Menu ////////////////////////////////////////////////////////////////////////////
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
		// Draw the editor.
		///////////////////////////////////////////////////////////////////
		function drawEditor() {
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$renderer->assign('TEMPLATES', $this->loadContactformTemplates());
				$renderer->render('site/admincontactform/editor.html');
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the detail editor.
		///////////////////////////////////////////////////////////////////
		function drawDetailEditor() {
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$renderer->render('site/admincontactform/detail_editor.html');
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the list view.
		///////////////////////////////////////////////////////////////////
		function drawList() {
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$renderer->render('site/admincontactform/list.html');
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the list view.
		///////////////////////////////////////////////////////////////////
		function drawDetailList() {
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$renderer->render('site/admincontactform/detail_list.html');
		}
		
		///////////////////////////////////////////////////////////////////
		// update an entry
		///////////////////////////////////////////////////////////////////
		public function update() {
				$id = (int)core()->getGetVar('id');
				
				if(0 == $id) {
						header('Location: index.php?s=cAdmincontactform&error=27');
						die;
				}
				
				$data['id'] = (int)$id;
				$data['title'] = core()->getPostVar('title');
				$data['template'] = (int)core()->getPostVar('template');
				$data['template_processing_successful'] = (int)core()->getPostVar('template_processing_successful');
				$data['mail_subject'] = core()->getPostVar('mail_subject');
				$data['mail_from'] = core()->getPostVar('mail_from');
				$data['mail_to'] = core()->getPostVar('mail_to');
				
				if(false === cAdmincontactform::checkContactformExistenceById((int)$data['id'])) {
						header('Location: index.php?s=cAdmincontactform&error=27');
						die;
				}
				
				$id = $this->save($data);
				
				if(empty($id)) {
						header('Location: index.php?s=cAdmincontactform&error=28');
						die;
				}
	
				header('Location: index.php?s=cAdmincontactform&action=edit&id=' . $id . '&success=7');
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Update an detail entry.
		///////////////////////////////////////////////////////////////////
		public function updateDetail() {
				$detail_id = (int)core()->getGetVar('detail_id');
				
				if(0 == $detail_id) {
						header('Location: index.php?s=cAdmincontactform&error=35');
						die;
				}
				
				$data['id'] = $detail_id;
				$data['contactform_id'] = (int)core()->getPostVar('contactform_id');
				$data['status'] = (int)core()->getPostVar('status');
				$data['title'] = core()->getPostVar('title');
				$data['name'] = core()->getPostVar('name');
				$data['value'] = core()->getPostVar('value');
				$data['description'] = core()->getPostVar('description');
				$data['required'] = (int)core()->getPostVar('required');
				$data['error_text_variable'] = core()->getPostVar('error_text_variable');
				$data['input_type'] = (int)core()->getPostVar('input_type');
				$data['contactform_details_parent_id'] = (int)core()->getPostVar('contactform_details_parent_id');
				$data['sort_order'] = (int)core()->getPostVar('sort_order');
				$data['input_processing'] = core()->getPostVar('input_processing');
				
				if(false === cAdmincontactform::checkContactformDetailsExistenceById((int)$data['id'])) {
						header('Location: index.php?s=cAdmincontactform&error=35');
						die;
				}
				
				$id = $this->saveDetail($data);
				
				if(empty($id)) {
						header('Location: index.php?s=cAdmincontactform&error=36');
						die;
				}
	
				header('Location: index.php?s=cAdmincontactform&action=edit_detail&id=' . $this->data['data']['id'] . '&detail_id=' . $id . '&success=12');
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Check if the item exists (by id)
		///////////////////////////////////////////////////////////////////
		public static function checkContactformExistenceById($id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT id FROM ' . $db->table('contactform') . ' WHERE id = :id LIMIT 1;');
				$db->bind(':id', (int)$id);
				$result = $db->execute();
				
				$retval = array();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(isset($tmp['id'])) {
						return true;
				}
				
				return false;
		}
		
		///////////////////////////////////////////////////////////////////
		// Check if the item exists (by id)
		///////////////////////////////////////////////////////////////////
		public static function checkContactformDetailsExistenceById($id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT id FROM ' . $db->table('contactform_details') . ' WHERE id = :id LIMIT 1;');
				$db->bind(':id', (int)$id);
				$result = $db->execute();
				
				$retval = array();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(isset($tmp['id'])) {
						return true;
				}
				
				return false;
		}
		
		///////////////////////////////////////////////////////////////////
		// Create entry.
		///////////////////////////////////////////////////////////////////
		function create() {
				$data['id'] = 0;
				$data['title'] = core()->getPostVar('title');
				$data['template'] = (int)core()->getPostVar('template');
				$data['template_processing_successful'] = (int)core()->getPostVar('template_processing_successful');
				$data['mail_subject'] = core()->getPostVar('mail_subject');
				$data['mail_from'] = core()->getPostVar('mail_from');
				$data['mail_to'] = core()->getPostVar('mail_to');
				
				$id = $this->save($data);
				
				if(empty($id)) {
						header('Location: index.php?s=cAdmincontactform&error=29');
						die;
				}
	
				header('Location: index.php?s=cAdmincontactform&action=edit&id=' . $id . '&success=8');
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Create detail entry.
		///////////////////////////////////////////////////////////////////
		function createDetail() {
				$data['id'] = 0;
				$data['contactform_id'] = (int)core()->getPostVar('contactform_id');
				$data['status'] = (int)core()->getPostVar('status');
				$data['title'] = core()->getPostVar('title');
				$data['name'] = core()->getPostVar('name');
				$data['value'] = core()->getPostVar('value');
				$data['description'] = core()->getPostVar('description');
				$data['required'] = (int)core()->getPostVar('required');
				$data['error_text_variable'] = core()->getPostVar('error_text_variable');
				$data['input_type'] = (int)core()->getPostVar('input_type');
				$data['contactform_details_parent_id'] = (int)core()->getPostVar('contactform_details_parent_id');
				$data['sort_order'] = (int)core()->getPostVar('sort_order');
				$data['input_processing'] = core()->getPostVar('input_processing');
				
				$id = $this->saveDetail($data);
				
				if(empty($id)) {
						header('Location: index.php?s=cAdmincontactform&error=34');
						die;
				}
	
				header('Location: index.php?s=cAdmincontactform&action=edit_detail&id=' . $this->data['data']['id'] . '&detail_id=' . $id . '&success=11');
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Loads a list of entries.
		///////////////////////////////////////////////////////////////////
		public function loadList() {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT id, title, template, template_processing_successful, mail_subject, mail_from, mail_to FROM ' . $db->table('contactform') . ' ' .
						'ORDER BY title;'
				);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$retval[] = $tmp;
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////
		// Loads a list of entries.
		///////////////////////////////////////////////////////////////////
		public function loadDetailList() {
			$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT ' .
								'id, contactform_id, status, title, name, value, description, required, error_text_variable, ' .
								'input_type, contactform_details_parent_id, sort_order, input_processing ' .
						'FROM ' . $db->table('contactform_details') . ' ' .
						'WHERE contactform_id = :contactform_id ' .
						'ORDER BY sort_order;'
				);
				$db->bind(':contactform_id', $this->data['data']['id']);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$retval[] = $tmp;
				}
				
				return $retval;
		}
	
		/////////////////////////////////////////////////////////////////////////////////
		// Save data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public function save($data) {
				$id = (int)$data['id'];
		
				if(0 === $id) {
						return $this->createInDB($data);
				}
				
				$this->updateInDB($id, $data);
				
				return $data['id'];
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Save detail data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public function saveDetail($data) {
				$id = (int)$data['id'];
		
				if(0 === $id) {
						return $this->createDetailInDB($data);
				}
				
				$this->updateDetailInDB($id, $data);
				
				return $data['id'];
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Create data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public function createInDB($data) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'INSERT INTO ' . $db->table('contactform') . ' (title, template, template_processing_successful, mail_subject, mail_from, mail_to) ' .
						'VALUES(:title, :template, :template_processing_successful, :mail_subject, :mail_from, :mail_to)'
				);
				$db->bind(':title', $data['title']);
				$db->bind(':template', (int)$data['template']);
				$db->bind(':template_processing_successful', (int)$data['template_processing_successful']);
				$db->bind(':mail_subject', $data['mail_subject']);
				$db->bind(':mail_from', $data['mail_from']);
				$db->bind(':mail_to', $data['mail_to']);
				$db->execute();
				
				return $db->insertId();
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Create detail data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public function createDetailInDB($data) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'INSERT INTO ' . $db->table('contactform_details') . 
						' (contactform_id, status, title, name, value, description, required, error_text_variable, input_type, contactform_details_parent_id, sort_order, input_processing) ' .
						'VALUES(:contactform_id, :status, :title, :name, :value, :description, :required, :error_text_variable, :input_type, :contactform_details_parent_id, :sort_order, :input_processing)'
				);
				$db->bind(':contactform_id', $this->data['data']['id']);
				$db->bind(':status', $data['status']);
				$db->bind(':title', $data['title']);
				$db->bind(':name', $data['name']);
				$db->bind(':value', $data['value']);
				$db->bind(':description', $data['description']);
				$db->bind(':required', $data['required']);
				$db->bind(':error_text_variable', $data['error_text_variable']);
				$db->bind(':input_type', $data['input_type']);
				$db->bind(':contactform_details_parent_id', $data['contactform_details_parent_id']);
				$db->bind(':sort_order', $data['sort_order']);
				$db->bind(':input_processing', $data['input_processing']);
				$db->execute();
				
				return $db->insertId();
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Update data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public function updateInDB($id, $data) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('contactform') . ' SET ' .
								'title = :title, ' .
								'template = :template, ' .
								'template_processing_successful = :template_processing_successful, ' .
								'mail_subject = :mail_subject, ' .
								'mail_from = :mail_from, ' .
								'mail_to = :mail_to ' .
						'WHERE ' .
								'id = :id'
				);
				$db->bind(':title', $data['title']);
				$db->bind(':template', (int)$data['template']);
				$db->bind(':template_processing_successful', (int)$data['template_processing_successful']);
				$db->bind(':mail_subject', $data['mail_subject']);
				$db->bind(':mail_from', $data['mail_from']);
				$db->bind(':mail_to', $data['mail_to']);
				
				$db->bind(':id', (int)$id);
				$db->execute();
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Update detail data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public function updateDetailInDB($id, $data) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('contactform_details') . ' SET ' .
								'contactform_id = :contactform_id, ' .
								'title = :title, ' .
								'status = :status, ' .
								'title = :title, ' .
								'name = :name, ' .
								'value = :value, ' .
								'description = :description, ' .
								'required = :required, ' .
								'error_text_variable = :error_text_variable, ' .
								'input_type = :input_type, ' .
								'contactform_details_parent_id = :contactform_details_parent_id, ' .
								'sort_order = :sort_order, ' .
								'input_processing = :input_processing ' .
						'WHERE ' .
								'id = :id'
				);
				$db->bind(':contactform_id', (int)$this->data['data']['id']);
				$db->bind(':status', (int)$data['status']);
				$db->bind(':title', $data['title']);
				$db->bind(':name', $data['name']);
				$db->bind(':value', $data['value']);
				$db->bind(':description', $data['description']);
				$db->bind(':required', (int)$data['required']);
				$db->bind(':error_text_variable', $data['error_text_variable']);
				$db->bind(':input_type', (int)$data['input_type']);
				$db->bind(':contactform_details_parent_id', (int)$data['contactform_details_parent_id']);
				$db->bind(':sort_order', (int)$data['sort_order']);
				$db->bind(':input_processing', $data['input_processing']);
				
				$db->bind(':id', (int)$id);
				$db->execute();
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Loads a database entry.
		/////////////////////////////////////////////////////////////////////////////////
		public function getData() {
				$id = (int)core()->getGetVar('id');
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT id, title, template, template_processing_successful, mail_subject, mail_from, mail_to FROM ' . $db->table('contactform') . ' ' .
						'WHERE ' .
								'id = :id ' .
						'LIMIT 1'
				);
				$db->bind(':id', (int)$id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				$this->data['data'] = $tmp;
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Loads a database entry.
		/////////////////////////////////////////////////////////////////////////////////
		public function getDetailData() {
				$detail_id = (int)core()->getGetVar('detail_id');
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT id, contactform_id, status, title, name, value, description, required, error_text_variable, input_type, contactform_details_parent_id, sort_order, input_processing ' .
						'FROM ' . $db->table('contactform_details') . ' ' .
						'WHERE ' .
								'id = :id ' .
						'LIMIT 1'
				);
				$db->bind(':id', (int)$detail_id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				$this->data['detail_data'] = $tmp;
		}
		
		///////////////////////////////////////////////////////////////////////////////
		// Load contactform templates.
		///////////////////////////////////////////////////////////////////////////////
		public function loadContactformTemplates() {
				$templates = cAdmincms::loadList();
				return $templates;
		}
		
		//////////////////////////////////////////////////////////////////////////////////
		// Hook us into the system in the additional hooks..
		//////////////////////////////////////////////////////////////////////////////////
    public static function setAdditionalHooks() {
				core()->setHook('cCore|init', 'addMenuBarEntries');
				
    }
		
		//////////////////////////////////////////////////////////////////////////////////
		// Callback function, adds a menu item.
		//////////////////////////////////////////////////////////////////////////////////
		public static function addMenuBarEntries() {
				$cAdmin = core()->getInstance('cAdmin');
				
				if(false !== $cAdmin) {
						$admin_menu_entry_path = array(
								array(
										'position' => 100,
										'title' => 'Inhalte'
								),
								array(
										'position' => 500,
										'title' => 'Kontakt-Formulare'
								)
						);
		        $cAdmin->registerAdminMenuEntry($admin_menu_entry_path, 'index.php?s=cAdmincontactform');
				}
		}
}
?>