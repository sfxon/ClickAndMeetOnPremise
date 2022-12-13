// JavaScript Document
class mvUploadQueryBuilder {
		makeByPlatform(platform, controller, action) {
				switch(platform) {
						case 'wordpress':
								return this.makeByPlatformWordpress(controller, action);
						case 'blitz':
								return this.makeByPlatformBlitz(controller, action);
				}
				
				alert('Fehler: Ungültige Plattform übermittelt an mvUploadQueryBuilder. Details sind in der Konsole zu finden.');
				console.log('platform: ' + platform);
				console.log('controller: ' + controller);
				console.log('action: ' + action);
		}
		
		makeByPlatformWordpress(controller, action) {
				var get_params = {
						action: '' + controller + '_' + action		//Make this a string.. On Wordpress, there is no controller like system, so we emulate it by prefixing!
				};
				get_params = jQuery.param(get_params);
				return get_params;
		}
		
		makeByPlatformBlitz(controller, action) {
				var get_params = {
						s: 'cAdminCmCalendar',
						action: action		//Make this a string.. On Wordpress, there is no controller like system, so we emulate it by prefixing!
				};
				get_params = jQuery.param(get_params);
				return get_params;
		}
}