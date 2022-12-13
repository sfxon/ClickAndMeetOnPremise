$(function() {
		/////////////////////////////////////////////////////////////////////////////////
		// If the "select all" checkbox was checked..
		/////////////////////////////////////////////////////////////////////////////////
		$('#mv-select-all').on('click', function() {
				if ($(this).prop('checked')) {
						mv_set_all_select_fields('checked');
				} else {
						mv_set_all_select_fields('unchecked');
				}
		});
	
		////////////////////////////////////////////////////////////////////////////////
		// function for checking or unchecking all the checkboxes
		////////////////////////////////////////////////////////////////////////////////
		function mv_set_all_select_fields(state) {
				if(state == 'checked') {
						$('.systemright-checkbox').prop('checked', true);
				} else {
						$('.systemright-checkbox').prop('checked', false);
				}
		}
});