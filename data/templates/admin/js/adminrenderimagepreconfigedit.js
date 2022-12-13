$(function() {
		//////////////////////////////////////////////////////////////////////////////////////
		// (Re-)init the layer box actions.
		//////////////////////////////////////////////////////////////////////////////////////
		function mv_renderer_image_layer_init_actions() {
				// Toogle Action for the mvbox-body-collapsed boxes.
				$('.mvbox-body-collapsed .mvbox-title').off('click');
				$('.mvbox-body-collapsed .mvbox-title').on('click', function() {
						var parent = $(this).parent();
						$(parent).find('.mvbox-body').slideToggle();
						$(this).find('.mv-indicate-collapsed-field').toggleClass('fa-caret-right');
						$(this).find('.mv-indicate-collapsed-field').toggleClass('fa-caret-down');
				});
				
				// Set delete actions
				$('.mv-action-delete-layer').off('click');
				$('.mv-action-delete-layer').on('click', function(event) {
						event.preventDefault();
						event.stopPropagation();
						
						var layer_id = $(this).attr('data-attr-layer-id');
						mv_renderer_image_layer_action_delete(layer_id);
				});
				
				// Set edit actions
				$('.mv-action-edit-layer').off('click');
				$('.mv-action-edit-layer').on('click', function(event) {
						mv_renderer_image_layer_action_prepare_edit(event, this);
				});
				
				//Set "move up one position" actions
				$('.mv-action-move-up-layer').off('click');
				$('.mv-action-move-up-layer').on('click', function(event) {
						mv_renderer_image_layer_move_up_action(event, this);
				});
				
				//Set "move down one position" actions
				$('.mv-action-move-down-layer').off('click');
				$('.mv-action-move-down-layer').on('click', function(event) {
						mv_renderer_image_layer_move_down_action(event, this);
				});
				
				//Untereffekt einfügen
				$('.mv-action-add-child-layer').off('click');
				$('.mv-action-add-child-layer').on('click', function(event) {
						mv_renderer_image_layer_add_child_layer_action(event, this);
				});
		}
		
		//////////////////////////////////////////////////////////////////////////////////////
		// Load data for a layer from db and set it in the modal editor.
		//////////////////////////////////////////////////////////////////////////////////////
		function mv_renderer_image_layer_add_child_layer_action(mv_event, mv_item) {
				var layer_id = $(mv_item).attr('data-attr-layer-id');
				var id = $('#id').val();
				
				var get_params = { s: 'cAdminrenderimagepreconfigedit', action: 'ajax_add_child_layer', id: id, layer_id: layer_id };
				var get_params_url_string = $.param(get_params);
				
				$('#mv-renderer-image-layers').html('loading..');
				
				$.ajax({
						method: "POST",
						url: "index.php?" + get_params_url_string
				}).done(function(msg) {
						alert('show editor with and for child layer!');
						
						
						mv_renderer_image_layer_reload();
				});
		}
		
		//////////////////////////////////////////////////////////////////////////////////////
		// Load data for a layer from db and set it in the modal editor.
		//////////////////////////////////////////////////////////////////////////////////////
		function mv_renderer_image_layer_move_down_action(mv_event, mv_item) {
				var layer_id = $(mv_item).attr('data-attr-layer-id');
				var id = $('#id').val();
				
				var get_params = { s: 'cAdminrenderimagepreconfigedit', action: 'ajax_move_down_layer', id: id, layer_id: layer_id };
				var get_params_url_string = $.param(get_params);
				
				$('#mv-renderer-image-layers').html('loading..');
				
				$.ajax({
						method: "POST",
						url: "index.php?" + get_params_url_string
				}).done(function(msg) {
						mv_renderer_image_layer_reload();
				});
		}
		
		//////////////////////////////////////////////////////////////////////////////////////
		// Load data for a layer from db and set it in the modal editor.
		//////////////////////////////////////////////////////////////////////////////////////
		function mv_renderer_image_layer_move_up_action(mv_event, mv_item) {
				var layer_id = $(mv_item).attr('data-attr-layer-id');
				var id = $('#id').val();
				
				var get_params = { s: 'cAdminrenderimagepreconfigedit', action: 'ajax_move_up_layer', id: id, layer_id: layer_id };
				var get_params_url_string = $.param(get_params);
				
				$('#mv-renderer-image-layers').html('loading..');
				
				$.ajax({
						method: "POST",
						url: "index.php?" + get_params_url_string
				}).done(function(msg) {
						mv_renderer_image_layer_reload();
				});
		}
		
		//////////////////////////////////////////////////////////////////////////////////////
		// Load data for a layer from db and set it in the modal editor.
		//////////////////////////////////////////////////////////////////////////////////////
		function mv_load_layer_edit_modal_current_configuration_and_set_in_modal_editor() {
				var layer_id = $('#renderer_image_preconfig_layers_id').val();
				var id = $('#id').val();
				var dropdown_first_rendererfxconfig_id = $('#rendererfxconfig_dropdown').val();		//Get default value - for the case there is no value in the set, yet.
				
				var get_params = { s: 'cAdminrenderimagepreconfigedit', action: 'ajax_load_layer_data', id: id, layer_id: layer_id, dropdown_first_rendererfxconfig_id: dropdown_first_rendererfxconfig_id };
				var get_params_url_string = $.param(get_params);
				
				$.ajax({
						method: "POST",
						url: "index.php?" + get_params_url_string
				}).done(function(msg) {
						set_renderfxconfigfields_container_html(msg);
						mv_init_modal_save_action();
				});
		}
		
		//////////////////////////////////////////////////////////////////////////////////////
		// Prepare program for edit mode.
		//////////////////////////////////////////////////////////////////////////////////////
		function mv_renderer_image_layer_action_prepare_edit(mv_event, mv_item) {
				mv_event.preventDefault();
				mv_event.stopPropagation();
				
				var layer_id = $(mv_item).attr('data-attr-layer-id');
				
				//Load the render fx dropdown and attach the onchange event.
				set_renderfx_dropdown_container_html('Loading fx dropdown..');
				set_renderfxconfigfields_container_html('Loading config fields..');
				
				$('#layer_edit_modal').modal();
				
				mv_init_layer_edit_modal(layer_id);
				$('#renderer_image_preconfig_layers_id').val(layer_id);
				mv_load_renderfx_dropdown_html(mv_load_layer_edit_modal_current_configuration_and_set_in_modal_editor);
		}
		
		//////////////////////////////////////////////////////////////////////////////////////
		// Delete a layer.
		//////////////////////////////////////////////////////////////////////////////////////
		function mv_renderer_image_layer_action_delete(layer_id) {
				var id = $('#id').val();
				
				var get_params = { s: 'cAdminrenderimagepreconfigedit', action: 'ajax_delete_layer', id: id, layer_id: layer_id };
				var get_params_url_string = $.param(get_params);
				
				$.ajax({
						method: "POST",
						url: "index.php?" + get_params_url_string
				}).done(function(msg) {
						mv_renderer_image_layer_reload();
				});
		}
	
		//////////////////////////////////////////////////////////////////////////////////////
		// Reload the layer box.
		//////////////////////////////////////////////////////////////////////////////////////
		function mv_renderer_image_layer_reload() {
				var id = $('#id').val();
				
				var get_params = { s: 'cAdminrenderimagepreconfigedit', action: 'ajax_load_image_layers', id: id };
				var get_params_url_string = $.param(get_params);
				
				$.ajax({
						method: "POST",
						url: "index.php?" + get_params_url_string
				}).done(function(msg) {
						/*set_renderfxconfigfields_container_html(msg);*/
						$('#mv-renderer-image-layers').html(msg);
						mv_renderer_image_layer_init_actions();
				});
				
				/*alert('Layer Liste neu laden..');*/
		}
		
		//////////////////////////////////////////////////////////////////////////////////////
		// Set renderfx dropdown container html
		//////////////////////////////////////////////////////////////////////////////////////
		function mv_load_renderfxconfigfields_by_current_renderfx_selection() {
				var current_selection = $('#rendererfxconfig_dropdown').val();
				var get_params = { s: 'cAdminrenderimagepreconfigedit', action: 'ajax_load_renderfxconfigfields_html_by_fxconfig_id', rendererfxconfig_id: current_selection };
				var get_params_url_string = $.param(get_params);
				
				$.ajax({
						method: "POST",
						url: "index.php?" + get_params_url_string
				}).done(function(msg) {
						set_renderfxconfigfields_container_html(msg);
				});
		}
		
		//////////////////////////////////////////////////////////////////////////////////////
		// Set renderfxconfigfields container html
		//////////////////////////////////////////////////////////////////////////////////////
		function set_renderfxconfigfields_container_html(html) {
				$('#renderfxconfigfields_container').html(html);
		}
		
		//////////////////////////////////////////////////////////////////////////////////////
		// Set renderfx dropdown container html
		//////////////////////////////////////////////////////////////////////////////////////
		function set_renderfx_dropdown_container_html(html) {
				$('#renderfx_dropdown_container').html(html);
		}
		
		//////////////////////////////////////////////////////////////////////////////////////
		// Load renderfx_dropdown by an ajax request.
		//////////////////////////////////////////////////////////////////////////////////////
		function mv_load_renderfx_dropdown_html(finish_load_callback_function_name) {
				var get_params = { s: 'cAdminrenderimagepreconfigedit', action: 'ajax_load_renderfx_dropdown_html' };
				var get_params_url_string = $.param(get_params);
				
				$.ajax({
					  method: "POST",
					  url: "index.php?" + get_params_url_string
				}).done(function(msg) {
						set_renderfx_dropdown_container_html(msg);
						finish_load_callback_function_name();
			  });
		}
		
		//////////////////////////////////////////////////////////////////////////////////////
		// Reset event handlers for current selected dropdown.
		//////////////////////////////////////////////////////////////////////////////////////
		function deactivate_renderfx_dropdown_event_handlers() {
				$('#renderfx_dropdown_container').off('click');
				$('#renderfx_dropdown_container').off('change');
		}
		
		//////////////////////////////////////////////////////////////////////////////////////
		// Init the "save" action.
		//////////////////////////////////////////////////////////////////////////////////////
		function mv_init_modal_save_action() {
				$('#layer_edit_modal_save_button').off('click');
				$('#layer_edit_modal_save_button').on('click', function() {
						var file_size = 0;
						var errors = 0;
						var error_text = '';
						var myFormData = new FormData();		//form object erstellen
						var url = '';
						var url_params = '';
						
						//Get the id of the current selected layer type
						var rendererfxconfig_dropdown_value = $('#rendererfxconfig_dropdown').val();
						myFormData.append('rendererfxconfig_id', rendererfxconfig_dropdown_value);
						
						//load all input fields and values - if there are values missing - show information!
						$('.renderfxconfigfields_input').each(function() {
								if($(this).attr('type') == 'file') {
										file_size = 0;

										if (($(this))[0].files.length > 0) {
												myFormData.append($(this).attr('name'), $(this)[0].files[0]);
										} else {
												// no file chosen!
												errors = 1;
												var label = $("label[for='"+$(this).attr('id')+"']");
												error_text += '<br />Bitte wählen Sie eine Datei für das Eingabefeld mit dem Titel "' + $(label).html() + '" aus.';
										}
								} else {
										myFormData.append( $(this).attr('name'), $(this).val() );
								}
						});
						
						//Url zusammenstellen
						url_params = { s: 'cAdminrenderimagepreconfigedit', id: $('#id').val(), action: 'ajax_update_layer', layer_id: $('#renderer_image_preconfig_layers_id').val() }
						
						url = 'index.php?' + $.param(url_params);
						
						//Auf Fehler prüfen...
						if(errors == 1) {
								//Fehler ausgeben.
								$('#renderfxconfigfields_error_message_container').html('<span class="text-danger">' + error_text + '</span>');
						} else {
								//Wenn kein Fehler aufgetreten ist, Datei hochladen.
								$.ajax({
						        url: url,
						        type: 'POST',
						        data: myFormData,
						        cache: false,
						        /*dataType: 'json',*/
						        processData: false, // Don't process the files
       							contentType: false
								}).done( function(msg) {
										mv_renderer_image_layer_reload();
										/*alert('layer editor fenster schließen.');*/
										$('#layer_edit_modal').modal('hide');
								});
						}
				});
		}
		
		//////////////////////////////////////////////////////////////////////////////////////
		// Creates the layer in the system..
		//////////////////////////////////////////////////////////////////////////////////////
		function mv_layer_create_id_in_system(renderer_image_preconfig_id, parent_layer, sort_order) {
				var get_params = { s: 'cAdminrenderimagepreconfigedit', action: 'ajax_create_layer' };
				var get_params_url_string = $.param(get_params);
								
				$.ajax({
					  method: "POST",
					  url: "index.php?" + get_params_url_string,
						data: { renderer_image_preconfig_id: renderer_image_preconfig_id, parent_layer: parent_layer, sort_order: sort_order }
				}).done(function(id) {
						mv_init_layer_edit_modal(id);
						//load the dropdown - the parameter is a callback function: that Loads the input fields for the current selected renderfx
						mv_load_renderfx_dropdown_html(mv_load_renderfxconfigfields_by_current_renderfx_selection);
						mv_init_modal_save_action();
			  });
		}
		
		//////////////////////////////////////////////////////////////////////////////////////
		// Creates the layer in the system at the last position in the upmost level.
		//////////////////////////////////////////////////////////////////////////////////////
		function mv_get_layer_level_highest_sort_order(parent_layer_id) {
				var highest_sort_order = 0;
				
				$('.layer').each(function() {
						var tmp_parent_layer_id = $(this).attr('data-attr-parent-layer-id');
						
						if(tmp_parent_layer_id == parent_layer_id) {
								var tmp_sort_order = $(this).attr('data-attr-sort-order');
								
								tmp_sort_order = parseInt(tmp_sort_order);
								highest_sort_order = parseInt(highest_sort_order);
								
								if(tmp_sort_order > highest_sort_order) {
										highest_sort_order = tmp_sort_order;
								}
						}
				});
				
				return highest_sort_order;
		}
		
		//////////////////////////////////////////////////////////////////////////////////////
		// init dialog variables..
		//////////////////////////////////////////////////////////////////////////////////////
		function mv_init_layer_edit_modal(renderer_image_preconfig_layers_id) {
				$('#renderer_image_preconfig_layers_id').val(renderer_image_preconfig_layers_id);
				deactivate_renderfx_dropdown_event_handlers();
				$('#renderfxconfigfields_error_message_container').html('');
		}
		
		//////////////////////////////////////////////////////////////////////////////////////
		// Creates the layer in the system at the last position in the upmost level.
		//////////////////////////////////////////////////////////////////////////////////////
		function mv_append_zero_level_layer() {
				var renderer_image_preconfig_id = $('#id').val();
				var highest_sort_order = mv_get_layer_level_highest_sort_order(0);
				
				highest_sort_order += 1;
				
				mv_layer_create_id_in_system(renderer_image_preconfig_id, 0, highest_sort_order);
		}
		
		
		//////////////////////////////////////////////////////////////////////////////////////
		// When the "add layer" button has been clicked - show the layer dialog..
		//////////////////////////////////////////////////////////////////////////////////////
		$('#add-layer').on('click', function(event) {
				event.preventDefault();
				
				//Load the render fx dropdown and attach the onchange event.
				set_renderfx_dropdown_container_html('Loading fx dropdown..');
				set_renderfxconfigfields_container_html('Loading config fields..');
				
				mv_append_zero_level_layer();
		});	
		
		//////////////////////////////////////////////////////////////////////////////////////
		// Render the preview image.
		//////////////////////////////////////////////////////////////////////////////////////	
		function mv_renderer_render_image() {
				var id = $('#id').val();
				
				var get_params = { s: 'cAdminrenderimagepreconfigedit', action: 'ajax_render_image', id: id };
				var get_params_url_string = $.param(get_params);
								
				$.ajax({
					  method: "GET",
					  url: "index.php?" + get_params_url_string
				}).done(function(msg) {
						$('#mv-render-preview-container').html(msg);
			  });
		}
		
		//////////////////////////////////////////////////////////////////////////////////////
		// Init the image upload (for the preview image..).
		//////////////////////////////////////////////////////////////////////////////////////
		function mv_renderer_preview_image_upload_init() {
				$('#mv-renderer-upload-different-logo-button').on('click', function() {
						var myFormData = new FormData();
						var url = '';
						var url_params = '';
						
						var filedata = $('#mv-renderer-upload-different-logo-input')[0].files[0];
						myFormData.append('preview-image', filedata);
						
						//Url zusammenstellen
						url_params = { s: 'cAdminrenderimagepreconfigedit', id: $('#id').val(), action: 'ajax_upload_preview_logo' }
						url = 'index.php?' + $.param(url_params);

						$.ajax({
								url: url,
								type: 'POST',
								data: myFormData,
								cache: false,
								/*dataType: 'json',*/
								processData: false, // Don't process the files
								contentType: false
						}).done(function(msg) {
								if(msg != '') {
										$('#mv-renderer-logo-current-filename').html(msg);
										$('#mv-renderer-image-preview').html('<img src="data/images/webseller_layer_images/' + msg + '" />');
										$('#mv-renderer-logo-remove-container').html('<button type="button" id="mv-renderer-logo-remove">Zurücksetzen</button>');
										
										mv_renderer_preview_image_reset_init();
								}
						});
				});
		}
		
		//////////////////////////////////////////////////////////////////////////////////////
		// Reset preview image. (Removes it from database and resets the display.
		//////////////////////////////////////////////////////////////////////////////////////
		function mv_renderer_preview_image_reset_init() {
				$('#mv-renderer-logo-remove').off('click');
				$('#mv-renderer-logo-remove').on('click', function() {
						var myFormData = new FormData();
						var url = '';
						var url_params = '';
						
						//Url zusammenstellen
						url_params = { s: 'cAdminrenderimagepreconfigedit', id: $('#id').val(), action: 'ajax_preview_image_reset' }
						url = 'index.php?' + $.param(url_params);

						$.ajax({
								url: url,
								type: 'POST',
								data: myFormData,
								cache: false,
								/*dataType: 'json',*/
								processData: false, // Don't process the files
								contentType: false
						}).done(function(msg) {
								$('#mv-renderer-logo-current-filename').html('');
								$('#mv-renderer-image-preview').html('<img src="data/images/renderer_default_image.png" />');
						});
				});
		}
		
		//////////////////////////////////////////////////////////////////////////////////////
		// Do this, when the page is loaded (load additional data and prepare some stuff..
		//////////////////////////////////////////////////////////////////////////////////////
		mv_renderer_image_layer_reload();
		mv_renderer_render_image();
		mv_renderer_preview_image_upload_init();
		mv_renderer_preview_image_reset_init();
});