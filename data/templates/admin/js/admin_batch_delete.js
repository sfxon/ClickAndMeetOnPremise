$(function() {
		mv_init_event_location_select_button();
		mv_init_time_input_for_monday();
		mv_reset_abweichende_zeiten_action_buttons_actions();
		mv_init_weekdays_by_checkbox_status();
		mv_init_weekday_checkbox_click_action_handler();
		mv_calculate_automatic_times_display();		//Da wir Termine von Vortagen übernehmen, wenn keine abweichenden Zeiten eingetragen sind, zeigen wir das deutlich an.
		mv_init_delete_appointment_button();
});

//////////////////////////////////////////////////////////////////////////////////////////
// Betriebsstätten Toggle Button Action aktivieren.
//////////////////////////////////////////////////////////////////////////////////////////
function mv_init_event_location_select_button() {
		$('#event_location').off('change');
		$('#event_location').on('change', function() {
				//Dropdown-Liste für UserUnit (Team) aktualisieren.
				updateUserUnitList($(this).val());
		});
}

//////////////////////////////////////////////////////////////////
// User-Unit Liste aktualisieren
//////////////////////////////////////////////////////////////////
function updateUserUnitList(event_location) {
		$('#user-unit').val(0);						//Reset chosen values..
		
		$("#user-unit option").removeAttr('disabled');
		$("#user-unit option").show();
			
		if(event_location == 0) {
				//Show all..
		} else {
				$("#user-unit").find('option').each(function() {
						var item = this;
						
						var event_location_id = $(item).attr('data-attr-event-location-id');
						var value = $(item).val();
						
						//Nicht die "Alle" box ausblenden!
						if(value == 0) {
								return;
						}
						
						//Ausblenden, wenn diese Option nicht zur aktuell gewählten Event-Location passt.
						if(event_location_id != event_location) {
								$(item).attr("disabled", "disabled");
								$(item).hide();
						}
				});
		}
}

//////////////////////////////////////////////////////////////////////////////////////////
// Für den Montag wird ein erstes Zeitinterval initialisiert als Default..
//////////////////////////////////////////////////////////////////////////////////////////
function mv_init_time_input_for_monday() {
		mv_weektime_add_range(1);
}

//////////////////////////////////////////////////////////////////////////////////////////
// Zeitinterval hinzufügen
//////////////////////////////////////////////////////////////////////////////////////////
function mv_weektime_add_range(weekday) {
		var id = '#weekday-row-' + weekday;
		
		var template = $('#mv-weekday-times-template').html();
		$(id + ' .mv-weekday-times').append(template);
		
		mv_weektime_reset_action_handlers();
		mv_calculate_automatic_times_display();
		mv_init_time_input_change_action_handlers();
}

//////////////////////////////////////////////////////////////////////////////////////////
// Action Handler neu intialisieren.
//////////////////////////////////////////////////////////////////////////////////////////
function mv_weektime_reset_action_handlers() {
		mv_weektime_reset_add_btns();
		mv_weektime_reset_del_btns();
}

//////////////////////////////////////////////////////////////////////////////////////////
// Action Handler für Weektime Add Action neu initialisieren.
//////////////////////////////////////////////////////////////////////////////////////////
function mv_weektime_reset_add_btns() {
		$('.weekday-time-add-btn').off('click');
		$('.weekday-time-add-btn').on('click', function() {
				mv_weektime_add_button_clicked(this);
		});
}

//////////////////////////////////////////////////////////////////////////////////////////
// Action Handler für Weektime Del Action neu initialisieren.
//////////////////////////////////////////////////////////////////////////////////////////
function mv_weektime_reset_del_btns() {
		$('.weekday-time-del-btn').off('click');
		$('.weekday-time-del-btn').on('click', function() {
				mv_weektime_del_button_clicked(this);
		});
}

//////////////////////////////////////////////////////////////////////////////////////////
// Action Handler für Weektime Add Action.
//////////////////////////////////////////////////////////////////////////////////////////
function mv_weektime_add_button_clicked(item) {
		//Id heraussuchen.
		var parent = $(item).closest('.mv-weekday-row');
		var id = $(parent).attr('id');
		var id = id.replace('weekday-row-', '');
		
		//Eintrag hinzufügen.
		mv_weektime_add_range(id);
		mv_calculate_automatic_times_display();
}

