<?php

///////////////////////////////////////////////////////////////////////////////////
// This class is providing some basic functionality for ajax request handling
// and answering.
///////////////////////////////////////////////////////////////////////////////////

class cDatalanguages extends cModule {
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Daten für einen Statuscode Eintrag laden
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadById($id) {
				$language_where = cDatalanguages::buildActiveLanguageWhereString('dl.language_id');
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT d.*, dl.title, dl.language_id
						FROM ' . $db->table('datalanguages') . ' d
								JOIN ' . $db->table('datalanguages_description') . ' dl ON d.id = dl.datalanguages_id
						WHERE (' . $language_where . ') AND d.id = :id'
				);
				$db->bind(':id', (int)$id);
				$result = $db->execute();
				
				$data = $result->fetchArrayAssoc();
				
				if(empty($data)) {
						return false;
				}
				
				//check language flag
				if(!empty($data['flag_symbol']) && file_exists('userdata/datalanguage_flags/' . $data['flag_symbol'])) {
						$data['flag_symbol_location'] = 'userdata/datalanguage_flags/' . $data['flag_symbol'];
				} else {
						$data['flag_symbol_location'] = '';
				}
				
				return $data;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Aktive Daten-Sprachen laden
		// TODO: default_language_id konfigurierbar machen?!
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadActivated() {
				$default_language_id = 1;
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT d.*, dl.title ' .
						'FROM ' . $db->table('datalanguages') . ' d ' .
								'JOIN ' . $db->table('datalanguages_description') . ' dl ON d.id = dl.datalanguages_id ' .
						'WHERE dl.language_id = :language_id ' .
								'AND status = :status group by id'
				);
				$db->bind(':language_id', (int)$default_language_id);
				$db->bind(':status', '1');
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						
						//check language flag
						if(!empty($tmp['flag_symbol']) && file_exists('userdata/datalanguage_flags/' . $tmp['flag_symbol'])) {
								$tmp['flag_symbol_location'] = 'userdata/datalanguage_flags/' . $tmp['flag_symbol'];
						} else {
								$tmp['flag_symbol_location'] = '';
						}
						
						$retval[] = $tmp;
				}
				
				return $retval;
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////////
		// Check if a datalanguage is activated.
		/////////////////////////////////////////////////////////////////////////////////////////////
		public static function CheckIfLanguageIsActivated($datalanguage_id) {
				global $db;
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * ' .
						'FROM ' . $db->table('datalanguages') . ' ' .
						'where status = 1 ' .
						'AND id = :id'
				);
				$db->setChar(':id', (int)$datalanguage_id);
				$result = $db->execute();
				
				$data = $result->fetch_array_assoc();
				
				if(empty($data)) {
						return false;
				}
				
				return true;
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////
		// Build a where string for an active language..
		////////////////////////////////////////////////////////////////////////////////////////////
		public static function buildActiveLanguageWhereString($language_field = 'language_id') {
				$retval = '';
				
				$lang_instance = core()->get('lang');
				$langs = $lang_instance->langs;

				foreach($langs as $lang) {
						if(!empty($retval)) {
								$retval .= ' or ';
						}
						
						$retval .= $language_field . ' = ' . (int)$lang['id'];
				}

				return $retval;
		}
}

?>