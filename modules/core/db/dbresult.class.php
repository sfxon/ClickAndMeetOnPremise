<?php 
///////////////////////////////////////////////////////////////////////////////////////////////////
// Datenbank-Abfrageergebnis Klasse.
// Diese Klasse enthält das Ergebnis einer Datenbankabfrage.
///////////////////////////////////////////////////////////////////////////////////////////////////
class cDBResult {
		var $error;
		var $result;
		var $gotFirstRow;
		var $data;
		var $debug;
		var $connection;
		
		/////////////////////////////////////////////////////////////////////////////////////////////////
		// Konstruktor
		/////////////////////////////////////////////////////////////////////////////////////////////////
		function __construct($result, $errormessage = '', $query = '') {
				if($errormessage == '') {
						$this->error = false;
				}
				$this->debug = true;
				$this->result = $result;
				$this->data   = '';
				$this->gotFirstRow = false;
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////////////
		// Wert abfragen
		/////////////////////////////////////////////////////////////////////////////////////////////////
		function value($index) {
				if(!$this->gotFirstRow) {
						$this->next();
				}

				if( empty($this->data[$index]) ) {
						if(!isset($this->data[$index])) {
								echo $this->data[$index];
								
								if($this->debug == true) {
										echo mysql_error();
										echo $index;
								}
								echo 'Es ist ein Fehler aufgetreten. Bitte informieren Sie den Server Betreiber.';
								die;
						}

						return $this->data[$index];
				}

				return($this->data[$index]);
		}

		/////////////////////////////////////////////////////////////////////////////////////////////////
		// Wert abfragen
		// Gibt auch bei 0 Werten einen Wert zurück!
		/////////////////////////////////////////////////////////////////////////////////////////////////
		function valueNULL($index) {
				if(!$this->gotFirstRow) {
						$this->next();
				}

				return($this->data[$index]);
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////////////
		// get next row
		/////////////////////////////////////////////////////////////////////////////////////////////////
		function next() {
				$this->data = $this->result->fetch( PDO::FETCH_ASSOC );

				if($this->data === false) {
						return(false);
				}
				
				$this->gotFirstRow = true;
				return(true);
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////////////
		// Assoziatives Array abrufen
		/////////////////////////////////////////////////////////////////////////////////////////////////
		function fetchArrayAssoc() {
				$retval = array();
				
				if(!$this->gotFirstRow) {
						$this->next();
				}
				
				return($this->data);
		}
}

?>