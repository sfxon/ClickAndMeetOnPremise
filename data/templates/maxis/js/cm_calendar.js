var cal = null;
var mv_cm_timer = null;
var mv_mark_current_day_as_active = false;

$(function() {
		confirmBackspaceNavigations();
		
		var loader = new mvCmCalLoadInitial({
				url_selector: '#mv-base-url',
				url_controller: 'cFrontendCm',
				url_action: 'ajaxLoadCalendar',
				kalendar_container: '#mv-kalender-container',
				timer_container: '#mv-timer-container',
				event_location_container: '#mv-event-location-container',
				user_unit_container: '#mv-user-unit-container'
		});
		loader.loadCalender();
		/* calendar is now loaded in loader.. */
});

//////////////////////////////////////////////////////////////////////
// Verhindern, dass man die Seite mit Backspace verlässt.
// -> verhindert nervige Fehl-Klicks!
//////////////////////////////////////////////////////////////////////
function confirmBackspaceNavigations () {
    // http://stackoverflow.com/a/22949859/2407309
    var backspaceIsPressed = false
    $(document).keydown(function(event){
        if (event.which == 8) {
            backspaceIsPressed = true
        }
    })
    $(document).keyup(function(event){
        if (event.which == 8) {
            backspaceIsPressed = false
        }
    })
    $(window).on('beforeunload', function(){
        if (backspaceIsPressed) {
            backspaceIsPressed = false
            return "Sind Sie sicher, dass Sie diese Seite verlassen wollen?"
        }
    })
} // confirmBackspaceNavigations

//////////////////////////////////////////////////////////////////////
// Wenn ein Kalendertag angeklickt wurde.
//////////////////////////////////////////////////////////////////////
function mv_calendar_day_clicked(data) {
		var base_url = $('#url').val();
		
		//Eingabefelder verstecken..
		var form = new mvForm();
		form.hideForm();
		
		//Set hidden input fields - may need them later, when we have to reload.. for example when changing event_location box.
		$('#mv-times-list-current-date-day').val(data.day);
		$('#mv-times-list-current-date-month').val(data.month);
		$('#mv-times-list-current-date-year').val(data.year);
		
		$('#mv-kalender-current-selected-day').val(data.day);
		$('#mv-kalender-current-selected-month').val(data.month);
		$('#mv-kalender-current-selected-year').val(data.year);

		if(null != mv_cm_timer) {
				mv_cm_timer.update(base_url, data.day, data.month, data.year);
		} else {
				mv_cm_timer = new mvCmTimer(base_url, data.url_controller, data.day, data.month, data.year);
		}
		
		console.log(data.user_unit_id);

		var timer_loader = new mvCmTimerLoad(base_url, data.url_controller, data.event_location, data.user_unit_id, data.day, data.month, data.year);
		timer_loader.startRequest();
}

//////////////////////////////////////////////////////////////////////
// Kalender-Klasse
//////////////////////////////////////////////////////////////////////
class theCalender {
		//////////////////////////////////////////////////////////////////
		// constructor.
		//////////////////////////////////////////////////////////////////
		constructor(data) {
				this.elementEventLocationSelector = null;
				this.elementUserUnitSelector = null;
				this.elementMonthInputSelector = null;
				this.elementYearInputSelector = null;
				this.elementPrevMonthSelector = null;
				this.elementNextMonthSelector = null;
				this.daySelector = null;
				this.daySelectorClickCallback = null;
				this.actionDelayInMilliseconds = 700;
				this.loadingTimeout = null;
				this.baseUrl = "";
				this.url_controller = "";
				
				if(typeof data != 'undefined') {
						if(typeof data.elementEventLocationSelector != 'undefined') {
								this.elementEventLocationSelector = data.elementEventLocationSelector;
						}
						
						if(typeof data.elementUserUnitSelector != 'undefined') {
								this.elementUserUnitSelector = data.elementUserUnitSelector;
						}
						
						if(typeof data.elementMonthInputSelector != 'undefined') {
								this.elementMonthInputSelector = data.elementMonthInputSelector;
						}
						
						if(typeof data.elementYearInputSelector != 'undefined') {
								this.elementYearInputSelector = data.elementYearInputSelector;
						}
						
						if(typeof data.elementPrevMonthSelector != 'undefined') {
								this.elementPrevMonthSelector = data.elementPrevMonthSelector;
						}
						
						if(typeof data.elementNextMonthSelector != 'undefined') {
								this.elementNextMonthSelector = data.elementNextMonthSelector;
						}
						
						if(typeof data.daySelector != 'undefined') {
								this.daySelector = data.daySelector;
						}
						
						if(typeof data.daySelectorClickCallback != 'undefined') {
								this.daySelectorClickCallback = data.daySelectorClickCallback;
						}
						
						if(typeof data.url_controller != 'undefined') {
								this.url_controller = data.url_controller;
						}
				}
				
				this.loadBaseUrl();
				this.initActionHandlers();
				this.updateCalendar();
				this.updateTimer();
		}
		
