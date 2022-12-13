//////////////////////////////////////////////////////////////////////////////////////
// Class to handle dates and date operations.
//////////////////////////////////////////////////////////////////////////////////////
class mvDate {
		//////////////////////////////////////////////////////////////////////////////////
		// constructor builds variables
		//////////////////////////////////////////////////////////////////////////////////
		constructor() {
				this.day = 0;
				this.month = 0;
				this.year = 0;
		}
	
		//////////////////////////////////////////////////////////////////////////////////
		// Versucht Datumswerte aus Eingabefeldern über einen CSS üblichen Selector
		// herauszusuchen. Überprüft das Datum.
		// Funktioniert aktuell nur mit deutschem Datumsformat: (dd.mm.YYYY)!
		//////////////////////////////////////////////////////////////////////////////////
		loadFromInputField(selector) {
				var date = $(selector).val();
				
				if(typeof date == 'undefined') {
						console.log("Parsen der Datumseingabe fehlgeschlagen in mvDate.loadFromInputField. Übergebener CSS Selector Sting: " + selector.toString());
						return false;
				}
				
				//Extract date values
				if(date.length < 10) {
						return false;
				}
				
				var day = date.substr(0, 2);
				var month = date.substr(3, 2);
				var year = date.substr(6, 4);
				
				//turn to integer..
				var iday = parseInt(day, 10);				//Removes leading zero
				var imonth = parseInt(month, 10);		//Removes leading zero
				var iyear = parseInt(year, 10);			//Just check if this is a number
				
				if(isNaN(iday) || isNaN(imonth) || isNaN(iyear)) {
						return false;
				}
				
				imonth = imonth - 1;
				
				//Check date for validity.		
				var d = Date.parse(year + "/" + month + "/" + day);
				
				if(isNaN(d)) {
						return false;
				}
				
				//Fix rolling date problem in javascript.. (Wenn ein Datumswert eingegeben wird wie 31.11 - obwohl es nur bis 30.11 geht - rollt Date einfach die Differenz an Tagen weiter. Das wollen wir aber nicht!!
				var d = new Date(iyear, imonth, iday);
				
				if(d.getMonth() != imonth) {
						return false;
				}
				
				this.day = iday;
				this.month = imonth;
				this.year = iyear;
				
				return true;
		}
		
		//////////////////////////////////////////////////////////////////////////////////
		// Eingestellte Werte als date Objekt zurückgeben.
		// Achtung! nicht failsafe!
		//////////////////////////////////////////////////////////////////////////////////
		getAsDate() {
				return new Date(this.year, this.month, this.day);
		}
		
		//////////////////////////////////////////////////////////////////////////////////
		// Dieses Datum mit einem weiteren von dieser Klasse vergleichen.
		//////////////////////////////////////////////////////////////////////////////////
		compareTo(to) {
				var d_from = this.getAsDate();
				var d_to = to.getAsDate();
				
				if(d_from > d_to) {
						return 1;
				}
				
				if(d_from < d_to) {
						return -1;
				}
				
				return 0;
		}
}