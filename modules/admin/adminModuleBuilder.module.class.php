<?php

class cAdminModuleBuilder extends cModule {
		var $template = 'admin';
		var $navbar_title = 'Admin Module Builder';
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
				if(false === cAccount::adminrightCheck('cAdminModuleBuilder', 'USE_MODULE', (int)$_SESSION['user_id'])) {
						header('Location: index.php?s=cAdmin&error=304');
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
				$cAdmin->appendBreadcrumb('Admin Module Builder', 'index.php?s=cAdminModuleBuilder');
				
				switch($this->action) {
						case 'delete':
								$this->initData();
								$this->getContent();
								$this->delete();
								break;
						case 'confirm_delete':
								$this->initData();
								$this->getContent();
								$this->data['url'] = 'index.php?s=cAdminModuleBuilder&amp;action=delete&amp;id=' . (int)$this->data['data']['id'];
								$cAdmin->appendBreadcrumb('L&ouml;schen best&auml;tigen', '');
								$this->navbar_title = 'L&ouml;schen best&auml;tigen';
								break;
						
						case 'edit':
								$this->initData();
								$this->getContent();
								$this->data['url'] = 'index.php?s=cAdminModuleBuilder&amp;action=update&amp;id=' . (int)$this->data['data']['id'];
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
								$this->data['url'] = 'index.php?s=cAdminModuleBuilder&amp;action=create';
								$cAdmin->appendBreadcrumb('Neu', '');
								$this->navbar_title = 'Neu';
								break;
						default:
								$this->getList();
								break;
								
						case 'confirm_overwrite':
								$this->initData();
								$this->getContent();
								$this->data['url'] = 'index.php?s=cAdminModuleBuilder&amp;action=overwrite&amp;id=' . (int)$this->data['data']['id'];
								$cAdmin->appendBreadcrumb('&Uuml;berschreiben best&auml;tigen', '');
								$this->navbar_title = '&Uuml;berschreiben best&auml;tigen';
								$this->confirmOverwrite();
								break;
								
						case 'overwrite':
								$this->initData();
								$this->getContent();
								//$this->update();
								$this->overwrite();
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
						header('Location: index.php?s=cAdminModuleBuilder&error=331');
						die;
				}

				//delete button
				if($button_delete !== NULL && $button_delete === 'delete') {
						cModuleBuilder::deleteById($this->data['data']['id']);

						header('Location: index.php?s=cAdminModuleBuilder&success=332');
						die;
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Prüfen, ob das Modul schon existiert.
		///////////////////////////////////////////////////////////////////
		public function confirmOverwrite() {
				$id = (int)core()->getGetVar('id');
				$data = cModuleBuilder::loadById($id);
				
				if(NULL === $data['generation_time']) {
						header('Location: index.php?s=cAdminModuleBuilder&action=overwrite&id=' . $id);
						die;
				}			
		}
				
		///////////////////////////////////////////////////////////////////
		// Objekt generieren
		///////////////////////////////////////////////////////////////////
		public function overwrite() {
				$id = (int)core()->getGetVar('id');
				$this->data['data'] = cModuleBuilder::loadById($id);
			
				$button_do_not_overwrite = core()->getPostVar('button_do_not_overwrite');
				$button_overwrite = core()->getPostVar('button_overwrite');
				
				$this->generateModuleCode();				
				
				$this->saveGenerationTime();
		
				//$data['generation_time'] = core()->getPostVar('generation_time');
				header('Location: index.php?s=cAdminModuleBuilder&success=416');
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Objekt generieren
		///////////////////////////////////////////////////////////////////
		public function generateModuleCode() {
				//Standard-Felder laden.
				$fields = cModuleBuilderFields::getDefaultFields($this->data['data']['id']);
				
				//Felder laden.
				$tmp_fields = cModuleBuilderFields::loadListByModuleBuilderId($this->data['data']['id']);
				
				//Add fields from database to default fields.
				$fields = array_merge($fields, $tmp_fields);
				$this->data['data']['fields'] = $fields;
				
				//Get database table name without prefix.
				$this->data['data']['database_table_raw'] = $this->getRawDatabaseTableName();
				
				//Generate files..
				$this->buildDatabaseTableAndFields();
				$this->buildModuleFile('admin_module_master', 'cAdminDemoMaster.master.class.php.tpl', $this->data['data']['admin_module'] . 'Master.master.class.php');
				$this->buildModuleFile('module_master', 'cDemoMaster.master.class.php.tpl', $this->data['data']['data_module'] . 'Master.master.class.php');
				$this->buildModuleFile('confirm_delete_dialog', 'confirm_delete_dialog.html.tpl', 'confirm_delete_dialog.html');
				$this->buildModuleFile('editor', 'editor.html', 'editor.html');
				$this->buildModuleFile('list', 'list.html', 'list.html');
				
				//Generate the next two only, if there was nothing generated till now!
				if(NULL === $this->data['data']['generation_time']) {
						$this->buildModuleFile('admin_module', 'cAdminDemo.module.class.php.tpl', $this->data['data']['admin_module'] . '.module.class.php');
						$this->buildModuleFile('module', 'cDemo.module.class.php.tpl', $this->data['data']['data_module'] . '.module.class.php');
				}
				
				//Create systemright (if it does not exist).
				$rights_id = cSystemrights::getByModuleAndRightsKey($this->data['data']['admin_module'], 'USE_MODULE');
				
				if(false === $rights_id) {
						$rights_id = cSystemrights::createInDB(array('module' => $this->data['data']['admin_module'], 'rightskey' => 'USE_MODULE'));
				}
				
				//Give rights to the admins (if they do not own yet).
				//1st: load all Admins.
				$admins = cAccount::loadAdmins();
				
				foreach($admins as $admin) {
						$right_exists = cAdminrights::checkIfAdminrightExists($admin['id'], $rights_id);
						
						if(true === $right_exists) {
								cAdminrights::update($admin['id'], $rights_id, 1);
						} else {
								cAdminrights::create($admin['id'], $rights_id, 1);
						}
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Build database table and fields.
		///////////////////////////////////////////////////////////////////
		public function getRawDatabaseTableName() {
				$db = core()->get('db');
				$db->useInstance('systemdb');
			
				$prefix = $db->getTablePrefix();
				$table_name = $this->data['data']['database_table'];
				$table_name = str_replace($prefix, '', $table_name);
				
				return $table_name;
		}
		
		///////////////////////////////////////////////////////////////////
		// Build database table and fields.
		///////////////////////////////////////////////////////////////////
		public function buildModuleFile($file_type, $input_file_name, $output_file_name) {
				//get the information, if this has already been rendered. If it has, we do not override the extension files.
				$generation_time = $this->data['data']['generation_time'];
				
				$b_has_been_generated = false;
				
				if(NULL !== $generation_time) {
						$b_has_been_generated = true;
				}
				
				//get destination directory (create them, if necessary).
				$destination_module = $this->data['data']['data_module'];
				$destination_directory_and_file = $this->getDestinationPathAndFilenameForModuleFile($file_type, $output_file_name, $destination_module);			//also creates the paths, if necessary.
				
				//Get source file.
				$source_directory = 'modules/moduleBuilder/presets/';
				$source_directory_and_file = $source_directory . $input_file_name;
				
				//load the contents of the template file
				$template = file_get_contents($source_directory_and_file);
				
				//Run smarty on the template file.
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$final_template = $renderer->fetchFromString($template);
				
				//output the file in the destination directory
				file_put_contents($destination_directory_and_file, $final_template);
		}
		
		
		///////////////////////////////////////////////////////////////////
		// Get destination path and filename for module file.
		// Creates the paths to the files, if necessary.
		///////////////////////////////////////////////////////////////////
		public function getDestinationPathAndFilenameForModuleFile($file_type, $file_name, $destination_module) {
				$destination_directory = 'modules/' . $destination_module;
				
				if(!is_dir($destination_directory)) {
						mkdir($destination_directory, 0777);
				}
				
				$destination_directory_admin_templates = $destination_directory . '/admin_templates/';
				
				if(!is_dir($destination_directory_admin_templates)) {
						mkdir($destination_directory_admin_templates, 0777);
				}
				
				$destination_directory_master_templates = $destination_directory . '/master/';
				
				if(!is_dir($destination_directory_master_templates)) {
						mkdir($destination_directory_master_templates, 0777);
				}
				
				switch($file_type) {
						case 'admin_module':
						case 'module':
								return $destination_directory . '/' . $file_name;
						case 'admin_module_master':
						case 'module_master':
								return $destination_directory_master_templates . '/' . $file_name;
						case 'confirm_delete_dialog':
						case 'editor':
						case 'list':
								return $destination_directory_admin_templates . '/' . $file_name;
						default:
								die('Unknown file_type in ' . __FILE__ . ', Zeile ' . __LINE__);
				}
				
				return '';
		}
		
		///////////////////////////////////////////////////////////////////
		// Build database table and fields.
		///////////////////////////////////////////////////////////////////
		public function buildDatabaseTableAndFields() {
				$table_name = $this->data['data']['database_table'];
				
				//Check if table exists.
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$result = $db->checkTableExistence($table_name);

				//If table does not exist - create it.
				if(false === $result) {
						$this->createDatabaseTable();
				} else {
						$this->updateDatabaseTable();
				}
				
				$this->saveTableNameForLastDatabaseTableBuild();
				$this->saveFieldnamesForLastDatabaseTableBuild();
		}
		
		///////////////////////////////////////////////////////////////////
		// Save table name for last database table build..
		///////////////////////////////////////////////////////////////////
		public function saveTableNameForLastDatabaseTableBuild() {
				$moduleBuilder_id = $this->data['data']['id'];
				$last_builds_database_table = $this->data['data']['database_table'];
				
				cModuleBuilder::updateLastBuildsDatabaseTableNameById($moduleBuilder_id, $last_builds_database_table);				
		}
		
		///////////////////////////////////////////////////////////////////
		// Save fieldnames for last database table build..
		///////////////////////////////////////////////////////////////////
		public function saveFieldnamesForLastDatabaseTableBuild() {
				$moduleBuilder_id = $this->data['data']['id'];
				
				//go through all fields and save the current fieldname in the last_builds_fieldname column.
				foreach($this->data['data']['fields'] as $field) {
						if(0 == $field['id']) {
								continue;
						}
					
						//Save last_builds_fieldname
						cModuleBuilderFields::updateLastBuildsFieldDataByModuleBuilderFieldsId($field['id'], $field['field_name'], $field['data_type'], $field['length']);
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Update database tables and fields.
		///////////////////////////////////////////////////////////////////
		public function updateDatabaseTable() {
				//Update database name if necessary.
				$this->updateDatabaseTablesName();
				
				//Update field names
				$this->updateDatabaseTableFieldsNames();
				
				//Update datatypes and length
				$this->updateDatabaseTableFieldsLengths();		
		}
		
		///////////////////////////////////////////////////////////////////
		// Update database tables name.
		///////////////////////////////////////////////////////////////////
		public function updateDatabaseTablesName() {
				$table_name = $this->data['data']['database_table'];
				$last_builds_table_name = $this->data['data']['last_builds_database_table'];
				
				if($last_builds_table_name == '') {
						return;
				}
				
				if($table_name != $last_builds_table_name) {
						$query = 'RENAME TABLE `' . $last_builds_table_name . '` TO `' . $table_name . '`';
						
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery($query);
						$db->execute();			
				}
			
		}
		
		///////////////////////////////////////////////////////////////////
		// Update database tables and fields.
		///////////////////////////////////////////////////////////////////
		public function updateDatabaseTableFieldsNames() {
				$table_name = $this->data['data']['database_table'];
				
				foreach($this->data['data']['fields'] as $field) {
						//Cannot modify the default entries.
						if($field['id'] == 'id' || $field['id'] == 'title') {
								continue;
						}
					
						//Skip fields that have the same name like the original fields.
						if($field['field_name'] == $field['last_builds_field_name']) {
								continue;
						}
						
						//Get the datatype sql query part.
						$data_type_query_part = $this->getSQLDatatypeQueryByModuleBuilderDatatype($field['data_type'], $field['length']);
						
						//Check if new field exists.. (Removed this check, because this would be the part for the user interface).
						//If it exists - this is an error!! Cannot have the same field twice! Show error!

						//Check if original column exists..
						if($field['last_builds_field_name'] == '') {		//If original column exists, update it with the new name
								$this->createDatabaseField($table_name, $field['field_name'], $data_type_query_part);
						} else {		//If original column does not exist - create the new column.
								$this->updateDatabaseField($table_name, $field['last_builds_field_name'], $field['field_name'], $data_type_query_part);
						}
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Update database table fields type and length
		///////////////////////////////////////////////////////////////////
		public function updateDatabaseTableFieldsLengths() {
				$table_name = $this->data['data']['database_table'];
				
				foreach($this->data['data']['fields'] as $field) {
						//Cannot modify the default entries.
						if($field['id'] == 'id' || $field['id'] == 'title') {
								continue;
						}
					
						//Skip fields that have the same values like the original fields.
						if($field['data_type'] == $field['last_builds_data_type'] && $field['length'] == $field['last_builds_length']) {
								continue;
						}
						
						//Get the datatype sql query part.
						$data_type_query_part = $this->getSQLDatatypeQueryByModuleBuilderDatatype($field['data_type'], $field['length']);

						//Check if original column exists..
						$this->updateDatabaseFieldsDataTypeAndLength($table_name, $field['field_name'], $data_type_query_part);
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Datenbankfeld erstellen.
		///////////////////////////////////////////////////////////////////
		public function createDatabaseField($table_name, $field_name, $data_type_query_part) {
				$query =
						'ALTER TABLE `' . $table_name . '` ' .
						'ADD `' . $field_name . '` ' . $data_type_query_part;
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery($query);
				$db->execute();
		}
		
		///////////////////////////////////////////////////////////////////
		// Datenbankfeld aktualisieren.
		///////////////////////////////////////////////////////////////////
		public function updateDatabaseFieldsDataTypeAndLength($table_name, $field_name, $data_type_query_part) {
				$query = 
						'ALTER TABLE `' . $table_name . '` ' .
						'MODIFY `' . $field_name . '` ' . $data_type_query_part;
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery($query);
				$db->execute();	
		}
		
		///////////////////////////////////////////////////////////////////
		// Datenbankfeld aktualisieren.
		///////////////////////////////////////////////////////////////////
		public function updateDatabaseField($table_name, $last_builds_field_name, $field_name, $data_type_query_part) {
				$query = 
						'ALTER TABLE `' . $table_name . '` ' .
						'CHANGE `' . $last_builds_field_name . '` `' . $field_name . '` ' . $data_type_query_part . ';';
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery($query);
				$db->execute();	
				
				/*
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery($query);
				$db->execute();	
				*/
		}
		
		///////////////////////////////////////////////////////////////////
		// Build database table and fields.
		///////////////////////////////////////////////////////////////////
		public function createDatabaseTable() {
				$binding_parameters = array();
			
				$table_name = $this->data['data']['database_table'];
				$query = 'CREATE TABLE ' . $table_name . '(';
				$binding_parameters[] = array(
						':table_name' => $table_name
				);
				
				//build fields.
				$field_query = '';
				$value_query = '';
				
				foreach($this->data['data']['fields'] as $field) {
						if(strlen($field_query) > 0) {
								$field_query .= ', ';
						}
						
						$data_type = $this->getSQLDatatypeQueryByModuleBuilderDatatype($field['data_type'], $field['length']);
						$default_value = $this->getSQLDatatypeDefaultValuesByMOduleBuilderDatatype($field['data_type']);
						
						if($default_value != '') {
								$default_value = ' ' . $default_value;
						}
						
						$field_string = '`' . $field['field_name'] . '` ' . $data_type . $default_value;
						
						if($field['id'] == 0 && $field['field_name'] == 'id') {
								$field_string = '`id` int(11) NOT NULL AUTO_INCREMENT';
						}
						
						if($field['id'] == 0 && $field['field_name'] == 'title') {
								$field_string = '`title` varchar(256) NOT NULL';
						}
						
						$field_query .= $field_string;
				}
				
				$field_query .= ', PRIMARY KEY (`id`)';
				
				$query .= $field_query;
				
				$query .= ')';
				$query .= 'ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;';
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery($query);
				$db->execute();
		}
		
		///////////////////////////////////////////////////////////////////
		// Sql Datentyp Query anhand des Module Builder Datentypes abholen.
		///////////////////////////////////////////////////////////////////
		public function getSQLDatatypeDefaultValuesByMOduleBuilderDatatype($module_builder_data_type) {
				$retval = '';
						
				switch($module_builder_data_type) {
						case 'int':
								$retval = 'DEFAULT 0';
								break;
						case 'float':
								$retval = 'DEFAULT 0.0';
								break;
						case 'varchar':
								$retval = '';
								break;
						case 'char':
								$retval = '';
								break;
						case 'foreign_table_id':
								$retval = 'DEFAULT 0';
								break;
						case 'timestamp':
								$retval = '';
								break;
						case 'datetime':
								$retval = '';
								break;
						case 'date':
								$retval = '';
								break;
						case 'time':
								$retval = '';
								break;
						case 'text':
								$retval = '';
								break;
				}
						
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////
		// Sql Datentyp Query anhand des Module Builder Datentypes abholen.
		///////////////////////////////////////////////////////////////////
		public function getSQLDatatypeQueryByModuleBuilderDatatype($module_builder_data_type, $length) {
				$data_type = 'int(11)';
						
				switch($module_builder_data_type) {
						case 'int':
								$data_type = 'int(' . $length . ')';
								break;
						case 'float':
								$data_type = 'float';
								break;
						case 'varchar':
								$data_type = 'varchar(' . $length . ')';
								break;
						case 'char':
								$data_type = 'char(' . $length . ')';
								break;
						case 'foreign_table_id':
								$data_type = 'int(11)';
								break;
						case 'timestamp':
								$data_type = 'timestamp';
								break;
						case 'datetime':
								$data_type = 'DATETIME';
								break;
						case 'date':
								$data_type = 'DATE';
								break;
						case 'time':
								$data_type = 'time';
								break;
						case 'text':
								$data_type = 'text';
								break;
				}
						
				return $data_type;
		}
				

		///////////////////////////////////////////////////////////////////
		// Objekt generieren
		///////////////////////////////////////////////////////////////////
		public function saveGenerationTime() {
				$id = (int)core()->getGetVar('id');

				if(0 == $id) {
							header('Location: index.php?s=cAdminModuleBuilder&error=305');
							die;
					}

					$data['id'] = $id;
					$data['generation_time'] = date('Y-m-d H:i:s');

					if(NULL === cModuleBuilder::loadById((int)$data['id'])) {
							header('Location: index.php?s=cAdminModuleBuilder&error=306');
							die;
					}

					cModuleBuilder::updateGenerationTimeInDB($id, $data);
		}
		
		
		///////////////////////////////////////////////////////////////////
		// Prepare data for the editor.
		///////////////////////////////////////////////////////////////////
		function initData() {
				$this->data['data']['id'] = 0;
				$this->data['data']['title'] = '';
				$this->data['data']['data_module'] = '';
				$this->data['data']['admin_module'] = '';
				$this->data['data']['database_table'] = '';
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
					case 'confirm_overwrite':
								$this->drawConfirmOverwriteDialog();
								break;
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
		// Draw the editor.
		///////////////////////////////////////////////////////////////////
		function drawConfirmOverwriteDialog() {
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$renderer->assign('SITE_URLS', cSite::loadSiteUrls());
				$renderer->assign('NAVBARS', cAdminnavbaredit::loadNavbarList());
				$renderer->assign('ERRORS', $this->errors);
				$renderer->render('site/adminModuleBuilder/confirm_overwrite_dialog.html');
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
				$renderer->render('site/adminModuleBuilder/confirm_delete_dialog.html');
		}
		
		
		///////////////////////////////////////////////////////////////////
		// Draw the editor.
		///////////////////////////////////////////////////////////////////
		function drawEditor() {
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$renderer->render('site/adminModuleBuilder/editor.html');
		}

		///////////////////////////////////////////////////////////////////
		// Draw the list view.
		///////////////////////////////////////////////////////////////////
		function drawList() {
				$success = core()->getGetVar('success');
				
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$renderer->assign('SUCCESS', $success);
				$renderer->render('site/adminModuleBuilder/list.html');
		}
		
		///////////////////////////////////////////////////////////////////
		// update an entry
		///////////////////////////////////////////////////////////////////
		public function update() {
				$id = (int)core()->getGetVar('id');
				
				if(0 == $id) {
						header('Location: index.php?s=cAdminModuleBuilder&error=305');
						die;
				}
				
				$data['id'] = $id;
				$data['title'] = core()->getPostVar('title');
				$data['data_module'] = core()->getPostVar('data_module');
				$data['admin_module'] = core()->getPostVar('admin_module');
				$data['database_table'] = core()->getPostVar('database_table');
				
				if(NULL === cModuleBuilder::loadById((int)$data['id'])) {
						header('Location: index.php?s=cAdminModuleBuilder&error=306');
						die;
				}
				
				$id = $this->save($data);
				
				if(empty($id)) {
						header('Location: index.php?s=cAdminModuleBuilder&error=307');
						die;
				}
	
				header('Location: index.php?s=cAdminModuleBuilder&action=edit&id=' . $id . '&success=308');
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Crate a content entry.
		///////////////////////////////////////////////////////////////////
		function create() {
				$data['id'] = 0;
				$data['title'] = core()->getPostVar('title');
				$data['data_module'] = core()->getPostVar('data_module');
				$data['admin_module'] = core()->getPostVar('admin_module');
				$data['database_table'] = core()->getPostVar('database_table');
				
				$id = $this->save($data);
				
				if(empty($id)) {
						header('Location: index.php?s=cAdminModuleBuilder&error=309');
						die;
				}
	
				header('Location: index.php?s=cAdminModuleBuilder&action=edit&id=' . $id . '&success=310');
				die;
		}

		///////////////////////////////////////////////////////////////////
		// Loads a list of content entries.
		///////////////////////////////////////////////////////////////////
		public function loadList() {
				return cModuleBuilder::loadList();
		}
	
		/////////////////////////////////////////////////////////////////////////////////
		// Save data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public function save($data) {
				$id = (int)$data['id'];
		
				if(0 === $id) {
						return cModuleBuilder::createInDB($data);
				}
				
				cModuleBuilder::updateInDB($id, $data);
				
				return $data['id'];
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Loads a content entry.
		/////////////////////////////////////////////////////////////////////////////////
		public function getContent() {
				$id = (int)core()->getGetVar('id');
				$this->data['data'] = cModuleBuilder::loadById($id);
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the page footer.
		///////////////////////////////////////////////////////////////////
		public function footer() {
				$additional_output = 
						"\n" . '<script src="data/templates/' . $this->template . '/js/adminModuleBuilder.js"></script>' .
						"\n";
				$renderer = core()->getInstance('cRenderer');
				$renderer->renderText($additional_output);
		}
		
		//////////////////////////////////////////////////////////////////////////////////
		// Hook us into the system in the additional hooks..
		//////////////////////////////////////////////////////////////////////////////////
    public static function setAdditionalHooks() {
				//core()->setHook('cCore|init', 'addMenuBarEntries');
				
    }
		
		//////////////////////////////////////////////////////////////////////////////////
		// Callback function, adds a menu item.
		//////////////////////////////////////////////////////////////////////////////////
		public static function addMenuBarEntries() {
				$cAdmin = core()->getInstance('cAdmin');
				
				if(false !== $cAdmin) {
						$admin_menu_entry_path = array(
								array(
										'position' => 900,
										'title' => 'Module Builder'
								),
								array(
										'position' => 0,
										'title' => 'Module Builder'
								)
						);
		        $cAdmin->registerAdminMenuEntry($admin_menu_entry_path, 'index.php?s=cAdminModuleBuilder');
				}
		}
}

?>