		//////////////////////////////////////////////////////////////////
		// Timer aktualisieren.
		//////////////////////////////////////////////////////////////////
		updateTimer() {
				//Timer aktualisieren
				var base_url = $('#url').val();
				var url_controller = $('#mv_url_controller').val();
				var event_location = $(this.elementEventLocationSelector).val();
				var user_unit_id = $(this.elementUserUnitSelector).val();
				var day = $('#mv-times-list-current-date-day').val();
				var month = $('#mv-times-list-current-date-month').val();
				var year = $('#mv-times-list-current-date-year').val();
				
				var data = {
						base_url: base_url,
						url_controller: url_controller,
						event_location: event_location,
						user_unit_id: user_unit_id,
						day: day,
						month: month,
						year: year
				};

				mv_calendar_day_clicked(data);
		}
		
		//////////////////////////////////////////////////////////////////
		// Load Base url
		//////////////////////////////////////////////////////////////////
		loadBaseUrl() {
				this.baseUrl = $('#url').val();
		}
		
		//////////////////////////////////////////////////////////////////
		// init action handlers.
		//////////////////////////////////////////////////////////////////
		initActionHandlers() {
				this.initEventLocationInputHandler();
				this.initUserUnitInputHandler();
				this.initMonthInputHandler();
				this.initYearInputHandler();
				this.initPrevMonthHandler();
				this.initNextMonthHandler();
		}
		
		//////////////////////////////////////////////////////////////////
		// Action Handler: Wenn sich der event-location input handler ändert.
		//////////////////////////////////////////////////////////////////
		initEventLocationInputHandler() {
				var self = this;
				
				if(null == this.elementEventLocationSelector) {
						console.log('Kann Input-Handler für Event-Location nicht initialisieren, weil elementEventLocationSelector nicht gesetzt ist.');
						return;
				}
				
				$(this.elementEventLocationSelector).off('change');
				$(this.elementEventLocationSelector).on('change', function() {
						//Kalender aktualisieren
						self.updateCalendar();
						
						//Dropdown-Liste für UserUnit (Team) aktualisieren.
						self.updateUserUnitList(self, $(this).val());
						
						//Timer aktualisieren
						var base_url = $('#url').val();
						var url_controller = $('#mv_url_controller').val();
						var event_location = $(this).val();
						var user_unit_id = 0;		//Switched to zero, so this must be a zero.
						var day = $('#mv-times-list-current-date-day').val();
						var month = $('#mv-times-list-current-date-month').val();
						var year = $('#mv-times-list-current-date-year').val();
						
						var data = {
								base_url: base_url,
								url_controller: url_controller,
								event_location: event_location,
								user_unit_id: user_unit_id,
								day: day,
								month: month,
								year: year
						};
						
										console.log('click');
				
		
						mv_calendar_day_clicked(data);
				});
		}
		
