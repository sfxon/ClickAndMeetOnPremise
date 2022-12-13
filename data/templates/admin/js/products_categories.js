$(function() {
		products_categories_selectors_instances = [];
});

///////////////////////////////////////////////////////////////////////////////////////////////////
// function for removing a products category..
///////////////////////////////////////////////////////////////////////////////////////////////////
function remove_products_category(item) {
		var channel_id = $(item).attr('data-channel-id');
		var categories_id = $(item).attr('data-categories-id');
		
		//do an ajax call to get set this item and receive the data for this item..
		params = { s: 'cAdminproducts', action: 'ajax_remove_products_category_temp', channel_id: channel_id, categories_id: categories_id, tmp_products_id: $('#tmp_products_id').val() };
		params = $.param(params);
		
		$.ajax({
				type: "POST",
				url: "index.php?" + params,
		}).done(function( msg ) {
				//remove from table..
				$('#data_product_category_' + channel_id + '_' + categories_id).remove();
		});
}

///////////////////////////////////////////////////////////////////////////////////////////////////
// function for showing the editor
///////////////////////////////////////////////////////////////////////////////////////////////////
function show_products_category_editor(item) {
		$('#products_categories_add_editor').modal();
		
		var channel_id = $(item).attr('data-channel-id');
		var parent_id = $(item).attr('data-parent-id');
		
		$('#add_product_category_channel_id').val(channel_id);
		$('#add_product_category_parent_id').val(parent_id);
}

///////////////////////////////////////////////////////////////////////////////////////////////////
// select a products category for one channel
///////////////////////////////////////////////////////////////////////////////////////////////////
function select_products_category(item) {
		var channel_id = $(item).attr('data-channel-id');
		var categories_id = $(item).attr('data-product-categories-id');
		
		//check if this item already exists
		if( $('#data_product_category_' + channel_id + '_' + categories_id).length > 0) {
				return;
		}
		
		//do an ajax call to get set this item and receive the data for this item..
		params = { s: 'cAdminproducts', action: 'ajax_add_products_category_temp', channel_id: channel_id, categories_id: categories_id, tmp_products_id: $('#tmp_products_id').val() };
		params = $.param(params);
		
		$.ajax({
				type: "POST",
				url: "index.php?" + params,
		}).done(function( msg ) {
				//If received data is okay, add this item to the table..
				msg = $.parseJSON(msg);
				
				var text_delete_button = $('#text_delete_button').val();
				
				var app_string = '<tr id="data_product_category_' + channel_id + '_' + categories_id + '">';
						app_string += '<td>' + msg['categories_string'] + '</td>';
						app_string += '<td class="text-right">';
								app_string += '<button type="button" class="remove_category_from_channel" data-channel-id="' + channel_id + '" data-categories-id="' + categories_id + '">';
										app_string += text_delete_button
								app_string += '</button>';
						app_string += '</td>';
				app_string += '</tr>';
				
				$('#product_category_table_' + channel_id + ' tbody').append(app_string);
				
				//add the remove action for the button
				$('.remove_category_from_channel').off('click');
				$('.remove_category_from_channel').on('click', function() {
						remove_products_category(this);
				});
		});
}

