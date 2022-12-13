class mvCmTimerSaveAppointment {
		//////////////////////////////////////////////////////////////////
		// Constructor.
		//////////////////////////////////////////////////////////////////
		constructor(base_url, data) {
				this.baseUrl = base_url;
				this.data = data;
		}
		
		//////////////////////////////////////////////////////////////////
		// Kalenderdaten - Querydaten aufbereiten.
		//////////////////////////////////////////////////////////////////
		startRequest() {
				var self = this;
				
				var callbacks = [
						{
								//Upload company if not selected..
								callback: self.prepareRequest,
								result_callback: self.processRequestResult,
								type: 'ajax'
						}
				];
			
				var my_queue = new mvUploadQueue(self, callbacks, self.error_callback);
				my_queue.process();
		}
		
		//////////////////////////////////////////////////////////////////
		// Timer-Daten - Querydaten für Ajax Request vorbereiten.
		//////////////////////////////////////////////////////////////////
		prepareRequest(queue) {
				var get_params = {
						s: 'cAdminCmCalendar',
						action: 'ajaxSaveAppointment'
				};
				get_params = $.param(get_params);
				
				var request = {
						url: queue.data.baseUrl + '?' + get_params,
						post_data: queue.data.data,
						mode: 'POST'
				};
				
				return request;
		}
		
		//////////////////////////////////////////////////////////////////
		// Anfrage-Ergebnis verarbeiten.
		//////////////////////////////////////////////////////////////////
		processRequestResult(queue, result) {
				//Try to parse the result
				try {
						var data = $.parseJSON(result);
						
						if(data.status == "success") {
								var base_url = data.data.base_url;
								var day = data.data.day;
								var month = data.data.month;
								var year = data.data.year;
								
								//Close editor..
								$('#mvCmCalendarSuccessMessage').find('.alert').html('Der Termin wurde erfolgreich gespeichert');
								$('#mvCmCalendarSuccessMessage').show();
								
								$('#AppointmentEditor').modal('hide');
								
								//Update calendar
								cal.updateCalendar();
								
								//Update list
								mv_calendar_day_clicked({
										day: day,
										month: month, 
										year: year
								});
						} else if(data.status == "error") {
								switch(data.error) {
										case 'unknown_appointment':
												alert('Beim Speichern des Datensatzes ist etwas schiefgegangen. Bitte lade die Seite neu.');
												return false;
										case 'last_save_datetime_mismatch':
												alert('Der Datensatz wurde zwischenzeitig von einem anderen Benutzer oder Kunden verändert. Bitte lade die Seite neu, wenn du den Datensatz trotzdem bearbeiten möchtest.');
												return false;
								}
						}
						
						return true;
				} catch(e) {
						console.log("Ergebnis: ", result, "Exception: ", e);
				}
				
				return false;
		}
		
		//////////////////////////////////////////////////////////////////
		// Fehler-Callback für visuelle Fehlerausgabe.
		//////////////////////////////////////////////////////////////////
		error_callback() {
				alert('Es ist ein Fehler aufgetreten in mvCmTimerSaveAppointment. Bitte versuchen Sie es erneut. Fehlerdetails finden Sie in der Konsole.');
		}
}
	