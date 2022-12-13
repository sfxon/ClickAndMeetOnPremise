var dragula_card_actions = new Array();
var mv_card_actions_hovered_card = null;

function mv_card_actions_editor__re_init_add_action() {
	$('.mv-card-add-action').off('click');
	$('.mv-card-add-action').on('click', function(event) {
		event.stopPropagation();
		event.preventDefault();
		
		//add to list..
		html = $('#mv-card-action-prototype').html();	//get html prototype.
		html = $( html );						//Wrap html text in jquery.
		
		var new_id = 'cardaction-' + mv_create_guid();
		
		html = html.clone().attr('id', new_id);		//Build dom element and give it an unique id.
		
		$(this).closest('.mv-card').find('.mv-card-actions').append(html);
		
		mv_card_actions_edit_re_init_edit_actions();
		
		var card_id = $(this).closest('.mv-list-item').attr('id');

		board.addCardAction(new_id, card_id, "Neue Aktion", "", "", "");
		
		mv_card_actions_editor__re_init_card_actions();		//Aktionen reinitialisieren, die ausgeführt werden, wenn man auf diese Aktion klickt!
	});
}

function mv_init_actions_dragula() {
	for(var i = 0; i < dragula_card_actions.length; i++) {
		dragula_card_actions[i].destroy();
	}
	
	dragula_card_actions = new Array();
	
	
	$('#mv-guide-editor .mv-card').each(function() {
		var me = this;
		var my_list_item = $(this).closest('.mv-list-item').get()[0];		//Benötige das HTML Element für den Abgleich, ob wir über das aktuelle Element hovern!
		
		items = $(me).find('.mv-card-actions').toArray();
		
		dragula_card_actions.push(
			dragula(items)
				.on('drag', function(el) {
					mv_card_actions__hover_cards_on_drag(el, me, my_list_item);
				})
				.on('dragend', function(el) {
					$('body').off('mousemove');
					$('.mv-list-item').removeClass('mv-action-hover');
					
					mv_card_actions__link_cards_after_drag(el, me, my_list_item);
					
					//Update positions
					board.updateCardActionOrder();
					arrows.updatePositions();
				})
		);
	});
}

////////////////////////////////////////////////////////////////////////////////////////////
// Diese Funktion sorgt für den Hover-Effekt über einer Karte..
////////////////////////////////////////////////////////////////////////////////////////////
function mv_card_actions__hover_cards_on_drag(el, me, my_list_item) {
	card_boundaries = mv_get_card_boundaries();
	

	$('body').off('mousemove');
	$('body').on('mousemove', function(event) {
		var x = event.pageX;
		var y = event.pageY;
		var set = false;
		
		//Check if we are over one of the droppable containers..
		for(i = 0; i < card_boundaries.length; i++) {
			var elem = card_boundaries[i];
			
			if(elem.item == my_list_item) {
				continue;
			}
			
			if(x > elem.x1 && x < elem.x2 && y > elem.y1 && y < elem.y2) {
				$(elem.item).addClass('mv-action-hover');
				mv_card_actions_hovered_card = elem.item;
				set = true;
			} else {
				$(elem.item).removeClass('mv-action-hover');
			}
		}
		
		if(false == set) {
			mv_card_actions_hovered_card = null;
		}
	});
}

////////////////////////////////////////////////////////////////////////////////////////////
// Diese Funktion ermittelt, ob eine Karte mit einer Aktion verlinkt werden soll,
// und verlinkt diese anschließend!
// Sie wird aufgerufen, wenn ein Element gezogen wurde, aber dann losgelassen wurde..
////////////////////////////////////////////////////////////////////////////////////////////
function mv_card_actions__link_cards_after_drag(el, me, my_list_item) {
	if(mv_card_actions_hovered_card != null) {
		var from_id = $(el).attr('id');
		var to_id = $(mv_card_actions_hovered_card).attr('id');
		
		//Update data
		board.updateCardActionDestination(from_id, to_id, "mv-card");
		arrows.updateArrowBySrc(from_id, to_id);
		
		//Let some time flow to link the too, because of the incredible return of the great white dope.
		//var line = mv_card_action_line__add(from_id, to_id);
	}
}

function mv_card_actions_edit_re_init_edit_actions() {
	$('.mv-card-action').off('click');
	$('.mv-card-action').on('click', function(event) {
		event.preventDefault();
		event.stopPropagation();
		
	});
}

function mv_get_card_boundaries() {
	var retval = new Array();
	
	$('#mv-guide-editor .mv-list-item').each(function() {
		var offset = $(this).offset();
		var width = $(this).width();
		var height = $(this).height();
		
		var data = {
			item: this,
			x1: offset.left,
			y1: offset.top,
			x2: offset.left + width,
			y2: offset.top + height
		};
		
		retval.push(data);
	});
	
	return retval;
}

function mv_card_actions_editor__re_init_card_actions() {
	$('.mv-card-action').off('click');
	$('.mv-card-action').on('click', function(event) {
		event.stopPropagation();
		event.preventDefault();
		
		var src_item = this;
		var src_item_id = $(src_item).attr('id');
		var data = board.getCardActionById(src_item_id);
		
		mv_set_modal_card_action_title(data.title);
		modalCardDestinationEditor.init(data);
		modalCardDestinationEditor.show();
	});
}

function mv_set_modal_card_action_title(title) {
	$('#mv-card-action-title').val(title);
}
