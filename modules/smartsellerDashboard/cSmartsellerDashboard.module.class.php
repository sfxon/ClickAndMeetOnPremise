<?php

class cSmartsellerDashboard extends cModule {
        var $template = 'smartseller';
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
                $state = cWsseller::checkSellerStatus();        //Redirects to another page, if an error occures.
                       
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
								$_SESSION['site_history'] = array(
                        cSmartsellerSiteHistory::buildEntry(1)
                );
								
								$this->loadTextVariables();					
                $this->loadGeneralData();
                $this->loadPageSpecificData();
               
                $errormessage = '';
                $action = core()->getGetVar('action');
                $this->action = $action;
               
                switch($this->action) {
                        default:
                                break;
                }
               
                $this->renderPage();
        }
				
				/////////////////////////////////////////////////////////////////////////////////
				// Textvariablen laden.
				/////////////////////////////////////////////////////////////////////////////////
				public function loadTextVariables() {
						$this->data['textvariables'] = array(
								'kundenstamm_title' => 'Ihr Kundenstamm'
						);
				}
       
        /////////////////////////////////////////////////////////////////////////////////
        // Load some data that is always used.
        /////////////////////////////////////////////////////////////////////////////////
        private function loadGeneralData() {
						$iTime = new cTime();
						
						$this->data['data']['eingestellter_zeitpunkt'] = $this->loadEingestellterZeitpunkt();
						$this->data['data']['seller_account'] = cAccount::loadUserData($_SESSION['seller_id']);        //Verkäufer-Daten
						$this->data['data']['tageszeit'] = $iTime->getTageszeit();                                                                //Tageszeit laden.
						$this->data['data']['tag_in_bearbeitung'] = $this->getTagInBearbeitung();                                    //Laden, welcher Tag gerade bearbeitet wird (Datum aus Parameter, Texte für den Tag, ..)
						$this->data['data']['termine'] = $this->getTermine();
						$this->data['data']['systemsettings'] = $this->getSystemsettings();
						
						$iAccountGruppen = new cAccountGruppen();
						$this->data['data']['seller_account_gruppen'] = $iAccountGruppen->loadAccountGroupsByAccountId_AddAccountGroupsData($_SESSION['seller_id']);
						
						$iCustomHandlerSmartsellerAccountGruppen = new cCustomHandlerSmartsellerAccountGruppen();
						$seller_account_gruppe = $iCustomHandlerSmartsellerAccountGruppen->getCurrentAccountGruppe();
						$this->data['data']['seller_account_gruppe'] = $seller_account_gruppe;

						$this->setFooter();
        }
       
        /////////////////////////////////////////////////////////////////////////////////
        // Footer initialisieren.
        /////////////////////////////////////////////////////////////////////////////////
        private function setFooter() {
                $account_id = $_SESSION['seller_id'];
                $account_data = cAccount::loadUserData($account_id);
                $cms = core()->getInstance('cCMS');
                $cms->setFooterData('account_data', $account_data);
        }
       
        /////////////////////////////////////////////////////////////////////////////////
        // Systemeinstellungen laden.
        /////////////////////////////////////////////////////////////////////////////////
        private function getSystemsettings() {
                $iSystemsettings = new cSystemsettings();
               
                return $iSystemsettings->loadListIndexed();
        }
       
        /////////////////////////////////////////////////////////////////////////////////
        // Load some data that is always used.
        /////////////////////////////////////////////////////////////////////////////////
        private function loadPageSpecificData() {
                $this->data['data']['termin_statistik'] = $this->loadTerminStatistik();
                $this->data['data']['ueberfaellige_termine_statistik'] = $this->loadUeberfaelligeTermineStatistik();
                $this->data['data']['gestern_ihr_tag'] = $this->loadGesternIhrTag();
                $this->data['data']['gestern_call_performance'] = $this->loadGesternCallPerformance();
                $this->data['data']['birthdays'] = $this->loadNextBirthdays();
                $this->data['data']['aufgaben'] = $this->loadAufgaben();
                $this->data['data']['aufgaben_erledigt_heute'] = $this->loadAufgabenErledigtHeute();
        }
       
        /////////////////////////////////////////////////////////////////////////////////
        // Lade heute erledigte Aufgaben
        /////////////////////////////////////////////////////////////////////////////////
        public function loadAufgabenErledigtHeute() {
                $date_from = date('Y-m-d');
                $date_to = date('Y-m-d');
               
                $iAufgaben = new cAufgaben();
                return $iAufgaben->loadListByAccountIdAndAufgabenStatusAndErledigtDate($_SESSION['seller_id'], 1, $date_from, $date_to);
        }
       
        /////////////////////////////////////////////////////////////////////////////////
        // Load some data that is always used.
        /////////////////////////////////////////////////////////////////////////////////
        public function loadAufgaben() {
                $iAufgaben = new cAufgaben();
               
                return $iAufgaben->loadListByAccountIdAndAufgabenStatus($_SESSION['seller_id'], 0);
        }
       
        /////////////////////////////////////////////////////////////////////////////////
        // Die nächsten Geburtstage laden..
        /////////////////////////////////////////////////////////////////////////////////
        private function loadNextBirthdays() {
                $iAnsprechpartner = new cAnsprechpartner();
               
                return $iAnsprechpartner->getNextBirthdaysByDateAndIndexAndAccount(5, 0, $_SESSION['seller_id'], date('Y-m-d H:i:s'));
               
        }
       
        /////////////////////////////////////////////////////////////////////////////////
        // Load the data for "Gestern - Call Performance"
        /////////////////////////////////////////////////////////////////////////////////
        private function loadGesternCallPerformance() {
                $data['outbound'] = $this->loadGesternCallPerformance_Outbound();
                $data['outbound_calls'] = $this->loadGesternAnrufe_Outbound();
                $data['outbound_average_calltime'] = $this->loadGestern_DurchschnittlicheAnrufdauer_Outbound();
               
                return $data;
        }
       
        /////////////////////////////////////////////////////////////////////////////////
        // Load the data for "Gestern - Durchschnittliche Anrufdauer"
        //
        // Wie lange hat der Verkäufer gestern durchschnittlich
        // in einem Gespräch telefoniert?
        //
        /////////////////////////////////////////////////////////////////////////////////
        private function loadGestern_DurchschnittlicheAnrufdauer_Outbound() {
                $date = date('Y-m-d', strtotime('-1days'));
                $iAnrufe = new cAnrufe();
               
                $anrufdauer_durchschnittlich_gestern = $iAnrufe->averageAnrufDauerSekundenBySellerIdAndDate($_SESSION['seller_id'], $date, $date);
                $pflicht_anrufdauer_durchschnitt = $this->data['data']['seller_account']['pflicht_anrufdauer_durchschnitt'];
                $percent = 0;
               
                if($pflicht_anrufdauer_durchschnitt > 0) {
                        $percent = (100 / $pflicht_anrufdauer_durchschnitt) * $anrufdauer_durchschnittlich_gestern;
                }
               
                if($percent > 100) {
                        $percent = 100;
                }
               
                $anrufdauer_durchschnittlich_gestern_formatiert = date("H:i:s",$anrufdauer_durchschnittlich_gestern+strtotime("1970/1/1"));
                $pflicht_anrufdauer_durchschnitt_formatiert = date("H:i:s",$pflicht_anrufdauer_durchschnitt+strtotime("1970/1/1"));  
               
                $retval = array(
                        'anrufdauer_durchschnittlich_gestern' => $anrufdauer_durchschnittlich_gestern,
                        'anrufdauer_durchschnittlich_gestern_formatiert' => $anrufdauer_durchschnittlich_gestern_formatiert,
                        'pflicht_anrufdauer_durchschnitt' => $pflicht_anrufdauer_durchschnitt,
                        'pflicht_anrufdauer_durchschnitt_formatiert' => $pflicht_anrufdauer_durchschnitt_formatiert,
                        'percent' => $percent
                );
               
                return $retval;
        }
       
        /////////////////////////////////////////////////////////////////////////////////
        // Load the data for "Gestern - Call Performance"
        //
        // Wie viele Outbound Anrufe hat der Verkäufer gestern gehabt?
        //
        /////////////////////////////////////////////////////////////////////////////////
        private function loadGesternAnrufe_Outbound() {
                $date = date('Y-m-d', strtotime('-1days'));
                $iAnrufe = new cAnrufe();
               
                $anrufe_gestern = $iAnrufe->countBySellerIdAndDate($_SESSION['seller_id'], $date, $date);
                $pflicht_anrufe_pro_tag = $this->data['data']['seller_account']['pflicht_anrufe_pro_tag'];
                $percent = 0;
               
                if($pflicht_anrufe_pro_tag > 0) {
                        $percent = (100 / $pflicht_anrufe_pro_tag) * $anrufe_gestern;
                }
               
                if($percent > 100) {
                        $percent = 100;
                } 
               
                $retval = array(
                        'anrufe_gestern' => $anrufe_gestern,
                        'pflicht_anrufe_pro_tag' => $pflicht_anrufe_pro_tag,
                        'percent' => $percent
                );
               
                return $retval;
        }
       
        /////////////////////////////////////////////////////////////////////////////////
        // Load the data for "Gestern - Call Performance"
        //
        // Wie lange hat der Verkäufer gestern aktiv telefoniert,
        // im Verhältnis zu dem, wie lange er telefonieren soll?
        //
        /////////////////////////////////////////////////////////////////////////////////
        private function loadGesternCallPerformance_Outbound() {
                $date = date('Y-m-d', strtotime('-1days'));
                $iAnrufe = new cAnrufe();
               
                $anrufdauer_gestern = $iAnrufe->sumDauerSekundenBySellerIdAndDate($_SESSION['seller_id'], $date, $date);
                $pflicht_anrufdauer_pro_tag = $this->data['data']['seller_account']['pflicht_anrufdauer_pro_tag'];
                $percent = 0;
               
                if($pflicht_anrufdauer_pro_tag > 0) {
                        $percent = (100 / $pflicht_anrufdauer_pro_tag) * $anrufdauer_gestern;
                }
               
                if($percent > 100) {
                        $percent = 100;
                }

        }
       
        /////////////////////////////////////////////////////////////////////////////////
        // Load the data for "Gestern - Ihr Tag"
        /////////////////////////////////////////////////////////////////////////////////
        private function loadGesternIhrTag() {
                $data['erledigte_termine_im_call'] = $this->loadErledigteTermineImCall();
                $data['kunden_ohne_netto_kontakte'] = $this->loadKundenOhneNettoKontakte();
                $data['erfolgreich_abgeschlossene_verkaeufe'] = $this->loadErfolgreichAbgeschlosseneVerkaeufe();
               
                return $data;
        }
       
        /////////////////////////////////////////////////////////////////////////////////
        // Load  "erfoglreich abgeschlossene Verkäufe".
        // Anzahl der Termine, bei denen ein Verkauf erzielt wurde.
        //
        // Das sind alle die, bei denen der Status
        //        == 3  (= Verkauft)
        //
        // ist.
        //
        /////////////////////////////////////////////////////////////////////////////////
        private function loadErfolgreichAbgeschlosseneVerkaeufe() {
                $date = date('Y-m-d', strtotime('-1days'));
                $iTermine = new cTermine();
               
                $verkaeufe = $iTermine->countSellersTermineByState($_SESSION['seller_id'], $date, $date, 3);
                $pflicht_verkaeufe_pro_tag = $this->data['data']['seller_account']['anzahl_pflicht_verkaeufe_pro_werktag'];
               
                //$termine_nicht_erreicht = $offene_termine + $nur_angerufene_termine;
                //$erledigte_termine = $gesamt_termine - $termine_nicht_erreicht;
                $percent = 100;
               
                if($pflicht_verkaeufe_pro_tag > 0) {
                        $percent = ($percent / $pflicht_verkaeufe_pro_tag) * $verkaeufe;
                }
               
                if($percent > 100) {
                        $percent = 100;
                }
               
                $retval = array(
                        'verkaeufe' => $verkaeufe,
                        'pflicht_verkaeufe_pro_tag' => $pflicht_verkaeufe_pro_tag,
                        'percent' => $percent
                );
               
                return $retval;
        }
       
        /////////////////////////////////////////////////////////////////////////////////
        // Load  "Kunden ohne Netto Kontakte".
        // Anzahl der Termine, bei denen der Ansprechpartner NICHT erreicht wurde.
        //
        // Das sind alle die, bei denen der Status entweder
        //        == 0  (= offen)
        //        == 1  (= nur angerufen)
        //
        // ist.
        //
        /////////////////////////////////////////////////////////////////////////////////
        private function loadKundenOhneNettoKontakte() {
                $date = date('Y-m-d', strtotime('-1days'));
                $iTermine = new cTermine();
               
                $offene_termine = $iTermine->countSellersTermineByState($_SESSION['seller_id'], $date, $date, 0);
                $nur_angerufene_termine = $iTermine->countSellersTermineByState($_SESSION['seller_id'], $date, $date, 1);
                $gesamt_termine = $iTermine->countSellersTermine($_SESSION['seller_id'], $date, $date);
               
                $termine_nicht_erreicht = $offene_termine + $nur_angerufene_termine;
                $erledigte_termine = $gesamt_termine - $termine_nicht_erreicht;
                $percent = 100;
               
                if($termine_nicht_erreicht > 0) {
                        $percent = ($percent / $gesamt_termine) * $termine_nicht_erreicht;
                }
               
                $retval = array(
                        'offene_termine' => $offene_termine,
                        'nur_angerufene_termine' => $nur_angerufene_termine,
                        'termine_nicht_erreicht' => $termine_nicht_erreicht,
                        'erledigte_termine' => $erledigte_termine,
                        'gesamt_termine' => $gesamt_termine,
                        'percent' => $percent
                );
               
                return $retval;
        }
       
        /////////////////////////////////////////////////////////////////////////////////
        // Load  "erledigte Termine im Call".
        // Anzahl der Termine, bei denen eine Kontaktaufnahme überhaupt versucht wurde.
        //
        //    Das sind alle Termine, die als Status nicht 0 sind!
        //
        /////////////////////////////////////////////////////////////////////////////////
        private function loadErledigteTermineImCall() {
                $date = date('Y-m-d', strtotime('-1days'));
                $iTermine = new cTermine();
               
                $offene_termine = $iTermine->countSellersTermineByState($_SESSION['seller_id'], $date, $date, 0);
                $gesamt_termine = $iTermine->countSellersTermine($_SESSION['seller_id'], $date, $date);
               
                $erledigte_termine = $gesamt_termine - $offene_termine;
               
                $percent = 0;
               
                if($offene_termine > 0) {
                        $percent = ($percent / $gesamt_termine) * $erledigte_termine;
                }
               
                $retval = array(
                        'offene_termine' => $offene_termine,
                        'erledigte_termine' => $erledigte_termine,
                        'gesamt_termine' => $gesamt_termine,
                        'percent' => $percent
                );
               
                return $retval;
        }
       
        /////////////////////////////////////////////////////////////////////////////////
        // Load some data that is always used.
        /////////////////////////////////////////////////////////////////////////////////
        private function loadUeberfaelligeTermineStatistik() {
                $termin_statistik = array();               
                $iTermine = new cTermine();

                //today..
                $date_from = date('Y-m-d', strtotime('now')); //date('Y-m-d');
                $date_to = date('Y-m-d', strtotime('now')); //date('Y-m-d');
                $termin_statistik['ueberfaellige_termine_heute_count'] = $this->loadUeberfaelligeTerminStatistikFuerHeute($_SESSION['seller_id'], $date_from, $date_to);
               
                //gestern
                $date_from = date('Y-m-d', strtotime('-1 day')); //date('Y-m-d');
                $date_to = date('Y-m-d', strtotime('-1 day')); //date('Y-m-d');
                $termin_statistik['ueberfaellige_termine_gestern_count'] = $this->loadUeberfaelligeTerminStatistikFuerZeitraum($_SESSION['seller_id'], $date_from, $date_to);
               
                //Die letzten 5 Tage..
                $last_days = 6;
                $date_from = date('Y-m-d', strtotime('-' . $last_days . 'days'));
                $date_to = date('Y-m-d', strtotime('-2 day')); //date('Y-m-d');
                $termin_statistik['ueberfaellige_termine_last_five_days'] = $this->loadUeberfaelligeTerminStatistikFuerZeitraum($_SESSION['seller_id'], $date_from, $date_to);
               
                //Die letzten 20 Tage..
                $last_days = 20;
                //$date_from = date('Y-m-d', strtotime('-' . $last_days . 'days'));
                //$date_from = date('Y-m-d', strtotime('-' . $last_days . 'days'));
								$date_from = '1999-01-01';
                $date_to = date('Y-m-d', strtotime('-7 day')); //date('Y-m-d');
               
                //var_dump($date_to);
                //var_dump($date_from);
               
                //$termine_this_week_count = $iTermine->countSellersTermine($_SESSION['seller_id'], $date_from, $date_to);
                $termin_statistik['ueberfaellige_termine_last_twenty_days'] = $this->loadUeberfaelligeTerminStatistikFuerZeitraum($_SESSION['seller_id'], $date_from, $date_to);
               
                //total..
                $termin_statistik['ueberfaellige_termine_open_total'] =
                        $termin_statistik['ueberfaellige_termine_heute_count']['termine_open'] +
                        $termin_statistik['ueberfaellige_termine_gestern_count']['termine_open'] +
                        $termin_statistik['ueberfaellige_termine_last_five_days']['termine_open'] +
                        $termin_statistik['ueberfaellige_termine_last_twenty_days']['termine_open'];
                       
                //var_dump($termin_statistik['ueberfaellige_termine_last_five_days']['termine_open']);
               
                return $termin_statistik;
        }
       
        /////////////////////////////////////////////////////////////////////////////////
        // Load some data that is always used.
        /////////////////////////////////////////////////////////////////////////////////
        private function loadUeberfaelligeTerminStatistikFuerZeitraum($account_id, $date_from, $date_to) {
                $termine_total = 0;
                $termine_open = 0;
                $werktage = 0;
               
                $termin_statistik = array();
                $iTermine = new cTermine();
               
                //Berechnen, wieviele Tage zwischen Anfang und Ende liegen.
                $anzahl_tage = 0;
                $timestamp_from = strtotime($date_from . ' 00:00:00');
                $timestamp_to = strtotime($date_to . ' 00:00:00');
                $time_difference = $timestamp_to - $timestamp_from;
               
                //If the final date is lower than the start date - swap them..
                //This avoids an endless loop.
                if($timestamp_from > $timestamp_to) {
                        $tmp = $timestamp_from;
                        $timestamp_from = $timestamp_to;
                        $timestamp_to = $tmp;
                }
               
                //If there is a date difference, calcualte the number of days.
                if($time_difference > 0) {
                        $anzahl_tage = round($time_difference / (60 * 60 * 24));
                }
               
                $anzahl_tage += 1;
               
                //Alle Tage durchlaufen.               
                for($i = 0; $i < $anzahl_tage; $i++) {
                        if($i == 0) {
                                $date = date('Y-m-d', strtotime($date_from . ' 00:00:00'));        //Aktueller Tag..
                        } else {
                                $date = date('Y-m-d', strtotime('+' . $i . 'days', strtotime($date_from . ' 00:00:00')));        //Aktueller Tag + $i Tage...
                        }
                       
                        if($iTermine->isBusinessDay($account_id, $date)) {
                                $termine_total += $iTermine->countSellersTermine($account_id, $date, $date);
                                $termine_open += $iTermine->loadOpenAppointmentsForTimeRangeAndUserAndStatus($account_id, $date, $date, 0);
                                $werktage++;
                        }
                }
               
                //Prozentualen Wert berechnen (Wieviel Prozent davon sind erfüllt?
                $percent_of_completion = 0;
               
                if($termine_total > 0) {
                        $percent_of_completion = (100 / $termine_total) * $termine_open;
                }
               
                $max_100_percent_of_completion = $percent_of_completion;
               
                if($max_100_percent_of_completion > 100) {
                        $max_100_percent_of_completion = 100;
                }
               
                if($werktage == 0) {
                        $percent_of_completion = 0;
                        $max_100_percent_of_completion = 0;
                }
               
                if($termine_total == 0) {
                        $percent_of_completion = 0;
                        $max_100_percent_of_completion = 0;
                }
               
                $max_100_percent_of_completion = round($max_100_percent_of_completion);
                $percent_of_completion = round($percent_of_completion);
               
                $retval = array(
                        'werktage' => $werktage,
                        'termine_total' => $termine_open,
                        'termine_open' => $termine_open,
                        'percent_of_completion' => $percent_of_completion,
                        'max_100_percent_of_completion' => $max_100_percent_of_completion
                );
               
                return $retval;
        }
       
			  /////////////////////////////////////////////////////////////////////////////////
        // Load some data that is always used.
        /////////////////////////////////////////////////////////////////////////////////
        private function loadUeberfaelligeTerminStatistikFuerHeute($account_id, $date_from, $date_to) {
                $termine_total = 0;
                $termine_open = 0;
                $werktage = 0;
               
                $termin_statistik = array();
                $iTermine = new cTermine();
               
                //Berechnen, wieviele Tage zwischen Anfang und Ende liegen.
                $anzahl_tage = 0;
                $timestamp_from = strtotime($date_from . ' 00:00:00');
                $timestamp_to = strtotime($date_to . ' 00:00:00');
                $time_difference = $timestamp_to - $timestamp_from;
               
                //If the final date is lower than the start date - swap them..
                //This avoids an endless loop.
                if($timestamp_from > $timestamp_to) {
                        $tmp = $timestamp_from;
                        $timestamp_from = $timestamp_to;
                        $timestamp_to = $tmp;
                }
               
                //If there is a date difference, calcualte the number of days.
                if($time_difference > 0) {
                        $anzahl_tage = round($time_difference / (60 * 60 * 24));
                }
               
                $anzahl_tage += 1;
               
                //Alle Tage durchlaufen.               
                for($i = 0; $i < $anzahl_tage; $i++) {
                        if($i == 0) {
                                $date = date('Y-m-d', strtotime($date_from . ' 00:00:00'));        //Aktueller Tag..
                        } else {
                                $date = date('Y-m-d', strtotime('+' . $i . 'days', strtotime($date_from . ' 00:00:00')));        //Aktueller Tag + $i Tage...
                        }
                       
                        if($iTermine->isBusinessDay($account_id, $date)) {
                                $termine_total += $iTermine->countSellersTermine($account_id, $date, $date);
                                $termine_open += $iTermine->loadOpenAppointmentsForTodayAndUserAndStatusByTime($account_id, $date, $date, 0);
                                $werktage++;
                        }
                }
               
                //Prozentualen Wert berechnen (Wieviel Prozent davon sind erfüllt?
                $percent_of_completion = 0;
               
                if($termine_total > 0) {
                        $percent_of_completion = (100 / $termine_total) * $termine_open;
                }
               
                $max_100_percent_of_completion = $percent_of_completion;
               
                if($max_100_percent_of_completion > 100) {
                        $max_100_percent_of_completion = 100;
                }
               
                if($werktage == 0) {
                        $percent_of_completion = 0;
                        $max_100_percent_of_completion = 0;
                }
               
                if($termine_total == 0) {
                        $percent_of_completion = 0;
                        $max_100_percent_of_completion = 0;
                }
               
                $max_100_percent_of_completion = round($max_100_percent_of_completion);
                $percent_of_completion = round($percent_of_completion);
               
                $retval = array(
                        'werktage' => $werktage,
                        'termine_total' => $termine_open,
                        'termine_open' => $termine_open,
                        'percent_of_completion' => $percent_of_completion,
                        'max_100_percent_of_completion' => $max_100_percent_of_completion
                );
               
                return $retval;
        }
			 
        /////////////////////////////////////////////////////////////////////////////////
        // Load some data that is always used.
        /////////////////////////////////////////////////////////////////////////////////
        private function loadTerminStatistikFuerZeitraum($account_id, $date_from, $date_to) {
                $pflicht_termine_an_werktagen = 0;
                $termine_count = 0;
                $werktage = 0;
               
                $termin_statistik = array();
                $iTermine = new cTermine();
               
                //Berechnen, wieviele Tage zwischen Anfang und Ende liegen.
                $anzahl_tage = 0;
                $timestamp_from = strtotime($date_from . ' 00:00:00');
                $timestamp_to = strtotime($date_to . ' 00:00:00');
                $time_difference = $timestamp_to - $timestamp_from;
               
                //If the final date is lower than the start date - swap them..
                //This avoids an endless loop.
                if($timestamp_from > $timestamp_to) {
                        $tmp = $timestamp_from;
                        $timestamp_from = $timestamp_to;
                        $timestamp_to = $tmp;
                }
               
                //If there is a date difference, calcualte the number of days.
                if($time_difference > 0) {
                        $anzahl_tage = round($time_difference / (60 * 60 * 24));
                }
               
                $anzahl_tage += 1;

                //Alle Tage durchlaufen.               
                for($i = 0; $i < $anzahl_tage; $i++) {
                        if($i == 0) {
                                $date = date('Y-m-d', strtotime($date_from . ' 00:00:00'));        //Aktueller Tag..
                        } else {
                                $date = date('Y-m-d', strtotime('+' . $i . 'days', strtotime($date_from . ' 00:00:00')));        //Aktueller Tag + $i Tage...
                        }
                       
                        if($iTermine->isBusinessDay($account_id, $date)) {
                                $pflicht_termine_an_werktagen += $this->data['data']['seller_account']['anzahl_pflicht_termine_an_werktagen'];
                                //$termine_count += $iTermine->countSellersTermine($account_id, $date, $date);
																$termine_count += $iTermine->countSellersTermineByState($account_id, $date, $date, 0);
                                $werktage++;
                        }
                }
               
                //Prozentualen Wert berechnen (Wieviel Prozent davon sind erfüllt?
                $percent_of_completion = 0;
               
                if($pflicht_termine_an_werktagen > 0) {
                        $percent_of_completion = (100 / $pflicht_termine_an_werktagen) * $termine_count;
                }
               
                $max_100_percent_of_completion = $percent_of_completion;
               
                if($max_100_percent_of_completion > 100) {
                        $max_100_percent_of_completion = 100;
                }
               
                if($werktage == 0) {
                        $percent_of_completion = 100;
                        $max_100_percent_of_completion = 100;
                }
               
                if($pflicht_termine_an_werktagen == 0) {
                        $percent_of_completion = 100;
                        $max_100_percent_of_completion = 100;
                }
               
                $max_100_percent_of_completion = round($max_100_percent_of_completion);
                $percent_of_completion = round($percent_of_completion);
               
                $retval = array(
                        'werktage' => $werktage,
                        'pflicht_termine_an_werktagen' => $pflicht_termine_an_werktagen,
                        'termine_count' => $termine_count,
                        'percent_of_completion' => $percent_of_completion,
                        'max_100_percent_of_completion' => $max_100_percent_of_completion
                );
               
                return $retval;
        }
       
			 
			   /////////////////////////////////////////////////////////////////////////////////
        // Load some data that is always used.
        /////////////////////////////////////////////////////////////////////////////////
        private function loadTerminStatistikFuerHeuteByTime($account_id, $date_from, $date_to) {
                $pflicht_termine_an_werktagen = 0;
                $termine_count = 0;
                $werktage = 0;
               
                $termin_statistik = array();
                $iTermine = new cTermine();
               
                //Berechnen, wieviele Tage zwischen Anfang und Ende liegen.
                $anzahl_tage = 0;
                $timestamp_from = strtotime($date_from . ' 00:00:00');
                $timestamp_to = strtotime($date_to . ' 00:00:00');
                $time_difference = $timestamp_to - $timestamp_from;
               
                //If the final date is lower than the start date - swap them..
                //This avoids an endless loop.
                if($timestamp_from > $timestamp_to) {
                        $tmp = $timestamp_from;
                        $timestamp_from = $timestamp_to;
                        $timestamp_to = $tmp;
                }
               
                //If there is a date difference, calcualte the number of days.
                if($time_difference > 0) {
                        $anzahl_tage = round($time_difference / (60 * 60 * 24));
                }
               
                $anzahl_tage += 1;
               
                //Alle Tage durchlaufen.               
                for($i = 0; $i < $anzahl_tage; $i++) {
                        if($i == 0) {
                                $date = date('Y-m-d', strtotime($date_from . ' 00:00:00'));        //Aktueller Tag..
                        } else {
                                $date = date('Y-m-d', strtotime('+' . $i . 'days', strtotime($date_from . ' 00:00:00')));        //Aktueller Tag + $i Tage...
                        }
                       
                        if($iTermine->isBusinessDay($account_id, $date)) {
                                $pflicht_termine_an_werktagen += $this->data['data']['seller_account']['anzahl_pflicht_termine_an_werktagen'];
                                //$termine_count += $iTermine->countSellersTermine($account_id, $date, $date);
																$termine_count += $iTermine->countSellersTermineFuerHeuteByStateAndTime($account_id, $date, $date, 0);
                                $werktage++;
                        }
                }
               
                //Prozentualen Wert berechnen (Wieviel Prozent davon sind erfüllt?
                $percent_of_completion = 0;
               
                if($pflicht_termine_an_werktagen > 0) {
                        $percent_of_completion = (100 / $pflicht_termine_an_werktagen) * $termine_count;
                }
               
                $max_100_percent_of_completion = $percent_of_completion;
               
                if($max_100_percent_of_completion > 100) {
                        $max_100_percent_of_completion = 100;
                }
               
                if($werktage == 0) {
                        $percent_of_completion = 100;
                        $max_100_percent_of_completion = 100;
                }
               
                if($pflicht_termine_an_werktagen == 0) {
                        $percent_of_completion = 100;
                        $max_100_percent_of_completion = 100;
                }
               
                $max_100_percent_of_completion = round($max_100_percent_of_completion);
                $percent_of_completion = round($percent_of_completion);
               
                $retval = array(
                        'werktage' => $werktage,
                        'pflicht_termine_an_werktagen' => $pflicht_termine_an_werktagen,
                        'termine_count' => $termine_count,
                        'percent_of_completion' => $percent_of_completion,
                        'max_100_percent_of_completion' => $max_100_percent_of_completion
                );
               
                return $retval;
        }
        /////////////////////////////////////////////////////////////////////////////////
        // Load some data that is always used.
        /////////////////////////////////////////////////////////////////////////////////
        private function loadTerminStatistik() {   
                $termin_statistik = array();               
                $iTermine = new cTermine();
								
                //today..
                $date_from = date('Y-m-d');
                $date_to = date('Y-m-d');
								$termin_statistik['termine_today_count'] = $this->loadTerminStatistikFuerHeuteByTime($_SESSION['seller_id'], $date_from, $date_to);
								$termine_heute_count = $iTermine->countSellersTermineFuerHeuteByStateAndTime($_SESSION['seller_id'], $date_from, $date_to, 0);
               
                //diese woche ab und inkl. morgen
                $date_from = date('Y-m-d', strtotime('+1days'));
                $day_of_week = date('w');
               
                if($day_of_week == 0) {
                        $date_to = date('Y-m-d');
                } else {
                        $remaining_days = 7 - $day_of_week;
                       
                        $date_to = date('Y-m-d', strtotime('+' . $remaining_days . 'days'));
								}
								
                $termin_statistik['termine_this_week_count'] = $this->loadTerminStatistikFuerZeitraum($_SESSION['seller_id'], $date_from, $date_to);
               
                //next 20 day ab und inkl. heute
                $day_of_week = date('w');
                $remaining_days = 7 - $day_of_week;
                $date_from = date('Y-m-d', strtotime('+' . $remaining_days . 'days'));
               
                $date_to = date('Y-m-d', strtotime('+30days'));
                $termin_statistik['termine_next_days_count'] = $this->loadTerminStatistikFuerZeitraum($_SESSION['seller_id'], $date_from, $date_to);
								
								//Alle weiteren Termine (weiter in der Zukunft als 30 Tage..
								$date_from = date('Y-m-d', strtotime('+1day', strtotime($date_to)));
								$date_to = '2035-01-01';
								$termin_statistik['termine_more_count'] = $this->loadTerminStatistikFuerZeitraum($_SESSION['seller_id'], $date_from, $date_to);
								
                //total..
                $date_from = date('Y-m-d', strtotime('+1days'));
                //$termine_open_total_count = $iTermine->countSellersTermineFrom($_SESSION['seller_id'], $date_from);
								$termine_open_tomorrow_till_twenty_days_count = $iTermine->countSellersTermineFromAndStatus($_SESSION['seller_id'], $date_from);
								$termine_open_total_count = $termine_heute_count + $termine_open_tomorrow_till_twenty_days_count;
				
                $termin_statistik['termine_open_total_count'] = $termine_open_total_count;
               
                return $termin_statistik;
        }
       
       
       
        /////////////////////////////////////////////////////////////////////////////////
        // Aktuell eingestellten Zeitpunkt laden.
        /////////////////////////////////////////////////////////////////////////////////
        private function loadEingestellterZeitpunkt() {
                if(!isset($_POST['set_time'])) {
                        return date('Y-m-d H:i:s');
                }
               
                return $_POST['set_time'];
        }
       
        /////////////////////////////////////////////////////////////////////////////////
        //Laden, welcher Tag gerade bearbeitet wird (Datum aus Parameter, Texte für den Tag, ..)
        /////////////////////////////////////////////////////////////////////////////////
        private function getTagInBearbeitung() {
                $iTime = new cTime();
               
                //Laden, welcher Tag gerade ausgewählt ist -> TODO!!!
                $day = strtotime($this->data['data']['eingestellter_zeitpunkt']);//time();
               
                //Tages-spezifische Texte laden: Info Text zur Beziehung des Tages abrufen: heute, morgen übermorgen...
                $tages_beziehungstext = $iTime->getTagesBeziehungstext($referenz_tag = $day, $heutiger_tag = time());
                $wochentag = $iTime->getWochentagText($day);
               
                return array(
                        'tages_beziehungstext' => $tages_beziehungstext,
                        'wochentag' => $wochentag,
                        'datum' => date('d.m.Y', $day),
                        'timestamp' => $day
                );
        }
       
        /////////////////////////////////////////////////////////////////////////////////
        //Laden, welcher Tag gerade bearbeitet wird (Datum aus Parameter, Texte für den Tag, ..)
        /////////////////////////////////////////////////////////////////////////////////
        private function getTermine() {
                $iTermine = new cTermine();
                $date = date('Y-m-d', strtotime($this->data['data']['eingestellter_zeitpunkt']));
                $termine = $iTermine->loadTermineBySellerIdAndDateAndStatus($_SESSION['seller_id'], $date, 0);
                return $termine;
        }
       
        //////////////////////////////////////////////////////////////////////////////
        // Anzeigen der eigentlichen Seite..
        //////////////////////////////////////////////////////////////////////////////
        public function renderPage() {
                $site_url = cSeourls::loadSeourlByQueryString('s=cSmartsellerDashboard');
                $site_url = ltrim($site_url, '/');
               
                //If message is set -> set to template
                $message = core()->getGetVar('message');
               
                //Load the CMS Entry for the login page.
                $cms = core()->getInstance('cCMS');
                $content = $cms->loadContentDataByKey('SMARTSELLER_DASHBOARD');
               
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
                $content['text'] = $renderer->fetch('site/dashboard.html');
               
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
                        /* "\n" . '<script src="//' . cSite::loadSiteUrl(core()->get('site_id')) . 'data/templates/' . $this->template . '/js/merlin_customer.js"></script>' . */
                        /* "\n" . '<script src="//' . cSite::loadSiteUrl(core()->get('site_id')) . 'data/templates/' . $this->template . '/js/dialer.js"></script>' . */
												"\n" . '<script src="//' . cSite::loadSiteUrl(core()->get('site_id')) . 'data/templates/' . $this->template . '/js/dialer_connector_2.js"></script>' .
                        "\n" . '<script src="//' . cSite::loadSiteUrl(core()->get('site_id')) . 'data/templates/' . $this->template . '/js/task-list-actions.js"></script>' .
                        "\n";
                $renderer = core()->getInstance('cRenderer');
                $renderer->renderText($additional_output);
        }
}

?>