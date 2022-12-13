<?php

class cAccount extends cModule {		
		/////////////////////////////////////////////////////////////////////////
		// Define where to set this module in the boot (hook) chain.
		/////////////////////////////////////////////////////////////////////////
		public static function setBootHooks() {
				core()->setBootHook('cSession');
		}
		
		////////////////////////////////////////////////////////////////////////
		// This is executed when the system boots and the chain reached this module.
		////////////////////////////////////////////////////////////////////////
		public static function boot() {
				$logged_in = false;
				
				if(isset($_SESSION['user_id'])) {
						if(false === cAccount::checkUser((int)$_SESSION['user_id'])) {
								unset($_SESSION['user_id']);		//Logout finally!
						}
				}
				
				//now - if we are still logged in..
				if(isset($_SESSION['user_id'])) {
						core()->set('user_id', (int)$_SESSION['user_id']);
				} else {
						core()->set('user_id', (int)0);
				}
		}
		
		//////////////////////////////////////////////////////////////////////////
		// Returns an array of all modules.
		//
		// Return Value Array Shema:
		//		array(
		//				array(
		//						'module' => 'path/and/module/name',
		//						'version' => '1.6'		//Minimum version of dependent module that is needed to run this module.
		//				), 
		//				array(..)
		//		);
		//
		//		The systems core logic checks all dependencies in the auto loader.
		//			
		//////////////////////////////////////////////////////////////////////////
		public static function getDependenciesAsArray() {
				return array(
						array(
								'module' => '/core/session/cSession'
						)
				);
		}
		
		//////////////////////////////////////////////////////////////////////////
		// Returns the version of a module.
		// Returns 0 (zero) if you define no version for your module.
		//////////////////////////////////////////////////////////////////////////
		public static function getVersion() {
				return 0.1;
		}
		
