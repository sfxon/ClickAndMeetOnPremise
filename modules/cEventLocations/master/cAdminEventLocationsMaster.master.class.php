<?php

class cAdminEventLocationsMaster extends cModule {
		var $template = 'admin_templates';
		var $navbar_title = 'Event Locations';
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
				if(false === cAccount::adminrightCheck('cAdminEventLocations', 'USE_MODULE', (int)$_SESSION['user_id'])) {
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
				$cAdmin->appendBreadcrumb('Admin Event Locations', 'index.php?s=cAdminEventLocations');
				
				switch($this->action) {
						case 'delete':
								$this->initData();
								$this->getContent();
								$this->checkIfDeletable();
								$this->delete();
								break;
						case 'confirm_delete':
								$this->initData();
								$this->getContent();
								$this->data['url'] = 'index.php?s=cAdminEventLocations&amp;action=delete&amp;id=' . (int)$this->data['data']['id'];
								$cAdmin->appendBreadcrumb('L&ouml;schen best&auml;tigen', '');
								$this->navbar_title = 'L&ouml;schen best&auml;tigen';
								$this->checkIfDeletable();
								break;
						
						case 'edit':
								$this->initData();
								$this->getContent();
								$this->data['url'] = 'index.php?s=cAdminEventLocations&amp;action=update&amp;id=' . (int)$this->data['data']['id'];
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
								$this->data['url'] = 'index.php?s=cAdminEventLocations&amp;action=create';
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
						header('Location: index.php?s=cAdminEventLocations&error=331');
						die;
				}
				
				//delete button
				if($button_delete !== NULL && $button_delete === 'delete') {
						cEventLocations::deleteById($this->data['data']['id']);
						
						header('Location: index.php?s=cAdminEventLocations&success=332');
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
     		        $this->data['data']['description'] = '';
     		        $this->data['data']['user_id'] = 0;
     		        $this->data['data']['email_address'] = '';
     		        $this->data['data']['booking_info_by_mail'] = 0;
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
    		return 'modules/cEventLocations/';
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
						header('Location: index.php?s=admincAdminEventLocations&error=305');
						die;
				}
        
        $data['id'] = $id;

                $data['title'] = core()->getPostVar('title');
     		        $data['description'] = core()->getPostVar('description');
     		        $data['user_id'] = (int)core()->getPostVar('user_id');
     		        $data['email_address'] = core()->getPostVar('email_address');
     		        $data['booking_info_by_mail'] = (int)core()->getPostVar('booking_info_by_mail');
     		
				
				if(NULL === cEventLocations::loadById((int)$data['id'])) {
						header('Location: index.php?s=cAdminEventLocations&error=306');
						die;
				}
				
				$id = $this->save($data);
				
				if(empty($id)) {
						header('Location: index.php?s=cAdminEventLocations&error=307');
						die;
				}
	
				header('Location: index.php?s=cAdminEventLocations&action=edit&id=' . $id . '&success=308');
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Crate a content entry.
		///////////////////////////////////////////////////////////////////
		function create() {
				$data['id'] = 0;
				
                $data['title'] = core()->getPostVar('title');
     		        $data['description'] = core()->getPostVar('description');
     		        $data['user_id'] = (int)core()->getPostVar('user_id');
     		        $data['email_address'] = core()->getPostVar('email_address');
     		        $data['booking_info_by_mail'] = (int)core()->getPostVar('booking_info_by_mail');
     						
				$id = $this->save($data);
				
				if(empty($id)) {
						header('Location: index.php?s=cAdminEventLocations&error=309');
						die;
				}
	
				header('Location: index.php?s=cAdminEventLocations&action=edit&id=' . $id . '&success=310');
				die;
		}

		///////////////////////////////////////////////////////////////////
		// Loads a list of content entries.
		///////////////////////////////////////////////////////////////////
		public function loadList() {
				return cEventLocations::loadList();
		}
	
		/////////////////////////////////////////////////////////////////////////////////
		// Save data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public function save($data) {
				$id = (int)$data['id'];
		
				if(0 === $id) {
						return cEventLocations::createInDB($data);
				}
				
				cEventLocations::updateInDB($id, $data);
				
				return $data['id'];
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Loads a content entry.
		/////////////////////////////////////////////////////////////////////////////////
		public function getContent() {
				$id = (int)core()->getGetVar('id');
				$this->data['data'] = cEventLocations::loadById($id);
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
									'position' => 10,
									'title' => 'Betriebsstätte/Standort'
							)
					);
			$cAdmin->registerAdminMenuEntry($admin_menu_entry_path, 'index.php?s=cAdminEventLocations');
			}
	}

	private function checkIfDeletable() {
		$eventLocationId = (int)$this->data['data']['id'];

		// Check if there are appointments assigned
		$iAppointment = new cAppointment();
		$status = $iAppointment->hasEventLocationsAppointments($eventLocationId);

		if($status == true) {
			header('Location: index.php?s=cAdminEventLocations&error=1001');
			die;
		}

		//Check if there are userUnit (Teams) assigned
		$iUserUnit = new cUserUnit();
		$status = $iUserUnit->hasEventLocation($eventLocationId);

		if($status == true) {
			header('Location: index.php?s=cAdminEventLocations&error=1002');
			die;
		}
	}
    
}
?>