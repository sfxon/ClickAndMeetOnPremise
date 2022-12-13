class mvFormSend {
		//////////////////////////////////////////////////////////////////
		// Constructor.
		//////////////////////////////////////////////////////////////////
		constructor(base_url, url_controller, form) {
				this.baseUrl = base_url;
				this.url_controller = url_controller;
				this.form = form;
		}
		
		//////////////////////////////////////////////////////////////////
		// Request starten.
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
			
				var my_queue = new mvUploadQueue(self, callbacks, this.error_callback);
				my_queue.process();
		}
		
		//////////////////////////////////////////////////////////////////
		// Timer-Daten - Querydaten für Ajax Request vorbereiten.
		//////////////////////////////////////////////////////////////////
		prepareRequest(queue) {
				var get_params = {
						s: queue.data.url_controller,
						action: 'ajaxChooseAppointment'
				};
				get_params = $.param(get_params);
				
				console.log(queue.data);
				
				var request = {
						url: queue.data.baseUrl + '?' + get_params,
						post_data: {
								appointment_id: queue.data.form.appointment_id,
								firstname: queue.data.form.firstname,
								lastname: queue.data.form.lastname,
								email_address: queue.data.form.email_address,
								email_reminder: queue.data.form.email_reminder,
								customers_number: queue.data.form.customers_number,
								phone: queue.data.form.phone,
								street: queue.data.form.street,
								plz: queue.data.form.plz,
								city: queue.data.form.city,
								comment_visitor_booking: queue.data.form.comment_visitor_booking,
								accept_agb: queue.data.form.accept_agb
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
								$('#kalender-content-container').hide();
								$('#kalender-success-container').show();
								
								//Scroll to success container
								$('html, body').animate({
										scrollTop: $("#mv-kalender-book-now").offset().top
								}, 0);
								
								
						} else if(data.status == "error") {
								switch(data.error) {
										case 'firstname':
												queue.data.showFieldErrorMessage('#mv-editor-firstname-error', 'Bitte geben Sie Ihren Vornamen ein.');
												queue.data.showGeneralError();
												break;
										case 'lastname':
												queue.data.showFieldErrorMessage('#mv-editor-lastname-error', 'Bitte geben Sie Ihren Nachnamen ein.');
												queue.data.showGeneralError();
												break;
										case 'email_address':
												queue.data.showFieldErrorMessage('#mv-editor-email-address-error', 'Bitte geben Sie Ihre E-Mail Adresse ein.');
												queue.data.showGeneralError();
												break;
										case 'appointment_id':
												queue.data.showFieldErrorMessage('#mv-additional-error-message', 'Leider wurde der Termin gerade eben vergeben. Bitte wähle einen anderen Termin.');
												break;
								}
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
				$('#mv-editor-save-error').html('Es ist ein unerwarteter Fehler aufgetreten. Bitte versuche es erneut.');
				$('#mv-editor-save-error').show();
				
				console.log(error);
				alert('Es ist ein Fehler aufgetreten. Weitere Informationen finden Sie in der Konsole.');
		}
		
		//////////////////////////////////////////////////////////////////
		// Fehlermeldung anzeigen.
		//////////////////////////////////////////////////////////////////
		showGeneralError(msg) {
				$('#mv-editor-save-error').html('Es ist ein Fehler aufgetreten. Bitte versuche es erneut.');
				$('#mv-editor-save-error').show();
		}
		
		//////////////////////////////////////////////////////////////////
		// Fehlermeldung anzeigen.
		//////////////////////////////////////////////////////////////////
		showFieldErrorMessage(css_selector, msg) {
				$(css_selector).html(msg);
				$(css_selector).show();
		}
}