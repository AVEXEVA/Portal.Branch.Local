<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/bin/php/index.php' );
}
if(isset(
  $_SESSION['User'],
  $_SESSION['Hash'] ) ) {
    $r = \singleton\database::getInstance( )->query(
      null,
      " SELECT *
    		FROM       Connection
    		WHERE      Connection.Connector = ?
    		       AND Connection.Hash  = ?
	;",array($_SESSION['User'],$_SESSION['Hash']));
    $Connection = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $r = $database->query(null,
      " SELECT *,
    		       Emp.fFirst AS First_Name,
    			     Emp.Last   AS Last_Name
    		FROM   Emp
    		WHERE  Emp.ID = ?
	;",array($_SESSION['User']));
    $User = sqlsrv_fetch_array($r);
	$r = \singleton\database::getInstance( )->query(
    null,
    " SELECT *
  		FROM   Privilege
  		WHERE  Privilege.User_ID = ?
	;",array($_SESSION['User']));
	$Privileges = array();
	if($r){while($Privilege = sqlsrv_fetch_array($r)){$Privileges[$Privilege['Access_Table']] = $Privilege;}}
    if(	!isset($Connection['ID'])
	   	|| !isset($Privileges['Admin'])
	  		|| $Privileges['Admin']['User_Privilege']  < 4
	  		|| $Privileges['Admin']['Group_Privilege'] < 4){
				?><?php require('../404.html');?><?php }
    else {
      \singleton\database::getInstance( )->query(
        null,
          " INSERT INTO Activity([User], [Date], [Page])
			      VALUES(?,?,?)
		;",array($_SESSION['User'],date("Y-m-d H:i:s"), "units.php"));
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Peter D. Speranza">
    <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>
</head>
<body onload='finishLoadingPage();'>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require( bin_php . 'element/navigation.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content'>
			<div class="panel panel-primary">
				<div class="panel-heading"><h3><?php \singleton\fontawesome::getInstance( )->Unit();?> Product Types</h3></div>
				<div class="panel-body">
					<table id='Table_Product_Types' class='display' cellspacing='0' width='100%'>
						<thead>
							<th></th>
							<th></th>
							<th></th>
							<th></th>
						</thead>
					</table>
				</div>
            </div>
        </div>
    </div>
    <!-- Bootstrap Core JavaScript -->


    <!-- Metis Menu Plugin JavaScript -->


    <?php require(PROJECT_ROOT.'js/datatables.php');?>

    <!-- Custom Theme JavaScript -->


    <!--Moment JS Date Formatter-->


    <!-- JQUERY UI Javascript -->


    <!-- Custom Date Filters-->

    <script>
	var Editor_Product_Types = new $.fn.dataTable.Editor({
		ajax: "php/post/Product_Type.php?ID=<?php echo $_GET['ID'];?>",
		table: "#Table_Product_Types",
		idSrc: "ID",
		formOptions: {
			inline: {
				submit: "allIfChanged"
			}
		},
		fields : [{
			label: "ID",
			name: "ID"
		},{
			label: "Name",
			name: "Name"
		},{
			label: "Description",
			name: "Description"
		},{
			label: "Category",
			name: "Category",
			type: "select",
			options: [<?php
				$r = $database->query(null,"
					SELECT   Product_Type_Category.ID   AS Value,
						     Product_Type_Category.Name AS Label
					FROM     Portal.dbo.Product_Type_Category
					WHERE    Product_Type_Category.ID <> 1
					ORDER BY Product_Type_Category.Name ASC
				;");
				$Types = array();
				$Types = array("{label:'Uncategorized', value:'1'}");
				if($r){while($Type = sqlsrv_fetch_array($r)){
					$Types[] = '{' . "label: '{$Type['Label']}', value:'{$Type['Value']}'" . '}'
				;}}
				echo implode(",",$Types);
			?>]
		}]
	});
	Editor_Product_Types.field('ID').disable();
	var Table_Product_Types = $('#Table_Product_Types').DataTable( {
		"ajax": "bin/php/get/Product_Types.php",
		"columns": [
			{
				"data": "ID",
				"visible":false
			},{
				"data": "Name"
			},{
				"data": "Description"
			},{
				"data": "Category"
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
			},{
				extend: "create",
				editor: Editor_Product_Types
			},{
				extend: "edit",
				editor: Editor_Product_Types
			},{
				extend: "remove",
				editor: Editor_Product_Types
			},{
				extend: "edit",
				editor:Editor_Product_Types,
				text:"Edit Survey Sheet"
			}
		],
		<?php require('bin/js/datatableOptions.php');?>
	} );
	</script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=units.php';</script></head></html><?php }?>
