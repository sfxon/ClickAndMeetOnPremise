
var modalCardDestinationEditor = {
	data: null,
	
	//////////////////////////////////////////////////////////////////////
	// Initialisieren - use like a constructor!
	//////////////////////////////////////////////////////////////////////
	init: function(data) {
		this.data = data;
		this.setDestinationTypeDropdown(data.destination_type);
		this.init_onDestinationTypDropdownChange();
	},
	
	//////////////////////////////////////////////////////////////////////
	// Editor anzeigen.
	//////////////////////////////////////////////////////////////////////
	show: function() {
		var myModal = new bootstrap.Modal(document.getElementById('mv-modal-card-action-editor'));
		
		myModal.show();
		this.initSaveAction(myModal);
		this.initDeleteAction(myModal);
	},
	
	//////////////////////////////////////////////////////////////////////
	// Speicher-Vorgang
	//////////////////////////////////////////////////////////////////////
	initSaveAction: function(myModal) {
		var me = this;
		
		$('.mv-modal-card-action-save').off('click');
		$('.mv-modal-card-action-save').on('click', function(event) {
			//get title
			var title = $('#mv-card-action-title').val();
			
			//get link type
			var destination_type = $('#mv-card-action-type').val();
			
			//get destination (set to empty, if link_type is not card)
			var destination_id = "";
			
			if(destination_type == 'card') {
				destination_id = $('#mv-card-action-type-card-card').val();
			}
			
			//get www url
			var destination_www = $('#mv-card-action-link-www').val();
			
			//update data object
			board.updateCardAction(me.data.id, me.data.card_id, me.data.position, title, destination_id, destination_type, destination_www);
			
			//update arrowlines!
			if(destination_type == 'card' && destination_id != "") {
				arrows.add(me.data.id, me.data.destination_id);
			} else {
				arrows.removeByFromId(me.data.id);
			}
			
			//update user interface (title)
			title = removeTags(title);
			$('#' + me.data.id).html(title);
			myModal.hide();
		});
	},
	
	//////////////////////////////////////////////////////////////////////
	// Löschen Aktion initialisieren.
	//////////////////////////////////////////////////////////////////////
	initDeleteAction: function(myModal) {
		var me = this;
		
		$('.mv-modal-card-action-delete').off('click');
		$('.mv-modal-card-action-delete').on('click', function(event) {
			board.removeCardActionById(me.data.id);		//Remove from data.
			boardHtmlEditor.removeCard(me.data.id);			//Remove Card from editor.
			arrows.removeByFromId(me.data.id);			//Remove Arrow.
			myModal.hide();
		});
	},
	
	//////////////////////////////////////////////////////////////////////
	// Das Link-Typ Dropdown auf einen bestimmten Wert setzen.
	//////////////////////////////////////////////////////////////////////
	setDestinationTypeDropdown: function(destination_type) {
		$('#mv-card-action-type').val(destination_type);
	
		//Check, which one is the current selected type...
		var destination_type = this.getCurrentDestinationType();
		
		if(null == destination_type) {
			destination_type = 'card';
			$('#mv-card-action-type').val(destination_type);	//Update again, since it now is empty..
		}
		
		this.updateByDestinationType(destination_type);
	},
	
	//////////////////////////////////////////////////////////////////////
	// Wenn eine andere Link-Typ ausgewählt wurde.
	//////////////////////////////////////////////////////////////////////
	init_onDestinationTypDropdownChange: function() {
		$('#mv-card-action-type').on('change', function() {
			var destination_type = $(this).val();
			modalCardDestinationEditor.updateByDestinationType(destination_type);
		});
	},
	
	//////////////////////////////////////////////////////////////////////
	// Aktuell ausgewählten Destination Typ abfragen.
	//////////////////////////////////////////////////////////////////////
	getCurrentDestinationType: function() {
		var destination_type = $('#mv-card-action-type').val();
		return destination_type;
	},
	
	//////////////////////////////////////////////////////////////////////
	// Editor anhand des Destination Typs aktualisieren.
	//////////////////////////////////////////////////////////////////////
	updateByDestinationType: function(destination_type) {
		this.hideAllDestinationEditors();
	
		switch(destination_type) {
			case 'www':
				this.showWww();
				break;
			case 'card':
				this.showCard();
				break;
		}
	},
	
	//////////////////////////////////////////////////////////////////////
	// Alle Sondereditoren ausblenden.
	//////////////////////////////////////////////////////////////////////
	hideAllDestinationEditors: function() {
		$('.mv-card-action-type-link-card').hide();
		$('.mv-card-action-type-link-www').hide();
	},
	
	//////////////////////////////////////////////////////////////////////
	// WWW-Link Editor einblenden.
	//////////////////////////////////////////////////////////////////////
	showWww: function() {
		$('.mv-card-action-type-link-www').show();
	},

	//////////////////////////////////////////////////////////////////////
	// Karten-Link-Editor einblenden.
	//////////////////////////////////////////////////////////////////////
	showCard: function() {
		$('.mv-card-action-type-link-card').show();
		
		//Set lists
		this.setLists();
		
		//Set current list item
		this.setCurrentListItemByCardId(this.data.destination_id);
		currentListId = this.getCurrentSelectedListItemId();
		
		//Set cards
		this.setCardsByListId(currentListId);
		this.setCurrentCard(this.data.destination_id);
		
		//set current cards item
	},
	
	//**************************************************************************************************
	// Listen Dropdown
	//**************************************************************************************************
	//////////////////////////////////////////////////////////////////////
	// Listen Dropdown initialisieren.
	//////////////////////////////////////////////////////////////////////
	setLists: function() {
		var lists = board.lists;
		
		this.clearListHtmlSelect();
		this.addListToHtmlSelect("", "Bitte eine Liste auswählen");
		
		for(var i = 0; i < lists.length; i++) {
			this.addListToHtmlSelect(lists[i].id, lists[i].title);
		}
		
		this.init_ListsDropdownChangeHandler();
	},
	
	//////////////////////////////////////////////////////////////////////
	// Aktuell ausgewähltes Listen-Element anhand des Karten-Elements festlegen.
	//////////////////////////////////////////////////////////////////////	
	setCurrentListItemByCardId: function(card_id) {
		var card_data = board.getCardById(card_id);
		
		if(null == card_data) {
			return;
		}
		
		this.setCurrentListItemById(card_data.list_id);
	},
	
	//////////////////////////////////////////////////////////////////////
	// Abfragen, welches Listen-Element gerade ausgewählt ist.
	//////////////////////////////////////////////////////////////////////
	getCurrentSelectedListItemId: function() {
		return $('#mv-card-action-type-card-list').val();
	},
	
	//////////////////////////////////////////////////////////////////////
	// Aktuelles Listen-Element anhand der ID festlegen.
	//////////////////////////////////////////////////////////////////////
	setCurrentListItemById: function(list_id) {
		$('#mv-card-action-type-card-list').val(list_id);
	},
	
	//////////////////////////////////////////////////////////////////////
	// Dropdown-Option für Listen hinzufügen.
	//////////////////////////////////////////////////////////////////////	
	addListToHtmlSelect(option_value, option_text) {
		var o = new Option(option_text, option_value);
		$(o).html(option_text);
		$('#mv-card-action-type-card-list').append(o);
	},
	
	//////////////////////////////////////////////////////////////////////
	// Listen-Dropdown leeren.
	//////////////////////////////////////////////////////////////////////
	clearListHtmlSelect: function() {
		$('#mv-card-action-type-card-list option').remove();
	},
	
	//////////////////////////////////////////////////////////////////////
	// Action Listener: Wenn ein Wert verändert wurde.
	//////////////////////////////////////////////////////////////////////
	init_ListsDropdownChangeHandler: function() {
		var me = this;
		
		$('#mv-card-action-type-card-list').off('change');
		$('#mv-card-action-type-card-list').on('change', function() {
			me.setCardsByListId($(this).val());
		});
	},
	
	//**************************************************************************************************
	// Karten-Dropdown
	//**************************************************************************************************
	//////////////////////////////////////////////////////////////////////
	// Karten-Dropdown initialisieren
	//////////////////////////////////////////////////////////////////////
	setCardsByListId: function(list_id) {
		this.clearCardHtmlSelect();
		this.addCardToHtmlSelect("", "Bitte auswählen");
			
		var cards = board.getCardsByListId(list_id);
		
		for(var i = 0; i < cards.length; i++) {
			this.addCardToHtmlSelect(cards[i].id, cards[i].title);
		}
	},
	
	//////////////////////////////////////////////////////////////////////
	// Aktuelle Karte festlegen
	//////////////////////////////////////////////////////////////////////
	setCurrentCard: function(card_id) {
		$('#mv-card-action-type-card-card').val(card_id);
	},
	
	//////////////////////////////////////////////////////////////////////
	// Eine Option zum Karten-Dropdown hinzufügen.
	//////////////////////////////////////////////////////////////////////
	addCardToHtmlSelect(option_value, option_text) {
		var o = new Option(option_text, option_value);
		$(o).html(option_text);
		$('#mv-card-action-type-card-card').append(o);
	},
	
	//////////////////////////////////////////////////////////////////////
	// Karten-Dropdown leeren.
	//////////////////////////////////////////////////////////////////////
	clearCardHtmlSelect: function() {
		$('#mv-card-action-type-card-card option').remove();
	}
}

