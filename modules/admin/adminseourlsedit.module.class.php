<?php

class cAdminseourlsedit extends cModule {
		var $template = 'admin';
		var $navbar_title = TEXT_MODULE_TITLE_ADMINNSEOURLSEDIT;
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
				if(false === cAccount::adminrightCheck('cAdminseourlsedit', 'USE_MODULE', (int)$_SESSION['user_id'])) {
						header('Location: index.php?s=cAdmin&error=21');
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
				$cAdmin->appendBreadcrumb(TEXT_MODULE_TITLE_ADMINSEOURLSEDIT, 'index.php?s=cAdminseourlsedit');
				
				switch($this->action) {
						case 'edit':
								$this->initData();
								$this->getData();
								$this->data['url'] = 'index.php?s=cAdminseourlsedit&amp;action=update&amp;id=' . (int)$this->data['data']['id'];
								$cAdmin->appendBreadcrumb(TEXT_ADMINSEOURLSEDIT_EDIT_CONTENT, '');
								$this->navbar_title = TEXT_ADMINSEOURLSEDIT_EDIT_CONTENT;
								break;
								
						case 'update':
								$this->initData();
								$this->getData();
								$this->update();
								$cAdmin->appendBreadcrumb(TEXT_ADMINSEOURLSEDIT_EDIT_CONTENT, '');
								$this->navbar_title = TEXT_ADMINSEOURLSEDIT_EDIT_CONTENT;
								break;
								
						case 'create':
								$this->create();
								$cAdmin->appendBreadcrumb(TEXT_ADMINSEOURLSEDIT_NEW_CONTENT, '');
								$this->navbar_title = TEXT_ADMINSEOURLSEDIT_NEW_CONTENT;
								break;
						
						case 'new':
								$this->initData();
								$this->data['url'] = 'index.php?s=cAdminseourlsedit&amp;action=create';
								$cAdmin->appendBreadcrumb(TEXT_ADMINSEOURLSEDIT_NEW_CONTENT, '');
								$this->navbar_title = TEXT_ADMINSEOURLSEDIT_NEW_CONTENT;
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
				$this->data['data']['querystring'] = '';
				$this->data['data']['seourl'] = '';
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
				$renderer->render('site/seourlsedit/editor.html');
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the list view.
		///////////////////////////////////////////////////////////////////
		function drawList() {
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$renderer->render('site/seourlsedit/list.html');
		}
		
		///////////////////////////////////////////////////////////////////
		// update an entry
		///////////////////////////////////////////////////////////////////
		public function update() {
				$id = (int)core()->getGetVar('id');
				
				if(0 == $id) {
						header('Location: index.php?s=cAdminseourlsedit&error=22');
						die;
				}
				
				$data['id'] = $id;
				$data['site_id'] = (int)core()->getPostVar('site_id');
				$data['querystring'] = core()->getPostVar('querystring');
				$data['seourl'] = core()->getPostVar('seourl');
				
				if(false === cAdminseourlsedit::checkSeourlsExistenceById((int)$data['id'])) {
						header('Location: index.php?s=cAdminseourlsedit&error=22');
						die;
				}
				
				$id = $this->save($data);
				
				if(empty($id)) {
						header('Location: index.php?s=cAdminseourlsedit&error=23');
						die;
				}
	
				header('Location: index.php?s=cAdminseourlsedit&action=edit&id=' . $id . '&success=4');
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Check if the item exists (by id)
		///////////////////////////////////////////////////////////////////
		public static function checkSeourlsExistenceById($id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT id FROM ' . $db->table('seourls') . ' WHERE id = :id LIMIT 1;');
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
				$data['site_id'] = core()->getPostVar('site_id');
				$data['querystring'] = core()->getPostVar('querystring');
				$data['seourl'] = core()->getPostVar('seourl');
				
				$id = $this->save($data);
				
				if(empty($id)) {
						header('Location: index.php?s=cAdminseourlsedit&error=24');
						die;
				}
	
				header('Location: index.php?s=cAdminseourlsedit&action=edit&id=' . $id . '&success=5');
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Loads a list of entries.
		///////////////////////////////////////////////////////////////////
		public function loadList() {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT id, site_id, querystring, seourl FROM ' . $db->table('seourls') . ' ' .
						'ORDER BY seourl;'
				);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						
						//Load additional data
						$tmp['site_url'] = cSite::loadSiteUrl($tmp['site_id']);
						
						if(false === $tmp['site_url']) {
								$tmp['site_url'] = '';
						}
						
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
		// Create data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public function createInDB($data) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'INSERT INTO ' . $db->table('seourls') . ' (site_id, querystring, seourl) ' .
						'VALUES(:site_id, :querystring, :seourl)'
				);
				$db->bind(':site_id', (int)$data['site_id']);
				$db->bind(':querystring', $data['querystring']);
				$db->bind(':seourl', $data['seourl']);
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
						'UPDATE ' . $db->table('seourls') . ' SET ' .
								'site_id = :site_id, ' .
								'querystring = :querystring, ' .
								'seourl = :seourl ' .
						'WHERE ' .
								'id = :id'
				);
				$db->bind(':site_id', (int)$data['site_id']);
				$db->bind(':querystring', $data['querystring']);
				$db->bind(':seourl', $data['seourl']);
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
						'SELECT id, site_id, querystring, seourl FROM ' . $db->table('seourls') . ' ' .
						'WHERE ' .
								'id = :id ' .
						'LIMIT 1'
				);
				$db->bind(':id', (int)$id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(false === $tmp) {
						return false;
				}
				
				//Load additional data
				$tmp['site_url'] = cSite::loadSiteUrl($tmp['site_id']);
				
				if(false === $tmp['site_url']) {
						$tmp['site_url'] = '';
				}
				
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
										'position' => 400,
										'title' => 'SEO Urls'
								)
						);
		        $cAdmin->registerAdminMenuEntry($admin_menu_entry_path, 'index.php?s=cAdminseourlsedit');
				}
		}
}
?>