		/////////////////////////////////////////////////////////////////////////
		// check a user..
		/////////////////////////////////////////////////////////////////////////
		public static function checkUser($user_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT id FROM ' . $db->table('accounts') . ' WHERE id = :id LIMIT 1');
				$db->bind(':id', (int)$user_id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(isset($tmp['id'])) {
						return true;
				}
				
				return false;
		}
		
		///////////////////////////////////////////////////////////////////////
		// Gets a navbar item - either it is active or inactive..
		///////////////////////////////////////////////////////////////////////
		public static function getNavbarData() {
				$site_id = core()->get('site_id');
				
				if(isset($_SESSION['user_id'])) {
						return array(
								'url' => '//' . cSite::loadSiteUrl($site_id) . 'myaccount/index.html',
								'title' => TEXT_NAVBAR_ACCOUNT
						);
				}
				
				return array(
						'url' => '//' . cSite::loadSiteUrl($site_id) . 'login/index.html',
						'title' => TEXT_NAVBAR_LOGIN
				);
		}
		
		/////////////////////////////////////////////////////////////////////
		// Check if the user got an specific administration right.
		/////////////////////////////////////////////////////////////////////
		public static function adminrightCheck($module, $rightskey, $user_id) {
				$systemrights_id = cAccount::systemrightLoadByModuleAndRightskey($module, $rightskey);
				
				if(empty($systemrights_id['id'])) {
						return false;
				}
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('select * from ' . $db->table('adminrights') . ' where systemrights_id = :systemrights_id and accounts_id = :accounts_id');
				$db->bind(':systemrights_id', (int)$systemrights_id['id']);
				$db->bind(':accounts_id', (int)$user_id);
				$result = $db->execute();
				
				$data = $result->fetchArrayAssoc();
				
				if(empty($data['status'])) {
						return false;
				}
				
				return true;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Load data for one specific system right
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function systemrightLoadByModuleAndRightskey($module, $rightskey) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('select * from ' . $db->table('systemrights') . ' where module = :module and rightskey = :rightskey');
				$db->bind(':module', $module);
				$db->bind(':rightskey', $rightskey);
				$result = $db->execute();
				
				$data = $result->fetchArrayAssoc();
				
				return $data;
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////////////
		// Alias function for : Load data for one user.
		//////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadById($user_id) {
				return cAccount::loadUserData($user_id);
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////////////
		// Load data for one user.
		//////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadUserData($user_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('accounts') . ' WHERE id = :id LIMIT 1;');
				$db->bind(':id', (int)$user_id);
				$result = $db->execute();
				
				$data = $result->fetchArrayAssoc();
				
				return $data;
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////////////
		// Delete a user by his id.
		//////////////////////////////////////////////////////////////////////////////////////////////////
		public static function deleteById($user_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('DELETE FROM ' . $db->table('accounts') . ' WHERE id = :id;');
				$db->bind(':id', (int)$user_id);
				$result = $db->execute();
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////////////
		// Update Telefon Station By Account-Id.
		//////////////////////////////////////////////////////////////////////////////////////////////////
		public static function updateTelefonStationByAccountId($account_id, $telefon_station) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('UPDATE ' . $db->table('accounts') . ' SET telefon_station = :telefon_station WHERE id = :id;');
				$db->bind(':telefon_station', $telefon_station);
				$db->bind(':id', (int)$account_id);
				$db->execute();
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////////////
		// Update Telefon Station By Account-Id.
		//////////////////////////////////////////////////////////////////////////////////////////////////
		public static function updateSingleLoginKeyAndSingleLoginKeyValidUntilByAccountId($account_id, $single_login_key, $single_login_key_valid_until) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('accounts') . ' SET ' . 
								'single_login_key = :single_login_key, ' .
								'single_login_key_valid_until = :single_login_key_valid_until ' .
						'WHERE id = :id;');
				$db->bind(':single_login_key', $single_login_key);
				$db->bind(':single_login_key_valid_until', $single_login_key_valid_until);
				$db->bind(':id', (int)$account_id);
				$db->execute();
		}
		//////////////////////////////////////////////////////////////////////////////////////////////////
		// Update Telefon Station By Account-Id.
		//////////////////////////////////////////////////////////////////////////////////////////////////
		public static function checkSingleSignOn($account_id, $single_login_key, $single_login_key_valid_until) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT id FROM ' . $db->table('accounts') . ' WHERE ' .
								'single_login_key = :single_login_key AND ' .
								'single_login_key_valid_until >= :single_login_key_valid_until AND ' .
								'id = :account_id ' .
						'LIMIT 1;'
				);
				$db->bind(':single_login_key', $single_login_key);
				$db->bind(':single_login_key_valid_until', $single_login_key_valid_until);
				$db->bind(':account_id', $account_id);
				$result = $db->execute();
				$data = $result->fetchArrayAssoc();
				if(isset($data['id'])) {
						return true;
				}
				return false;
		}
		//////////////////////////////////////////////////////////////////////////////////////////////////
		// Save customers email address.
		//////////////////////////////////////////////////////////////////////////////////////////////////
		public static function saveCustomersEmailAddress($user_id, $email_address) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('UPDATE ' . $db->table('accounts') . ' SET email = :email_address WHERE id = :id LIMIT 1;');
				$db->bind(':email_address', $email_address);
				$db->bind(':id', (int)$user_id);
				$result = $db->execute();
				
				$data = $result->fetchArrayAssoc();
				
				return $data;
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////////////
		// Save customers new password.
		//////////////////////////////////////////////////////////////////////////////////////////////////
		public static function saveCustomersPassword($user_id, $password) {
				$password_hash = password_hash($password, PASSWORD_BCRYPT);
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('UPDATE ' . $db->table('accounts') . ' SET password = :password WHERE id = :id LIMIT 1;');
				$db->bind(':password', $password_hash);
				$db->bind(':id', (int)$user_id);
				$result = $db->execute();
				
				$data = $result->fetchArrayAssoc();
				
				return $data;
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////////////
		// Save customers email language.
		//////////////////////////////////////////////////////////////////////////////////////////////////
		public static function saveCustomersEmailLanguage($user_id, $email_language) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('UPDATE ' . $db->table('accounts') . ' SET email_language = :email_language WHERE id = :id LIMIT 1;');
				$db->bind(':email_language', $email_language);
				$db->bind(':id', (int)$user_id);
				$result = $db->execute();
				
				$data = $result->fetchArrayAssoc();
				
				return $data;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// load contacts by type..
		///////////////////////////////////////////////////////////////////////////////////////////////////	
		public static function loadSuppliers($index = 0, $max_results = 0, $sort_fields = '') {
				$limit = '';
		
				if(0 !== (int)$max_results) {
						$limit = ' LIMIT ' . (int)$index . ', ' . (int)$max_results;
				}
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('accounts') . ' WHERE is_supplier = 1 ORDER BY company, firstname, lastname' . $limit);
				$result = $db->execute();
				
				$retval = false;
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$retval[] = $tmp;
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Load accounts by type (manufacturer)
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadManufacturers($index = 0, $max_results = 0, $sort_fields = '') {
				$limit = '';
				
				if(0 !== (int)$max_results) {
						$limit = ' LIMIT ' . (int)$index . ', ' . (int)$max_results;
				}
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('accounts') . ' WHERE is_manufacturer = 1 ORDER BY company, firstname, lastname' . $limit);
				$result = $db->execute();
				
				$retval = false;
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$retval[] = $tmp;
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Load sellers by type.
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadSellers($index = 0, $max_results = 0, $sort_fields = '') {
				$limit = '';
				
				if(0 !== (int)$max_results) {
						$limit = ' LIMIT ' . (int)$index . ', ' . (int)$max_results;
				}
				
				$order_by = ' ORDER BY company, firstname, lastname';
				
				if($sort_fields == 'email ASC') {
						$order_by = ' ORDER BY email ASC';
				}
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('accounts') . ' WHERE seller_status != 0 and account_type != 0' . $order_by . $limit);
				$result = $db->execute();
				
				$retval = false;
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$retval[] = $tmp;
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Load Admins by type.
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadAdmins($index = 0, $max_results = 0, $sort_fields = '') {
				$limit = '';
				
				if(0 !== (int)$max_results) {
						$limit = ' LIMIT ' . (int)$index . ', ' . (int)$max_results;
				}
				
				$order_by = ' ORDER BY company, firstname, lastname';
				
				if($sort_fields == 'email ASC') {
						$order_by = ' ORDER BY email ASC';
				}
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('accounts') . ' WHERE account_type = 1 ' . $order_by . $limit);
				$result = $db->execute();
				
				$retval = false;
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$retval[] = $tmp;
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Load seller status by account id.
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function getSellerStatusByAccountId($account_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT seller_status FROM ' . $db->table('accounts') . ' WHERE id = :id LIMIT 1');
				$db->bind(':id', (int)$account_id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(false === $tmp) {
						return false;
				}
				
				return $tmp['seller_status'];
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Load seller status by account id.
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function getSalutationByGender($gender) {
				switch($gender) {
						case 1:
								return 'Herr';
						case 2:
								return 'Frau';
				}
				
				return '';
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////////////
		// Load manufacturer by company name.
		//////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadManufacturersIdByCompanyName($company) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT id FROM ' . $db->table('accounts') . ' WHERE company = :company AND is_manufacturer = 1 LIMIT 1');
				$db->bind(':company', $company);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(isset($tmp['id'])) {
						return $tmp['id'];
				}
				
				return false;
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////////////
		// Create Manufacturers account by manufacturers name.
		//////////////////////////////////////////////////////////////////////////////////////////////////
		public static function createManufacturersAccountByManufacturersName($company) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'INSERT INTO ' . $db->table('accounts') . ' ' .
								'(account_type, username, email_language, firstname, lastname, company, notice, created_on, is_manufacturer, gender) ' .
						'VALUES ' .
								'(1, :username, 1, :firstname, :lastname, :company, :notice, NOW(), 1, 1);'
				);
				$db->bind(':username', $company);
				$db->bind(':firstname', $company);
				$db->bind(':lastname', $company);
				$db->bind(':company', $company);
				$db->bind(':notice', 'Automatisch erstellter Account.');
				$db->execute();
				
				return $db->insertId();
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////
		// Load account id by email (lowercase comparison)
		///////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadAccountIdByEmail($email) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT id FROM ' . $db->table('accounts') . ' WHERE LOWER(email) = LOWER(:email) LIMIT 1');
				$db->bind(':email', $email);
				
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(isset($tmp['id'])) {
						return $tmp['id'];
				}
				
				return NULL;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////
		// Load account id by original_mail (lowercase comparison)
		///////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadAccountIdByOriginalMail($original_mail) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT id FROM ' . $db->table('accounts') . ' WHERE LOWER(original_mail) = LOWER(:original_mail) LIMIT 1');
				$db->bind(':original_mail', $original_mail);
				
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(isset($tmp['id'])) {
						return $tmp['id'];
				}
				
				return NULL;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////
		// Load account id by email (lowercase comparison)
		///////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadAccountIdByUsername($telefon_benutzername) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT id FROM ' . $db->table('accounts') . ' WHERE LOWER(telefon_benutzername) = LOWER(:telefon_benutzername) LIMIT 1');
				$db->bind(':telefon_benutzername', $telefon_benutzername);
				$result = $db->execute();
				$tmp = $result->fetchArrayAssoc();
				if(isset($tmp['id'])) {
						return $tmp['id'];
				}
				return NULL;
		}
		////////////////////////////////////////////////////////////////////////////////////////////
		// Check an email for valid input..
		// Returns true - or an array with error messages..
		////////////////////////////////////////////////////////////////////////////////////////////
		public static function isEmailValid($email) {
				$errors = array();
				
				//check email
				if(empty($email)) {
						$errors[] = 'Email string is empty.';
				}
				
				//check if email contains @ sign - and it must be greater than 1, because it must be at second position at least!!!
				if(strpos($email, '@') === false || strpos($email, '@') < 2) {
						$errors['email'] = 'Wrong [at] sign or @ sign not found.';
				}
				
				//check if email contains . (dot) sign
				if(strpos($email, '.') === false ) {
						$errors['email'] = 'Wrong dot sign or dot sign not found.';
				}
				
				if(count($errors) > 0) {
						return $errors;
				}
				
				return true;
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Processing a login.
		/////////////////////////////////////////////////////////////////////////////////
		public function checkPasswordByUserId($user_id, $password) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT password FROM ' . $db->table('accounts') . ' WHERE id = :id LIMIT 1');
				$db->bind(':id', $user_id);
				
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(isset($tmp['password'])) {
						$password_hash = $tmp['password'];
						
						if(true === password_verify($password, $password_hash)) {		//Aaaaaaand - you are logged in!
								return true;
						}
				}
				
				return false;
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Prüfen, ob die Mail Adresse innerhalb eines bestimmten Zeitraumes geändert wurden.
		// week_count	=> Zeitraum in Wochen.
		/////////////////////////////////////////////////////////////////////////////////
		public function isLastMailChangeOlderThanWeeks($user_id, $week_count = 4) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT new_mail_request_on FROM ' . $db->table('accounts') . ' WHERE id = :id LIMIT 1');
				$db->bind(':id', $user_id);
				
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(isset($tmp['new_mail_request_on'])) {
						$last_change = $tmp['new_mail_request_on'];
						
						//Wenn keine aktive Mail Änderung im System vermerkt ist.
						if($tmp['new_mail_request_on'] === '0000-00-00 00:00:00' || $tmp['new_mail_request_on'] === NULL) {
								return true;
						}
						
						//timestamp 4 weeks back in time.
						$history_timestamp = strtotime('-' . $week_count . 'weeks');
						$current_timestamp = strtotime($tmp['new_mail_request_on']);
						
						//Wenn die letzte Änderung mehr als 4 Wochen zurückliegt.
						if($current_timestamp < $history_timestamp) {
								return true;
						}
				}
				
				return false;
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Create content data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public static function createInDB($data) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'INSERT INTO ' . $db->table('accounts') . ' ' .
								'(account_type, superadmin, username, email, phone, email_language, firstname, lastname, ' .
								'company, company_position, street_address, street_address_house_number, zip, city, country, notice, created_on, mqllock_count, demo_accounts, email_validation_code, email_validated, ' .
								'is_supplier, is_manufacturer, gender, seller_status, email_footer, customers_number, webseller_machines_id, ' .
								'anzahl_pflicht_termine_an_werktagen, pflicht_umsatz_an_werktagen, anzahl_pflicht_verkaeufe_pro_werktag, ' .
								'pflicht_anrufdauer_pro_tag, pflicht_anrufdauer_durchschnitt, pflicht_anrufe_pro_tag, durchschnittlicher_erwarteter_angebotswert, ' .
								'is_groupleader, telefon_benutzername, telefon_passwort, telefon_port, telefon_station, telefon_server, account_gruppen_id, c4po_benutzername, ' .
								'debug ' .
								') ' .
						'VALUES ' .
								'(:account_type, :superadmin, :username, :email, :phone, :email_language, :firstname, :lastname, ' .
								':company, :company_position, :street_address, :street_address_house_number, :zip, :city, :country, :notice, :created_on, ' .':mqllock_count, :demo_accounts, :email_validation_code, :email_validated, ' .
								':is_supplier, :is_manufacturer, :gender, :seller_status, :email_footer, :customers_number, :webseller_machines_id, ' .
								':anzahl_pflicht_termine_an_werktagen, :pflicht_umsatz_an_werktagen, :anzahl_pflicht_verkaeufe_pro_werktag, ' .
								':pflicht_anrufdauer_pro_tag, :pflicht_anrufdauer_durchschnitt, :pflicht_anrufe_pro_tag, :durchschnittlicher_erwarteter_angebotswert, ' .
								':is_groupleader, :telefon_benutzername, :telefon_passwort, :telefon_port, :telefon_station , :telefon_server, :account_gruppen_id, :c4po_benutzername, ' .
								':debug ' .
								')'
				);
				
				$db->bind(':account_type', (int)$data['account_type']);
				$db->bind(':superadmin', (int)$data['superadmin']);
				$db->bind(':username', $data['username']);
				$db->bind(':email', $data['email']);
				$db->bind(':phone', $data['phone']);
				//$db->bind(':password', password_hash($data['password'], PASSWORD_BCRYPT));
				$db->bind(':email_language', $data['email_language']);
				$db->bind(':firstname', $data['firstname']);
				$db->bind(':lastname', $data['lastname']);
				$db->bind(':company', $data['company']);
				$db->bind(':company_position', $data['company_position']);
				$db->bind(':street_address', $data['street_address']);
				$db->bind(':street_address_house_number', $data['street_address_house_number']);
				$db->bind(':zip', $data['zip']);
				$db->bind(':city', $data['city']);
				$db->bind(':country', $data['country']);
				$db->bind(':notice', $data['notice']);
				$db->bind(':created_on', $data['created_on']);
				$db->bind(':mqllock_count', 0);
				$db->bind(':demo_accounts', $data['demo_accounts']);
				$db->bind(':email_validation_code', $data['email_validation_code']);
				$db->bind(':email_validated', $data['email_validated']);
				$db->bind(':is_supplier', (int)$data['is_supplier']);
				$db->bind(':is_manufacturer', (int)$data['is_manufacturer']);
				$db->bind(':gender', (int)$data['gender']);
				$db->bind(':seller_status', (int)$data['seller_status']);
				$db->bind(':email_footer', $data['email_footer']);
				$db->bind(':customers_number', $data['customers_number']);
				$db->bind(':webseller_machines_id',  (int)$data['webseller_machines_id']);
				$db->bind(':anzahl_pflicht_termine_an_werktagen',  (int)$data['anzahl_pflicht_termine_an_werktagen']);
				$db->bind(':pflicht_umsatz_an_werktagen',  (int)$data['pflicht_umsatz_an_werktagen']);
				$db->bind(':anzahl_pflicht_verkaeufe_pro_werktag',  (int)$data['anzahl_pflicht_verkaeufe_pro_werktag']);
				$db->bind(':pflicht_anrufdauer_pro_tag',  (int)$data['pflicht_anrufdauer_pro_tag']);
				$db->bind(':pflicht_anrufdauer_durchschnitt',  (int)$data['pflicht_anrufdauer_durchschnitt']);
				$db->bind(':pflicht_anrufe_pro_tag',  (int)$data['pflicht_anrufe_pro_tag']);
				$db->bind(':durchschnittlicher_erwarteter_angebotswert', (int)$data['durchschnittlicher_erwarteter_angebotswert']);
				$db->bind(':is_groupleader', (int)$data['is_groupleader']);
				$db->bind(':telefon_benutzername', $data['telefon_benutzername']);
				$db->bind(':telefon_passwort', $data['telefon_passwort']);
				$db->bind(':telefon_port', (int)$data['telefon_port']);
				$db->bind(':telefon_station', (int)$data['telefon_station']);
				$db->bind(':telefon_server', (int)$data['telefon_server']);
				$db->bind(':account_gruppen_id', (int)$data['account_gruppen_id']);
				$db->bind(':c4po_benutzername', $data['c4po_benutzername']);
				$db->bind(':debug', (int)$data['debug']);
			
				$db->execute();
				
				return $db->insertId();
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Update data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public static function updateInDB($id, $data) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery( 
						'UPDATE ' . $db->table('accounts') . ' SET ' .
								'account_type = :account_type, superadmin = :superadmin, username = :username, email = :email, phone = :phone, email_language = :email_language, firstname = :firstname, lastname = :lastname, ' .
								'company = :company, company_position = :company_position, street_address = :street_address, street_address_house_number = :street_address_house_number, zip = :zip, city = :city, country = :country, notice = :notice, ' .
								'created_on = :created_on, mqllock_count = :mqllock_count, demo_accounts = :demo_accounts, email_validation_code = :email_validation_code, email_validated = :email_validated,  ' .
								'is_supplier = :is_supplier, is_manufacturer = :is_manufacturer, gender = :gender, seller_status = :seller_status, email_footer = :email_footer, customers_number = :customers_number, webseller_machines_id = :webseller_machines_id, ' .
								'anzahl_pflicht_termine_an_werktagen = :anzahl_pflicht_termine_an_werktagen, pflicht_umsatz_an_werktagen = :pflicht_umsatz_an_werktagen, anzahl_pflicht_verkaeufe_pro_werktag = :anzahl_pflicht_verkaeufe_pro_werktag, ' .
								'pflicht_anrufdauer_pro_tag = :pflicht_anrufdauer_pro_tag, pflicht_anrufdauer_durchschnitt = :pflicht_anrufdauer_durchschnitt, pflicht_anrufe_pro_tag = :pflicht_anrufe_pro_tag, durchschnittlicher_erwarteter_angebotswert = :durchschnittlicher_erwarteter_angebotswert, ' .
								'is_groupleader = :is_groupleader, telefon_benutzername = :telefon_benutzername, telefon_passwort = :telefon_passwort, telefon_port = :telefon_port, telefon_station = :telefon_station, telefon_server = :telefon_server, account_gruppen_id = :account_gruppen_id, ' .
								'c4po_benutzername = :c4po_benutzername, debug = :debug ' .
						'WHERE ' .
								'id = :id'

				);
				$db->bind(':account_type', (int)$data['account_type']);
				$db->bind(':superadmin', (int)$data['superadmin']);
				$db->bind(':username', $data['username']);
				$db->bind(':email', $data['email']);
				$db->bind(':phone', $data['phone']);
				//$db->bind(':password', password_hash($data['password'], PASSWORD_BCRYPT));
				$db->bind(':email_language', $data['email_language']);
				$db->bind(':firstname', $data['firstname']);
				$db->bind(':lastname', $data['lastname']);
				$db->bind(':company', $data['company']);
				$db->bind(':company_position', $data['company_position']);
				$db->bind(':street_address', $data['street_address']);
				$db->bind(':street_address_house_number', $data['street_address_house_number']);
				$db->bind(':zip', $data['zip']);
				$db->bind(':city', $data['city']);
				$db->bind(':country', $data['country']);
				$db->bind(':notice', $data['notice']);
				$db->bind(':created_on', $data['created_on']);
				$db->bind(':mqllock_count', 0);
				$db->bind(':demo_accounts', $data['demo_accounts']);
				$db->bind(':email_validation_code', $data['email_validation_code']);
				$db->bind(':email_validated', $data['email_validated']);
				$db->bind(':is_supplier', (int)$data['is_supplier']);
				$db->bind(':is_manufacturer', (int)$data['is_manufacturer']);
				$db->bind(':gender', (int)$data['gender']);
				$db->bind(':seller_status', (int)$data['seller_status']);
				$db->bind(':email_footer', $data['email_footer']);
				$db->bind(':customers_number', $data['customers_number']);
				$db->bind(':webseller_machines_id',  (int)$data['webseller_machines_id']);
				$db->bind(':anzahl_pflicht_termine_an_werktagen',  (int)$data['anzahl_pflicht_termine_an_werktagen']);
				$db->bind(':pflicht_umsatz_an_werktagen',  (int)$data['pflicht_umsatz_an_werktagen']);
				$db->bind(':anzahl_pflicht_verkaeufe_pro_werktag',  (int)$data['anzahl_pflicht_verkaeufe_pro_werktag']);
				$db->bind(':pflicht_anrufdauer_pro_tag',  (int)$data['pflicht_anrufdauer_pro_tag']);
				$db->bind(':pflicht_anrufdauer_durchschnitt',  (int)$data['pflicht_anrufdauer_durchschnitt']);
				$db->bind(':pflicht_anrufe_pro_tag',  (int)$data['pflicht_anrufe_pro_tag']);
				$db->bind(':durchschnittlicher_erwarteter_angebotswert', $data['durchschnittlicher_erwarteter_angebotswert']);
				$db->bind(':is_groupleader', (int)$data['is_groupleader']);
				$db->bind(':telefon_benutzername', $data['telefon_benutzername']);
				$db->bind(':telefon_passwort', $data['telefon_passwort']);
				$db->bind(':telefon_port', (int)$data['telefon_port']);
				$db->bind(':telefon_station', (int)$data['telefon_station']);
				$db->bind(':telefon_server', (int)$data['telefon_server']);
				$db->bind(':account_gruppen_id', (int)$data['account_gruppen_id']);
				$db->bind(':c4po_benutzername', $data['c4po_benutzername']);
				$db->bind(':debug', (int)$data['debug']);
        $db->bind(':id', (int)$id);
				$db->execute();
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////////////
		// User- Online-Status speichern.
		//////////////////////////////////////////////////////////////////////////////////////////////////
		public static function setIsOnline($user_id, $is_online) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('UPDATE ' . $db->table('accounts') . ' SET is_online = :is_online WHERE id = :id LIMIT 1;');
				$db->bind(':is_online', $is_online);
				$db->bind(':id', (int)$user_id);
				$db->execute();
				
				return true;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Liste laden..
		///////////////////////////////////////////////////////////////////////////////////////////////////	
		public static function loadList() {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('accounts') . ' ORDER BY lastname, firstname');
				$result = $db->execute();
				
				$retval = false;
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$retval[] = $tmp;
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Liste anhand von der Gruppe laden
		///////////////////////////////////////////////////////////////////////////////////////////////////	
		public static function loadListByGroupId($account_gruppen_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * ' . 
						'FROM ' . $db->table('accounts') . ' ' .
						'WHERE ' .
								'account_gruppen_id = :account_gruppen_id ' .
						'ORDER BY lastname, firstname;'
				);
				$db->bind(':account_gruppen_id', $account_gruppen_id);
				$result = $db->execute();
				
				$retval = false;
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$retval[] = $tmp;
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// gesamte Liste ohne eine Gruppe laden
		///////////////////////////////////////////////////////////////////////////////////////////////////	
		public static function loadListWithoutAGroupId($account_gruppen_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * ' . 
						'FROM ' . $db->table('accounts') . ' ' .
						'WHERE ' .
								':account_gruppen_id != account_gruppen_id ' .
						'ORDER BY lastname, firstname;'
				);
				$db->bind(':account_gruppen_id', $account_gruppen_id);
				$result = $db->execute();
				
				$retval = false;
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$retval[] = $tmp;
				}
				
				return $retval;
		}

		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Liste laden..
		///////////////////////////////////////////////////////////////////////////////////////////////////	
		public static function loadListActiveAccounts() {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('accounts') . ' WHERE account_type != 0 ORDER BY lastname, firstname');
				$result = $db->execute();
				
				$retval = false;
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$retval[] = $tmp;
				}
				
				return $retval;
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////////////
		// Update TelefonRestartMarker anhand der AccountId.
		//////////////////////////////////////////////////////////////////////////////////////////////////
		public static function updateTelefonRestartMarkerByAccountId($account_id, $telefon_restart_marker) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('UPDATE ' . $db->table('accounts') . ' SET telefon_restart_marker = :telefon_restart_marker WHERE id = :id;');
				$db->bind(':telefon_restart_marker', (int)$telefon_restart_marker);
				$db->bind(':id', (int)$account_id);
				$db->execute();
		}
		
		
		///////////////////////////////////////////////////////////////////
		// Check if mail address is in use..
		///////////////////////////////////////////////////////////////////
		public static function isMailAddressInUse($email_address) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT id FROM ' . $db->table('accounts') . ' ' .
						'WHERE ' .
								'LOWER(email) = LOWER(:email_address) OR ' .
								'LOWER(original_mail) = LOWER(:original_mail) ' .
						'LIMIT 1;');
				$db->bind(':email_address', $email_address);
				$db->bind(':original_mail', $email_address);
				$result = $db->execute();
				$tmp = $result->fetchArrayAssoc();
				
				if(isset($tmp['id'])) {
						return true;
				}
				
				return false;
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////////////
		// original_mail in Datenbank für Benutzer speichern.
		//////////////////////////////////////////////////////////////////////////////////////////////////
		public static function saveOldMailAdress($id, $mail) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('UPDATE ' . $db->table('accounts') . ' SET original_mail = :original_mail WHERE id = :id;');
				$db->bind(':original_mail', $mail);
				$db->bind(':id', (int)$id);
				$db->execute();
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////////////
		// Mail Adresse in Datenbank aktualisieren.
		//////////////////////////////////////////////////////////////////////////////////////////////////
		public static function updateMailInDb($id, $mail) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('UPDATE ' . $db->table('accounts') . ' SET email = :mail WHERE id = :id;');
				$db->bind(':mail', $mail);
				$db->bind(':id', (int)$id);
				$db->execute();
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////////////
		// Mail Adresse in Datenbank aktualisieren.
		//////////////////////////////////////////////////////////////////////////////////////////////////
		public static function updateUsernameInDb($id, $username) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('UPDATE ' . $db->table('accounts') . ' SET username = :username WHERE id = :id;');
				$db->bind(':username', $username);
				$db->bind(':id', (int)$id);
				$db->execute();
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////////////
		// new_mail_request_on in Datenbank aktualisieren.
		//////////////////////////////////////////////////////////////////////////////////////////////////
		public static function update_newMailRequestOn_InDb($id, $new_mail_request_on) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('UPDATE ' . $db->table('accounts') . ' SET new_mail_request_on = :new_mail_request_on WHERE id = :id;');
				$db->bind(':new_mail_request_on', $new_mail_request_on);
				$db->bind(':id', (int)$id);
				$db->execute();
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////////////
		// new_mail_reset_key in Datenbank aktualisieren.
		//////////////////////////////////////////////////////////////////////////////////////////////////
		public static function update_newMailResetKey_InDb($id, $new_mail_reset_key) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('UPDATE ' . $db->table('accounts') . ' SET new_mail_reset_key = :new_mail_reset_key WHERE id = :id;');
				$db->bind(':new_mail_reset_key', $new_mail_reset_key);
				$db->bind(':id', (int)$id);
				$db->execute();
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////////////
		// new_mail_reset_key in Datenbank aktualisieren.
		//////////////////////////////////////////////////////////////////////////////////////////////////
		public static function updatePassword($id, $password) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('UPDATE ' . $db->table('accounts') . ' SET password = :password WHERE id = :id;');
				$db->bind(':password', password_hash($password, PASSWORD_BCRYPT));
				$db->bind(':id', (int)$id);
				$db->execute();
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////////////
		// recover_password_token in Datenbank aktualisieren.
		//////////////////////////////////////////////////////////////////////////////////////////////////
		public static function updateRecoverPasswordToken($id, $recover_password_token) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('UPDATE ' . $db->table('accounts') . ' SET recover_password_token = :recover_password_token WHERE id = :id;');
				$db->bind(':recover_password_token', $recover_password_token);
				$db->bind(':id', (int)$id);
				$db->execute();
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////////////
		// recover_password_on in Datenbank aktualisieren.
		//////////////////////////////////////////////////////////////////////////////////////////////////
		public static function updateRecoverPasswordOn($id, $recover_password_on) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('UPDATE ' . $db->table('accounts') . ' SET recover_password_on = :recover_password_on WHERE id = :id;');
				$db->bind(':recover_password_on', $recover_password_on);
				$db->bind(':id', (int)$id);
				$db->execute();
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////////////
		// delete_account_token in Datenbank aktualisieren.
		//////////////////////////////////////////////////////////////////////////////////////////////////
		public static function update_deleteAccountToken_InDb($id, $delete_account_token) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('UPDATE ' . $db->table('accounts') . ' SET delete_account_token = :delete_account_token WHERE id = :id;');
				$db->bind(':delete_account_token', $delete_account_token);
				$db->bind(':id', (int)$id);
				$db->execute();
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////////////
		// delete_account_on in Datenbank aktualisieren.
		//////////////////////////////////////////////////////////////////////////////////////////////////
		public static function update_deleteAccountOn_InDb($id, $delete_account_on) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('UPDATE ' . $db->table('accounts') . ' SET delete_account_on = :delete_account_on WHERE id = :id;');
				$db->bind(':delete_account_on', $delete_account_on);
				$db->bind(':id', (int)$id);
				$db->execute();
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////////////
		// Plattform-Arten auslesen.
		//////////////////////////////////////////////////////////////////////////////////////////////////
		public static function getRegistrationPlatformTypesFromDb() {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SHOW COLUMNS FROM ' . $db->table('accounts') . ' LIKE \'registration_platform\'');
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				$option_array = explode("','",preg_replace("/(enum|set)\('(.+?)'\)/","\\2", $tmp['Type']));
				
				return $option_array;
		}
}

?>