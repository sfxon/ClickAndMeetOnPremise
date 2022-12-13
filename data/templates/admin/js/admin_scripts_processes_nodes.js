$(function() {
		reset_next_processes_id_refresh();
		
		////////////////////////////////////////////////////////////////////////////////
		// Action Handler für Klick auf den "Refresh"-Button.
		////////////////////////////////////////////////////////////////////////////////
		function reset_next_processes_id_refresh() {
				$('#next_processes_id_refresh').on('click', function(event) {
						event.stopPropagation();
						event.preventDefault();
						
						$('#next_processes_id_container').html('Loading..');
						
						mv_ajax_load_next_processes_select();
				});
		}
		
		////////////////////////////////////////////////////////////////////////////////
		// HTML Liste laden und anzeigen für "scripts_contents_id".
		////////////////////////////////////////////////////////////////////////////////
		function mv_ajax_load_next_processes_select() {
				var scripts_id = $('#scripts_id').val();
				var processes_id = $('#processes_id').val();		//Not next_processes_id, since we want the ones for this one..
				
				var params = {
						s: 'cAdminScriptsProcessesNodes', 
						action: 'ajax_load_list',
						scripts_id: scripts_id,
						processes_id: processes_id
				};
		
				params = $.param(params);
				
				$.ajax({
						type: "POST",
						url: "index.php?" + params
				}).done(function( msg ) {
						$('#next_processes_id_container').html(msg);
				});
		}
});