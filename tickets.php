<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [ 'read_and_close' => true ] ); 
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/bin/php/index.php' );
}
if(isset($_SESSION['User'],$_SESSION['Hash'])){
  if ( FALSE ) {
    /*Override Zone*/
  } elseif( in_array( $_SESSION[ 'User' ], array( 250, 895 ) ) && ( isset( $_GET[ 'Version' ] ) || isset( $_SESSION[ 'Version' ] ) ) ){
    $_SESSION[ 'Version' ] = isset( $_GET[ 'Version' ] ) ? $_GET[ 'Version' ] : $_SESSION[ 'Version' ];
    switch( isset( $_GET[ 'Version' ] ) ? $_GET[ 'Version' ] : $_SESSION[ 'Version' ] ){
      case 'Beta' : 
        require('tickets1.php');
        break;
      case 'Live':
        require('tickets1.php');
        break;
    }
  } else {
    require('tickets1.php');
  }
} else {
  ?><script>document.location.href="../login.php?Forward=tickets.php?<?php echo http_build_query( $_GET );?>";</script><?php
}?>