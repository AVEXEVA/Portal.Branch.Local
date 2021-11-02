<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( ); 
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/cgi-bin/php/index.php' );
}
if(isset($_SESSION['User'],$_SESSION['Hash'])){
	if ( FALSE ) {

	} elseif( in_array( $_SESSION[ 'User' ], array( 250, 895 ) ) && isset( $_GET[ 'Version' ] ) ){
		$_SESSION[ 'Version' ] = $_GET[ 'Version' ];
  		switch( $_GET[ 'Version' ] ){
	  		case 'Beta' : 
	  			require('home2021h.php');
	  			break;
	  		case 'Live':
	  			require('home2021h.php');
	  			break;
	  	}
	} elseif( in_array( $_SESSION[ 'User' ], array( 250, 895 ) ) ) {
		require( 'home-choice.php' );
	} else {
	  	require('home2021h.php');
	}
} else {?><html><head><script>document.location.href='../login.php?Forward=home.php';</script></head></html><?php }
?>