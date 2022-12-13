<?php

class cAdminsystemrights extends cModule {
		var $template = 'admin';
		var $navbar_title = TEXT_MODULE_TITLE_ADMINSYSTEMRIGHTS;
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
				if(false === cAccount::adminrightCheck('cAdminsystemrights', 'USE_MODULE', (int)$_SESSION['user_id'])) {
						header('Location: index.php?s=cAdmin&error=173');
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
				$cAdmin->appendBreadcrumb(TEXT_MODULE_TITLE_ADMINSYSTEMRIGHTS, 'index.php?s=cAdminsystemrights');
				
				switch($this->action) {
						case 'edit':
								$this->initData();
								$this->getContent();
								$this->data['url'] = 'index.php?s=cAdminsystemrights&amp;action=update&amp;id=' . (int)$this->data['data']['id'];
								$cAdmin->appendBreadcrumb(TEXT_ADMINSYSTEMRIGHTS_EDIT, '');
								$this->navbar_title = TEXT_ADMINSYSTEMRIGHTS_EDIT;
								break;
								
						case 'update':
								$this->initData();
								$this->getContent();
								$this->update();
								$cAdmin->appendBreadcrumb(TEXT_ADMINSYSTEMRIGHTS_EDIT, '');
								$this->navbar_title = TEXT_ADMINSYSTEMRIGHTS_EDIT;
								break;
								
						case 'create':
								$this->create();
								$cAdmin->appendBreadcrumb(TEXT_ADMINSYSTEMRIGHTS_NEW, '');
								$this->navbar_title = TEXT_ADMINSYSTEMRIGHTS_NEW;
								break;
						
						case 'new':
								$this->initData();
								$this->data['url'] = 'index.php?s=cAdminsystemrights&amp;action=create';
								$cAdmin->appendBreadcrumb(TEXT_ADMINSYSTEMRIGHTS_NEW, '');
								$this->navbar_title = TEXT_ADMINSYSTEMRIGHTS_NEW;
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
				$this->data['data']['module'] = '';
				$this->data['data']['rightskey'] = '';
				$this->data['data']['created_on'] = '1990-01-01 00:00:00';
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
				$renderer->render('site/adminsystemrights/editor.html');
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the list view.
		///////////////////////////////////////////////////////////////////
		function drawList() {
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$renderer->render('site/adminsystemrights/list.html');
		}
		
		///////////////////////////////////////////////////////////////////
		// update an entry
		///////////////////////////////////////////////////////////////////
		public function update() {
				$id = (int)core()->getGetVar('id');
				
				if(0 == $id) {
						header('Location: index.php?s=cAdminsystemrights&error=174');
						die;
				}
				
				$data['id'] = $id;
				$data['module'] = core()->getPostVar('module');
				$data['rightskey'] = core()->getPostVar('rightskey');
				
				if(false === cSystemrights::checkExistenceById((int)$data['id'])) {
						header('Location: index.php?s=cAdminsystemrights&error=175');
						die;
				}
				
				$id = $this->save($data);
				
				if(empty($id)) {
						header('Location: index.php?s=cAdminsystemrights&error=176');
						die;
				}
	
				header('Location: index.php?s=cAdminsystemrights&action=edit&id=' . $id . '&success=57');
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Crate a content entry.
		///////////////////////////////////////////////////////////////////
		function create() {
				$data['id'] = 0;
				$data['module'] = core()->getPostVar('module');
				$data['rightskey'] = core()->getPostVar('rightskey');
				
				$id = $this->save($data);
				
				if(empty($id)) {
						header('Location: index.php?s=cAdminsystemrights&error=177');
						die;
				}
	
				header('Location: index.php?s=cAdminsystemrights&action=edit&id=' . $id . '&success=58');
				die;
		}

		///////////////////////////////////////////////////////////////////
		// Loads a list of content entries.
		///////////////////////////////////////////////////////////////////
		public function loadList() {
				return cSystemrights::loadList();
		}
	
		/////////////////////////////////////////////////////////////////////////////////
		// Save content data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public function save($data) {
				$id = (int)$data['id'];
		
				if(0 === $id) {
						return cSystemrights::createInDB($data);
				}
				
				cSystemrights::updateInDB($id, $data);
				
				return $data['id'];
		}
		
		
		
		/////////////////////////////////////////////////////////////////////////////////
		// Loads a content entry.
		/////////////////////////////////////////////////////////////////////////////////
		public function getContent() {
				$id = (int)core()->getGetVar('id');
				
				$this->data['data'] = cSystemrights::getDataById($id);
		}
		
		//////////////////////////////////////////////////////////////////////////////////
		// Hook us into the system in the additional hooks..
		//////////////////////////////////////////////////////////////////////////////////
    public static function setAdditionalHooks() {
				//core()->setHook('cCore|init', 'addMenuBarEntries');
				
    }
		
		//////////////////////////////////////////////////////////////////////////////////
		// Callback function, adds a menu item.
		//////////////////////////////////////////////////////////////////////////////////
		public static function addMenuBarEntries() {
				$cAdmin = core()->getInstance('cAdmin');
				
				if(false !== $cAdmin) {
						$admin_menu_entry_path = array(
								array(
										'position' => 1000,
										'title' => 'Einstellungen'
								),
								array(
										'position' => 0,
										'title' => 'Systemrechte'
								)
						);
		        $cAdmin->registerAdminMenuEntry($admin_menu_entry_path, 'index.php?s=cAdminsystemrights');
				}
		}
}
?>