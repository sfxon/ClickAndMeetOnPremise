<?php

class cAdminBatchDelete extends cModule {
		var $template = 'admin';
		var $navbar_title = 'Kalender - Bereinigung';
		var $navbar_id = 0;
		var $errors = array();
		var $errors_description = array();
		var $results = array();
		var $data;
		
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
				if(false === cAccount::adminrightCheck('cAdminCcConfig', 'USE_MODULE', (int)$_SESSION['user_id'])) {
						header('Location: index.php?s=cAdmin&error=1000');
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
				$cAdmin->appendBreadcrumb('Kalender - Bereinigung', 'index.php?s=cAdminCcConfig');
				
				switch($this->action) {
						case 'ajaxDeleteMonthsUpload':
								$this->ajaxDeleteMonthsUpload();
								die;
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Daten für einen Monat speichern.
		///////////////////////////////////////////////////////////////////
		private function ajaxDeleteMonthsUpload() {
				//Load post data.
				$current_day = 1;
				$current_month = (int)core()->getPostVar('current_month');
				$current_year = (int)core()->getPostVar('current_year');
				
				$date_from_day = (int)core()->getPostVar('date_from_day');
				$date_from_month = (int)core()->getPostVar('date_from_month');
				$date_from_year = (int)core()->getPostVar('date_from_year');
				
				$date_to_day = (int)core()->getPostVar('date_to_day');
				$date_to_month = (int)core()->getPostVar('date_to_month');
				$date_to_year = (int)core()->getPostVar('date_to_year');
				
				$weekdays_and_times = core()->getPostVar('weekdays_and_times');
				
				$event_location_id = (int)core()->getPostVar('event_location_id');
				$user_unit_id = (int)core()->getPostVar('user_unit_id');
				
				$statis = core()->getPostVar('statis');
				
				//Delete Mode anhand event Location und user unit id ermitteln.
				$delete_mode = 'all';
				
				if($user_unit_id != 0) {
						$delete_mode = 'user_unit';
				} else if($event_location_id != 0) {
						$delete_mode = 'event_location';
				}
				
				//Wenn wir gerade den Startmonat verarbeiten, beginnen wir erst ab dem gewählten Tag.
				if($current_month == $date_from_month && $current_year == $date_from_year) {
						$current_day = $date_from_day;
				}
				
				//Check date to..
				$day_to = cal_days_in_month(CAL_GREGORIAN, $current_month, $current_year);
				
				//Wenn wir gerade den Endmonat verarbeiten, enden wir am letzten Tag..
				if($current_month == $date_to_month && $current_year == $date_to_year) {
						$day_to = $date_to_day;
				}
				
				//Durch die Tage des Monats laufen..
				for($i = $current_day; $i <= $day_to; $i++) {
						$day = $current_year . '-' . str_pad($current_month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($i, 2, '0', STR_PAD_LEFT);
						
						$timestamp = strtotime($day);
						$weekday = date('N', $timestamp);		//N 	Numerische Repräsentation des Wochentages gemäß ISO-8601 (in PHP 5.1.0 hinzugefügt) 	1 (für Montag) bis 7 (für Sonntag)
						$weekday_status = false;
						$data = false;
						
						foreach($weekdays_and_times as $wat) {
								if(!isset($wat['weekday']) || !isset($wat['status'])) {
										continue;
								}
								
								if($wat['weekday'] > $weekday) {
										break;
								}
								
								if(isset($wat['data']) && is_array($wat['data']) && count($wat['data']) > 0) {
										$data = $wat['data'];
								}
								
								if($wat['weekday'] == $weekday) {
										$weekday_status = $wat['status'];
										break;
								}
						}
						
						//Wenn der Wochentag aktiviert ist -> lege die Zeiten für diesen Wochentag an..
						if($weekday_status == "true") {
								//Durch die Zeiten laufen..
								$this->deleteTimes(
										$i,
										$current_month,
										$current_year,
										$data,
										$event_location_id,
										$user_unit_id,
										$delete_mode,
										$statis
								);
						}
				}
				
				//Say okay..
				$retval = array(
						'status' => 'success',
						'data' => array(
						)
				);
				$retval = json_encode($retval, JSON_PRETTY_PRINT);
				echo $retval;
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Zeiten einfügen.
		///////////////////////////////////////////////////////////////////
		private function deleteTimes($day, $month, $year, $times, $event_location_id, $user_unit_id, $delete_mode, $statis) {
				foreach($times as $time) {
						$hour = (int)$time['timeFrom']['hour'];
						$minute = (int)$time['timeFrom']['minute'];
						$hour_to = (int)$time['timeTo']['hour'];
						$minute_to = (int)$time['timeTo']['minute'];
						
						$sql_day = str_pad($day, 2, '0', STR_PAD_LEFT);
						$sql_month = str_pad($month, 2, '0', STR_PAD_LEFT);
						$sql_year = $year;
						$sql_hour_from = str_pad($hour, 2, '0', STR_PAD_LEFT);
						$sql_minute_from = str_pad($minute, 2, '0', STR_PAD_LEFT);
						$sql_hour_to = str_pad($hour_to, 2, '0', STR_PAD_LEFT);
						$sql_minute_to = str_pad($minute_to, 2, '0', STR_PAD_LEFT);
						
						$between_from = $sql_year . '-' . $sql_month . '-' . $sql_day . ' ' . $sql_hour_from . ':' . $sql_minute_from;
						$between_to = $sql_year . '-' . $sql_month . '-' . $sql_day . ' ' . $sql_hour_to . ':' . $sql_minute_to;
						
						foreach($statis as $status) {
								if($delete_mode == 'all') {
										$this->deleteAppointments($between_from, $between_to, $status);
								} else if($delete_mode == 'user_unit') {
										$this->deleteAppointmentsUserUnit($between_from, $between_to, $status, $user_unit_id);
								} else if($delete_mode == 'event_location') {
										$this->deleteAppointmentsEventLocation($between_from, $between_to, $status, $event_location_id);
								}
						}
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Termin einfügen.
		///////////////////////////////////////////////////////////////////
		private function deleteAppointments($between_from, $between_to, $status) {
				$appointment = new cAppointment();
				$appointment->deleteByDateFromAndDateToAndAndStatus($between_from, $between_to, $status);
		}
		
		///////////////////////////////////////////////////////////////////
		// Anhand der User Unit löschen.
		///////////////////////////////////////////////////////////////////
		private function deleteAppointmentsUserUnit($between_from, $between_to, $status, $user_unit_id) {
				$appointment = new cAppointment();
				$appointment->deleteByDateFromAndDateToAndAndStatusAndUserUnit($between_from, $between_to, $status, $user_unit_id);
		}
		
		///////////////////////////////////////////////////////////////////
		// Anhand der Event Location löschen.
		///////////////////////////////////////////////////////////////////
		private function deleteAppointmentsEventLocation($between_from, $between_to, $status, $event_location_id) {
				$appointment = new cAppointment();
				$appointment->deleteByDateFromAndDateToAndAndStatusAndEventLocation($between_from, $between_to, $status, $event_location_id);
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the page content.
		///////////////////////////////////////////////////////////////////
		public function content() {
				$this->drawEditor();
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the editor.
		///////////////////////////////////////////////////////////////////
		function drawEditor() {
				$iEventLocations = new cEventLocations();
				$event_locations = $iEventLocations->loadList($_SESSION['user_id']);
				
				$iUserUnit = new cUserUnit();
				$user_units = $iUserUnit->loadList();
				
				$iCmAppointmentStatus = new cCmAppointmentStatus();
				$appointment_status_list = $iCmAppointmentStatus->loadList();
						
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				
				$renderer->assign('DATE_FROM', "");
				$renderer->assign('DATE_TO', "");
				
				$renderer->assign('TEMPLATE_URL', cCMS::loadTemplateUrl(core()->get('site_id')));
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$renderer->assign('EVENT_LOCATIONS', $event_locations);
				$renderer->assign('USER_UNITS', $user_units);
				$renderer->assign('APPOINTMENT_STATUS_LIST', $appointment_status_list);
				$renderer->render('site/adminBatchDelete/editor.html');
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
										'position' => 250,
										'title' => 'Click&Meet'
								),
								array(
										'position' => 200,
										'title' => 'Daten löschen'
								)
						);
		        $cAdmin->registerAdminMenuEntry($admin_menu_entry_path, 'index.php?s=cAdminBatchDelete');
				}
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Loads a content entry.
		/////////////////////////////////////////////////////////////////////////////////
		public function footer() {
				$additional_output = 
						"\n" . '<script src="data/templates/' . $this->template . '/js/mvUploadQueue.js"></script>' .
						"\n" . '<script src="data/templates/' . $this->template . '/js/mvTime.js"></script>' .
            "\n" . '<script src="data/templates/' . $this->template . '/js/mvDate.js"></script>' .
						"\n" . '<script src="data/templates/' . $this->template . '/js/admin_batch_delete.js"></script>' .
						"\n" . '<script src="data/templates/' . $this->template . '/js/admin_batch_delete_check.js"></script>' .
            "\n" . '<script src="data/templates/' . $this->template . '/js/admin_batch_delete_upload.js"></script>' .
						"\n";
				$renderer = core()->getInstance('cRenderer');
				$renderer->renderText($additional_output);
		}
}
?>