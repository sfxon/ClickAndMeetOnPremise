<?php

class cApiAppId {
		/////////////////////////////////////////////////////////////////////////////////
		// App id prüfen.
		// Minimale app_id Länge ergibt sich so:
		//	Benutzername(min3)_Gerät(min3)_timestamp(min5) + Unterstriche => 13
		/////////////////////////////////////////////////////////////////////////////////
		public function checkForValidity($app_id) {
				if(strlen(trim($app_id)) < 13) {		//Only whitespaces are not allowed!
						return false;
				}
				
				return true;
		}
		
		
}