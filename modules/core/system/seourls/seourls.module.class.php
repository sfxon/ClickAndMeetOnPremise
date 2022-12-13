<?php

/////////////////////////////////////////////////////////////////////////////////
// The systems database initialisation.
// We use this database information for all our system modules.
// In this database we save things like session_data, languages_data, ...
// It is very essential for all other modules.
/////////////////////////////////////////////////////////////////////////////////
class cSeourls extends cModule {		
		/////////////////////////////////////////////////////////////////////////
		// Define where to set this module in the boot (hook) chain.
		/////////////////////////////////////////////////////////////////////////
		public static function setBootHooks() {
				core()->setBootHook('cSystemdb');
		}
		
		////////////////////////////////////////////////////////////////////////
		// This is executed when the system boots and the chain reached this module.
		////////////////////////////////////////////////////////////////////////
		public static function boot() {
				if(!isset($_GET['seourl']) && !isset($_GET['s'])) {
						$_GET = array();
						$_GET['s'] = 'cIndexPage';
				}
				
				if(isset($_GET['seourl'])) {					
						if(isset($_SERVER['REQUEST_URI'])) {
								$seo_path = $_SERVER['REQUEST_URI'];
								
								$site_id = cSeourls::getSiteId();
								$site_data = cSite::loadSiteData($site_id);

								if($site_data['path'] != '/') {
										$seo_path = str_replace($site_data['path'], '', $seo_path);
								}
								
								//remove ? query from string..
								$seo_path = explode('?', $seo_path);
								$seo_path = $seo_path[0];
								
								//add slashes to the beginning and the end - if they do not exist..
								if(strpos($seo_path, '/') !== 0) {
										$seo_path = '/' . $seo_path;
								}
								
								//Remove last trailing slash, to always have same url..
								$search_path = rtrim($seo_path, '/');
								
								/*var_dump($seo_path);
								
								$basename = basename($seo_path);
								var_dump($basename);
								
								if(strpos(strrev($seo_path), '/') !== 0) {
										$seo_path .= '/';
								}*/
								
								//CHECK Database for SEO Url
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery(
										'SELECT querystring FROM ' . $db->table('seourls') . ' ' .
										'WHERE ' .
												'site_id = :site_id AND ' .
												'seourl = :seourl ' .
										'LIMIT 1'
								);
								$db->bind(':site_id', $site_id);
								$db->bind(':seourl', $search_path);
								$result = $db->execute();
								
								$tmp = $result->fetchArrayAssoc();
								
								if(isset($tmp['querystring'])) {
										$tmp_result = array();
										
										parse_str($tmp['querystring'], $tmp_result);		//Overrides other query parameters that are set. But we think, this is fair.
										$_GET = array_merge($_GET, $tmp_result);
								}
						}
				}
				
				//Clear all get parameters for security reasons. (cms command should not get any parameters, if no page was loaded by will
				if(!isset($_GET['s'])) {
						$_GET = array();
				}
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
								'module' => '/core/system/systemdb/cSystemdb'
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
		// TODO: Maybe push this to cSite
		// and also use it in cSite::boot..
		//////////////////////////////////////////////////////////////////////////
		public static function getSiteId(){
				$url = $_SERVER['HTTP_HOST'];
				
				if(empty($url)) {
						$url = 'mindfav.com';
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
												'MATCH(path) AGAINST(:path in boolean mode) AS score, ' .	
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
										return $data['id'];
								}
								
								$tmp = dirname($tmp);
						}
				}
				
				return false;
		}
		
		//////////////////////////////////////////////////////////////////////////
		// Try to load seo url by the parameter string.
		//////////////////////////////////////////////////////////////////////////
		public static function loadSeourlByQueryString($querystring) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT seourl FROM ' . $db->table('seourls') . ' WHERE querystring = :querystring LIMIT 1');
				$db->bind(':querystring', $querystring);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(false == $tmp) {
						return false;
				}
				
				return $tmp['seourl'];
		}
		
		/////////////////////////////////////////////////////////////////////////
		// check seo url - if the url is different from the calculated - redirect
		/////////////////////////////////////////////////////////////////////////
		public static function checkSeoUrl($site_url, $module, $params) {
				$s = core()->getGetVar('s');
				
				if($s == $module) {
						$content_url = http_build_query($params);
						$seo_url = cSeourls::loadSeourlByQueryString($content_url);
						
						//get all get params and append the one, that are not set yet..
						$do_not_get_this_params = array('seourl');
						
						foreach($params as $index => $param) {
								$do_not_get_this_params[] = $index;
						}					
						
						$param_string = cSite::getAllGetParamsAsString( $do_not_get_this_params );
						$final_content_url = '//' . $site_url . '?' . $content_url . '&' . $param_string;
						
						if(false !== $seo_url) {								
								//Build final urls
								
								//Remove first /.
								if(strpos($seo_url, '/') === 0) {
										if(strlen($seo_url) > 1) {
												$seo_url = substr($seo_url, 1, strlen($seo_url) - 1);
										} else {
												$seo_url = '';
										}
								}
								
								$final_content_url = '//' . $site_url . $seo_url . '?' . $param_string;
								
								//Check if the current complete request is the same as our generated request
								$current_url  = '//' . core()->getCurrentDomain() . $_SERVER['REQUEST_URI'];
								
								//remove ? in both urls, if it is the last char..
								$final_content_url = rtrim($final_content_url, "?");
								$current_url = rtrim($current_url, "?");
								
								if(0 !== strcasecmp($current_url, $final_content_url)) {
										//This is not the seo url! Redirect to the seo url!
										header('Location: ' . $final_content_url, 301);
										die;
								}
						}
				}
		}
}

?>