class mvForm {
		//////////////////////////////////////////////////////////////////
		// Constructor: Form initialisieren.
		//////////////////////////////////////////////////////////////////
		constructor() {
				
		}
		
		//////////////////////////////////////////////////////////////////
		// Aktionen initialisieren.
		//////////////////////////////////////////////////////////////////
		initActions() {
				var self = this;
				
				$('#mv-editor-save').off('click');
				$('#mv-editor-save').on('click', function(event) {
						event.stopPropagation();
						event.preventDefault();
						
						self.onSaveButtonClick(self, this);
				});
		}
		
		//////////////////////////////////////////////////////////////////
		// Aktionen initialisieren.
		//////////////////////////////////////////////////////////////////
		onSaveButtonClick(self, item) {
				self.hideAllErrors();
				
				//Daten prüfen und zusammenstellen.
				var form = self.fetchData(self);
				
				if(false === form) {
						return false;
				}
				
				$(item).hide();
				$('#mv-editor-saving').show();
				
				var base_url = $('#mv-base-url').val();
				var url_controller = $('#mv_url_controller').val();
				
				var sender = new mvFormSend(base_url, url_controller, form);
				sender.startRequest();
		}
		
		
		
		//////////////////////////////////////////////////////////////////
		// Form anzeigen.
		//////////////////////////////////////////////////////////////////
		showForm() {
				$('#mv-kalender-book-now').slideDown();

				$('html, body').animate({
						scrollTop: $("#mv-kalender-book-now").offset().top
				}, 800);
		}
		
		//////////////////////////////////////////////////////////////////
		// Form verstecken.
		//////////////////////////////////////////////////////////////////
		hideForm() {
				$('#mv-kalender-book-now').slideUp();
		}
		
		//////////////////////////////////////////////////////////////////
		// Daten prüfen und zusammenstellen.
		//////////////////////////////////////////////////////////////////
		fetchData(self) {
				if(self.checkInputFields(self) == false) {
						return false;
				}
				
				//Alle Daten abrufen.
				var form = self.fetchAllDataFromInputFields();
				
				return form;
				
		}
		
		//////////////////////////////////////////////////////////////////
		// fetchAllDataFromInputFields
		//////////////////////////////////////////////////////////////////
		fetchAllDataFromInputFields(self) {
				var form = new mvFormData();
				
				//Fetch Appointment id
				form.appointment_id = $('#editor-appointment-id').val();
				
				form.comment_visitor_booking = $('#editor-comment-visitor-booking').val();
				
				//Fetch information about the customer.
				form.firstname = $('#editor-firstname').val();
				form.lastname = $('#editor-lastname').val();
				form.email_address = $('#editor-email-address').val();
				form.email_reminder = $('#editor-email-reminder').val();
				form.customers_number = $('#editor-customers-number').val();
				form.phone = $('#editor-phone').val();
				form.street = $('#editor-street').val();
				form.plz = $('#editor-plz').val();
				form.city = $('#editor-city').val();
				form.accept_agb = $('#editor-accept-agb').val();
				
				return form;
		}

		//////////////////////////////////////////////////////////////////
		// Alle Eingabefelder überprüfen.
		//////////////////////////////////////////////////////////////////
		checkInputFields(self) {
				self.errors = false;
				var accept_null = true;
				var do_not_accept_null = false;
				
				self.checkFieldInt(self, '#editor-appointment-id', accept_null, '#editor-appointment-id-error', 'Bitte geben Sie einen gültigen Wert ein!', false);
				
				//Weitere Eingabefelder prüfen.
				self.checkFieldText(self, '#editor-firstname', 2, '#editor-firstname-error', $('#mv-error-text-editor-firstname').val());
				self.checkFieldText(self, '#editor-lastname', 2, '#editor-lastname-error', $('#mv-error-text-editor-lastname').val());
				self.checkFieldText(self, '#editor-email-address', 5, '#editor-email-address-error', $('#mv-error-text-editor-email-address').val());
				
				/*self.checkFieldInt(self, '#editor-email-reminder', true, '#editor-email-reminder-error', 'Sie müssen auswählen, ob wir Sie per E-Mail kontaktieren dürfen oder nicht.', false);*/
				self.checkFieldInt(self, '#editor-accept-agb', do_not_accept_null, '#editor-accept-agb-error', $('#mv-error-text-editor-accept-agb').val(), 1);
				
				if(self.errors != false) {
						self.showGeneralError('Es ist ein Fehler aufgetreten. Bitte überprüfen Sie Ihre Eingaben!');
						return false;
				}
				
				return true;
		}
		
