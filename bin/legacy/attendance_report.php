<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [
        'read_and_close' => true
    ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
if( isset( $_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
  $result = \singleton\database::getInstance()->query((
    null,
    " SELECT  *
      FROM    Connection
      WHERE       Connection.Connector = ?
              AND Connection.Hash  = ?;",
    array(
      $_SESSION[ 'User' ],
      $_SESSION[ 'Hash' ]
    )
  );
  $Connection = sqlsrv_fetch_array( $result );
  //User
  $result = sqlsrv_query(
    null,
    " SELECT  *,
              Emp.fFirst AS First_Name,
              Emp.Last   AS Last_Name
      FROM    Emp
      WHERE   Emp.ID = ?;",
    array(
      $_SESSION[ 'User' ]
    )
  );
  $User = sqlsrv_fetch_array( $result );
  //Privileges
  $result = \singleton\database::getInstance()->query((
    null,
    " SELECT  Privilege.Access_Table,
              Privilege.User_Privilege,
              Privilege.Group_Privilege,
              Privilege.Other_Privilege
      FROM    Privilege
      WHERE   Privilege.User_ID = ?;",
    array(
      $_SESSION[ 'User' ]
    )
  );
  $Privileges = array();
  if( $result ){while( $Privilege = sqlsrv_fetch_array( $result ) ){ $Privileges[ $Privilege[ 'Access_Table' ] ] = $Privilege; } }
  if(     !isset( $Connection[ 'ID' ] )
      ||  !isset($Privileges[ 'Attendance' ])
      ||  $Privileges[ 'Attendance' ][ 'User_Privilege' ]  < 4
      ||  $Privileges[ 'Attendance' ][ 'Group_Privilege' ] < 4
      ||  $Privileges[ 'Attendance' ][ 'Other_Privilege' ] < 4
  ){
      ?><?php require( '../404.html' );?><?php
  } else {
    \singleton\database::getInstance()->query((
      $NEI,
      " INSERT INTO Activity( [User], [Date], [Page] )
        VALUES( ?, ?, ? );",
      array(
        $_SESSION[ 'User' ],
        date( 'Y-m-d H:i:s' ),
        'Attendance_Report.php'
      )
    ); ?>
<!DOCTYPE html>
<html lang="en"style="min-height:100%;height:100%;webkit-background-size: cover;-moz-background-size: cover;-o-background-size: cover;background-size: cover;height:100%;">
<head>
    <?php require( bin_meta . 'index.php');?>
	<title>Nouveau Illinois Portal</title>
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>
    <link rel='stylesheet' href='bin/libraries/timepicker/jquery.timepicker.min.css' />
    <script src='bin/libraries/timepicker/jquery.timepicker.min.js'></script>
</head>
<body onload='finishLoadingPage();' style="min-height:100%;background-size:cover;background-color:#1d1d1d;height:100%;">
<div id='container' style='min-height:100%;height:100%;'>
  <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>" style='height:100%;'>
    <?php require( bin_php . 'element/navigation.php');?>
    <?php require( bin_php . 'element/loading.php');?>
    <div id="page-wrapper" class='content'>
      <div class='panel panel-primary'>
        <div class='panel-heading'>Attendance Report</div>
        <div class='panel-body'>
          <div class='row'>
            <div class='col-xs-1'>Supervisor:</div>
            <div class='col-xs-11'><select name='Supervisor' style='color:black !important;' onChange='refresh();'><option value='' style='color:black;'>Select</option>
              <?php
                $r = $database->query(null,"SELECT tblWork.Super FROM nei.dbo.tblWork WHERE tblWork.Super <> '' GROUP BY tblWork.Super ORDER BY tblWork.Super ASC ;");
                if($r){while($row = sqlsrv_fetch_array($r)){?><option style='color:black !important;' value='<?php echo $row['Super'];?>' <?php echo isset($_GET['Supervisor']) && $row['Super'] == $_GET['Supervisor']  && $_GET['Supervisor'] != '' ? 'selected' : '';?>><?php echo $row['Super'];?></option><?php }}?>
            </select></div>
          </div>
        </div>
        <div class='panel-body'>
          <table id='Table_Attendance_Report' class='display' cellspacing='0' width='100%' style='font-size:12px;'>
            <thead>
              <th>First Name</th>
              <th>Last Name</th>
              <th>Clock In</th>
              <th>Clock Out</th>
              <th>Notes</th>
            </thead>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
<?php }
}?>
