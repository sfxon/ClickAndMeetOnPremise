{literal}<?php{/literal}

class {$DATA.data.data_module}Master extends cModule {literal}{{/literal}
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Load Customer groups data by id.
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadById($id) {literal}{{/literal}
				$retval = array();
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('{$DATA.data.database_table_raw}') . ' WHERE id = :id LIMIT 1;');
				$db->bind(':id', (int)$id);
				$result = $db->execute();
				
				$data = $result->fetchArrayAssoc();
				
				if(empty($data)) {literal}{{/literal}
						return NULL;
				{literal}}{/literal}
		
				return $data;
		{literal}}{/literal}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Alle Einträge laden
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadList() {literal}{{/literal}
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * ' .
						'FROM ' . $db->table('{$DATA.data.database_table_raw}') . ' ' .
						'ORDER BY title;'
				);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {literal}{{/literal}
						$tmp = $result->fetchArrayAssoc();
						$retval[] = $tmp;
				{literal}}{/literal}
				
				return $retval;
		{literal}}{/literal}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Create data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public static function createInDB($data) {literal}{{/literal}
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'INSERT INTO ' . $db->table('{$DATA.data.database_table_raw}') . ' ' .
            		'({foreach from=$DATA.data.fields item=field name="create_in_db_foreach"}{if $field.field_name != 'id'}{$field.field_name}{if $smarty.foreach.create_in_db_foreach.last !== TRUE},{/if}{/if}{/foreach}) ' .
								'VALUES' .
                '({foreach from=$DATA.data.fields item=field name="create_in_db_foreach"}{if $field.field_name != 'id'}:{$field.field_name}{if $smarty.foreach.create_in_db_foreach.last !== TRUE},{/if}{/if}{/foreach}) '
				);
        {foreach from=$DATA.data.fields item=field name="create_in_db_foreach"}
        		{if $field.field_name != 'id'}
            		$db->bind(':{$field.field_name}', $data['{$field.field_name}']);
            {/if}
        {/foreach}
				$db->execute();
				
				return $db->insertId();
		{literal}}{/literal}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Update data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public static function updateInDB($id, $data) {literal}{{/literal}
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('{$DATA.data.database_table_raw}') . ' SET ' .
								'{foreach from=$DATA.data.fields item=field name="create_in_db_foreach"}{if $field.field_name != 'id'}{$field.field_name} = :{$field.field_name}{if $smarty.foreach.create_in_db_foreach.last !== TRUE}, {/if}{/if}{/foreach} ' .
						'WHERE ' .
								'id = :id'
				);
				{foreach from=$DATA.data.fields item=field name="create_in_db_foreach"}{strip}
        		{/strip}{if $field.field_name != 'id'}
            		$db->bind(':{$field.field_name}', $data['{$field.field_name}']);
            {/if}
        {/foreach}
				$db->bind(':id', (int)$id);
				$db->execute();
		{literal}}{/literal}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Delete data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public static function deleteById($id) {literal}{{/literal}
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'DELETE FROM ' . $db->table('{$DATA.data.database_table_raw}') . ' ' .
						'WHERE ' .
								'id = :id'
				);
				$db->bind(':id', (int)$id);
				$db->execute();
		{literal}}{/literal}
{literal}}{/literal}

{literal}?>{/literal}