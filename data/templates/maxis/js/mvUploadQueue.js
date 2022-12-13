class mvUploadQueue {
		//////////////////////////////////////////////////////////////////////////////////
		// constructor builds variables
		// Parameters:
		//	data: Objekt mit benötigten Daten. Die Callback Funkktionen greifen auf die Daten zu.
		//  callbacks: Array mit Callback Funktionen, die der Reihe nach aufgerufen werden.
		//  Jede Callback Funktion sollte als ersten Parameter einen Zeiger auf diese Queue hier erhalten,
		//  damit sie auf die Daten zugreifen kann.
		//////////////////////////////////////////////////////////////////////////////////
		constructor(data, callbacks, error_callback) {
				this.data = data;
				this.callbacks = callbacks;		//Array of callback function pointers...
				this.error_callback = error_callback;
				this.current_callback = 0;
		}
		
		//////////////////////////////////////////////////////////////////////////////////
		// Process callbacks.
		//////////////////////////////////////////////////////////////////////////////////
		process() {
				if(this.current_callback < this.callbacks.length) {
						var tmp_current_callback = this.current_callback;
						this.current_callback++;
						
						var data = this.callbacks[tmp_current_callback].callback(this);
						
						switch(this.callbacks[tmp_current_callback].type) {
								case 'ajax':
										this.ajax(data, tmp_current_callback);
										break;
								case 'plain':
										this.plain(data, tmp_current_callback);
										break;
								default:
										console.log('No valid type defined for callback. Reverting to plain (to maybe call the next one..)');
										this.plain(data, tmp_current_callback);
										break;
						}
				}
		}
		
		//////////////////////////////////////////////////////////////////////////////////
		// Ajax call -> führt eine Ajax Anfrage durch!
		//////////////////////////////////////////////////////////////////////////////////
		ajax(data, current_callback) {
				var self = this;
				
				//Check if the request is wanted..
				if(false == data) {
						self.process();
						return true;
				}
				
				if(null == data) {
						console.log('Got NULL data in ajax request function: ', this.callbacks[(this.current_callback-1)]);
						this.error_callback('ajax_invalid_data', 'Got NULL data in ajax request function.');
						return null;
				} else {
						//check params
						if(typeof data.url == 'undefined') {
								console.log('Invalid URL in data.url');
								this.error_callback(data, 'ajax_invalid_url', 'Invalid URL in data.url');
								return;
						}
						
						if(typeof data.post_data == 'undefined') {
								console.log('Invalid URL in data.post_data');
								this.error_callback(data, 'ajax_invalid_post', 'Invalid POST in data.post_data');
						}
						
						if(typeof data.mode == 'undefined') {
								console.log('Invalid MODE in data.post_data');
								this.error_callback(data, 'ajax_invalid_mode', 'Invalid MODE in data.post_data');
								return;
						}
						
						//Ajax call
						$.ajax({
								url: data.url,
								method: data.mode,
								data: data.post_data
						}).done(function( msg ) {
								if(typeof self.callbacks[current_callback].result_callback != 'undefined') {
										if(true != self.callbacks[current_callback].result_callback(self, msg)) {
												return false;
										}
								}
								
								//Call next step..
								self.process();
						});
				}
		}
		
		//////////////////////////////////////////////////////////////////////////////////
		// Plain call -> Führt eine "plain" Abfrage durch.
		//////////////////////////////////////////////////////////////////////////////////
		plain(data) {
				this.process();
		}
};