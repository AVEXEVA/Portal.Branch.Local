<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( ); 
    require( '/var/www/portal.live.local/html/cgi-bin/php/index.php' );
}
if(isset($_SESSION['User'],$_SESSION['Hash'])){
  if ( FALSE ) {
    /*Override Zone*/
  } elseif( in_array( $_SESSION[ 'User' ], array( 250, 895 ) ) && ( isset( $_GET[ 'Version' ] ) || isset( $_SESSION[ 'Version' ] ) ) ){
    $_SESSION[ 'Version' ] = isset( $_GET[ 'Version' ] ) ? $_GET[ 'Version' ] : $_SESSION[ 'Version' ];
    switch( isset( $_GET[ 'Version' ] ) ? $_GET[ 'Version' ] : $_SESSION[ 'Version' ] ){
      case 'Beta' : 
        require('new-ticket2.php');
        break;
      case 'Live':
        require('new-ticket1.php');
        break;
    }
  } else {
    require('new-ticket1.php');
  }
} else {
  ?><script>document.location.href='../login.php?Forward=location.php?<?php echo isset( $_GET[ 'ID'] ) ? 'ID=' . $_GET['ID'] : NULL;?>';</script><?php
}?>