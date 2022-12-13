<?php

class cRecoverPasswordService extends cModule {
		///////////////////////////////////////////////////////////////////////////////////
		// Schritt 1 des Passwort-Recovery-Vorganges
		//		Erstellung eines Tokens. Versenden einer E-Mail an den Account-Inhaber.
		//		ACHTUNG! Wenn der Inhaber seine E-Mail Adresse zuletzt geändert hat,
		//		wird diese Mail an die alte E-Mail Adresse gesendet - zusammen mit einer Warnung!
		///////////////////////////////////////////////////////////////////////////////////
		public function step1($mail) {
				if(strlen($mail) < 5) {
						return false;
				}
				
				//Check if mail exists in normal user address or mail-change address table entries.
				$iAccount = new cAccount();
				
				if(false === $iAccount->isMailAddressInUse($mail)) {
						return false;
				}
				
				//Get user id..
				$account_id = $iAccount->loadAccountIdByEmail($mail);
				
				if(false === $account_id) {
						$account_id = $iAccount->loadAccountIdByOriginalMail($mail);
				}
				
				if(false === $account_id) {
						return false;
				}
				
				//Generate token
				$iToken = new cToken();
				$token = $iToken->generate();
				
				//Save token and timestamp in database.
				$iAccount->updateRecoverPasswordToken($account_id, $token);
				$iAccount->updateRecoverPasswordOn($account_id, date('Y-m-d H:i:s'));
				
				//Hat der Nutzer seine E-Mail Adresse in den letzten Tagen geändert?
				$account_data = $iAccount->loadById($account_id);
				
				//Ja:
				//Send email to old account for security reasons with warning!
				if(false === $iAccount->isLastMailChangeOlderThanWeeks($account_id, $week_count = 4) && $account_data['original_mail'] != '') {
						$this->sendSecurityWarningMailToOldAccount($account_data['original_mail'], $account_id, $account_data['new_mail_reset_key']);
				}
				
				//Send email to current account with key..
				$this->sendPasswordRecoveryMail($mail, $account_id, $token);
		}
		
		///////////////////////////////////////////////////////////////////////////////////
		// Sicherheits-Warnung versenden.
		// Wenn die E-Mail in den letzten 4 Wochen geändert wurde,
		// und jetzt das Passwort geändert werden soll,
		// senden wir diese Sicherheitswarnung an den alten Account!
		///////////////////////////////////////////////////////////////////////////////////
		public function sendSecurityWarningMailToOldAccount($original_mail, $account_id, $new_mail_reset_key) {
				$site_id = core()->get('site_id');							//ID of site (url based)
				$siteData = cSite::loadSiteData($site_id);	//Load URL by site id
				
				$url = $siteData['default_protocol'] . ':' . cCMS::loadTemplateUrl(core()->get('site_id')) . 'recovermailaccount?tmp1=' . $account_id . '&tmp2=' . $new_mail_reset_key;
				
				//Bestätigungs-Mail versenden.
				$mail_text_plain = file_get_contents('data/mail_templates/recover_password_security_warning_for_old_account.txt');
				$mail_text_html= file_get_contents('data/mail_templates/recover_password_security_warning_for_old_account.html');
				
				//Make it non utf8 for the mail programs!
				$mail_text_plain = utf8_decode($mail_text_plain);
				$mail_text_html = utf8_decode($mail_text_html);
				
				//URL ersetzen
				$mail_text_plain = str_replace('{$URL}', $url, $mail_text_plain);
				$mail_text_html = str_replace('{$URL}', $url, $mail_text_html);
				
				/*
				$mail_text_plain = str_replace('{$ORIGINAL_MAIL}', $original_mail, $mail_text_plain);
				$mail_text_html = str_replace('{$ORIGINAL_MAIL}', $original_mail, $mail_text_html);
				
				$mail_text_plain = str_replace('{$NEW_MAIL}', $new_mail, $mail_text_plain);
				$mail_text_html = str_replace('{$NEW_MAIL}', $new_mail, $mail_text_html);
				*/
				
				//Send Mail to user about account creation
				$mailer = new mvPhpMailer();
				$mailer->init();
				
				$mailer->setFrom('mailfrom@mail...', 'Name');
				$mailer->addAddress($original_mail, $original_mail);
				$mailer->setSubject(utf8_decode('Sicherheitswarnung! Eine Passwort-Änderung wurde angefordert, nachdem zuvor schon deine E-Mail Adresse geändert wurde'));
				$mailer->setPlainText($mail_text_plain);
				$mailer->setHTML($mail_text_html);
				$status = $mailer->send();
		}
		
		///////////////////////////////////////////////////////////////////////////////////
		// Recovery-Mail versenden.
		// Wenn der Benutzer sein Passwort ändern möchte, ohne eingeloggt zu sein.
		///////////////////////////////////////////////////////////////////////////////////
		public function sendPasswordRecoveryMail($mail, $account_id, $token) {
				$site_id = core()->get('site_id');							//ID of site (url based)
				$siteData = cSite::loadSiteData($site_id);	//Load URL by site id
				
				$url = $siteData['default_protocol'] . ':' . cCMS::loadTemplateUrl(core()->get('site_id')) . 'reset-password?action=recover&tmp=' . $account_id . '&tmp2=' . $token;
				
				//Bestätigungs-Mail versenden.
				$mail_text_plain = file_get_contents('data/mail_templates/password_recovery.txt');
				$mail_text_html= file_get_contents('data/mail_templates/password_recovery.html');
				
				//Make it non utf8 for the mail programs!
				$mail_text_plain = utf8_decode($mail_text_plain);
				$mail_text_html = utf8_decode($mail_text_html);
				
				//URL ersetzen
				$mail_text_plain = str_replace('{$URL}', $url, $mail_text_plain);
				$mail_text_html = str_replace('{$URL}', $url, $mail_text_html);
				
				/*
				$mail_text_plain = str_replace('{$ORIGINAL_MAIL}', $original_mail, $mail_text_plain);
				$mail_text_html = str_replace('{$ORIGINAL_MAIL}', $original_mail, $mail_text_html);
				
				$mail_text_plain = str_replace('{$NEW_MAIL}', $new_mail, $mail_text_plain);
				$mail_text_html = str_replace('{$NEW_MAIL}', $new_mail, $mail_text_html);
				*/
				
				//Send Mail to user about account creation
				$mailer = new mvPhpMailer();
				$mailer->init();
				
				$mailer->setFrom('mailfrom@mail...', 'Name');
				$mailer->addAddress($mail, $mail);
				$mailer->setSubject(utf8_decode('Informationen zum Zurücksetzen deines Passwortes bei tellface.com'));
				$mailer->setPlainText($mail_text_plain);
				$mailer->setHTML($mail_text_html);
				$status = $mailer->send();
		}
}