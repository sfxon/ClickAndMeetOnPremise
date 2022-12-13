//////////////////////////////////////////////////////////////////////////////////////////
// Filter Funktionen für den Admin-Kalender (rechts oben über der Termin-Liste)
//////////////////////////////////////////////////////////////////////////////////////////
jQuery(function() {
		class mvAdminCmFilter {
				//////////////////////////////////////////////////////////////////////////////////
				// Konstruktor
				// Klassenweite Variablen und Einstellungen setzen.
				//////////////////////////////////////////////////////////////////////////////////
				constructor(config) {
						this.filter_button_selector = null;
						this.filter_editor_selector = null;
						this.adminCmFilter = this;
						this.base_url = '';
					
						if(typeof config.base_url !== 'undefined') {
								this.base_url = config.base_url;
						}
						
						if(typeof config.filter_button_selector !== 'undefined') {
								this.filter_button_selector = config.filter_button_selector;
						}
						
						if(typeof config.filter_editor_selector !== 'undefined') {
								this.filter_editor_selector = config.filter_editor_selector;
						}

						this.initActions();
				}
				
				//////////////////////////////////////////////////////////////////////////////////
				// Aktion-Handler (Re-Initialisieren)
				//////////////////////////////////////////////////////////////////////////////////
				initActions() {
						this.initFilterButtonActions();
				}
				
				//////////////////////////////////////////////////////////////////////////////////
				// Filter-Button Aktionen initialisieren.
				//////////////////////////////////////////////////////////////////////////////////
				initFilterButtonActions() {
						var self = this;
						
						if(this.filter_button_selector !== null) {
								jQuery(this.filter_button_selector).off('click');
								jQuery(this.filter_button_selector).on('click', function() {
										//Optionen einblenden/ausblenden
										this.adminCmFilter = self;
										
										var filter_new_status = self.getNewFilterStatus();
										
										//Anfrage an Server: Status des Views in Session setzen,
										self.updateFilterView(filter_new_status);
										
										//damit diese Option bei den nächsten Seitenaufrufen
										//(so lange die Session läuft) geöffnet ist.
										self.updateFilterViewState(filter_new_status);
								});
						}
				}
				
				//////////////////////////////////////////////////////////////////////////////////
				// Status abfragen.
				//////////////////////////////////////////////////////////////////////////////////
				getNewFilterStatus() {
						var status = 'visible';
												
						if(jQuery(this.adminCmFilter.filter_editor_selector).css('display') != 'none') {
								//Important! We change the status -> so we return the new status, not the current status!
								status = 'hidden';		
						}
						
						return status;
				}
				
				//////////////////////////////////////////////////////////////////////////////////
				// Ansicht anpassen.
				//////////////////////////////////////////////////////////////////////////////////
				updateFilterView(filter_new_status) {
						if(filter_new_status == 'hidden') {
								jQuery(this.adminCmFilter.filter_editor_selector).hide();
								
								//Update Button text.
								jQuery(this.adminCmFilter.filter_button_selector + ' span').html('<span class="dashicons dashicons-arrow-down"></span>Filter anzeigen');
						} else {
								jQuery(this.adminCmFilter.filter_editor_selector).show();
								
								//Update Button text.
								jQuery(this.adminCmFilter.filter_button_selector + ' span').html('<span class="dashicons dashicons-arrow-up"></span>Filter verstecken');
						}
				}
				
				//////////////////////////////////////////////////////////////////////////////////
				// Daten sichern.
				//////////////////////////////////////////////////////////////////////////////////
				updateFilterViewState(filter_new_status) {
						//Make a post to the server!
						var post_data = {
								//action: 'setFilterStatus',
								status: filter_new_status
						};
						
						var baseUrl = this.adminCmFilter.base_url;
						
						var saver = new mvCmFilterSaver(baseUrl, post_data);
						saver.startRequest();
				}
		}
		
		//////////////////////////////////////////////////////////////////////////////////////
		// Filter initialisieren.
		//////////////////////////////////////////////////////////////////////////////////////
		var AdminCmFilter = new mvAdminCmFilter({
				filter_button_selector: '#mv-calender-editor-show-filter-button',
				filter_editor_selector: '#mv-calender-editor-filter',
				base_url: $('#url').val()
		});
});