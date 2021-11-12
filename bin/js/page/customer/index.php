<script src="https://www.nouveauelevator.com/vendor/flot/excanvas.min.js"></script>
<script src="https://www.nouveauelevator.com/vendor/flot/jquery.flot.js"></script>
<script src="https://www.nouveauelevator.com/vendor/flot/jquery.flot.pie.js"></script>
<script src="https://www.nouveauelevator.com/vendor/flot/jquery.flot.resize.js"></script>
<script src="https://www.nouveauelevator.com/vendor/flot/jquery.flot.time.js"></script>
<script src="https://www.nouveauelevator.com/vendor/flot/jquery.flot.categories.js"></script>
<script src="https://www.nouveauelevator.com/vendor/flot-tooltip/jquery.flot.tooltip.min.js"></script>
<script>
$( document ).ready( function( ){
	$( 'input.edit' ).bind( 'keyup', function( event ){
		if( event.keyCode == 13 ){
			$( this ).closest( 'form' ).submit( );
		}
	});
});
</script>