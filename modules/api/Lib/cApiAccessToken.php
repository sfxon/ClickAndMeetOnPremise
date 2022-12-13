<?php

//Access Token
//Access tokens are used to access specific data.
//Used to generate an access token.
//Uses a very simple sign with a hash over posted values in sha1
//Hash key that is added is provided on login and refresh_token calls.
//Token is invalidated if too old (5 Minutes) or 10 times not wrong or by app_id (on login).
class cApiAccessToken {
		/////////////////////////////////////////////////////////////////////////////////
		// Check public_key format for validity.
		// We just check if we got 32 digits.
		/////////////////////////////////////////////////////////////////////////////////
		public function checkPublicKeyFormatForValidity($access_token_pub_key) {
				if(strlen(trim($access_token_pub_key)) < 32) {		//Only whitespaces are not allowed
						return false;
				}
				
				return true;
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Invalidate token by user_id.
		/////////////////////////////////////////////////////////////////////////////////
		public function invalidateByUserId($user_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'DELETE FROM ' . $db->table('api_access_tokens') . ' ' .
						'WHERE ' .
								'user_id = :user_id'			
				);
				$db->bind(':user_id', $user_id);
				$result = $db->execute();
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Invalidate token by app_id.
		/////////////////////////////////////////////////////////////////////////////////
		public function invalidateByAppId($app_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'DELETE FROM ' . $db->table('api_access_tokens') . ' ' .
						'WHERE ' .
								'app_id = :app_id'			
				);
				$db->bind(':app_id', $app_id);
				$result = $db->execute();
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Invalidate token by token
		/////////////////////////////////////////////////////////////////////////////////
		public function invalidateByToken($access_token) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'DELETE FROM ' . $db->table('api_access_tokens') . ' ' .
						'WHERE ' .
								'access_token = :access_token'			
				);
				$db->bind(':access_token', $access_token);
				$result = $db->execute();
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Invalidate token by token
		/////////////////////////////////////////////////////////////////////////////////
		public function invalidateByTokenAndUserId($access_token, $user_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'DELETE FROM ' . $db->table('api_access_tokens') . ' ' .
						'WHERE ' .
								'access_token = :access_token AND ' .
								'user_id = :user_id'
				);
				$db->bind(':access_token', $access_token);
				$db->bind(':user_id', $user_id);
				$result = $db->execute();
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Invalidate old access_tokens by time.
		// Invalidates the one that are older than 5 minutes.
		/////////////////////////////////////////////////////////////////////////////////
		public function invalidateOldAccessTokens() {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'DELETE FROM ' . $db->table('api_access_tokens') . ' ' .
						'WHERE ' .
								'expires < DATE_SUB(NOW(), INTERVAL 5 MINUTE)'			
				);
				$result = $db->execute();
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Create access token..
		/////////////////////////////////////////////////////////////////////////////////
		public function create($app_id, $user_id, $access_token_pub_key) {
				$access_token = "";
				$iApiTokens = new cApiTokens();
				
				do {
						//$token = $this->generateToken();		//Generate 40 Byte token.
						$access_token = $iApiTokens->generateNewToken();
				} while(false === $this->createInDatabase($access_token, $app_id, $user_id, $access_token_pub_key));
				
				return $access_token;
		}
		
		/////////////////////////////////////////////////////////////////////
		// Long-Time Cookie-Session erstellen.
		/////////////////////////////////////////////////////////////////////
		public function createInDatabase($access_token, $app_id, $user_id, $access_token_pub_key) {
				//Check if this token exists..
				if(false !== $this->checkIfEntryExistsByToken($access_token)) {
						return false;
				}
				
				//Create (if this fails, an error is risen automatically!
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'INSERT INTO ' . $db->table('api_access_tokens') . ' ' .
								'(access_token, app_id, user_id, expires, pub_key, error_uses) ' .
						'VALUES ' .
								'(:access_token, :app_id, :user_id, NOW(), :pub_key, :error_uses);'
				);
				$db->bind(':access_token', $access_token);
				$db->bind(':app_id', $app_id);
				$db->bind(':user_id', (int)$user_id);
				$db->bind(':pub_key', $access_token_pub_key);
				$db->bind(':error_uses', 0);
				$result = $db->execute();
				