///////////////////////////////////////////////////////////////////////////////////////////////////
// the main function..
///////////////////////////////////////////////////////////////////////////////////////////////////
(function( $ ) {
		$.fn.products_categories_selector = function( options ) {
				//check if there already exists an input with this options
				if(options.channel_id == undefined) {
						console.log('No channel_id was set. Could not start this instance of products_categories_selector');
						return;
				}
				
				for(var i = 0; i < products_categories_selectors_instances.length; i++) {
						if(	products_categories_selectors_instances[i] != undefined
								&& products_categories_selectors_instances[i]['channel_id'] != undefined 
								&& products_categories_selectors_instances[i]['channel_id'] == options.channel_id) {
								console.log('There is already an active instance for this channel_id');
								return;
						}
				}
				
				// This is the easiest way to have default options.
				var settings = $.extend({
						// These are the defaults.
						tmp_products_id: "0",		//this is a temporary product id
						selector: "",						//this is the selector, that created the input field
						input_container: "",		//this is the container, that holds the input field that is loaded via ajax calls
						channel_id: "",					//this is the channel id..
						debug: true							//set this to false, to disable the console output
				}, options );
				
				//output debug information
				if(settings['debug']) {
						console.log('connecting');
						console.log('tmp_products_id: ' + settings['tmp_products_id']);
						console.log('selector: ' + settings['selector']);
						console.log('input_container: ' + settings['input_container']);
						console.log('channel_id: ' + settings['channel_id']);
				};
				
				products_categories_selectors_instances.push(options);
				
				//load the content via ajax..
				var params = { s: 'cAdminproductcategories', action: 'ajax_load_channel_view', channel_id: settings['channel_id'] };
				params = $.param(params);
				
				$.ajax({
						type: "POST",
						url: "index.php?" + params,
				}).done(function( msg ) {
						$('#' + settings['input_container']).html(  msg  );
						
						//Treeview functionality
						$('.tree li').off('click');
						$('.tree>ul>li li').hide();
								
						$('.tree li').on('click', function (e) {
								//close the children
								var children = $(this).find('> ul > li');
								
								if (children.is(":visible")) {
										//change the symbol
										$(this).find(">span>i").removeClass('fa-caret-down');
										$(this).find(">span>i").addClass('fa-caret-right');
										
										//hide the child view..
										children.hide('fast');
										e.stopPropagation();
								} else {
										//change the symbol
										$(this).find(">span>i").removeClass('fa-caret-right');
										$(this).find(">span>i").addClass('fa-caret-down');
								
										//show the child view..
										children.show('fast');
										e.stopPropagation();
								}
						});
						
						//init the tooltips
						$('.mv_tooltip').tooltip();
						
						//init the create category buttons
						$('.product_category_add').off('click');		//remove old handlers
						$('.product_category_add').click( function(event) {
								event.stopPropagation();
								show_products_category_editor(this);
						});
						
						//init the select category buttons
						$('.product_category_select').off('click');
						$('.product_category_select').click( function(event) {
								event.stopPropagation();
								select_products_category(this);
						});
						
						
				});
		};
}( jQuery ));

///////////////////////////////////////////////////////////////////////////////////////////////////
// center the bootstrap dialog vertically
///////////////////////////////////////////////////////////////////////////////////////////////////
function centerModal() {
    $(this).css('display', 'block');
    var $dialog = $(this).find(".modal-dialog");
    var offset = ($(window).height() - $dialog.height()) / 2;
    
		if(offset < 0) offset = 0;	//if the dialog is taller than the page - reset it's offset to zero..
		
		// Center modal vertically in window
    $dialog.css("margin-top", offset);
}

$('.modal').on('show.bs.modal', centerModal);
$(window).on("resize", function () {
    $('.modal:visible').each(centerModal);
});

///////////////////////////////////////////////////////////////////////////////////////////////////
// add a new products category

///////////////////////////////////////////////////////////////////////////////////////////////////
$('#add_product_category_submit').click(function(event) {
		//collect all the data
		var channel_id = $('#add_product_category_channel_id').val();
		var parent_id = $('#add_product_category_parent_id').val();
		var title = $.trim($('#add_product_category_title').val());
		
		if(title.length == 0) {
				$('#add_product_category_title_error').show();
				return;
		}
		
		params = { channel_id: channel_id, parent_id: parent_id, title: title, action: 'ajax_add_category', s: 'cAdminproductcategories' };
		params = $.param(params);
		
		$.ajax({
				type: "POST",
				url: "index.php?" + params,
		}).done(function( msg ) {
				$('#products_categories_add_editor').modal('hide');
				
				//reload the dialog view..
				$("#add_category_" + channel_id).html('');		//remove the html..
				
				//remove the data from our temporary saved object list
				for(var i = 0; i < products_categories_selectors_instances.length; i++) {
						if(	products_categories_selectors_instances[i] != undefined
								&& products_categories_selectors_instances[i]['channel_id'] != undefined 
								&& products_categories_selectors_instances[i]['channel_id'] == channel_id) {
										products_categories_selectors_instances.splice(i, 1);
						}
				}
				
				//show the new data..
				$(this).products_categories_selector({
						tmp_products_id: $('#tmp_products_id').val(),
						selector: 'add_category_button_' + channel_id,
						input_container: "add_category_" + channel_id,
						channel_id: channel_id
				});
		});
});
