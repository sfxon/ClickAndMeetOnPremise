<?php

//////////////////////////////////////////////////////////////////////////////////
// User Image über die API aktualisieren in der Datenbank.
// Erwartete Parameter:
// image_id -> ID des Bildes!
// age -> Alter größer 17 und kleiner 151
// user_message -> Textnachricht an den Bewerter -> darf auch eine leere Zeichenkette sein.
//////////////////////////////////////////////////////////////////////////////////
class cApiDeleteAccount {
		var $errors = array();
		
		//////////////////////////////////////////////////////////////////////////////////
		// Verarbeitung
		//////////////////////////////////////////////////////////////////////////////////
		public function process() {
				//Controller can only be used by logged in users. Check access rights!
				$iApiAccessToken = new cApiAccessToken();
				$user_id = $iApiAccessToken->checkAccessRightsByRequest();
				
				$iAccount = new cAccount();
				$account_data = $iAccount->loadById($user_id);
				
				//Token generieren und in Datenbank speichern.
				$iToken = new cToken();
				$token = $iToken->generate();
				
				$iAccount = new cAccount();
				$iAccount->update_deleteAccountToken_InDb($user_id, $token);
				$iAccount->update_deleteAccountOn_InDb($user_id, date('Y-m-d H:i:s'));
				
				//Mail versenden mit Token an die alte E-Mail Adresse, damit man diese im Zweifelsfall zurücksetzen kann.
				$this->sendDeleteAccountMail($account_data['email'], $token);
				
				//Success. 
				$retval = array(
						'status' => 'success',
				);
				cApiOutput::sendData($statuscode = 1, $retval);
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Send Delete Account Mail.
		///////////////////////////////////////////////////////////////////
		public function sendDeleteAccountMail($original_mail, $token) {
				$site_id = core()->get('site_id');							//ID of site (url based)
				$siteData = cSite::loadSiteData($site_id);	//Load URL by site id
				
				$url = $siteData['default_protocol'] . ':' . cCMS::loadTemplateUrl(core()->get('site_id')) . 'account-loeschen?action=confirm&tmp2=' . $token;
				
				//Bestätigungs-Mail versenden.
				$mail_text_plain = file_get_contents('data/mail_templates/delete-account-confirm.txt');
				$mail_text_html= file_get_contents('data/mail_templates/delete-account-confirm.html');
				
				//Make it non utf8 for the mail programs!
				$mail_text_plain = utf8_decode($mail_text_plain);
				$mail_text_html = utf8_decode($mail_text_html);
				
				//URL ersetzen
				$mail_text_plain = str_replace('{$URL}', $url, $mail_text_plain);
				$mail_text_html = str_replace('{$URL}', $url, $mail_text_html);
				
				//Send Mail to user about account creation
				$mailer = new mvPhpMailer();
				$mailer->init();
				
				$mailer->setFrom('mailfrom@mail...', 'Name');
				$mailer->addAddress($original_mail, $original_mail);
				$mailer->setSubject(utf8_decode('Bestätige die Löschung deines Accounts!'));
				$mailer->setPlainText($mail_text_plain);
				$mailer->setHTML($mail_text_html);
				$status = $mailer->send();
		}
}