				return true;
		}
		
		/////////////////////////////////////////////////////////////////////
		// Prüfen, ob ein Eintrag existiert (anhand des Tokens).
		/////////////////////////////////////////////////////////////////////
		public function checkIfEntryExistsByToken($access_token) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('api_access_tokens') . ' WHERE ' .
						'access_token = :access_token;'				
				);
				$db->bind(':access_token', $access_token);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(false === $tmp) {
						return false;
				}
				
				return true;
		}
		
		/////////////////////////////////////////////////////////////////////
		// Prüfen, ob Benutzer eingeloggt ist und ausreichend Rechte hat.
		// Return Value: Returns the user_id..
		/////////////////////////////////////////////////////////////////////
		public function checkAccessRightsByRequest() {
				//Auth-Token entgegennehmen
				if(!isset($_SERVER['HTTP_X_TELLFACE_ACCESS_TOKEN'])) {
						cApiOutput::sendError($errorcode = 3, $errormessage = 'missing access token in header');
						die;
				}
				
				$access_token = $_SERVER['HTTP_X_TELLFACE_ACCESS_TOKEN'];
				
				//Signatur abrufen.
				if(!isset($_SERVER['HTTP_X_TELLFACE_ACCESS_SIGN'])) {
						cApiOutput::sendError($errorcode = 4, $errormessage = 'missing access sign in header');
						die;
				}
				
				$access_sign = $_SERVER['HTTP_X_TELLFACE_ACCESS_SIGN'];
				
				//Signatur abrufen.
				if(!isset($_SERVER['HTTP_X_TELLFACE_TIMESTAMP'])) {
						cApiOutput::sendError($errorcode = 4, $errormessage = 'missing timestamp in header');
						die;
				}
				
				$timestamp = $_SERVER['HTTP_X_TELLFACE_TIMESTAMP'];
				
				//Lösche alte Access Tokens (und damit auch dieses, falls es abgelaufen ist..
				$this->invalidateOldAccessTokens();
				
				//Datenbank-Abfrage: Daten für das Token abrufen.
				$access_token_data = $this->loadByToken($access_token);
				
				if(false === $access_token_data) {
						cApiOutput::sendError($errorcode = 5, $errormessage = 'token not found');
						die;
				}
						
				//Build the auth sign for comparison.
				/*
				$tmp = array_merge($_GET, $_POST);
				$tmp = json_encode($tmp);
				$tmp .= $access_token_data['pub_key'];
				$tmp = sha1($tmp);
				*/
				$calculated_sign = trim(
						$timestamp .
						//Add additional stuff here - but wo do not have that yet...
						$access_token_data['app_id'] .
						$access_token_data['pub_key']
				);
						
				$calculated_sign = sha1($calculated_sign);
				
				//Compare auth sign.
				if($access_sign != $calculated_sign) {
						//Wenn der Counter > 9 ist (also 10 -  wird das Token gelöscht)
						if($access_token_data['error_uses'] > 9) {
								$this->invalidateByToken($access_token);
						} else {
								//Counter um 1 erhöhen und Token in Datenbank damit aktualisieren.
								$error_uses = $access_token_data['error_uses'] + 1;
								$this->updateTokenErrorUses($access_token, $error_uses);
						}
					
						cApiOutput::sendError($errorcode = 6, $errormessage = 'access sign ist invalid');
						die;
				}
				
				return $access_token_data['user_id'];
		}
		
		
		/////////////////////////////////////////////////////////////////////
		// Lade einen Eintrag aus der Datenbank (anhand des Tokens).
		/////////////////////////////////////////////////////////////////////
		public function loadByToken($access_token) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('api_access_tokens') . ' WHERE ' .
						'access_token = :access_token;'				
				);
				$db->bind(':access_token', $access_token);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(false === $tmp) {
						return false;
				}
				
				return $tmp;
		}
		
		/////////////////////////////////////////////////////////////////////
		// Token Fehlerzähler aktualisieren
		/////////////////////////////////////////////////////////////////////
		public function updateTokenErrorUses($access_token, $error_uses) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('api_access_tokens') . ' SET ' .
								'error_uses = :error_uses ' .
						'WHERE ' .
								'access_token = :access_token'
				);
				$db->bind(':error_uses', (int)$error_uses);
				$db->bind(':access_token', $access_token);
				$result = $db->execute();
				
				return true;
		}
	
}