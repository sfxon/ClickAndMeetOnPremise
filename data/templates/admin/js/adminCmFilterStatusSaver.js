class mvCmFilterStatusSaver {
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
		// Query-Daten für den Ajax-Request vorbereiten.
		//////////////////////////////////////////////////////////////////
		prepareRequest(queue) {
				var tmpUploadQueryBuilder = new mvUploadQueryBuilder();
				var get_params = tmpUploadQueryBuilder.makeByPlatform('blitz', 'mvclickandmeet', 'ajaxUpdateFilterStatus');
				
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
				//Update calendar
				cal.updateCalendar();
		
				//Update list
				mv_calendar_day_clicked({
						day: jQuery('#mv-kalender-current-selected-day').val(),
						month: jQuery('#mv-kalender-current-selected-month').val(), 
						year: jQuery('#mv-kalender-current-selected-year').val()
				});
		}
		
		//////////////////////////////////////////////////////////////////
		// Fehler-Callback für visuelle Fehlerausgabe.
		//////////////////////////////////////////////////////////////////
		error_callback() {
				alert('Es ist ein Fehler aufgetreten in adminCmFilterStatusSaver.js. Bitte versuche es erneut. Fehlerdetails findest du in der Konsole.');
		}
}
	