		//////////////////////////////////////////////////////////////////
		// Action Handler: Wenn sich der user-unit input handler ändert.
		//////////////////////////////////////////////////////////////////
		initUserUnitInputHandler() {
				var self = this;
				
				if(null == this.elementUserUnitSelector) {
						console.log('Kann Input-Handler für UserUnit nicht initialisieren, weil elementUserUnitSelector nicht gesetzt ist.');
						return;
				}
				
				$(this.elementUserUnitSelector).off('change');
				$(this.elementUserUnitSelector).on('change', function() {
						//Kalender aktualisieren
						self.updateCalendar();
						
						//Dropdown-Liste für UserUnit (Team) aktualisieren.
						//self.updateUserUnitList(self, $(this).val());
						
						//Timer aktualisieren
						var base_url = $('#url').val();
						var url_controller = $('#mv_url_controller').val();
						var event_location = 0;
						var user_unit_id = $(this).val();
						var day = $('#mv-times-list-current-date-day').val();
						var month = $('#mv-times-list-current-date-month').val();
						var year = $('#mv-times-list-current-date-year').val();
						
						var data = {
								base_url: base_url,
								url_controller: url_controller,
								event_location: event_location,
								user_unit_id: user_unit_id,
								day: day,
								month: month,
								year: year
						};
		
						mv_calendar_day_clicked(data);
				});
		}
		
