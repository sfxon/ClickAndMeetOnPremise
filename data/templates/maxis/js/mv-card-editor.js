var dragula_cards = null;

function mv_init_add_card_button() {
	$('.mv-list-footer-action').off('click');
	$('.mv-list-footer-action').on('click', function() {
		//get list container
		var list = $(this).closest('.mv-list-inner').find('.mv-list-body');
		
		html = $('#mv-card-prototype').html(); //get card demo layout
		html = $( html );	//Wrap html text in jquery.
		
		var new_id = 'card-' + mv_create_guid();
		html = html.clone().attr('id', new_id);		//Build dom element and give it an unique id.
		list.append(html);		//Append new element in DOM (in list)
		
		mv_card_editor__re_init_card_actions();
		
		//Get list_id
		var list_id = $(this).closest('.mv-list').attr('id');

		board.addCard(new_id, list_id, "Neue Karte", "");
	});
}

function mv_init_cards_dragula() {
	if(dragula_cards !== null) {
		dragula_cards.destroy();
	}
	
	var items = new Array();
	
	$('.mv-list-body').each( function(){
		items.push(this);
	});
	
	dragula_cards = dragula(items, {
		moves: function(el, container, handle) {
			return handle.classList.contains('card-handle');
		}
	})
	//Wenn eine Karte verschoben wird.
	.on('dragend', function(el) {
		//Kartenreihenfolgen in Daten aktualisieren.		
		var new_list_id = $(el).closest('.mv-list').attr('id');
		var card_id = $(el).attr('id');
		
		board.updateCardsListId(card_id, new_list_id);		
		board.updateCardOrder();
		arrows.updatePositions();
	});
	
	mv_init_add_card_button();
	mv_init_actions_dragula();
}

function mv_card_editor__re_init_card_actions() {
	$('.mv-card').off('click');
	$('.mv-card').on('click', function() {
		var src_item = this;
		var id = $(this).closest('.mv-list-item').attr('id');
		
		console.log(id);
		
		var data = board.getCardById(id);
		
		mv_set_modal_card_title(data.title);
		mv_set_modal_card_content(data.content);
		
		var myModal = new bootstrap.Modal(document.getElementById('mv-modal-card-editor'));
		
		myModal.show();
		mv_init_modal_card_save_action(src_item, myModal);
		mv_init_modal_card_delete_action(src_item, myModal);
	});
	
	//Additionally re-init all actions on the cards..*/
	mv_card_actions_editor__re_init_add_action();
	mv_init_actions_dragula();
}

function mv_set_modal_card_title(title) {
	$('#mv-card-title').val(title);
}

function mv_set_modal_card_content(content) {
	$('#mv-card-content').val(content);
}

function mv_init_modal_card_save_action(item, myModal) {
	$('.mv-modal-card-save').off('click');
	$('.mv-modal-card-save').on('click', function() {
		//Save title
		var title = $('#mv-card-title').val();
		title = removeTags(title);	//remove all tags for the editor..
		
		$(item).find('.mv-card-header').find('.mv-card-header-title').html(title);
		
		//Save content
		var content = $('#mv-card-content').val();
		content = removeTags(content);	//remove all tags for the editor..
		$(item).find('.mv-card-body').find('.mv-card-content').html(content.substr(0, 64));
		myModal.hide();
		
		//Update data
		board.updateCardData(
			$(item).closest('.mv-list-item').attr('id'),		//ID to identify this card.
			title,
			content
		);
	});
}


function removeTags(str) {
	var tmp = document.createElement('div');
    tmp.innerHTML = str;
    
    return tmp.textContent || tmp.innerText;
}

//////////////////////////////////////////////////////////////////////////////
// Karte löschen.
//////////////////////////////////////////////////////////////////////////////
function mv_init_modal_card_delete_action(item, myModal) {
	var id = $(item).closest('.mv-list-item').attr('id');
	
	$('.mv-modal-card-delete').off('click');
	$('.mv-modal-card-delete').on('click', function() {
		//Alle zugehörigen Card-Actions löschen.
		var card_actions = board.getCardActionsByCardId(id);			//Alle zugehörigen Card-Actions abrufen.
		deleteCardActions(card_actions);
		
		//Alle Card-Actions löschen, die auf diese Karte zeigen!
		var card_actions = board.getDstCardActionsOnCardByCardId(id);	
		removeCardActions(card_actions);
		
		//Karte löschen und View aufräumen.
		board.deleteCardById(id);				//Lösche diese Karte in den Daten
		mv_card_delete_from_html_by_id(id);		//Lösche diese Karte im HTML
		arrows.updatePositions();				//Aktualisiere alle Pfeile (weil sie sich verschoben haben könnten.
		myModal.hide();							//Schließe Editor
	});
}

//////////////////////////////////////////////////////////////////////////////
// Karte aus HTML entfernen
//////////////////////////////////////////////////////////////////////////////
function mv_card_delete_from_html_by_id(card_id) {
	$('#' + card_id).remove();
}

//////////////////////////////////////////////////////////////////////////////
// Daten löschen.
//////////////////////////////////////////////////////////////////////////////
function deleteCardActions(card_actions) {
	for(var i = 0; i < card_actions.length; i++) {					
		arrows.removeByFromId(card_actions[i].id);			//Lösche alle Action-Pfeile dieser Karte
		boardHtmlEditor.removeCard(card_actions[i].id);		//Aus HTML entfernen.
		board.removeCardActionById(card_actions[i].id);		//Lösche die Action.
			
	}
}

//////////////////////////////////////////////////////////////////////////////
// Daten löschen.
//////////////////////////////////////////////////////////////////////////////
function removeCardActions(card_actions) {
	for(var i = 0; i < card_actions.length; i++) {					
		arrows.removeByFromId(card_actions[i].id);			//Lösche alle Action-Pfeile dieser Karte
		
		board.updateCardActionDestination(card_actions[i].id, "", "");
	}
}






