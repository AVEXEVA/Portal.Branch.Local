<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [
        'read_and_close' => true
    ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
if( isset( $_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
  $result = sqlsrv_query(
    $NEI,
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
    $NEI,
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
  $result = sqlsrv_query(
    $NEI,
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
    sqlsrv_query(
      $NEI,
      " INSERT INTO Activity( [User], [Date], [Page] )
        VALUES( ?, ?, ? );",
      array(
        $_SESSION[ 'User' ],
        date( 'Y-m-d H:i:s' ),
        'attendance.php'
      )
    );

?><!DOCTYPE html>
<html lang="en"style="min-height:100%;height:100%;background-image:url('http://www.nouveauelevator.com/Images/Backgrounds/New_York_City_Skyline.jpg');webkit-background-size: cover;-moz-background-size: cover;-o-background-size: cover;background-size: cover;height:100%;">
<head>
    <?php require( bin_meta . 'index.php');?>
	<title>Nouveau Illinois Portal</title>
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>
	<style>
		.panel {background-color:transparent !important;}
		.panel > div.panel-body.white-background {background-color:rgba(255,255,255,.7) !important;}
		.nav-tabs > li:not(.active) {background-color:rgba(255,255,255,.6) !important;}
		.panel-heading {font-family: 'BankGothic' !important;}
		.shadow {box-shadow:0px 5px 5px 0px;}
		<?php if(isMobile()){?>
		.panel-body {padding:0px !important;}
		<?php }?>

			div#wrapper {
				overflow:scroll;
			}
		@media print {
			div#wrapper {overflow:visible;}
		}
	</style>
</head>
<body onload='finishLoadingPage();' style="min-height:100%;background-size:cover;background-color:rgba(255,255,255,.7);height:100%;">
    <div id='container' style='min-height:100%;height:100%;'>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>" style='height:100%;'>
        <?php require( bin_php . 'element/navigation.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content'>
          <div class='panel'>
            <div class='panel-heading'>
              <h4>Attendance</h4>
            </div>
            <div class='panel-body'>
              <div class='row'>
              <form action='#'>
                <!--<div class='col-xs-12'>Start: <input name='Start' value='<?php echo isset($_GET[ 'Start' ]) ? $_GET[ 'Start' ] : '';?>' /></div>
                <div class='col-xs-12'>End: <input name='End'  value='<?php echo isset($_GET[ 'End' ]) ? $_GET[ 'End' ] : '';?>'   /></div>
                <div class='col-xs-12'><input type='submit' value='Search' /></div>-->
                <div class='col-xs-12'>Supervisor: <select name='Supervisor'><?php
                  $result = $database->query(null,
                  "SELECT tblWork.Super
                   FROM nei.dbo.tblWork GROUP BY tblWork.Super ORDER BY tblWork.Super ASC;");
                  if($result){while($resultow = sqlsrv_fetch_array($result)){
                    ?><option value='<?php echo $resultow['Super'];?>'><?php echo $resultow['Super'];?></option><?php
                  }}
                ?></select>
                <div class='col-xs-12'><input type='submit' value='Search' /></div>
              </form>
              </div>
            </div>
            <div class='panel-body'>
              <div class='row'>
              </div>
            </div>
            <div class='panel-body'>
              <table id='attendance' style='width:100%;'>
                <thead><tr>
                  <th>First Name</th>
                  <th>Last Name</th>
                  <th>Start</th>
                  <th>End</th>
                </tr></thead>
              </table>
              <script>
              $(document).ready(function(){
                $("input[name='Start']").datepicker();
                $("input[name='End']").datepicker();
              });
              var table = $('#attendance').DataTable({
                "ajax": {
                    "url":"bin/php/get/attendance.php?<?php echo isset($_GET['Supervisor']) ? 'Supervisor=' . $_GET['Supervisor'] : '';?>",
                    "dataSrc":function(json){if(!json.data){json.data = [];}return json.data;}
                },
                "columns": [
                    { "data": "fFirst"},
                    { "data": "Last"},
                    { "data": "Start"},
                    { "data": "End"}
                ],
                "order": [[1, 'asc']],
                "language":{"loadingRecords":""},
                "pageLength":-1,
                "initComplete":function(){finishLoadingPage();}
              });
              </script>
            </div>
          </div>
        </div>
    </div>
	</div>
</body>
</html>
<?php
    }
} else {require("404.html");}?>
