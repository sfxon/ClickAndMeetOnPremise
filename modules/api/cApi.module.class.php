<?php

//TODO: implement auto loader for library?
require_once('Lib/cApiOutput.php');
//require_once('Lib/cApiSession.php');
require_once('Lib/cApiAccessToken.php');
require_once('Lib/cApiAppId.php');
require_once('Lib/cApiDeviceInfo.php');
require_once('Lib/cApiRefreshToken.php');
require_once('Lib/cApiTokens.php');
require_once('Lib/cApiUrl.php');

class cApi extends cModule {
		//////////////////////////////////////////////////////////////////////////////////
		// Hook us into the system
		//////////////////////////////////////////////////////////////////////////////////
		public static function setExecutionalHooks() {
				$core = core();
				
				//Now set our own hooks below the CMS hooks.
				core()->setHook('cCore|process', 'process');
		}
		
		//////////////////////////////////////////////////////////////////////////////////
		// Verarbeitung
		//////////////////////////////////////////////////////////////////////////////////
		public function process() {
				$action = core()->getGetVar('action');
				
				switch($action) {
						case 'create_account':
								require_once('Controllers/cApiCreateAccount.php');
								$iApiCreateAccount = new cApiCreateAccount();
								$iApiCreateAccount->process();
								break;
            case 'create_user_image':
								require_once('Controllers/cApiCreateUserImage.php');
								$iApiCreateUserImage = new cApiCreateUserImage();
								$iApiCreateUserImage->process();
								break;
						case 'delete_account':
								require_once('Controllers/cApiDeleteAccount.php');
								$iApiDeleteAccount = new cApiDeleteAccount();
								$iApiDeleteAccount->process();
								break;
						case 'delete_user_image':
								require_once('Controllers/cApiDeleteUserImage.php');
								$iApiDeleteUserImage = new cApiDeleteUserImage();
								$iApiDeleteUserImage->process();
								break;
						case 'estimate_user_age':
								require_once('Controllers/cApiEstimateUserAge.php');
								$iApiEstimateUserAge = new cApiEstimateUserAge();
								$iApiEstimateUserAge->process();
								break;
						case 'estimate_user_age_send_data':
								require_once('Controllers/cApiEstimateUserAgeSendData.php');
								$iApiEstimateUserAgeSendData = new cApiEstimateUserAgeSendData();
								$iApiEstimateUserAgeSendData->process();
								break;
						case 'get_account_delete_status':
								require_once('Controllers/cApiGetAccountDeleteStatus.php');
								$iApiGetAccountDeleteStatus = new cApiGetAccountDeleteStatus();
								$iApiGetAccountDeleteStatus->process();
								break;
						case 'get_user_data':
								require_once('Controllers/cApiGetUserData.php');
								$iApiGetUserData = new cApiGetUserData();
								$iApiGetUserData->process();
								break;
						case 'get_user_image_detail':
								require_once('Controllers/cApiGetUserImageDetail.php');
								$iApiGetUserImageDetail = new cApiGetUserImageDetail();
								$iApiGetUserImageDetail->process();
								break;
						case 'get_user_images_list':
								require_once('Controllers/cApiGetUserImagesList.php');
								$iApiGetUserImagesList = new cApiGetUserImagesList();
								$iApiGetUserImagesList->process();
								break;
						case 'login':
								require_once('Controllers/cApiLogin.php');
								$iApiLogin = new cApiLogin();
								$iApiLogin->login();
								break;
						case 'logout':
								require_once('Controllers/cApiLogout.php');
								$iApiLogout = new cApiLogout();
								$iApiLogout->process();
								break;
						case 'refresh_token':
								require_once('Controllers/cApiRefreshTokenController.php');
								$iApiRefreshToken = new cApiRefreshTokenController();
								$iApiRefreshToken->process();
								break;	
						case 'reset_password':
								require_once('Controllers/cApiResetPassword.php');
								$iApiResetPassword = new cApiResetPassword();
								$iApiResetPassword->process();
								break;
						case 'update_user_image':
								require_once('Controllers/cApiUpdateUserImage.php');
								$iApiUpdateUserImage = new cApiUpdateUserImage();
								$iApiUpdateUserImage->process();
								break;
						case 'update_user_settings':
								require_once('Controllers/cApiUpdateUserSettings.php');
								$iApiUpdateUserSettings = new cApiUpdateUserSettings();
								$iApiUpdateUserSettings->process();
								break;
				}
				
				//If we get here, no other message was processed. So just return the api status..
				$data = array(
						'api-status' => 'Api up and running.',
						'api-version' => '1.0',
						'api-release-date' => '2020-07-10'
				);
				cApiOutput::sendData($statuscode = 1, $data);
				die;
		}
}