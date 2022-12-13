class mvCmCalLoadInitial {
		////////////////////////////////////////////////////////////////////////////
		// Konstruktor
		////////////////////////////////////////////////////////////////////////////
		constructor(data) {
				this.url_selector = null;
				this.url_controller = null;
				this.url_action = null;
				this.kalendar_container = null;
				this.timer_container = null;
				this.event_location_container = null;
				this.user_unit_container = null;
				
				//Leave this values = 0, if you want to fetch the current moment!
				this.day = 0;
				this.month = 0;
				this.year = 0;
				
				if(typeof data != 'undefined') {
						if(typeof data.url_selector != 'undefined') {
								this.url_selector = data.url_selector;
						}
						
						if(typeof data.url_controller != 'undefined') {
								this.url_controller = data.url_controller;
						}
						
						if(typeof data.url_action != 'undefined') {
								this.url_action = data.url_action;
						}
						
						if(typeof data.kalendar_container != 'undefined') {
								this.kalendar_container = data.kalendar_container;
						}
						
						if(typeof data.timer_container != 'undefined') {
								this.timer_container = data.timer_container;
						}
						
						if(typeof data.event_location_container != 'undefined') {
								this.event_location_container = data.event_location_container;
						}
						
						if(typeof data.user_unit_container != 'undefined') {
								this.user_unit_container = data.user_unit_container;
						}
				}
				
				this.base_url = '';
		}
		
		////////////////////////////////////////////////////////////////////////////
		// Start request.
		////////////////////////////////////////////////////////////////////////////
		loadCalender() {
				var self = this;
				
				if(self.url_selector != null) {
						self.base_url = $(this.url_selector).val();
				} else {
						self.base_url = '';
				}
				
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
		
		////////////////////////////////////////////////////////////////////////////
		// Request vorbereiten.
		////////////////////////////////////////////////////////////////////////////
		prepareRequest(queue) {
				var get_params = {
						s: queue.data.url_controller,
						action: queue.data.url_action
				};
				get_params = $.param(get_params);
				
				var request = {
						url: queue.data.base_url + '?' + get_params,
						post_data: {
								event_location: queue.data.event_location,
								user_unit_id: queue.data.user_unit_id,
								day: queue.data.day,
								month: queue.data.month,
								year: queue.data.year
						},
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
								$(queue.data.kalendar_container).html(data.data.calendar_html);
								$(queue.data.timer_container).html(data.data.timer_html);
								$(queue.data.event_location_container).html(data.data.event_location_html);
								$(queue.data.user_unit_container).html(data.data.user_unit_html);
								
								cal = new theCalender({
										elementEventLocationSelector: '#mv-kalender-event-location',
										elementUserUnitSelector: '#mv-kalender-user-unit',
										elementMonthInputSelector: '#current_month',
										elementYearInputSelector: '#current_year',
										elementPrevMonthSelector: '.kalender-top-nav-prev',
										elementNextMonthSelector: '.kalender-top-nav-next',
										daySelector: '.kalender-entry',
										daySelectorClickCallback: mv_calendar_day_clicked,
										url_controller: queue.data.url_controller
								});
						}
						
						return true;
				} catch(e) {
						console.log("Ergebnis: ", result, "Exception: ", e);
				}
				
				return false;
		}
		
		//////////////////////////////////////////////////////////////////
		// Data-Load error handler.
		//////////////////////////////////////////////////////////////////
		error_callback(error) {
				console.log(error);
				alert('Es ist ein Fehler aufgetreten. Weitere Informationen finden Sie in der Konsole.');
		}
}