    <?php
    session_start( [ 'read_and_close' => true ] );
    require('cgi-bin/php/index.php');
    setlocale(LC_MONETARY, 'en_US');
    if(isset($_SESSION[ 'User' ],$_SESSION[ 'Hash' ])){
          $result = sqlsrv_query($NEI,
          "SELECT *
           FROM Connection
           WHERE Connector = ? AND Hash = ?;",
           array($_SESSION[ 'User' ],$_SESSION[ 'Hash' ])
         );
    $array = sqlsrv_fetch_array($result);
    if(!isset($_SESSION[ 'Branch' ]) || $_SESSION[ 'Branch' ] == 'Nouveau Elevator'){
        $result= sqlsrv_query($NEI,
          "SELECT *, First
           AS First_Name, Last as Last_Name
           FROM Emp
           WHERE ID= ?",array($_SESSION[ 'User' ])
         );
        $User = sqlsrv_fetch_array($result);
        $Field = ($User[ 'Field' ] == 1 && $User[ 'Title' ] != 'OFFICE') ? True : False;
        $result = sqlsrv_query($Portal,
        "   SELECT Access_Table,
                   User_Privilege,
                   Group_Privilege,
                   Other_Privilege
            FROM   Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION[ 'User' ]
      )
    );
        $Privileges = array();
        while($array2 = sqlsrv_fetch_array($result)){$Privileges[$array2[ 'Access_Table' ]] = $array2;}
        $Privileged = FALSE;
        if(isset($Privileges[ 'Time' ]) && $Privileges[ 'Time' ][ 'User_Privilege' ] >= 4 && $Privileges[ 'Time' ][ 'Group_Privilege' ] >= 4 && $Privileges['Time']['Other_Privilege'] >= 4){
        	$Privileged = TRUE;
		   }
    }
        //
        if(!isset(
          $array[ 'ID' ]) || !$Privileged){require( '401.html' );}
        else {
    		sqlsrv_query(
          $Portal,
          "INSERT INTO Activity([User],
           [Date], [Page])
           VALUES(?,?,?);",
         array($_SESSION[ 'User' ],
         date("Y-m-d H:i:s"), "collector.php")
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
        <?php require(PROJECT_ROOT.'php/element/navigation/index2.php');?>
        <?php require(PROJECT_ROOT.'php/element/loading.php');?>
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
                  $result = sqlsrv_query($NEI,
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
                    "url":"cgi-bin/php/get/attendance.php?<?php echo isset($_GET['Supervisor']) ? 'Supervisor=' . $_GET['Supervisor'] : '';?>",
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
    <!-- Bootstrap Core JavaScript -->
    <script src="https://www.nouveauelevator.com/vendor/bootstrap/js/bootstrap.min.js"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="https://www.nouveauelevator.com/vendor/metisMenu/metisMenu.js"></script>

    <?php require(PROJECT_ROOT.'js/datatables.php');?>
    <script src="cgi-bin/js/jquery.dataTables.yadcf.js"></script>
    <!-- Custom Theme JavaScript -->
    <script src="../dist/js/sb-admin-2.js"></script>

    <!--Moment JS Date Formatter-->
    <script src="../dist/js/moment.js"></script>

    <!-- JQUERY UI Javascript -->
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

	<script src="https://www.nouveauelevator.com/vendor/flot/excanvas.min.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/flot/jquery.flot.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/flot/jquery.flot.pie.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/flot/jquery.flot.resize.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/flot/jquery.flot.time.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/flot/jquery.flot.symbol.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/flot/jquery.flot.axislabels.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/flot-tooltip/jquery.flot.tooltip.min.js"></script>
	<style>
    div.column {display:inline-block;vertical-align:top;}
    div.label1 {display:inline-block;font-weight:bold;width:150px;vertical-align:top;}
    div.data {display:inline-block;width:300px;vertical-align:top;}
    div#map * {overflow:visible;}
    </style>
    <script>
	$(document).ready(function(){
		$("a[tab='overview-pills']").click();
	});
	</script>
</body>
</html>
<?php
    }
} else {require("404.html");}?>
