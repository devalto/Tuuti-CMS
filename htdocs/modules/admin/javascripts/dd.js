var makeSortable = function(id, update_url) {
	$(document).ready(function() {
		$("#" + id).sortable({
			axis: "y",
			placeholder: "placeholder",
			opacity: 0.6,
			update: function(event, ui) {
				var parent = ui.item.context.parentNode;
				
				// Navigating throug the elements to find the new priority
				var child = parent.firstChild;
				var i = 0;
				while (child) {
					if (child.nodeType == 1 && child.localName) {
						i++;
						if (child == ui.item.context) {
							var matches = /[a-zA-Z]{1,}-([0-9]{1,})/.exec(child.id);
							if (matches != null) {
								jQuery.getJSON(update_url + "/id/" + matches[1], {new_priority: i, format: 'json'}, function() {
									statusMessage("The priority has been changed succesfully");
								});	
							}
						}
					}
					child = child.nextSibling;
				}
				
				
			}
		});
	});
}

var statusMessage = function(message) {
	var element = $(document.createElement('div'));
	element.addClass('message');
	
	element.append('<div class="ui-state-highlight ui-corner-all"><p>' + message + '</p></div>');
	
	element.hide();
	$('body').prepend(element);
	
	element.show('slide', {direction: 'up'}, 500, function() {
		setTimeout(function() {
			element.hide('drop', {direction: 'up'}, 500, function() {
				element.remove();
			});
		}, 3000);
	});
}

var errorMessage = function(message) {
	var element = $(document.createElement('div'));
	element.addClass('message');
	
	element.append('<div class="ui-state-error ui-corner-all"><p>' + message + '</p></div>');
	
	element.hide();
	$('body').prepend(element);
	
	element.show('slide', {direction: 'up'}, 500, function() {
		setTimeout(function() {
			element.hide('drop', {direction: 'up'}, 500, function() {
				element.remove();
			});
		}, 5000);
	});
}

$(function() {
	$('a.confirmation').each(function(conf) {
		$(this).click(function(e) {
			if (!confirm("Are you sure you want to do that ?")) {
				e.preventDefault();
			}
		});
	});
});