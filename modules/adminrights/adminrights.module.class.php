<?php

class cAdminrights extends cModule {	
		/////////////////////////////////////////////////////////////////////////////
		// Check if the combination of account and systemright is set.
		/////////////////////////////////////////////////////////////////////////////
		public static function checkIfAdminrightExists($accounts_id, $systemrights_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT status FROM ' . $db->table('adminrights') . ' ' .
						'WHERE ' .
								'accounts_id = :accounts_id AND ' .
								'systemrights_id = :systemrights_id ' .
						'LIMIT 1');
				$db->bind(':accounts_id', (int)$accounts_id);
				$db->bind(':systemrights_id', (int)$systemrights_id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(isset($tmp['status'])) {
						return true;
				}
				
				return false;
		}
		
		/////////////////////////////////////////////////////////////////////////////
		// Check if the combination of account and systemright is set.
		/////////////////////////////////////////////////////////////////////////////
		public static function create($accounts_id, $systemrights_id, $status) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'INSERT INTO ' . $db->table('adminrights') . ' ' .
								'(accounts_id, systemrights_id, status) ' .
						'VALUES ' .
								'(:accounts_id, :systemrights_id, :status) '
				);
				$db->bind(':accounts_id', (int)$accounts_id);
				$db->bind(':systemrights_id', (int)$systemrights_id);
				$db->bind(':status', (int)$status);
				$db->execute();
		}
		
		/////////////////////////////////////////////////////////////////////////////
		// Check if the combination of account and systemright is set.
		/////////////////////////////////////////////////////////////////////////////
		public static function update($accounts_id, $systemrights_id, $status) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('adminrights') . ' SET ' .
								'status = :status ' .
						'WHERE ' .
								'accounts_id = :accounts_id AND ' .
								'systemrights_id = :systemrights_id'
				);
				$db->bind(':status', (int)$status);
				$db->bind(':accounts_id', (int)$accounts_id);
				$db->bind(':systemrights_id', (int)$systemrights_id);
				$db->execute();
		}
		
		/////////////////////////////////////////////////////////////////////////////
		// Check if the combination of account and systemright is set.
		/////////////////////////////////////////////////////////////////////////////
		public static function loadEntriesArrayByAccountsId($accounts_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('adminrights') . ' ' .
						'WHERE ' .
								'accounts_id = :accounts_id'
				);
				$db->bind(':accounts_id', (int)$accounts_id);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$retval[$tmp['systemrights_id']] = $tmp;
				}
				
				return $retval;
		}
}