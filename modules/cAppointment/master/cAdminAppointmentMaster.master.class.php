<?php

class cAdminAppointmentMaster extends cModule {
		var $template = 'admin_templates';
		var $navbar_title = 'Appointment';
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
				if(false === cAccount::adminrightCheck('cAdminAppointment', 'USE_MODULE', (int)$_SESSION['user_id'])) {
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
				$cAdmin->appendBreadcrumb('Admin Appointment', 'index.php?s=cAdminAppointment');
				
				switch($this->action) {
						case 'delete':
								$this->initData();
								$this->getContent();
								$this->delete();
								break;
						case 'confirm_delete':
								$this->initData();
								$this->getContent();
								$this->data['url'] = 'index.php?s=cAdminAppointment&amp;action=delete&amp;id=' . (int)$this->data['data']['id'];
								$cAdmin->appendBreadcrumb('L&ouml;schen best&auml;tigen', '');
								$this->navbar_title = 'L&ouml;schen best&auml;tigen';
								break;
						
						case 'edit':
								$this->initData();
								$this->getContent();
								$this->data['url'] = 'index.php?s=cAdminAppointment&amp;action=update&amp;id=' . (int)$this->data['data']['id'];
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
								$this->data['url'] = 'index.php?s=cAdminAppointment&amp;action=create';
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
						header('Location: index.php?s=cAdminAppointment&error=331');
						die;
				}
				
				//delete button
				if($button_delete !== NULL && $button_delete === 'delete') {
						cAppointment::deleteById($this->data['data']['id']);
						
						header('Location: index.php?s=cAdminAppointment&success=332');
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
     		        $this->data['data']['datetime_of_event'] = '';
     		        $this->data['data']['event_location_id'] = 0;
     		        $this->data['data']['user_unit_id'] = 0;
     		        $this->data['data']['status'] = 0;
     		        $this->data['data']['created_by'] = 0;
     		        $this->data['data']['datetime_checkin'] = '';
     		        $this->data['data']['datetime_checkout'] = '';
     		        $this->data['data']['visitor_user_id'] = 0;
     		        $this->data['data']['checkin_by'] = 0;
     		        $this->data['data']['checkout_by'] = 0;
     		        $this->data['data']['comment_checkin'] = '';
     		        $this->data['data']['comment_checkout'] = '';
     		        $this->data['data']['comment_visitor_booking'] = '';
     		        $this->data['data']['reminder_user_mail'] = '';
     		        $this->data['data']['reminder_active'] = 0;
     		        $this->data['data']['reminder_user_mail_sent'] = 0;
     		        $this->data['data']['reminder_user_mail_sent_datetime'] = '';
     		        $this->data['data']['duration_in_minutes'] = 0;
     		        $this->data['data']['firstname'] = '';
     		        $this->data['data']['lastname'] = '';
     		        $this->data['data']['email_address'] = '';
     		        $this->data['data']['customers_number'] = '';
     		        $this->data['data']['phone'] = '';
     		        $this->data['data']['street_number'] = '';
     		        $this->data['data']['plz'] = '';
     		        $this->data['data']['city'] = '';
     		        $this->data['data']['street'] = '';
     		        $this->data['data']['last_save_datetime'] = '';
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
    		return 'modules/cAppointment/';
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
						header('Location: index.php?s=admincAdminAppointment&error=305');
						die;
				}
        
        $data['id'] = $id;

                $data['title'] = core()->getPostVar('title');
     		        $data['datetime_of_event'] = core()->getPostVar('datetime_of_event');
     		        $data['event_location_id'] = (int)core()->getPostVar('event_location_id');
     		        $data['user_unit_id'] = (int)core()->getPostVar('user_unit_id');
     		        $data['status'] = (int)core()->getPostVar('status');
     		        $data['created_by'] = (int)core()->getPostVar('created_by');
     		        $data['datetime_checkin'] = core()->getPostVar('datetime_checkin');
     		        $data['datetime_checkout'] = core()->getPostVar('datetime_checkout');
     		        $data['visitor_user_id'] = (int)core()->getPostVar('visitor_user_id');
     		        $data['checkin_by'] = (int)core()->getPostVar('checkin_by');
     		        $data['checkout_by'] = (int)core()->getPostVar('checkout_by');
     		        $data['comment_checkin'] = core()->getPostVar('comment_checkin');
     		        $data['comment_checkout'] = core()->getPostVar('comment_checkout');
     		        $data['comment_visitor_booking'] = core()->getPostVar('comment_visitor_booking');
     		        $data['reminder_user_mail'] = core()->getPostVar('reminder_user_mail');
     		        $data['reminder_active'] = (int)core()->getPostVar('reminder_active');
     		        $data['reminder_user_mail_sent'] = (int)core()->getPostVar('reminder_user_mail_sent');
     		        $data['reminder_user_mail_sent_datetime'] = core()->getPostVar('reminder_user_mail_sent_datetime');
     		        $data['duration_in_minutes'] = (int)core()->getPostVar('duration_in_minutes');
     		        $data['firstname'] = core()->getPostVar('firstname');
     		        $data['lastname'] = core()->getPostVar('lastname');
     		        $data['email_address'] = core()->getPostVar('email_address');
     		        $data['customers_number'] = core()->getPostVar('customers_number');
     		        $data['phone'] = core()->getPostVar('phone');
     		        $data['street_number'] = core()->getPostVar('street_number');
     		        $data['plz'] = core()->getPostVar('plz');
     		        $data['city'] = core()->getPostVar('city');
     		        $data['street'] = core()->getPostVar('street');
     		        $data['last_save_datetime'] = core()->getPostVar('last_save_datetime');
     		
				
				if(NULL === cAppointment::loadById((int)$data['id'])) {
						header('Location: index.php?s=cAdminAppointment&error=306');
						die;
				}
				
				$id = $this->save($data);
				
				if(empty($id)) {
						header('Location: index.php?s=cAdminAppointment&error=307');
						die;
				}
	
				header('Location: index.php?s=cAdminAppointment&action=edit&id=' . $id . '&success=308');
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Crate a content entry.
		///////////////////////////////////////////////////////////////////
		function create() {
				$data['id'] = 0;
				
                $data['title'] = core()->getPostVar('title');
     		        $data['datetime_of_event'] = core()->getPostVar('datetime_of_event');
     		        $data['event_location_id'] = (int)core()->getPostVar('event_location_id');
     		        $data['user_unit_id'] = (int)core()->getPostVar('user_unit_id');
     		        $data['status'] = (int)core()->getPostVar('status');
     		        $data['created_by'] = (int)core()->getPostVar('created_by');
     		        $data['datetime_checkin'] = core()->getPostVar('datetime_checkin');
     		        $data['datetime_checkout'] = core()->getPostVar('datetime_checkout');
     		        $data['visitor_user_id'] = (int)core()->getPostVar('visitor_user_id');
     		        $data['checkin_by'] = (int)core()->getPostVar('checkin_by');
     		        $data['checkout_by'] = (int)core()->getPostVar('checkout_by');
     		        $data['comment_checkin'] = core()->getPostVar('comment_checkin');
     		        $data['comment_checkout'] = core()->getPostVar('comment_checkout');
     		        $data['comment_visitor_booking'] = core()->getPostVar('comment_visitor_booking');
     		        $data['reminder_user_mail'] = core()->getPostVar('reminder_user_mail');
     		        $data['reminder_active'] = (int)core()->getPostVar('reminder_active');
     		        $data['reminder_user_mail_sent'] = (int)core()->getPostVar('reminder_user_mail_sent');
     		        $data['reminder_user_mail_sent_datetime'] = core()->getPostVar('reminder_user_mail_sent_datetime');
     		        $data['duration_in_minutes'] = (int)core()->getPostVar('duration_in_minutes');
     		        $data['firstname'] = core()->getPostVar('firstname');
     		        $data['lastname'] = core()->getPostVar('lastname');
     		        $data['email_address'] = core()->getPostVar('email_address');
     		        $data['customers_number'] = core()->getPostVar('customers_number');
     		        $data['phone'] = core()->getPostVar('phone');
     		        $data['street_number'] = core()->getPostVar('street_number');
     		        $data['plz'] = core()->getPostVar('plz');
     		        $data['city'] = core()->getPostVar('city');
     		        $data['street'] = core()->getPostVar('street');
     		        $data['last_save_datetime'] = core()->getPostVar('last_save_datetime');
     						
				$id = $this->save($data);
				
				if(empty($id)) {
						header('Location: index.php?s=cAdminAppointment&error=309');
						die;
				}
	
				header('Location: index.php?s=cAdminAppointment&action=edit&id=' . $id . '&success=310');
				die;
		}

		///////////////////////////////////////////////////////////////////
		// Loads a list of content entries.
		///////////////////////////////////////////////////////////////////
		public function loadList() {
				return cAppointment::loadList();
		}
	
		/////////////////////////////////////////////////////////////////////////////////
		// Save data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public function save($data) {
				$id = (int)$data['id'];
		
				if(0 === $id) {
						return cAppointment::createInDB($data);
				}
				
				cAppointment::updateInDB($id, $data);
				
				return $data['id'];
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Loads a content entry.
		/////////////////////////////////////////////////////////////////////////////////
		public function getContent() {
				$id = (int)core()->getGetVar('id');
				$this->data['data'] = cAppointment::loadById($id);
		}
    
    //////////////////////////////////////////////////////////////////////////////////
		// Hook us into the system in the additional hooks..
		//////////////////////////////////////////////////////////////////////////////////
    //public static function setAdditionalHooks() {
				//core()->setHook('cCore|init', 'addMenuBarEntries');
   //}
		
		//////////////////////////////////////////////////////////////////////////////////
		// Callback function, adds a menu item.
		//////////////////////////////////////////////////////////////////////////////////
		public static function addMenuBarEntries() {
				$cAdmin = core()->getInstance('cAdmin');
				
				if(false !== $cAdmin) {
						$admin_menu_entry_path = array(
								array(
										'position' => 800,
										'title' => 'Module'
								),
								array(
										'position' => 3,
										'title' => 'Appointment'
								)
						);
		        $cAdmin->registerAdminMenuEntry($admin_menu_entry_path, 'index.php?s=cAdminAppointment');
				}
		}
    
}
?>