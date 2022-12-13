var mv_current_product_image_file_type = '';
var mv_current_product_image_file_id = 0;
var mv_tmp_products_id = 0;

////////////////////////////////////////////////////////////////////////////////////////////////
// Load the products box by ajax.
////////////////////////////////////////////////////////////////////////////////////////////////
function handler_products_image_added(data) {			
		var src = 'data/tmp/tmpuploads/' + data.filename;
		var item_id = mv_current_product_image_file_type + '-' + mv_current_product_image_file_id;
		var tmp_html = '<img src="' + src + '" style="max-width: 90%;" />';
		$('#' + item_id).closest('tr').children('.image-container').html(tmp_html);
}

////////////////////////////////////////////////////////////////////////////////////////////////
// Init the upload function (when the user selected a file, it is uploaded automatically).
////////////////////////////////////////////////////////////////////////////////////////////////
function websellersessions_products_init_image_upload_button(products_id, item) {
		//1st: Trigger the file upload click event
		$(item).on('click', function() {
				mv_current_product_image_file_type = $(this).closest('.fileinput').attr('data-attr-image-type');
				mv_current_product_image_file_id = $(this).closest('.fileinput').attr('data-attr-image-id');
				mv_tmp_products_id = products_id;
				
				var item_identifier = '#' + mv_current_product_image_file_type + '-' + mv_current_product_image_file_id;
				//disable event for this item, because it is re-equipped in the mv_file_uploader function..
				$(item_identifier).off('change');
				
				var params = {
						s: 'cAdminwebsellersessions',
						action: 'ajax_upload_products_image',
						tmp_websellersessions_id: $('#tmp_websellersessions_id').val(),
						user_id: $('#user_id').val(),
						image_id: mv_current_product_image_file_id,
						image_type: mv_current_product_image_file_type,
						products_id: products_id
				};
				
				params = $.param(params);
				
				$(this).mv_file_uploader({
						input_file_selector: '',
						input_upload_button: '',
						input_file: item_identifier,
						receiver_url: 'index.php?' + params,
						trigger_add_post_vars: '',
						handler_uploaded: handler_products_image_added,
						trigger_allow_upload_without_file: false,
						auto_upload: true
				});
		});
}

////////////////////////////////////////////////////////////////////////////////////////////////
// Init all upload buttons upload functions.
////////////////////////////////////////////////////////////////////////////////////////////////
function websellersessions_products_init_image_upload_buttons() {
		$('.product-image_file').off('click');
		
		$('.product-image-file').each(function() {
				var products_id = $(this).attr('data-attr-products-id');
				websellersessions_products_init_image_upload_button(products_id, this);
		});
}

////////////////////////////////////////////////////////////////////////////////////////////////
// Load the products box by ajax.
////////////////////////////////////////////////////////////////////////////////////////////////
function websellersessions_products_add_product(products_id) {
		var params = {
				s: 'cAdminwebsellersessions', 
				action: 'ajax_add_product', 
				id: $('#id').val(),
				user_id: $('#user_id').val(),
				products_id: products_id,
				tmp_websellersessions_id: $('#tmp_websellersessions_id').val()
		};
		
		params = $.param(params);
		
		$.ajax({
				type: "POST",
				url: "index.php?" + params
		}).done(function( msg ) {
				try {
							var result = $.parseJSON(msg);
							$('#products').html(result.message);
							
							websellersessions_products_init_image_upload_buttons();
							websellersessions_products_init_toggleable_bar();
							websellersessions_products_init_remove_button();
				}	catch (err) {
							// Do something about the exception here
							alert('Es ist ein Fehler aufgetreten. Bitte versuchen Sie es noch einmal.' + err);
				}
		});
}

////////////////////////////////////////////////////////////////////////////////////////////////
// If a user clicked on the "Add" button in the products list of the modal dialog.
////////////////////////////////////////////////////////////////////////////////////////////////
function websellersessions_products_dialog_list_button_clicked(item) {
		var id = $(item).attr('data-attr-id');
		
		//Ajax - Reload the products boxes
		websellersessions_products_add_product(id);
}


////////////////////////////////////////////////////////////////////////////////////////////////
// Load all the action button in the products list live..
////////////////////////////////////////////////////////////////////////////////////////////////
function websellersessions_products_dialog_live() {
		$('.websellersessions-product-add-to-list').off('click');
		$('.websellersessions-product-add-to-list').on('click', function() {
				websellersessions_products_dialog_list_button_clicked(this);
		});
}
	
	

