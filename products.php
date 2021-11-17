<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
if( isset( $_SESSION[ 'Connection' ][ 'User' ], $_SESSION[ 'Connection' ][ 'Hash' ] ) ){
  //Connection
    $result = \singleton\database::getInstance( )->query(
      'Portal',
      " SELECT  [Connection].[ID]
        FROM    dbo.[Connection]
        WHERE       [Connection].[User] = ?
                AND [Connection].[Hash] = ?;",
      array(
        $_SESSION[ 'Connection' ][ 'User' ],
        $_SESSION[ 'Connection' ][ 'Hash' ]
      )
    );
    $Connection = sqlsrv_fetch_array($result);
    //User
	$result = \singleton\database::getInstance( )->query(
		null,
		" SELECT  Emp.fFirst  AS First_Name,
		          Emp.Last    AS Last_Name,
		          Emp.fFirst + ' ' + Emp.Last AS Name,
		          Emp.Title AS Title,
		          Emp.Field   AS Field
		  FROM  Emp
		  WHERE   Emp.ID = ?;",
		array(
		  	$_SESSION[ 'Connection' ][ 'User' ]
		)
	);
	$User   = sqlsrv_fetch_array( $result );
	//Privileges
	$Access = 0;
	$Hex = 0;
	$result = \singleton\database::getInstance( )->query(
		'Portal',
		"   SELECT  [Privilege].[Access],
                    [Privilege].[Owner],
                    [Privilege].[Group],
                    [Privilege].[Department],
                    [Privilege].[Database],
                    [Privilege].[Server],
                    [Privilege].[Other],
                    [Privilege].[Token],
                    [Privilege].[Internet]
		  FROM      dbo.[Privilege]
		  WHERE     Privilege.[User] = ?;",
		array(
		  	$_SESSION[ 'Connection' ][ 'User' ],
		)
	);
    $Privileges = array();
    if( $result ){while( $Privilege = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC ) ){

        $key = $Privilege['Access'];
        unset( $Privilege[ 'Access' ] );
        $Privileges[ $key ] = implode( '', array(
        	dechex( $Privilege[ 'Owner' ] ),
        	dechex( $Privilege[ 'Group' ] ),
        	dechex( $Privilege[ 'Department' ] ),
        	dechex( $Privilege[ 'Database' ] ),
        	dechex( $Privilege[ 'Server' ] ),
        	dechex( $Privilege[ 'Other' ] ),
        	dechex( $Privilege[ 'Token' ] ),
        	dechex( $Privilege[ 'Internet' ] )
        ) );
    }}
    if( 	!isset( $Connection[ 'ID' ] )
        ||  !isset( $Privileges[ 'Job' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'Job' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        \singleton\database::getInstance( )->query(
          null,
          " INSERT INTO Activity([User], [Date], [Page] )
            VALUES( ?, ?, ? );",
          array(
            $_SESSION[ 'Connection' ][ 'User' ],
            date('Y-m-d H:i:s'),
            'job.php'
        )
      );
    $Privileges = array();
    if( $result ){while( $Privilege = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC ) ){

        $key = $Privilege['Access'];
        unset( $Privilege[ 'Access' ] );
        $Privileges[ $key ] = implode( '', array(
        	dechex( $Privilege[ 'Owner' ] ),
        	dechex( $Privilege[ 'Group' ] ),
        	dechex( $Privilege[ 'Department' ] ),
        	dechex( $Privilege[ 'Database' ] ),
        	dechex( $Privilege[ 'Server' ] ),
        	dechex( $Privilege[ 'Other' ] ),
        	dechex( $Privilege[ 'Token' ] ),
        	dechex( $Privilege[ 'Internet' ] )
        ) );
    }}
    if( 	!isset( $Connection[ 'ID' ] )
        ||  !isset( $Privileges[ 'Unit' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'Unit' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
    	$result = \singleton\database::getInstance( )->query(
    		null,
    		"",//REPLACE SQL HERE
    		array( )
    	)
    	$Object_Name = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC );
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
				<div class="panel-heading"><h3><?php \singleton\fontawesome::getInstance( )->Unit();?> Products</h3></div>
				<div class="panel-body">
					<table id='Table_Products' class='display' cellspacing='0' width='100%'>
						<thead>
							<th></th>
							<th></th>
							<th></th>
							<th></th>
							<th></th>
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
	var Editor_Products = new $.fn.dataTable.Editor({
		ajax: "php/post/Products.php?ID=<?php echo $_GET['ID'];?>",
		table: "#Table_Products",
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
			name: "Description",
			type:"textarea"
		},{
			label: "Type",
			name: "Type",
			type: "select",
			options: [<?php
				$r = $database->query(null,"
					SELECT Product_Type.ID   AS Value,
						   Product_Type.Name AS Label
					FROM   Portal.dbo.Product_Type
					ORDER BY Product_Type.Name ASC
				;");
				$Types = array();
				//$Types = array("{label:'Uncategorized', value:'1'}");
				if($r){while($Type = sqlsrv_fetch_array($r)){
					$Types[] = '{' . "label: '{$Type['Label']}', value:'{$Type['Value']}'" . '}'
				;}}
				echo implode(",",$Types);
			?>]
		},{
			label:"Manufacturer",
			name:"Manufacturer"
		},{
			label:"Model",
			name:"Model"
		},{
			label:"Model Number",
			name:"Model_Number"
		},{
			label:"Notes",
			name:"Noes",
			type:"textarea"
		}]
	});
	Editor_Products.field('ID').disable();
	var Table_Products = $('#Table_Products').DataTable( {
		"ajax": "bin/php/get/Products.php",
		"columns": [
			{
				"data": "ID",
				"visible":false
			},{
				"data": "Name"
			},{
				"data": "Type",
				render:function(data){
					switch(data){
						<?php
						$r = $database->query(null,"
							SELECT Product_Type.ID   AS ID,
								   Product_Type.Name AS Name
							FROM   Portal.dbo.Product_Type
						;");
						if($r){while($array = sqlsrv_fetch_array($r)){?>case '<?php echo $array['ID'];?>':return '<?php echo $array['Name'];?>';<?php }}?>
					}
				}
			},{
				"data": "Description",
				"visible":false
			},{
				"data": "Manufacturer"
			},{
				"data": "Model"
			},{
				"data": "Model_Number"
			},{
				"data" : "Notes",
				"visible":false
			},{
				"data" : "Verified"
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
				editor: Editor_Products
			},{
				extend: "edit",
				editor: Editor_Products
			},{
				extend: "remove",
				editor: Editor_Products
			},{
				extend: "edit",
				editor:Editor_Products,
				text:"Edit Survey Sheet"
			},{
				text:"View",
			  	action:function(e,dt,node,config){
					var data = Table_Products.rows({selected:true}).data()[0];
				  	document.location.href = 'product.php?ID=' + data.ID;
			  	}
			},{
				text : "Preview",
				action:function(e,dt,node,config){
					$("tr.selected").each(function(){
						var tr = $(this);
						var row = Table_Products.row( tr );

						if ( row.child.isShown() ) {
							row.child.hide();
							tr.removeClass('shown');
						}
						else {
							row.child( format(row.data()) ).show();
							tr.addClass('shown');
						}
					});
				}
			}
		],
		<?php require('bin/js/datatableOptions.php');?>
	} );
	yadcf.init(Table_Units,[
		{   column_number:0,
			filter_type:"auto_complete",
			filter_default_label:"ID"},
		{   column_number:1,
			filter_type:"auto_complete",
			filter_default_label:"State"},
		{   column_number:2,
			filter_type:"auto_complete",
			filter_default_label:"Label"},
		{   column_number:3,
			filter_default_label:"Type"},
		{   column_number:4,
			filter_default_label:"Status",
			filter_match_mode:"exact"},
		{   column_number:5,
			filter_default_label:"Customer"},
		{   column_number:6,
			filter_default_label:"Location"}

	]);
	function format ( d ) {
		return "<div>"+
			"<div>"+
				"<div class='column' style='width:45%;vertical-align:top;'>"+
					"<div><b>Description</b></div>"+
					"<div><pre>"+d.Description+"</div>"+
				"</div>"+
				"<div class='column' style='width:45%;vertical-align:top;'>"+
					"<div><b>Notes</b></div>"+
					"<div><pre>"+d.Notes+"</div>"+
				"</div>"+
			"</div>"+
		'</div>'+
		"<div><a href='product.php?ID="+d.ID+"' target='_blank'>View Product</a></div>"
	}
	</script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=units.php';</script></head></html><?php }?>