		//////////////////////////////////////////////////////////////////
		// User-Unit Liste aktualisieren
		//////////////////////////////////////////////////////////////////
		updateUserUnitList(self, event_location) {
				$(self.elementUserUnitSelector).off('change');		//Temporär deaktivieren, damit der nicht gleich getriggert wird, wenn wir den Wert auf 0 setzen.
				$(self.elementUserUnitSelector).val(0);						//Reset chosen values..
				
				$(self.elementUserUnitSelector + " option").removeAttr('disabled');
				$(self.elementUserUnitSelector + " option").show();
						
				if(event_location == 0) {
						//Show all..
				} else {
						$(self.elementUserUnitSelector).find('option').each(function() {
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
				
				self.initUserUnitInputHandler();									//Und Action-Handler wieder aktivieren.
		}
		
		//////////////////////////////////////////////////////////////////
		// Action Handler: Wenn sich der Monat ändert.
		//////////////////////////////////////////////////////////////////
		initMonthInputHandler() {
				var self = this;
				
				if(null == this.elementMonthInputSelector) {
						console.log('Kann Input-Handler für Monat nicht initialisieren, weil elementMonthInputSelector nicht gesetzt ist.');
						return;
				}
				
				$(this.elementMonthInputSelector).off('keyup');
				$(this.elementMonthInputSelector).on('keyup', function() {
						self.updateCalendar();
				});
		}
		
		//////////////////////////////////////////////////////////////////
		// Action Handler: Wenn sich das Jahr ändert.
		//////////////////////////////////////////////////////////////////
		initYearInputHandler() {
				var self = this;
			
				if(null == this.elementYearInputSelector) {
						console.log('Kann Input-Handler für Jahr nicht initialisieren, weil elementYearInputSelector nicht gesetzt ist.');
						return;
				}
				
				$(this.elementYearInputSelector).off('keyup');
				$(this.elementYearInputSelector).on('keyup', function() {
						self.updateCalendar();
				});
		}
		
		//////////////////////////////////////////////////////////////////
		// Action Handler: Wenn jemand den Button für den
		// vorherigen Monat betätigt.
		//////////////////////////////////////////////////////////////////
		initPrevMonthHandler() {
				var self = this;
				
				if(null == this.elementPrevMonthSelector) {
						console.log('Kann Input-Handler für "vorheriger Monat" nicht initialisieren, weil elementPrevMonthSelector nicht gesetzt ist.');
						return;
				}
				
				$(this.elementPrevMonthSelector).off('click');
				$(this.elementPrevMonthSelector).on('click', function() {
						var month = $(self.elementMonthInputSelector).val();
						var year = $(self.elementYearInputSelector).val();
						
						month = parseInt(month);
						year = parseInt(year);
						
						if(isNaN(month)) {
								return;
						}
						
						if(isNaN(year)) {
								return;
						}
						
						if(month < 1 || month > 12) {
								return;
						}
						
						if(year < 2000 || year > 5000) {
								return;
						}
						
						var d = new Date(year, month-1, 1);
						d.setMonth(d.getMonth()-1);
						
						$(self.elementMonthInputSelector).val(d.getMonth()+1);
						$(self.elementYearInputSelector).val(d.getFullYear());
						
						self.updateCalendar();
				});
		}
		
		//////////////////////////////////////////////////////////////////
		// Action Handler: Wenn jemand den Button für den
		// nächsten Monat betätigt.
		//////////////////////////////////////////////////////////////////
		initNextMonthHandler() {
				var self = this;
				
				if(null == this.elementNextMonthSelector) {
						console.log('Kann Input-Handler für "nächster Monat" nicht initialisieren, weil elementNextMonthSelector nicht gesetzt ist.');
						return;
				}
				
				$(this.elementNextMonthSelector).off('click');
				$(this.elementNextMonthSelector).on('click', function() {
						var month = $(self.elementMonthInputSelector).val();
						var year = $(self.elementYearInputSelector).val();
						
						month = parseInt(month);
						year = parseInt(year);
						
						if(isNaN(month)) {
								return;
						}
						
						if(isNaN(year)) {
								return;
						}
						
						if(month < 1 || month > 12) {
								return;
						}
						
						if(year < 2000 || year > 5000) {
								return;
						}
						
						var d = new Date(year, month-1, 1);
						d.setMonth(d.getMonth()+1);
						
						$(self.elementMonthInputSelector).val(d.getMonth()+1);
						$(self.elementYearInputSelector).val(d.getFullYear());
						
						self.updateCalendar();
				});
		}
		
		//////////////////////////////////////////////////////////////////
		// Wenn ein Wochentag angklickt wird.
		//////////////////////////////////////////////////////////////////
		initDayClickHandler() {
				var self = this;
				
				if(null == this.daySelector) {
						console.log('Kann Input-Handler für "Kalendertag" nicht initialisieren, weil daySelector nicht gesetzt ist.');
						return;
				}
				
				$(this.daySelector).off('click');
				$(this.daySelector).on('click', function() {
						var event_location = $(self.elementEventLocationSelector).val();
						var url_controller = $('#mv_url_controller').val();
						var day = $(this).attr('data-attr-day');
						var month = $(this).attr('data-attr-month');
						var year = $(this).attr('data-attr-year');
						var weekday = $(this).attr('data-attr-weekday');
						
						var data = {
								event_location: event_location,
								url_controller: url_controller,
								event_location: $(self.elementEventLocationSelector).val(),
								user_unit_id: $(self.elementUserUnitSelector).val(),
								day: day,
								month: month,
								year: year,
								weekday: weekday
						};
						
						//Tag mit CSS-Klasse versehen, um ihn optisch zu markieren.
						$('.kalender-entry').removeClass('mv-current-selected-day');
						$(this).addClass('mv-current-selected-day');
						
						//Timer Liste auf "Loading" setzen!
						$('.times-list-content').html('Daten werden geladen');
						
						if(null == self.daySelectorClickCallback) {
								console.log('Es ist keine Callback Funktion für initDayClickHandler hinterlegt. Es wird bei Klick auf einen Tag dadurch keine Aktion ausgelöst!');
								return;
						}
						
						self.daySelectorClickCallback(data);
				});
		}
		
		//////////////////////////////////////////////////////////////////
		// Kalender aktualisieren.
		//////////////////////////////////////////////////////////////////
		updateCalendar() {
				var self = this;
				
				if(this.loadingTimeout != null) {
						window.clearTimeout(this.loadingTimeout);
				}
				
				var event_location = $(this.elementEventLocationSelector).val();
				var user_unit = $(this.elementUserUnitSelector).val();
				var month = $(this.elementMonthInputSelector).val();
				var year = $(this.elementYearInputSelector).val();
				
				event_location = parseInt(event_location);
				user_unit = parseInt(user_unit);
				month = parseInt(month);
				year = parseInt(year);
				
				if(isNaN(event_location)) {
						event_location = 0;
				}
				
				if(isNaN(user_unit)) {
						user_unit = 0;
				}
				
				if(isNaN(month)) {
						return;
				}
				
				if(isNaN(year)) {
						return;
				}
				
				if(month < 1 || month > 12) {
						return;
				}
				
				if(year < 2000 || year > 5000) {
						return;
				}
					
				this.loadingTimeout = window.setTimeout(function() { self.updateCalendarHandler(event_location, user_unit, month, year); }, self.actionDelayInMilliseconds);
		}
		
		//////////////////////////////////////////////////////////////////
		// Kalenderdaten abrufen.
		//////////////////////////////////////////////////////////////////
		showLoadingText() {
				$('.calendar-content').hide();
				$('.calendar-content-loading').show();
		}
		
		//////////////////////////////////////////////////////////////////
		// Kalenderdaten abrufen.
		//////////////////////////////////////////////////////////////////
		updateCalendarHandler(event_location, user_unit, month, year) {
				this.showLoadingText();
				
				var self = this;
				
				var loader = new mvCmCalendarLoad(this.baseUrl, this.url_controller, event_location, user_unit, month, year);
				loader.parent = self;
				
				var callbacks = [
						{
								//Upload company if not selected..
								callback: loader.loadCalendarData,
								result_callback: self.loadCalendarDataResult,
								type: 'ajax'
						}
				];
			
				var my_queue = new mvUploadQueue(loader, callbacks, this.error_callback);
				my_queue.process();
		}
		
		//////////////////////////////////////////////////////////////////
		// Anfrage-Ergebnis verarbeiten.
		//////////////////////////////////////////////////////////////////
		loadCalendarDataResult(queue, result) {
				//Try to parse the result
				try {
						var data = $.parseJSON(result);
						
						if(data.status == "success") {
								if(typeof data.data.html != "undefined") {
										$('.calendar-content').html(data.data.html);
										$('.calendar-content-loading').hide();
										$('.calendar-content').show();
								}
						}
						
						queue.data.parent.initDayClickHandler();
						
						//Mark current day (does it only, if it is contained in calender..)
						queue.data.parent.markCurrentDay();
						
						return true;
				} catch(e) {
						console.log("Ergebnis: ", result, "Exception: ", e);
				}
				
				return false;
		}
		
		//////////////////////////////////////////////////////////////////
		// Aktuellen Tag markieren.
		//////////////////////////////////////////////////////////////////
		markCurrentDay() {
				var day = $('#mv-kalender-current-selected-day').val();
				var month = $('#mv-kalender-current-selected-month').val();
				var year = $('#mv-kalender-current-selected-year').val();
				var id = '#mv-kalender-entry-' + year + '-' + month + '-' + day;
				
				$('.kalender-entry').removeClass('mv-current-selected-day');		//Bei den anderen Tagen ggf. entfernen.
				$(id).addClass('mv-current-selected-day');		//Aktuellen Tag festlegen.
		}
}

//////////////////////////////////////////////////////////////////////
// Loader-Helper für Kalenderdaten.
//////////////////////////////////////////////////////////////////////
class mvCmCalendarLoad {
		//////////////////////////////////////////////////////////////////
		// Constructor.
		//////////////////////////////////////////////////////////////////
		constructor(base_url, url_controller, event_location, user_unit, month, year) {
				this.event_location = event_location;
				this.user_unit = user_unit;
				this.month = month;
				this.year = year;
				this.baseUrl = base_url;
				this.url_controller = url_controller;
		}
		
		//////////////////////////////////////////////////////////////////
		// Kalenderdaten - Querydaten aufbereiten.
		//////////////////////////////////////////////////////////////////
		loadCalendarData(queue) {
				var get_params = {
						s: queue.data.url_controller,
						action: 'ajaxLoadMonth'
				};
				get_params = $.param(get_params);
				
				var request = {
						url: queue.data.url + '?' + get_params,
						post_data: {
								event_location: queue.data.event_location,
								user_unit: queue.data.user_unit,
								month: queue.data.month,
								year: queue.data.year
						},
						mode: 'POST'
				};
				
				return request;
		}
}




