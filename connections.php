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
        ||  !isset( $Privileges[ 'Connection' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'Connection' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        \singleton\database::getInstance( )->query(
          null,
          " INSERT INTO Activity([User], [Date], [Page] )
            VALUES( ?, ?, ? );",
          array(
            $_SESSION[ 'Connection' ][ 'User' ],
            date('Y-m-d H:i:s'),
            'connections.php'
        )
      );
      $result = $database->query(null,
    "SELECT TOP 1
        Job.ID               AS ID,
        Job.fDesc            AS Name,
        JobType.Type         AS Type,
        Loc.Loc              AS Location_ID,
        Loc.ID               AS Location_Name,
        Loc.Tag              AS Location_Tag,
        Loc.Address          AS Street,
        Loc.City             AS City,
        Loc.State            AS State,
        Loc.Zip              AS Zip,
        Loc.Route            AS Route,
        Zone.Name            AS Zone,
        OwnerWithRol.Name    AS Customer,
        OwnerWithRol.Status  AS Customer_Status,
        OwnerWithRol.Elevs   AS Customer_Elevators,
        OwnerWithRol.Address AS Customer_Street,
        OwnerWithRol.City    AS Customer_City,
        OwnerWithRol.State   AS Customer_State,
        OwnerWithRol.Zip     AS Customer_Zip,
        Elev.ID              AS Unit_ID,
        Elev.Unit            AS Unit_Label,
        Elev.State           AS Unit_State,
        Elev.Cat             AS Unit_Category,
        Elev.Type            AS Unit_Type,
        Emp.fFirst           AS Mechanic_First_Name,
        Emp.Last             AS Mechanic_Last_Name,
        Route.ID             AS Route_ID
    FROM
        ((((((Job
        LEFT JOIN nei.dbo.Loc          ON Job.Loc   = Loc.Loc)
        LEFT JOIN nei.dbo.Zone         ON Loc.Zone  = Zone.ID)
        LEFT JOIN nei.dbo.JobType      ON Job.Type  = JobType.ID)
        LEFT JOIN nei.dbo.OwnerWithRol ON Job.Owner = OwnerWithRol.ID)
        LEFT JOIN nei.dbo.Elev         ON Job.Elev  = Elev.ID)
        LEFT JOIN nei.dbo.Route        ON Loc.Route = Route.ID)
        LEFT JOIN Emp          ON Emp.fWork = Route.Mech
    WHERE
        Job.ID = ?
;",array($_GET['ID']));
$Job = sqlsrv_fetch_array($result);
?><!DOCTYPE html>
<html lang="en">
<head>
    <?php require( bin_meta . 'index.php');?>
    <title>Nouveau Texas | Portal</title>
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>
</head>
<body onload=''>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>" style='margin:0px !important;;'>
        <?php require( bin_php . 'element/navigation.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content' style='margin:0px !important; padding-top:50px !important'>
            <script>
                function grantField(){$.post('php/post/grantMassField.php', {}).done(function (data) {});}
                function grantOffice(){$.post('php/post/grantMassOffice.php', {}).done(function (data) {});}
                function grantDispatch(){$.post('php/post/grantMassDispatch.php',{}).done(function(data){});}
            </script>
            <div class="row">
                <div class="col-lg-12" style='padding:0px;'>
                    <div class="panel panel-primary">
                        <div class="panel-heading" style='background-color:whitesmoke;color:black;'><h3><?php \singleton\fontawesome::getInstance( )->Connection();?>Connections</h3></div>
                        <div class="panel-body">
                            <table id='Table_Connections' class='display' cellspacing='0' width='100%'>
                                <thead>
                                    <th>User ID</th>
                                    <th>ID</th>
                                    <th>Last Name</th>
                                    <th>First Name</th>
                                    <th>Time</th>
                                </thead>
                               <tfooter><th>ID</th><th>Last Name</th><th>First Name</th><th>Time</th></tfooter>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <?php require('bin/js/datatables.php');?>


    <script>
        $(document).ready(function() {
            var Table_Connections = $('#Table_Connections').DataTable( {
                "ajax": {
                    "url":"php/get/Connections.php",
                    "dataSrc":function(json){if(!json.data){json.data = [];}return json.data;}
                },
                "columns": [
                    { "data": "User_ID"},
                    { "data": "ID"},
                    { "data": "Last_Name"},
                    { "data": "First_Name"},
                    { "data": "TimeStamped"}
                ],
                "order": [[1, 'asc']],
                "language":{
                    "loadingRecords":""
                },
                "lengthMenu":[[10,25,50,100,500,-1],[10,25,50,100,500,"All"]],
                "initComplete":function(){
                    $("tr[role='row']>th:nth-child(5)").click().click();
                    hrefConnections();
                    finishLoadingPage();
                }

            } );

        } );
        function hrefConnections(){$("#Table_Connections tbody tr").each(function(){$(this).on('click',function(){document.location.href="connection.php?ID=" + $(this).children(":first-child").html();});});}
    </script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=job<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
