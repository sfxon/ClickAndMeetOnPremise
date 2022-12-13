<?php

class cAdminaccountsrights extends cModule {
		var $template = 'admin';
		var $navbar_title = TEXT_MODULE_TITLE_ADMINACCOUNTSRIGHTS;
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
				if(false === cAccount::adminrightCheck('cAdminaccountsrights', 'USE_MODULE', (int)$_SESSION['user_id'])) {
						header('Location: index.php?s=cAdmin&error=178');
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
				$this->action = core()->getGetVar('action');
				$cAdmin = core()->getInstance('cAdmin');
				$cAdmin->appendBreadcrumb(TEXT_MODULE_TITLE_ADMINACCOUNTSRIGHTS, 'index.php?s=cAdminaccountsrights');
				
				$this->initData();		//We have always to init the data, because we always have to check the user account that is to be editet.
				
				switch($this->action) {
					case 'update':
								$this->update();
								break;
						default:
								$this->data['url'] = 'index.php?s=cAdminaccountsrights&amp;action=update&amp;accounts_id=' . (int)$this->data['accounts_id'];
								$this->getList();
								break;
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Prepare data for the editor.
		///////////////////////////////////////////////////////////////////
		function initData() {
				//Get accounts id.
				$accounts_id = (int)core()->getGetVar('accounts_id');
				
				if(0 == $accounts_id) {
						header('Location: index.php?s=cAdminaccounts&error=179');
						die;
				}
				
				//Check if account exists
				$tmp = cAccount::loadUserData($accounts_id);
				
				if(empty($tmp)) {
						header('Location: index.php?s=cAdminaccounts&error=180');
						die;
				}
				
				//Check if account is master account (master account cannot be edited - if you are not the master!)
				if($tmp['superadmin'] === 1) {
						if((int)$accounts_id != (int)$_SESSION['user_id']) {
								header('Location: index.php?s=cAdminaccounts&error=181');
						}
				}
				
				$this->data['accounts_id'] = $accounts_id;
				$this->data['data']['systemrights'] = array();
				$this->data['data']['systemrights_list'] = cSystemrights::loadList();
				$this->getList();		//Loads the user based settings
		}
		
		///////////////////////////////////////////////////////////////////
		// getList
		///////////////////////////////////////////////////////////////////
		function getList() {
				$this->data['data']['systemrights'] = $this->loadList();
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
						default:
								$this->drawList();
								break;
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the list view.
		///////////////////////////////////////////////////////////////////
		function drawList() {
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$renderer->render('site/adminaccountsrights/list.html');
		}
		
		///////////////////////////////////////////////////////////////////
		// update an entry
		///////////////////////////////////////////////////////////////////
		public function update() {
				$data['systemrights'] = core()->getPostVar('systemrights');
				$this->save($data);
	
				header('Location: index.php?s=cAdminaccountsrights&action=edit&accounts_id=' . $this->data['accounts_id'] . '&success=59');
				die;
		}

		///////////////////////////////////////////////////////////////////
		// Loads a list of content entries.
		///////////////////////////////////////////////////////////////////
		public function loadList() {
				return cAdminrights::loadEntriesArrayByAccountsId($this->data['accounts_id']);
		}
	
		/////////////////////////////////////////////////////////////////////////////////
		// Save content data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public function save($data) {
				foreach($this->data['data']['systemrights_list'] as $systemright) {
						$systemright_id = $systemright['id'];
						$systemright_posted_value = 0;
						
						//check if systemright is present in the posted values
						if(isset($data['systemrights'][$systemright_id])) {
								$systemright_posted_value = (int)$data['systemrights'][$systemright_id];
						}
						
						//check posted value
						if($systemright_posted_value !== 0 && $systemright_posted_value !== 1) {
								$systemright_posted_value = 0;
						}
						
						//check if accounts systemright is available in the accounts_systemrights table
						$bExists = cAdminrights::checkIfAdminrightExists($this->data['accounts_id'], $systemright_id);
						
						//create or update systemright
						if(false == $bExists) {
								//create
								cAdminrights::create($this->data['accounts_id'], $systemright_id, $systemright_posted_value);
						} else {
								//update
								cAdminrights::update($this->data['accounts_id'], $systemright_id, $systemright_posted_value);
						}
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the page footer.
		///////////////////////////////////////////////////////////////////
		public function footer() {
				$additional_output = 
						"\n" . '<script src="data/templates/' . $this->template . '/js/adminaccountsrights.js"></script>' .
						"\n";
				$renderer = core()->getInstance('cRenderer');
				$renderer->renderText($additional_output);
		}
}
?>