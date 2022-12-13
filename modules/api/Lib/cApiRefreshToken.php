<?php

//Refresh Token
//Used to generate an access token.
//Refresh token is used to get a new access token.
//Uses a very simple sign with a hash over posted values in sha256
//Hash key that is added is provided on login and refresh_token calls.
//Token is invalidated if too old (3 months) or 10 times wrong or by app_id (on login).
class cApiRefreshToken {
		var $signature_config = [
				"digest_alg" => "sha128",
				"private_key_bits" => 2048,
				"private_key_type" => OPENSSL_KEYTYPE_RSA,
		];
		var $sign_algorithm = OPENSSL_ALGO_SHA256;
		var $verify_algorithm_name = 'sha256WithRSAEncryption';
		
		/////////////////////////////////////////////////////////////////////////////////
		// Check public_key format for validity.
		// We just check if we got 512 digits.
		/////////////////////////////////////////////////////////////////////////////////
		public function checkPublicKeyFormatForValidity($access_token_pub_key) {
				if(strlen(trim($access_token_pub_key)) < 256) {		//Only whitespaces are not allowed
						return false;
				}
				
				return true;
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Invalidate token by app_id.
		/////////////////////////////////////////////////////////////////////////////////
		public function invalidateByAppId($app_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'DELETE FROM ' . $db->table('long_time_sessions') . ' ' .
						'WHERE ' .
								'app_id = :app_id'			
				);
				$db->bind(':app_id', $app_id);
				$result = $db->execute();
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Invalidate old tokens by time.
		// Invalidates the one that are older than 3 months.
		/////////////////////////////////////////////////////////////////////////////////
		public function invalidateOldTokens() {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'DELETE FROM ' . $db->table('long_time_sessions') . ' ' .
						'WHERE ' .
								'first_time_created < DATE_SUB(NOW(), INTERVAL 3 MONTH) AND ' .
								'app_id != \'\''
				);
				$result = $db->execute();
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Invalidate token by token.
		/////////////////////////////////////////////////////////////////////////////////
		public function invalidateByToken($token) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'DELETE FROM ' . $db->table('long_time_sessions') . ' ' .
						'WHERE ' .
								'token = :token'			
				);
				$db->bind(':token', $token);
				$result = $db->execute();
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Invalidate token by token.
		/////////////////////////////////////////////////////////////////////////////////
		public function invalidateByTokenAndUserId($token, $user_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'DELETE FROM ' . $db->table('long_time_sessions') . ' ' .
						'WHERE ' .
								'token = :token AND ' .
								'user_id = :user_id'			
				);
				$db->bind(':token', $token);
				$db->bind(':user_id', $user_id);
				$result = $db->execute();
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Create access token..
		/////////////////////////////////////////////////////////////////////////////////
		public function create($app_id, $user_id, $refresh_token_pub_key) {
				$refresh_token = "";
				
				$iApiTokens = new cApiTokens();
				
				//Get the user agent. We use the user agent as a security feature.
				//If it changes, we do log out this user,
				//because we think, this might be an unknown user..
				//We could alternatively send him an email for security!
				$iApiDeviceInfo = new cApiDeviceInfo();
				
				$user_agent = $iApiDeviceInfo->getUserAgentInfo();
				$system_type = $iApiDeviceInfo->getSystemType();
				
				do {
						//$token = $this->generateToken();		//Generate 40 Byte token.
						$refresh_token = $iApiTokens->generateNewToken();
				} while(false === $this->createInDatabase($refresh_token, $app_id, $user_id, $refresh_token_pub_key, $user_agent, $system_type));
				
				return $refresh_token;
		}
		
