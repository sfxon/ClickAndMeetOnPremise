<?php

class cAdmintextvariables extends cModule {
		var $template = 'admin';
		var $navbar_title = TEXT_MODULE_TITLE_ADMINTEXTVARIABLES;
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
				if(false === cAccount::adminrightCheck('cAdmintextvariables', 'USE_MODULE', (int)$_SESSION['user_id'])) {
						header('Location: index.php?s=cAdmin&error=30');
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
				$cAdmin->appendBreadcrumb(TEXT_MODULE_TITLE_ADMINTEXTVARIABLES, 'index.php?s=cAdmintextvariables');
				
				switch($this->action) {
						case 'edit':
								$this->initData();
								$this->getData();
								$this->data['url'] = 'index.php?s=cAdmintextvariables&amp;action=update&amp;id=' . (int)$this->data['data']['id'];
								$cAdmin->appendBreadcrumb(TEXT_ADMINTEXTVARIABLES_EDIT_CONTENT, '');
								$this->navbar_title = TEXT_ADMINTEXTVARIABLES_EDIT_CONTENT;
								break;
								
						case 'update':
								$this->initData();
								$this->getData();
								$this->update();
								$cAdmin->appendBreadcrumb(TEXT_ADMINTEXTVARIABLES_EDIT_CONTENT, '');
								$this->navbar_title = TEXT_ADMINTEXTVARIABLES_EDIT_CONTENT;
								break;
								
						case 'create':
								$this->create();
								$cAdmin->appendBreadcrumb(TEXT_ADMINTEXTVARIABLES_NEW_CONTENT, '');
								$this->navbar_title = TEXT_ADMINTEXTVARIABLES_NEW_CONTENT;
								break;
						
						case 'new':
								$this->initData();
								$this->data['url'] = 'index.php?s=cAdmintextvariables&amp;action=create';
								$cAdmin->appendBreadcrumb(TEXT_ADMINTEXTVARIABLES_NEW_CONTENT, '');
								$this->navbar_title = TEXT_ADMINTEXTVARIABLES_NEW_CONTENT;
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
				$this->data['data']['language_id'] = 1;
				$this->data['data']['name'] = '';
				$this->data['data']['filename'] = '';
				$this->data['data']['item_text'] = '';
				$this->data['data']['accepted'] = 0;
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
				$renderer->render('site/admintextvariables/editor.html');
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the list view.
		///////////////////////////////////////////////////////////////////
		function drawList() {
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$renderer->render('site/admintextvariables/list.html');
		}
		
		///////////////////////////////////////////////////////////////////
		// update an entry
		///////////////////////////////////////////////////////////////////
		public function update() {
				$id = (int)core()->getGetVar('id');
				
				if(0 == $id) {
						header('Location: index.php?s=cAdmintextvariables&error=31');
						die;
				}
				
				$data['id'] = $id;
				$data['language_id'] = core()->getPostVar('language_id');
				$data['name'] = core()->getPostVar('name');
				$data['filename'] = core()->getPostVar('filename');
				$data['item_text'] = core()->getPostVar('item_text');
				$data['accepted'] = core()->getPostVar('accepted');
				
				if(false === cAdmintextvariables::checkExistenceById((int)$data['id'])) {
						header('Location: index.php?s=cAdmintextvariables&error=31');
						die;
				}
				
				$id = $this->save($data);
				
				if(empty($id)) {
						header('Location: index.php?s=cAdminstextvariables&error=32');
						die;
				}
	
				header('Location: index.php?s=cAdmintextvariables&action=edit&id=' . $id . '&success=9');
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Check if the item exists (by id)
		///////////////////////////////////////////////////////////////////
		public static function checkExistenceById($id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT id FROM ' . $db->table('text_variables') . ' WHERE id = :id LIMIT 1;');
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
				$data['language_id'] = core()->getPostVar('language_id');
				$data['name'] = core()->getPostVar('name');
				$data['filename'] = core()->getPostVar('filename');
				$data['item_text'] = core()->getPostVar('item_text');
				$data['accepted'] = core()->getPostVar('accepted');
				
				$id = $this->save($data);
				
				if(empty($id)) {
						header('Location: index.php?s=cAdmintextvariables&error=33');
						die;
				}
	
				header('Location: index.php?s=cAdmintextvariables&action=edit&id=' . $id . '&success=10');
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Loads a list of entries.
		///////////////////////////////////////////////////////////////////
		public function loadList() {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT id, language_id, name, filename, item_text, accepted FROM ' . $db->table('text_variables') . ' ' .
						'ORDER BY id DESC;'
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
		// Save data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public function save($data) {
				$id = (int)$data['id'];
		
				if(0 === $id) {
						$data['id'] = $this->createInDB($data);
				} else {
						$this->updateInDB($id, $data);
				}
				
				$this->clearLanguageCacheFiles();
				
				return $data['id'];
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////
		// clearLanguageCacheFiles
		//////////////////////////////////////////////////////////////////////////////////////////
		function clearLanguageCacheFiles() {
				$foldername = dirname( dirname( dirname(__FILE__)  ) ) . '/data/languages/';
				$handle = opendir($foldername);
				
				while($filename = readdir($handle)) {
						$filename = $foldername . $filename;
					
						if(!is_dir($filename) && $filename!="." && $filename!="..") {
								unlink($filename);
						}
				}
				
				closedir($handle);
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Create data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public function createInDB($data) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'INSERT INTO ' . $db->table('text_variables') . ' (language_id, name, filename, item_text, accepted) ' .
						'VALUES(:language_id, :name, :filename, :item_text, :accepted)'
				);
				$db->bind(':language_id', (int)$data['language_id']);
				$db->bind(':name', $data['name']);
				$db->bind(':filename', $data['filename']);
				$db->bind(':item_text', $data['item_text']);
				$db->bind(':accepted', (int)$data['accepted']);
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
						'UPDATE ' . $db->table('text_variables') . ' SET ' .
								'language_id = :language_id, ' .
								'name = :name, ' .
								'filename = :filename, ' .
								'item_text = :item_text, ' .
								'accepted = :accepted ' .
						'WHERE ' .
								'id = :id'
				);
				$db->bind(':language_id', (int)$data['language_id']);
				$db->bind(':name', $data['name']);
				$db->bind(':filename', $data['filename']);
				$db->bind(':item_text', $data['item_text']);
				$db->bind(':accepted', (int)$data['accepted']);
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
						'SELECT id, language_id, name, filename, item_text, accepted FROM ' . $db->table('text_variables') . ' ' .
						'WHERE ' .
								'id = :id ' .
						'LIMIT 1'
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
										'position' => 600,
										'title' => 'Text-Variablen'
								)
						);
		        $cAdmin->registerAdminMenuEntry($admin_menu_entry_path, 'index.php?s=cAdmintextvariables');
				}
		}
}
?>