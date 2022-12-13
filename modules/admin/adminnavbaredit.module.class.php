<?php

class cAdminnavbaredit extends cModule {
		var $template = 'admin';
		var $navbar_title = TEXT_MODULE_TITLE_ADMINNAVBAREDIT;
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
				if(false === cAccount::adminrightCheck('cAdminnavbaredit', 'USE_MODULE', (int)$_SESSION['user_id'])) {
						header('Location: index.php?s=cAdmin&error=notallowed');
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
				$cAdmin->appendBreadcrumb(TEXT_MODULE_TITLE_ADMINNAVBAREDIT, 'index.php?s=cAdminnavbaredit');
				
				switch($this->action) {
						case 'list_navbar_items':
								$this->initData();
								$this->getNavbar();
								$this->getNavbarItemList();
								$cAdmin->appendBreadcrumb(TEXT_MODULE_TITLE_ADMINNAVBAREDIT_ITEM_LIST, '');
								$this->navbar_title = TEXT_MODULE_TITLE_ADMINNAVBAREDIT_ITEM_LIST;
								break;
								
						case 'new_navbar_item':
								$this->initData();
								$this->getNavbar();
								$this->initNavbarItemData();
								$this->data['url'] = 'index.php?s=cAdminnavbaredit&amp;action=create_navbar_item&amp;navbar_id=' . (int)$this->data['id'];
								$cAdmin->appendBreadcrumb(TEXT_MODULE_TITLE_ADMINNAVBAREDIT_ITEM_LIST, 'index.php?s=cAdminnavbaredit&amp;action=list_navbar_items&amp;navbar_id=' . (int)$this->data['id']);
								$cAdmin->appendBreadcrumb(TEXT_ADMINNAVBAREDIT_NEW_NAVBAR_ITEM, '');
								$this->navbar_title = TEXT_MODULE_TITLE_ADMINNAVBAREDIT_NEW_NAVBAR_ITEM;
								break;
								
						case 'create_navbar_item':
								$this->initData();
								$this->getNavbar();
								$this->initNavbarItemData();
								$this->editorCreateNavbarItem();
								$cAdmin->appendBreadcrumb(TEXT_MODULE_TITLE_ADMINNAVBAREDIT_ITEM_LIST, 'index.php?s=cAdminnavbaredit&amp;action=list_navbar_items&amp;navbar_id=' . (int)$this->data['id']);
								$cAdmin->appendBreadcrumb(TEXT_ADMINNAVBAREDIT_NEW_NAVBAR_ITEM, '');
								$this->navbar_title = TEXT_MODULE_TITLE_ADMINNAVBAREDIT_NEW_NAVBAR_ITEM;
								break;
								
						case 'edit_navbar_item':
								$this->initData();
								$this->getNavbar();
								$this->initNavbarItemData();
								$this->getNavbarItemData();
								$this->data['url'] = 'index.php?s=cAdminnavbaredit&amp;action=update_navbar_item&amp;navbar_id=' . (int)$this->data['id'] . '&amp;navbar_item_id=' . (int)$this->data['item']['id'];
								$cAdmin->appendBreadcrumb(TEXT_MODULE_TITLE_ADMINNAVBAREDIT_ITEM_LIST, 'index.php?s=cAdminnavbaredit&amp;action=list_navbar_items&amp;navbar_id=' . (int)$this->data['id']);
								$cAdmin->appendBreadcrumb(TEXT_MODULE_TITLE_ADMINNAVBAREDIT_EDIT_NAVBAR_ITEM, '');
								$this->navbar_title = TEXT_MODULE_TITLE_ADMINNAVBAREDIT_EDIT_NAVBAR_ITEM;
								break;
								
						case 'update_navbar_item':
								$this->initData();
								$this->getNavbar();
								$this->initNavbarItemData();
								$this->editorUpdateNavbarItem();
								$cAdmin->appendBreadcrumb(TEXT_MODULE_TITLE_ADMINNAVBAREDIT_ITEM_LIST, 'index.php?s=cAdminnavbaredit&amp;action=list_navbar_items&amp;navbar_id=' . (int)$this->data['id']);
								$cAdmin->appendBreadcrumb(TEXT_ADMINNAVBAREDIT_EDIT_NAVBAR_ITEM, '');
								$this->navbar_title = TEXT_MODULE_TITLE_ADMINNAVBAREDIT_EDIT_NAVBAR_ITEM;
								break;
						
						case 'edit':
								$this->initData();
								$this->getNavbar();
								$this->data['url'] = 'index.php?s=cAdminnavbaredit&amp;action=update&amp;navbar_id=' . (int)$this->data['navbar_id'];
								$cAdmin->appendBreadcrumb(TEXT_ADMINNAVBAREDIT_EDIT_NAVBAR, '');
								$this->navbar_title = TEXT_ADMINNAVBAREDIT_EDIT_NAVBAR;
								break;
								
						case 'update':
								$this->getNavbar();
								$this->editorUpdate();
								$cAdmin->appendBreadcrumb(TEXT_ADMINNAVBAREDIT_EDIT_NAVBAR, '');
								$this->navbar_title = TEXT_ADMINNAVBAREDIT_EDIT_NAVBAR;
								break;
								
						case 'create':
								$this->editorCreate();
								$cAdmin->appendBreadcrumb(TEXT_ADMINNAVBAREDIT_NEW_NAVBAR, '');
								$this->navbar_title = TEXT_ADMINNAVBAREDIT_NEW_NAVBAR;
								break;
						
						case 'new':
								$this->initData();
								$this->data['url'] = 'index.php?s=cAdminnavbaredit&amp;action=create';
								$cAdmin->appendBreadcrumb(TEXT_ADMINNAVBAREDIT_NEW_NAVBAR, '');
								$this->navbar_title = TEXT_ADMINNAVBAREDIT_NEW_NAVBAR;
								break;
						default:
								$this->getList();
								break;
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// @INFO		prepare data for the editor
		///////////////////////////////////////////////////////////////////
		function initData() {
				$this->data['data']['id'] = 0;
				$this->data['navbar_id'] = $this->data['data']['id'];
				$this->data['data']['title'] = '';
				$this->data['data']['status'] = '';
				$this->data['data']['navbar_url'] = '';
				
				$this->data['data']['parent_navbar_entries_item'] = core()->getGetVar('navbar_item_id');
		}
		
		///////////////////////////////////////////////////////////////////
		// Init data for one item.
		///////////////////////////////////////////////////////////////////
		public function initNavbarItemData() {
				$item = array();
				$item['id'] = 0;
				$item['navbar_id'] = $this->data['data']['id'];
				$item['sort_order'] = 0;
				$item['status'] = 1;
				$item['data'] = '';
				$item['data_type'] = 1;
				$item['logged_in_only'] = 0;
				
				//Load descriptions..
				$descs = array();
				$lang = core()->get('lang');
				
				foreach($lang->langs as $language) {
						$descs[] = array(
								'language_id' => $language['id'],
								'language_name' => $language['name'],
								'title' => '',
								'description' => ''
						);
				}
				
				$item['descriptions'] = $descs;
				$this->data['item'] = $item;
		}
		
		///////////////////////////////////////////////////////////////////
		// getList
		///////////////////////////////////////////////////////////////////
		function getList() {
				$this->data['list'] = cAdminnavbaredit::loadNavbarList();
		}
		
		///////////////////////////////////////////////////////////////////
		// Get Navbar Items List.
		///////////////////////////////////////////////////////////////////
		public function getNavbarItemList() {
				$lang = core()->get('lang');
				$language_id = (int)$lang->getCurLangId();
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT ne.id, ne.status, ne.sort_order, ned.title, ne.navbar_id ' . 
						'FROM ' . $db->table('navbar_entries') . ' ne ' .
						'JOIN ' . $db->table('navbar_entries_descriptions') . ' ned ON ne.id = ned.navbar_entries_id AND ned.language_id = :language_id ' .
						'WHERE ne.navbar_id = :navbar_id ' .
						'ORDER BY ne.sort_order;'
				);
				$db->bind(':language_id', (int)$language_id);
				$db->bind(':navbar_id', (int)$this->data['id']);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$tmp['sub_navbar_id'] = $this->getNavbarEntriesSubNavbar($tmp['id']);
						$retval[] = $tmp;
				}
				
				$this->data['list'] = $retval;
		}
		
