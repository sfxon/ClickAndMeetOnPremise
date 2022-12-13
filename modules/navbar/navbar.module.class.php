<?php

class cNavbar extends cModule {
		/////////////////////////////////////////////////////////////////////////////////
		// Load Navbar data by navbar_id and language_id.
		/////////////////////////////////////////////////////////////////////////////////
		public static function loadNavbarData($navbar_id, $language_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT id, status, navbar_url, parent_navbar_entries_item FROM ' . $db->table('navbar') . ' WHERE id = :id AND status = 1 LIMIT 1 ');
				$db->bind(':id', (int)$navbar_id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(false !== $tmp) {
						//Try to load the entries for this navbar.+
						$db->useInstance('systemdb');
						$db->setQuery(
								'SELECT id, navbar_id, sort_order, status, data, data_type, logged_in_only ' .
								'FROM ' . $db->table('navbar_entries') . ' ' .
								'WHERE navbar_id = :navbar_id AND status = 1 ' .
								'ORDER BY sort_order'
						);
						$db->bind(':navbar_id', (int)$navbar_id);
						$result = $db->execute();
						
						$entries = array();
						
						while($result->next()) {
								$tmp_entry = $result->fetchArrayAssoc();
								$tmp_entry['url'] = '';
								
								//check if this is only availlable to logged in users - and if the user is logged in!
								if(1 == $tmp_entry['logged_in_only'] && (!isset($_SESSION['user_id']) || 0 == $_SESSION['user_id'])) {
										continue;
								}
								
								
								//Load Language specific data.
								$db->setQuery(
										'SELECT title, description ' .
										'FROM ' . $db->table('navbar_entries_descriptions') . ' ' .
										'WHERE navbar_entries_id = :navbar_entries_id AND language_id = :language_id LIMIT 1'
								);
								$db->bind(':navbar_entries_id', (int)$tmp_entry['id']);
								$db->bind(':language_id', (int)$language_id);
								$result_description = $db->execute();
								
								$tmp_description = $result_description->fetchArrayAssoc();
								
								if(!isset($tmp_description['title'])) {
										continue;		//Entry has not been found - skip..
								}
								
								$tmp_entry['description'] = $tmp_description;

								if($tmp_entry['data_type'] == 1) {
										//This is a query string or url..	
										//check if the protocol is set in the url
										if(strpos($tmp_entry['data'], 'http://') === 0 || strpos($tmp_entry['data'], 'https://') === 0 || strpos($tmp_entry['data'], '//') === 0 || strpos($tmp_entry['data'], '#') === 0) {
												$tmp_entry['url'] = $tmp_entry['data'];
										} else {
												//append protocoll and domain (build the final url)
												$tmp_entry['url'] = cSite::loadSiteProtocol() . '//' . cSite::loadSiteUrl(core()->get('site_id')) . $tmp_entry['data'];
										}
								} elseif($tmp_entry['data_type'] == 2) {
										//This indicates, that the module should calculate the text and url of the menu point
										//THIS IS NOT THE SEO URL THING
										$module = $tmp_entry['data'];

										if(class_exists($module)) {
												if(method_exists($module, 'getNavbarData')) {
														$navbar_data = $module::getNavbarData();
														
														if(is_array($navbar_data)) {
																if(isset($navbar_data['title'])) {
																		$tmp_entry['title'] = $navbar_data['title'];
																}
																
																if(isset($navbar_data['url'])) {
																		if(strpos($navbar_data['url'], 'http://') === 0 || strpos($navbar_data['url'], 'https://') === 0 || strpos($navbar_data['url'], '//') === 0 || strpos($navbar_data['data'], '#') === 0) {
																				$tmp_entry['url'] = $navbar_data['url'];
																		} else {
																				//append protocoll and domain (build the final url)
																				$tmp_entry['url'] = cSite::loadSiteProtocol() . '//' . cSite::loadSiteUrl(core()->get('site_id')) . '/' . $navbar_data['url'];
																		}
																}
														}
												}
										}
								}
								
								/*echo '<pre>';
								var_dump($tmp_entry);
								echo '</pre>';*/
								
								//Load Sub-Navbars..
								$tmp_entry['sub_navbar'] = cNavbar::loadSubNavbars($tmp_entry['id'], $language_id);
								$entries[] = $tmp_entry;
						}
						
						$tmp['navbar_entries'] = $entries;
				}
				
				return $tmp;
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Load Navbar data by navbar_id and language_id.
		/////////////////////////////////////////////////////////////////////////////////
		public static function loadSubNavbars($parent_navbar_entries_item, $language_id) {
				if(0 === (int)$parent_navbar_entries_item) {
						return array();
				}
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT id FROM ' . $db->table('navbar') . ' WHERE parent_navbar_entries_item = :parent_navbar_entries_item AND status = 1');
				$db->bind(':parent_navbar_entries_item', (int)$parent_navbar_entries_item);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$retval = cNavbar::loadNavbarData($tmp['id'], $language_id);
				}
				
				return $retval;
		}
		
}

?>