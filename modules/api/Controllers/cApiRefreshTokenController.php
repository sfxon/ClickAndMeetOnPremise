<?php

class cApiRefreshTokenController {
		//////////////////////////////////////////////////////////////////////////////////
		// Verarbeitung
		//////////////////////////////////////////////////////////////////////////////////
		public function process() {
				$access_token_pub_key = core()->getPostVar('access_token_pub_key');		//New access token pub key to check signed messages from client
				$refresh_token_pub_key = core()->getPostVar('refresh_token_pub_key');	//New refresh token pub key to check signed messages from client
				$refresh_token = core()->getPostVar('refresh_token');									//Current refresh token
				$refresh_sign = core()->getPostVar('refresh_sign');										//Signatur unter dem Request zur Prüfung der Validität der Nachricht.
				
				//Lösche alte Access Tokens (und damit auch dieses, falls es abgelaufen ist..)
				$iApiRefreshToken = new cApiRefreshToken();
				$iApiRefreshToken->invalidateOldTokens();
				
				//Datenbank-Abfrage: Daten für das Token abrufen.
				$refresh_token_data = $iApiRefreshToken->loadByToken($refresh_token);
				
				if(false === $refresh_token_data) {
						cApiOutput::sendError($errorcode = 7, $errormessage = 'invalid', array('info' => 'refresh token not found'));
						die;
				}
				
				if(!isset($_SERVER['HTTP_X_TELLFACE_APP_ID'])) {
						cApiOutput::sendError($errorcode = 7, $errormessage = 'invalid', array('info' => 'missing app_id in header'));
						die;
				}
				
				$app_id = $_SERVER['HTTP_X_TELLFACE_APP_ID'];
				
				//Signatur abrufen.
				//TODO: Wir könnten das hier erweitern, und bspw. prüfen, ob zweimal der gleiche Timestamp für dieses Token gesendet wurde.
				//Das müsste einen Fehler ergeben, weil das eben nicht möglich wäre!
				if(!isset($_SERVER['HTTP_X_TELLFACE_TIMESTAMP'])) {
						cApiOutput::sendError($errorcode = 7, $errormessage = 'invalid', array('info' => 'missing timestamp in header'));
						die;
				}
				
				$request_timestamp = $_SERVER['HTTP_X_TELLFACE_TIMESTAMP'];
						
				//Build the auth sign for comparison.
				$verify_string = $request_timestamp . $app_id . $refresh_token;
				
				/*
				$request_vars = array(
						'access_token_pub_key' => $access_token_pub_key,
						'refresh_token_pub_key' => $refresh_token_pub_key,
						'refresh_token' => $refresh_token
				);
				*/
				
				//Signatur in Binärformat umwandeln! Wurde für die Übertragung in Hex umgewandelt.
				$refresh_sign = base64_decode(trim($refresh_sign));
				
				//$tmp = $request_vars;
				//$tmp = json_encode($request_vars);
				$verify_result = $iApiRefreshToken->verifySignature($verify_string, $refresh_sign, $refresh_token_data['pub_key']);
				
				//Compare auth sign.
				if(true !== $verify_result) {
						//Wenn der Counter > 9 ist (also 10 -  wird das Token gelöscht)
						if($refresh_token_data['error_uses'] > 9) {
								$iApiRefreshToken->invalidateByToken($refresh_token);
						} else {
								//Counter um 1 erhöhen und Token in Datenbank damit aktualisieren.
								$error_uses = $refresh_token_data['error_uses'] + 1;
								$iApiRefreshToken->updateTokenErrorUses($refresh_token, $error_uses);
						}
					
						cApiOutput::sendError($errorcode = 7, $errormessage = 'invalid', array('info' => 'refresh sign invalid'));
						die;
				}
				
				//Invalid refresh_tokens and access_tokens by app_id.
				$iApiTokens = new cApiTokens();
				$iApiTokens->invalidateByAppId($refresh_token_data['app_id']);
				
				//Check access_token_pub_key
				$iApiAccessToken = new cApiAccessToken();
				$result = $iApiAccessToken->checkPublicKeyFormatForValidity($access_token_pub_key);
				
				if(false === $result) {
						cApiOutput::sendError($errorcode = 7, $errormessage = 'invalid', array('info' => 'access token public key format is invalid'));
						die;
				}


				//Check refresh_token_pub_key
				$iApiRefreshToken = new cApiRefreshToken();
				$result = $iApiRefreshToken->checkPublicKeyFormatForValidity($refresh_token_pub_key);
				
				if(false === $result) {
						cApiOutput::sendError($errorcode = 7, $errormessage = 'invalid', array('info' => 'private token public key format is invalid'));
						die;
				}
				
				//Create Access token
				$access_token = $iApiAccessToken->create($refresh_token_data['app_id'], $refresh_token_data['user_id'], $access_token_pub_key);
				
				//Create Refresh token
				$refresh_token = $iApiRefreshToken->create($refresh_token_data['app_id'], $refresh_token_data['user_id'], $refresh_token_pub_key);
				
				//Return values on success
				$data = array(
						'access_token' => $access_token,
						'refresh_token' => $refresh_token
				);
				
				cApiOutput::sendData($statuscode = 1, $data);
				die;
		}
}