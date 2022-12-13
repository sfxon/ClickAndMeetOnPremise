<?php

class cAdminsites extends cModule {
		var $template = 'admin';
		var $navbar_title = TEXT_MODULE_TITLE_ADMINSITES;
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
				if(false === cAccount::adminrightCheck('cAdminsites', 'USE_MODULE', (int)$_SESSION['user_id'])) {
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
				$cAdmin->appendBreadcrumb(TEXT_MODULE_TITLE_ADMINSITES, 'index.php?s=cAdminsites');
				
				switch($this->action) {
						case 'edit':
								$this->initData();
								$this->getContent();
								$this->data['url'] = 'index.php?s=cAdminsites&amp;action=update&amp;id=' . (int)$this->data['data']['id'];
								$cAdmin->appendBreadcrumb(TEXT_ADMINSITES_EDIT_CONTENT, '');
								$this->navbar_title = TEXT_ADMINSITES_EDIT_CONTENT;
								break;
								
						case 'update':
								$this->initData();
								$this->getContent();
								$this->update();
								$cAdmin->appendBreadcrumb(TEXT_ADMINSITES_EDIT_CONTENT, '');
								$this->navbar_title = TEXT_ADMINSITES_EDIT_CONTENT;
								break;
								
						case 'create':
								$this->create();
								$cAdmin->appendBreadcrumb(TEXT_ADMINSITES_NEW_CONTENT, '');
								$this->navbar_title = TEXT_ADMINSITES_NEW_CONTENT;
								break;
						
						case 'new':
								$this->initData();
								$this->data['url'] = 'index.php?s=cAdminsites&amp;action=create';
								$cAdmin->appendBreadcrumb(TEXT_ADMINSITES_NEW_CONTENT, '');
								$this->navbar_title = TEXT_ADMINSITES_NEW_CONTENT;
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
				$this->data['data']['url'] = '';
				$this->data['data']['path'] = '';
				$this->data['data']['template_path'] = '';
				$this->data['data']['status'] = 1;
				$this->data['data']['home_cms_id'] = '';
				$this->data['data']['template'] = '';
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
				$renderer->assign('TEMPLATES', $this->getAllTemplates());
				$renderer->render('site/adminsites/editor.html');
		}
		
		///////////////////////////////////////////////////////////////////
		// Get an array of templates..
		///////////////////////////////////////////////////////////////////
		public function getAllTemplates() {
				$path = 'data/templates/';
				
				$files = scandir($path);
				
				foreach($files as $index => $data) {
						if($data == '.' || $data == '..' || $data == 'admin') {
								unset($files[$index]);
						}
						
						if(!is_dir($path . $data)) {
								unset($files[$index]);
						}
				}
				
				return $files;
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the list view.
		///////////////////////////////////////////////////////////////////
		function drawList() {
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$renderer->render('site/adminsites/list.html');
		}
		
		///////////////////////////////////////////////////////////////////
		// update an entry
		///////////////////////////////////////////////////////////////////
		public function update() {
				$id = (int)core()->getGetVar('id');
				
				if(0 == $id) {
						header('Location: index.php?s=cAdminsites&error=38');
						die;
				}
				
				$data['id'] = $id;
				$data['title'] = core()->getPostVar('title');
				$data['url'] = core()->getPostVar('url');
				$data['path'] = core()->getPostVar('path');
				$data['template_path'] = core()->getPostVar('template_path');
				$data['status'] = (int)core()->getPostVar('status');
				$data['home_cms_id'] = (int)core()->getPostVar('home_cms_id');
				$data['template'] = core()->getPostVar('template');
				
				if(false === cAdminsites::checkExistenceById((int)$data['id'])) {
						header('Location: index.php?s=cAdminsites&error=38');
						die;
				}
				
				$id = $this->save($data);
				
				if(empty($id)) {
						header('Location: index.php?s=cAdminsites&error=39');
						die;
				}
	
				header('Location: index.php?s=cAdminsites&action=edit&id=' . $id . '&success=13');
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Check if the content exists (by id)
		///////////////////////////////////////////////////////////////////
		public static function checkExistenceById($id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT id FROM ' . $db->table('site') . ' WHERE id = :id LIMIT 1;');
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
				$data['title'] = core()->getPostVar('title');
				$data['url'] = core()->getPostVar('url');
				$data['path'] = core()->getPostVar('path');
				$data['template_path'] = core()->getPostVar('template_path');
				$data['status'] = (int)core()->getPostVar('status');
				$data['home_cms_id'] = (int)core()->getPostVar('home_cms_id');
				$data['template'] = core()->getPostVar('template');
				
				$id = $this->save($data);
				
				if(empty($id)) {
						header('Location: index.php?s=cAdminsites&error=40');
						die;
				}
	
				header('Location: index.php?s=cAdminsites&action=edit&id=' . $id . '&success=14');
				die;
		}

		///////////////////////////////////////////////////////////////////
		// Loads a list of content entries.
		///////////////////////////////////////////////////////////////////
		public function loadList() {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT id, title, url, path, template_path, status, home_cms_id, template ' .
						'FROM ' . $db->table('site') . ' ' .
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
						'INSERT INTO ' . $db->table('site') . 
								' (title, url, path, template_path, status, home_cms_id, template) ' .
						'VALUES(:title, :url, :path, :template_path, :status, :home_cms_id, :template)'
				);
				$db->bind(':title', $data['title']);
				$db->bind(':url', $data['url']);
				$db->bind(':path', $data['path']);
				$db->bind(':template_path', $data['template_path']);
				$db->bind(':status', (int)$data['status']);
				$db->bind(':home_cms_id', (int)$data['home_cms_id']);
				$db->bind(':template', $data['template']);
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
						'UPDATE ' . $db->table('site') . ' SET ' .
								'title = :title, ' .
								'url = :url, ' .
								'path = :path, ' .
								'template_path = :template_path, ' .
								'status = :status, ' .
								'home_cms_id = :home_cms_id, ' .
								'template = :template ' .
						'WHERE ' .
								'id = :id'
				);
				$db->bind(':title', $data['title']);
				$db->bind(':url', $data['url']);
				$db->bind(':path', $data['path']);
				$db->bind(':template_path', $data['template_path']);
				$db->bind(':status', (int)$data['status']);
				$db->bind(':home_cms_id', (int)$data['home_cms_id']);
				$db->bind(':template', $data['template']);
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
						'SELECT id, title, url, path, template_path, status, home_cms_id, template ' .
						'FROM ' . $db->table('site') . ' ' .
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
										'position' => 0,
										'title' => 'Seiten/Domains'
								)
						);
		        $cAdmin->registerAdminMenuEntry($admin_menu_entry_path, 'index.php?s=cAdminsites');
				}
		}
}
?>