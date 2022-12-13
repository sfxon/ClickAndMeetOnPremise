<?php

class cAdminCmSettingsMaster extends cModule {
		var $template = 'admin_templates';
		var $navbar_title = 'CmSettings';
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
				if(false === cAccount::adminrightCheck('cAdminCmSettings', 'USE_MODULE', (int)$_SESSION['user_id'])) {
						header('Location: index.php?s=cAdmin&error=304');
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
				$cAdmin->appendBreadcrumb('Admin CmSettings', 'index.php?s=cAdminCmSettings');
				
				switch($this->action) {
						case 'delete':
								$this->initData();
								$this->getContent();
								$this->delete();
								break;
						case 'confirm_delete':
								$this->initData();
								$this->getContent();
								$this->data['url'] = 'index.php?s=cAdminCmSettings&amp;action=delete&amp;id=' . (int)$this->data['data']['id'];
								$cAdmin->appendBreadcrumb('L&ouml;schen best&auml;tigen', '');
								$this->navbar_title = 'L&ouml;schen best&auml;tigen';
								break;
						
						case 'edit':
								$this->initData();
								$this->getContent();
								$this->data['url'] = 'index.php?s=cAdminCmSettings&amp;action=update&amp;id=' . (int)$this->data['data']['id'];
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
								$this->data['url'] = 'index.php?s=cAdminCmSettings&amp;action=create';
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
						header('Location: index.php?s=cAdminCmSettings&error=331');
						die;
				}
				
				//delete button
				if($button_delete !== NULL && $button_delete === 'delete') {
						cCmSettings::deleteById($this->data['data']['id']);
						
						header('Location: index.php?s=cAdminCmSettings&success=332');
						die;
				}
				
				//unknown operation (we didn't get proper input).
				header('Location: index.php?s=cAdmin&error=333');
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Prepare data for the editor.
		///////////////////////////////////////////////////////////////////
		function initData() {
    		        $this->data['data']['id'] = 0;
     		        $this->data['data']['title'] = '';
     		        $this->data['data']['field_title'] = '';
     		        $this->data['data']['field_value'] = '';
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
		// Draw the delete dialog.
		///////////////////////////////////////////////////////////////////
		public function drawConfirmDeleteDialog() {
        $renderer = core()->getInstance('cRenderer');
        $original_template_path = $renderer->getTemplatePath();
				$renderer->setTemplatePath($this->getTemplatePath());
        $renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$renderer->assign('SITE_URLS', cSite::loadSiteUrls());
				$renderer->assign('NAVBARS', cAdminnavbaredit::loadNavbarList());
				$renderer->assign('ERRORS', $this->errors);
				$renderer->render('confirm_delete_dialog.html');
        
        $renderer->setTemplatePath($original_template_path);
		}
		
		
		///////////////////////////////////////////////////////////////////
		// Draw the editor.
		///////////////////////////////////////////////////////////////////
		public function drawEditor() {
        $renderer = core()->getInstance('cRenderer');
        $original_template_path = $renderer->getTemplatePath();
				$renderer->setTemplatePath($this->getTemplatePath());
        $renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$renderer->render('editor.html');
        
        $renderer->setTemplatePath($original_template_path);
		}
    
    ///////////////////////////////////////////////////////////////////
		// Draw the list view.
		///////////////////////////////////////////////////////////////////
		public function getTemplatePath() {
    		return 'modules/cCmSettings/';
    }

		///////////////////////////////////////////////////////////////////
		// Draw the list view.
		///////////////////////////////////////////////////////////////////
		function drawList() {
				$renderer = core()->getInstance('cRenderer');
        $original_template_path = $renderer->getTemplatePath();
        $renderer->setTemplatePath($this->getTemplatePath());
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$renderer->render('list.html');
        
        $renderer->setTemplatePath($original_template_path);
		}
		
		///////////////////////////////////////////////////////////////////
		// update an entry
		///////////////////////////////////////////////////////////////////
		public function update() {
				$id = (int)core()->getGetVar('id');
				
				if(0 == $id) {
						header('Location: index.php?s=admincAdminCmSettings&error=305');
						die;
				}
        
        $data['id'] = $id;

                $data['title'] = core()->getPostVar('title');
     		        $data['field_title'] = core()->getPostVar('field_title');
     		        $data['field_value'] = core()->getPostVar('field_value');
     		
				
				if(NULL === cCmSettings::loadById((int)$data['id'])) {
						header('Location: index.php?s=cAdminCmSettings&error=306');
						die;
				}
				
				$id = $this->save($data);
				
				if(empty($id)) {
						header('Location: index.php?s=cAdminCmSettings&error=307');
						die;
				}
	
				header('Location: index.php?s=cAdminCmSettings&action=edit&id=' . $id . '&success=308');
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Crate a content entry.
		///////////////////////////////////////////////////////////////////
		function create() {
				$data['id'] = 0;
				
                $data['title'] = core()->getPostVar('title');
     		        $data['field_title'] = core()->getPostVar('field_title');
     		        $data['field_value'] = core()->getPostVar('field_value');
     						
				$id = $this->save($data);
				
				if(empty($id)) {
						header('Location: index.php?s=cAdminCmSettings&error=309');
						die;
				}
	
				header('Location: index.php?s=cAdminCmSettings&action=edit&id=' . $id . '&success=310');
				die;
		}

		///////////////////////////////////////////////////////////////////
		// Loads a list of content entries.
		///////////////////////////////////////////////////////////////////
		public function loadList() {
				return cCmSettings::loadList();
		}
	
		/////////////////////////////////////////////////////////////////////////////////
		// Save data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public function save($data) {
				$id = (int)$data['id'];
		
				if(0 === $id) {
						return cCmSettings::createInDB($data);
				}
				
				cCmSettings::updateInDB($id, $data);
				
				return $data['id'];
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Loads a content entry.
		/////////////////////////////////////////////////////////////////////////////////
		public function getContent() {
				$id = (int)core()->getGetVar('id');
				$this->data['data'] = cCmSettings::loadById($id);
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
										'position' => 800,
										'title' => 'Einstellungen'
								),
								array(
										'position' => 50,
										'title' => 'Formular-Einstellungen'
								)
						);
		        $cAdmin->registerAdminMenuEntry($admin_menu_entry_path, 'index.php?s=cAdminCmSettings');
				}
		}
    
}
?>