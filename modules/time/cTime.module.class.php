<?php

class cTime extends cModule {
		///////////////////////////////////////////////////////////////////////////
		// Tageszeit ermitteln (Morgens, Mittags, Abends, ...)
		//
		// Parameter:
		//		time 
		//				Kann einen Zeitstempel erhalten. Wenn nicht gesetzt,
		//				falls nicht gesetzt, wird die aktuelle Zeit des Systemes
		//				verwendet.
		///////////////////////////////////////////////////////////////////////////
		public function getTageszeit($time = NULL) {
				if(NULL === $time) {
						$time = time();
				}
				
				//Tageszeitabhängige Steuerung vorbereiten
				$tageszeit = 'Morgens';
				$stunde = date('H', $time);
				
				if($stunde < 10) {
						$tageszeit = 'Morgens';
				} else if($stunde) {
						$tageszeit = 'Vormittags';
				} else if($stunde < 14) {
						$tageszeit = 'Mittags';
				} else if($stunde < 18) {
						$tageszeit = 'Nachmittags';
				} else if($stunde < 22) {
						$tageszeit = 'Abends';
				} else {
						$tageszeit = 'Nachts';
				}
				
				return $tageszeit;
		}
		
		///////////////////////////////////////////////////////////////////////////
		// Lädt einen spezifischen Text, je nachdem, welcher Tag gerade ist,
		// und wie weit der Referenz-Tag davon entfernt ist.
		// Bspw.: heute, morgen, übermorgen, letzte Woche, ...
		///////////////////////////////////////////////////////////////////////////
		public function getTagesBeziehungstext($referenz_tag_timestamp, $heutiger_tag_timestamp) {
				$referenz_datum = date('Y-m-d', $referenz_tag_timestamp);
				$heutiger_tag_datum = date('Y-m-d', $heutiger_tag_timestamp);
				
				//////////////////////////////////////////////////////////
				// Werte für die folgenden Berechnungen zusammentragen.
				//////////////////////////////////////////////////////////
				//Jahreszahlen ermitteln..
				$referenz_jahr = date('Y', $referenz_tag_timestamp);
				$heutiger_tag_jahr = date('Y', $heutiger_tag_timestamp);
				
				$aktuelle_woche = date('W', $heutiger_tag_timestamp);
				$referenz_woche = date('W', $referenz_tag_timestamp);
				
				$referenz_monat = date('n', $referenz_tag_timestamp);
				$aktueller_monat = date('n', $heutiger_tag_timestamp);
				
				//Heute..
				//Datum prüfen..
				if($referenz_datum == $heutiger_tag_datum) {
						return 'heute';
				}
				
				//Daten der Vergangenheit.
				$gestern = date('Y-m-d', strtotime('-1 days', $heutiger_tag_timestamp));
				$vorgestern = date('Y-m-d', strtotime('-2 days', $heutiger_tag_timestamp));
				
				$letzte_woche = date('W', strtotime('-7 days', $heutiger_tag_timestamp));
				$letzte_woche_jahr = date('Y', strtotime('-7 days', $heutiger_tag_timestamp));
				$vor_2_wochen = date('W', strtotime('-14 days', $heutiger_tag_timestamp));
				$vor_2_wochen_jahr = date('Y', strtotime('-14 days', $heutiger_tag_timestamp));
				$vor_3_wochen = date('W', strtotime('-21 days', $heutiger_tag_timestamp));
				$vor_3_wochen_jahr = date('Y', strtotime('-21 days', $heutiger_tag_timestamp));
				$vor_4_wochen = date('W', strtotime('-28 days', $heutiger_tag_timestamp));
				$vor_4_wochen_jahr = date('Y', strtotime('-28 days', $heutiger_tag_timestamp));
				$vor_5_wochen = date('W', strtotime('-35 days', $heutiger_tag_timestamp));
				$vor_5_wochen_jahr = date('Y', strtotime('-35 days', $heutiger_tag_timestamp));
				
				
				$letzter_monat = date('m', strtotime('-1 months', $heutiger_tag_timestamp));
				$letzter_monat_jahr = date('m', strtotime('-1 months', $heutiger_tag_timestamp));
				
				$letztes_jahr = date('Y', strtotime('-1 years', $heutiger_tag_timestamp));
				
				//Daten der Zukunft.
				$morgen = date('Y-m-d', strtotime('+1 days', $heutiger_tag_timestamp));
				$uebermorgen = date('Y-m-d', strtotime('+2 days', $heutiger_tag_timestamp));
				
				$naechste_woche = date('W', strtotime('+7 days', $heutiger_tag_timestamp));
				$naechste_woche_jahr = date('Y', strtotime('+7 days', $heutiger_tag_timestamp));
				$in_2_wochen = date('W', strtotime('+14 days', $heutiger_tag_timestamp));
				$in_2_wochen_jahr = date('Y', strtotime('+14 days', $heutiger_tag_timestamp));
				$in_3_wochen = date('W', strtotime('+21 days', $heutiger_tag_timestamp));
				$in_3_wochen_jahr = date('Y', strtotime('+21 days', $heutiger_tag_timestamp));
				$in_4_wochen = date('W', strtotime('+28 days', $heutiger_tag_timestamp));
				$in_4_wochen_jahr = date('Y', strtotime('+28 days', $heutiger_tag_timestamp));
				$in_5_wochen = date('W', strtotime('+35 days', $heutiger_tag_timestamp));
				$in_5_wochen_jahr = date('Y', strtotime('+35 days', $heutiger_tag_timestamp));
				
				$naechster_monat = date('m', strtotime('+1 months', $heutiger_tag_timestamp));
				$naechster_monat_jahr = date('Y', strtotime('+1 months', $heutiger_tag_timestamp));
				
				$naechstes_jahr = date('Y', strtotime('+1 years', $heutiger_tag_timestamp));
				
				//////////////////////////////////////////////////////////
				// Termine davor..
				//////////////////////////////////////////////////////////
				//Wenn es gestern war..
				if($gestern == $referenz_datum) {
						return 'gestern';
				}
				
				//Wenn es vorgestern war..
				if($vorgestern == $referenz_datum) {
						return 'vorgestern';
				}
				
				//Wenn es im letzten Jahr oder davor war..
				if($referenz_jahr <  $heutiger_tag_jahr) {
						if($heutiger_tag_jahr - $referenz_jahr == 1) {
								return 'letztes Jahr';
						} else {
								return 'vor ' . ($heutiger_tag_jahr - $referenz_jahr) . ' Jahren';
						}
				}
				
				//Wenn es letzte Woche war..
				if($letzte_woche == $referenz_woche && $letzte_woche_jahr == $referenz_jahr) {
						return 'letzte Woche';
				}
				
				//Wenn es vor 2 Wochen war..
				if($vor_2_wochen == $referenz_woche && $vor_2_wochen_jahr == $referenz_jahr) {
						return 'vor 2 Wochen';
				}
				
				//Wenn es vor 3 Wochen war..
				if($vor_3_wochen == $referenz_woche && $vor_3_wochen_jahr == $referenz_jahr) {
						return 'vor 3 Wochen';
				}
				
				//Wenn es vor einem Monat war..
				if($vor_4_wochen == $referenz_woche && $vor_4_wochen_jahr == $referenz_jahr) {
						return 'letzten Monat';
				}
				
				//Wenn es vor einem Monat war..
				if($vor_5_wochen == $referenz_woche && $vor_5_wochen_jahr == $referenz_jahr) {
						return 'letzten Monat';
				}
				
				//Wenn es letzten Monat war..
				if($letzter_monat == $referenz_monat && $letzter_monat_jahr == $referenz_jahr) {
						return 'letzten Monat';
				}
				
				//Wenn es in den letzten Monaten war - in diesem Jahr..
				//if($referenz_monat < $letzter_monat && $referenz_jahr == $heutiger_tag_jahr) {
				if($referenz_monat < $letzter_monat && $referenz_jahr == $letzter_monat_jahr) {
						return 'vor ' . ($aktueller_monat - $referenz_monat) . ' Monaten';
				}
						
						
				//Wenn es in den letzten Monaten war - aber im letzten Jahr..		
				if($referenz_monat > $letzter_monat && $referenz_jahr == $letzter_monat_jahr - 1) {
						return 'vor ' . ($letzter_monat + (12 - $referenz_monat)) . ' Monaten..';
				}
				
				//Alle anderen - wenn es vor ... Tagen war..
				if($referenz_tag_timestamp < $heutiger_tag_timestamp) {
						$tag = $heutiger_tag_timestamp - $referenz_tag_timestamp;
						
						$tag = $tag / 365;
						$tag = round($tag, 0);
						$tag = $heutiger_tag_jahr - $tag;
						
						return 'vor ' . round($tag, 0) . ' Tagen';
				}
				
				//////////////////////////////////////////////////////////
				// Termine danach..
				//////////////////////////////////////////////////////////
				//Wenn es morgen ist..
				if($morgen == $referenz_datum) {
						return 'morgen';
				}
				
				//Wenn es übermorgen ist..
				if($uebermorgen == $referenz_datum) {
						return 'übermorgen';
				}
				
				//Wenn es nächste Woche ist.
				if($naechste_woche == $referenz_woche && $naechste_woche_jahr == $referenz_jahr) {
						return 'nächste Woche';
				}
				
				//Wenn es in 2 Wochen ist..
				if($in_2_wochen == $referenz_woche && $in_2_wochen_jahr == $referenz_jahr) {
						return 'in 2 Wochen';
				}
				
				//Wenn es in 3 Wochen ist..
				if($in_3_wochen == $referenz_woche && $in_3_wochen_jahr == $referenz_jahr) {
						return 'in 3 Wochen';
				}
				
				//Wenn es in einem Monat ist..
				if($in_4_wochen == $referenz_woche && $in_4_wochen_jahr == $referenz_jahr) {
						return 'nächsten Monat';
				}
				
				//Wenn es in einem Monat ist..
				if($in_5_wochen == $referenz_woche && $in_5_wochen_jahr == $referenz_jahr) {
						return 'nächsten Monat';
				}
				
				//Wenn es nächsten Monat ist..
				if($naechster_monat == $referenz_monat && $naechster_monat_jahr == $referenz_jahr) {
						return 'nächsten Monat';
				}
				
				//Wenn es in den nächsten Monaten ist, in diesem Jahr
				if($referenz_monat > $naechster_monat && $referenz_jahr == $heutiger_tag_jahr) {
						return 'in ' . ($referenz_monat - $aktueller_monat) . ' Monaten';
				}
						
						
				//Wenn es in den letzten Monaten war - aber im letzten Jahr..		
				if($referenz_monat < $letzter_monat && $referenz_jahr == $heutiger_tag_jahr + 1) {
						return 'in ' . ((12 - $letzter_monat) + $referenz_monat) . ' Monaten';
				}
				
				
				//Wenn es nächstes Jahr oder in den nächsten Jahren ist..
				if($referenz_jahr >  $heutiger_tag_jahr) {
						if($referenz_jahr - $heutiger_tag_jahr == 1) {
								return 'nächstes Jahr';
						} else {
								return 'in ' . ($referenz_jahr - $heutiger_tag_jahr) . ' Jahren';
						}
				}
				
				//Alle anderen - wenn es in ... Tagen ist..
				if($referenz_tag_timestamp > $heutiger_tag_timestamp) {
						$tag = $referenz_tag_timestamp - $heutiger_tag_timestamp;
						
						$tag = $tag / 365;
						
						return 'in ' . round($tag, 0) . ' Tagen';
				}
			
				return '...';		//Hier dürften wir theoretisch nicht landen..
		}
		
		///////////////////////////////////////////////////////////////////////////
		// Wochentag laden.
		///////////////////////////////////////////////////////////////////////////
		public function getWochentagText($day_timestamp) {
				$day = date('w', $day_timestamp);
				
				switch($day) {
						case 0:
								return 'Sonntag';
						case 1:
								return 'Montag';
						case 2:
								return 'Dienstag';
						case 3:
								return 'Mittwoch';
						case 4:
								return 'Donnerstag';
						case 5:
								return 'Freitag';
						case 6:
								return 'Samstag';
				}
				
				return '';
		}
}