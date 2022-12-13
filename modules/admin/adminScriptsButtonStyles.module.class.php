<?php

class cAdminScriptsButtonStyles extends cModule {
		var $template = 'admin';
		var $navbar_title = TEXT_MODULE_TITLE__ADMIN_SCRIPTS_BUTTON_STYLES;
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
				if(false === cAccount::adminrightCheck('cAdminScriptsButtonStyles', 'USE_MODULE', (int)$_SESSION['user_id'])) {
						header('Location: index.php?s=cAdmin&error=225');
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
				$cAdmin->appendBreadcrumb(TEXT_MODULE_TITLE__ADMIN_SCRIPTS_BUTTON_STYLES, 'index.php?s=cAdminScriptsButtonStyles');
				
				switch($this->action) {
						case 'delete_confirm':
								$this->initData();
								$this->getContent();
								$this->delete();
								break;
						case 'delete':
								$this->initData();
								$this->getContent();
								$this->data['url'] = 'index.php?s=cAdminScriptsButtonStyles&amp;action=delete_confirm&amp;id=' . (int)$this->data['data']['id'];
								$cAdmin->appendBreadcrumb(TEXT_ADMIN_SCRIPTS_BUTTON_STYLES_DELETE_CONTENT, '');
								$this->navbar_title = TEXT_ADMIN_SCRIPTS_BUTTON_STYLES_DELETE_CONTENT;
								break;
						case 'edit':
								$this->initData();
								$this->getContent();
								$this->data['url'] = 'index.php?s=cAdminScriptsButtonStyles&amp;action=update&amp;id=' . (int)$this->data['data']['id'];
								$cAdmin->appendBreadcrumb(TEXT_ADMIN_SCRIPTS_BUTTON_STYLES_EDIT_CONTENT, '');
								$this->navbar_title = TEXT_ADMIN_SCRIPTS_BUTTON_STYLES_EDIT_CONTENT;
								break;
						case 'update':
								$this->initData();
								$this->getContent();
								$this->update();
								$cAdmin->appendBreadcrumb(TEXT_ADMIN_SCRIPTS_BUTTON_STYLES_EDIT_CONTENT, '');
								$this->navbar_title = TEXT_ADMIN_SCRIPTS_BUTTON_STYLES_EDIT_CONTENT;
								break;		
						case 'new':
								$this->initData();
								$this->data['url'] = 'index.php?s=cAdminScriptsButtonStyles&amp;action=create';
								$cAdmin->appendBreadcrumb(TEXT_ADMIN_SCRIPTS_BUTTON_STYLES_NEW_CONTENT, '');
								$this->navbar_title = TEXT_ADMIN_SCRIPTS_BUTTON_STYLES_NEW_CONTENT;
								break;
						case 'create':
								$this->initData();
								$this->getContent();
								$this->create();
								$cAdmin->appendBreadcrumb(TEXT_ADMIN_SCRIPTS_BUTTON_STYLES_NEW_CONTENT, '');
								$this->navbar_title = TEXT_ADMIN_SCRIPTS_BUTTON_STYLES_NEW_CONTENT;
								break;
						default:
								$this->getList();
								$cAdmin->appendBreadcrumb(TEXT_ADMIN_SCRIPTS_BUTTON_STYLES_LIST, '');
								$this->navbar_title = TEXT_ADMIN_SCRIPTS_BUTTON_STYLES_LIST;
								break;
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Delete an entry.
		///////////////////////////////////////////////////////////////////
		function delete() {
				$id = (int)core()->getGetVar('id');
				
				if(0 == $id) {
						header('Location: index.php?s=cAdminScriptsButtonStyles&error=226');
						die;
				}
				
				$data['id'] = $id;
				
				if(NULL === cScriptsButtonStyles::loadById((int)$data['id'])) {
						header('Location: index.php?s=cAdminScriptsButtonStyles&error=226');
						die;
				}
				
				cScriptsButtonStyles::deleteFromDB($id);
				
				header('Location: index.php?s=cAdminScriptsButtonStyles&success=227');
				die;
		}
				
		
		///////////////////////////////////////////////////////////////////
		// Prepare data for the editor.
		///////////////////////////////////////////////////////////////////
		function initData() {
				$this->data['data']['id'] = 0;
				$this->data['data']['title'] = '';
				$this->data['data']['css_id'] = '';
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
				$renderer->render('site/AdminScriptsButtonStyles/delete_confirm_dialog.html');
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the editor.
		///////////////////////////////////////////////////////////////////
		function drawEditor() {
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$renderer->assign('CSS_CLASSES', cScriptsCSS::loadList());
				$renderer->render('site/AdminScriptsButtonStyles/editor.html');
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
				$renderer->render('site/AdminScriptsButtonStyles/list.html');
		}
		
		///////////////////////////////////////////////////////////////////
		// update an entry
		///////////////////////////////////////////////////////////////////
		public function update() {
				$id = (int)core()->getGetVar('id');
				
				if(0 == $id) {
						header('Location: index.php?s=cAdminScriptsButtonStyles&error=228');
						die;
				}
				
				$data['id'] = $id;
				$data['title'] = core()->getPostVar('title');
				$data['css_id'] = (int)core()->getPostVar('css_id');
				
				if(NULL === cScriptsButtonStyles::loadById((int)$data['id'])) {
						header('Location: index.php?s=cAdminScriptsButtonStyles&error=228');
						die;
				}
				
				$id = $this->save($data);
				
				if(empty($id)) {
						header('Location: index.php?s=cAdminScriptsButtonStyles&error=229');
						die;
				}
	
				header('Location: index.php?s=cAdminScriptsButtonStyles&action=edit&id=' . $id . '&success=230');
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Crate a content entry.
		///////////////////////////////////////////////////////////////////
		function create() {
				$data['id'] = 0;
				$data['title'] = core()->getPostVar('title');
				$data['css_id'] = (int)core()->getPostVar('css_id');
				
				$id = $this->save($data);
				
				if(empty($id)) {
						header('Location: index.php?s=cAdminScriptsButtonStyles&error=231');
						die;
				}
	
				header('Location: index.php?s=cAdminScriptsButtonStyles&action=edit&id=' . $id . '&success=232');
				die;
		}

		///////////////////////////////////////////////////////////////////
		// Loads a list of content entries.
		///////////////////////////////////////////////////////////////////
		public function loadList() {
				$list = cScriptsButtonStyles::loadList();
				
				$list = $this->addCSSIdsData($list);
				
				return $list;
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Daten zur CSS ID hinzufügen.
		/////////////////////////////////////////////////////////////////////////////////
		public function addCSSIdsData($list) {
				foreach($list as $index => $data) {
						$tmp = cScriptsCSS::loadById($data['css_id']);
						
						if(false !== $tmp) {
								$data['css_id_data'] = $tmp;
						}
						
						$list[$index] = $data;
				}
				
				return $list;
		}
	
		/////////////////////////////////////////////////////////////////////////////////
		// Save data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public function save($data) {
				$id = (int)$data['id'];
		
				if(0 === $id) {
						return cScriptsButtonStyles::createInDB($data);
				}
				
				cScriptsButtonStyles::updateInDB($id, $data);
				
				return $data['id'];
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Loads a content entry.
		/////////////////////////////////////////////////////////////////////////////////
		public function getContent() {
				$id = (int)core()->getGetVar('id');
				$this->data['data'] = cScriptsButtonStyles::loadById($id);
		}
}
?>