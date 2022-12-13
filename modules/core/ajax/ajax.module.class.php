<?php

///////////////////////////////////////////////////////////////////////////////////
// This class is providing some basic functionality for ajax request handling
// and answering.
///////////////////////////////////////////////////////////////////////////////////

class cAjax extends cModule {
		///////////////////////////////////////////////////////////////////////////////
		// Return an error.
		///////////////////////////////////////////////////////////////////////////////
		public static function returnErrorAndQuit($error_code, $error_message) {
				
				$data = array(
						'result' => 'error',
						'error_code' => $error_code,
						'error_message' => $error_message
				);
				
				$json = json_encode($data);
				
				echo $json;
				die;
		}
		
		///////////////////////////////////////////////////////////////////////////////
		// Return success and quit
		///////////////////////////////////////////////////////////////////////////////
		public static function returnSuccessAndQuit($success_code, $success_message, $additional_parameters = false) {
				$data = array(
						'result' => 'success',
						'success_code' => $success_code,
						'message' => $success_message,
						'additional_parameters' => $additional_parameters
				);
				
				/*
				$json = json_encode($data, JSON_HEX_APOS|JSON_HEX_QUOT);
				$json = str_replace("\u0022","\\\\\"", $json);
				*/
				$json = json_encode($data);
				
				echo $json;
				die;
		}
}

?>