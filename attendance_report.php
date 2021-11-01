<?php
session_start();
require('cgi-bin/php/index.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
        $r= sqlsrv_query($NEI,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID= ?",array($_SESSION['User']));
        $My_User = sqlsrv_fetch_array($r);
        $Field = ($My_User['Field'] == 1 && $My_User['Title'] != 'OFFICE') ? True : False;
        $r = sqlsrv_query($Portal,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Time']) && $My_Privileges['Time']['User_Privilege'] >= 4 && $My_Privileges['Time']['Group_Privilege'] >= 4 && $My_Privileges['Time']['Other_Privilege'] >= 4){
        	$Privileged = TRUE;
		    }
    }
    //
    if(!isset($array['ID']) || !$Privileged){require('401.html');}
    else {?>
<!DOCTYPE html>
<html lang="en"style="min-height:100%;height:100%;webkit-background-size: cover;-moz-background-size: cover;-o-background-size: cover;background-size: cover;height:100%;">
<head>
    <?php require(PROJECT_ROOT.'php/meta.php');?>
	<title>Nouveau Illinois Portal</title>
    <?php require(PROJECT_ROOT."css/index.php");?>
    <?php require(PROJECT_ROOT.'js/index.php');?>
    <link rel='stylesheet' href='cgi-bin/libraries/timepicker/jquery.timepicker.min.css' />
    <script src='cgi-bin/libraries/timepicker/jquery.timepicker.min.js'></script>
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
<body onload='finishLoadingPage();' style="min-height:100%;background-size:cover;background-color:#1d1d1d;height:100%;">
<div id='container' style='min-height:100%;height:100%;'>
  <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>" style='height:100%;'>
    <?php require(PROJECT_ROOT.'php/element/navigation/index2.php');?>
    <?php require(PROJECT_ROOT.'php/element/loading.php');?>
    <div id="page-wrapper" class='content'>
      <div class='panel panel-primary'>
        <div class='panel-heading'>Attendance Report</div>
        <div class='panel-body'>
          <div class='row'>
            <div class='col-xs-1'>Supervisor:</div>
            <div class='col-xs-11'><select name='Supervisor' style='color:black !important;' onChange='refresh();'><option value='' style='color:black;'>Select</option>
              <?php
                $r = sqlsrv_query($NEI,"SELECT tblWork.Super FROM nei.dbo.tblWork WHERE tblWork.Super <> '' GROUP BY tblWork.Super ORDER BY tblWork.Super ASC ;");
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
<!-- Bootstrap Core JavaScript -->
<script src="../vendor/bootstrap/js/bootstrap.min.js"></script>

<!-- Metis Menu Plugin JavaScript -->
<script src="../vendor/metisMenu/metisMenu.js"></script>

<?php require(PROJECT_ROOT.'js/datatables.php');?>
<script src="cgi-bin/js/jquery.dataTables.yadcf.js"></script>
<!-- Custom Theme JavaScript -->
<script src="../dist/js/sb-admin-2.js"></script>

<!-- JQUERY UI Javascript -->
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script>
var Attendance_Report = null;
$(document).ready(function(){
  Attendance_Report = $('#Table_Attendance_Report').DataTable( {
    "ajax": {
     "url": "cgi-bin/php/reports/Attendance_Report.php",
     "data": function ( d ) {
        return $.extend( {}, d, {
          "Supervisor": $("select[name='Supervisor']").val()
        } );
       }
     },
    "processing":true,
    "serverSide":true,
    "columns": [
      {
        data:"First_Name",
        width:"200px"
      },
      {
        data:"Last_Name",
        width:"200px"
      },
      {
        data:"Clock_In",
        width:"200px"
      },
      {
        data:"Clock_Out",
        width:"200px"
      },
      {
        data:"Start_Notes"
      }
    ],
    "buttons":[
      {
        extend: 'collection',
        text: 'Export',
        buttons: [
          'copy',
          'excel',
          'csv',
          'pdf',
          'print'
        ]
      }
    ],
    "language":{
      "loadingRecords":"<div style='text-align:center;'><div class='sk-cube-grid' style='display:inline-block;position:relative;';><div class='sk-cube sk-cube1' style='background-color:#cc0000'></div><div class='sk-cube sk-cube2' style='background-color:#cc0000'></div><div class='sk-cube sk-cube3' style='background-color:#cc0000'></div><div class='sk-cube sk-cube4' style='background-color:#cc0000'></div><div class='sk-cube sk-cube5' style='background-color:#cc0000'></div><div class='sk-cube sk-cube6' style='background-color:#cc0000'></div><div class='sk-cube sk-cube7' style='background-color:#cc0000'></div><div class='sk-cube sk-cube8' style='background-color:#cc0000'></div><div class='sk-cube sk-cube9' style='background-color:#cc0000'></div></div><div class='sk-cube-grid' style='display:inline-block;position:relative;top:-45px;'><div class='sk-cube sk-cube1' style='background-color:#00007f'></div><div class='sk-cube sk-cube2' style='background-color:#00007f'></div><div class='sk-cube sk-cube3' style='background-color:#00007f'></div><div class='sk-cube sk-cube4' style='background-color:#00007f'></div><div class='sk-cube sk-cube5' style='background-color:#00007f'></div><div class='sk-cube sk-cube6' style='background-color:#00007f'></div><div class='sk-cube sk-cube7' style='background-color:#00007f'></div><div class='sk-cube sk-cube8' style='background-color:#00007f'></div><div class='sk-cube sk-cube9' style='background-color:#00007f'></div></div><div class='sk-cube-grid' style='display:inline-block;position:relative;top:-84px;'><div class='sk-cube sk-cube1' style='background-color:gold'></div><div class='sk-cube sk-cube2' style='background-color:gold'></div><div class='sk-cube sk-cube3' style='background-color:gold'></div><div class='sk-cube sk-cube4' style='background-color:gold'></div><div class='sk-cube sk-cube5' style='background-color:gold'></div><div class='sk-cube sk-cube6' style='background-color:gold'></div><div class='sk-cube sk-cube7' style='background-color:gold'></div><div class='sk-cube sk-cube8' style='background-color:gold'></div><div class='sk-cube sk-cube9' style='background-color:gold'></div></div></div><div style='font-size:72px;text-align:center;' class='BankGothic'>Nouveau Texas</div><div style='font-size:42px;text-align:center;'><i>Raising Your Life</i></div>"
    },
    "paging":true,
    "initComplete":function(){},
    "scrollY" : "600px",
    "scrollCollapse":true,
    //"lengthChange": false,
    "order": [[ 1, "ASC" ]]
  } );
});
function refresh(){
  Attendance_Report.draw();
}
</script>
</body>
</html>
<?php }
}?>
