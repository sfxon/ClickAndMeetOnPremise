<?php

class cAdminCSSEditor extends cModule {
		var $template = 'admin';
		var $navbar_title = TEXT_MODULE_TITLE__ADMIN_CSS_EDITOR;
		var $navbar_id = 0;
		var $errors = array();
		var $errors_description = array();
		var $results = array();
		var $data;
		
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
				if(false === cAccount::adminrightCheck('cAdminCSSEditor', 'USE_MODULE', (int)$_SESSION['user_id'])) {
						header('Location: index.php?s=cAdmin&error=261');
						die;
				}
				
				//We use the Admin module for output.
				cAdmin::setSmallBodyExecutionalHooks();	
				
				//Now set our own hooks below the CMS hooks.
				$core = core();
				core()->setHook('cCore|process', 'process');
				core()->setHook('cRenderer|content', 'content');
				core()->setHook('cRenderer|footer', 'footer');
		}
	
	
		///////////////////////////////////////////////////////////////////
		// processData
		///////////////////////////////////////////////////////////////////
		function process() {
				$this->action = core()->getGetVar('action');
				$cAdmin = core()->getInstance('cAdmin');
				$cAdmin->appendBreadcrumb(TEXT_MODULE_TITLE__ADMIN_CSS_EDITOR, 'index.php?s=cAdminCSSEditor');
				
				$this->initData();
				
				switch($this->action) {
						case 'update':
								$this->update();
								break;		
						default:
								$this->data['url'] = 'index.php?s=cAdminCSSEditor&amp;action=update';
								break;
				}
		}
				
		
		///////////////////////////////////////////////////////////////////
		// Prepare data for the editor.
		///////////////////////////////////////////////////////////////////
		function initData() {
				$filename = 'data/templates/blitz2016/css/mindfav.css';
				
				$fp = fopen($filename, 'r');
				$data = fread($fp, 50000);
				fclose($fp);
				
				$this->data['data']['css'] = $data;
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the page content.
		///////////////////////////////////////////////////////////////////
		public function content() {
				switch($this->action) {
						default:
								$this->drawEditor();
								break;
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the editor.
		///////////////////////////////////////////////////////////////////
		function drawEditor() {
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$renderer->render('site/AdminCSSEditor/editor.html');
		}
		
		///////////////////////////////////////////////////////////////////
		// update an entry
		///////////////////////////////////////////////////////////////////
		public function update() {
				//Make backup of old css file.
				$history_filename = 'data/tmp/css_history/mindfav.css.' . date('Y-m-d_H-i-s') . '__' . uniqid() . '.css';
				$mindfav_css_filename = 'data/templates/blitz2016/css/mindfav.css';
				
				copy($mindfav_css_filename, $history_filename);
				
				//Create new css file.
				$css = core()->getPostVar('css');
				
				usleep(200);
				
				$fp = fopen($mindfav_css_filename, 'w');
				fwrite($fp, $css);
				fclose($fp);
				
				header('Location: index.php?s=cAdminCSSEditor');
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the page footer.
		///////////////////////////////////////////////////////////////////
		public function footer() {
				$additional_output = 
						"\n" . '<script src="data/templates/' . $this->template . '/js/codemirror/codemirror.js"></script>' .
						"\n" . '<link rel="stylesheet" href="data/templates/' . $this->template . '/js/codemirror/codemirror.css">' .
						"\n" . '<script src="data/templates/' . $this->template . '/js/codemirror/mode/css/css.js"></script>' .
						"\n" . '<script src="data/templates/' . $this->template . '/js/admin_css_editor.js"></script>' .
						"\n";
				$renderer = core()->getInstance('cRenderer');
				$renderer->renderText($additional_output);
		}
}
?>