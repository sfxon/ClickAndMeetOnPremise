$(function() {
	mv_init_lists_dragula();
	mv_init_mouse_move_handler();
	mv_init_add_list_button();
});

function mv_init_lists_dragula() {
	var items = new Array();
	items.push(document.getElementById('mv-lists'));
	
	dragula(items, {
		direction: 'horizontal',
		moves: function(el, container, handle) {
			return handle.classList.contains('handle');
		}
	})
	//Wenn ein Element gedraggt wurde.
	.on('dragend', function(el) {
		//Listenpositionen aktualisieren!
		board.updateListOrder();
		arrows.updatePositions();
	});
}

////////////////////////////////////////////////////////////////////////////////////
// Fügt einen Bewegungshandler zu mv-guide-editor hinzu,
// damit man das Fenster mit der Maus verschieben kann.
////////////////////////////////////////////////////////////////////////////////////
function mv_init_mouse_move_handler() {
	const ele = document.getElementById('mv-guide-editor');
    ele.style.cursor = 'grab';

    let pos = { top: 0, left: 0, x: 0, y: 0 };

    const mouseDownHandler = function(e) {
		if (!event.target.classList.contains('mv-guide-editor') && !event.target.classList.contains('mv-list') && !event.target.classList.contains('mv-lists')) {
			return;
		}
		
		
		
        ele.style.cursor = 'grabbing';
        ele.style.userSelect = 'none';

        pos = {
            left: ele.scrollLeft,
            top: ele.scrollTop,
            // Get the current mouse position
            x: e.clientX,
            y: e.clientY,
        };

        document.addEventListener('mousemove', mouseMoveHandler);
        document.addEventListener('mouseup', mouseUpHandler);
    };

    const mouseMoveHandler = function(e) {
        // How far the mouse has been moved
        const dx = e.clientX - pos.x;
        const dy = e.clientY - pos.y;

        // Scroll the element
        ele.scrollTop = pos.top - dy;
        ele.scrollLeft = pos.left - dx;
    };

    const mouseUpHandler = function() {
        ele.style.cursor = 'grab';
        ele.style.removeProperty('user-select');

        document.removeEventListener('mousemove', mouseMoveHandler);
        document.removeEventListener('mouseup', mouseUpHandler);
		
		arrows.updatePositions();
    };

    // Attach the handler
    ele.addEventListener('mousedown', mouseDownHandler);
}

function mv_init_add_list_button() {
	$('.mv-add-list').on('click', function() {
		
		var html = $('#mv-list-prototype').html();	//get list layout
		html = $( html );	//Wrap html text in jquery.
		var new_element_id = 'list-' + mv_create_guid();
		html = html.clone().attr('id', new_element_id);		//Build dom element and give it an unique id.
		$('#mv-lists').append(html);	//Append new element in DOM (in list)
		
		mv_init_cards_dragula();
		mv_init_list_header_edit_button();
		
		board.addList(new_element_id, "Neue Liste", 0);
		
		re_init_list_actions();
	});
}


function mv_init_list_header_edit_button() {
	//$('.mv-list-header-edit-button').off('click');
	//$('.mv-list-header-edit-button').on('click', function() {
	$('.mv-list-header-title').off('click');
	$('.mv-list-header-title').on('click', function() {
		var src_item = this;
		mv_set_modal_list_title(src_item);
		
		var myModal = new bootstrap.Modal(document.getElementById('mv-modal-list-editor'));
		
		myModal.show();
		mv_init_modal_save_action(src_item, myModal);
		mv_init_modal_delete_action(src_item, myModal);
	});
}

function mv_set_modal_list_title(item) {
	var content = $(item).closest('.mv-list-header').find('.mv-list-header-title.handle').html();
	$('#mv-list-title').val(content);
}

function mv_init_modal_save_action(item, myModal) {
	$('.mv-modal-list-save').off('click');
	$('.mv-modal-list-save').on('click', function() {
		var content = $('#mv-list-title').val();
		
		//Update data
		board.updateListTitle(
			$(item).closest('.mv-list').attr('id'),		//ID to identify this list.
			content
		);
		
		//find list and set title text
		$(item).closest('.mv-list-header').find('.mv-list-header-title.handle').html(removeTags(content));
		myModal.hide();
	});
}

function mv_init_modal_delete_action(item, myModal) {
	$('.mv-modal-list-delete').off('click');
	$('.mv-modal-list-delete').on('click', function() {
		var id = $(item).closest('.mv-list').attr('id');
		
		//Alle dazugehörigen Karten ermitteln.
		var cards = board.getCardsByListId(id);
		
		//Alle dazugehörigen Karten der Reihe nach löschen.
		for(var i = 0; i < cards.length; i++) {
			//Alle zugehörigen Card-Actions löschen.
			var cards_id = cards[i].id;
			var card_actions = board.getCardActionsByCardId(cards_id);			//Alle zugehörigen Card-Actions abrufen.
			deleteCardActions(card_actions);
			
			//Alle Card-Actions löschen, die auf diese Karte zeigen!
			var card_actions = board.getDstCardActionsOnCardByCardId(cards_id);	
			removeCardActions(card_actions);
			
			//Karte löschen und View aufräumen.
			board.deleteCardById(cards_id);				//Lösche diese Karte in den Daten
			mv_card_delete_from_html_by_id(cards_id);	//Lösche diese Karte im HTML
			
		}
		
		//Hauptdaten löschen (die Liste).
		board.deleteList(id);				//Liste in den Daten löschen.
		mv_list_delete_in_html(id);	//Liste im HTML löschen.
		
		arrows.updatePositions();				//Aktualisiere alle Pfeile (weil sie sich verschoben haben könnten).
		
		myModal.hide();
	});
}

function re_init_list_actions() {
	$('.mv-list-body').off('scroll');
	$('.mv-list-body').on('scroll', function() {
		arrows.updatePositions();
	});
}




////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Create some pseudo random unique id.
// Good thing here: We can check, if an element with this unique id already exists.
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function mv_create_guid() {
	var fC = function() { return (((1 + Math.random()) * 0x10000) | 0).toString(16).substring(1).toUpperCase(); }
	var guid = "";
	
	do {
		unique = true;
		guid = fC() + fC() + "-" + fC() + "-" + fC() + "-" + fC() + "-" + fC() + fC() + fC();
		
		if($('#' + guid).length > 0) {
			unique = false;
		}
	} while(!unique);

	return guid;
}

//////////////////////////////////////////////////////////////////////////////
// Liste aus HTML entfernen
//////////////////////////////////////////////////////////////////////////////
function mv_list_delete_in_html(list_id) {
	$('#' + list_id).remove();
}
