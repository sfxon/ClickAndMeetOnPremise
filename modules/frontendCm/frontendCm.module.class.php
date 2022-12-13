<?php
/////////////////////////////////////////////////////////////////////////////////////
// Click&Meet Modul fürs Frontend
/////////////////////////////////////////////////////////////////////////////////////
class cFrontendCm extends cModule {
		var $template = 'maxis';
		var $cms_id = 0;
		var $contentData = '';
		var $siteUrl = '';
		
		//////////////////////////////////////////////////////////////////////////////////
		// Hook us into the system
		//////////////////////////////////////////////////////////////////////////////////
		public static function setExecutionalHooks() {
				//We use the CMS module for output.
				cCMS::setExecutionalHooks();		
				
				$core = core();
				
				//Now set our own hooks below the CMS hooks.
				//core()->setHook('cCMS|init', 'init');
				core()->setHook('cCore|process', 'process');
				core()->setHook('cRenderer|content', 'content');
				core()->setHook('cRenderer|begin_page', 'beginPage');
				core()->setHook('cRenderer|end_page', 'footer');
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Verarbeitung..
		/////////////////////////////////////////////////////////////////////////////////
		public function process() {
				$action = core()->getGetVar('action');
				$this->errors = array();
				
				$this->initData();
				
				switch($action) {
						case 'ajaxLoadCalendar':
								$this->ajaxLoadCalendar();
								die;
						case 'ajaxLoadMonth':
								$this->ajaxLoadMonth();
								die;
						case 'ajaxLoadAppointments':
								$this->ajaxLoadAppointments();
								die;
						case 'ajaxChooseAppointment':
								$this->ajaxChooseAppointment();
								die;
				}
				
				$this->defaultProcess();
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Standard-Verarbeitung
		/////////////////////////////////////////////////////////////////////////////////
		public function defaultProcess() {
				$errormessage = '';
				
				//Output - if user is not doing action!				
				$cms = core()->getInstance('cCMS');
				$cms->setHtmlBodyClasses('login scrollable');
				$content = $cms->loadContentDataByKey('FRONTEND_LOGIN');
				
				//Set the site url. We need this for the form to have the right action url!
				$login_form_url = cSeourls::loadSeourlByQueryString('s=cFrontendLogin');
				$login_form_url = ltrim($login_form_url, '/');
				$login_form_url .= '?action=process';
				$login_form_url = '//' . cSite::loadSiteUrl(core()->get('site_id')) . $login_form_url;
				
				//Farben und Texte aus Einstellungen laden.
				$iMvCalendarColors = new cMvCalendarColors();
				$calendar_colors = $iMvCalendarColors->loadIndexedList();
				
				$iMvBookingFormTexts = new cMvBookingFormTexts();
				$calendar_texts = $iMvBookingFormTexts->loadIndexedList();
				
				$iCmSettings = new cCmSettings();
				$cm_settings = $iCmSettings->loadIndexedList();

				//Load the CMS Entry for the login page.
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('TEMPLATE_URL', cCMS::loadTemplateUrl(core()->get('site_id')));
				$renderer->assign('TEMPLATE', $cms->getTemplate());
				$renderer->assign('LOGIN_FORM_URL', $login_form_url);
				$renderer->assign('ERRORMESSAGE', $errormessage);
				$renderer->assign('ERRORS', $this->errors);
				$renderer->assign('MV_CALENDAR_COLORS', $calendar_colors);
				$renderer->assign('MV_CALENDAR_TEXTS', $calendar_texts);
				$renderer->assign('CM_SETTINGS', $cm_settings);
				$tmp_content = $renderer->fetch('site/frontend_cm.html');
				
				$content['text'] = $tmp_content;
				$cms->setContentData($content);
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Monat laden.
		/////////////////////////////////////////////////////////////////////////////////
		public function ajaxLoadMonth() {
				$event_location_id = (int)core()->getPostVar('event_location');
				$user_unit = (int)core()->getPostVar('user_unit');
				$day = 1;
				$month = (int)core()->getPostVar('month');
				$year = (int)core()->getPostVar('year');
				
				$cal = new mvCalendar();
				$data = $cal->loadMonthCalender($event_location_id, $user_unit, $day, $month, $year);
			
				$retval = array(
						'status' => 'success',
						'data' => $data
				);
				$retval = json_encode($retval);
				echo $retval;
				die;
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Kalender-HTML laden.
		/////////////////////////////////////////////////////////////////////////////////
		public function ajaxLoadCalendar() {
				$day = (int)core()->getPostVar('day');
				$month = (int)core()->getPostVar('month');
				$year = (int)core()->getPostVar('year');
				
				//Aktuellen Monat laden, wenn Monat oder Jahr gleich 0 sind.
				if($month == 0 || $year == 0) {
						$day = date('j');			//Tag des Monats ohne führende Nullen
						$month = date('n');		//Monatszahl, ohne führende Nullen
						$year = date('Y');		//Jahr vierstellig
				}
				
				//Load calendar stuff..
				$cal = new mvCalendar();
				$cal_data = $cal->loadDefaultData($day, $month, $year);
				$cal_html = $cal->drawCalendarContainer($cal_data);
				$timer_html = $cal->drawTimerContainer($cal_data);
				$event_location_html = $cal->drawEventLocationContainer($cal_data);
				$user_unit_html = $cal->drawUserUnitsContainer($cal_data);
				
				$retval = array(
						'status' => 'success',
						'data' => array(
								'cal_data' => $cal_data,
								'calendar_html' => $cal_html,
								'timer_html' => $timer_html,
								'event_location_html' => $event_location_html,
								'user_unit_html' => $user_unit_html
						)
				);
				$retval = json_encode($retval, JSON_PRETTY_PRINT);
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
				
				$cal = new mvCalendar();
				$data = $cal->loadAppointments($event_location, $user_unit_id, $day, $month, $year);
				
				$retval = json_encode($data);
				echo $retval;
				die;
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Kunde möchte Termin wählen.
		/////////////////////////////////////////////////////////////////////////////////
		public function ajaxChooseAppointment() {
				$appointment_id = core()->getPostVar('appointment_id');
				$firstname = htmlspecialchars(core()->getPostVar('firstname'));
				$lastname = htmlspecialchars(core()->getPostVar('lastname')); 
				$email_address = htmlspecialchars(core()->getPostVar('email_address'));
				$email_reminder = (int)core()->getPostVar('email_reminder');
				$customers_number = htmlspecialchars(core()->getPostVar('customers_number'));
				$phone = htmlspecialchars(core()->getPostVar('phone'));
				$street = htmlspecialchars(core()->getPostVar('street'));
				$plz = htmlspecialchars(core()->getPostVar('plz'));
				$city = htmlspecialchars(core()->getPostVar('city'));
				$comment_visitor_booking = htmlspecialchars(core()->getPostVar('comment_visitor_booking'));
				
				//Check appointment_id
				$iAppointment = new cAppointment();
				$data = $iAppointment->loadById($appointment_id);
				
				if(false == $data) {
						$this->finishWithError('appointment_id');
						die;
				}
				
				if($data['status'] != 1) {
						$this->finishWithError('appointment_id');
						die;
				}
				
				//Check if appointment is in the past..
				$tmp_time = strtotime($data['datetime_of_event']);
				$tmp_time = date('Y-m-d', $tmp_time);
				$tmp_time_compare = date('Y-m-d');
				
				if($tmp_time < $tmp_time_compare) {
						$this->finishWithError('appointment_id');
						die;
				}
				
				//Check firstname
				if(strlen($firstname) < 2) {
						$this->finishWithError('firstname');
						die;
				}

				//Check lastname
				if(strlen($lastname) < 2) {
						$this->finishWithError('lastname');
						die;
				}

				//Check email_address
				if(strlen($email_address) < 5) {
						$this->finishWithError('email_address');
						die;
				}

				//Prepare data for saving.
				unset($data['id']);
				$data['comment_visitor_booking'] = $comment_visitor_booking;
				$data['reminder_active'] = $email_reminder;
				$data['firstname'] = $firstname;
				$data['lastname'] = $lastname;
				$data['email_address'] = $email_address;
				$data['customers_number'] = $customers_number;
				$data['phone'] = $phone;
				$data['street'] = $street;
				$data['plz'] = $plz;
				$data['city'] = $city;
				$data['status'] = 2;
				$data['last_save_datetime'] = date('Y-m-d H:i:s');
				
				$iAppointment->updateInDb($appointment_id, $data);
			
				$retval = array(
						'status' => 'success',
						'data' => array()
				);
				$retval = json_encode($retval);
				echo $retval;
				
				//Prepare E-Mail
				$this->sendMails($data);
				
				die;
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Mit Fehlermeldung beenden.
		/////////////////////////////////////////////////////////////////////////////////
		public function finishWithError($error) {
				$retval = array(
						'status' => 'error',
						'error' => $error
				);
				$retval = json_encode($retval, JSON_PRETTY_PRINT);
				echo $retval;
				die;
		}
		
		/////////////////////////////////////////////////////////////////////////////
		// Init all needed data.
		/////////////////////////////////////////////////////////////////////////////
		public function initData() {
				$this->data = array(
						
				);
		}
		
		
		/////////////////////////////////////////////////////////////////////////////////
		// E-Mail versenden.
		/////////////////////////////////////////////////////////////////////////////////
		public function sendMails($data) {
				$mail_to = $data['email_address'];
				
				$mail_id_plain = 1;
				$mail_id_html = 2;
				$mail_id_subject = 3;
				
				$mail_data = $data;
				
				//Datum und Zeit zusammenstellen.
				$mail_data['date'] = date('d.m.Y', strtotime($data['datetime_of_event']));
				$mail_data['time'] = date('H:i', strtotime($data['datetime_of_event']));
				
				//Event Location zusammenstellen.
				$iEventLocations = new cEventLocations();
				$event_location = $iEventLocations->loadById((int)$data['event_location_id']);
				$event_location_data = $event_location;
				
				if(false == $event_location) {
						$event_location = '';
				} else {
						$event_location = $event_location['title'];
				}
				
				$mail_data['betriebsstaette'] = $event_location;
				
				//Abteilung zusammenstellen:
				$iUserUnit = new cUserUnit();
				$user_unit = $iUserUnit->loadById((int)$data['user_unit_id']);
				$user_unit_data = $user_unit;
				
				if(false == $user_unit) {
						$user_unit = '';
				} else {
						$user_unit = $user_unit['title'];
				}
				
				$mail_data['abteilung'] = $event_location;
				
				//Kommentar:
				$mail_data['comment'] = $data['comment_visitor_booking'];
				
				//Ja/Nein: Erinnerungsmail
				if($data['reminder_active'] == 1) {
						$mail_data['reminder_yes_no'] = 'Ja';
				} else {
						$mail_data['reminder_yes_no'] = 'Nein';
				}
				
				$mailTextBuilder = new cMailTextBuilder($mail_id_plain, $mail_id_html, $mail_id_subject, $mail_data);
				
				$mailer = new mvPhpMailer();
				$mailer->init();
				
				$mailer->setFrom('mailfrom@mail...', 'Name');
				$mailer->addAddress($mail_to, $mail_to);
				$mailer->setSubject(utf8_decode($mailTextBuilder->mail_text_subject));
				$mailer->setPlainText(utf8_decode($mailTextBuilder->mail_text_plain));
				$mailer->setHTML(utf8_decode($mailTextBuilder->mail_text_html));
				$status = $mailer->send();
				
				//Prüfen, ob E-Mails an Betriebsstätte aktiviert sind:
				if(false != $event_location_data && $event_location_data['booking_info_by_mail'] == "1") {
						$mailTextBuilder = new cMailTextBuilder(6, 5, 4, $mail_data);
						
						$mailer = new mvPhpMailer();
						$mailer->init();
						
						$mailer->setFrom('mailfrom@mail...', 'Name');
						$mailer->addAddress($event_location_data['email_address'], $event_location_data['email_address']);
						$mailer->setSubject(utf8_decode($mailTextBuilder->mail_text_subject));
						$mailer->setPlainText(utf8_decode($mailTextBuilder->mail_text_plain));
						$mailer->setHTML(utf8_decode($mailTextBuilder->mail_text_html));
						$status = $mailer->send();
				}
				
				
				//Prüfen, ob E-Mails an Event-Location aktiviert sind:
				if(false != $user_unit_data && $user_unit_data['booking_info_by_mail'] == "1") {
						$mailTextBuilder = new cMailTextBuilder(8, 9, 7, $mail_data);
						
						$mailer = new mvPhpMailer();
						$mailer->init();
						
						$mailer->setFrom('mailfrom@mail...', 'Name');
						$mailer->addAddress($user_unit_data['email_address'], $user_unit_data['email_address']);
						$mailer->setSubject(utf8_decode($mailTextBuilder->mail_text_subject));
						$mailer->setPlainText(utf8_decode($mailTextBuilder->mail_text_plain));
						$mailer->setHTML(utf8_decode($mailTextBuilder->mail_text_html));
						$status = $mailer->send();
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Add code to the beginning of the page.
		// We use this to add custom css!
		///////////////////////////////////////////////////////////////////
		public function beginPage() {
				$additional_output = 	
						"\n" . '<link rel="stylesheet" href="//' . cSite::loadSiteUrl(core()->get('site_id')) . '/data/templates/' . $this->template . '/css/frontend_cm.css" />' .
						"\n" . '<link rel="stylesheet" href="//' . cSite::loadSiteUrl(core()->get('site_id')) . '/data/templates/' . $this->template . '/css/kalendar.css" />' .
						"\n";
				$renderer = core()->getInstance('cRenderer');
				$renderer->renderText($additional_output);
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Loads a content entry.
		/////////////////////////////////////////////////////////////////////////////////
		public function footer() {
				$additional_output = 
						"\n" . '<script src="data/templates/' . $this->template . '/js/mvForm.js"></script>' .
						"\n" . '<script src="data/templates/' . $this->template . '/js/mvFormSend.js"></script>' .
						"\n" . '<script src="data/templates/' . $this->template . '/js/mvCmCalLoadInitial.js"></script>' .
						"\n" . '<script src="data/templates/' . $this->template . '/js/mvUploadQueue.js"></script>' .
						"\n" . '<script src="data/templates/' . $this->template . '/js/mvTime.js"></script>' .
            "\n" . '<script src="data/templates/' . $this->template . '/js/mvDate.js"></script>' .
						"\n" . '<script src="data/templates/' . $this->template . '/js/cm_calendar.js"></script>' .
						"\n" . '<script src="data/templates/' . $this->template . '/js/cm_timer.js"></script>' .
						"\n" . '<script src="data/templates/' . $this->template . '/js/moment.js"></script>' .
						/*"\n" . '<script src="data/templates/' . $this->template . '/js/dtsel.js"></script>' .*/
						/*"\n" . '<script src="data/templates/' . $this->template . '/js/jquery-clock-timepicker.js"></script>' .	//https://github.com/loebi-ch/jquery-clock-timepicker*/
						"\n" . '<script src="data/templates/' . $this->template . '/js/mvCmTimerLoad.js"></script>' .
						
						/*"\n" . '<script src="data/templates/' . $this->template . '/js/mvCmTimerSaveAppointment.js"></script>' .*/
						"\n";
				$renderer = core()->getInstance('cRenderer');
				$renderer->renderText($additional_output);
		}
}
