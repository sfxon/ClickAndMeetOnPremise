//////////////////////////////////////////////////////////////////////////////////////////
// Export Funktionen für den Admin-Kalender (unter der Termin-Liste)
//////////////////////////////////////////////////////////////////////////////////////////
jQuery(function() {
		class mvAdminCmExport {
				//////////////////////////////////////////////////////////////////////////////////
				// Konstruktor
				// Klassenweite Variablen und Einstellungen setzen.
				//////////////////////////////////////////////////////////////////////////////////
				constructor(config) {
						this.show_export_button_selector = null;
						this.export_editor_selector = null;
						this.adminCmExport = this;
						
						if(typeof config.show_export_button_selector !== 'undefined') {
								this.show_export_button_selector = config.show_export_button_selector;
						}
						
						if(typeof config.export_editor_selector !== 'undefined') {
								this.export_editor_selector = config.export_editor_selector;
						}

						this.initActions();
				}
				
				//////////////////////////////////////////////////////////////////////////////////
				// Aktion-Handler (Re-Initialisieren)
				//////////////////////////////////////////////////////////////////////////////////
				initActions() {
						this.initExportButtonActions();
						this.initExportDateSelector();
						this.initExportDatePresetSelectors();
				}
				
				//////////////////////////////////////////////////////////////////////////////////
				// Filter-Button Aktionen initialisieren.
				//////////////////////////////////////////////////////////////////////////////////
				initExportButtonActions() {
						var self = this;
						
						if(this.show_export_button_selector !== null) {
								jQuery(this.show_export_button_selector).off('click');
								jQuery(this.show_export_button_selector).on('click', function() {
										//Optionen einblenden/ausblenden
										this.adminCmExport = self;
										
										var export_new_status = self.getNewExportContainerStatus();
										
										//Anfrage an Server: Status des Views in Session setzen,
										self.updateExportContainerView(export_new_status);
										
										//Set Calendar "from" and "to" with current date.
										self.setCurrentDateForFromAndToInputFields();
								});
						}
				}
				
				//////////////////////////////////////////////////////////////////////////////////
				// Datums-Selektor initialisieren.
				//////////////////////////////////////////////////////////////////////////////////
				initExportDateSelector() {
						//Datums-Kalender für das Export-Feld "von" initialisieren
						new dtsel.DTS('input[id="mv-export-date-from"]', {
								direction: 'BOTTOM',
								dateFormat: 'dd.mm.YYYY',
								showTime: false,
								timeFormat: "HH:MM"
						});
						
						//Datums-Kalender für das Export-Feld "bis" initialisieren
						new dtsel.DTS('input[id="mv-export-date-to"]', {
								direction: 'BOTTOM',
								dateFormat: 'dd.mm.YYYY',
								showTime: false,
								timeFormat: "HH:MM"
						});
				}
				
				//////////////////////////////////////////////////////////////////////////////////
				// Vorauswahlen anklickbar machen.
				//////////////////////////////////////////////////////////////////////////////////
				initExportDatePresetSelectors() {
						self = this;
						
						jQuery('#mv-export-selection-presets .mv-selection').off('click');
						jQuery('#mv-export-selection-presets .mv-selection').on('click', function() {
								var option = jQuery(this).attr('data-attr-option');
								
								switch(option) {
										case 'chosen':
												self.setCurrentDateForFromAndToInputFields();
												break;
										case 'today':
												self.setTodayForFromAndToInputFields();
												break;
										case 'tomorrow':
												self.setTomorrowForFromAndToInputFields();
												break;
										case 'running-workweek':
												self.setRunningWorkweekForFromAndToInputFields(self);
												break;
										case 'this-week-full':
												self.setThisWeekFullForFromAndToInputFields(self);
												break;
										case 'this-month':
												self.setThisMonthForFromAndToInputFields(self);
												break;
								}
						});
				}
						
				//////////////////////////////////////////////////////////////////////////////////
				// Status abfragen.
				//////////////////////////////////////////////////////////////////////////////////
				getNewExportContainerStatus() {
						var status = 'visible';
											
						if(jQuery(this.adminCmExport.export_editor_selector).css('display') != 'none') {
								//Important! We change the status -> so we return the new status, not the current status!
								status = 'hidden';		
						}
						
						return status;
				}
				
				//////////////////////////////////////////////////////////////////////////////////
				// Aktuell ausewähltes Datum setzen in den Eingabefeldern
				// beim Export, wo man den Zeitraum auswählen kann.
				//////////////////////////////////////////////////////////////////////////////////
				setCurrentDateForFromAndToInputFields() {
						//Datum zusammenstellen.
						var year = jQuery('#mv-times-list-current-date-year').val();
						var month = jQuery('#mv-times-list-current-date-month').val();
						var day = jQuery('#mv-times-list-current-date-day').val();
						
						month = month.padStart(2, '0');
						day = day.padStart(2, '0');
						
						var date = day + '.' + month + '.' + year;
						
						//Werte in Eingabefelder setzen:
						jQuery('#mv-export-date-from').val(date);
						jQuery('#mv-export-date-to').val(date);
				}
				
				//////////////////////////////////////////////////////////////////////////////////
				// Heutiges Datum setzen in den Eingabefeldern
				// beim Export, wo man den Zeitraum auswählen kann.
				//////////////////////////////////////////////////////////////////////////////////
				setTodayForFromAndToInputFields() {
						var datetime = new Date();    
						
						//Datum zusammenstellen.
						var year = datetime.getFullYear().toString();
						var month = (datetime.getMonth() + 1).toString();
						var day = datetime.getDate().toString();
						
						month = month.padStart(2, '0');
						day = day.padStart(2, '0');
						
						var date = day + '.' + month + '.' + year;
						
						//Werte in Eingabefelder setzen:
						jQuery('#mv-export-date-from').val(date);
						jQuery('#mv-export-date-to').val(date);
				}
				
				//////////////////////////////////////////////////////////////////////////////////
				// Morgiges Datum setzen in den Eingabefeldern
				// beim Export, wo man den Zeitraum auswählen kann.
				//////////////////////////////////////////////////////////////////////////////////
				setTomorrowForFromAndToInputFields() {
						var datetime = new Date();  
						datetime.setDate(datetime.getDate() + 1)  
						
						//Datum zusammenstellen.
						var year = datetime.getFullYear().toString();
						var month = (datetime.getMonth() + 1).toString();
						var day = datetime.getDate().toString();
						
						month = month.padStart(2, '0');
						day = day.padStart(2, '0');
						
						var date = day + '.' + month + '.' + year;
						
						//Werte in Eingabefelder setzen:
						jQuery('#mv-export-date-from').val(date);
						jQuery('#mv-export-date-to').val(date);
				}
				
				//////////////////////////////////////////////////////////////////////////////////
				// Datum für die laufende Woche setzen in den Eingabefeldern
				// beim Export, wo man den Zeitraum auswählen kann.
				//////////////////////////////////////////////////////////////////////////////////
				setRunningWorkweekForFromAndToInputFields(self) {
						var datetime = new Date();   
						
						//Datum "von" zusammenstellen.
						var year = datetime.getFullYear().toString();
						var month = (datetime.getMonth() + 1).toString();
						var day = datetime.getDate().toString();
						
						month = month.padStart(2, '0');
						day = day.padStart(2, '0');
						
						var date = day + '.' + month + '.' + year;
						
						//Werte in Eingabefelder setzen:
						jQuery('#mv-export-date-from').val(date);
						
						//Datum "bis" zusammenstellen
						datetime = self.getSundayOfCurrentWeek(datetime);
						
						year = datetime.getFullYear().toString();
						month = (datetime.getMonth() + 1).toString();
						day = datetime.getDate().toString();
						
						month = month.padStart(2, '0');
						day = day.padStart(2, '0');
						
						date = day + '.' + month + '.' + year;
						
						jQuery('#mv-export-date-to').val(date);
				}
				
				//////////////////////////////////////////////////////////////////////////////////
				// Datum für die gesamte aktuelle Woche setzen in den Eingabefeldern
				// beim Export, wo man den Zeitraum auswählen kann.
				//////////////////////////////////////////////////////////////////////////////////
				setThisWeekFullForFromAndToInputFields(self) {
						var datetime = new Date();
						datetime = self.getMondayOfCurrentWeek(datetime);   
						
						//Datum "von" zusammenstellen.
						var year = datetime.getFullYear().toString();
						var month = (datetime.getMonth() + 1).toString();
						var day = datetime.getDate().toString();
						
						month = month.padStart(2, '0');
						day = day.padStart(2, '0');
						
						var date = day + '.' + month + '.' + year;
						
						//Werte in Eingabefelder setzen:
						jQuery('#mv-export-date-from').val(date);
						
						//Datum "bis" zusammenstellen
						datetime = self.getSundayOfCurrentWeek(datetime);
						
						year = datetime.getFullYear().toString();
						month = (datetime.getMonth() + 1).toString();
						day = datetime.getDate().toString();
						
						month = month.padStart(2, '0');
						day = day.padStart(2, '0');
						
						date = day + '.' + month + '.' + year;
						
						jQuery('#mv-export-date-to').val(date);
				}

				//////////////////////////////////////////////////////////////////////////////////
				// Datum für die gesamte aktuelle Woche setzen in den Eingabefeldern
				// beim Export, wo man den Zeitraum auswählen kann.
				//////////////////////////////////////////////////////////////////////////////////
				setThisMonthForFromAndToInputFields(self) {
						var datetime = new Date();
						var datetime = new Date(datetime.getFullYear(), datetime.getMonth()+1, 1);
						
						//Datum "von" zusammenstellen.
						var year = datetime.getFullYear().toString();
						var month = (datetime.getMonth()).toString();
						var day = datetime.getDate().toString();
						
						month = month.padStart(2, '0');
						day = day.padStart(2, '0');
						
						var date = day + '.' + month + '.' + year;
						
						//Werte in Eingabefelder setzen:
						jQuery('#mv-export-date-from').val(date);
						
						//Datum "bis" zusammenstellen
						datetime = new Date(datetime.getFullYear(), datetime.getMonth(), 0);		//Setting day parameter to 0 means one day less than first day of the month which is last day of the previous month.

						year = datetime.getFullYear().toString();
						month = (datetime.getMonth() + 1).toString();
						day = datetime.getDate().toString();
						
						month = month.padStart(2, '0');
						day = day.padStart(2, '0');
						
						date = day + '.' + month + '.' + year;
						
						jQuery('#mv-export-date-to').val(date);
				}
				//////////////////////////////////////////////////////////////////////////////////
				// Ansicht anpassen.
				//////////////////////////////////////////////////////////////////////////////////
				updateExportContainerView(export_new_status) {
						if(export_new_status == 'hidden') {
								jQuery(this.adminCmExport.export_editor_selector).hide();
								
								//Update Button text.
								jQuery(this.adminCmExport.show_export_button_selector + ' span').html('<span class="dashicons dashicons-arrow-down"></span>Export anzeigen');
						} else {
								jQuery(this.adminCmExport.export_editor_selector).show();
								
								//Update Button text.
								jQuery(this.adminCmExport.show_export_button_selector + ' span').html('<span class="dashicons dashicons-arrow-up"></span>Export verstecken');
						}
				}
				
				//////////////////////////////////////////////////////////////////////////////////
				// Montag der aktuellen Woche berechnen.
				//////////////////////////////////////////////////////////////////////////////////
				getMondayOfCurrentWeek(d) {
						var day = d.getDay();
						return new Date(d.getFullYear(), d.getMonth(), d.getDate() + (day == 0?-6:1)-day );
				}

				//////////////////////////////////////////////////////////////////////////////////
				// Sonntag der aktuellen Woche berechnen
				//////////////////////////////////////////////////////////////////////////////////
				getSundayOfCurrentWeek(d)
				{
						var day = d.getDay();
						return new Date(d.getFullYear(), d.getMonth(), d.getDate() + (day == 0?0:7)-day );
				}
		}
		
		//////////////////////////////////////////////////////////////////////////////////////
		// Filter initialisieren.
		//////////////////////////////////////////////////////////////////////////////////////
		var AdminCmExport = new mvAdminCmExport ({
				show_export_button_selector: '#mv-calender-editor-show-export-button',
				export_editor_selector: '#mv-calender-editor-export',
		});
});