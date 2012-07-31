jQuery( function( $ ) {
	$( '.alpha-links li' ).css( { 'float' : 'left', 'width' : '1.5em', 'list-style' : 'none', 'padding' : 0, 'margin' : 0 } );
	var total = 0;
	$( '.alpha-list > li .letter-list' ).each( function() { total++; total += $( this ).children().length; } );
	/*var total = $( '.alpha-list > li' ).length;*/
	var perpage = Math.ceil( total / 3 );
	$( '.alpha-list' ).addClass( 'one-third first' ).parent().append( $( '<ul class="alpha-list one-third second"></ul><ul class="alpha-list one-third third"></ul>' ) );
	
	console.log( 'Total: ' + total );
	console.log( 'Per page: ' + perpage );
	
	var current = 0;
	var alreadyDone = false;
	$( '.alpha-list.first > li .letter-list' ).each( function() { 
		if ( alreadyDone ) {
			return;
		}
		
		current++;
		current += $( this ).children().length; 
		if ( current >= perpage ) {
			alreadyDone = true;
			
			var newItem = ( $( this ).children().length - ( current - perpage ) );
			
			console.log( 'Current: ' + current );
			console.log( 'Nth-child: ' + newItem );
			
			if ( newItem === 0 ) {
				$( this ).closest( '.alpha-letter' ).nextAll().appendTo( '.alpha-list.second' );
				$( this ).closest( '.alpha-letter' ).prependTo( '.alpha-list.second' );
				return;
			}
			
			$( '<ul class="letter-list"></ul>' ).appendTo( '.alpha-list.second' );
			$( this ).find( ':nth-child( ' + newItem + ' )' ).nextAll().appendTo( $( '.alpha-list.second ul.letter-list' ) );
			$( this ).closest( '.alpha-letter' ).nextAll().appendTo( $( '.alpha-list.second' ) );
		}
	} );
	
	var current = 0;
	var alreadyDone = false;
	$( '.alpha-list.second > li .letter-list' ).each( function() { 
		if ( alreadyDone ) {
			return;
		}
		
		current++;
		current += $( this ).children().length; 
		if ( current >= perpage ) {
			alreadyDone = true;
			
			var newItem = ( $( this ).children().length - ( current - perpage ) );
			
			console.log( 'Current: ' + current );
			console.log( 'Nth-child: ' + newItem );
			
			if ( newItem === 0 ) {
				$( this ).closest( '.alpha-letter' ).nextAll().appendTo( '.alpha-list.third' );
				$( this ).closest( '.alpha-letter' ).prependTo( '.alpha-list.third' );
				return;
			}
			
			$( '<ul class="letter-list"></ul>' ).appendTo( '.alpha-list.third' );
			$( this ).find( ':nth-child( ' + newItem + ' )' ).nextAll().appendTo( $( '.alpha-list.third ul.letter-list' ) );
			$( this ).closest( '.alpha-letter' ).nextAll().appendTo( $( '.alpha-list.third' ) );
		}
	} );
	
	/*$( '.alpha-list.first > li:nth-child( ' + perpage + ' )' ).nextAll().appendTo( '.alpha-list.second' );
	$( '.alpha-list.second > li:nth-child( ' + perpage + ' )' ).nextAll().appendTo( '.alpha-list.third' );*/
} );