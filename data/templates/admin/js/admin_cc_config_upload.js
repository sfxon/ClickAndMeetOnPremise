function mv_cc_upload_start() {
		if(confirm("Haben Sie alles überprüft? Wollen Sie das Anlegen der Termine wirklich starten?")) {
				//Hide container and show process view..
				$('#mv-config-container').hide();
				$('#mv-process-container').show();
				$('#mv-loading-spinner-text').html('Verarbeitung wird gestartet');
				
				var uploader = new mvAdminCcConfigUpload();
				uploader.startUpload();
		}
}

//////////////////////////////////////////////////////////////////////////////////////
// Class to handle upload..
//////////////////////////////////////////////////////////////////////////////////////
class mvAdminCcConfigUpload {
		//////////////////////////////////////////////////////////////////////////////////
		// constructor builds variables
		//////////////////////////////////////////////////////////////////////////////////
		constructor() {
				this.url = "";
				this.event_location_id = 0;
				this.user_unit_id = 0;
				this.date_from_day = 0;
				this.date_from_month = 0;
				this.date_from_year = 0;
				this.date_to_day = 0;
				this.date_to_month = 0;
				this.date_to_year = 0;
				this.weekdays_and_times = [];
				this.appointment_duration_in_minutes = 0;
				this.appointment_count = 0;
				
				//Load data..
				this.loadUrl();
				this.loadEventLocationId();
				this.loadUserUnitId();
				this.loadDateFromParts();
				this.loadDateToParts();
				this.loadWeekdaysAndTimes();
				this.loadAppointmentDurationInMinutes();
				this.loadAppointmentCount();
				
				this.current_month = this.date_from_month;
				this.current_year = this.date_from_year;
		}
		
		//////////////////////////////////////////////////////////////////////////////////
		// Lade Basisadresse der Webseite.
		//////////////////////////////////////////////////////////////////////////////////
		loadUrl() {
				this.url = $('#url').val();
		}
		
		//////////////////////////////////////////////////////////////////////////////////
		// Lade ID der EventLocation
		//////////////////////////////////////////////////////////////////////////////////
		loadEventLocationId() {
				this.event_location_id = $('#event_location').val();
		}
		
		//////////////////////////////////////////////////////////////////////////////////
		// Abteilung/Mitarbeiter laden.
		//////////////////////////////////////////////////////////////////////////////////
		loadUserUnitId() {
				this.user_unit_id = $('#user-unit').val();
		}
		
		//////////////////////////////////////////////////////////////////////////////////
		// "Datum von" aus Eingabefeld laden und in einzelne Variablen reinkippen..
		//////////////////////////////////////////////////////////////////////////////////
		loadDateFromParts() {
				var dateFrom = new mvDate();
				var result = dateFrom.loadFromInputField('#date_from');
				
				if(result != false) {
						this.date_from_day = dateFrom.day;
						this.date_from_month = dateFrom.month;
						this.date_from_year = dateFrom.year;
				} else {
						this.error = true;
				}
		}
		
		//////////////////////////////////////////////////////////////////////////////////
		// "Datum bis" aus Eingabefeld laden und in einzelne Variablen reinkippen..
		//////////////////////////////////////////////////////////////////////////////////
		loadDateToParts() {
				var dateTo = new mvDate();
				var result = dateTo.loadFromInputField('#date_to');
				
				if(result != false) {
						this.date_to_day = dateTo.day;
						this.date_to_month = dateTo.month;
						this.date_to_year = dateTo.year;
				} else {
						this.error = true;
				}
		}
		
