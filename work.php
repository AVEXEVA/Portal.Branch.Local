<?php
if(session_id() == '' || !isset($_SESSION) ){
    session_start();
}
if( isset( $_SESSION[ 'User' ] ) ){
  require('work4.php');
} else {
  ?><script>document.location.href='../login.php?Forward=work.php';</script><?php
}?>