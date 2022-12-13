<?php

class cAdmincms extends cModule {
		///////////////////////////////////////////////////////////////////////////////
		// Saves an entry. Desides if the entry is to create or to update.
		///////////////////////////////////////////////////////////////////////////////
		public static function saveContentText($content_id, $language_id, $text) {
				cAdmincms::saveContentTextHistory($content_id, $language_id);
				
				//Now save the file itself.
				
				$path = 'data' . DIRECTORY_SEPARATOR . 'cms' . DIRECTORY_SEPARATOR;
				$filename = $path .(int)$content_id . '_' . (int)$language_id . '.tpl';
				
				$fp = fopen($filename, 'w');		//Open and destroy contents..
				fwrite($fp, $text);
				fclose($fp);
		}
		
		///////////////////////////////////////////////////////////////////////////////
		// Saves the current given version of content_id and language_id text file
		// as an history entry.
		///////////////////////////////////////////////////////////////////////////////
		public static function saveContentTextHistory($content_id, $language_id) {
				$data = cCMS::loadCmsFile($content_id, $language_id);
				
				if(false === $data) {
						return false;
				}
				
				$path = 'data' . DIRECTORY_SEPARATOR . 'cms' . DIRECTORY_SEPARATOR . 'history' . DIRECTORY_SEPARATOR;
				$filename = $path .(int)$content_id . '_' . (int)$language_id . '_';
				
				$highest_version_number = 0;
				
				foreach(glob($filename . '*') as $tmp_filename) {
						//parse out the version number of this file
						$tmp_version_number = str_replace($filename, '', $tmp_filename);
						$tmp_version_number = (int)str_replace('.tpl', '', $tmp_version_number);
						
						if($tmp_version_number > $highest_version_number) {
								$highest_version_number = $tmp_version_number;
						}
				}
				
				$highest_version_number+= 1;
				
				//now save the old data in a new file..
				$new_filename = $filename . $highest_version_number . '.tpl';
				
				$fp = fopen($new_filename, 'w');
				fwrite($fp, $data);
				fclose($fp);
		}
		
		//////////////////////////////////////////////////////////////////////////////
		// Loads a list with all cms entries.
		//////////////////////////////////////////////////////////////////////////////
		public static function loadList() {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT id, site_id, cms_key, name, default_navbar_id, meta_title FROM ' . $db->table('cms') .
						'ORDER BY name;'
				);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$retval[] = $tmp;
				}
				
				return $retval;
		}
}

?>