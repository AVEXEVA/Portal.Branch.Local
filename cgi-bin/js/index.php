<script data-pagespeed-no-defer src="https://www.nouveauelevator.com/vendor/jquery/jquery.min.js"></script>
<?php
  $_GET[ 'Bootstrap' ] = isset( $_GET[ 'Bootstrap' ] ) ? $_GET[ 'Bootstrap' ] : null;
  switch( $_GET[ 'Bootstrap' ] ){
    case '5.1':?><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-U1DAWAznBHeqEIlVSCgzq+c9gqGAJn5c/t99JyeKa9xxaYpSvHU5awsuZVVFIhvj" crossorigin="anonymous"></script><?php break;
    default:?><script src="https://www.nouveauelevator.com/vendor/bootstrap/js/bootstrap.min.js"></script><?php break;
    break;
  }
?>
<?php if( !isset( $_GET[ 'JQUERY_UI' ]) || $_GET[ 'JQUERY_UI' ]  == 1 ){?><script data-pagespeed-no-defer src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script><?php }?>
<script src="https://www.nouveauelevator.com/portal/cgi-bin/js/functions.js"></script>
<script src="https://www.nouveauelevator.com/portal/cgi-bin/js/onload.js"></script>
<script src="https://kit.fontawesome.com/46bc044748.js" crossorigin="anonymous"></script>
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-56288874-1"></script>
<script src="https://www.nouveauelevator.com/portal/cgi-bin/js/index.js"></script>
<script src="cgi-bin/js/index.js?<?php echo rand( 1000, 9999999 );?>"></script>
<script>
  function linkTab( Tab ){ 
    $("div[tab='" + Tab + "']")[0].scrollIntoView( );
    $("div[tab='" + Tab + "']").click( ); 
  }
</script>
<?php 
if( file_exists( bin_js . 'page/' . substr( basename( $_SERVER['SCRIPT_NAME'] ), 0, strlen( basename( $_SERVER['SCRIPT_NAME'] ) ) - 4 ) . '/index.php') ){
  require( bin_js . 'page/' .  substr( basename( $_SERVER['SCRIPT_NAME'] ), 0, strlen( basename( $_SERVER['SCRIPT_NAME'] ) ) - 4 ) . '/index.php' );
}
?>
<?php require( bin_js . 'datatables.php');?>