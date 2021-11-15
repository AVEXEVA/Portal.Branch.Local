$( document ).ready( function( ){
	$( 'input.edit' ).bind( 'keyup', function( event ){
		if( event.keyCode == 13 ){
			$( this ).closest( 'form' ).submit( );
		}
	});
	$( 'select.edit' ).bind( 'change', function( event ){
		$( this ).closest( 'form' ).submit( );
	});
	$( '.card-columns .card-heading h5' ).bind( 'click', function( ){
		$( this ).closest( '.card-heading' ).next( ).toggle( );
		$.ajax({
			url : 'bin/php/post/card_state.php',
			method : 'POST',
			data : {
				Card : $( this ).children( 'span' ).html( )
			}
		});
	});
});