		/////////////////////////////////////////////////////////////////////
		// Long-Time Cookie-Session erstellen.
		/////////////////////////////////////////////////////////////////////
		public function createInDatabase($refresh_token, $app_id, $user_id, $refresh_token_pub_key, $user_agent, $system_type) {
				//Check if this token exists..
				if(false !== $this->checkIfEntryExistsByToken($refresh_token)) {
						return false;
				}
				
				//Create (if this fails, an error is risen automatically!
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'INSERT INTO ' . $db->table('long_time_sessions') . ' ' .
								'(token, session_id, user_id, app_id, user_agent, system_type, first_time_created, valid_until, pub_key, error_uses) ' .
						'VALUES ' .
								'(:token, :session_id, :user_id, :app_id, :user_agent, :system_type, NOW(), :valid_until, :pub_key, :error_uses);'
				);
				$db->bind(':token', $refresh_token);
				$db->bind(':session_id', "");
				$db->bind(':user_id', (int)$user_id);
				$db->bind(':app_id', $app_id);
				$db->bind(':user_agent', $user_agent);
				$db->bind(':system_type', $system_type);
				$db->bind(':valid_until', '9999-12-31 23:59:59');		//Wird interessant, wenn wir doch noch den Tod überlisten und endlos leben... :O
				$db->bind(':pub_key', $refresh_token_pub_key);
				$db->bind(':error_uses', 0);
				$result = $db->execute();
				
				return true;
		}
		
		/////////////////////////////////////////////////////////////////////
		// Prüfen, ob ein Eintrag existiert (anhand des Tokens).
		/////////////////////////////////////////////////////////////////////
		public function checkIfEntryExistsByToken($token) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('long_time_sessions') . ' WHERE ' .
						'token = :token;'				
				);
				$db->bind(':token', $token);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(false === $tmp) {
						return false;
				}
				
				return true;
		}
		
		/////////////////////////////////////////////////////////////////////
		// Lade einen Eintrag aus der Datenbank (anhand des Tokens).
		/////////////////////////////////////////////////////////////////////
		public function loadByToken($token) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('long_time_sessions') . ' WHERE ' .
						'token = :token;'				
				);
				$db->bind(':token', $token);
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
		public function updateTokenErrorUses($token, $error_uses) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('long_time_sessions') . ' SET ' .
								'error_uses = :error_uses ' .
						'WHERE ' .
								'token = :token'
				);
				$db->bind(':error_uses', (int)$error_uses);
				$db->bind(':token', $token);
				$result = $db->execute();
				
				return true;
		}
		
		/////////////////////////////////////////////////////////////////////
		// Verify signature
		/////////////////////////////////////////////////////////////////////
		public function verifySignature($data, $signature, $public_key_pem) {
				$result = openssl_verify($data, $signature, $public_key_pem, "sha256WithRSAEncryption");
				
				if($result == 1) {
						return true;
				}
				
				return false;
		}
		
		/////////////////////////////////////////////////////////////////////
		// Sign
		/////////////////////////////////////////////////////////////////////
		public function createSignature($data, $private_key_pem) {
				$signature = '';
				openssl_sign($data, $signature, $private_key_pem, $this->sign_algorithm);
				
				return $signature;
		}
		
		/////////////////////////////////////////////////////////////////////
		// Create keys (private and public key pair)
		// We use this only for debugging purpose.
		// In general, the program is generating the keys.
		// But we could in future also define keys for backward communication.
		// Then this function would be perfect!
		/////////////////////////////////////////////////////////////////////
		public function createKeys() {
				// Create the private and public key
				$res = openssl_pkey_new($this->signature_config);
				
				// Extract the private key from $res to $privKey
				$private_key_pem = '';
				openssl_pkey_export($res, $private_key_pem);
				
				// Extract the public key from $res to $pubKey
				$public_key_pem = '';
				$public_key_pem = openssl_pkey_get_details($res);
				$public_key_pem = $public_key_pem["key"];
				
				$keys = array(
						'private_key_pem' => $private_key_pem,
						'public_key_pem' => $public_key_pem
				);
						
				var_dump($keys);
		}
}