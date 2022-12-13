<?php

class cAdmin extends cModule {
		var $template = 'admin';
		var $metaTitle = '';
		var $breadcrumb = array();
		var $contentData = array();
		var $adminMenu = array();
	
		//////////////////////////////////////////////////////////////////////////////////
		// Hook us into the system
		//////////////////////////////////////////////////////////////////////////////////
		public static function setExecutionalHooks() {
				cAdmin::setSmallBodyExecutionalHooks();
				core()->setHook('cRenderer|content', 'content');
		}
		
		//////////////////////////////////////////////////////////////////////////////////
		// Hook us into the system
		//////////////////////////////////////////////////////////////////////////////////
		public static function setSmallBodyExecutionalHooks() {
				core()->setHook('cCore|init', 'init');
				core()->setHook('cRenderer|header', 'header');
				core()->setHook('cRenderer|begin_page', 'begin_page');
				core()->setHook('cRenderer|header_bar', 'header_bar');
				core()->setHook('cRenderer|footer', 'footer');
				core()->setHook('cRenderer|end_page', 'end_page');
				
				core()->set('is_admin_page', true);
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Initialize this module.
		/////////////////////////////////////////////////////////////////////////////////
		public function init() {
				//check, if user is logged in..
				if(!isset($_SESSION['user_id'])) {
						header('Location: index.php?error=notallowed');
						die;
				}
				
				//check the rights..
				if(false === cAccount::adminrightCheck('cAdmin', 'USE_MODULE', (int)$_SESSION['user_id'])) {
						header('Location: index.php?error=notallowed');
						die;
				}
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Render the header (html header!)
		/////////////////////////////////////////////////////////////////////////////////
		public function header() {
				$renderer = core()->getInstance('cRenderer');
				if(isset($this->contentData['meta_title'])) {
						$renderer->assign('META_TITLE', $this->contentData['meta_title']);
				}
				
				$renderer->setTemplate($this->template);
				$renderer->render('site/header.html');
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Render the Beginning of the page
		/////////////////////////////////////////////////////////////////////////////////
		public function begin_page() {
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->render('site/begin_page.html');
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Render the Header Bar (for example: logo, header navigation)
		// e.g.: Things that are always of the same style on every page.
		/////////////////////////////////////////////////////////////////////////////////
		public function header_bar() {
				$s = core()->get('s');
				
				if(empty($this->breadcrumb) && $s = 'cAdmin') {
						$this->appendBreadcrumb(TEXT_TITLE_DASHBOARD, '');
				}
				
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('BREADCRUMB', $this->breadcrumb);
				$renderer->assign('ADMIN_MENU', $this->adminMenu);
				$renderer->render('site/header_bar_calculated.html');
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Render the content. This is the middle part of the page.
		/////////////////////////////////////////////////////////////////////////////////
		public function content() {
				//Load RSS News Feed
				$rssnews = cDashboardrssnews::getRssNews($this->template);
			
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('RSS_NEWS', $rssnews);
				$renderer->render('site/dashboard/dashboard.html');
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Render the pages footer.
		/////////////////////////////////////////////////////////////////////////////////
		public function footer() {
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->render('site/footer.html');
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Render the pages footer.
		/////////////////////////////////////////////////////////////////////////////////
		public function end_page() {
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->render('site/end_page.html');

				$renderer->display();
		}
		
		////////////////////////////////////////////////////////////////////////////////
		// Add entries to the breadcrumb.
		// @param		parts:	Appends one part to the breadcrumb array.
		////////////////////////////////////////////////////////////////////////////////
		public function appendBreadcrumb($title, $url) {
				$this->breadcrumb[] = array(
						'title' => $title,
						'url' => $url
				);
		}
		
		////////////////////////////////////////////////////////////////////////////////
		// Add entries to the admin menu.
		// @param		parts:	Appends one part to the breadcrumb array.
		////////////////////////////////////////////////////////////////////////////////
		public function registerAdminMenuEntry($path, $url) {
				if(!is_array($path)) {
						return false;
				}
				
				/*
				echo '<pre>';
				var_dump($path);
				echo '</pre>';
				
				$trace = debug_backtrace();
				if (isset($trace[1])) {
						// $trace[0] is ourself
						// $trace[1] is our caller
						// and so on...
						var_dump($trace[1]);
	
						echo "called by {$trace[1]['class']} :: {$trace[1]['function']}";
	
				}
				
				
				echo '<hr />';
				*/
				
				//Menü Ebene 1
				if(!isset($this->adminMenu[$path[0]['position']])) {
						$this->adminMenu[$path[0]['position']] = array('title' => $path[0]['title'], 'url' => $url, 'sub_menu_items' => array());
				}
				
				ksort($this->adminMenu);
				
				if(count($path) == 1) {
						return;
				}
				
				//Menü-Ebene 2
				if(!isset($this->adminMenu[$path[0]['position']]['sub_menu_items'][$path[1]['position']])) {
						$this->adminMenu[$path[0]['position']]['sub_menu_items'][$path[1]['position']] = array('title' => $path[1]['title'], 'url' => $url, 'sub_menu_items' => array());
				}
				
				ksort($this->adminMenu[$path[0]['position']]['sub_menu_items']);
				
				if(count($path) == 2) {
						return;
				}
				
				//Menü-Ebene 3
				if(!isset($this->adminMenu[$path[0]['position']]['sub_menu_items'][$path[1]['position']]['sub_menu_items'][$path[2]])) {
						$this->adminMenu[$path[0]['position']]['sub_menu_items'][$path[1]['position']]['sub_menu_items'][$path[2]['position']] = array('title' => $path[2]['title'], 'url' => $url, 'sub_menu_items' => array());
				}
				
				ksort($this->adminMenu[$path[0]['position']]['sub_menu_items'][$path[1]['position']]['$sub_menu_items']);
		}
		
		//////////////////////////////////////////////////////////////////////////////////
		// Hook us into the system in the additional hooks..
		//////////////////////////////////////////////////////////////////////////////////
    public static function setAdditionalHooks() {
				core()->setHook('cCore|init', 'addMenuBarEntries');
    }
		
		//////////////////////////////////////////////////////////////////////////////////
		// Callback function, adds a menu item.
		//////////////////////////////////////////////////////////////////////////////////
		public static function addMenuBarEntries() {
				$cAdmin = core()->getInstance('cAdmin');
				
				if(false !== $cAdmin) {
						$admin_menu_entry_path = array(
								array(
										'position' => 0,
										'title' => '<i class="fa fa-home"></i>'
								),
								array(
										'position' => 0,
										'title' => 'Dashboard'
								)
						);
		        $cAdmin->registerAdminMenuEntry($admin_menu_entry_path, 'index.php?s=cAdmin');
						
						
						
						$admin_menu_entry_path = array(
								array(
										'position' => 0,
										'title' => '<i class="fa fa-home"></i>'
								),
								array(
										'position' => 100,
										'title' => 'Website'
								)
						);
		       $cAdmin->registerAdminMenuEntry($admin_menu_entry_path, 'index.php');
						
						$admin_menu_entry_path = array(
								array(
										'position' => 0,
										'title' => '<i class="fa fa-home"></i>'
								),
								array(
										'position' => 200,
										'title' => 'Logout'
								)
						);
		        $cAdmin->registerAdminMenuEntry($admin_menu_entry_path, 'index.php?s=cLogout');
				}
		}
}

?>