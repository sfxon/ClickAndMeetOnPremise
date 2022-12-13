//////////////////////////////////////////////////////////////////////////////////////
// Class to handle times and time operations.
//////////////////////////////////////////////////////////////////////////////////////
class mvTime {
		//////////////////////////////////////////////////////////////////////////////////
		// constructor builds variables
		//////////////////////////////////////////////////////////////////////////////////
		constructor() {
				this.hour = 0;
				this.minute = 0;
		}
	
		//////////////////////////////////////////////////////////////////////////////////
		// Versucht Zeitwerte aus Eingabefeldern über einen jQuery üblichen Selector
		// herauszusuchen. Überprüft das Datum.
		// Funktioniert aktuell nur mit deutschem Datumsformat: (HH:ii)!
		//////////////////////////////////////////////////////////////////////////////////
		loadFromInputField(selector) {
				var time = $(selector).val();
				
				if(typeof time == 'undefined') {
						console.log("Parsen der Zeit fehlgeschlagen in mvTime.loadFromInputField. Übergebener CSS Selector Sting: " + selector.toString());
						return false;
				}
				
				//Extract date values
				if(time.length < 4) {
						this.showErrorForInputField(selector);
						return false;
				}
				
				//Anhand des Doppelpunktes splitten..
				var time_parts = time.split(':');
				
				if(time_parts.length != 2) {
						this.showErrorForInputField(selector);
						return false;
				}
				
				//turn to integer..
				var ihour = parseInt(time_parts[0], 10);				//Removes leading zero
				var iminute = parseInt(time_parts[1], 10);		//Removes leading zero
				
				if(isNaN(ihour) || isNaN(iminute)) {
						this.showErrorForInputField(selector);
						return false;
				}
				
				//Check time ranges..
				if(ihour < 0 || ihour > 23) {
						this.showErrorForInputField(selector);
						return false;
				}
				
				if(iminute < 0 || iminute > 59) {
						this.showErrorForInputField(selector);
						return false;
				}
				
				this.hour = ihour;
				this.minute = iminute;
				
				return true;
		}
		
		showErrorForInputField(selector) {
				$(selector).addClass("mv-input-error");
		}
		
		//////////////////////////////////////////////////////////////////////////////////
		// Dieses Datum mit einem weiteren von dieser Klasse vergleichen.
		//////////////////////////////////////////////////////////////////////////////////
		compareTo(to) {
				if(this.hour < to.hour) {
						return -1;
				} else if(this.hour == to.hour) {
						if(this.minute < to.minute) {
								return -1;
						} else if(this.minute == to.minute) {
								return 0;
						} else {
								return 1;
						}
				} else {
						return 1;
				}
				
				return false;
		}
}