		///////////////////////////////////////////////////////////////////
		// Get subnavigations id..
		///////////////////////////////////////////////////////////////////
		public function getNavbarEntriesSubNavbar($navbar_entries_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT id ' . 
						'FROM ' . $db->table('navbar') . ' ' .
						'WHERE parent_navbar_entries_item = :navbar_entries_id ' .
						'LIMIT 1;'
				);
				$db->bind(':navbar_entries_id', (int)$navbar_entries_id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(false !== $tmp) {
						return $tmp['id'];
				}
				
				return false;
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
						case 'list_navbar_items':
								$this->drawNavbarItemsList();
								break;
						case 'new_navbar_item':
								$this->drawNavbarItemEditor();
								break;
						case 'edit_navbar_item':
								$this->drawNavbarItemEditor();
								break;
						case 'new':
								$this->drawEditorTemplate();
								break;
						case 'edit':
								$this->drawEditorTemplate();
								break;
						default:
								//$this->getPagination();
								$this->drawList();
								break;
				}
		}
		
		
		
		///////////////////////////////////////////////////////////////////
		// drawEditorTemplate
		///////////////////////////////////////////////////////////////////
		function drawEditorTemplate() {
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$renderer->render('site/navbaredit/editor.html');
		}
		
		///////////////////////////////////////////////////////////////////
		// draw lists
		///////////////////////////////////////////////////////////////////
		function drawList() {
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$renderer->render('site/navbaredit/list.html');
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw a list of all navbar_items.
		///////////////////////////////////////////////////////////////////
		function drawNavbarItemsList() {
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$renderer->render('site/navbaredit/navbar_items_list.html');
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the navbar_entries (item) editor.
		///////////////////////////////////////////////////////////////////
		public function drawNavbarItemEditor() {				
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$renderer->render('site/navbaredit/navbar_item_editor.html');
		}
		
		///////////////////////////////////////////////////////////////////
		// load a navbar entry
		///////////////////////////////////////////////////////////////////
		public function getNavbar() {
				$this->data['id'] = (int)core()->getGetVar('navbar_id');
				$this->data['data'] = $this->loadNavbarData($this->data['id']);
		}
		
		///////////////////////////////////////////////////////////////////
		// Load Navbar item data..
		///////////////////////////////////////////////////////////////////
		public function getNavbarItemData() {
				$id = (int)core()->getGetVar('navbar_item_id');
				$this->data['item'] = $this->loadNavbarItemData($id);
		}
		
		///////////////////////////////////////////////////////////////////
		// update an entry
		///////////////////////////////////////////////////////////////////
		public function editorUpdate() {
				$id = (int)core()->getPostVar('id');
				
				if(0 == $id) {
						header('Location: index.php?s=cAdminnavbaredit&error=12');
						die;
				}
				
				$data['id'] = $id;
				$data['title'] = core()->getPostVar('title');
				$data['status'] = (int)core()->getPostVar('status');
				$data['parent_navbar_entries_item'] = (int)core()->getPostVar('parent_navbar_entries_item');
				
				if(false === cAdminnavbaredit::checkNavbarExistenceByNavbarId((int)$data['id'])) {
						header('Location: index.php?s=cAdminnavbaredit&error=13');
						die;
				}
				
				$id = $this->saveNavbar($data);
				
				if(empty($id)) {
						header('Location: index.php?s=cAdminnavbaredit&error=14');
						die;
				}
	
				header('Location: index.php?s=cAdminnavbaredit&action=edit&navbar_id=' . $id . '&success=10');
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Update a navbar item entry.
		///////////////////////////////////////////////////////////////////
		public function editorUpdateNavbarItem() {
				$id = (int)core()->getGetVar('navbar_item_id');
				
				if(0 == $id) {
						header('Location: index.php?s=cAdminnavbaredit&error=16');
						die;
				}
				
				if(false === cAdminnavbaredit::checkNavbarEntriesExistenceByNavbarEntriesId((int)$id)) {
						header('Location: index.php?s=cAdminnavbaredit&error=16');
						die;
				}
				
				if(false === cAdminnavbaredit::checkNavbarExistenceByNavbarId((int)$this->data['id'])) {
						header('Location: index.php?s=cAdminnavbaredit&error=13');
						die;
				}
				
				$data['item']['id'] = $id;
				
				//get the languages texts..
				$descs = array();
				$lang = core()->get('lang');
				
				foreach($lang->langs as $language) {
						$title = '';
						$description = '';
						
						if(isset($_POST['title']) && isset($_POST['title'][$language['id']])) {
								$title = $_POST['title'][$language['id']];
						}
						
						if(isset($_POST['description']) && isset($_POST['description'][$language['id']])) {
								$description = $_POST['description'][$language['id']];
						}
						
						$descs[] = array(
								'language_id' => $language['id'],
								'language_name' => $language['name'],
								'title' => $title,
								'description' => $description
						);
				}
				
				//get other basic data
				$data['item']['navbar_id'] = (int)core()->getPostVar('navbar_id');
				$data['item']['sort_order'] = (int)core()->getPostVar('sort_order');
				$data['item']['status'] = (int)core()->getPostVar('status');
				$data['item']['data'] = core()->getPostVar('data');
				$data['item']['data_type'] = (int)core()->getPostVar('data_type');
				$data['item']['logged_in_only'] = (int)core()->getPostVar('logged_in_only');
				$data['item']['descriptions'] = $descs;
				
				$id = $this->saveNavbarItem($data['item']);
				
				if(empty($id)) {
						header('Location: index.php?s=cAdminnavbaredit&error=17');
						die;
				}
	
				header('Location: index.php?s=cAdminnavbaredit&action=edit_navbar_item&navbar_id=' . $this->data['id'] . '&navbar_item_id=' . $id . '&success=10');
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Check if the navbar exists (by id)
		///////////////////////////////////////////////////////////////////
		public static function checkNavbarExistenceByNavbarId($navbar_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT id FROM ' . $db->table('navbar') . ' WHERE id = :id LIMIT 1;');
				$db->bind(':id', (int)$navbar_id);
				$result = $db->execute();
				
				$retval = array();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(isset($tmp['id'])) {
						return true;
				}
				
				return false;
		}
		
		///////////////////////////////////////////////////////////////////
		// Check if the navbar entry exists (by id)
		///////////////////////////////////////////////////////////////////
		public static function checkNavbarEntriesExistenceByNavbarEntriesId($navbar_entries_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT id FROM ' . $db->table('navbar_entries') . ' WHERE id = :id LIMIT 1;');
				$db->bind(':id', (int)$navbar_entries_id);
				$result = $db->execute();
				
				$retval = array();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(isset($tmp['id'])) {
						return true;
				}
				
				return false;
		}
		
		
		///////////////////////////////////////////////////////////////////
		// Crate a Navbar.
		///////////////////////////////////////////////////////////////////
		function editorCreate() {
				$data['id'] = 0;
				$data['title'] = core()->getPostVar('title');
				$data['status'] = (int)core()->getPostVar('status');
				$data['parent_navbar_entries_item'] = (int)core()->getPostVar('parent_navbar_entries_item');
				
				$id = $this->saveNavbar($data);
				
				if(empty($id)) {
						header('Location: index.php?s=cAdminnavbaredit&error=14');
						die;
				}
	
				header('Location: index.php?s=cAdminnavbaredit&action=edit&navbar_id=' . $id . '&success=10');
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Create a Navbar Item Entry.
		///////////////////////////////////////////////////////////////////
		public function editorCreateNavbarItem() {
				//get the languages texts..
				$descs = array();
				$lang = core()->get('lang');
				
				foreach($lang->langs as $language) {
						$title = '';
						$description = '';
						
						if(isset($_POST['title']) && isset($_POST['title'][$language['id']])) {
								$title = $_POST['title'][$language['id']];
						}
						
						if(isset($_POST['description']) && isset($_POST['description'][$language['id']])) {
								$description = $_POST['description'][$language['id']];
						}
						
						
						
						$descs[] = array(
								'language_id' => $language['id'],
								'language_name' => $language['name'],
								'title' => $title,
								'description' => $description
						);
				}
				
				//get other basic data
				$data['item']['navbar_id'] = (int)core()->getPostVar('navbar_id');
				$data['item']['sort_order'] = (int)core()->getPostVar('sort_order');
				$data['item']['status'] = (int)core()->getPostVar('status');
				$data['item']['data'] = core()->getPostVar('data');
				$data['item']['data_type'] = (int)core()->getPostVar('data_type');
				$data['item']['logged_in_only'] = (int)core()->getPostVar('logged_in_only');
				$data['item']['descriptions'] = $descs;

				if(false === cAdminnavbaredit::checkNavbarExistenceByNavbarId((int)$this->data['id'])) {
						header('Location: index.php?s=cAdminnavbaredit&error=13');
						die;
				}
				
				$id = $this->saveNavbarItem($data['item']);
				
				if(empty($id)) {
						header('Location: index.php?s=cAdminnavbaredit&error=15');
						die;
				}
	
				header('Location: index.php?s=cAdminnavbaredit&action=edit_navbar_item&navbar_id=' . $this->data['id'] . '&navbar_item_id=' . $id . '&success=10');
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Loads a list of navbars.
		///////////////////////////////////////////////////////////////////
		public static function loadNavbarList($parent_navbar_entries_id = 0) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT id, status, title FROM ' . $db->table('navbar') . ' WHERE parent_navbar_entries_item = :parent_navbar_entries_id ORDER BY title;');
				$db->bind(':parent_navbar_entries_id', (int)$parent_navbar_entries_id);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$retval[] = $tmp;
				}
				
				return $retval;
		}
		
		////////////////////////////////////////////////////////////////
		// Load navbar data..
		////////////////////////////////////////////////////////////////
		public function loadNavbarData($navbar_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT id, title, status, navbar_url, parent_navbar_entries_item FROM ' . $db->table('navbar') . ' WHERE id = :id LIMIT 1 ');
				$db->bind(':id', (int)$navbar_id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(false !== $tmp) {
						//Try to load the entries for this navbar.+
						$db->useInstance('systemdb');
						$db->setQuery(
								'SELECT id, navbar_id, sort_order, status, data, data_type, logged_in_only ' .
								'FROM ' . $db->table('navbar_entries') . ' ' .
								'WHERE navbar_id = :navbar_id AND status = 1 ' .
								'ORDER BY sort_order'
						);
						$db->bind(':navbar_id', (int)$navbar_id);
						$result = $db->execute();
						
						$entries = array();
						
						while($result->next()) {
								$tmp_entry['url'] = '';
								$tmp_entry = $result->fetchArrayAssoc();
								
								//Load Language specific data.
								$db->setQuery(
										'SELECT title, description ' .
										'FROM ' . $db->table('navbar_entries_descriptions') . ' ' .
										'WHERE navbar_entries_id = :navbar_entries_id ORDER BY language_id'
								);
								$db->bind(':navbar_entries_id', (int)$tmp_entry['id']);
								$result_description = $db->execute();
								
								while($result_description->next()) {
										$tmp_description[] = $result_description->fetchArrayAssoc();
										
								}
								
								$tmp_entry['description'] = $tmp_description;
								
								//Load Sub-Navbar..
								$tmp_entry['has_sub_navbar'] = $this->hasSubNavbar($tmp_entry['id']);
								$entries[] = $tmp_entry;
						}
						
						$tmp['navbar_entries'] = $entries;
				}
				
				return $tmp;
		}
		
		////////////////////////////////////////////////////////////////
		// Load navbar item data.
		////////////////////////////////////////////////////////////////
		public function loadNavbarItemData($navbar_entries_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT id, navbar_id, sort_order, status, data, data_type, logged_in_only ' .
						'FROM ' . $db->table('navbar_entries') . ' ' .
						'WHERE id = :id ' .
						'LIMIT 1;'
				);
				$db->bind(':id', (int)$navbar_entries_id);
				$result = $db->execute();
				
				$item = $result->fetchArrayAssoc();
				
				if(false !== $item) {
						//Load description fields
						$descriptions = $this->loadNavbarItemDescriptions($item['id']);
				}
				
				$item['descriptions'] = $descriptions;
				return $item;
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Load navbar items descriptions.
		/////////////////////////////////////////////////////////////////////////////////
		public function loadNavbarItemDescriptions($navbar_entries_id) {
				//get all languages..
				$descs = array();
				$lang = core()->get('lang');
				$retval = array();
				
				foreach($lang->langs as $language) {
						$tmp = $this->loadNavbarItemDescription($navbar_entries_id, $language['id']);
						
						$title = '';
						$description = '';
						
						if(isset($tmp['title'])) {
								$title = $tmp['title'];
						}
						
						if(isset($tmp['description'])) {
								$description = $tmp['description'];
						}
						
						$retval[] = array(
								'language_id' => $language['id'],
								'language_name' => $language['name'],
								'title' => $title,
								'description' => $description
						);
				}
				
				return $retval;
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Loads a navbar item language entry from database.
		/////////////////////////////////////////////////////////////////////////////////
		public function loadNavbarItemDescription($navbar_entries_id, $language_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT navbar_entries_id, language_id, title, description ' .
						'FROM ' . $db->table('navbar_entries_descriptions') . ' ' .
						'WHERE ' .
								'navbar_entries_id = :navbar_entries_id AND ' .
								'language_id = :language_id ' .
						'LIMIT 1;'
				);
				$db->bind(':navbar_entries_id', (int)$navbar_entries_id);
				$db->bind(':language_id', (int)$language_id);
				$result_description = $db->execute();
				
				$tmp = $result_description->fetchArrayAssoc();
				
				return $tmp;
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Checks, if a navbar item has a subnavbar..
		/////////////////////////////////////////////////////////////////////////////////
		public function hasSubNavbar($parent_navbar_entries_item) {
				if(0 === (int)$parent_navbar_entries_item) {
						return array();
				}
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT count(id) as quantity FROM ' . $db->table('navbar') . ' WHERE parent_navbar_entries_item = :parent_navbar_entries_item AND status = 1');
				$db->bind(':parent_navbar_entries_item', (int)$parent_navbar_entries_item);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(isset($tmp['quantity'])) {
						return $tmp['quantity'];
				}
				
				return 0;
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Save navbar data in database
		/////////////////////////////////////////////////////////////////////////////////
		public function saveNavbar($data) {
				$id = (int)$data['id'];
		
				if(0 === $id) {
						return $this->createNavbar($data);
				}
				
				$this->updateNavbar($id, $data);
				
				return $data['id'];
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Save a navbar item.
		/////////////////////////////////////////////////////////////////////////////////
		public function saveNavbarItem($data) {
				$id = (int)$data['id'];
				
				if(0 === $id) {
						$id = $this->createNavbarItem($data);
				} else {
						$this->updateNavbarItem($id, $data);
				}
				
				$this->saveNavbarItemDescriptions($id, $data['descriptions']);
				return $id;
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Save a navbar item description.
		/////////////////////////////////////////////////////////////////////////////////
		public function saveNavbarItemDescriptions($id, $data) {
				$id = (int)$id;
				
				//walk through all descriptions..
				foreach($data as $desc) {
						//check if this entry exists..
						$tmp_desc = $this->loadNavbarItemDescription($id, $desc['language_id']);
						
						if(false === $tmp_desc) {
								$this->createNavbarItemDescription($id, $desc);
						}else {
								$this->updateNavbarItemDescription($id, $desc);
						}
						
				}
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Create navbar data in database
		/////////////////////////////////////////////////////////////////////////////////
		public function createNavbar($data) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'INSERT INTO ' . $db->table('navbar') . ' (title, status, parent_navbar_entries_item) ' .
						'VALUES(:title, :status, :parent_navbar_entries_item)'
				);
				$db->bind(':title', $data['title']);
				$db->bind(':status', (int)$data['status']);
				$db->bind(':parent_navbar_entries_item', (int)$data['parent_navbar_entries_item']);
				$db->execute();
				
				return $db->insertId();
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Create navbar item data in database
		/////////////////////////////////////////////////////////////////////////////////
		public function createNavbarItem($data) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'INSERT INTO ' . $db->table('navbar_entries') . ' (navbar_id, sort_order, status, data, data_type, logged_in_only) ' .
						'VALUES(:navbar_id, :sort_order, :status, :data, :data_type, :logged_in_only)'
				);
				$db->bind(':navbar_id', (int)$data['navbar_id']);
				$db->bind(':sort_order', (int)$data['sort_order']);
				$db->bind(':status', (int)$data['status']);
				$db->bind(':data', $data['data']);
				$db->bind(':data_type', (int)$data['data_type']);
				$db->bind(':logged_in_only', (int)$data['logged_in_only']);
				$db->execute();
				
				return $db->insertId();
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Create navbar item data in database
		/////////////////////////////////////////////////////////////////////////////////
		public function createNavbarItemDescription($navbar_entries_id, $data) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'INSERT INTO ' . $db->table('navbar_entries_descriptions') . ' (navbar_entries_id, language_id, title, description) ' .
						'VALUES(:navbar_entries_id, :language_id, :title, :description)'
				);
				$db->bind(':navbar_entries_id', (int)$navbar_entries_id);
				$db->bind(':language_id', (int)$data['language_id']);
				$db->bind(':title', $data['title']);
				$db->bind(':description', $data['description']);
				$result = $db->execute();
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Update navbar data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public function updateNavbar($id, $data) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('navbar') . ' SET ' .
								'title = :title, ' .
								'status = :status, ' .
								'parent_navbar_entries_item = :parent_navbar_entries_item ' .
						'WHERE ' .
								'id = :id'
				);
				$db->bind(':title', $data['title']);
				$db->bind(':status', (int)$data['status']);
				$db->bind(':parent_navbar_entries_item', (int)$data['parent_navbar_entries_item']);
				$db->bind(':id', (int)$id);
				$db->execute();
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Update navbar item data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public function updateNavbarItem($id, $data) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('navbar_entries') . ' SET ' .
								'navbar_id = :navbar_id, ' .
								'sort_order = :sort_order, ' .
								'status = :status, ' .
								'data = :data, ' .
								'data_type = :data_type, ' .
								'logged_in_only = :logged_in_only ' .
						'WHERE ' .
								'id = :id'
				);
				$db->bind(':navbar_id', (int)$data['navbar_id']);
				$db->bind(':sort_order', (int)$data['sort_order']);
				$db->bind(':status', (int)$data['status']);
				$db->bind(':data', $data['data']);
				$db->bind(':data_type', (int)$data['data_type']);
				$db->bind(':logged_in_only', (int)$data['logged_in_only']);
				$db->bind(':id', (int)$id);
				$db->execute();
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Update navbar item data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public function updateNavbarItemDescription($id, $data) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('navbar_entries_descriptions') . ' SET ' .
								'title = :title, ' .
								'description = :description ' .
						'WHERE ' .
								'navbar_entries_id = :navbar_entries_id AND ' .
								'language_id = :language_id;'
				);
				$db->bind(':title', $data['title']);
				$db->bind(':description', $data['description']);
				$db->bind(':navbar_entries_id', (int)$id);
				$db->bind(':language_id', (int)$data['language_id']);
				$db->execute();
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
										'position' => 100,
										'title' => 'Menüs/Navigation'
								)
						);
		        $cAdmin->registerAdminMenuEntry($admin_menu_entry_path, 'index.php?s=cAdminnavbaredit');
				}
		}
}
?>