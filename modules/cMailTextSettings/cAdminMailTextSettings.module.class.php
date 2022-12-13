<?php

require_once('master/cAdminMailTextSettingsMaster.master.class.php');

///////////////////////////////////////////////////////////////////////////////////////
// Use this class to implement your own code to enhance the class.
///////////////////////////////////////////////////////////////////////////////////////
class cAdminMailTextSettings extends cAdminMailTextSettingsMaster {
		//////////////////////////////////////////////////////////////////////////////////
		// Hook us into the system
		//////////////////////////////////////////////////////////////////////////////////
		public static function setExecutionalHooks() {
				//If the user is not logged in..
				if(!isset($_SESSION['user_id'])) {
						header('Location: index.php/account');
						die;
				}
				
				//check the rights..
				if(false === cAccount::adminrightCheck('cAdminMailTextSettings', 'USE_MODULE', (int)$_SESSION['user_id'])) {
						header('Location: index.php?s=cAdmin&error=304');
						die;
				}
				
				//We use the Admin module for output.
				cAdmin::setSmallBodyExecutionalHooks();	
				
				//Now set our own hooks below the CMS hooks.
				$core = core();
				core()->setHook('cCore|process', 'process');
				core()->setHook('cRenderer|content', 'content');
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the page content.
		///////////////////////////////////////////////////////////////////
		public function content() {
				switch($this->action) {
						case 'confirm_delete':
								$this->drawConfirmDeleteDialog();
								break;
						case 'new':
								$this->drawEditor();
								break;
						case 'edit':
								$this->drawEditor();
								break;
						default:
								$this->drawList();
								break;
				}
		}
		
		
		///////////////////////////////////////////////////////////////////
		// Draw the editor.
		///////////////////////////////////////////////////////////////////
		public function drawEditor() {
				$renderer = core()->getInstance('cRenderer');
        $original_template_path = $renderer->getTemplatePath();
				$renderer->setTemplatePath($this->getTemplatePath());
        $renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$renderer->render('editor_override.html');
        
        $renderer->setTemplatePath($original_template_path);
		}
}

?>