		//////////////////////////////////////////////////////////////////
		// Prüfe Integer Feld..
		//////////////////////////////////////////////////////////////////
		checkFieldInt(self, css_selector, accept_null, css_selector_error_container, error_message, required_value) {
				var value = $(css_selector).val();
				var tmp = parseInt(value);
				
				if(isNaN(tmp)) {
						if(accept_null == false) {
								self.errors = true;
								self.outputFieldError(css_selector_error_container, error_message);
								return false;
						} else {
								return true;
						}
				}
				
				if(accept_null == false) {
						if(tmp == 0) {
								self.errors = true;
								self.outputFieldError(css_selector_error_container, error_message);
								return false;
						}
				}
				
				if(required_value != false) {
						if(value != required_value) {
								self.errors = true;
								self.outputFieldError(css_selector_error_container, error_message);
								return false;
						}
				}
						
				return true;
		}
		
		//////////////////////////////////////////////////////////////////
		// Prüfe Datums Feld..
		//////////////////////////////////////////////////////////////////
		checkFieldDate(self, css_selector, accept_null, css_selector_error_container, error_message) {
				var value = $(css_selector).val();
				
				if(value == "") {
						if(accept_null) {
								return true;
						}
				}
				
				var d = new mvDate();
				var result = d.loadFromInputField(css_selector);
				
				if(false == result) {
						self.errors = true;
						self.outputFieldError(css_selector_error_container, error_message);
						return false;
				}
				
				return true;
		}
		
		//////////////////////////////////////////////////////////////////
		// Prüfe Zeit Feld..
		//////////////////////////////////////////////////////////////////
		checkFieldTime(self, css_selector, accept_null, css_selector_error_container, error_message) {	
				var value = $(css_selector).val();
				
				if(value == "") {
						if(accept_null) {
								return true;
						}
				}
				
				var t = new mvTime();
				var result = t.loadFromInputField(css_selector);
				
				if(false == result) {
						self.errors = true;
						self.outputFieldError(css_selector_error_container, error_message);
						return false;
				}
				
				return true;
		}
		
		//////////////////////////////////////////////////////////////////
		// Prüfe Text-Feld
		//////////////////////////////////////////////////////////////////
		checkFieldText(self, css_selector, min_length, css_selector_error_container, error_message) {
				var value = $(css_selector).val();
				
				if(typeof value == 'undefined') {
						console.log('Value undefined for: ' + css_selector);
						self.errors = true;
						self.outputFieldError(css_selector_error_container, error_message);
						return false;
				}
				
				if(value.length < min_length) {
						self.errors = true;
						self.outputFieldError(css_selector_error_container, error_message);
						return false;
				}
				
				return true;
		}
		
		//////////////////////////////////////////////////////////////////
		// Prüfe Integer Feld..
		//////////////////////////////////////////////////////////////////
		outputFieldError(css_selector_error_container, error_message) {
				$(css_selector_error_container).html(error_message);
				$(css_selector_error_container).show();
		}
		
		//////////////////////////////////////////////////////////////////
		// Allgemeinen Fehler ausgeben.
		//////////////////////////////////////////////////////////////////
		showGeneralError(error_message) {
				$('#mv-editor-save-error').html(error_message);
				$('#mv-editor-save-error').show();
		}
		
		//////////////////////////////////////////////////////////////////
		// Alle Fehler ausblenden.
		//////////////////////////////////////////////////////////////////
		hideAllErrors() {
				$('.mv-error').hide();
		}
		
		//////////////////////////////////////////////////////////////////
		// Alle Fehler ausblenden.
		//////////////////////////////////////////////////////////////////
		setKalenderBookInfo(text_date, text_time, text_event_location, text_user_unit) {
				$('#mv-kalender-book-now-info').html(text_date + "<br />" + text_time + "<br />" + text_event_location + "<br />" + text_user_unit);
				$('#mv-kalender-book-now-info-2').html(text_date + "<br />" + text_time + "<br />" + text_event_location + "<br />" + text_user_unit);
		}
		
		setKalenderBookInfoOnSuccessPage(text_date, text_time, text_event_location, text_user_unit) {
				$('#mv-kalender-success-appointment-info').html(
						text_date + "<br />" + 
						text_time + "<br />" + 
						text_event_location + "<br />" + 
						text_user_unit
				);
				
				
		}
}




class mvFormData {
		//////////////////////////////////////////////////////////////////
		// Constructor: Form initialisieren.
		//////////////////////////////////////////////////////////////////
		constructor(data) {
				//Alle Eingabefelder prüfen.
				this.appointment_id = 0;
				this.firstname = "";
				this.lastname = "";
				this.email_address = "";
				this.email_reminder = 0;
				this.customers_number = "";
				this.phone = "";
				this.street = "";
				this.plz = "";
				this.city = "";
				this.comment_visitor_booking = "";
				this.accept_agb = "";
		}
		
		
}