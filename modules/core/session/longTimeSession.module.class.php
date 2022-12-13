<?php

class cLongTimeSession {
		/////////////////////////////////////////////////////////////////////
		// Cookie aktualisieren
		/////////////////////////////////////////////////////////////////////
		function update($old_token, $user_id) {
				//Get the user agent. We use the user agent as a security feature.
				//If it changes, we do log out this user,
				//because we think, this might be an unknown user..
				//We could alternatively send him an email for security!
				$user_agent = $this->getUserAgentInfo();
				
				//Get session id
				$session_id = session_id();
				
				do {
						$token = $this->generateToken();		//Generate 256 bit token.
				} while(false === $this->updateInDatabase($old_token, $token, $session_id, $user_id, $user_agent));
				
				return $token;
						
		}
		
		/////////////////////////////////////////////////////////////////////
		// Cookie erstellen
		/////////////////////////////////////////////////////////////////////
		function create($user_id) {
				//Get the user agent. We use the user agent as a security feature.
				//If it changes, we do log out this user,
				//because we think, this might be an unknown user..
				//We could alternatively send him an email for security!
				$user_agent = $this->getUserAgentInfo();
				
				//Get session id
				$session_id = session_id();
				
				//Create the token and try to insert it into the database
				do {
						$token = $this->generateToken();		//Generate 256 bit token.
				} while(false === $this->createInDatabase($token, $session_id, $user_id, $user_agent));
				
				return $token;
		}
		
		/////////////////////////////////////////////////////////////////////
		// Generate 256 bit token.
		/////////////////////////////////////////////////////////////////////
		public function generateToken() {
				return hash('sha256', time() . rand());
		}
		
		/////////////////////////////////////////////////////////////////////
		// User-Agent Informationen auslesen.
		/////////////////////////////////////////////////////////////////////
		public function getUserAgentInfo() {
				$browser = get_browser();
				$user_agent = '';
				
				if(isset($browser->browser)) {
						$user_agent .= $browser->browser;
				}
				
				if(isset($browser->platform)) {
						if(strlen($user_agent) > 0) {
								$user_agent .= '-';
						}
						
						$user_agent .= $browser->platform;
				}
				
				if(isset($browser->device_type)) {
						if(strlen($user_agent) > 0) {
								$user_agent .= '-';
						}
						
						$user_agent .= $browser->device_type;
				}
				
				return $user_agent;
		}
		
		/////////////////////////////////////////////////////////////////////
		// Long-Time Cookie-Session erstellen.
		/////////////////////////////////////////////////////////////////////
		public function createInDatabase($token, $session_id, $user_id, $user_agent) {
				//Check if this token exists..
				if(false !== $this->checkIfEntryExistsByToken($token)) {
						return false;
				}
				
				//Create (if this fails, an error is risen automatically!
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'INSERT INTO ' . $db->table('long_time_sessions') . ' ' .
								'(token, session_id, user_id, user_agent, first_time_created, valid_until, system_type) ' .
						'VALUES ' .
								'(:token, :session_id, :user_id, :user_agent, NOW(), :valid_until, \'Browser/Web\');'				
				);
				$db->bind(':token', $token);
				$db->bind(':session_id', $session_id);
				$db->bind(':user_id', (int)$user_id);
				$db->bind(':user_agent', $user_agent);
				$db->bind(':valid_until', date('Y-m-d H:i:s', $this->getCookieLifetime()));
				$result = $db->execute();
				
				return true;
		}
		
		/////////////////////////////////////////////////////////////////////
		// Long-Time Cookie-Session aktualisieren.
		/////////////////////////////////////////////////////////////////////
		public function updateInDatabase($old_token, $token, $session_id, $user_id, $user_agent) {
				//Check if this token exists..
				if(false !== $this->checkIfEntryExistsByToken($token)) {
						return false;
				}
				
				//Create (if this fails, an error is risen automatically!
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('long_time_sessions') . ' SET ' .
								'token = :token, ' .
								'session_id = :session_id, ' .
								'user_id = :user_id, ' .
								'user_agent = :user_agent, ' . 
								'valid_until = :valid_until ' .
								'system_type = \'Browser/Web\' ' .
						'WHERE ' .
								'token = :old_token'			
				);
				$db->bind(':token', $token);
				$db->bind(':session_id', $session_id);
				$db->bind(':user_id', (int)$user_id);
				$db->bind(':user_agent', $user_agent);
				$db->bind(':valid_until', date('Y-m-d H:i:s', $this->getCookieLifetime()));
				$db->bind(':old_token', $old_token);
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
		public function loadById($token) {
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
		// Cookie Lifetime ausleben.
		/////////////////////////////////////////////////////////////////////
		public function getCookieLifetime() {
				return time()+86400*30;
		}
		
		/////////////////////////////////////////////////////////////////////
		// Alle LongTimeSessions für diesen User beenden!
		/////////////////////////////////////////////////////////////////////
		public function logoutAllInstancesForUser($user_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'DELETE FROM ' . $db->table('long_time_sessions') . ' ' .
						'WHERE ' .
								'user_id = :user_id'			
				);
				$db->bind(':user_id', (int)$user_id);
				$result = $db->execute();
		}
}