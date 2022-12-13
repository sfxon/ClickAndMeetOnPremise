<?php

class cApiLogin {
		var $wrongLoginText = 'Login war nicht erfolgreich. Bitte versuche es erneut!';
		var $accountNotActivated = 'Der Account wurde noch nicht aktiviert. Bitte prüfe dein Mail-Postfach.';
		var $accountIsBanned = 'Der Account wurde aus Sicherheitsgründen gesperrt. Wenn du nicht weißt wieso, wende dich bitte an uns.';
		var $appIdErrorText = 'Die App ID ist ungültig';
		var $accessTokenPubKeyErrorText = 'Der Pub Key für das Access Token ist ungültig';
		var $refreshTokenPubKeyErrorText = 'Der Pub Key für das Refresh Token ist ungültig';
		var $errors = array();
		
		public function login() {
				//We have no authentication routine yet - return 403 if not authenticated and an error json..
				$mail = core()->getPostVar('username');
				$pass = core()->getPostVar('password');
				$refresh_token_pub_key = core()->getPostVar('refresh_token_pub_key');
				$access_token_pub_key = core()->getPostVar('access_token_pub_key');
				$app_id = core()->getPostVar('app_id');
				$access_token_algorithm = core()->getPostVar('access_token_algorithm');
				$refresh_token_algorithm = core()->getPostVar('refresh_token_algorithm');
				$keep_logged_in = 1;
				$token = '';
				
				if(strlen($mail) == 0 || strlen($pass) == 0) {
						$this->errors['wrong_login_data'] = $this->wrongLoginText;
				}
						
				//Process Login
				if(count($this->errors) == 0) {
						$token = $this->doLogin($mail, $pass, $refresh_token_pub_key, $access_token_pub_key, $app_id, $keep_logged_in);
				}
				
				//If Login was successful.
				/*
				if('' !== $token && count($this->errors) == 0) {
						$data = array(
								'token' => $token
						);
						cApiOutput::sendData($statuscode = 1, $data);
						die;
				}
				*/
				
				//If Login was not successful.
				//http_response_code(403);
				cApiOutput::sendError($errorcode = 2, $errormessage = 'login not successful', $this->errors);
				die;
				
				/*
				http_response_code(403);
				cApiOutput::sendError($errorcode = 1, $errormessage = 'not authenticated');
				die;
				*/
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Processing a login.
		/////////////////////////////////////////////////////////////////////////////////
		private function doLogin($mail, $pass, $refresh_token_pub_key, $access_token_pub_key, $app_id, $keep_logged_in) {
				//Select user with this mail address from database.
				$account = new cAccount();
				$user_id = $account->loadAccountIdByEmail($mail);
				$token = false;
				
				if(false === $user_id) {
						$this->errors['wrong_login_data'] = $this->wrongLoginText;
						return false;
				}
				
				//Load additional user data..
				$user_data = $account->loadById($user_id);
				
				if(false === $user_data) {
						$this->errors['wrong_login_data'] = $this->wrongLoginText;
						return false;
				}
				
				//Check if user is activated
				if($user_data['account_type'] != 1 && $user_data['account_type'] != 2) {
						$this->errors['wrong_login_data'] = $this->accountNotActivated;
						return false;
				}
				
				//Check if user is banned
				if($user_data['is_banned'] == 1) {
						$this->errors['wrong_login_data'] = $this->accountIsBanned;
						return false;
				}
				
				//Check password..
				$password_hash = $user_data['password'];
				
				if(false === password_verify($pass, $password_hash)) {		//Aaaaaaand - you are logged in!
						$this->errors['wrong_login_data'] = $this->wrongLoginText;
						return false;
				}
				
				//Check app_id
				$iApiAppId = new cApiAppId();
				$result = $iApiAppId->checkForValidity($app_id);
				
				if(false === $result) {
						$this->errors['app_id_error'] = $this->appIdErrorText;
						return false;
				}
					
				//Check access_token_pub_key
				$iApiAccessToken = new cApiAccessToken();
				$result = $iApiAccessToken->checkPublicKeyFormatForValidity($access_token_pub_key);
				
				if(false === $result) {
						$this->errors['access_token_pub_key_error'] = $this->accessTokenPubKeyErrorText;
						return false;
				}


				//Check refresh_token_pub_key
				$iApiRefreshToken = new cApiRefreshToken();
				$result = $iApiRefreshToken->checkPublicKeyFormatForValidity($refresh_token_pub_key);
				
				if(false === $result) {
						$this->errors['refresh_token_pub_key_error'] = $this->refreshTokenPubKeyErrorText;
						return false;
				}
				
				//Invalid refresh_tokens and access_tokens by app_id.
				$iApiTokens = new cApiTokens();
				$iApiTokens->invalidateByAppId($app_id);
				
				//Invalidate access tokens older than 3 months (keep database clean..)
				$iApiAccessToken->invalidateOldAccessTokens();
				
				//Invalidate refresh tokens older than 3 months!
				$iApiRefreshToken->invalidateOldTokens();
				
				//Create Access token
				$access_token = $iApiAccessToken->create($app_id, $user_id, $access_token_pub_key);
				
				//Create Refresh token
				$refresh_token = $iApiRefreshToken->create($app_id, $user_id, $refresh_token_pub_key);
				
				//Return values on success
				$data = array(
						'access_token' => $access_token,
						'refresh_token' => $refresh_token
				);
				
				cApiOutput::sendData($statuscode = 1, $data);
				die;
		}
}