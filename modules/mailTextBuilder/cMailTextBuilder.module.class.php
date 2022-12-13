<?php

/////////////////////////////////////////////////////////////////////////////////////
// E-Mail Texte zusammenstellen.
/////////////////////////////////////////////////////////////////////////////////////
class cMailtextBuilder extends cModule {
		var $mail_id_plain = 0;
		var $mail_id_html = 0;
		var $data = array();
		var $mail_text_plain = "";
		var $mail_text_html = "";
		var $mail_text_subject = "";
		
		////////////////////////////////////////////////////////////////////////////////
		// Erstellt das Objekt und verarbeitet gleichzeitig die Daten.
		////////////////////////////////////////////////////////////////////////////////
		public function __construct($mail_id_plain, $mail_id_html, $mail_id_subject, $data) {
				//Lade Texte
				$mail_text_plain = $this->loadMailTextById($mail_id_plain);
				$mail_text_html = $this->loadMailTextById($mail_id_html);
				
				$subject = $this->loadMailTextById($mail_id_subject);
				$this->mail_text_subject = $subject['email_text'];
				
				//Text aufbereiten.
				$this->mail_text_plain = $this->parseText($mail_text_plain['email_text'], $data);
				$this->mail_text_html = $this->parseText($mail_text_html['email_text'], $data);
		}
		
		////////////////////////////////////////////////////////////////////////////////
		// Mail-Texte aus Datenbank laden.
		////////////////////////////////////////////////////////////////////////////////
		private function loadMailTextById($mail_id_plain) {
				$iMailTextSettings = new cMailTextSettings();
				$mail_text = $iMailTextSettings->LoadById($mail_id_plain);
				
				return $mail_text;
		}
		
		////////////////////////////////////////////////////////////////////////////////
		// Variablen in Mailtexte einfügen.
		////////////////////////////////////////////////////////////////////////////////
		private function parseText($text, $data) {
				$w = array(
						array(
								'type' => 'text',
								'value' => $text
						)
				);
				
				foreach($data as $key => $value) {
						$new_w = array();
						
						foreach($w as $index => $part) {							
								if($part['type'] == 'text') {
										$tmp = explode('%' . $key . '%', $part['value']);
										
										$anzahl = count($tmp);
										
										if(is_array($tmp) && count($tmp) > 1) {
												$subarray = array();
												
												$i = 0;
												
												foreach($tmp as $k => $p) {
														$i++;
													
														$subarray = array(
																'type' => 'text',
																'value' => $p
														);
														
														$new_w[] = $subarray;
														
														if($i < $anzahl) {		//Exclude the last one, otherwise it will append all values at the end!
																$subarray = array(
																		'type' => 'replacement',
																		'value' => $value
																);
																
																$new_w[] = $subarray;
														}
														
														
												}
										} else {
												$new_w[] = $part;
										}
								} else {
										$new_w[] = $part;
								}
						}					
						
						
						if(count($new_w) > 0) {
								$w = $new_w;
						}
				}
				
				//Build final text
				$retval = '';
				
				foreach($w as $index => $part) {
						$retval .= $part['value'];
				}
				
				return $retval;
		}
		
}