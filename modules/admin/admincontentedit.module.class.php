<?php

class cAdmincontentedit extends cModule {
		var $template = 'admin';
		var $navbar_title = TEXT_MODULE_TITLE_ADMINNCONTENTEDIT;
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
				if(false === cAccount::adminrightCheck('cAdmincontentedit', 'USE_MODULE', (int)$_SESSION['user_id'])) {
						header('Location: index.php?s=cAdmin&error=18');
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
				$cAdmin->appendBreadcrumb(TEXT_MODULE_TITLE_ADMINCONTENTEDIT, 'index.php?s=cAdmincontentedit');
				
				switch($this->action) {
						case 'edit':
								$this->initData();
								$this->getContent();
								$this->data['url'] = 'index.php?s=cAdmincontentedit&amp;action=update&amp;id=' . (int)$this->data['data']['id'];
								$cAdmin->appendBreadcrumb(TEXT_ADMINCONTENTEDIT_EDIT_CONTENT, '');
								$this->navbar_title = TEXT_ADMINCONTENTEDIT_EDIT_CONTENT;
								break;
								
						case 'update':
								$this->initData();
								$this->getContent();
								$this->update();
								$cAdmin->appendBreadcrumb(TEXT_ADMINCONTENTEDIT_EDIT_CONTENT, '');
								$this->navbar_title = TEXT_ADMINCONTENTEDIT_EDIT_CONTENT;
								break;
								
						case 'create':
								$this->create();
								$cAdmin->appendBreadcrumb(TEXT_ADMINCONTENTEDIT_NEW_CONTENT, '');
								$this->navbar_title = TEXT_ADMINCONTENTEDIT_NEW_CONTENT;
								break;
						
						case 'new':
								$this->initData();
								$this->data['url'] = 'index.php?s=cAdmincontentedit&amp;action=create';
								$cAdmin->appendBreadcrumb(TEXT_ADMINCONTENTEDIT_NEW_CONTENT, '');
								$this->navbar_title = TEXT_ADMINCONTENTEDIT_NEW_CONTENT;
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
				$this->data['data']['site_id'] = 0;
				$this->data['data']['cms_key'] = '';
				$this->data['data']['name'] = '';
				$this->data['data']['default_navbar_id'] = 0;
				$this->data['data']['meta_title'] = '';
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
				$renderer->assign('SITE_URLS', cSite::loadSiteUrls());
				$renderer->assign('NAVBARS', cAdminnavbaredit::loadNavbarList());
				$renderer->render('site/contentedit/editor.html');
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the list view.
		///////////////////////////////////////////////////////////////////
		function drawList() {
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$renderer->render('site/contentedit/list.html');
		}
		
		///////////////////////////////////////////////////////////////////
		// update an entry
		///////////////////////////////////////////////////////////////////
		public function update() {
				$id = (int)core()->getGetVar('id');
				
				if(0 == $id) {
						header('Location: index.php?s=cAdmincontentedit&error=19');
						die;
				}
				
				$data['id'] = $id;
				$data['site_id'] = (int)core()->getPostVar('site_id');
				$data['cms_key'] = core()->getPostVar('cms_key');
				$data['name'] = core()->getPostVar('name');
				$data['default_navbar_id'] = core()->getPostVar('default_navbar_id');
				$data['meta_title'] = core()->getPostVar('meta_title');
				
				if(false === cAdmincontentedit::checkContentExistenceById((int)$data['id'])) {
						header('Location: index.php?s=cAdmincontentedit&error=19');
						die;
				}
				
				$id = $this->save($data);
				
				if(empty($id)) {
						header('Location: index.php?s=cAdmincontentedit&error=20');
						die;
				}
	
				header('Location: index.php?s=cAdmincontentedit&action=edit&id=' . $id . '&success=3');
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Check if the content exists (by id)
		///////////////////////////////////////////////////////////////////
		public static function checkContentExistenceById($id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT id FROM ' . $db->table('cms') . ' WHERE id = :id LIMIT 1;');
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
		// Crate a content entry.
		///////////////////////////////////////////////////////////////////
		function create() {
				$data['id'] = 0;
				$data['site_id'] = (int)core()->getPostVar('site_id');
				$data['cms_key'] = core()->getPostVar('cms_key');
				$data['name'] = core()->getPostVar('name');
				$data['default_navbar_id'] = core()->getPostVar('default_navbar_id');
				$data['meta_title'] = core()->getPostVar('meta_title');
				
				$id = $this->save($data);
				
				if(empty($id)) {
						header('Location: index.php?s=cAdmincontentedit&error=20');
						die;
				}
				
				$this->createContentFiles($id);
	
				header('Location: index.php?s=cAdmincontentedit&action=edit&id=' . $id . '&success=3');
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Create content files.
		///////////////////////////////////////////////////////////////////
		public function createContentFiles($id) {
				$descs = array();
				$lang = core()->get('lang');
				
				foreach($lang->langs as $language) {
						cAdmincms::saveContentText((int)$id, (int)$language['id'], 'This is a new entry.');
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Loads a list of content entries.
		///////////////////////////////////////////////////////////////////
		public function loadList() {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT id, site_id, cms_key, name, default_navbar_id, meta_title ' .
						'FROM ' . $db->table('cms') . ' ' .
						'ORDER BY name;'
				);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$retval[] = $tmp;
				}
				
				return $retval;
		}
	
		/////////////////////////////////////////////////////////////////////////////////
		// Save content data in database.
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
		// Create content data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public function createInDB($data) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'INSERT INTO ' . $db->table('cms') . 
								' (site_id, cms_key, name, default_navbar_id, meta_title) ' .
						'VALUES(:site_id, :cms_key, :name, :default_navbar_id, :meta_title)'
				);
				$db->bind(':site_id', (int)$data['site_id']);
				$db->bind(':cms_key', $data['cms_key']);
				$db->bind(':name', $data['name']);
				$db->bind(':default_navbar_id', (int)$data['default_navbar_id']);
				$db->bind(':meta_title', $data['meta_title']);
				$db->execute();
				
				return $db->insertId();
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Update navbar data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public function updateInDB($id, $data) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('cms') . ' SET ' .
								'site_id = :site_id, ' .
								'cms_key = :cms_key, ' .
								'name = :name, ' .
								'default_navbar_id = :default_navbar_id, ' .
								'meta_title = :meta_title ' .
						'WHERE ' .
								'id = :id'
				);
				
				$db->bind(':site_id', (int)$data['site_id']);
				$db->bind(':cms_key', $data['cms_key']);
				$db->bind(':name', $data['name']);
				$db->bind(':default_navbar_id', (int)$data['default_navbar_id']);
				$db->bind(':meta_title', $data['meta_title']);
				$db->bind(':id', (int)$id);
				$db->execute();
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Loads a content entry.
		/////////////////////////////////////////////////////////////////////////////////
		public function getContent() {
				$id = (int)core()->getGetVar('id');
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT id, site_id, cms_key, name, default_navbar_id, meta_title ' .
						'FROM ' . $db->table('cms') . ' ' .
						'WHERE ' .
								'id = :id'
				);
				$db->bind(':id', (int)$id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				$this->data['data'] = $tmp;
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
										'position' => 200,
										'title' => 'Content/Inhalte'
								)
						);
		        $cAdmin->registerAdminMenuEntry($admin_menu_entry_path, 'index.php?s=cAdmincontentedit');
				}
		}
}
?>