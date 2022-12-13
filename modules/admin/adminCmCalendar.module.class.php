<?php

class cAdminCmCalendar extends cModule {
		var $template = 'admin';
		var $navbar_title = TEXT_MODULE_TITLE_ADMIN_CM_CALENDAR;
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
				if(false === cAccount::adminrightCheck('cAdminCmCalendar', 'USE_MODULE', (int)$_SESSION['user_id'])) {
						header('Location: index.php?s=cAdmin&error=1001');
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
				$cAdmin->appendBreadcrumb(TEXT_MODULE_TITLE_ADMIN_CM_CALENDAR, 'index.php?s=cAdminCmCalendar');
				
				switch($this->action) {
						case 'ajaxLoadMonth':
								$this->ajaxLoadMonth();
								die;
						case 'ajaxLoadAppointments':
								$this->ajaxLoadAppointments();
								die;
						case 'ajaxSaveAppointment':
								$this->ajaxSaveAppointment();
								die;
						case 'ajaxDeleteAppointment':
								$this->ajaxDeleteAppointment();
								die;
						case 'ajaxUpdateFilter':
								$this->ajaxUpdateFilter();
								die;
						case 'ajaxUpdateFilterStatus':
								$this->ajaxUpdateFilterStatus();
								die;
						case 'export':
								$this->export();
								die;
						default:
								$this->loadDefaultData();
								break;
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Export
		///////////////////////////////////////////////////////////////////
		private function export() {
				$date_from = core()->getPostVar('mv-export-date-from');
				$date_to = core()->getPostVar('mv-export-date-to');
				$export_format = core()->getPostVar('mv-export-format');
				
				$date_from = $this->convertDateFromGermanToSql($date_from);
				$date_to = $this->convertDateFromGermanToSql($date_to);
				
				$date_from .= ' 00:00:00';
				$date_to .= ' 23:59:59';
				
				switch($export_format) {
						case 'csv;':
								$this->exportCsvSemicolonSeparated($date_from, $date_to);
								break;
						default:
								die('Unbekanntes Export-Format');
				}
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Deutsches Datum in SQL Datum übersetzen
		///////////////////////////////////////////////////////////////////
		private function convertDateFromGermanToSql($date) {
				$parts = explode('.', $date);
				
				if(!is_array($parts) || count($parts) != 3) {
						die('Ungültiges Datum: ' . $date);
				}
				
				$day = ltrim($parts[0], '0');
				$month = ltrim($parts[1], '0');
				$year = $parts[2];
				
				if(false == checkdate($month, $day, $year)) {
						die('Ungültiges Datum: ' . $date);
				}
				
				$datetime = $this->buildDate($year, $month, $day);
				
				return $datetime;
		}
		
		///////////////////////////////////////////////////////////////////
		// CSV-Datei komma-separiert exportieren.
		///////////////////////////////////////////////////////////////////
		private function exportCsvSemicolonSeparated($date_from, $date_to) {
				//Build additional WHERE Parts for the Queries -> This adds filters for example.
				$sql_additional_where = $this->buildAdditionalSqlWhereByFilters();
				
				//Load appointments
				$iAppointment = new cAppointment();
				$appointments = array();
				
				//if($user_unit_id == 0 && $event_location == 0) {
						$appointments = $iAppointment->adminLoadListByDateRange($date_from, $date_to, $sql_additional_where);
				//}
				
				//Export-Format definieren (Auch schon in Vorbereitung auf zukünftige Exports..
				$export_format = 'id;datetime_of_event;event_location_id;status;created_by;';
				$export_format .= 'datetime_checkin;datetime_checkout;visitor_user_id;checkin_by;';
				$export_format .= 'checkout_by;comment_checkin;comment_checkout;comment_visitor_booking;';
				$export_format .= 'reminder_user_mail;reminder_active;reminder_user_mail_sent;reminder_user_mail_sent_datetime;';
				$export_format .= 'duration_in_minutes;firstname;lastname;email_address;customers_number;';
				$export_format .= 'phone;street_number;plz;city;street;user_unit_id;last_save_datetime;custom_form_dropdown';
				
				$export_format_parts = explode(';', $export_format);
				
				if(!is_array($export_format_parts) || count($export_format_parts) < 1) {
						die('Ungültiges Export-Format');
				}
				
				$part_count = count($export_format_parts);
				
				//Output some headers, so the file is offered for download, instead for opening in browser
				header('Content-Type: application/csv');
				header('Content-Disposition: attachment; filename=clickandmeet-export-' . date('Y-m-d_H-i-s') . '-' . rand() . '.csv');
				header('Pragma: no-cache');
				
				echo $export_format . "\n";
				
				//Start output.
				foreach($appointments as $ap) {
						$exported_fields_in_row = 0;
						
						foreach($export_format_parts as $index => $part) {
								if(isset($ap[$part])) {
										if($exported_fields_in_row > 0) {
												echo ';';
										}
										
										$data = $ap[$part];
										
										//Escape SQL Delimiters
										$data = str_replace(';', '\;', $data);
										
										//Escape SQL Quotes.
										$data = str_replace('"', '\"', $data);
										
										//Output.
										echo '"' . $data . '"';
										
										$exported_fields_in_row++;
								}
						}
						
						echo "\n";
				}
				
				die;	
		}
		
		///////////////////////////////////////////////////////////////////
		// Filter aktualisieren
		///////////////////////////////////////////////////////////////////
		private function ajaxUpdateFilter() {
				$new_status = core()->getPostVar('status');
				
				if($new_status == 'hidden') {
						setcookie('adminCmFilter_visibility', 'hidden', time()+31556926);	
				} else {
						setcookie('adminCmFilter_visibility', 'visible', time()+31556926);
				}
				
				$retval = array(
						'status' => 'success',
						'data' => array(
								'new_status' => $new_status
						)
				);
				$retval = json_encode($retval);
				echo $retval;
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Filter aktualisieren
		///////////////////////////////////////////////////////////////////
		private function ajaxUpdateFilterStatus() {
				$status_id = (int)core()->getPostVar('status_id');
				$click_state = core()->getPostVar('click_state');
				
				//Load the array
				$adminCmFilter_status = false;
				
				if(isset($_COOKIE['adminCmFilter_status'])) {
						$adminCmFilter_status = unserialize(base64_decode($_COOKIE['adminCmFilter_status']));
				}
				
				if(false === $adminCmFilter_status || !is_array($adminCmFilter_status)) {
						$adminCmFilter_status = array();
				}
				
				//Set the value.
				//Check, if we should set or unset the state..
				if($click_state == 'checked') {
						$adminCmFilter_status[(string)$status_id] = 'checked';
				} else {
						unset($adminCmFilter_status[(string)$status_id]);
				}
				
				//Prepare the array for saving.
				$adminCmFilter_status = base64_encode(serialize($adminCmFilter_status));
				
				
				
				//Save the value in the cookie
				setcookie('adminCmFilter_status', $adminCmFilter_status, time()+31556926);	
				
				$retval = array(
						'status' => 'success',
						'data' => array(
								'action' => 'saved'
						)
				);
				$retval = json_encode($retval);
				echo $retval;
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Kalendereinträge laden.
		///////////////////////////////////////////////////////////////////
		private function ajaxLoadAppointments() {
				$event_location = (int)core()->getPostVar('event_location');
				$user_unit_id = (int)core()->getPostVar('user_unit_id');
				$day = (int)core()->getPostVar('day');
				$month = (int)core()->getPostVar('month');
				$year = (int)core()->getPostVar('year');
				
				$sql_date = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);
				$sql_date_from = $sql_date . ' 00:00:00';
				$sql_date_to = $sql_date . ' 23:59:59';
				
				$user_id = (int)$_SESSION['user_id'];
				
				$iAppointment = new cAppointment();
				$appointments = array();
				
				//Build additional WHERE Parts for the Queries.
				$sql_additional_where = $this->buildAdditionalSqlWhereByFilters();
				
				if($user_unit_id == 0 && $event_location == 0) {
						$appointments = $iAppointment->adminLoadListByDateRange($sql_date_from, $sql_date_to, $sql_additional_where);
				} else if($user_unit_id != 0) {
						$appointments = $iAppointment->adminLoadListByDateRangeAndUserUnitId($user_unit_id, $sql_date_from, $sql_date_to, $sql_additional_where);
				} else if($event_location != 0) {
						$appointments = $iAppointment->adminLoadListByDateRangeAndEventLocation($event_location, $sql_date_from, $sql_date_to, $sql_additional_where);
				}
				
				//Add more information to the list: eventLocation
				$iEventLocations = new cEventLocations();
				$event_location_list = $iEventLocations->loadIndexedList();
				
				$iUserUnit = new cUserUnit();
				$user_units = $iUserUnit->loadIndexedList();
				
				$iCmAppointmentStatus = new cCmAppointmentStatus();
				$cm_appointment_status = $iCmAppointmentStatus->loadIndexedList();
				
				foreach($appointments as $index => $app) {
						if(is_array($event_location_list) && isset($event_location_list[$app['event_location_id']])) {
								$appointments[$index]['event_location'] = $event_location_list[$app['event_location_id']]['title'];
						} else {
								$appointments[$index]['event_location'] = "";
						}
						
						if(is_array($user_units) && isset($user_units[$app['user_unit_id']])) {
								$appointments[$index]['user_unit'] = $user_units[$app['user_unit_id']]['title'];
						} else {
								$appointments[$index]['user_unit'] = "***";
						}
						
						if(is_array($cm_appointment_status) && isset($cm_appointment_status[$app['status']])) {
								$appointments[$index]['event_status'] = $cm_appointment_status[$app['status']]['title'];
						} else {
								$appointments[$index]['event_status'] = "";
						}
				}
				
				$retval = array(
						'status' => 'success',
						'data' => array(
								'iday' => $day,
								'imonth' => $month,
								'iyear' => $year,
								'day' => str_pad($day, 2, '0', STR_PAD_LEFT),
								'month' => str_pad($month, 2, '0', STR_PAD_LEFT),
								'year' => $year,
								'appointments' => $appointments
						)
				);
				$retval = json_encode($retval);
				echo $retval;
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Kalendereinträge laden.
		///////////////////////////////////////////////////////////////////
		public function buildAdditionalSqlWhereByFilters() {
				//Rückgabewert vorbereiten.
				$retval = array(
						'where' => '',
						'params' => ''
				);
			
				//1. Filter nur anwenden, wenn sie ausgeklappt sind.
				if(!isset($_COOKIE['adminCmFilter_visibility']) || $_COOKIE['adminCmFilter_visibility'] != 'visible') {
						return $retval;
				}
				
				//Gesetzt Termin-Status-Filter berechnen
				if(isset($_COOKIE['adminCmFilter_status'])) {
						$tmp_filter_status = unserialize(base64_decode($_COOKIE['adminCmFilter_status']));
						
						if(is_array($tmp_filter_status)) {
								$tmp_where = '';
								
								foreach($tmp_filter_status as $index => $value) {
										if($tmp_where == '') {
												$tmp_where .= 'AND (';
										} else {
												$tmp_where .= ' OR ';
										}
										
										$tmp_where .= 'status = ' . (int)$index;
								}
								
								if($tmp_where != '') {
										$tmp_where .= ') ';
										
										$retval['where'] = $tmp_where;
								}
						}
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////
		// Kalendereinträge laden.
		///////////////////////////////////////////////////////////////////
		private function ajaxSaveAppointment() {
				$appointment_id = core()->getPostVar('appointment_id');
				$day = (int)core()->getPostVar('day');
				$month = (int)core()->getPostVar('month');
				$year = (int)core()->getPostVar('year');
				$hour = (int)core()->getPostVar('hour');
				$minute = (int)core()->getPostVar('minute');
				$event_location_id = (int)core()->getPostVar('event_location_id');
				$user_unit_id = (int)core()->getPostVar('user_unit_id');
				$duration_in_minutes = (int)core()->getPostVar('duration_in_minutes');
				$status = (int)core()->getPostVar('status');
				$comment_visitor_booking = core()->getPostVar('comment_visitor_booking');
				
				$firstname = core()->getPostVar('firstname');
				$lastname = core()->getPostVar('lastname');
				$email_address = core()->getPostVar('email_address');
				$email_reminder = (int)core()->getPostVar('email_reminder');
				$customers_number = core()->getPostVar('customers_number');
				$phone = core()->getPostVar('phone');
				$street = core()->getPostVar('street');
				$plz = core()->getPostVar('plz');
				$city = core()->getPostVar('city');
				
				$checkin_date_day = (int)core()->getPostVar('checkin_date_day');
				$checkin_date_month = (int)core()->getPostVar('checkin_date_month');
				$checkin_date_year = (int)core()->getPostVar('checkin_date_year');
				$checkin_time_hour = (int)core()->getPostVar('checkin_time_hour');
				$checkin_time_minute = (int)core()->getPostVar('checkin_time_minute');
				
				$checkout_date_day = (int)core()->getPostVar('checkout_date_day');
				$checkout_date_month = (int)core()->getPostVar('checkout_date_month');
				$checkout_date_year = (int)core()->getPostVar('checkout_date_year');
				$checkout_time_hour = (int)core()->getPostVar('checkout_time_hour');
				$checkout_time_minute = (int)core()->getPostVar('checkout_time_minute');
				
				$checkin_comment = core()->getPostVar('checkin_comment');
				$checkout_comment = core()->getPostVar('checkout_comment');
				
				//Prüfe und erstelle Werte..
				$datetime = $this->buildDateTime($year, $month, $day, $hour, $minute);
				$datetime_checkin = $this->buildDateTime($checkin_date_year, $checkin_date_month, $checkin_date_day, $checkin_time_hour, $checkin_time_minute);
				$datetime_checkout = $this->buildDateTime($checkout_date_year, $checkout_date_month, $checkout_date_day, $checkout_time_hour, $checkout_time_minute);
				
				$last_save_datetime = core()->getPostVar('last_save_datetime');
				
				//Erstelle Datenstruktur für das Speichern in der Datenbank.
				//Daten für Datenbank-Query zusammenstellen.
				$data = array(
						'title' => '',
						'datetime_of_event' => $datetime,
						'event_location_id' => $event_location_id,
						'user_unit_id' => $user_unit_id,
						'status' => $status,
						'created_by' => $_SESSION['user_id'],
						'datetime_checkin' => $datetime_checkin,
						'datetime_checkout' => $datetime_checkout,
						'visitor_user_id' => 0,
						'checkin_by' => 0,
						'checkout_by' => 0,
						'comment_checkin' => $checkin_comment,
						'comment_checkout' => $checkout_comment,
						'comment_visitor_booking' => $comment_visitor_booking,
						'reminder_user_mail' => $email_address,
						'reminder_active' => $email_reminder,
						'reminder_user_mail_sent' => 0,
						'reminder_user_mail_sent_datetime' => '0000-00-00 00:00',
						'duration_in_minutes' => $duration_in_minutes,
						'firstname' => $firstname,
						'lastname' => $lastname,
						'email_address' => $email_address,
						'customers_number' => $customers_number,
						'phone' => $phone,
						'street' => $street,
						'street_number' => '',
						'plz' => $plz,
						'city' => $city,
						'last_save_datetime' => $last_save_datetime
				);
				
				if($appointment_id == "") {
						$this->createAppointment($data);
				} else {
						$this->updateAppointment($appointment_id, $data);
				}
				
				$retval = array(
						'status' => 'success',
						'data' => array(
								'base_url' => cCMS::loadTemplateUrl(core()->get('site_id')),
								'day' => $day,
								'month' => $month,
								'year' => $year
						)
				);
				$retval = json_encode($retval);
				echo $retval;
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Kalendereinträge laden.
		///////////////////////////////////////////////////////////////////
		private function ajaxDeleteAppointment() {
				$appointment_id = core()->getPostVar('appointment_id');
				
				if($appointment_id != "") {
						$appointment = new cAppointment();
						$appointment->deleteById($appointment_id);
				}
				
				$retval = array(
						'status' => 'success',
						'data' => array(
						)
				);
				$retval = json_encode($retval);
				echo $retval;
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Termin erstellen.
		///////////////////////////////////////////////////////////////////
		private function createAppointment($data) {
				$data['last_save_datetime'] = date('Y-m-d H:i:s');
				
				$appointment = new cAppointment();
				$appointment->createInDb($data);
		}
		
		///////////////////////////////////////////////////////////////////
		// Termin aktualisieren.
		///////////////////////////////////////////////////////////////////
		private function updateAppointment($id, $data) {
				$appointment = new cAppointment();
				
				//Try to load the appointment.
				$server_data = $appointment->loadById($id);
				
				if(false == $server_data) {
						$retval = array(
								'status' => 'error',
								'error' => 'unknown_appointment ' . print_r($id, true)
						);
						
						$retval = json_encode($retval);
						echo $retval;
						die;
				}
				
				if($server_data['last_save_datetime'] != $data['last_save_datetime']) {
						$retval = array(
								'status' => 'error',
								'error' => 'last_save_datetime_mismatch'
						);
						
						$retval = json_encode($retval);
						echo $retval;
						die;
				}
				
				$data['last_save_datetime'] = date('Y-m-d H:i:s');
				
				$appointment->updateInDB($id, $data);
		}
		
		///////////////////////////////////////////////////////////////////
		// SQL Datums-String aus Einzelwerten erstellen.
		///////////////////////////////////////////////////////////////////
		private function buildDateTime($year, $month, $day, $hour, $minute) {
				$sql_date_string = 
						str_pad($year, 4, "0", STR_PAD_LEFT) . '-' . 
						str_pad($month, 2, "0", STR_PAD_LEFT) . '-' . 
						str_pad($day, 2, "0", STR_PAD_LEFT) . ' ' .
						str_pad($hour, 2, "0", STR_PAD_LEFT) . ':' .
						str_pad($minute, 2, "0", STR_PAD_LEFT);
						
				return $sql_date_string;
		}
		
		///////////////////////////////////////////////////////////////////
		// Monatsdaten laden.
		///////////////////////////////////////////////////////////////////
		private function ajaxLoadMonth() {
				$event_location = (int)core()->getPostVar('event_location');
				$user_unit = (int)core()->getPostVar('user_unit');				
				$day = 1;
				$month = (int)core()->getPostVar('month');
				$year = (int)core()->getPostVar('year');
				
				$month_data = $this->loadMonth($month, $year);
				
				//Termin-Anzahl ermitteln.
				$iAppointment = new cAppointment();
				$appointent_count = array();
				
				if($event_location == 0 && $user_unit == 0) {
						$appointment_count = $iAppointment->countAppointmentsByDays(
								$month_data['first_day_sql'] . " 00:00:00",
								$month_data['last_day_sql'] . " 23:59:59"
						);
				} else if($user_unit != 0) {
						$appointment_count = $iAppointment->countAppointmentsByDaysAndUserUnit(
								$user_unit,
								$month_data['first_day_sql'] . " 00:00:00",
								$month_data['last_day_sql'] . " 23:59:59"
						);
				} else if($event_location != 0) {
						$appointment_count = $iAppointment->countAppointmentsByDaysAndEventLocation(
								$event_location,
								$month_data['first_day_sql'] . " 00:00:00",
								$month_data['last_day_sql'] . " 23:59:59"
						);
				}
				
				foreach($appointment_count as $ap) {
						foreach($month_data['days'] as $index => $md) {
								if((int)$ap['day'] == (int)$md['day']) {
										$month_data['days'][$index]['status_count'][] = $ap;
										break;
								}
						}
				}
				
				//Termin-Anzahl Klassen zusammensetzen:
				foreach($month_data['days'] as $index => $md) {
						$classes = '';
						
						if(isset($md['status_count'])) {
								foreach($md['status_count'] as $status) {
										$classes .= " mv-status-count-" . $status['status'];
								}
						}
						
						if(strlen($classes) > 0) {
								$month_data['days'][$index]['status_count_classes'] = $classes;
						}
				}		
				
				//Daten zusammenstellen.
				$data = array(
						'today_day' => date('j'),		// Tag des Monats ohne führende Nullen 	1 bis 31
						'today_month' => date('n'), // Monatszahl, ohne führende Nullen
						'today_year' => date('Y'),
						'current_day' => $day,
						'current_month' => $month,
						'current_year' => $year,
						'month_data' => $month_data
				);
				
				$this->data['date'] = $data;
				
				//Render month calender..
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('TEMPLATE_URL', cCMS::loadTemplateUrl(core()->get('site_id')));
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$html = $renderer->fetch('site/adminCmCalendar/calendar.html');
				
				$retval = array(
						'status' => 'success',
						'data' => array(
								'month_data' => $data,
								'html' => $html
						)
				);
				$retval = json_encode($retval);
				echo $retval;
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Standard-Daten laden.
		///////////////////////////////////////////////////////////////////
		private function loadDefaultData() {
				$day = date('d');
				$month = date('m');
				$year = date('Y');
				
				$iday = ltrim($day, '0');
				$imonth = ltrim($month, '0');
				$iyear = $year;
				
				//Lade Monat..
				$month = $this->loadMonth($imonth, $iyear);
				
				$data = array(
						'today_day' => date('j'),		// Tag des Monats ohne führende Nullen 	1 bis 31
						'today_month' => date('n'), // Monatszahl, ohne führende Nullen
						'today_year' => date('Y'),
						'current_day' => $iday,
						'current_month' => $imonth,
						'current_year' => $iyear,
						'month_data' => $month
				);
				
				$this->data['date'] = $data;
		}
		
		///////////////////////////////////////////////////////////////////
		// Lade den Monat
		///////////////////////////////////////////////////////////////////
		private function loadMonth($month, $year) {
				$days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year); // 31
				
				$days = array();
				
				for($i = 1; $i <= $days_in_month; $i++) {
						$sql_date = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($i, 2, '0', STR_PAD_LEFT);
						
						$tmp_time = strtotime($sql_date);
						
						
						$days[] = array(
								'day' => $i,
								'month' => $month,
								'year' => $year,
								'weekday' => date('N', $tmp_time),
								'weeknumber' => date('W', $tmp_time)
						);
				}
				
				$retval = array(
						'month' => $month,
						'year' => $year,
						'days_in_month' => $days_in_month,
						'days' => $days,
						'first_day_sql' => $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . str_pad("1", 2, '0', STR_PAD_LEFT),
						'last_day_sql' => $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($days_in_month, 2, '0', STR_PAD_LEFT)
				);
				
				//calculate weeks
				//$pattern = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-';
				
				$cal_week_start = date('W', strtotime($retval['first_day_sql']));
				$cal_week_end = date('W', strtotime($retval['last_day_sql']));
				
				$weeks_in_month = ($cal_week_end - $cal_week_start) + 1;
				
				$retval['cal_week_start'] = $cal_week_start;
				$retval['cal_week_end'] = $cal_week_end;
				$retval['weeks_in_month'] = $weeks_in_month;
				
				return $retval;
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
				$event_locations = $iEventLocations->loadList();
				$ev_indexed = $this->getEventLocationsIndexed($event_locations);
				
				$iUserUnit = new cUserUnit();
				$user_units = $iUserUnit->loadList();
				
				foreach($user_units as $index => $uu) {
						if(isset($ev_indexed[$uu['event_location_id']])) {
								$user_units[$index]['title_long'] = $uu['title'] . ' - ' . $ev_indexed[$uu['event_location_id']]['title'];
						} else {
								$user_units[$index]['title_long'] = $uu['title'] . ' - Nicht zugeordnet';
						}
				}
				
				$iCmAppointmentStatus = new cCmAppointmentStatus();
				$appointment_status = $iCmAppointmentStatus->loadList();
				
				$iCmSettings = new cCmSettings();
				$cm_settings = $iCmSettings->loadSettingsIndexed();
				
				// Filter Erweiterung
				$tmp_view_state = ' style="display: none;"';
        $tmp_filter_button_text = '<span class="dashicons dashicons-arrow-down"></span>Filter anzeigen';
        $tmp_filter_status = array();
						
				if(isset($_COOKIE['adminCmFilter_visibility'])) {
						if($_COOKIE['adminCmFilter_visibility'] == 'visible') {
								$tmp_view_state = '';
								$tmp_filter_button_text = '<span class="dashicons dashicons-arrow-up"></span>Filter verstecken';
						}
				}
						
				//Gesetzt Termin-Status-Filter berechnen
				if(isset($_COOKIE['adminCmFilter_status'])) {
						$tmp_filter_status = unserialize(base64_decode($_COOKIE['adminCmFilter_status']));
						
						if(!is_array($tmp_filter_status)) {
								$tmp_filter_status = array();
						}
				}
                                
        // Template rendern		
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('TEMPLATE_URL', cCMS::loadTemplateUrl(core()->get('site_id')));
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$renderer->assign('EVENT_LOCATIONS', $event_locations);
				$renderer->assign('USER_UNITS', $user_units);
				$renderer->assign('CM_APPOINTMENT_STATUS', $appointment_status);
				$renderer->assign('CM_SETTINGS', $cm_settings);
				
				$renderer->assign('FILTER_BUTTON_TEXT', $tmp_filter_button_text);
				$renderer->assign('FILTER_VIEW_STATE', $tmp_view_state);
				$renderer->assign('FILTER_STATUS', $tmp_filter_status);
				
				$renderer->render('site/adminCmCalendar/editor.html');
		}
		
		//////////////////////////////////////////////////////////////////////////////////
		// Event-Locations indexiert laden.
		//////////////////////////////////////////////////////////////////////////////////
		private function getEventLocationsIndexed($event_locations) {
				$retval = array();
				
				foreach($event_locations as $el) {
						$retval[$el['id']] = $el;
				}
				
				return $retval;
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
										'position' => 10,
										'title' => 'Kalender'
								)
						);
		        $cAdmin->registerAdminMenuEntry($admin_menu_entry_path, 'index.php?s=cAdminCmCalendar');
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
						"\n" . '<script src="data/templates/' . $this->template . '/js/admin_cm_calendar.js"></script>' .
						"\n" . '<script src="data/templates/' . $this->template . '/js/admin_cm_timer.js"></script>' .
						"\n" . '<script src="data/templates/' . $this->template . '/js/moment.js"></script>' .
						"\n" . '<script src="data/templates/' . $this->template . '/js/dtsel.js"></script>' .
						"\n" . '<script src="data/templates/' . $this->template . '/js/jquery-clock-timepicker.js"></script>' .	//https://github.com/loebi-ch/jquery-clock-timepicker
						"\n" . '<script src="data/templates/' . $this->template . '/js/mvCmTimerLoad.js"></script>' .
						"\n" . '<script src="data/templates/' . $this->template . '/js/mvCmTimerSaveAppointment.js"></script>' .
						"\n" . '<script src="data/templates/' . $this->template . '/js/mvCmTimerDeleteAppointment.js"></script>' .
						
						"\n" . '<script src="data/templates/' . $this->template . '/js/adminCmFilter.js"></script>' .
						"\n" . '<script src="data/templates/' . $this->template . '/js/adminCmFilterSaver.js"></script>' .
						"\n" . '<script src="data/templates/' . $this->template . '/js/adminCmFilterStatus.js"></script>' .
						"\n" . '<script src="data/templates/' . $this->template . '/js/adminCmFilterStatusSaver.js"></script>' .
						
						"\n" . '<script src="data/templates/' . $this->template . '/js/mvUploadQueryBuilder.js"></script>' .
						
						"\n" . '<script src="data/templates/' . $this->template . '/js/adminCmExport.js"></script>' .
						
						"\n";
				$renderer = core()->getInstance('cRenderer');
				$renderer->renderText($additional_output);
		}
		
		///////////////////////////////////////////////////////////////////
		// SQL Datums-String aus Einzelwerten erstellen.
		///////////////////////////////////////////////////////////////////
		private function buildDate($year, $month, $day) {
				$sql_date_string = 
						str_pad($year, 4, "0", STR_PAD_LEFT) . '-' . 
						str_pad($month, 2, "0", STR_PAD_LEFT) . '-' . 
						str_pad($day, 2, "0", STR_PAD_LEFT);
						
				return $sql_date_string;
		}
}
?>