class mvCmTimerDeleteAppointment {
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
						action: 'ajaxDeleteAppointment'
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
								var day = queue.data.data.day;
								var month = queue.data.data.month;
								var year = queue.data.data.year;
								
								//Close editor..
								$('#mvCmCalendarSuccessMessage').find('.alert').html('Der Termin wurde gelöscht.');
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
				alert('Es ist ein Fehler aufgetreten in mvCmTimerDeleteAppointment. Bitte versuchen Sie es erneut. Fehlerdetails finden Sie in der Konsole.');
		}
}
	