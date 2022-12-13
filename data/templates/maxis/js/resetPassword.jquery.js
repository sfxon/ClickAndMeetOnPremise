$(function() {
		mv_init_form_submit_handler();
});

//////////////////////////////////////////////////////////////////////////
// Submit Handler
//////////////////////////////////////////////////////////////////////////
function mv_init_form_submit_handler() {
		$('#mv-reset-password-form').off('submit');
		$('#mv-reset-password-form').on('submit', function(event) {
				mvRegisterResetErrorMessages();
				
				if(false == mv_account_settings_check_form_fields()) {
						event.preventDefault();
						event.stopPropagation();
						return;
				}
		});
}

//////////////////////////////////////////////////////////////////////////
// Eingabefelder prüfen.
//////////////////////////////////////////////////////////////////////////
function mv_account_settings_check_form_fields() {
		var error = false;
		var password = $('#password').val();
		var password_repeat = $('#password-repeat').val();		
		
		//Check password
		if(password.length < 8) {
				mvShowError('#password', '.mv-password-error', 'Das Passwort muss mindestens 8 Zeichen lang sein.<br /><br />');
				error = true;
		}
		
		if(password != password_repeat) {
				mvShowError('#password_repeat', '.mv-password-repeat-error', 'Die Passwörter stimmen nicht überein.<br /><br />');
				error = true;
		}
		
		if(error == true) {
				return false;
		}
		
		return true;
}

///////////////////////////////////////////////////////////////////
// Fehler anzeigen.
///////////////////////////////////////////////////////////////////
function mvShowError(item_selector, error_message_selector, error_text) {
		mvShowGeneralError();
		
		$(item_selector).addClass('is-invalid');
		$(error_message_selector).html(error_text);
		$(error_message_selector).show();
}

///////////////////////////////////////////////////////////////////
// Alle Fehlermeldungen anzeigen.
///////////////////////////////////////////////////////////////////
function mvShowGeneralError() {
		$('#mv-error-message').show();
}

///////////////////////////////////////////////////////////////////
// Alle Fehlermeldungen ausblenden.
///////////////////////////////////////////////////////////////////
function mvRegisterResetErrorMessages() {
		//Fehlertexte ausblenden.
		$('.mv-password-error').hide();
		$('.mv-password-repeat-error').hide();
		
		//Stile zurücksetzen.
		$('#mv-error-message').hide();
		$('#password').removeClass('is-invalid');
		$('#password-repeat').removeClass('is-invalid');
}