////////////////////////////////////////////////////////////////////////////////////////////////
// Load the products list.
////////////////////////////////////////////////////////////////////////////////////////////////
function websellersessions_products_dialog_load_products_list() {
		var params = {
				s: 'cAdminwebsellersessions', 
				action: 'ajax_load_products_list', 
				id: $('#id').val(),
				user_id: $('#user_id').val(),
				tmp_websellersessions_id: $('#tmp_websellersessions_id').val()
		};

		
		params = $.param(params);
		
		$.ajax({
				type: "POST",
				url: "index.php?" + params
		}).done(function( msg ) {
				try {
							var result = $.parseJSON(msg);
							$('#websellersessions_products_dialog_products_list').html(result.message);
							websellersessions_products_dialog_live();		//Let all the add buttons live..
				}	catch (err) {
							// Do something about the exception here
							alert('Es ist ein Fehler aufgetreten. Bitte versuchen Sie es noch einmal.');
				}
		});
}

////////////////////////////////////////////////////////////////////////////////////////////////
// Inits the toggleable-ability of the products boxes.
////////////////////////////////////////////////////////////////////////////////////////////////
function websellersessions_products_init_toggleable_bar() {
		$('.mv-toggle-products-box .mvbox-title').off('click');		//Remove event, to avoid double occurence of this event.
		$('.mv-toggle-products-box .mvbox-title').on('click', function() {
				
				var item = $(this).closest('.mv-toggle-products-box').each(function() {
						$(this).find('.mv-toggleable-products-box').each(function() {
								$(this).slideToggle();
						});
				});
				/*console.log($(item));/*.slideToggle();*/
				$(this).find('.mv-toggle-icon').toggleClass('fa-minus-square-o');
				$(this).find('.mv-toggle-icon').toggleClass('fa-plus-square-o');
		});
}

////////////////////////////////////////////////////////////////////////////////////////////////
// Remove one product from this webseller session.
////////////////////////////////////////////////////////////////////////////////////////////////
function websellersessions_products_remove_product(websellersessions_id, tmp_websellersessions_id, user_id, products_id) {
		var params = {
				s: 'cAdminwebsellersessions', 
				action: 'ajax_remove_product', 
				id: websellersessions_id,
				user_id: user_id,
				products_id: products_id,
				tmp_websellersessions_id: tmp_websellersessions_id
		};
		
		params = $.param(params);
		
		$.ajax({
				type: "POST",
				url: "index.php?" + params
		}).done(function( msg ) {
				try {
							var result = $.parseJSON(msg);
							
							//remove from display
							$('#mv-product-box-' + products_id).remove();
							
							
							
				}	catch (err) {
							// Do something about the exception here
							alert('Es ist ein Fehler aufgetreten. Bitte versuchen Sie es noch einmal.' + err);
				}
		});
}

////////////////////////////////////////////////////////////////////////////////////////////////
// Init the remove button.
////////////////////////////////////////////////////////////////////////////////////////////////
function websellersessions_products_init_remove_button() {
		$('.mv-remove-product-from-list').off('click');		//First we remove it, to avoid double event occurence..
		$('.mv-remove-product-from-list').on('click', function(event) {
				event.stopPropagation();
				
				if(confirm('Soll dieser Eintrag wirklich entfernt werden?')) {
						var products_id = $(this).attr('data-attr-products-id');
						var websellersessions_id = $('#id').val();
						var tmp_websellersessions_id = $('#tmp_websellersessions_id').val();
						var user_id = $('#user_id').val();
						
						websellersessions_products_remove_product(websellersessions_id, tmp_websellersessions_id, user_id, products_id);
				}
		});
}

////////////////////////////////////////////////////////////////////////////////////////////////
// Execute after page load.
////////////////////////////////////////////////////////////////////////////////////////////////
$(function() {
		$('#add_product').on('click', function() {
				var products_list_html = websellersessions_products_dialog_load_products_list();
				$('#websellersessions_products_dialog').modal();
		});
		
		$('#websellersessions_products_dialog_searchterm').on('change', function() {
				var products_list_html = websellersessions_products_dialog_load_products_list();
				$('#websellersessions_products_dialog').modal();
		});
		
		$('#websellersessions_products_dialog_searchterm').on('keyup', function() {
				var products_list_html = websellersessions_products_dialog_load_products_list();
				$('#websellersessions_products_dialog').modal();
		});
		
		websellersessions_products_init_image_upload_buttons();
		websellersessions_products_init_toggleable_bar();
		websellersessions_products_init_remove_button();
});