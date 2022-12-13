///////////////////////////////////////////////////////////////////////////
// Data definition.
///////////////////////////////////////////////////////////////////////////
var board = {
	title: "Neuer Guide",
	
	lists: [
	],
	
	cards: [
	],
	
	card_actions: [
	],
	
	
	//////////////////////////////////
	// List-Method definitions
	//////////////////////////////////
	addList: function(id, title) {
		position = this.lists.length;
		
		var list = {
			id: id,
			position: position,
			title: title
		}
		
		this.lists.push(list);
	},
	
	updateListTitle: function(id, title) {		
		for(i = 0; i < this.lists.length; i++) {
			if(this.lists[i].id == id) {
				this.lists[i].title = title;
			}
		}
	},
	
	updateListPosition: function(id, position) {
		for(i = 0; i < this.lists.length; i++) {
			if(this.lists[i].id == id) {
				this.lists[i].position = position;
			}
		}
	},
	
	updateListOrder: function() {
		$('.mv-list').each(function(index) {
			var item = this;
			var id = $(item).attr('id');
			
			board.updateListPosition(id, index);			
		});
	},
	
	deleteList: function(list_id) {
		for(var i = 0; i < board.lists.length; i++) {
			if(board.lists[i].id == list_id) {
				board.lists.splice(i, 1);
				return;
			}
		}
	},
	
	//////////////////////////////////
	// Card-Method definitions
	//////////////////////////////////
	addCard: function(id, list_id, title, content) {
		//TODO: calculate position
		var position = this.countCardsByListId(list_id);
		
		var card = {
			id: id,
			list_id: list_id,
			position: position,
			title: title,
			content: content
		};
		
		this.cards.push(card);
	},
	
	getCardById: function(card_id) {
		var retval = new Array();
		
		for(var i = 0; i < this.cards.length; i++) {
			if(this.cards[i].id == card_id) {
				return this.cards[i];
			}
		}
		
		return null;
	},
	
	countCardsByListId: function(list_id) {
		var count = 0;
		
		for(var i = 0; i < this.cards.length; i++) {
			if(this.cards[i].list_id == list_id) {
				count++;
			}
		}
		
		return count;
	},
	
	getCardsByListId: function(list_id) {
		var retval = new Array();
		
		for(var i = 0; i < this.cards.length; i++) {
			if(this.cards[i].list_id == list_id) {
				retval.push(this.cards[i]);
			}
		}
		
		return retval;
	},
	
	updateCardOrder: function() {
		for(var i = 0; i < this.lists.length; i++) {
			var selector = '#' + this.lists[i].id + " .mv-list-item";
			
			$(selector).each(function(index) {
				var item = this;
				var id = $(item).attr('id');
				
				board.updateCardPosition(id, index);	
			});
		}
	},
	
	updateCardPosition: function(id, position) {
		for(var i = 0; i < board.cards.length; i++) {
			if(board.cards[i].id == id) {
				board.cards[i].position = position;
				return;
			}
		}
	},
	
	updateCardData: function(id, title, content) {		
		for(var i = 0; i < this.cards.length; i++) {
			if(this.cards[i].id == id) {
				this.cards[i].title = title;
				this.cards[i].content = content;
				break;
			}
		}
	},
	
	updateCardsListId: function(card_id, new_list_id) {
		for(var i = 0; i < this.cards.length; i++) {
			if(this.cards[i].id == card_id) {
				this.cards[i].list_id = new_list_id;
				break;
			}
		}
	},
	
	deleteCardById: function(card_id) {
		for(var i = 0; i < board.cards.length; i++) {
			if(board.cards[i].id == card_id) {
				board.cards.splice(i, 1);
				return;
			}
		}
	},
	
	//////////////////////////////////
	// Card-Actions Method definitions
	//////////////////////////////////
	addCardAction: function(id, card_id, title, destination_id, destination_type, destination_www) {
		var position = this.countCardActionsByCardId(card_id);
		
		var card_action = {
			id: id,
			card_id: card_id,
			position: position,
			title: title,
			destination_id: destination_id,
			destination_type: destination_type,
			destination_www: destination_www
		};
		
		this.card_actions.push(card_action);
	},
	
	//////////////////////////////////
	// Daten einer Card-Action aktualisieren.
	//////////////////////////////////	
	updateCardAction: function(id, card_id, position, title, destination_id, destination_type, destination_www) {
		for(var i = 0; i < this.card_actions.length; i++) {
			if(id == this.card_actions[i].id) {
				this.card_actions[i].card_id = card_id;
				this.card_actions[i].position = position;
				this.card_actions[i].title = title;
				this.card_actions[i].destination_id = destination_id;
				this.card_actions[i].destination_type = destination_type;
				this.card_actions[i].destination_www = destination_www;
			}
		}
	},
	
	//////////////////////////////////
	// Anzahl an Card-Actions in einer bestimmten Liste ermitteln
	//////////////////////////////////
	countCardActionsByCardId: function(card_id) {
		var count = 0;
		
		for(var i = 0; i < this.card_actions.length; i++) {
			if(this.card_actions[i].card_id == card_id) {
				count++;
			}
		}
		
		return count;
	},
	
	//////////////////////////////////
	// Karten-Position für alle Karten aktualisieren.
	//////////////////////////////////
	updateCardActionOrder: function() {
		for(var i = 0; i < this.cards.length; i++) {
			var selector = '#' + this.cards[i].id + " .mv-card-action";
			
			$(selector).each(function(index) {
				var item = this;
				var id = $(item).attr('id');
				
				board.updateCardActionPosition(id, index);	
			});
		}
	},
	
	//////////////////////////////////
	// Karten Position in einer Karte aktualisieren.
	//////////////////////////////////
	updateCardActionPosition: function(id, position) {
		for(var i = 0; i < board.card_actions.length; i++) {
			if(board.card_actions[i].id == id) {
				board.card_actions[i].position = position;
				return;
			}
		}
	},
	
	//////////////////////////////////
	// Link-Ziel einer Karte aktualisieren.
	//////////////////////////////////
	updateCardActionDestination: function(id, destination_id, destination_type) {
		for(var i = 0; i < board.card_actions.length; i++) {
			if(board.card_actions[i].id == id) {
				board.card_actions[i].destination_id = destination_id;
				board.card_actions[i].destination_type = destination_type;
			}
		}
	},
	
	//////////////////////////////////
	// Karten anhand einer ID zurückgeben.
	//////////////////////////////////
	getCardActionById: function(id) {
		for(var i = 0; i < board.card_actions.length; i++) {
			if(board.card_actions[i].id == id) {
				return board.card_actions[i];
			}
		}
		
		return null;
	},
	
	//////////////////////////////////
	// Karten-Aktion löschen.
	//////////////////////////////////
	removeCardActionById: function(id) {
		for(var i = 0; i < board.card_actions.length; i++) {
			if(board.card_actions[i].id == id) {
				board.card_actions.splice(i, 1);
				return;
			}
		}
	},
	
	//////////////////////////////////
	// Karten-Aktionen für eine Karten id ermitteln.
	//////////////////////////////////
	getCardActionsByCardId: function(card_id) {
		var retval = new Array();
		
		for(var i = 0; i < this.card_actions.length; i++) {
			if(this.card_actions[i].card_id == card_id) {
				retval.push(this.card_actions[i]);
			}
		}
		
		return retval;
	},
	
	//////////////////////////////////
	// Alle Aktionen ermitteln, die auf eine bestimmte Karte zeigen.
	//////////////////////////////////
	getDstCardActionsOnCardByCardId: function(card_id) {
		var retval = new Array();
		
		for(var i = 0; i < this.card_actions.length; i++) {
			if(this.card_actions[i].destination_id == card_id) {
				retval.push(this.card_actions[i]);
			}
		}
		
		return retval;
	}
	
};