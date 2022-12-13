<?php

class cAdminaccounts extends cModule {
		var $template = 'admin';
		var $navbar_title = TEXT_MODULE_TITLE_ADMINACCOUNTS;
		var $navbar_id = 0;
		var $errors = array();
		var $info_messages = array();
		var $success_messages = array();
		
		//////////////////////////////////////////////////////////////////////////////////
		// Hook us into the system
		//////////////////////////////////////////////////////////////////////////////////
		public static function setExecutionalHooks() {
				//If the user is not logged in..
				if(!isset($_SESSION['user_id'])) {
						header('Location: index.php/login/');
						die;
				}
				
				//check the rights..
				if(false === cAccount::adminrightCheck('cAdminaccounts', 'USE_MODULE', (int)$_SESSION['user_id'])) {
						header('Location: index.php?s=cAdmin&error=41');
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
				$cAdmin->appendBreadcrumb(TEXT_MODULE_TITLE_ADMINACCOUNTS, 'index.php?s=cAdminaccounts');
				
				switch($this->action) {
						case 'delete':
								$this->initData();
								$this->getContent();
								$this->delete();
								break;
						case 'confirm_delete':
								$this->initData();
								$this->getContent();
								$this->data['url'] = 'index.php?s=cAdminaccounts&amp;action=delete&amp;id=' . (int)$this->data['data']['id'];
								$cAdmin->appendBreadcrumb(TEXT_ADMINACCOUNTS_CONFIRM_DELETE, '');
								$this->navbar_title = TEXT_ADMINACCOUNTS_CONFIRM_DELETE;
								break;
						case 'edit':
								$this->initData();
								$this->getContent();
								$this->data['url'] = 'index.php?s=cAdminaccounts&amp;action=update&amp;id=' . (int)$this->data['data']['id'];
								$cAdmin->appendBreadcrumb(TEXT_ADMINACCOUNTS_EDIT, '');
								$this->navbar_title = TEXT_ADMINACCOUNTS_EDIT;
								break;
						case 'update':
								$this->initData();
								$this->getContent();
								$this->data['url'] = 'index.php?s=cAdminaccounts&amp;action=update&amp;id=' . (int)$this->data['data']['id'];
								$this->update();
								$cAdmin->appendBreadcrumb(TEXT_ADMINACCOUNTS_EDIT, '');
								$this->navbar_title = TEXT_ADMINACCOUNTS_EDIT;
								break;
						case 'create':
								$this->create();
								$this->data['url'] = 'index.php?s=cAdminaccounts&amp;action=create';
								$cAdmin->appendBreadcrumb(TEXT_ADMINACCOUNTS_NEW, '');
								$this->navbar_title = TEXT_ADMINACCOUNTS_NEW;
								break;
						case 'new':
								$this->initData();
								$this->data['url'] = 'index.php?s=cAdminaccounts&amp;action=create';
								$cAdmin->appendBreadcrumb(TEXT_ADMINACCOUNTS_NEW, '');
								$this->navbar_title = TEXT_ADMINACCOUNTS_NEW;
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
				$this->data['data']['account_type'] = 0;
				$this->data['data']['email'] = '';
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
						case 'create':
						case 'new':
								$this->drawEditor();
								break;
						case 'update':
						case 'edit':
								$this->drawEditor();
								break;
						default:
								$this->drawList();
								break;
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// "Delete" an entry..
		// We do not really delete any entry. We just flag it,
		// so it does not appear anymore.
		///////////////////////////////////////////////////////////////////
		private function delete() {
				//check if user got rights to delete an entry.
				//check the rights..
				if(false === cAccount::adminrightCheck('cAdminaccounts', 'DELETE_ACCOUNT', (int)$_SESSION['user_id'])) {
						header('Location: index.php?s=cAdmin&error=60');
						die;
				}
				
				//check if user wants to delete or abort the action
				$button_do_not_delete = core()->getPostVar('button_do_not_delete');
				$button_delete = core()->getPostVar('button_delete');
				
				//abort button..
				if($button_do_not_delete !== NULL && $button_do_not_delete === 'not_delete') {
						header('Location: index.php?s=cAdminaccounts&info_message=1');
						die;
				}
				
				//delete button
				if($button_delete !== NULL && $button_delete === 'delete') {
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery('UPDATE ' . $db->table('accounts') . ' SET account_type = 0 WHERE id = :id LIMIT 1;');
						$db->bind(':id', (int)$this->data['data']['id']);
						$result = $db->execute();
						
						header('Location: index.php?s=cAdminaccounts&success=24');
						die;
				}
				
				//unknown operation (we didn't get proper input).
				header('Location: index.php?s=cAdmin&error=61');
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the editor.
		///////////////////////////////////////////////////////////////////
		function drawConfirmDeleteDialog() {
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$renderer->assign('SITE_URLS', cSite::loadSiteUrls());
				$renderer->assign('NAVBARS', cAdminnavbaredit::loadNavbarList());
				$renderer->assign('ERRORS', $this->errors);
				$renderer->render('site/adminaccounts/confirm_delete_dialog.html');
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
				$renderer->assign('ERRORS', $this->errors);
				$renderer->render('site/adminaccounts/editor.html');
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the list view.
		///////////////////////////////////////////////////////////////////
		function drawList() {
				//Collect messages..
				$info_message = core()->getGetVar('info_message');
				$error = core()->getGetVar('error');
				$success = core()->getGetVar('success');
				
				if(NULL !== $info_message) {
						$this->info_messages[] = $info_message;
				}
				
				if(NULL !== $error) {
						$this->errors[] = $error;
				}
				
				if(NULL !== $success) {
						$this->success_messages[] = $success;
				}
				
				//Render page..
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$renderer->assign('ADMINRIGHT_DELETE_ACCOUNT', cAccount::adminrightCheck('cAdminaccounts', 'DELETE_ACCOUNT', (int)$_SESSION['user_id']));				
				$renderer->assign('INFO_MESSAGES', $this->info_messages);
				$renderer->assign('ERRORS', $this->errors);
				$renderer->assign('SUCCESS_MESSAGES', $this->success_messages);
				$renderer->render('site/adminaccounts/list.html');
		}
		
		///////////////////////////////////////////////////////////////////
		// update an entry
		///////////////////////////////////////////////////////////////////
		public function update() {
				$id = (int)core()->getGetVar('id');
				
				if(0 == $id) {
						header('Location: index.php?s=cAdminaccounts&error=42');
						die;
				}
				
				//get input values
				$data['id'] = (int)$id;
				$data['account_type'] = (int)core()->getPostVar('account_type');
				$data['password'] = core()->getPostVar('password');
				$data['email'] = core()->getPostVar('email');
				
				$this->data['data'] = $data;
				
				//Check input values
				//1. check email and email existence..
				if(false === cAdminaccounts::checkAccountExistenceById((int)$data['id'])) {
						header('Location: index.php?s=cAdminaccounts&error=43');
						die;
				}
				
				if(false === cAdminaccounts::validateEmailAddress($data['email'])) {
						$this->errors['email_format'] = true;
						return false;
				}
				
				$email_account_id = cAdminaccounts::getAccountIdByEmail($data['email']);
				
				if($email_account_id !== false && (int)$email_account_id != (int)$data['id']) {
						$this->errors['email_exists'] = true;
						return false;
				}
				
				//Save general data.
				$id = $this->save($data);
				
				if(empty($id)) {
						header('Location: index.php?s=cAdminaccounts&error=44');
						die;
				}
				
				//2nd: check Password and update.. (We only save it, when a new password was submitted.
				if(strlen($data['password']) > 0) {
						/*$tmp_errors = cAdminaccounts::validatePassword($data['password']);
				
						if(count($tmp_errors) != 0) {
								$this->errors = array_merge($this->errors, $tmp_errors);
								return false;
						}*/
						
						cAdminaccounts::updatePassword($id, $data['password']);
				}
				
				//Done. Redirect to success page.	
				header('Location: index.php?s=cAdminaccounts&action=edit&id=' . $id . '&success=15');
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Update the password.
		///////////////////////////////////////////////////////////////////
		public static function updatePassword($id, $password) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('UPDATE ' . $db->table('accounts') . ' SET password = :password WHERE id = :id LIMIT 1;');
				$db->bind(':password', password_hash($password, PASSWORD_BCRYPT));
				$db->bind(':id', (int)$id);
				$result = $db->execute();
		}
		
		///////////////////////////////////////////////////////////////////
		// Check if the entry exists (by id)
		///////////////////////////////////////////////////////////////////
		public static function checkAccountExistenceById($id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT id FROM ' . $db->table('accounts') . ' WHERE id = :id LIMIT 1;');
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
		// validate the email address.
		// pregmatch as of http://stackoverflow.com/questions/201323/using-a-regular-expression-to-validate-an-email-address
		//
		// Context:
		//	A valid e-mail address is a string that matches the ABNF production […].
		//	Note: This requirement is a willful violation of RFC 5322, which defines a syntax for e-mail addresses that is simultaneously too strict (before the "@" character), too vague (after the "@" character), and too lax (allowing comments, whitespace characters, and quoted strings in manners unfamiliar to most users) to be of practical use here.
		//	The following JavaScript- and Perl-compatible regular expression is an implementation of the above definition.
		//
		// TODO:
		//	This is a copy of this function in cMail.
		//	All calls of this function should be changed to be done by the other module (cMail).
		//	Then remove the function in this module.
		///////////////////////////////////////////////////////////////////
		public static function validateEmailAddress($email_address) {
				$output_array = array();
				preg_match("/^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/", $email_address, $output_array);
				
				if(count($output_array) > 0) {
						return true;
				}
				
				return false;
		}
		
		///////////////////////////////////////////////////////////////////
		// Get account ID by email.
		///////////////////////////////////////////////////////////////////
		public static function getAccountIdByEmail($email_address) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT id FROM ' . $db->table('accounts') . ' WHERE LOWER(email) = LOWER(:email_address) LIMIT 1;');
				$db->bind(':email_address', $email_address);
				$result = $db->execute();
				$tmp = $result->fetchArrayAssoc();
				
				if(isset($tmp['id'])) {
						return $tmp['id'];
				}
				
				return false;
		}
		
		///////////////////////////////////////////////////////////////////
		// Validate a password
		///////////////////////////////////////////////////////////////////
		public static function validatePassword($password) {
				$errors = array();
				
				$uppercase = preg_match('@[A-Z]@', $password);
				$lowercase = preg_match('@[a-z]@', $password);
				$number    = preg_match('@[0-9]@', $password);
				
				if(!$uppercase) {
						$errors['password_no_uppercase_char'] = true;
				}
				
				if(!$lowercase) {
						$errors['password_no_lowercase_char'] = true;
				}
				
				if(!$number) {
						$errors['password_no_number'] = true;
				}
				
				if(strlen($password) < 8) {
						$errors['password_length'] = true;
				}
				
				return $errors;
		}
		
		///////////////////////////////////////////////////////////////////
		// Crate a content entry.
		///////////////////////////////////////////////////////////////////
		function create() {
				$data['id'] = 0;
				$data['account_type'] = (int)core()->getPostVar('account_type');
				$data['password'] = core()->getPostVar('password');
				$data['email'] = core()->getPostVar('email');
				
				$this->data['data'] = $data;
				
				//Check input values
				//1. check email and email existence..
				if(false === cAdminaccounts::validateEmailAddress($data['email'])) {
						$this->errors['email_format'] = true;
						return false;
				}
				
				$email_account_id = cAdminaccounts::getAccountIdByEmail($data['email']);

				if($email_account_id !== false) {
						$this->errors['email_exists'] = true;
						return false;
				}
				
				//2nd: check Password
				if(!is_string($data['password']) || strlen($data['password']) == 0) {
						$data['password'] = 'ABCabc123!' . uniqid();
				}
				
				$tmp_errors = cAdminaccounts::validatePassword($data['password']);
				
				if(count($tmp_errors) != 0) {
						$this->errors = array_merge($this->errors, $tmp_errors);
						return false;
				}
				
				$id = $this->save($data);
				
				if(empty($id)) {
						header('Location: index.php?s=cAdminaccounts&error=45');
						die;
				}
	
				header('Location: index.php?s=cAdminaccounts&action=edit&id=' . $id . '&success=16');
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Loads a list of data..
		///////////////////////////////////////////////////////////////////
		public function loadList() {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT ' .
								'* ' .
						'FROM ' . $db->table('accounts') . ' ' .
						'ORDER BY lastname, firstname;'
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
						'INSERT INTO ' . $db->table('accounts') . ' ' .
								'(account_type, email, created_on' .
								') ' .
						'VALUES ' .
								'(:account_type, :email, NOW()' .
								')'
				);
				$db->bind(':account_type', (int)$data['account_type']);
				$db->bind(':email', $data['email']);
				$db->execute();
				$account_id = $db->insertId();
				
				$this->updateAdminRightsByAccountType($account_id, (int)$data['account_type']);
				
				return $account_id;
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Update navbar data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public function updateInDB($id, $data) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('accounts') . ' SET ' .
								'account_type = :account_type, ' .
								'email = :email ' .
						'WHERE ' .
								'id = :id'
				);
				$db->bind(':account_type', (int)$data['account_type']);
				$db->bind(':email', $data['email']);
				$db->bind(':id', (int)$id);
				$db->execute();

				$this->updateAdminRightsByAccountType($id, (int)$data['account_type']);
		}


		private function updateAdminRightsByAccountType($account_id, $accountTypeId) {
			
			if($accountTypeId == 0) {
				$this->upsertAdminRight((int)$account_id, 4, 0);		// cAdmin
				$this->upsertAdminRight((int)$account_id, 11, 0);		// cAdminAccounts -> USE_MODULE
				$this->upsertAdminRight((int)$account_id, 15, 0);		// cAdminAccounts -> DELETE_ACCOUNT
				$this->upsertAdminRight((int)$account_id, 119, 0);		// cAdminCcConfig -> USE_MODULE
				$this->upsertAdminRight((int)$account_id, 120, 0);		// cAdminEventLocations
				$this->upsertAdminRight((int)$account_id, 121, 0);		// cAdminAppointment
				$this->upsertAdminRight((int)$account_id, 122, 0);		// cAdminCmCalendar
				$this->upsertAdminRight((int)$account_id, 123, 0);		// cAdminCmappointmentstatus
				$this->upsertAdminRight((int)$account_id, 124, 0);		// cAdminCmSettings
				$this->upsertAdminRight((int)$account_id, 125, 0);		// cAdminUserUnit
				$this->upsertAdminRight((int)$account_id, 126, 0);		// cAdminMailTextSettings
				$this->upsertAdminRight((int)$account_id, 127, 0);		// cAdminMvCalendarColors
				$this->upsertAdminRight((int)$account_id, 128, 0);		// cAdminMvBookingFormTexts
			} else if($accountTypeId == 1) {
				$this->upsertAdminRight((int)$account_id, 4, 1);		// cAdmin
				$this->upsertAdminRight((int)$account_id, 11, 1);		// cAdminAccounts -> USE_MODULE
				$this->upsertAdminRight((int)$account_id, 15, 1);		// cAdminAccounts -> DELETE_ACCOUNT
				$this->upsertAdminRight((int)$account_id, 119, 1);		// cAdminCcConfig -> USE_MODULE
				$this->upsertAdminRight((int)$account_id, 120, 1);		// cAdminEventLocations
				$this->upsertAdminRight((int)$account_id, 121, 1);		// cAdminAppointment
				$this->upsertAdminRight((int)$account_id, 122, 1);		// cAdminCmCalendar
				$this->upsertAdminRight((int)$account_id, 123, 1);		// cAdminCmappointmentstatus
				$this->upsertAdminRight((int)$account_id, 124, 1);		// cAdminCmSettings
				$this->upsertAdminRight((int)$account_id, 125, 1);		// cAdminUserUnit
				$this->upsertAdminRight((int)$account_id, 126, 1);		// cAdminMailTextSettings
				$this->upsertAdminRight((int)$account_id, 127, 1);		// cAdminMvCalendarColors
				$this->upsertAdminRight((int)$account_id, 128, 1);		// cAdminMvBookingFormTexts
			} else if($accountTypeId == 2) {
				$this->upsertAdminRight((int)$account_id, 4, 1);		// cAdmin
				$this->upsertAdminRight((int)$account_id, 11, 0);		// cAdminAccounts -> USE_MODULE
				$this->upsertAdminRight((int)$account_id, 15, 0);		// cAdminAccounts -> DELETE_ACCOUNT
				$this->upsertAdminRight((int)$account_id, 119, 0);		// cAdminCcConfig -> USE_MODULE
				$this->upsertAdminRight((int)$account_id, 120, 0);		// cAdminEventLocations
				$this->upsertAdminRight((int)$account_id, 121, 0);		// cAdminAppointment
				$this->upsertAdminRight((int)$account_id, 122, 1);		// cAdminCmCalendar
				$this->upsertAdminRight((int)$account_id, 123, 0);		// cAdminCmappointmentstatus
				$this->upsertAdminRight((int)$account_id, 124, 0);		// cAdminCmSettings
				$this->upsertAdminRight((int)$account_id, 125, 0);		// cAdminUserUnit
				$this->upsertAdminRight((int)$account_id, 126, 0);		// cAdminMailTextSettings
				$this->upsertAdminRight((int)$account_id, 127, 0);		// cAdminMvCalendarColors
				$this->upsertAdminRight((int)$account_id, 128, 0);		// cAdminMvBookingFormTexts
			}
				
		}

		private function upsertAdminRight($account_id, $systemrights_id, $status) {
			// Insert
			if(false === cAdminrights::checkIfAdminrightExists($account_id, $systemrights_id)) {
				cAdminrights::create($account_id, $systemrights_id, $status);
				return;
			}

			// Update
			cAdminrights::update($account_id, $systemrights_id, $status);
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Loads a content entry.
		/////////////////////////////////////////////////////////////////////////////////
		public function getContent() {
				$id = (int)core()->getGetVar('id');
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT ' .
								'* ' .
						'FROM ' . $db->table('accounts') . ' ' .
						'WHERE ' .
								'id = :id'
				);
				$db->bind(':id', (int)$id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				$this->data['data'] = $tmp;
		}
		
		///////////////////////////////////////////////////////////////////////////////
		// Get an account (that is not deactivated..
		///////////////////////////////////////////////////////////////////////////////
		public static function getValidUser($user_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT ' .
								'* ' .
						'FROM ' . $db->table('accounts') . ' ' .
						'WHERE ' .
								'id = :id AND ' .
								'account_type != 0'
				);
				$db->bind(':id', (int)$user_id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				return $tmp;
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
										'position' => 200,
										'title' => 'Stammdaten'
								),
								array(
										'position' => 0,
										'title' => 'Accounts &amp; Kunden'
								)
						);
		        $cAdmin->registerAdminMenuEntry($admin_menu_entry_path, 'index.php?s=cAdminaccounts');
				}
		}
}
?>