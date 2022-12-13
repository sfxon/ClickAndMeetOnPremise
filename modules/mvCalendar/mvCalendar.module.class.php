<?php

class mvCalendar extends cModule  {
		var $show_appointments_in_past = false;
		
		///////////////////////////////////////////////////////////////////
		// Standard-Daten laden.
		///////////////////////////////////////////////////////////////////
		public function loadDefaultData($day, $month, $year) {
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
				
				$data['date'] = $data;
				
				return $data;
		}
		
		///////////////////////////////////////////////////////////////////
		// Lade den Monat
		///////////////////////////////////////////////////////////////////
		public function loadMonth($month, $year) {
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
								'weeknumber' => date('W', $tmp_time),
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
		// Draw the calendar container.
		///////////////////////////////////////////////////////////////////
		function drawCalendarContainer($data) {
				$this->template = 'maxis';
				
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
				
				$iMvBookingFormTexts = new cMvBookingFormTexts();
				$calendar_texts = $iMvBookingFormTexts->loadIndexedList();
						
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $data);
				$renderer->assign('TEMPLATE_URL', cCMS::loadTemplateUrl(core()->get('site_id')));
				$renderer->assign('MV_CALENDAR_TEXTS', $calendar_texts);
				$renderer->assign('EVENT_LOCATIONS', $event_locations);
				$renderer->assign('USER_UNITS', $user_units);
				$renderer->assign('CM_APPOINTMENT_STATUS', $appointment_status);
				$renderer->assign('CM_SETTINGS', $cm_settings);
				return $renderer->fetch('site/mvCalendar/calendar-container.html');
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the calendar container.
		///////////////////////////////////////////////////////////////////
		function drawTimerContainer($data) {
				$this->template = 'maxis';
				
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
				
				$iMvBookingFormTexts = new cMvBookingFormTexts();
				$calendar_texts = $iMvBookingFormTexts->loadIndexedList();
						
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $data);
				$renderer->assign('TEMPLATE_URL', cCMS::loadTemplateUrl(core()->get('site_id')));
				$renderer->assign('EVENT_LOCATIONS', $event_locations);
				$renderer->assign('USER_UNITS', $user_units);
				$renderer->assign('CM_APPOINTMENT_STATUS', $appointment_status);
				$renderer->assign('CM_SETTINGS', $cm_settings);
				$renderer->assign('MV_CALENDAR_TEXTS', $calendar_texts);
				return $renderer->fetch('site/mvCalendar/timer-container.html');
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the event-location container.
		///////////////////////////////////////////////////////////////////
		function drawEventLocationContainer($data) {
				$this->template = 'maxis';
				
				$iEventLocations = new cEventLocations();
				$event_locations = $iEventLocations->loadList();
				$ev_indexed = $this->getEventLocationsIndexed($event_locations);
				
				$iCmSettings = new cCmSettings();
				$cm_settings = $iCmSettings->loadSettingsIndexed();
				
				$iMvBookingFormTexts = new cMvBookingFormTexts();
				$calendar_texts = $iMvBookingFormTexts->loadIndexedList();
					
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $data);
				$renderer->assign('TEMPLATE_URL', cCMS::loadTemplateUrl(core()->get('site_id')));
				$renderer->assign('EVENT_LOCATIONS', $event_locations);
				$renderer->assign('CM_SETTINGS', $cm_settings);
				$renderer->assign('MV_CALENDAR_TEXTS', $calendar_texts);
				return $renderer->fetch('site/mvCalendar/event-locations-container.html');
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the user-units container
		///////////////////////////////////////////////////////////////////
		function drawUserUnitsContainer($data) {
				$this->template = 'maxis';
				
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
				
				$iMvBookingFormTexts = new cMvBookingFormTexts();
				$calendar_texts = $iMvBookingFormTexts->loadIndexedList();
				
				$iCmSettings = new cCmSettings();
				$cm_settings = $iCmSettings->loadSettingsIndexed();
					
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $data);
				$renderer->assign('TEMPLATE_URL', cCMS::loadTemplateUrl(core()->get('site_id')));
				$renderer->assign('EVENT_LOCATIONS', $event_locations);
				$renderer->assign('USER_UNITS', $user_units);
				$renderer->assign('CM_SETTINGS', $cm_settings);
				$renderer->assign('MV_CALENDAR_TEXTS', $calendar_texts);
				return $renderer->fetch('site/mvCalendar/user-units-container.html');
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
		
		///////////////////////////////////////////////////////////////////
		// Monatsdaten laden.
		///////////////////////////////////////////////////////////////////
		public function loadMonthCalender($event_location_id, $user_unit, $day, $month, $year) {
				$template = 'maxis';
				
				$month_data = $this->loadMonth($month, $year);
				
				//Termin-Anzahl ermitteln.
				$iAppointment = new cAppointment();
				$appointent_count = array();
				
				if($event_location_id == 0 && $user_unit == 0) {
						$appointment_count = $iAppointment->countAppointmentsByDaysAndStatus(
								$month_data['first_day_sql'] . " 00:00:00",
								$month_data['last_day_sql'] . " 23:59:59",
								$status = 1
						);
				} else if($user_unit != 0) {
						$appointment_count = $iAppointment->countAppointmentsByDaysAndUserUnitAndStatus(
								$user_unit,
								$month_data['first_day_sql'] . " 00:00:00",
								$month_data['last_day_sql'] . " 23:59:59",
								$status = 1
						);
				} else if($event_location_id != 0) {
						$appointment_count = $iAppointment->countAppointmentsByDaysAndEventLocationAndStatus(
								$event_location_id,
								$month_data['first_day_sql'] . " 00:00:00",
								$month_data['last_day_sql'] . " 23:59:59",
								$status = 1
						);
				}
				
				foreach($appointment_count as $ap) {
						//Prüfen, ob das Datum vor dem heutigen Tag liegt. Falls ja, überspringen wir diesen Eintrag.
						if($this->show_appointments_in_past == false) {		//Es gibt Fälle, da wollen wir alle Einträge. Deshalb kann man das Klassenweit einstellen.
								$tmp_date = $this->buildSqlStringDateFromInts($ap['day'], $month, $year);
								$tmp_date = strtotime($tmp_date);
								$tmp_date_compare = strtotime(date('Y-m-d'));
								
								if($tmp_date < $tmp_date_compare) {
										continue;
								}
						}
						
						//Die Termin-Anzahl zum richtigen Tag hinzufügen.
						foreach($month_data['days'] as $index => $md) {
								//Wenn der richtige Tag gefunden wurde -> Eintrag hinzufügen.
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
				
				$data['date'] = $data;
				
				$iMvBookingFormTexts = new cMvBookingFormTexts();
				$calendar_texts = $iMvBookingFormTexts->loadIndexedList();
				
				//Render month calender..
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($template);
				$renderer->assign('DATA', $data);
				$renderer->assign('TEMPLATE_URL', cCMS::loadTemplateUrl(core()->get('site_id')));
				$renderer->assign('MV_CALENDAR_TEXTS', $calendar_texts);
				$html = $renderer->fetch('site/mvCalendar/calendar.html');
				
				$retval = array(
						'month_data' => $data,
						'html' => $html
				);
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////
		// Kalendereinträge laden.
		///////////////////////////////////////////////////////////////////
		public function loadAppointments($event_location, $user_unit_id, $day, $month, $year) {
				$sql_date = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);
				$sql_date_from = $sql_date . ' 00:00:00';
				$sql_date_to = $sql_date . ' 23:59:59';
				
				$iAppointment = new cAppointment();
				$appointments = array();
				
				$status = 1;
				
				//Prüfen, ob das Datum vor dem heutigen Tag liegt. Falls ja, überspringen wir diesen Eintrag.
				$show_appointments = true;
				
				if($this->show_appointments_in_past == false) {		//Es gibt Fälle, da wollen wir alle Einträge. Deshalb kann man das Klassenweit einstellen.
						$tmp_date = $this->buildSqlStringDateFromInts($day, $month, $year);
						$tmp_date = strtotime($tmp_date);
						$tmp_date_compare = strtotime(date('Y-m-d'));
						
						if($tmp_date < $tmp_date_compare) {
								$show_appointments = false;;
						}
				}
				
				if($show_appointments) {
						if($user_unit_id == 0 && $event_location == 0) {
								$appointments = $iAppointment->loadListByDateRangeAndStatus($sql_date_from, $sql_date_to, $status);
						} else if($user_unit_id != 0) {
								$appointments = $iAppointment->loadListByDateRangeAndStatusAndUserUnitId($user_unit_id, $sql_date_from, $sql_date_to, $status);
						} else if($event_location != 0) {
								$appointments = $iAppointment->loadListByDateRangeAndStatusAndEventLocation($event_location, $sql_date_from, $sql_date_to, $status);
						}
						
						//Add more information to the list: eventLocation
						$iEventLocations = new cEventLocations();
						$event_location_list = $iEventLocations->loadIndexedList();
						
						$iUserUnit = new cUserUnit();
						$user_unit_list = $iUserUnit->loadIndexedList();
						
						$iCmAppointmentStatus = new cCmAppointmentStatus();
						$cm_appointment_status = $iCmAppointmentStatus->loadIndexedList();
						
						foreach($appointments as $index => $app) {
								if(is_array($event_location_list) && isset($event_location_list[$app['event_location_id']])) {
										$appointments[$index]['event_location'] = $event_location_list[$app['event_location_id']]['title'];
								} else {
										$appointments[$index]['event_location'] = "";
								}
								
								if(is_array($cm_appointment_status) && isset($cm_appointment_status[$app['status']])) {
										$appointments[$index]['event_status'] = $cm_appointment_status[$app['status']]['title'];
								} else {
										$appointments[$index]['event_status'] = "";
								}
								
								if(is_array($user_unit_list) && isset($user_unit_list[$app['user_unit_id']])) {
										$appointments[$index]['user_unit'] = $user_unit_list[$app['user_unit_id']]['title'];
								} else {
										$appointments[$index]['user_unit'] = "";
								}
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
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////
		// SQL-Style Datum aus Integer Datum zusammenbauen.
		///////////////////////////////////////////////////////////////////
		public function buildSqlStringDateFromInts($day, $month, $year) {
				return $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);
		}
}