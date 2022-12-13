//////////////////////////////////////////////////////////////////////////////////////////
// Filter Funktionen für den Admin-Kalender (rechts oben über der Termin-Liste)
//	-> Termin-Status Filter
//////////////////////////////////////////////////////////////////////////////////////////
jQuery(function() {
		class mvAdminCmFilterStatus {
				//////////////////////////////////////////////////////////////////////////////////
				// Konstruktor
				// Klassenweite Variablen und Einstellungen setzen.
				//////////////////////////////////////////////////////////////////////////////////
				constructor(config) {
						this.checkboxCssSelector = null;
						this.adminCmFilterStatus = this;
						this.base_url = '';
						
						if(typeof config.base_url !== 'undefined') {
								this.base_url = config.base_url;
						}
						
						if(typeof config.checkboxCssSelector !== 'undefined') {
								this.checkboxCssSelector = config.checkboxCssSelector;
						}
						
						this.initActions();
				}
				
				//////////////////////////////////////////////////////////////////////////////////
				// Aktion-Handler (Re-Initialisieren)
				//////////////////////////////////////////////////////////////////////////////////
				initActions() {
						this.initCheckboxActions();
				}
				
				//////////////////////////////////////////////////////////////////////////////////
				// Aktionen für die Checkboxen initialisieren.
				//////////////////////////////////////////////////////////////////////////////////
				initCheckboxActions() {
						var self = this;
						
						if(this.checkboxCssSelector !== null) {
								jQuery(this.checkboxCssSelector).off('click');
								jQuery(this.checkboxCssSelector).on('click', function() {
										//Optionen einblenden/ausblenden
										this.adminCmFilterStatus = self;
										
										var status_id = self.getClickedCheckboxesStatus(this);
										var click_state = self.getClickedCheckboxesState(this);
										
										//Anfrage an Server: Status der Checkbox in Session setzen,
										//damit diese Option bei den nächsten Seitenaufrufen
										//(so lange die Session läuft) ausgewählt ist.
										self.updateFilterViewState(status_id, click_state);
								});
						}
				}
				
				//////////////////////////////////////////////////////////////////////////////////
				// ID einer Checkbox abfragen 
				// Das ist in diesem Fall hier immer auch die ID des Status).
				//////////////////////////////////////////////////////////////////////////////////
				getClickedCheckboxesStatus(item) {
						var id = jQuery(item).attr('id');
						
						id = id.replace('mv-filter-status-', '');		//Remove the prefix.
						
						return id;
				}
				
				//////////////////////////////////////////////////////////////////////////////////
				// Check, if a checkbox is checked.
				//////////////////////////////////////////////////////////////////////////////////
				getClickedCheckboxesState(item) {
						var status = 'not-checked';
						
						if(jQuery(item).is(":checked")) {
								status = 'checked';
						}
						
						return status;
				}
				
				//////////////////////////////////////////////////////////////////////////////////
				// View-State aktualisieren.
				//////////////////////////////////////////////////////////////////////////////////
				updateFilterViewState(status_id, click_state) {
						//Make a post to the server!
						var post_data = {
								'status_id': status_id,
								'click_state': click_state
						};
						
						var baseUrl = this.adminCmFilterStatus.base_url;
						
						var saver = new mvCmFilterStatusSaver(baseUrl, post_data);
						saver.startRequest();
				}
		}
		
		//////////////////////////////////////////////////////////////////////////////////////
		// Filter initialisieren.
		//////////////////////////////////////////////////////////////////////////////////////
		var AdminCmFilterStatus = new mvAdminCmFilterStatus({
				checkboxCssSelector: '.mv-filter-status',
				base_url: jQuery('#url').val()
		});
});