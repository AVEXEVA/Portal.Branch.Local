<?php
$result = \singleton\database::getInstance( )->query(
    null,
    "   SELECT  TicketO.* 
        FROM    TicketO 
                LEFT JOIN Emp ON TicketO.fWork = Emp.fWork 
        WHERE       Emp.ID = ? 
                AND TicketO.High = 1 
                AND TicketO.Assigned < 5 
                AND TicketO.ID NOT IN ( 
                    SELECT  Alert.Ticket 
                    FROM    Portal.dbo.Alert
                );",
    array(
        $_SESSION[ 'Connection' ][ 'User' ]
    )
);
if( $result ){
  $Tickets = array();
  while( $row = sqlsrv_fetch_array( $result ) ){
    $database->query(
        null,
        "   INSERT INTO Alert(Ticket) 
            VALUES(?);",
        array(
            $row['ID']
        )
    );
    $Tickets[] = $row;
  }
  if(count( $Tickets ) > 0){
    if(count( $Tickets ) == 1){
      $row = array_pop( $Tickets );
      ?><div class='panel panel-primary' id='Banner-Alert'>
        <div class='panel-heading' style='background-color:#ffd700 !important;color:black !important;text-align:center;'>
          <div class='row'>
            <div class='col-xs-12'>You have recieved a  <a href='ticket.php?ID=<?php echo $row['ID'];?>' style='text-decoration:underline;'><b>high priorty ticket</b></a></div>
            <div class='col-xs-12' style='height:5px;'>&nbsp;</div>
            <div class='col-xs-6'><button style='width:100%;' onClick="document.location.href='ticket.php?ID=<?php echo $row['ID'];?>';">View</button></div>
            <div class='col-xs-6'><button style='width:100%;' onClick="closeAlert();">Close</button></div>
          </div>
        </div>
      </div><?php
    } else {
      ?><div class='panel panel-primary' id='Banner-Alert'>
        <div class='panel-heading' style='background-color:#ffd700 !important;color:black !important;text-align:center;'>
          <div class='row'>
            <div class='col-xs-12'>You have recieved <a href="document.location.href='work.php';"><b><?php echo count($Tickets);?> high priorty tickets</b></a></div>
            <div class='col-xs-12' style='height:5px;'>&nbsp;</div>
            <div class='col-xs-6'><button style='width:100%;' onClick="document.location.href='work.php';">View</button></div>
            <div class='col-xs-6'><button style='width:100%;' onClick="closeAlert();">Close</button></div>
          </div>
        </div>
      </div><?php
    }
    ?><script> function closeAlert(){ document.getElementById("Banner-Alert").remove(); } </script><?php
  }
}
?>
<nav class="navbar navbar-default navbar-static-top row" role="navigation">
    <div class="navbar-header col-10">
        <a class="navbar-brand BankGothic" href="index.php">
            <img src='https://www.nouveauelevator.com/Images/Icons/logo.png' width='25px' style='padding-right:5px;' align='left' />
           	<span><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?></span>
        </a>
    </div>
	<div class='col-2'>
		<a class='text-white' href='login.php?Logout'><span class='desktop'>Logout</span> <?php \singleton\fontawesome::getInstance( )->Logout( 1 );?></a>
	</div>
</nav>
