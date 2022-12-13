<?php

/*
class cMvCal extends cModule {
		var $template = 'maxis';
		var $cms_id = 0;
		var $contentData = '';
		var $siteUrl = '';
		var $action = '';
		var $errors = array();
		var $curl_retval = '';
		var $required_fields = array();
		var $error_fields = array();
		var $data = array();
		
		//////////////////////////////////////////////////////////////////////////////////
		// Hook us into the system
		//////////////////////////////////////////////////////////////////////////////////
		public static function setExecutionalHooks() {
				$state = cWsseller::checkSellerStatus();		//Redirects to another page, if an error occures.
						
				if($state != 'loggedin') {
						die('Seller is not logged in');
				}
				
				//We use the CMS module for output.
				cCMS::setExecutionalHooks();		
				
				$core = core();
				
				//Now set our own hooks below the CMS hooks.
				//core()->setHook('cCMS|init', 'init');
				core()->setHook('cCore|process', 'process');
				core()->setHook('cCMS|footer', 'footer');
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Verarbeitung..
		/////////////////////////////////////////////////////////////////////////////////
		public function process() {
				$this->loadGeneralData();
				$errormessage = '';
				$action = core()->getGetVar('action');
				$this->action = $action;
								
				switch($this->action) {
						
						case 'ajax_load_date_and_times_html_with_container':
								$this->ajaxLoadDateAndTimesHTMLWithContainer();
								break;
						
						case 'ajax_load_year_only_html_with_container':
								$this->ajaxLoadYearOnlyHTMLWithContainer();
								break;
						
						case 'ajax_load_year_only_html':
								$this->ajaxLoadYearOnlyHTML();
								break;
						
						case 'ajax_get_kalender':
								$this->ajaxGetKalender();
								break;
						case 'ajax_small_kalender':
								$this->ajaxSmallKalender();
								break;
						default:
								break;
						
				}
				
				//$this->renderPage();
				$this->renderPage();		//Render default page as an example.
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Load some data that is always used.
		/////////////////////////////////////////////////////////////////////////////////
		private function loadGeneralData() {
				$iTime = new cTime();
				$this->data['data']['systemsettings'] = $this->getSystemsettings();
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Systemeinstellungen laden.
		/////////////////////////////////////////////////////////////////////////////////
		private function getSystemsettings() {
				$iSystemsettings = new cSystemsettings();
				return $iSystemsettings->loadListIndexed();
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Kalender und Jahr laden.
		/////////////////////////////////////////////////////////////////////////////////
		public function ajaxLoadYearOnlyHTML() {
				$month = date('m');
				$year = core()->getPostVar('year');
				$vergangene_daten_laden = (int)core()->getPostVar('vergangene_daten_laden');
				
				echo $this->fetchSmallKalender($month, $year, $vergangene_daten_laden);
				die;
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Kalender mit Jahresdaten laden.
		/////////////////////////////////////////////////////////////////////////////////
		public function ajaxLoadDateAndTimesHTMLWithContainer() {
				$month = date('m');
				$year = core()->getPostVar('year');
				$vergangene_daten_laden = (int)core()->getPostVar('vergangene_daten_laden');

				$kalenderblaetter = $this->getKalenderblaetter($month, $year, $vergangene_daten_laden);
				
				$site_url = cSeourls::loadSeourlByQueryString('s=cMerlinCustomer');
				$site_url = ltrim($site_url, '/');
				
				//If message is set -> set to template
				$message = core()->getGetVar('message');
				
				//Load the CMS Entry for the login page.
				$cms = core()->getInstance('cCMS');
				$content = $cms->loadContentDataByKey('SMARTSELLER_KALENDER');
				
				//Override cms with our html contents.
				$template_url = $cms->getTemplateUrl();
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('MONTH', $month);
				$renderer->assign('YEAR', $year);
				$renderer->assign('KALENDERBLAETTER', $kalenderblaetter);
				$renderer->assign('TEMPLATE_URL', $template_url);
				$renderer->assign('SITE_URL', $site_url);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('ERROR_FIELDS', $this->error_fields);
				$renderer->assign('MESSAGE', $message);
				$content['text'] = $renderer->fetch('site/mv-calendar/kalenderblaetter_mit_zeiten.html');

				//Load the container around the stuff.
				$renderer->assign('kalenderblaetter_html', $content['text']);
				
				//Stunden berechnen.
				$time_hours = array();
				
				for($i = 0; $i < 24; $i++) {
						$time_hours[] = str_pad($i, 2, '0', STR_PAD_LEFT);
				}
				
				//Viertelstunden berechnen.
				//$time_minutes = array('00', '15', '30', '45');
				
				//5-Minuten-Takt berechnen.
				$time_minutes = array('00', '05', '10', '15', '20', '25', '30', '35', '40', '45', '50', '55');
				
				//Load time table.				
				$renderer->assign('time_hours', $time_hours);
				$renderer->assign('time_minutes', $time_minutes);				
				$content['time_table'] = $renderer->fetch('site/mv-calendar/time_table.html');
				
				//Gesamtcontainer zusammenstellen aus Datums-Kalender und Time-Table
				$renderer->assign('time_table_html', $content['time_table']);
				//Gespraechsergebnis-Typen für farbige Legende laden.
				$iTerminGespraechstypen = new cTerminGespraechstypen();
				$renderer->assign('termin_gespraechstypen', $iTerminGespraechstypen->loadList());
								
				$content['text'] = $renderer->fetch('site/mv-calendar/date_and_time_container.html');
				
				//Termine laden, damit wir den Kalender damit befüllen können..
				$termin_json = $this->loadTermineJsonWithHTMLContainer();
				$termin_json = htmlspecialchars($termin_json);
				$termin_json = '<input type="hidden" id="mv-kalender-termine-json" value="' . $termin_json . '" />';
				
				$content['text'] .= $termin_json;
				
				echo $content['text'];
				die;
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Alle Termine mit JSON HTML Container laden.
		/////////////////////////////////////////////////////////////////////////////////
		function loadTermineJsonWithHTMLContainer() {
				$iTermine = new cTermine();
				$termine_json = $iTermine->loadAppointmentsJSONForTimeRangeAndUserAndStatus(
						$_SESSION['seller_id'],
						date('Y-m-d'),
						date('Y') . '-12-31',
						0
				);
				
				return $termine_json;
		}
		
		
		/////////////////////////////////////////////////////////////////////////////////
		// Nur den Kalender laden.
		/////////////////////////////////////////////////////////////////////////////////
		public function ajaxLoadYearOnlyHTMLWithContainer() {
				$month = date('m');
				$year = core()->getPostVar('year');
				$vergangene_daten_laden = (int)core()->getPostVar('vergangene_daten_laden');

				$kalenderblaetter = $this->getKalenderblaetter($month, $year, $vergangene_daten_laden);
				
				$site_url = cSeourls::loadSeourlByQueryString('s=cMerlinCustomer');
				$site_url = ltrim($site_url, '/');
				
				//If message is set -> set to template
				$message = core()->getGetVar('message');
				
				//Load the CMS Entry for the login page.
				$cms = core()->getInstance('cCMS');
				$content = $cms->loadContentDataByKey('SMARTSELLER_KALENDER');
				
				//Override cms with our html contents.
				$template_url = $cms->getTemplateUrl();
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('MONTH', $month);
				$renderer->assign('YEAR', $year);
				$renderer->assign('KALENDERBLAETTER', $kalenderblaetter);
				$renderer->assign('TEMPLATE_URL', $template_url);
				$renderer->assign('SITE_URL', $site_url);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('ERROR_FIELDS', $this->error_fields);
				$renderer->assign('MESSAGE', $message);
				$content['text'] = $renderer->fetch('site/mv-calendar/kalenderblaetter.html');
				
				echo $content['text'];
				die;
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Kleinen Kalender rendern.
		// Gibt das HTML als Zeichenkette zurück.
		/////////////////////////////////////////////////////////////////////////////////
		public function fetchSmallKalender($month, $year, $vergangene_daten_laden = 0) {
				$retval = '';
				
				$kalenderblaetter = $this->getKalenderblaetter($month, $year, $vergangene_daten_laden);
				
				$site_url = cSeourls::loadSeourlByQueryString('s=cMerlinCustomer');
				$site_url = ltrim($site_url, '/');
				
				//If message is set -> set to template
				$message = core()->getGetVar('message');
				
				//Load the CMS Entry for the login page.
				$cms = core()->getInstance('cCMS');
				$content = $cms->loadContentDataByKey('SMARTSELLER_KALENDER');
				
				//Override cms with our html contents.
				$template_url = $cms->getTemplateUrl();
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('MONTH', $month);
				$renderer->assign('YEAR', $year);
				$renderer->assign('KALENDERBLAETTER', $kalenderblaetter);
				$renderer->assign('TEMPLATE_URL', $template_url);
				$renderer->assign('SITE_URL', $site_url);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('ERROR_FIELDS', $this->error_fields);
				$renderer->assign('MESSAGE', $message);
				$content['text'] = $renderer->fetch('site/mv-calendar/kalender-small.html');
				
				return $content['text'];
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Get small calendar by ajax.
		/////////////////////////////////////////////////////////////////////////////////
		public function ajaxSmallKalender() {
				die('not implemented yet..');
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Search for customers (answers to an ajax call).
		/////////////////////////////////////////////////////////////////////////////////
		public function ajaxGetKalender() {
				/*
				$keywords = core()->getGetVar('keywords');
				$index = (int)core()->getGetVar('index');
				
				$search_result = cMerlinSession::searchForCustomers($keywords, $result_limit = 10, $index);
				*//*
				
				$date_timestamp = time();
				
				$month = date('m', $date_timestamp);
				$year = date('Y', $date_timestamp);
				
				$content = $this->renderKalender($month, $year);
				
				echo $content;
				die;
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Kalenderblätter abholen.
		/////////////////////////////////////////////////////////////////////////////////
		public function getKalenderblaetter($month, $year, $vergangene_daten_laden = 0) {
				$kalenderblaetter = array();
				
				for($i = 1; $i < 13; $i++) {
						$kalenderblatt = $this->renderKalenderblatt(str_pad($i, 2, '0', STR_PAD_LEFT), $year, $vergangene_daten_laden);
						$kalenderblaetter[] = $kalenderblatt;
				}
				
				return $kalenderblaetter;
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Render a kalenderblatt.
		/////////////////////////////////////////////////////////////////////////////////
		public function renderKalender($month, $year) {
				$kalenderblaetter = $this->getKalenderblaetter($month, $year);
				
				$termine = $this->renderZeitplan($month, $year);
				
				$site_url = cSeourls::loadSeourlByQueryString('s=cMerlinCustomer');
				$site_url = ltrim($site_url, '/');
				
				//If message is set -> set to template
				$message = core()->getGetVar('message');
				
				//Load the CMS Entry for the login page.
				$cms = core()->getInstance('cCMS');
				$content = $cms->loadContentDataByKey('SMARTSELLER_KALENDER');
				
				//Override cms with our html contents.
				$template_url = $cms->getTemplateUrl();
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('KALENDERBLAETTER', $kalenderblaetter);
				$renderer->assign('TERMINE', $termine);
				$renderer->assign('TEMPLATE_URL', $template_url);
				$renderer->assign('SITE_URL', $site_url);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('ERROR_FIELDS', $this->error_fields);
				$renderer->assign('MESSAGE', $message);
				$content['text'] = $renderer->fetch('site/mv-calendar/kalender-gross.html');
				
				return $content['text'];
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Monatsnamen abholen.
		/////////////////////////////////////////////////////////////////////////////////
		public function getMonatsnamen() {
				return array(
						'01' => 'Januar',
						'02' => 'Februar',
						'03' => 'März',
						'04' => 'April',
						'05' => 'Mai',
						'06' => 'Juni',
						'07' => 'Juli',
						'08' => 'August',
						'09' => 'September',
						'10' => 'Oktober',
						'11' => 'November',
						'12' => 'Dezember'
				);
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Termine rendern.
		/////////////////////////////////////////////////////////////////////////////////
		public function renderZeitplan($monat, $jahr) {
				$site_url = cSeourls::loadSeourlByQueryString('s=cSmartsellerKalender');
				$site_url = ltrim($site_url, '/');
				
				//If message is set -> set to template
				$message = core()->getGetVar('message');
				
				//Load the CMS Entry for the login page.
				$cms = core()->getInstance('cCMS');
				$content = $cms->loadContentDataByKey('SMARTSELLER_KALENDER');
				
				$zeitplan = $this->generateZeitplan($monat, $jahr);
				
				//Override cms with our html contents.
				$template_url = $cms->getTemplateUrl();
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('TEMPLATE_URL', $template_url);
				$renderer->assign('SITE_URL', $site_url);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('ERROR_FIELDS', $this->error_fields);
				$renderer->assign('MESSAGE', $message);
				$renderer->assign('ZEITPLAN', $zeitplan);
				$content['text'] = $renderer->fetch('site/mv-calendar/zeitplan.html');
				
				return $content['text'];
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Zeitplan erstellen.
		/////////////////////////////////////////////////////////////////////////////////
		public function generateZeitplan($monat, $jahr) {
				$zeitplan = array();
			
				for($stunde = 6; $stunde < 20; $stunde++) {
						for($minute = 0; $minute < 60; $minute += 5) {
								$termin_daten = array();
								
								$zeitplan[] = array(
										'stunde' => $stunde,
										'stunde_formatiert' => str_pad($stunde, 2, '0', STR_PAD_LEFT),
										'minute' => $minute,
										'minute_formatiert' => str_pad($minute, 2, '0', STR_PAD_LEFT),
										'termin_daten' => $termin_daten
								);
						}
				}
				
				return $zeitplan;
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Render a kalenderblatt.
		// Erstellt das HTML für ein Kalenderblatt eines Monats.
		// Gibt das fertige HTML per Rückgabe zurück.
		/////////////////////////////////////////////////////////////////////////////////
		public function renderKalenderblatt($monat, $jahr, $vergangene_daten_laden) {
				//Allgemeine Daten.
				$monatsnamen = $this->getMonatsnamen();
				$kal_datum_monatsstart = strtotime($jahr . '-' . $monat . '-01');	//Kalenderdatum.
				$kal_tage_gesamt = date("t", $kal_datum_monatsstart);							//Anzahl der Tage im Monat.
				$kal_start_timestamp = $kal_datum_monatsstart;										//Start-Tag im Monat.
				$kal_start_tag = date("N", $kal_start_timestamp);									//Kalender Start-Tag.
				$kal_ende_tag_tmp = mktime(0,0,0, date("n",$kal_datum_monatsstart), $kal_tage_gesamt, date("Y",$kal_datum_monatsstart));
				$kal_ende_tag = date("N", $kal_ende_tag_tmp);		//Kalender-Ende-Tag.
				$aktuelles_datum = time();
				$kalendertage_gesamt = $kal_tage_gesamt + ($kal_start_tag-1) + (7-$kal_ende_tag);
				
				$kalender_row = array();
				$kalender_rows = array();
				
				//Kalendertage laden.
				for($i = 1; $i <= $kalendertage_gesamt; $i++){
						$kal_anzeige_akt_tag = $i - $kal_start_tag;
						$kal_anzeige_heute_timestamp = strtotime($kal_anzeige_akt_tag." day", $kal_start_timestamp);		//Das Datum des aktuellen Kalendereintrages als Zeitstempel
						$kal_anzeige_heute_tag = date("j", $kal_anzeige_heute_timestamp);
						$wochentag = date("N", $kal_anzeige_heute_timestamp);									//Wochentag ermitteln (ist heute Montag, Dienstag, Mittwoch??
						
						//Wenn das ein Montag ist - neue Row starten - bisherige Row speichern (wenn i > 1); Tage der Woche zurücksetzen.
						if(date("N", $kal_anzeige_heute_timestamp) == 1 && $i > 1){
								$kalender_rows[] = $kalender_row;
								$kalender_row = array();
						}
						
						$ist_aktueller_tag = false;
						
						
						//Prüfen, ob der Tag in der Schleife auswählbar ist oder nicht
						// Anzahl der Tage in der Termine vereinbart werden können (Änderung der Zahl in den Systemeinstellungen möglich)
						$anzahl_tage_terminvereinbarung = array($this->data['data']['systemsettings']['calendar_maximum_number_of_days_to_make_an_appointment']);
						$anzahl_tage_terminvereinbarung = $anzahl_tage_terminvereinbarung[0]["value"]; 
						$zeitstempel_spaetester_moeglicher_termin = strtotime('+' . $anzahl_tage_terminvereinbarung . ' days');
						//var_dump( $anzahl_tage_terminvereinbarung);
						
						$is_choosable = 'is-choosable';							//Standard-Einstellung: Wenn der Tag weder in der Vergangenheit liegt, noch über dem "maximal wählbaren Zeitraum für Termine" liegt, ist er anwählbar und erhält deswegen die Klasse "choosable".
							
						//Prüfen, ob der Tag in der Schleife vor dem aktuellen Tag liegt (dann die Klasse "not-choosable-past" mitegeben)
						if ($kal_anzeige_heute_timestamp < strtotime(date('Y-m-d') . ' 00:00:00')) {
								if($vergangene_daten_laden == 0) {
										$is_choosable = 'not-choosable-past';
								}
						}
						
						//Prüfen, ob der Tag in der Schleife nach dem "Maximale Wählbaren Zeitraum für Termine" liegt. (wenn ja - Klasse "not-choosable-future") mitgeben
						if($kal_anzeige_heute_timestamp > $zeitstempel_spaetester_moeglicher_termin) {
								$is_choosable = 'not-choosable-future';
						}
						
						//Prüfen, ob der Tag in der Schleife der aktuelle Tag ist.
						if((date("dmY", $aktuelles_datum) == date("dmY", $kal_anzeige_heute_timestamp)) && $monat == date('m', $kal_anzeige_heute_timestamp)) {
								$ist_aktueller_tag = true;
						}
												
						//Wenn Wochenende ist..
						if(  (($wochentag == 6 OR $wochentag ==7) && $kal_anzeige_akt_tag < $kal_tage_gesamt) && $monat == date('m', $kal_anzeige_heute_timestamp)  ){
								
								$kalender_row[] = $this->getKalenderblattDay($wochentag, $kal_anzeige_akt_tag, $kal_anzeige_heute_timestamp, $kal_anzeige_heute_tag, $wochenende = true, $ist_aktueller_tag, $is_day_of_month = true, $is_choosable);
								//echo "<td class='tab_wochenende'>" . $kal_anzeige_heute_tag . "</td>";
						} 
																 
						//Wenn der aktuelle Tag ist (Also HEUTE)
						elseif(  (date("dmY", $aktuelles_datum) == date("dmY", $kal_anzeige_heute_timestamp)) && $monat == date('m', $kal_anzeige_heute_timestamp)  ){
								
								$kalender_row[] = $this->getKalenderblattDay($wochentag, $kal_anzeige_akt_tag, $kal_anzeige_heute_timestamp, $kal_anzeige_heute_tag, $wochenende = false, $ist_aktueller_tag, $is_day_of_month = true, $is_choosable);
								//echo "<td class='kal_aktueller_tag c-btn--info'>" . $kal_anzeige_heute_tag . "</td>";
						}
						
						//Wenn ein ganz normaler Tag ist.
						elseif($kal_anzeige_akt_tag >= 0 AND $kal_anzeige_akt_tag < $kal_tage_gesamt){
								
								$kalender_row[] = $this->getKalenderblattDay($wochentag, $kal_anzeige_akt_tag, $kal_anzeige_heute_timestamp, $kal_anzeige_heute_tag, $wochenende = false, $ist_aktueller_tag, $is_day_of_month = true, $is_choosable);
								//echo "<td class='kal_standard_tag'>" . $kal_anzeige_heute_tag . "</td>";
						}  

						//Kein relevanter Tag - Überspringen..
						else{
								$kalender_row[] = $this->getKalenderblattDay($wochentag, $kal_anzeige_akt_tag, $kal_anzeige_heute_timestamp, $kal_anzeige_heute_tag, $wochenende = false, $ist_aktueller_tag, $is_day_of_month = false, $is_choosable);
						}
						if(date("N", $kal_anzeige_heute_timestamp) == 7){
								//echo "</tr>";
						}
						
						if($i == $kalendertage_gesamt) {
								$kalender_rows[] = $kalender_row;
						}
				}

				$kalenderblatt_data = array(
						'monat' => $monat,
						'jahr' => $jahr,
						'kal_datum_monatsstart' => $kal_datum_monatsstart,
						'kal_tage_gesamt' => $kal_tage_gesamt,
						'kal_start_timestamp' => $kal_start_timestamp,
						'kal_start_tag' => $kal_start_tag,
						'kal_ende_tag' => $kal_ende_tag,
						'aktuelles_datum' => $aktuelles_datum,
						'monatsnamen' => $monatsnamen,
						'kalender_rows' => $kalender_rows
				);
				
				$site_url = cSeourls::loadSeourlByQueryString('s=cMerlinCustomer');
				$site_url = ltrim($site_url, '/');
				
				//If message is set -> set to template
				$message = core()->getGetVar('message');
				
				//Load the CMS Entry for the login page.
				$cms = core()->getInstance('cCMS');
				$content = $cms->loadContentDataByKey('SMARTSELLER_KALENDER');
				
				//Override cms with our html contents.
				$template_url = $cms->getTemplateUrl();
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('TEMPLATE_URL', $template_url);
				$renderer->assign('SITE_URL', $site_url);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('ERROR_FIELDS', $this->error_fields);
				$renderer->assign('MESSAGE', $message);
				$renderer->assign('KALENDERBLATT_DATA', $kalenderblatt_data);
				$content['text'] = $renderer->fetch('site/mv-calendar/kalenderblatt.html');
				
				return $content['text'];
		}
		
		///////////////////////////////////////////////////////////////////
		// Daten für einen Kalenderblatt-Tag bekommen.
		///////////////////////////////////////////////////////////////////
		public function getKalenderblattDay($wochentag, $kal_anzeige_akt_tag, $kal_anzeige_heute_timestamp, $kal_anzeige_heute_tag, $wochenende, $ist_heute, $is_day_of_month, $is_choosable) {
				return array(
						'wochentag' => $wochentag,
						'kal_anzeige_akt_tag' => $kal_anzeige_akt_tag,
						'kal_anzeige_heute_timestamp' => $kal_anzeige_heute_timestamp,
						'kal_anzeige_heute_tag' => $kal_anzeige_heute_tag,
						'is_day_of_month' => $is_day_of_month,
						'wochenende' => $wochenende,
						'ist_heute' => $ist_heute,
						'is_choosable' => $is_choosable
				);
		}
		
		//////////////////////////////////////////////////////////////////////////////
		// Render a page.
		//////////////////////////////////////////////////////////////////////////////
		public function renderPage() {
				$site_url = cSeourls::loadSeourlByQueryString('s=cSmartsellerKalender');
				$site_url = ltrim($site_url, '/');
				
				//If message is set -> set to template
				$message = core()->getGetVar('message');
				
				//Load the CMS Entry for the login page.
				$cms = core()->getInstance('cCMS');
				$content = $cms->loadContentDataByKey('SMARTSELLER_KALENDER');
				
				/*
				//Override cms with our html contents.
				$template_url = $cms->getTemplateUrl();
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				//$renderer->assign('ERRORS', $this->error_escaping($this->errors));
				$renderer->assign('TEMPLATE_URL', $template_url);
				$renderer->assign('TEMPLATE', $this->template);
				$renderer->assign('SITE_URL', $site_url);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('MESSAGE', $message);
				$content['text'] = $renderer->fetch('site/kalender.html');
				*//*
				
				$date_timestamp = time();
				
				$month = date('m', $date_timestamp);
				$year = date('Y', $date_timestamp);
				
				//$content['text'] = $this->renderKalender($month, $year);
				$content['text'] = $this->fetchSmallKalender($month, $year);
				
				//set new html content in cms object.				
				$cms->setContentData($content);
				
				//Set the site url. We need this for the form to have the right action url!
				$cms->setSiteUrl(cSite::loadSiteUrl(core()->get('site_id')) . $site_url);
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the page footer.
		///////////////////////////////////////////////////////////////////
		public function footer() {
				$additional_output = 
						/*"\n" . '<script src="//' . cSite::loadSiteUrl(core()->get('site_id')) . 'data/templates/' . $this->template . '/js/merlin_customer.js"></script>' .*//*
						"\n" . '<script src="//' . cSite::loadSiteUrl(core()->get('site_id')) . 'data/templates/' . $this->template . '/js/kalender.js"></script>' .
						"\n" . '<script src="//' . cSite::loadSiteUrl(core()->get('site_id')) . 'data/templates/' . $this->template . '/js/smartseller-kalender-debug.js"></script>' .
						"\n";
				$renderer = core()->getInstance('cRenderer');
				$renderer->renderText($additional_output);
		}
		
		
}

*/