<?php
session_start();
require('cgi-bin/php/index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"
		SELECT *
		FROM   Connection
		WHERE  Connection.Connector = ?
		       AND Connection.Hash  = ?
	;",array($_SESSION['User'],$_SESSION['Hash']));
    $My_Connection = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $r = sqlsrv_query($NEI,"
		SELECT *,
		       Emp.fFirst AS First_Name,
			   Emp.Last   AS Last_Name
		FROM   Emp
		WHERE  Emp.ID = ?
	;",array($_SESSION['User']));
    $My_User = sqlsrv_fetch_array($r);
	$r = sqlsrv_query($NEI,"
		SELECT *
		FROM   Privilege
		WHERE  Privilege.User_ID = ?
	;",array($_SESSION['User']));
	$My_Privileges = array();
	if($r){while($My_Privilege = sqlsrv_fetch_array($r)){$My_Privileges[$My_Privilege['Access_Table']] = $My_Privilege;}}
    if(	!isset($My_Connection['ID'])
	   	|| !isset($My_Privileges['Requisition'])
	  		|| $My_Privileges['Requisition']['User_Privilege']  < 4
	  		|| $My_Privileges['Requisition']['Group_Privilege'] < 4){
				?><?php require('../404.html');?><?php }
    else {
		sqlsrv_query($NEI,"
			INSERT INTO Portal.dbo.Activity([User], [Date], [Page])
			VALUES(?,?,?)
		;",array($_SESSION['User'],date("Y-m-d H:i:s"), "units.php"));
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Peter D. Speranza">    <title>Nouveau Texas | Portal</title>
    <?php require(PROJECT_ROOT."css/index.php");?>
    <?php require(PROJECT_ROOT.'js/index.php');?>
</head>
<body onload='finishLoadingPage();' style='background-color:#1d1d1d;'>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require(PROJECT_ROOT.'php/element/navigation/index.php');?>
        <?php require(PROJECT_ROOT.'php/element/loading.php');?>
        <div id="page-wrapper" class='content'>
			<div class="panel panel-primary">
				<div class="panel-heading">
          <div style='float:left;' onClick="document.location.href='home.php';"><?php $Icons->Requisition();?> Requisitions</div>
          <div style='float:right;' onClick="document.location.href='purchase-requisition.php';"><?php $Icons->Add(1);?></div>
          <div style='clear:both;'></div>
        </div>
				<div class="panel-body">
					<table id='Table_Requisitions' class='display' cellspacing='0' width='100%'>
						<thead>
							<th>ID</th>
							<th>User</th>
							<th>Date</th>
							<th>Required</th>
							<th>Location</th>
							<th>Drop Off</th>
							<th>Unit</th>
							<th>Job</th>
						</thead>
					</table>
				</div>
            </div>
        </div>
    </div>
    <Style>
    tr:not([class]) {
      background-color:#1d1d1d !important;
      color:white !important;
    }
    </style>
    <!-- Bootstrap Core JavaScript -->
    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="../vendor/metisMenu/metisMenu.js"></script>

    <?php require(PROJECT_ROOT.'js/datatables.php');?>
    <script src="cgi-bin/js/jquery.dataTables.yadcf.js"></script>
    <!-- Custom Theme JavaScript -->
    <script src="../dist/js/sb-admin-2.js"></script>

    <!--Moment JS Date Formatter-->
    <script src="../dist/js/moment.js"></script>

    <!-- JQUERY UI Javascript -->
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

    <!-- Custom Date Filters-->
    <script src="../dist/js/filters.js"></script>
    <script>
    function format ( d ) {
        return '<table cellpadding="5" cellspacing="0" border="0" style="padding-left:50px;background-color:#1d1d1d;width:100%;"><tbody style="background-color:#1d1d1d;">'+
            '<tr>'+
                '<td style="background-color:#1d1d1d;">ID:</td>'+
                '<td style="background-color:#1d1d1d;">'+d.ID+'</td>'+
            '</tr>'+
            '<tr>'+
                '<td style="background-color:#1d1d1d;">DropOff:</td>'+
                '<td style="background-color:#1d1d1d;">'+d.DropOff+'</td>'+
            '</tr>'+
            '<tr>'+
                '<td style="background-color:#1d1d1d;">Unit:</td>'+
                '<td style="background-color:#1d1d1d;">'+d.Unit+'</td>'+
            '</tr>'+
            '<tr>'+
                '<td style="background-color:#1d1d1d;">Job:</td>'+
                '<td style="background-color:#1d1d1d;">'+d.Job+'</td>'+
            '</tr>'+
            '<tr>'+
                '<td colspan="2"><button style="width:100%;height:42px;" onClick="document.location.href=\'requisition.php?ID='+d.ID+'\';"><?php $Icons->Requisition();?>View Requisition</a></td>'+
            '</tr>'+
        '</tbody></table>';
    }
  $(document).ready(function(){
    	var Table_Requisitions = $('#Table_Requisitions').DataTable( {
    		"ajax": "cgi-bin/php/get/Requisitions.php",
    		"columns": [
    			{
    				"data" : "ID"
            <?php if(isMobile()){?>,"visible":false<?php }?>
    			},{
    				"data" : "User"
            <?php if(isMobile()){?>,"visible":false<?php }?>
    			},{
    				"data" : "Date"
    			},{
    				"data" : "Required"
    			},{
    				"data" : "Location"
    			},{
    				"data" : "DropOff"
            <?php if(isMobile()){?>,"visible":false<?php }?>
    			},{
    				"data" : "Unit"
            <?php if(isMobile()){?>,"visible":false<?php }?>
    			},{
    				"data" : "Job"
            <?php if(isMobile()){?>,"visible":false<?php }?>
    			}
    		],
        "drawCallback": function ( settings ) {
          <?php if(!isMobile()){?>hrefRequisitions(this.api());<?php }?>
        },
        "fixedHeader": {
        	header:true,
        	headerOffset: 55
        },
        "language":{
        	"loadingRecords":"<div style='text-align:center;'><div class='sk-cube-grid' style='display:inline-block;position:relative;';><div class='sk-cube sk-cube1' style='background-color:#cc0000'></div><div class='sk-cube sk-cube2' style='background-color:#cc0000'></div><div class='sk-cube sk-cube3' style='background-color:#cc0000'></div><div class='sk-cube sk-cube4' style='background-color:#cc0000'></div><div class='sk-cube sk-cube5' style='background-color:#cc0000'></div><div class='sk-cube sk-cube6' style='background-color:#cc0000'></div><div class='sk-cube sk-cube7' style='background-color:#cc0000'></div><div class='sk-cube sk-cube8' style='background-color:#cc0000'></div><div class='sk-cube sk-cube9' style='background-color:#cc0000'></div></div><div class='sk-cube-grid' style='display:inline-block;position:relative;top:-45px;'><div class='sk-cube sk-cube1' style='background-color:#00007f'></div><div class='sk-cube sk-cube2' style='background-color:#00007f'></div><div class='sk-cube sk-cube3' style='background-color:#00007f'></div><div class='sk-cube sk-cube4' style='background-color:#00007f'></div><div class='sk-cube sk-cube5' style='background-color:#00007f'></div><div class='sk-cube sk-cube6' style='background-color:#00007f'></div><div class='sk-cube sk-cube7' style='background-color:#00007f'></div><div class='sk-cube sk-cube8' style='background-color:#00007f'></div><div class='sk-cube sk-cube9' style='background-color:#00007f'></div></div><div class='sk-cube-grid' style='display:inline-block;position:relative;top:-84px;'><div class='sk-cube sk-cube1' style='background-color:gold'></div><div class='sk-cube sk-cube2' style='background-color:gold'></div><div class='sk-cube sk-cube3' style='background-color:gold'></div><div class='sk-cube sk-cube4' style='background-color:gold'></div><div class='sk-cube sk-cube5' style='background-color:gold'></div><div class='sk-cube sk-cube6' style='background-color:gold'></div><div class='sk-cube sk-cube7' style='background-color:gold'></div><div class='sk-cube sk-cube8' style='background-color:gold'></div><div class='sk-cube sk-cube9' style='background-color:gold'></div></div></div><div style='font-size:72px;text-align:center;' class='BankGothic'>Nouveau Texas</div><div style='font-size:42px;text-align:center;'><i>Raising Your Life</i></div>"
        },
        "paging":false,
        "select":true,
        "initComplete":function(){
        },
        "scrollY" : "600px",
        "scrollCollapse":true
    	} );
      <?php if(isMobile()){?>
      $('#Table_Requisitions tbody').on('click', 'td', function () {
          var tr = $(this).closest('tr');
          var row = Table_Requisitions.row( tr );

          if ( row.child.isShown() ) {
              row.child.hide();
              tr.removeClass('shown');
          }
          else {
              row.child( format(row.data()) ).show();
              tr.addClass('shown');
          }
      } );
      <?php } else {?>
        function hrefRequisitions(tbl){
          $("table#Table_Requisitions tbody tr").each(function(){
            $(this).on('click',function(){
              document.location.href='requisition.php?ID=' + tbl.row(this).data().ID;
            });
          });
        }
      <?php }?>
    });

  /**/
	</script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=units.php';</script></head></html><?php }?>