//////////////////////////////////////////////////////////////////////////////////////////
// Action Handler für Weektime Del Action.
//////////////////////////////////////////////////////////////////////////////////////////
function mv_weektime_del_button_clicked(item) {
		//Id heraussuchen.
		var parent = $(item).closest('.mv-weekday-row');
		var id = $(parent).attr('id');
		var id = id.replace('weekday-row-', '');
		
		//Eltern-Container für Liste auslesen.
		var rows_parent = $(item).closest('.mv-weekday-times');
		
		//Id heraussuchen.
		var parent = $(item).closest('.mv-weekday-times-row');
		$(parent).remove();
		
		//Zeige "Abweichende Zeiten Button" an, wenn keine weiteren Zeiten mehr vorhanden sind..
		var items = $(rows_parent).find('.mv-weekday-times-row');
		
		if(items.length == 0) {
				mv_show_abweichende_zeiten_button_for_weekday(id);
		}
		
		mv_calculate_automatic_times_display();
}

//////////////////////////////////////////////////////////////////////////////////////////
// "Abweichende Zeiten" Button anzeigen.
//////////////////////////////////////////////////////////////////////////////////////////
function mv_show_abweichende_zeiten_button_for_weekday(weekday) {
		var id = '#weekday-row-' + weekday;
		$(id).find('.mv-abweichende-zeiten-container').show();
		
		mv_reset_abweichende_zeiten_action_buttons_actions();
}

//////////////////////////////////////////////////////////////////////////////////////////
// "Abweichende Zeiten" Button verstecken.
//////////////////////////////////////////////////////////////////////////////////////////
function mv_hide_abweichende_zeiten_button_for_weekday(weekday) {
		var id = '#weekday-row-' + weekday;
		$(id).find('.mv-abweichende-zeiten-container').hide();
}

//////////////////////////////////////////////////////////////////////////////////////////
// "Abweichende Zeiten" Button Action Handler neu initialisieren.
//////////////////////////////////////////////////////////////////////////////////////////
function mv_reset_abweichende_zeiten_action_buttons_actions() {
		$('.add-custom-time-button').off('click');
		$('.add-custom-time-button').on('click', function() {
				mv_add_abweichende_zeiten_button_clicked(this);
		});
}

//////////////////////////////////////////////////////////////////////////////////////////
// "Abweichende Zeiten" Button Action Handler.
//////////////////////////////////////////////////////////////////////////////////////////
function mv_add_abweichende_zeiten_button_clicked(item) {
		//Id heraussuchen.
		var parent = $(item).closest('.mv-weekday-row');
		var id = $(parent).attr('id');
		var id = id.replace('weekday-row-', '');
		
		mv_hide_abweichende_zeiten_button_for_weekday(id);
		
		//Eintrag hinzufügen.
		mv_weektime_add_range(id);
		mv_calculate_automatic_times_display();
}

//////////////////////////////////////////////////////////////////////////////////////////
// Alle Werktage anhand des Checkbox Status initialisieren.
//////////////////////////////////////////////////////////////////////////////////////////
function mv_init_weekdays_by_checkbox_status() {
		$('.weekday-checkbox').each(function() {
				var item = this;
				
				mv_update_weekday_row_by_checkbox_by_status(item);
		});
}

//////////////////////////////////////////////////////////////////////////////////////////
// Werktag anhand des Checkbox Status aktualisieren.
//////////////////////////////////////////////////////////////////////////////////////////
function mv_update_weekday_row_by_checkbox_by_status(item) {
		//Status abfragen
		var status = $(item).is(':checked');
		
		//Zeilen-ID abfragen
		//Id heraussuchen.
		var parent = $(item).closest('.mv-weekday-row');
		var id = $(parent).attr('id');
		//var id = id.replace('weekday-row-', '');
		
		if(true == status) {
				$('#' + id + ' .mv-weekday-times').show();
		} else {
				$('#' + id + ' .mv-weekday-times').hide();
		}
}

//////////////////////////////////////////////////////////////////////////////////////////
// Action-Handler für Wochentag Checkboxen aktivieren.
//////////////////////////////////////////////////////////////////////////////////////////
function mv_init_weekday_checkbox_click_action_handler() {
		$('.weekday-checkbox').off('click');
		$('.weekday-checkbox').on('click', function() {
				mv_update_weekday_row_by_checkbox_by_status(this);
				mv_calculate_automatic_times_display();
		});
}

