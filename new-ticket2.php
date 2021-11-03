<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( ); 
    require( '/var/www/portal.live.local/html/cgi-bin/php/index.php' );
}
if( isset( $_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
  //Connection
  $result = sqlsrv_query(
    $NEI,
    " SELECT  Connection.* 
      FROM    Connection 
      WHERE       Connection.Connector = ? 
              AND Connection.Hash = ?;",
    array(
      $_SESSION[ 'User' ],
      $_SESSION[ 'Hash' ]
    )
  );
  $Connection = sqlsrv_fetch_array( $result );
  //User
  $result = sqlsrv_query(
    $NEI,
    " SELECT  Emp.*, 
              Emp.fFirst AS First_Name, 
              Emp.Last as Last_Name 
      FROM    Emp 
      WHERE   Emp.ID = ?;",
    array(
      $_SESSION[ 'User' ]
    )
  );
  $User = sqlsrv_fetch_array( $result );
  //Privileges
  $Privileges = array( );
  $Privileged = false;
  $result = sqlsrv_query(
    $NEI,
    " SELECT  Privilege.Access_Table, 
              Privilege.User_Privilege, 
              Privilege.Group_Privilege, 
              Privilege.Other_Privilege 
      FROM    Portal.dbo.Privilege 
      WHERE   Privilege.User_ID = ?;",
    array(
      $_SESSION[ 'User' ]
    )
  );
  if( $result ){ while( $Privilege = sqlsrv_fetch_array( $result ) ){ $Privileges[ $Privilege[ 'Access_Table' ] ] = $Privilege; } }
  
  if( isset( $Privileges[ 'Ticket' ] ) && $Privileges[ 'Ticket' ][ 'User_Privilege' ] >= 6){ $Privileged = TRUE; }
  if( !isset($Connection['ID'])  || !$Privileged ){require("401.html");}
  else {
    sqlsrv_query($NEI,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "ticket.php?ID=New"));
?><!DOCTYPE html>
<html lang="en">
<head>
  <title>Nouveau Elevator | Portal</title>
  <?php require( bin_meta . 'index.php' );?>
  <?php $_GET[ 'Bootstrap' ] = '5.1';?>
  <?php require( bin_css  . 'index.php' );?>
  <?php require( bin_js   . 'index.php' ); ?>
</head>
<body onload='finishLoadingPage();'>
  <div id='wrapper'>
    <?php require( 'cgi-bin/php/element/navigation/index.php'); ?>
    <?php require( 'cgi-bin/php/element/loading.php'); ?>
    <div id='page-wrapper' class='content' >
      <div class='row'>
        <div class='offset-md-3 col-md-6 panel panel-primary panel-sync'><form id='Ticket' action='new-ticket.php' method='POST'>
          <div class='panel-heading' onClick='document.location.href="work.php";'><h4><?php $Icons->Ticket( );?> Ticket Creation</h4></div>
          <div class='panel-body'>
            <div class='row g-0'>
              <div class='col-lg-12 col-xl-6'>
                <div class='row form-group g-0'><div class='col-sm-12'>&nbsp;</div></div>
                <div class='row form-group g-0'>
                  <label class='col-auto border-bottom v1'><?php $Icons->User(1);?> Worker:</label>
                  <div class='col-auto padding v1'><input type='text' disabled value='<?php echo $User['First_Name'] . " " . $User['Last_Name'];?>' /></div>
                </div>
                <div class='row form-group g-0'>
                  <label class='col-auto border-bottom v1'><?php $Icons->Calendar(1);?> Date:</label>
                  <div class='col-auto padding v1'><input name='Date' value='<?php echo isset($_GET['Date']) ? $_GET['Date'] : date('m/d/Y');?>'/></div>
                </div>
                <div class='row form-group g-0'>
                  <label class='col-auto border-bottom v1'><?php $Icons->Location(1);?> Location:</label>
                  <div class='col-auto padding v1'><button type='button' onClick='selectLocations(this);'><?php
                  $pass = false; 
                  if(isset($_GET['Location']) && is_numeric($_GET['Location'])){
                    $r = sqlsrv_query($NEI,"SELECT * FROM Loc WHERE Loc.Loc = ?;",array($_GET['Location']));
                    if($r){
                      $row = sqlsrv_fetch_array($r);
                      if(is_array($row)){
                        $pass = True;
                        echo $row['Tag'];
                      }
                    }
                  }
                  if(!$pass){?>Select Location<?php }?></button></div>
                 
                </div>
                <div class='row form-group g-0'>
                  <label class='col-auto border-bottom v1'><?php $Icons->Unit(1);?> Unit:</label>
                  <div class='col-auto padding v1'><button type='button' onClick='selectUnits(this);'><?php
                  $pass = false;
                  if(isset($_GET['Unit']) && is_numeric($_GET['Unit'])){
                    $r = sqlsrv_query($NEI,"SELECT * FROM Elev WHERE Elev.ID = ?;",array($_GET['Unit']));
                    if($r){
                      $row = sqlsrv_fetch_array($r);
                      if(is_array($row)){
                        $pass = True;
                        echo isset($row['State']) && strlen($row['State']) > 0 ? $row['State'] . ' - ' . $row['Unit'] : $row['Unit'];
                      }
                    }
                  }
                  if(!$pass){?>Select Unit<?php }?></button></div>
                 
                </div>
                <div class='row form-group g-0'>
                  <label class='col-auto border-bottom v1'><?php $Icons->Job(1);?> Job:</label>
                  <div class='col-auto padding v1'><button type='button' onClick='selectJobs(this);'><?php
                  $pass = false;
                  if(isset($_GET['Job']) && is_numeric($_GET['Job'])){
                    $r = sqlsrv_query($NEI,"SELECT * FROM Job WHERE Job.ID = ?;",array($_GET['Job']));
                    if($r){
                      $row = sqlsrv_fetch_array($r);
                      if(is_array($row)){
                        $pass = True;
                        echo $row['fDesc'];
                      }
                    }
                  }
                  if(!$pass){?>Select Job<?php }?></button></div>
                </div>
                <div class='row form-group g-0'>
                  <label class='col-auto border-bottom v1'><?php $Icons->Blank(1);?> Level:</label>
                  <div class='col-auto padding v1'><select style='width:100%;' name='Level'>
                    <option value=''>Select</option>
                    <option value='1'>Service Call</option>
                    <option value='2'>Trucking</option>
                    <option value='3'>Modernization</option>
                    <option value='4'>Violations</option>
                    <option value='5'>Door Lock Monitoring</option>
                    <option value='6'>Repair</option>
                    <option value='7'>Annual Test</option>
                    <option value='10'>Preventative Maintenance</option>
                    <option value='11'>Survey</option>
                    <option value='12'>Engineering</option>
                    <option value='13'>Support</option>
                    <option value='14'>M&R</option>'
                  </select></div>
                </div>
              </div>
              <div class='col-md-12'>
                <div class='row form-group g-0'>
                  <label class='col-auto border-bottom v1'><?php $Icons->Description(1);?> Description:</label>
                  <textarea class='col-sm-12' rows='8' name='Description'></textarea>
                </div>
              </div>
            </div>
            <div class='row'><div class='col-sm-12'>&nbsp;</div></div>
            <div class='row form-group g-0'>
              <div class='col-sm-12'><button onClick='saveTicket(this);' style='width:100%;height:35px;max-width:100%;'>Save</button></div>
            </div>
          </div>
        </form></div>
      </div>
  </div>
</body>
</html>
<?php }
}?>
