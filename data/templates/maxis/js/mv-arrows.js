var arrows = {
	items: [],			//Holds created leader lines.
	mode: "showOnHover",		//Can be showOnHover or showAlways. This decides how everything is rendered. Whole structure can be rebuild to showAlways or only show on hover.

	add: function(element_id1, element_id2) {
		this.removeByFromId(element_id1);
		
		// Add new item
		var leader_line = this.addLeaderLine(element_id1, element_id2, this.mode);
		
		var arrow = {
			leader_line: leader_line,
			from: element_id1,
			to: element_id2
		};
		
		this.items.push(arrow);
	},
	
	removeByFromId: function(element_id1) {
		//Check if entry exists - remove if already exists and add new one..
		var index = this.getByElementId1(element_id1);
		
		if(null !== index) {
			this.items[i].leader_line.remove();
			this.items.splice(i, 1);
		}
		
		this.updatePositions();		//Alle Positionen neu berechnen, weil sie sich hiermit verschoben haben könnten.
	},
	
	// Update all positions - when window or object has been moved..
	// Cost-intensive - use updateArrowPosition to update a single arrows position.
	updatePositions() {
		for(var i = 0; i < arrows.items.length; i++) {
			arrows.items[i].leader_line.position();
		}
	},
	
	updateArrowBySrc(element_id1, element_id2) {
		for(var i = 0; i < arrows.items.length; i++) {
			if(arrows.items[i].from == element_id1) {
				//Remove this arrow..
				arrows.items[i].leader_line.remove();
				arrows.items.splice(i, 1);
				break;
			}
		}
		
		arrows.add(element_id1, element_id2);		
	},
	
	addLeaderLine: function(element_id1, element_id2, mode) {
		var leader_line = null;
		
		if(mode == "showOnHover") {
			leader_line = new LeaderLine(
				LeaderLine.mouseHoverAnchor(document.getElementById(element_id1)),
				document.getElementById(element_id2)
			);
		} else {		//if mode == showAlways
			leader_line = new LeaderLine(
				document.getElementById(element_id1),
				document.getElementById(element_id2)
			);
		}
		
		return leader_line;
	},
	
	setMode: function(mode) {
		if(arrows.mode == mode) {
			return;
		}
		
		arrows.mode = mode;
		
		for(i = 0; i < arrows.items.length; i++) {
			arrows.items[i].leader_line.remove();
			arrows.items[i].leader_line = arrows.addLeaderLine(
				arrows.items[i].from,
				arrows.items[i].to,
				mode
			);
		}
	},
	
	getByElementId1: function(element_id1) {
		for(i = 0; i < arrows.items.length; i++) {
			if(element_id1 == arrows.items[i].from) {
				return i;
			}
		}
		
		return null;
	}
};

$(function() {
	$('#toggleArrowMode').on('click', function() {
		if(arrows.mode == "showOnHover") {
			arrows.setMode("showAlways");
			$('#toggleArrowMode').html('Pfeile verstecken');
		} else {
			arrows.setMode("showOnHover");
			$('#toggleArrowMode').html('Pfeile anzeigen');
		}
	});
});
