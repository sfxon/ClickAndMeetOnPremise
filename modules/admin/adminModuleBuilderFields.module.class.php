<?php

class cAdminModuleBuilderFields extends cModule {
		var $template = 'admin';
		var $navbar_title = 'Admin Module Builder Fields';
		var $navbar_id = 0;
		var $errors = array();
		var $errors_description = array();
		
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
				if(false === cAccount::adminrightCheck('cAdminModuleBuilderFields', 'USE_MODULE', (int)$_SESSION['user_id'])) {
						header('Location: index.php?s=cAdmin&error=311');
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
				$this->module_builder_id = (int)core()->getGetVar('module_builder_id');
				
				//Check if main item exists
				$this->module_builder_data = cModuleBuilder::loadById($this->module_builder_id);
				
				if(false === $this->module_builder_data) {
						header('Location: index.php?s=cAdminModuleBuilder&error=305');
						die;
				}

				$this->action = core()->getGetVar('action');
				$cAdmin = core()->getInstance('cAdmin');
				$cAdmin->appendBreadcrumb('Admin Module Builder', 'index.php?s=cAdminModuleBuilder');
				$cAdmin->appendBreadcrumb($this->module_builder_data['title'], '');
				$cAdmin->appendBreadcrumb('Admin Module Builder Fields', 'index.php?s=cAdminModuleBuilderFields&amp;module_builder_id=' . (int)$this->module_builder_id);
				
				switch($this->action) {
						case 'delete':
								$this->initData();
								$this->getContent();
								$this->delete();
								break;
						case 'confirm_delete':
								$this->initData();
								$this->getContent();
								$this->data['url'] = 'index.php?s=cAdminModuleBuilderFields&amp;action=delete&amp;id=' . (int)$this->data['data']['id'] . '&amp;module_builder_id=' . (int)$this->module_builder_id;
								$cAdmin->appendBreadcrumb('L&ouml;schen best&auml;tigen', '');
								$this->navbar_title = 'L&ouml;schen best&auml;tigen';
								break;
						
						case 'edit':
								$this->initData();
								$this->getContent();
								$this->data['url'] = 'index.php?s=cAdminModuleBuilderFields&amp;action=update&amp;id=' . (int)$this->data['data']['id'] . '&amp;module_builder_id=' . (int)$this->module_builder_id;
								$cAdmin->appendBreadcrumb('Bearbeiten', '');
								$this->navbar_title = 'Bearbeiten';
								break;
								
						case 'update':
								$this->initData();
								$this->getContent();
								$this->update();
								$cAdmin->appendBreadcrumb('Bearbeiten', '');
								$this->navbar_title = 'Bearbeiten';
								break;
								
						case 'create':
								$this->create();
								$cAdmin->appendBreadcrumb('Neu', '');
								$this->navbar_title = 'Neu';
								break;
						
						case 'new':
								$this->initData();
								$this->data['url'] = 'index.php?s=cAdminModuleBuilderFields&amp;action=create&amp;module_builder_id=' . (int)$this->module_builder_id;
								$cAdmin->appendBreadcrumb('Neu', '');
								$this->navbar_title = 'Neu';
								break;
						default:
								$this->getList();
								break;
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// "Delete" an entry..
		// We do not really delete any entry. We just flag it,
		// so it does not appear anymore.
		///////////////////////////////////////////////////////////////////
		private function delete() {
				//check if user wants to delete or abort the action
				$button_do_not_delete = core()->getPostVar('button_do_not_delete');
				$button_delete = core()->getPostVar('button_delete');
				
				//abort button..
				if($button_do_not_delete !== NULL && $button_do_not_delete === 'not_delete') {
						header('Location: index.php?s=cAdminModuleBuilderFields&error=328&module_builder_id=' . (int)$this->module_builder_id);
						die;
				}
				
				//delete button
				if($button_delete !== NULL && $button_delete === 'delete') {
						cModuleBuilderFields::deleteById($this->data['data']['id']);
						
						header('Location: index.php?s=cAdminModuleBuilderFields&success=329&module_builder_id=' . (int)$this->module_builder_id);
						die;
				}
				
				//unknown operation (we didn't get proper input).
				header('Location: index.php?s=cAdmin&error=330');
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Prepare data for the editor.
		///////////////////////////////////////////////////////////////////
		function initData() {
				$this->data['data']['id'] = 0;
				$this->data['data']['module_builder_id'] = 0;
				$this->data['data']['title'] = '';
				$this->data['data']['field_name'] = '';
				$this->data['data']['data_type'] = '';
				$this->data['data']['length'] = '';
				$this->data['data']['foreign_table_id'] = '';
				$this->data['data']['sort_order'] = 0;
		}
		
		///////////////////////////////////////////////////////////////////
		// getList
		///////////////////////////////////////////////////////////////////
		function getList() {
				$this->data['list'] = $this->loadList($this->module_builder_id);
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
						case 'confirm_delete':
								$this->drawConfirmDeleteDialog();
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
		// Draw the editor.
		///////////////////////////////////////////////////////////////////
		function drawConfirmDeleteDialog() {
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$renderer->assign('SITE_URLS', cSite::loadSiteUrls());
				$renderer->assign('NAVBARS', cAdminnavbaredit::loadNavbarList());
				$renderer->assign('ERRORS', $this->errors);
				$renderer->render('site/adminModuleBuilderFields/confirm_delete_dialog.html');
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the editor.
		///////////////////////////////////////////////////////////////////
		function drawEditor() {
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$renderer->assign('MODULE_BUILDER_ID', $this->module_builder_id);
				$renderer->render('site/adminModuleBuilderFields/editor.html');
		}

		///////////////////////////////////////////////////////////////////
		// Draw the list view.
		///////////////////////////////////////////////////////////////////
		function drawList() {
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$renderer->assign('MODULE_BUILDER_ID', $this->module_builder_id);
				$renderer->render('site/adminModuleBuilderFields/list.html');
		}
		
		///////////////////////////////////////////////////////////////////
		// update an entry
		///////////////////////////////////////////////////////////////////
		public function update() {
				$id = (int)core()->getGetVar('id');
				
				if(0 == $id) {
						header('Location: index.php?s=cAdminModuleBuilderFields&error=313&module_builder_id=' . $this->module_builder_id);
						die;
				}
				
				$data['id'] = $id;
				$data['module_builder_id'] = (int)core()->getGetVar('module_builder_id');
				$data['title'] = core()->getPostVar('title');
				$data['field_name'] = core()->getPostVar('field_name');
				$data['data_type'] = core()->getPostVar('data_type');
				$data['length'] = core()->getPostVar('length');
				$data['foreign_table_id'] = (int)core()->getPostVar('foreign_table_id');
				$data['sort_order'] = (int)core()->getPostVar('sort_order');
				
				if(NULL === cModuleBuilderFields::loadById((int)$data['id'])) {
						header('Location: index.php?s=cAdminModuleBuilderFields&error=314&module_builder_id=' . $this->module_builder_id);
						die;
				}
				
				$id = $this->save($data);
				
				if(empty($id)) {
						header('Location: index.php?s=cAdminModuleBuilderFields&error=315&module_builder_id=' . $this->module_builder_id);
						die;
				}
	
				header('Location: index.php?s=cAdminModuleBuilderFields&action=edit&id=' . $id . '&success=316&module_builder_id=' . $this->module_builder_id);
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Crate a content entry.
		///////////////////////////////////////////////////////////////////
		function create() {
				$data['id'] = 0;
				$data['module_builder_id'] = (int)core()->getGetVar('module_builder_id');
				$data['title'] = core()->getPostVar('title');
				$data['field_name'] = core()->getPostVar('field_name');
				$data['data_type'] = core()->getPostVar('data_type');
				$data['length'] = core()->getPostVar('length');
				$data['foreign_table_id'] = (int)core()->getPostVar('foreign_table_id');
				$data['sort_order'] = (int)core()->getPostVar('sort_order');
				
				$id = $this->save($data);
				
				if(empty($id)) {
						header('Location: index.php?s=cAdminModuleBuilderFields&error=317&module_builder_id=' . $this->module_builder_id);
						die;
				}
	
				header('Location: index.php?s=cAdminModuleBuilderFields&action=edit&id=' . $id . '&success=318&module_builder_id=' . $this->module_builder_id);
				die;
		}

		///////////////////////////////////////////////////////////////////
		// Loads a list of content entries.
		///////////////////////////////////////////////////////////////////
		public function loadList() {
				return cModuleBuilderFields::loadListByModuleBuilderId($this->module_builder_id);
		}
	
		/////////////////////////////////////////////////////////////////////////////////
		// Save data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public function save($data) {
				$id = (int)$data['id'];
		
				if(0 === $id) {
						return cModuleBuilderFields::createInDB($data);
				}
				
				cModuleBuilderFields::updateInDB($id, $data);
				
				return $data['id'];
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Loads a content entry.
		/////////////////////////////////////////////////////////////////////////////////
		public function getContent() {
				$id = (int)core()->getGetVar('id');
				$this->data['data'] = cModuleBuilderFields::loadById($id);
		}
}
?>