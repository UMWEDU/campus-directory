jQuery( function() {
	if ( jQuery( 'fieldset.offices' ).length < 1 )
		return;
	
	jQuery( '<p class="duplicate-fs-link"><a href="#">Add another office location</a></p>' ).appendTo( 'fieldset.offices' );
	jQuery( '.duplicate-fs-link a' ).click( function() { return duplicateContactFS() } );
	
	jQuery( 'fieldset.offices > .office-block' ).each( function() {
		jQuery( '<p class="remove-fs"><a href="#">X</a></p>' ).appendTo( jQuery( this ) );
	} );
	jQuery( '.remove-fs a' ).live( 'click', function() { return removeContactFS( jQuery( this ) ); } );
	
	function duplicateContactFS() {
		var fs = jQuery( 'fieldset.offices > .office-block' ).last().clone();
		var fieldID = Number( fs.find( 'select' ).attr( 'id' ).split( '_' ).pop() );
		fs.find( 'legend' ).html( fs.find( 'legend' ).html().replace( ( fieldID + 1 ), ( fieldID + 2 ) ) );
		
		var tmpSelect = fs.find( '.office-building-field' );
		tmpSelect.attr( 'id', tmpSelect.attr( 'id' ).replace( '_' + fieldID, '_' + ( fieldID + 1 ) ) );
		tmpSelect.attr( 'name', tmpSelect.attr( 'name' ).replace( '[' + fieldID + ']', '[' + ( fieldID + 1 ) + ']' ) );
		tmpSelect.prev( 'label' ).attr( 'for', tmpSelect.attr( 'id' ) );
		tmpSelect.val( 0 );
		
		var tmpInput = fs.find( '.office-room-field' );
		tmpInput.attr( 'id', tmpInput.attr( 'id' ).replace( '_' + fieldID, '_' + ( fieldID + 1 ) ) );
		tmpInput.attr( 'name', tmpInput.attr( 'name' ).replace( '[' + fieldID + ']', '[' + ( fieldID + 1 ) + ']' ) );
		tmpInput.prev( 'label' ).attr( 'for', tmpInput.attr( 'id' ) );
		tmpInput.val( '' );
		
		jQuery( fs ).insertBefore( '.duplicate-fs-link' );
		
		return false;
	}
	
	function removeContactFS( what ) {
		if ( jQuery( '.office-block' ).length <= 1 ) {
			alert( 'You cannot remove the last set of office fields' );
			return false;
		}
		
		jQuery( what ).closest( '.office-block' ).remove();
		return false;
	}
} );