<meta name="viewport" content="width=device-width, initial-scale=1">
<?php if( isset( $_GET[ 'Bootstrap' ] ) ){
  switch( $_GET[ 'Bootstrap' ] ){
    case '5.1':?><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KyZXEAg3QhqLMpG8r+8fhAXLRk2vvoC2f3B09zVXn8CA5QIVfZOJ3BCsw2P0p/We" crossorigin="anonymous"><?php break;
    default:?><link href="https://www.nouveauelevator.com/vendor/bootstrap/css/bootstrap.css?v=3" rel="stylesheet"><?php break;
    break;
  }
} else {
  require( bin_library . 'bootstrap/index.php' );
}
IF( file_exists( bin_css . 'page/' . substr( basename( $_SERVER['SCRIPT_NAME'] ), 0, strlen( basename( $_SERVER['SCRIPT_NAME'] ) ) - 4 ) . '.css') ){
  ?><link rel='stylesheet' href='<?php echo ( 'bin/css/page/' .  substr( basename( $_SERVER['SCRIPT_NAME'] ), 0, strlen( basename( $_SERVER['SCRIPT_NAME'] ) ) - 4 ) . '.css' );?>?v=<?php echo rand(1000,999999999);?>'><?php
} else {
  switch( $_SERVER[ 'SCRIPT_NAME' ] ){ 
    case '/portal/ticket.php' : 
          ?><link href='https://www.nouveauelevator.com/portal/bin/css/page/ticket.css?v=<?php echo rand(1000, 999999999);?>' rel='stylesheet'><?php
          break;
        case ( preg_match('/\/portal\/bin\/php\/element\/ticket\//', $_SERVER[ 'SCRIPT_NAME' ] ) ? true : false ) : 
          ?><link href='https://www.nouveauelevator.com/portal/bin/css/page/ticket.css?v=<?php echo rand(1000, 999999999);?>' rel='stylesheet'><?php
          break;
        default:
          break;
    }
}
if( !isset( $_GET[ 'JQUERY_UI' ]) || $_GET[ 'JQUERY_UI' ]  == 1 ){?><link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css"><?php }?>
<link href="bin/css/index.css?v=<?php echo rand(1000,999999999);?>" rel="stylesheet">
<link href="bin/css/table.css?v=<?php echo rand(1000,999999999);?>" rel="stylesheet">
<link href="bin/css/card.css?v=<?php echo rand(1000,999999999);?>" rel="stylesheet">
<link href="bin/css/animation.css?v=<?php echo rand(1000,999999999);?>" rel="stylesheet">
<link href="bin/css/print.css?v=<?php echo rand(1000,999999999);?>" rel="stylesheet">
<link href="bin/css/class.css?v=<?php echo rand(1000,999999999);?>" rel="stylesheet">
<link href="bin/css/wrapper.css?v=<?php echo rand(1000,999999999);?>" rel="stylesheet">
<link href="bin/css/gui.css?v=<?php echo rand(1000,999999999);?>" rel="stylesheet">
<link href="bin/css/navbar.css?v=<?php echo rand(1000,999999999);?>" rel="stylesheet">
<link href="bin/css/popup.css?v=<?php echo rand(1000,999999999);?>" rel="stylesheet">
<!--<link href="bin/css/datepicker.css?v=<?php echo rand(1000,999999999);?>" rel="stylesheet">-->
<link href="bin/css/color.css?v=<?php echo rand(1000,999999999);?>" rel="stylesheet">
<link href="bin/css/scrollbar.css?v=<?php echo rand(1000,999999999);?>" rel="stylesheet">
<link href="bin/css/map.css?v=<?php echo rand(1000,999999999);?>" rel="stylesheet">
<link href="bin/css/input.css?v=<?php echo rand(1000,999999999);?>" rel="stylesheet">
<?php 
if( isset( $_GET[ 'Entity_CSS' ] ) ){
  ?><link href="bin/css/entity.css?v=<?php echo rand(1000,999999999);?>" rel="stylesheet"><?php
}?>
<style>
/*Fonts*/
@font-face {
  font-family: 'BankGothic';
  src: url('bin/css/font/bankgothic-md-bt-medium-webfont.eot');
  src: url('bin/css/font/bank-gothic-md-bt-medium-1361510860.ttf')  format('truetype');
}
.BankGothic { font-family:'BankGothic'; }
</style>