//////////////////////////////////////////////////////////////////////////////////////////
// Automatische Zeiten berechnen und anzeigen.
//////////////////////////////////////////////////////////////////////////////////////////
function mv_calculate_automatic_times_display() {
		var previous = [];
		
		$('.mv-weekday-row').each(function() {
				var item = this;
				
				//Check if checkbox is active? No? not activated -> return Zero!
				//Status abfragen
				var status = $(item).find('.weekday-checkbox').is(':checked');
				
				if(true == status) {
						//has items?
						var rows = mv_check_weekday_appointments(item);
						
						if(rows > 0) {		//yes? -> fetch and remember in previous..
								previous = mv_get_time_preview_values_from_row(item);
						} else {					//no? -> show previous -> no previous? -> show error!
								mv_show_weekday_time_preview(item, previous);
						}
				}
		});
}

//////////////////////////////////////////////////////////////////////////////////////////
// Prüfen, ob Wochentag über eingetragene Zeiten verfügt.
//////////////////////////////////////////////////////////////////////////////////////////
function mv_check_weekday_appointments(item) {
		//Id heraussuchen.
		var parent = $(item).closest('.mv-weekday-row');
		var id = $(parent).attr('id');
		var id_numeric = id.replace('weekday-row-', '');
		
		//Eltern-Container für Liste auslesen.
		var rows_parent = $(item).find('.mv-weekday-times');
		
		//Zeige "Abweichende Zeiten Button" an, wenn keine weiteren Zeiten mehr vorhanden sind..
		var items = $(rows_parent).find('.mv-weekday-times-row');
		return items.length;
}

//////////////////////////////////////////////////////////////////////////////////////////
// Zeit-Array unter Button "Abweichende Zeiten einfügen" anzeigen.
//////////////////////////////////////////////////////////////////////////////////////////
function mv_show_weekday_time_preview(item, times_array) {
		if(times_array.length == 0) {
				$(item).find('.mv-abweichende-zeiten-details').html('<span style="color: red;">Warnung! Für diesen Tag ist keine Zeit eingetragen.</span>');
		} else {
				var final_text = "";
				
				for(i = 0; i < times_array.length; i++) {
						if(final_text.length > 0) {
								final_text += "<br />";
						}
						
						final_text += "von " + times_array[i].from;
						final_text += " bis " + times_array[i].to;
				}
				
				$(item).find('.mv-abweichende-zeiten-details').html(final_text);
		}
}

//////////////////////////////////////////////////////////////////////////////////////////
// Zeitwerte als Array aus Wochentag-Zeilen auslesen.
//////////////////////////////////////////////////////////////////////////////////////////
function mv_get_time_preview_values_from_row(item) {
		var retval = [];
		
		$(item).find('.mv-weekday-times-row').each(function() {
				var row = this;
				var time_from = $(this).find('.time_from').val();
				var time_to = $(this).find('.time_to').val();
				
				retval.push(
						{
								from: time_from,
								to: time_to
						}
				);
		});
		
		return retval;
}

//////////////////////////////////////////////////////////////////////////////////////////
// Reinitialisisert die Action-Handler für die Zeit-Eingabefelder.
//////////////////////////////////////////////////////////////////////////////////////////
function mv_init_time_input_change_action_handlers() {
		$('.time_from').off('keyup');
		$('.time_from').on('keyup', function() {
				mv_calculate_automatic_times_display();
		});
		
		$('.time_to').off('keyup');
		$('.time_to').on('keyup', function() {
				mv_calculate_automatic_times_display();
		});
}

//////////////////////////////////////////////////////////////////////////////////////////
// Button zum Erstellen der Termine angeklickt.
//////////////////////////////////////////////////////////////////////////////////////////
function mv_init_delete_appointment_button() {
		$('#delete-appointments').off('click');
		$('#delete-appointments').on('click', function() {
				if(false == mv_batch_delete_check_values()) {
						return false;
				}
				
				mv_batch_delete_start();
		});
}

/*
//////////////////////////////////////////////////////////////////////////////////////////
// Button "von vorn" angeklickt.
//////////////////////////////////////////////////////////////////////////////////////////
function mv_init_restart_button() {
		$('#mv-process-start-again').off('click');
		$('#mv-process-start-again').on('click', function() {
				//Reset selection of "event location"
				mv_reset_event_location();
				
				$('#mv-success-container').hide();
				$('#mv-config-container').show();
		});
}


//////////////////////////////////////////////////////////////////////////////////////////
// Event-Location zurücksettzen.
//////////////////////////////////////////////////////////////////////////////////////////
function mv_reset_event_location() {
		$('#betriebsstaette').val(0);
		$('#betriebsstaette-toggle-button').attr('data-attr-current-status', 'select');
		$('#betriebsstaette-new-container').hide();
		$('#betriebsstaette-select-container').show();
}
*/