		//////////////////////////////////////////////////////////////////////////////////
		// Zeitwerte laden.
		//////////////////////////////////////////////////////////////////////////////////
		loadWeekdaysAndTimes() {
				var self = this;
				var weekdays = [];
				
				//Durch alle aktivierten Zeilen laufen..
				$('.weekday-checkbox').each(function() {
						var item = this;
						var status = $(item).is(':checked');
						
						//Zeilen-ID abfragen
						//Id heraussuchen.
						var parent = $(item).closest('.mv-weekday-row');
						var id = $(parent).attr('id');
						var id_int = id.replace('weekday-row-', '');
						
						if(true == status) {
								var data = self.mvLoadTimesForWeekday(id);
						}
						
						weekdays.push(
								{
										weekday: id_int,
										status: status,
										data: data
								}
						);
				});
				
				this.weekdays_and_times = weekdays;
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////
		// Zeitraum - Range für einen Wochentag testen
		//////////////////////////////////////////////////////////////////////////////////////////
		mvLoadTimesForWeekday(weekday_id) {
				var self = this;
				var retval = [];
				var selector = '#' + weekday_id + ' .mv-weekday-times-row';
				
				$(selector).each(function() {
						var data =  self.mvLoadTimesForWeekdayRow(this);
						
						if(false !== data) {
								retval.push(data);
						}
				});
				
				return retval;
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////
		// Zeitraum - Range für einen Eintrag laden.
		//////////////////////////////////////////////////////////////////////////////////////////
		mvLoadTimesForWeekdayRow(item) {
				var timeFrom = new mvTime();
				var result1 = timeFrom.loadFromInputField($(item).find('.time_from'));
				
				var timeTo = new mvTime();
				var result2 = timeTo.loadFromInputField($(item).find('.time_to'));
				
				if(false == result1 || false == result2) {
						return false;
				}
				
				var data = {
						timeFrom: timeFrom,
						timeTo: timeTo
				};
				
				return data;
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////
		// Dauer je Termin in Minuten laden.
		//////////////////////////////////////////////////////////////////////////////////////////
		loadAppointmentDurationInMinutes() {
				var value = $('#dauer_in_minuten').val();
				this.appointment_duration_in_minutes = parseInt(value);
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////
		// Anzahl an freien Slots pro Termin
		//////////////////////////////////////////////////////////////////////////////////////////
		loadAppointmentCount() {
				var value = $('#appointment_count').val();
				this.appointment_count = parseInt(value);
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////
		// Event location dropdown anhand von Datenstruktur aktualisieren.
		//////////////////////////////////////////////////////////////////////////////////////////
		updateEventLocationDropdown(event_locations) {
				$('#betriebsstaette').empty();		//Remove all values
				
				//Add default value
				$('#betriebsstaette').append(
						$(
								'<option>', {
										value: 0,
										text: '-- Bitte auswählen --'
								}, 
								'</option>'
						)
				);
				
				for(var i = 0 ; i < event_locations.length; i++) {
						var id = event_locations[i].id;
						var title = event_locations[i].title;
						
						//Add default value
						$('#betriebsstaette').append(
								$(
										'<option>', {
												value: id,
												text: title
										}, 
										'</option>'
								)
						);
				}
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////
		// Ausgabe des Verarbeitungsschrittes aktualisieren.
		//////////////////////////////////////////////////////////////////////////////////////////
		updateVerarbeitungsSchrittAusgabe(message) {
				$('#mv-loading-spinner-text').html(message);
		}

		//////////////////////////////////////////////////////////////////////////////////////////
		// Upload starten.
		//////////////////////////////////////////////////////////////////////////////////////////
		startUpload() {
				var callbacks = [
						{
								//Upload dates month by month..
								callback: this.uploadMonths,
								result_callback: this.uploadMonthsResult,
								type: 'ajax'
						},
						{
								callback: this.finish_upload,
								type: 'plain'
						}
				];
			
				var my_queue = new mvUploadQueue(this, callbacks, this.error_callback);
				my_queue.process();
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////
		// Callback für: Upload eines Monats.
		//////////////////////////////////////////////////////////////////////////////////////////
		uploadMonths(queue) {
				var get_params = {
						s: 'cAdminCcConfig',
						action: 'ajaxSaveMonth'
				};
				get_params = $.param(get_params);
				
				var post_params = {
						//Aktueller Durchlauf
						current_month: 1 + queue.data.current_month,				//Javascript begins months with zero..
						current_year: queue.data.current_year,
						
						//Datum ab
						date_from_day: queue.data.date_from_day,
						date_from_month: 1 + queue.data.date_from_month,		//Javascript begins months with zero..
						date_from_year: queue.data.date_from_year,
						
						//Datum bis
						date_to_day: queue.data.date_to_day,
						date_to_month: 1 + queue.data.date_to_month,				//Javascript begins months with zero..
						date_to_year: queue.data.date_to_year,
						
						//Wochentage und Zeiten
						weekdays_and_times: queue.data.weekdays_and_times,
						
						//Weitere Einstellungen
						appointment_duration_in_minutes: queue.data.appointment_duration_in_minutes,
						appointment_count: queue.data.appointment_count,
						event_location_id: queue.data.event_location_id,
						user_unit_id: queue.data.user_unit_id
				};
				
				var request = {
						url: queue.data.url + '?' + get_params,
						post_data: post_params,
						mode: 'POST'
				};
				
				queue.data.updateVerarbeitungsSchrittAusgabe("Daten für den Monat " + post_params.current_month.toString().padStart(2, '0') + '.' + post_params.current_year + " anlegen.");
				
				return request;
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////
		// Callback für Result: Upload eines Monats.
		//////////////////////////////////////////////////////////////////////////////////////////
		uploadMonthsResult(queue, result) {
				//Try to parse the result
				try {
						var data = $.parseJSON(result);
						
						if(data.status == "success") {
								//Prüfen ob wir über den Zeitraum hinaus sind.
								var month = queue.data.current_month;			//Javascript begins months with zero..
								var year = queue.data.current_year;
								
								var d = new Date(year, month, 1);
								d.setMonth(d.getMonth() + 1);
								
								queue.data.current_month = d.getMonth();
								queue.data.current_year = d.getFullYear();	//do not use getYear, because it has the year 2000 bug!
								
								//Wenn wir über das Jahr hinaus sind..
								if(queue.data.current_year > queue.data.date_to_year) {
										return true;
								}
								
								//Wenn wir im selben Jahr sind und über den Monat hinaus sind.
								if(queue.data.current_year == queue.data.date_to_year) {
										if(queue.data.current_month > queue.data.date_to_month) {
												return true;
										}
								}
								
								//Wenn wir noch nicht drüber hinaus sind, den Counter nochmal zurücksetzen, und den selben Schritt nochmal ausführen.
								//Er wird ja jetzt mit dem nächsten Monat ausgeführt. Das läuft dann so lange, bis alle Monate abgearbeitet sind.
								queue.current_callback = queue.current_callback - 1;
								
								return true;
						}
						
						//queue.data.updateVerarbeitungsSchrittAusgabe("Neue Event-Location erfolgreich angelegt.");

						return true;
				} catch(e) {
						queue.error_callback('upload_company_result_error', "Es ist ein Fehler beim Anlegen der Betriebsstätte aufgetreten. Die Verarbeitung wurde sicherheitshalber gestoppt. Bitte prüfen Sie alle Daten, und versuchen Sie es erneut. Fehlerdetails finden Sie in der Console.");
						console.log("Ergebnis: ", result, "Exception: ", e);
				}
				
				return false;
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////
		// Callback für: Firma auf dem Server anlegen, wenn das hier ein regulärer Call ist.
		//////////////////////////////////////////////////////////////////////////////////////////
		finish_upload(queue) {
				$('#mv-process-container').hide();
				$('#mv-success-container').show();
				mv_init_restart_button();
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////
		// Callback für: Firma auf dem Server anlegen, wenn das hier ein regulärer Call ist.
		//////////////////////////////////////////////////////////////////////////////////////////
		error_callback(error_id, message) {
				console.log("Es ist ein Fehler aufgetreten. " + message);
				
				$('.mv-loading-spinner').hide();
				$('#mv-loading-error').html('Es ist ein Fehler aufgetreten. Die Verarbeitung wurde gestoppt. Fehlerdetails: ' + message);
				$('#mv-loading-error').show();
		}
		
}