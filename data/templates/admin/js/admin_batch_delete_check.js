function mv_batch_delete_check_values() {
		var error = false;
		var errors = [];
		var result = false;
		
		mv_reset_all_error_messages();
	
		//Check Zeitraum von
		var dateFrom = new mvDate();
		var result1 = dateFrom.loadFromInputField('#date_from');
		
		if(false == result1) {
				error = true;
				errors.push({zeitraum: 'from'});
		}
		
		//Check Zeitraum bis
		var dateTo = new mvDate();
		var result2 = dateTo.loadFromInputField('#date_to');
		
		if(false == result2) {
				error = true;
				errors.push({zeitraum: 'to'});
		}
		
		//Check Zeitraum range (von bis)
		//order = mv_check_zeitraum_range();
		if(result1 && result2) {
				result = dateFrom.compareTo(dateTo);
		
				if(result > 0) {
						error = true;
						errors.push({zeitraum: 'range'});
				}
		}
		
		//Check times
		if(false == mv_check_times()) {
				error = true;		//The checkTimes function already shows all the errornous input fields. So we do not this here again.
		}
		
		if(false == mvBatchDeleteCheckStatis()) {
				error = true;
				errors.push({status: 'none'});
		}
		
		//Fehler ausgeben.
		if(error) {
				mv_cc_output_errors(errors);
				return false;
		}		
		
		return true;
}

//////////////////////////////////////////////////////////////////////////////////////////
// Alle Fehler ausgeben.
//////////////////////////////////////////////////////////////////////////////////////////
function mv_cc_output_errors(errors) {
		for(i = 0; i < errors.length; i++) {
				mv_cc_output_error(errors[i]);
		}
		
		$('#mverror-general').show();
}

//////////////////////////////////////////////////////////////////////////////////////////
// Alle Fehler ausgeben.
// Einen Fehler ausgeben.
// Erwartet ein Objekt mit Bezeichner: Wert
//////////////////////////////////////////////////////////////////////////////////////////
function mv_cc_output_error(error) {
		for (let [key, value] of Object.entries(error)) {
				var error_container = '#error-' + key;
				var text = mv_cc_errors_get_text_for_code(key, value);
				
				var current_text = $(error_container).html();
				
				if(current_text.length > 0) {
						$(error_container).append("<br />");
				}
				
				$(error_container).append(text);
				$(error_container).show();
		}
}

//////////////////////////////////////////////////////////////////////////////////////////
// Button zum Erstellen der Termine angeklickt.
//////////////////////////////////////////////////////////////////////////////////////////
function mv_reset_all_error_messages() {
		$('.mverror').each(function() {
				var me = this;
				
				$(me).hide();
				$(me).html("");
		});
		
		$('#mverror-general').hide();
		
		$('.mv-input-error').removeClass('mv-input-error');		//Camp Kill Yourself..
}

//////////////////////////////////////////////////////////////////////////////////////////
// Fehlertext anhand von domäne und wert heraussuchen.
//////////////////////////////////////////////////////////////////////////////////////////
function mv_cc_errors_get_text_for_code(key, value) {
		var errors = {
				betriebsstaette: {
						selection: "Bitte wählen Sie eine Betriebsstätte oder erstellen Sie hier eine neue Betriebsstätte.",
						new: "Bitte geben Sie einen Titel für die Betriebsstätte an",
						unknown: "Unbekannter Modus bei Auswahl der Betriebsstätte (Neu oder bestehend)"
				},
				zeitraum: {
						from: "Das Startdatum ist ungültig.",
						to: "Das Enddatum ist ungültig.",
						range: "Fehler im Zeitraum: Das eingegebene Startdatum liegt nach dem Enddatum"
				},
				dauer_in_minuten: {
						invalid: "Der Termin muss mindestens eine Minute dauern."
				},
				appointment_count: {
						invalid: "Es muss mindestens 1 Termin zu jedem Zeitpunkt stattfinden."
				},
				status: {
						none: "Sie müssen mindestens einen Status auswählen."
				}
		}
		
		if(typeof errors[key] != 'undefined' && typeof errors[key][value] != 'undefined') {
				return errors[key][value];
		}
		
		alert('Es ist ein unerwarteter Fehler aufgetreten. Key: ' + key + '; Wert: ' + value);
		return 'Es ist ein unerwarteter Fehler aufgetreten. Key: ' + key + '; Wert: ' + value;
}

//////////////////////////////////////////////////////////////////////////////////////////
// Zeitraum - Range testen
//////////////////////////////////////////////////////////////////////////////////////////
function mv_check_times() {
		var error = false;
		
		//Durch alle aktivierten Zeilen laufen..
		$('.weekday-checkbox').each(function() {
				var item = this;
				var status = $(item).is(':checked');
				
				//Zeilen-ID abfragen
				//Id heraussuchen.
				var parent = $(item).closest('.mv-weekday-row');
				var id = $(parent).attr('id');
				
				if(true == status) {
						if(false == mv_check_times_for_weekday(id)) {
								error = true;
						}
				}
		});
		
		if(error == true) {
				return false;
		}
		
		return true;
}

//////////////////////////////////////////////////////////////////////////////////////////
// Zeitraum - Range für einen Wochentag testen
//////////////////////////////////////////////////////////////////////////////////////////
function mv_check_times_for_weekday(weekday_id) {
		var error = false;
		var selector = '#' + weekday_id + ' .mv-weekday-times-row';
		
		$(selector).each(function() {
				if(false == mv_check_times_for_weekday_row(this)) {
						error = true;
				}
		});
		
		if(true == error) {
				return false;
		}
		
		return true;
}

//////////////////////////////////////////////////////////////////////////////////////////
// Zeitraum - Range für einen Eintrag testen.
//////////////////////////////////////////////////////////////////////////////////////////
function mv_check_times_for_weekday_row(item) {
		var timeFrom = new mvTime();
		var result1 = timeFrom.loadFromInputField($(item).find('.time_from'));
		
		var timeTo = new mvTime();
		var result2 = timeTo.loadFromInputField($(item).find('.time_to'));
		
		if(false == result1 || false == result2) {
				$(item).closest('.mv-weekday-times-row').find('.mv-time-error-general').show();
				$(item).closest('.mv-weekday-times-row').find('.mv-time-error-general').html('Bitte prüfe deine Eingaben!');
				return false;
		}
		
		if(timeFrom.compareTo(timeTo) > 0) {
				$(item).closest('.mv-weekday-times-row').find('.mv-time-error-range').show();
				$(item).closest('.mv-weekday-times-row').find('.mv-time-error-range').html('Der Startzeitpunkt liegt nach dem Endzeitpunkt.');
				return false;
		}
		
		return true;
}

//////////////////////////////////////////////////////////////////////////////////////////
// Alle Stati checken
//////////////////////////////////////////////////////////////////////////////////////////
function mvBatchDeleteCheckStatis() {
		statis = [];
		
		$('.mv-appointment-status-checkbox').each(function() {
				var item = this;
				
				if($(item).is(':checked')) {
						statis.push($(item).val());
				}
		});
		
		if(this.statis.length == 0) {
				return false;
		}
		
		return true;
}

