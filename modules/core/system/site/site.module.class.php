<?php

/////////////////////////////////////////////////////////////////////////////////
// Multi-Domain Handler.
// This class desides, which domain is called and handles the request.
//
// If a domain is called, that could not be found:
//		TODO: Make a setting with default domain!
//		If there is no default domain, or the default domain could not be found
//		this module raises an error or 404.
/////////////////////////////////////////////////////////////////////////////////
class cSite extends cModule {		
		/////////////////////////////////////////////////////////////////////////
		// Define where to set this module in the boot (hook) chain.
		/////////////////////////////////////////////////////////////////////////
		public static function setBootHooks() {
				core()->setBootHook('cSeourls');
		}
		
		////////////////////////////////////////////////////////////////////////
		// This is executed when the system boots and the chain reached this module.
		////////////////////////////////////////////////////////////////////////
		public static function boot() {
				//get the current uri..
				$url = $_SERVER['HTTP_HOST'];
				
				if(empty($url)) {
					die('URL konnte nicht ermittelt werden.');
				}

				if(isset($_SERVER['REQUEST_URI'])) {
						//make two parts of request uri 
						$tmp = explode('?', $_SERVER['REQUEST_URI']);		//remove all query string, so we dont match accidentally
						
						//Move along the path
						$tmp = $tmp[0];
						
						while(strlen($tmp) > 0) {
								//Check, if this url is set as a site.
								$db = core()->get('db');
								$db->useInstance('systemdb');
								/*$db->setQuery('SELECT id, url FROM ' . $db->table('site') . ' WHERE url = :url AND status = 1 LIMIT 1');*/
								$db->setQuery(
										'SELECT ' . 
												'id, ' .
												'MATCH(path) AGAINST(:path in boolean mode) AS score, ' .	//Example for :path:
												'INSTR (:path2, path) as position ' . //Example for :path2: 
										'FROM ' . $db->table('site') . ' ' .
										'WHERE ' .
												'INSTR(:path3, path) > 0 AND ' . //Example for :path3: 
												'url LIKE :url ' .							
										'ORDER BY ' .
												'position ASC, ' .
												'score desc;'
								);
								$db->bind(':path', $tmp);
								$db->bind(':path2', $tmp);
								$db->bind(':path3', $tmp);
								$db->bind(':url', $url);
								$result = $db->execute();
								$data = $result->fetchArrayAssoc();

								if(false !== $data) {
										core()->set('site_id', $data['id']);
										return;
								}
								
								if($tmp === "/") {
									die('Es wurde kein Projekt mit der Domain <b>' . htmlspecialchars($url) . '</b> in der Datenbank gefunden. Bitte prüfen Sie die Einstellungen in <b>' . htmlspecialchars($db->table('site')) . '</b>.');
								}
								
								$tmp = dirname($tmp);
						}
				} else {
						echo ('REQUEST_URI not found!' . __FILE__ . ', line: ' . __LINE__);
						die;
				}
				
				/////////////////////////////////////////////////////////
				// if we are here - the path was not found...				
				die('we did not find..' . __FILE__ . ', line: ' . __LINE__);
				
				//Check, if this url is set as a site.
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT id, url FROM ' . $db->table('site') . ' WHERE url = :url AND status = 1 LIMIT 1');
				$db->bind(':url', $url);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(false === $tmp) {
						//TODO: expand error handling.
						die('Site not set in ' . __FILE__ . ', line: ' . __LINE__);
				}
				
				core()->set('site_id', $tmp['id']);
		}
		
		//////////////////////////////////////////////////////////////////////////
		// Returns an array of all modules.
		//
		// Return Value Array Shema:
		//		array(
		//				array(
		//						'module' => 'path/and/module/name',
		//						'version' => '1.6'		//Minimum version of dependent module that is needed to run this module.
		//				), 
		//				array(..)
		//		);
		//
		//		The systems core logic checks all dependencies in the auto loader.
		//			
		//////////////////////////////////////////////////////////////////////////
		public static function getDependenciesAsArray() {
				return array(
						array(
								'module' => '/core/system/seourls/cSeourls'
						)
				);
		}
		
		//////////////////////////////////////////////////////////////////////////
		// Returns the version of a module.
		// Returns 0 (zero) if you define no version for your module.
		//////////////////////////////////////////////////////////////////////////
		public static function getVersion() {
				return 0.1;
		}
		
		//////////////////////////////////////////////////////////////////////////
		// Loads the default cms id for a site.
		//////////////////////////////////////////////////////////////////////////
		public static function loadDefaultCmsId($site_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT home_cms_id FROM ' . $db->table('site') . ' WHERE id = :id LIMIT 1');
				$db->bind(':id', (int)$site_id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(false === $tmp) {
						return 0;
				}
				
				return $tmp['home_cms_id'];
		}
		
		//////////////////////////////////////////////////////////////////////////
		// Loads the cms domain.
		//////////////////////////////////////////////////////////////////////////
		public static function loadSiteUrl($site_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT url, path FROM ' . $db->table('site') . ' WHERE id = :id LIMIT 1');
				$db->bind(':id', (int)$site_id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(false === $tmp) {
						return false;
				}
				
				return $tmp['url'] . $tmp['path'];
		}
		
		//////////////////////////////////////////////////////////////////////////
		// Loads the Template for a site
		//////////////////////////////////////////////////////////////////////////
		public static function loadSiteTemplate($site_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT template FROM ' . $db->table('site') . ' WHERE id = :id LIMIT 1');
				$db->bind(':id', (int)$site_id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(false === $tmp) {
						return '';
				}
				
				return $tmp['template'];
		}
		
		//////////////////////////////////////////////////////////////////////////
		// Load site data.
		//////////////////////////////////////////////////////////////////////////
		public static function loadSiteData($site_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('site') . ' WHERE id = :id LIMIT 1');
				$db->bind(':id', (int)$site_id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(false === $tmp) {
						return false;
				}
				
				return $tmp;
		}
		
		//////////////////////////////////////////////////////////////////////////
		// Load site urls (domains).
		//////////////////////////////////////////////////////////////////////////
		public static function loadSiteUrls() {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT id, title, url, path, status, home_cms_id FROM ' . $db->table('site') . ' ORDER BY url, path');
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$retval[] = $result->fetchArrayAssoc();
				}
				
				return $retval;
		}

		//////////////////////////////////////////////////////////////////////////
		// Loads the protocol this site has been called with.
		//////////////////////////////////////////////////////////////////////////
		public static function loadSiteProtocol() {
				$protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https'?'https':'http';
				return $protocol;
		}
		
		/////////////////////////////////////////////////////////////////////////
		// Get all get params as a string.
		// The first param is used for parameters that should not be included.
		/////////////////////////////////////////////////////////////////////////
		public static function getAllGetParamsAsString($do_not_use) {
				$final = '';
				
				foreach($_GET as $index => $value) {
						if(in_array($index, $do_not_use)) {
								continue;
						}
						
						if(0 !== strlen($final)) {
								$final .= '&';
						}
						
						$final .= $index . '=' . $value;
				}
				
				return $final;
		}
		
		///////////////////////////////////////////////////////////////////////
		// Get path from site settings..
		///////////////////////////////////////////////////////////////////////
		public static function getCurrentSitesPath() {
				$site_id = (int)core()->get('site_id');
				
				if(empty($site_id)) {
						return '/';
				}
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT path FROM ' . $db->table('site') . ' WHERE id = :id LIMIT 1');
				$db->bind(':id', (int)$site_id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(false === $tmp) {
						return '/';
				}
				
				return $tmp['path'];
				
				
		}
}

?>