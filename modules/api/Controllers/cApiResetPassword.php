<?php

//////////////////////////////////////////////////////////////////////////////////
// Passworterneuerung angefordert.
//////////////////////////////////////////////////////////////////////////////////
class cApiResetPassword {
		//////////////////////////////////////////////////////////////////////////////////
		// Verarbeitung
		//////////////////////////////////////////////////////////////////////////////////
		public function process() {
				$mail = core()->getPostVar('email');
						
				$iRecoverPasswordService = new cRecoverPasswordService();
				$status = $iRecoverPasswordService->step1($mail);
				
				//Rückgabe zusammensetzen
				$retval = array(
						'status' => 'success'
				);
				cApiOutput::sendData($statuscode = 1, $retval);
				die;
		}
}