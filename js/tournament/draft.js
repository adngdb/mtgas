function start(id) {
	ajax_error_management() ;
	booster_cards = document.getElementById('booster_cards') ;
	drafted_cards = document.getElementById('drafted_cards') ;
	timeleft = document.getElementById('timeleft') ;
	ready = document.getElementById('ready') ;
	r_changing = false ;
	ready.addEventListener('change', function(ev) { // On click
		r_changing = true ; // Forbid server to change readyness
		$.getJSON('json/draft.php', {'id': id, 'ready': ev.target.checked+0}, function(data) {
			r_changing = false ;
		}) ;
	}, false) ;
	game = new Object() ;
	game.image_cache = new image_cache() ;
	cache_draft = null ;
	cache_pick = '' ;
	cache_pool = '' ;
	img_width = 200 ;
	draft(id) ;
}
function Img(container, ext, name, attrs) {
	var img = create_img('', name, name) ;
	img.width = img_width ;
	container.appendChild(img) ;
	// Loading its URL independently
	img.url = card_image_url(ext, name, attrs) ;
	game.image_cache.load(card_images(img.url), function(img, tag) {
		tag.src = img.src ;
		tag.url = img.src ;
	}, function(img, url) {
		//alert(url) ;
	}, img) ;
	return img ;
}
function draft(id) { // Call by 1s timer (self-relaunching), get current booster, display it if it changed
	$.getJSON('json/draft.php', {'id': id}, function(data) {
		// Redirect (if needed) before anything else
		switch ( parseInt(data.tournament.status) ) {
			case 3 : // Standard case : Drafting
				break ;
			case 4 : // Normal case : Building
				window.location.replace('build.php?id='+id) ;
				return null ;
			default : // All other cases considered anormal
				window.location.replace('index.php?id='+id) ; // Go to tournament's main page
				return null ;
		}
		timeleft.value = time_disp(data.tournament.timeleft) ;
		// Retroact readyness
		if ( ! r_changing ) {
			var readyness = ready.checked+0 ;
			if ( readyness != data.player.ready ) {
				readyness = data.player.ready ;
				if ( readyness == 1 )
					ready.checked = true ;
				else
					ready.checked = false ;
			}
		}
		if ( data.msg ) // Display msg only if any (after redirection, if tournament changed, it may have generated an error message)
			alert(data.msg) ;
		// Update draft
		if ( ( data.booster.content != cache_draft ) || ( data.booster.pick != cache_pick ) ) {
			cache_draft = data.booster.content ; // Caching draft displaying
			cache_pick = data.booster.pick ; // Caching pick
			var content = JSON.parse(data.booster.content);
			var cards = content.cards ;
			node_empty(booster_cards) ;
			for ( var i = 0 ; i < cards.length ; i++ ) {
				var card = cards[i] ;
				// Image in div
				var img = Img(booster_cards, content.ext, card.name, card.attrs) ;
				img.id = i + 1 ;
				if ( parseInt(img.id) == Math.abs(data.booster.pick) )
					img.className = 'pick' ;
				// Event
				img.addEventListener('click', function(ev) { // On click
					ev.target.className = 'picking' ;
					var r = ready.checked+0 ; 
					if ( localStorage['draft_auto_ready'] == 'true' )
						r = 1 ;
					$.getJSON('json/draft.php', {'id': id, 'pick': ev.target.id, 'ready': r}, function(data) { // Update pick
						if ( data.msg )
							alert(data.msg) ;
					}) ;
				}, false) ;
				// Transform
				if ( iso(card.attrs.transformed_attrs) && iss(card.attrs.transformed_attrs.name) ) {
					game.image_cache.load(card_images(card_image_url(content.ext, card.attrs.transformed_attrs.name, card.attrs)), function(img, tag) {
						tag.transformed_url = img.src ;
						tag.addEventListener('mouseover', function(ev) {
							ev.target.src = tag.transformed_url ;
						}, false) ;
						tag.addEventListener('mouseout', function(ev) {
							ev.target.src = ev.target.url ;
						}, false) ;
					}, function(card, url) {
					}, img) ;
				}

			}
		}
		// Update pool
		if ( data.player.deck != cache_pool ) {
			cache_pool = data.player.deck ; // Caching deck parsing
			node_empty(drafted_cards) ;
			var lines = deck_parse(data.player.deck) ;
			for ( var i = 0 ; i < lines.length ; i++) {
				var line = lines[i] ;
				switch ( typeof line ) {
					case 'string' : 
						var h2 = document.createElement('h2') ;
						h2.appendChild(document.createTextNode(line)) ;
						drafted_cards.appendChild(h2) ;
						break ;
					case 'object' :
						var attrs = {} ;
						if ( lines[i][3] > 0 )
							attrs.nb = lines[i][3] ;
						var img = Img(drafted_cards, lines[i][1], lines[i][2], attrs) ;
						break ;
					default : 
						alert(typeof line) ;
				}
			}
		}
		window.setTimeout(draft, draft_timer, id) ;
	}) ;
}
