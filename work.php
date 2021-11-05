<?php
if(session_id() == '' || !isset($_SESSION) ){
    session_start( [ 'read_and_close' => true ] );
}
if( isset( $_SESSION[ 'User' ] ) ){
  require('work4.php');
} else {
  ?><script>document.location.href='../login.php?Forward=work.php';</script><?php
}?>