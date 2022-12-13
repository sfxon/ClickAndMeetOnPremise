$(function() {
		reset_scripts_contents_id_refresh();
		
		////////////////////////////////////////////////////////////////////////////////
		// Action Handler für Klick auf den "Refresh"-Button.
		////////////////////////////////////////////////////////////////////////////////
		function reset_scripts_contents_id_refresh() {
				$('#scripts_contents_id_refresh').on('click', function(event) {
						event.stopPropagation();
						event.preventDefault();
						
						$('#scripts_contents_id_container').html('Loading..');
						
						mv_ajax_load_scripts_contents_select();
				});
		}
		
		////////////////////////////////////////////////////////////////////////////////
		// HTML Liste laden und anzeigen für "scripts_contents_id".
		////////////////////////////////////////////////////////////////////////////////
		function mv_ajax_load_scripts_contents_select() {
				var scripts_id = $('#scripts_id').val();
				
				var params = {
						s: 'cAdminScriptsProcesses', 
						action: 'ajax_load_list',
						scripts_id: scripts_id
				};
		
				params = $.param(params);
				
				$.ajax({
						type: "POST",
						url: "index.php?" + params
				}).done(function( msg ) {
						$('#scripts_contents_id_container').html(msg);
				});
		}
});