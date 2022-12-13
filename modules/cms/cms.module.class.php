<?php

class cCMS extends cModule {
		var $template = 'maxis';
		var $templateUrl = '';
		var $cms_id = 0;
		var $contentData = '';
		var $siteUrl = '';
		var $metaTitle = '';
		var $footerData = array();
		var $html_body_classes = '';
		var $footerJavascript = array();
		var $headCss = array();
	
		//////////////////////////////////////////////////////////////////////////////////
		// Hook us into the system
		//////////////////////////////////////////////////////////////////////////////////
		public static function setExecutionalHooks() {
				core()->setHook('cCore|init', 'init');
				core()->setHook('cRenderer|header', 'header');
				core()->setHook('cRenderer|begin_page', 'begin_page');
				core()->setHook('cRenderer|header_bar', 'header_bar');
				core()->setHook('cRenderer|content', 'content');
				core()->setHook('cRenderer|footer', 'footer');
				core()->setHook('cRenderer|end_page', 'end_page');
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Initialize this module.
		/////////////////////////////////////////////////////////////////////////////////
		public function init() {
				//Load some basic settings
				$this->cms_id = (int)core()->getGetVar('id');		//ID of CMS entry
				$site_id = core()->get('site_id');							//ID of site (url based)
				$this->siteUrl = cSite::loadSiteUrl($site_id);	//Load URL by site id
				$this->templateUrl = cCMS::loadTemplateUrl($site_id);
				$this->template = cSite::loadSiteTemplate($site_id);
								
				//if this is a logged in user..
				/*
				$state = cWsseller::getSellerStatus();
				
				if($state == 'loggedin') {
						$account_id = $_SESSION['seller_id'];
						$account_data = cAccount::loadUserData($account_id);
						$this->footerData['account_data'] = $account_data;
				} else {
						$this->footerData['account_data'] = false;
				}
				*/
				
				//Load the cms entry.
				$this->loadContentFromDatabase($this->cms_id);
				
				//Check SEO Url
				$params = array('s' => 'cCMS', 'id' => $this->cms_id);
				cSeourls::checkSeoUrl($this->siteUrl, 'cCMS', $params);
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Load Template Url
		/////////////////////////////////////////////////////////////////////////////////
		public static function loadTemplateUrl($site_id) {
				$site_data = cSite::loadSiteData($site_id);
				return '//' . $site_data['url'] . $site_data['template_path'];
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Loads content from database.
		/////////////////////////////////////////////////////////////////////////////////
		public function loadContentFromDatabase() {
				$this->contentData = '';
				
				//Load the current site..
				$site_id = core()->get('site_id');

				//Check cms_id - if it is zero - try to load the default cms id for this site.
				if($this->cms_id == 0) {
						if(core()->getGetVar('s') == 'cCMS') {
								header('Location: https://www.mindfav.com', 302);
								die;
						}
						$this->cms_id = cSite::loadDefaultCmsId($site_id);
				}
				
				$this->contentData = $this->loadContentDataByCmsId($this->cms_id);
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Load Content Entry by CMS-Key
		/////////////////////////////////////////////////////////////////////////////////
		public function loadContentDataByKey($key) {
				$contentData = '';
				
				//Try to load the site id by key.
				$cms_id = $this->loadCmsIdByKey($key);
				
				if(false === $cms_id) {
						return false;
				}
				
				$contentData = $this->loadContentDataByCmsId($cms_id);
				
				return $contentData;
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Load a CMS id by key.
		/////////////////////////////////////////////////////////////////////////////////
		public function loadCmsIdByKey($key) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT id FROM ' . $db->table('cms') . ' WHERE cms_key = :key AND site_id = :site_id LIMIT 1');
				$db->bind(':key', $key);
				$db->bind(':site_id', (int)core()->get('site_id'));
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(isset($tmp['id'])) {
						return (int)$tmp['id'];
				}
				
				return false;
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Load content Data from database and files.
		/////////////////////////////////////////////////////////////////////////////////
		public function loadContentDataByCmsId($cms_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT id, cms_key, name, default_navbar_id, meta_title FROM ' . $db->table('cms') . ' WHERE id = :id LIMIT 1');
				$db->bind(':id', (int)$cms_id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(false !== $tmp) {
						$lang = core()->get('lang');
						$language_id = (int)$lang->getCurLangId();
						
						//Load text content in current language
						$tmp['text'] = cCMS::loadCmsFile((int)$tmp['id'], (int)$language_id);
						
						$tmp['navbar'] = cNavbar::loadNavbarData((int)$tmp['default_navbar_id'], (int)$language_id);
				}
				
				return $tmp;
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Load Content Entry by CMS-Key
		/////////////////////////////////////////////////////////////////////////////////
		public static function loadContentDataByCmsKeyStatic($key) {
				$contentData = '';
				
				//Try to load the site id by key.
				$cms_id = cCMS::loadCmsIdByCmsKeyStatic($key);
				
				if(false === $cms_id) {
						return false;
				}
				
				$contentData = cCMS::loadContentDataByCmsIdStatic($cms_id);
				
				return $contentData;
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Load a CMS id by key.
		/////////////////////////////////////////////////////////////////////////////////
		public static function loadCmsIdByCmsKeyStatic($key) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT id FROM ' . $db->table('cms') . ' WHERE cms_key = :key AND site_id = :site_id LIMIT 1');
				$db->bind(':key', $key);
				$db->bind(':site_id', (int)core()->get('site_id'));
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(isset($tmp['id'])) {
						return (int)$tmp['id'];
				}
				
				return false;
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Load content Data from database and files.
		/////////////////////////////////////////////////////////////////////////////////
		public static function loadContentDataByCmsIdStatic($cms_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT id, cms_key, name, default_navbar_id, meta_title FROM ' . $db->table('cms') . ' WHERE id = :id LIMIT 1');
				$db->bind(':id', (int)$cms_id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(false !== $tmp) {
						$lang = core()->get('lang');
						$language_id = (int)$lang->getCurLangId();
						
						//Load text content in current language
						$tmp['text'] = cCMS::loadCmsFile((int)$tmp['id'], (int)$language_id);
						
						$tmp['navbar'] = cNavbar::loadNavbarData((int)$tmp['default_navbar_id'], (int)$language_id);
				}
				
				return $tmp;
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Load CMS File data by id and language_id.
		/////////////////////////////////////////////////////////////////////////////////
		public static function loadCmsFile($id, $language_id) {
				$filename = 'data' . DIRECTORY_SEPARATOR . 'cms' . DIRECTORY_SEPARATOR . (int)$id . '_' . (int)$language_id . '.tpl';			
				$tmp = '';
				
				if(false !== file_exists($filename)) {
						$tmp = file_get_contents($filename);
				} else {
						//TODO: Load 404 file or redirect?? -> I think: redirect..
				}
				
				return $tmp;
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Loads injectable content..
		/////////////////////////////////////////////////////////////////////////////////
		public static function loadInjectableContent($cms_id, $part) {
				$filename = 'data' . DIRECTORY_SEPARATOR . 'cms' . DIRECTORY_SEPARATOR . 'inject' . DIRECTORY_SEPARATOR .  (int)$cms_id . '_' . $part . '.tpl';
				$tmp = '';
				
				if(false !== file_exists($filename)) {
						$tmp = file_get_contents($filename);
				}
				
				return $tmp;
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Render the header (html header!)
		/////////////////////////////////////////////////////////////////////////////////
		public function header() {
				$core = core();
			
				$renderer = core()->getInstance('cRenderer');
				$renderer->assign('SITE_URL', cSite::loadSiteProtocol() . '://' . $this->siteUrl);
				$renderer->assign('TEMPLATE_URL', $this->templateUrl);
				$renderer->assign('META_TITLE', $this->contentData['meta_title']);
				$renderer->assign('TEMPLATE', $this->template);
				$renderer->setTemplate($this->template);
				$renderer->render('site/header.html');
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Render the Beginning of the page
		/////////////////////////////////////////////////////////////////////////////////
		public function begin_page() {
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('TEMPLATE', $this->template);
				
				if(core()->getGetVar('s') == 'cCms') {
						if(strlen($this->html_body_classes) != 0) {
								$this->html_body_classes .= ' ';
						}
						$this->html_body_classes .= 'content-page';
				}
				
				if(strlen($this->html_body_classes) != 0) {
						$renderer->assign('HTML_BODY_CLASSES', $this->html_body_classes);
				}
					
				$renderer->render('site/begin_page.html');
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Render the Header Bar (for example: logo, header navigation)
		// e.g.: Things that are always of the same style on every page.
		/////////////////////////////////////////////////////////////////////////////////
		public function header_bar() {
				$lang = core()->get('lang');
				
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$header_navigation = $this->getHeaderNavigation();
				$renderer->assign('HEADER_NAVIGATION', $header_navigation);
				$renderer->assign('SITE_URL', cSite::loadSiteProtocol() . '://' . $this->siteUrl);
				$renderer->assign('TEMPLATE_URL', $this->templateUrl);
				$renderer->assign('LANGUAGE', $lang);
				$renderer->assign('TEMPLATE', $this->template);
				$renderer->render('site/header_bar.html');
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Render the content. This is the middle part of the page.
		/////////////////////////////////////////////////////////////////////////////////
		public function content() {
				$renderer = core()->getInstance('cRenderer');
				
				//Render the content (if there are set some CMS variables..)
				$renderer->assign('CONTENT', $this->contentData['text']);
				$renderer->assign('TEMPLATE', $this->template);
				$this->contentData['text'] = $renderer->fetchFromString($this->contentData['text']);
				
				//Render the content page itself..
				$renderer->setTemplate($this->template);
				$renderer->assign('CONTENT', $this->contentData['text']);
				$renderer->assign('TEMPLATE', $this->template);
				$renderer->render('site/content.html');
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Render the pages footer.
		/////////////////////////////////////////////////////////////////////////////////
		public function footer() {
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('SITE_URL', cSite::loadSiteProtocol() . '://' . $this->siteUrl);
				$renderer->assign('TEMPLATE_URL', $this->templateUrl);
				$renderer->assign('TEMPLATE', $this->template);
				$renderer->assign('FOOTER_DATA', $this->footerData);
				$renderer->render('site/footer.html');
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Render the pages footer.
		/////////////////////////////////////////////////////////////////////////////////
		public function end_page() {
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('SITE_URL', cSite::loadSiteProtocol() . '://' . $this->siteUrl);
				$renderer->assign('TEMPLATE_URL', $this->templateUrl);
				$renderer->assign('TEMPLATE', $this->template);
				
				//Load additional content in the end part of the page..				
				$injectable = $this->loadInjectableContent($this->cms_id, 'end_page');
				if(strlen($injectable) > 0) {
						$injectable = $renderer->fetchFromString($injectable);
				}
				$renderer->assign('INJECT_CONTENT', $injectable);
				
				$renderer->assign('HEAD_CSS', $this->buildHeadCss());
				$renderer->assign('FOOTER_JAVASCRIPT', $this->buildFooterJavascript());
				
				//Render page part
				$renderer->render('site/end_page.html');

				$renderer->display();
		}
		
		////////////////////////////////////////////////////////////////////////////////
		// Get the header navigation.
		////////////////////////////////////////////////////////////////////////////////
		protected function getHeaderNavigation() {
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('SITE_URL', cSite::loadSiteProtocol() . '://' . $this->siteUrl);
				$renderer->assign('TEMPLATE_URL', $this->templateUrl);
				$renderer->assign('TEMPLATE', $this->template);
				$renderer->assign('DATA', $this->contentData['navbar']);
				return $renderer->fetch('site/header_navigation.html');
		}
		
		////////////////////////////////////////////////////////////////////////////////
		// Set content data.
		////////////////////////////////////////////////////////////////////////////////
		public function setContentData($contentData) {
				$this->contentData = $contentData;
		}
		
		////////////////////////////////////////////////////////////////////////////////
		// Get content data.
		////////////////////////////////////////////////////////////////////////////////
		public function getContentData() {
				return $this->contentData;
		}
		
		///////////////////////////////////////////////////////////////////////////////
		// Check if a content id exists in database.
		///////////////////////////////////////////////////////////////////////////////
		public static function contentIdExists($cms_id) {
				// Load language				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT id FROM ' . $db->table('cms') . ' WHERE id = :id LIMIT 1');
				$db->bind(':id', (int)$cms_id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(false === $tmp) {
						return false;
				}
				
				return true;
		}
		
		/////////////////////////////////////////////////////////////////////////////
		// Set the sites url.
		/////////////////////////////////////////////////////////////////////////////
		public function setSiteUrl($site_url) {
				$this->siteUrl = $site_url;
		}
		
		/////////////////////////////////////////////////////////////////////////////
		// Get template url.
		/////////////////////////////////////////////////////////////////////////////
		public function getTemplateUrl() {
				return $this->templateUrl;
		}
		
		/////////////////////////////////////////////////////////////////////////////
		// Get template url.
		/////////////////////////////////////////////////////////////////////////////
		public function setTemplateUrl($template_url) {
				$this->templateUrl = $template_url;
		}
		
		/////////////////////////////////////////////////////////////////////////////
		// Set template.
		/////////////////////////////////////////////////////////////////////////////
		public function setTemplate($template) {
				$this->template = $template;
		}
		
		/////////////////////////////////////////////////////////////////////////////
		// Get template.
		/////////////////////////////////////////////////////////////////////////////
		public function getTemplate() {
				return $this->template;
		}
		
		/////////////////////////////////////////////////////////////////////////////
		// Set footer data.
		/////////////////////////////////////////////////////////////////////////////
		public function setFooterData($index, $data) {
				$this->footerData[$index] = $data;
		}
		
		/////////////////////////////////////////////////////////////////////////////
		// Set footer data.
		/////////////////////////////////////////////////////////////////////////////
		public function getFooterData($index) {
				if(isset($this->footerData[$index])) {
						return $this->footerData[$index];
				}
				
				return false;
		}
		
		/////////////////////////////////////////////////////////////////////////////
		// Set footer data.
		/////////////////////////////////////////////////////////////////////////////
		public function addFooterJavascript($url) {
				$this->footerJavascript[] = $url;
		}
		
		/////////////////////////////////////////////////////////////////////////////
		// Add something to the header.
		/////////////////////////////////////////////////////////////////////////////
		public function addHeadCss($css) {
				$this->headCss[] = $css;
		}
		
		/////////////////////////////////////////////////////////////////////////////
		// buildFooterJavascript
		/////////////////////////////////////////////////////////////////////////////
		public function buildFooterJavascript() {
				$final_javascript = '';
				
				foreach($this->footerJavascript as $js) {
						$final_javascript .= "\n" . '<script src="' . $js . '"></script>';
				}
				
				return $final_javascript;
		}
		
		/////////////////////////////////////////////////////////////////////////////
		// buildHeadCss
		/////////////////////////////////////////////////////////////////////////////
		public function buildHeadCss() {
				$final_css = '';
				
				foreach($this->headCss as $hcss) {
						$final_css .= "\n" . $hcss;
				}
				
				return $final_css;
		}
		
		/////////////////////////////////////////////////////////////////////////////
		// Body classes hinzufügen.
		/////////////////////////////////////////////////////////////////////////////
		public function setHtmlBodyClasses($classes) {
				$this->html_body_classes = $classes;
		}
}

?>