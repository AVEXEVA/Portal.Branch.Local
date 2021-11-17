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
        ||  !isset( $Privileges[ 'Ticket' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'Ticket' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        \singleton\database::getInstance( )->query(
          null,
          " INSERT INTO Activity([User], [Date], [Page] )
            VALUES( ?, ?, ? );",
          array(
            $_SESSION[ 'Connection' ][ 'User' ],
            date('Y-m-d H:i:s'),
            'support.php'
        )
      );
    ?><!DOCTYPE html>
    <html lang="en">
    <head>
        <?php require( bin_meta . 'index.php');?>
        <title>Nouveau Texas | Portal</title>
        <?php require( bin_css . 'index.php');?>
        <?php require( bin_js . 'index.php');?>
    </head>
    <body onload='finishLoadingPage();'>
        <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
            <?php require( bin_php . 'element/navigation.php');?>
            <?php require( bin_php . 'element/loading.php');?>
            <div id="page-wrapper" class='content'>
        			<div class="panel panel-primary">
        				<div class="panel-heading"><h3><i class="fa fa-question-circle fa-1x fa-fw" aria-hidden="true"></i> Support</h3></div>
        				<div class="panel-body">
        					<table id='Table_Tickets' class='display' cellspacing='0' width='100%'>
        						<thead>
        							<th>ID</th>
        							<th>First Name</th>
        							<th>Last Name</th>
        							<th>Date</th>
        							<th>Status</th>
        							<th>On Site</th>
                      <th>Job</th>
                      <th>Location</th>
                      <th>Division</th>
        						</thead>
        					</table>
        				</div>
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

	<style>
        div.column {display:inline-block;vertical-align:top;}
        div.label1 {display:inline-block;font-weight:bold;width:150px;vertical-align:top;}
        div.data {display:inline-block;width:300px;vertical-align:top;}
    </style>
    <script>
      var Table_Tickets = $('#Table_Tickets').DataTable( {
  			"ajax": {
  				"url":"bin/php/reports/Support.php",
  				"dataSrc":function(json){if(!json.data){json.data = [];}return json.data;}
  			},
  			"lengthMenu":[[10,25,50,100,500,-1,0],[10,25,50,100,500,"All","None"]],
  			"columns": [
  				{ "data": "ID" },
  				{ "data": "Worker_First_Name"},
  				{ "data": "Worker_Last_Name"},
  				{
  					"data": "Date",
  					render: function(data){if(data != null){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}else{return null;}}
  				},
  				{ "data": "Status"},
  				{
  					"data": "On_Site",
  					"defaultContent":"0"
  				}, {
            "data":"Job_Description"
          }, {
            "data":"Location"
          },{
            "data" :"Division"
          }
  			],
  			"order": [[5, 'asc']],
  			"language":{
  				"loadingRecords":""
  			},
  			"initComplete":function(){

  			}
  		} );
      function hrefTickets(){hrefRow("Table_Tickets","ticket");}
		$("Table#Table_Tickets").on("draw.dt",function(){hrefTickets();});
    </script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=units.php';</script></head></html><?php }?>
