<?php

class cApiTokens {
		/////////////////////////////////////////////////////////////////////////////////
		// Invalidate tokens by app_id.
		/////////////////////////////////////////////////////////////////////////////////
		public function invalidateByAppId($app_id) {
				$iApiAccessToken = new cApiAccessToken();
				$iApiAccessToken->invalidateByAppId($app_id);
				
				$iApiRefreshToken = new cApiRefreshToken();
				$iApiRefreshToken->invalidateByAppId($app_id);
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Generate a random token.
		/////////////////////////////////////////////////////////////////////////////////
		public function generateNewToken() {
				$token = bin2hex(random_bytes(40));
				return $token;
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Invalidate by tokens and user_id
		/////////////////////////////////////////////////////////////////////////////////
		public function invalidateByTokensAndUserId($access_token, $refresh_token, $user_id) {
				$iApiAccessToken = new cApiAccessToken();
				$iApiAccessToken->invalidateByTokenAndUserId($access_token, $user_id);
				
				$iApiRefreshToken = new cApiRefreshToken();
				$iApiRefreshToken->invalidateByTokenAndUserId($refresh_token, $user_id);
		}
}