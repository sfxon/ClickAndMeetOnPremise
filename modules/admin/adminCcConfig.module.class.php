<?php

class cAdminCcConfig extends cModule {
		var $template = 'admin';
		var $navbar_title = TEXT_MODULE_TITLE_CCCONFIG;
		var $navbar_id = 0;
		var $errors = array();
		var $errors_description = array();
		var $results = array();
		var $data;
		var $event_locations = array();
		var $user_units = array();
		
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
				$cAdmin->appendBreadcrumb(TEXT_MODULE_TITLE_CCCONFIG, 'index.php?s=cAdminCcConfig');
				
				$iEventLocations = new cEventLocations();
				$this->event_locations = $iEventLocations->loadList($_SESSION['user_id']);
				
				$iUserUnit = new cUserUnit();
				$this->user_units = $iUserUnit->loadList();
				
				switch($this->action) {
						case 'ajaxSaveMonth':
								$this->ajaxSaveMonth();
								die;
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Daten für einen Monat speichern.
		///////////////////////////////////////////////////////////////////
		private function ajaxSaveMonth() {
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
				
				$appointment_duration_in_minutes = (int)core()->getPostVar('appointment_duration_in_minutes');
				$appointment_count = (int)core()->getPostVar('appointment_count');
				$event_location_id = (int)core()->getPostVar('event_location_id');
				$user_unit_id = (int)core()->getPostVar('user_unit_id');
				
				//Delete Mode anhand event Location und user unit id ermitteln.
				$creation_mode = 'all';
				
				if($user_unit_id != 0) {
						$creation_mode = 'user_unit';
				} else if($event_location_id != 0) {
						$creation_mode = 'event_location';
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
						$status = false;
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
										$status = $wat['status'];
										break;
								}
						}
						
						//Wenn der Wochentag aktiviert ist -> lege die Zeiten für diesen Wochentag an..
						if($status == "true") {
								//Durch die Zeiten laufen..
								$this->insertTimes(
										$i,
										$current_month,
										$current_year,
										$data,
										$appointment_duration_in_minutes,
										$appointment_count,
										$event_location_id,
										$user_unit_id,
										$creation_mode
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
		private function insertTimes($day, $month, $year, $times, $appointment_duration_in_minutes, $appointment_count, $event_location_id, $user_unit_id, $creation_mode) {
				foreach($times as $time) {
						$hour = (int)$time['timeFrom']['hour'];
						$minute = (int)$time['timeFrom']['minute'];
						$hour_to = (int)$time['timeTo']['hour'];
						$minute_to = (int)$time['timeTo']['minute'];
						
						do {
								//insert..
								for($i = 0; $i < $appointment_count; $i++) {
										//echo 'Füge neuen dingens ein am: ' . $day . '.' . $month . '.' . $year . ' ' . $hour . ':' . $minute . "\n";
										$this->insertAppointmentByMode($day, $month, $year, $hour, $minute, $appointment_duration_in_minutes, $event_location_id, $user_unit_id, $creation_mode);
								}
								
								$minute += (int)($appointment_duration_in_minutes);
								
								if($minute > 59) {
										//Das ist etwas umständlich, aber so bekommen wir auch Zeitspannen hin, die länger als 1 Stunde gehen.
										$add_hours = (int)($minute / 60);
										$minute = (int)($minute % 60);	//Rest ausrechnen..
										//$minute -= (int)60;
										$hour += (int)($add_hours);
								}
								
								if($hour == $hour_to && $minute >= $minute_to) {
										break;
								}
						} while($hour < $hour_to);
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Modi prüfen, auswerten und je nach Modi Termin einfügen.
		///////////////////////////////////////////////////////////////////
		private function insertAppointmentByMode($day, $month, $year, $hour, $minute, $appointment_duration_in_minutes, $event_location_id, $user_unit_id, $creation_mode) {
				if($creation_mode == 'all') {
						//Laufe durch alle user-units..
						foreach($this->user_units as $uu) {
								$user_unit_id = $uu['id'];
								$event_location_id = $uu['event_location_id'];
								
								$this->insertAppointment($day, $month, $year, $hour, $minute, $appointment_duration_in_minutes, $event_location_id, $user_unit_id);
						}
				} else if($creation_mode == 'user_unit') {
						//Erstelle nur für die gewählte user-unit (event location mit eintragen!)
						$event_location_id = 0;
						
						foreach($this->user_units as $uu) {
								if($uu['id'] == $user_unit_id) {
										$event_location_id = $uu['event_location_id'];
										break;
								}
						}
						
						if($event_location_id == 0) {
								return;
						}
						
						$this->insertAppointment($day, $month, $year, $hour, $minute, $appointment_duration_in_minutes, $event_location_id, $user_unit_id);
				} else if($creation_mode == 'event_location') {
						//Erstelle für event-location
						foreach($this->user_units as $uu) {
								if($uu['event_location_id'] == $event_location_id) {
										$user_unit_id = $uu['id'];
										
										$this->insertAppointment($day, $month, $year, $hour, $minute, $appointment_duration_in_minutes, $event_location_id, $user_unit_id);
								}
						}
				}
		}
				
		///////////////////////////////////////////////////////////////////
		// Termin einfügen.
		///////////////////////////////////////////////////////////////////
		private function insertAppointment($day, $month, $year, $hour, $minute, $appointment_duration_in_minutes, $event_location_id, $user_unit_id) {
				$day = str_pad($day, 2, '0', STR_PAD_LEFT);
				$month = str_pad($month, 2, '0', STR_PAD_LEFT);
				$hour = str_pad($hour, 2, '0', STR_PAD_LEFT);
				$minute = str_pad($minute, 2, '0', STR_PAD_LEFT);
				
				$time_string = $year . '-' . $month . '-' . $day . ' ' . $hour . ':' . $minute . ':00';
				
				//Daten für Datenbank-Query zusammenstellen.
				$data = array(
						'title' => 'Generated by adminCcConfig',
						'datetime_of_event' => $time_string,
						'event_location_id' => $event_location_id,
						'user_unit_id' => $user_unit_id,
						'status' => 1,
						'created_by' => $_SESSION['user_id'],
						'datetime_checkin' => '0000-00-00 00:00',
						'datetime_checkout' => '0000-00-00 00:00',
						'visitor_user_id' => 0,
						'checkin_by' => 0,
						'checkout_by' => 0,
						'comment_checkin' => "",
						'comment_checkout' => "",
						'comment_visitor_booking' => "",
						'reminder_user_mail' => "",
						'reminder_active' => 1,
						'reminder_user_mail_sent' => 0,
						'reminder_user_mail_sent_datetime' => '0000-00-00 00:00',
						'duration_in_minutes' => $appointment_duration_in_minutes,
						'firstname' => '',
						'lastname' => '',
						'email_address' => '',
						'customers_number' => '',
						'phone' => '',
						'street' => '',
						'street_number' => '',
						'plz' => '',
						'city' => '',
						'last_save_datetime' => date('Y-m-d H:i:s')
				);
				
				$appointment = new cAppointment();
				$appointment->createInDb($data);
		}
		
		///////////////////////////////////////////////////////////////////
		// Delete an entry.
		///////////////////////////////////////////////////////////////////
		private function ajaxCreateBetriebsstaette() {
				$title = core()->getPostVar('title');								//Get title
				$description = core()->getPostVar('description');		//Get description
				$user_id = (int)$_SESSION['user_id'];								//Get user_id
				
				//insert
				$eventLocations = new cEventLocations();
				$id = $eventLocations->createInDB(
						array(
								'title' => $title,
								'description' => $description,
								'user_id' => $user_id
						)
				);
				
				//Load list of all for user..
				$iEventLocations = new cEventLocations();
				$event_locations = $iEventLocations->loadListByUserId($user_id);
				
				//build output like this and convert to json
				$retval = array(
						'status' => "success",
						'data' => array(
								'id' => $id,
								'event_locations' => $event_locations
						)
				);
				$retval = json_encode($retval, JSON_PRETTY_PRINT);
				echo $retval;
				die;
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
				$date_from = date('d.m.Y');
				$date_to = '31.12.' . date('Y');
				
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('DATE_FROM', $date_from);
				$renderer->assign('DATE_TO', $date_to);
				$renderer->assign('TEMPLATE_URL', cCMS::loadTemplateUrl(core()->get('site_id')));
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$renderer->assign('EVENT_LOCATIONS', $this->event_locations);
				$renderer->assign('USER_UNITS', $this->user_units);
				$renderer->render('site/adminCcConfig/editor.html');
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
										'position' => 100,
										'title' => 'Konfigurator'
								)
						);
		        $cAdmin->registerAdminMenuEntry($admin_menu_entry_path, 'index.php?s=cAdminCcConfig');
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
				"\n" . '<script src="data/templates/' . $this->template . '/js/admin_cc_config.js"></script>' .
				"\n" . '<script src="data/templates/' . $this->template . '/js/admin_cc_config_check.js"></script>' .
				"\n" . '<script src="data/templates/' . $this->template . '/js/admin_cc_config_upload.js"></script>' .
				"\n";
			$renderer = core()->getInstance('cRenderer');
			$renderer->renderText($additional_output);
		}
}
?>