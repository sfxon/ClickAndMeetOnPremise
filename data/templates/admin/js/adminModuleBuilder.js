$(function() {
		$('input[name=title]').on('keyup', function() {
				mv_admin_module_builder__newValueInInput( $(this).val() );
		});
		
		$('input[name=title]').on('change', function() {
				mv_admin_module_builder__newValueInInput( $(this).val() );
		});
});

/////////////////////////////////////////////////////////////////////////////
// Berechnen der Inhalte der zusätzlichen Eingabefelder,
// wenn im ersten etwas eingegeben wurde..
/////////////////////////////////////////////////////////////////////////////
function mv_admin_module_builder__newValueInInput( item_val) {
		item_val = item_val.replace( /[^a-zA-Z]/g, " "); // Replace any non letters with a space 
		item_val = item_val.replace( /\s+/, " "); 			// Replace one or more spaces, with just a +
		
		//trim left and right spaces
		item_val = item_val.trim();
		
		//make every substrings first char an uppercase char.
		var capitalized = item_val.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
		capitalized = capitalized.replace(/ /g,'');
		
		$('input[name=data_module]').val('c' + capitalized);
		$('input[name=admin_module]').val('cAdmin' + capitalized);
		
		//Make all chars lowercase. Replace all spaces with underscores.
		var lowercased = item_val.toLowerCase();
		lowercased = lowercased.replace(/ /g,'_');
		
		$('input[name=database_table]').val('aloha_' + lowercased);
}