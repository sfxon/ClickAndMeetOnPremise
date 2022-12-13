$(function() {
		$('#title').on('keyup', function(event) {
				mv_admin_scripts_change_title__change_class(this, event);
		});
		
		$('#title').on('change', function(event) {
				mv_admin_scripts_change_title__change_class(this, event);
		});
	
	
		//////////////////////////////////////////////////////////////////////////////
		// Changes the class by the title (action handler)
		//////////////////////////////////////////////////////////////////////////////
		function mv_admin_scripts_change_title__change_class(item, event) {
				var title = $(item).val();
				
				var newString = title.replace(/[^A-Z0-9\-]/ig, "_");
				
				$('#class_name').val(newString);
		}
	
	
	
	
	
	
	
	
});