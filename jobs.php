<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/bin/php/index.php' );
}
if( isset($_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ) {
    $result = \singleton\database::getInstance( )->query(
        null,
        "   SELECT  *
    		FROM    Connection
    		WHERE       Connection.Connector = ?
    		            AND Connection.Hash  = ?;",
        array(
            $_SESSION[ 'User' ],
            $_SESSION[ 'Hash' ]
        )
    );
    $Connection = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);

    //User
    $result = \singleton\database::getInstance( )->query(
          null,
        "   SELECT    *,
    		           Emp.fFirst AS First_Name,
    			         Emp.Last   AS Last_Name
    		    FROM   Emp
    		    WHERE  Emp.ID = ?;",
        array(
            $_SESSION[ 'User' ]
        )
    );
    $User = sqlsrv_fetch_array($result);

    //Privileges
    $result = \singleton\database::getInstance( )->query(
        null,
        "   SELECT  Privilege.Access_Table,
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
	if( $result ){while($Privilege = sqlsrv_fetch_array( $result ) ){$Privileges[$Privilege[ 'Access_Table' ]] = $Privilege;}}
    if(	!isset($Connection[ 'ID' ])
	   	|| !isset($Privileges[ 'Job' ])
	  		|| $Privileges[ 'Job' ][ 'User_Privilege' ]  < 4
	  		|| $Privileges[ 'Job' ][ 'Group_Privilege' ] < 4){
				?><?php require('../404.html');?><?php }
    else {
		\singleton\database::getInstance( )->query(
            null,
            "   INSERT INTO Activity([User], [Date], [Page])
			    VALUES(?, ?, ?);",
            array($_SESSION[ 'User' ],
                date('Y-m-d H:i:s'),
                'jobs.php'
            )
        );
?><!DOCTYPE html>
<html lang='en'>
<head>
	<title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
    <?php $_GET[ 'Bootstrap' ] = '5.1';?>
    <?php require( bin_meta . 'index.php' );?>
    <?php require( bin_css  . 'index.php' );?>
    <?php require( bin_js   . 'index.php' );?>
</head>
<body onload='finishLoadingPage();'>
    <div id='wrapper'>
        <?php require( bin_php . 'element/navigation.php'); ?>
        <?php require( bin_php . 'element/loading.php'); ?>
        <div id='page-wrapper' class='content'>
			<div class='card card-full card-primary'>
				<div class='card-heading'><h4><?php \singleton\fontawesome::getInstance( )->Job( 1 );?> Jobs</h4></div>
				<div class='card-body bg-dark'>
					<table id='Table_Jobs' class='display' cellspacing='0' width='100%'>
						<thead><tr class='text-align:center;'>
                            <th class='text-white border border-white' title='ID'>ID</th>
                            <th class='text-white border border-white' title='Name'>Name</th>
                            <th class='text-white border border-white' title='Customer'>Customer</th>
                            <th class='text-white border border-white' title='Location'>Location</th>
                            <th class='text-white border border-white' title='Type'>Type</th>
                            <th class='text-white border border-white' title='Status'>Status</th>
                        </tr><tr>
                            <th class='text-white border border-white' title='ID'><input class='redraw form-control' type='text' name='ID' placeholder='ID' value='<?php echo isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null;?>' /></th>
                            <th class='text-white border border-white' title='Name'><input class='redraw form-control' text='text'name='Name' placeholder='Name' value='<?php echo isset( $_GET[ 'Name' ] ) ? $_GET[ 'Name' ] : null;?>' /></th>
                            <th class='text-white border border-white' title='Customer'><input class='redraw form-control' text='text'name='Customer' placeholder='Customer' value='<?php echo isset( $_GET[ 'Customer' ] ) ? $_GET[ 'Customer' ] : null;?>' /></th>
                            <th class='text-white border border-white' title='Location'><input class='redraw form-control' text='text'name='Location' placeholder='Location' value='<?php echo isset( $_GET[ 'Location' ] ) ? $_GET[ 'Location' ] : null;?>' /></th>
                            <th class='text-white border border-white' title='Type'><select class='redraw form-control' name='Type'>
                                <option value=''>Select</option>
                                <?php 
                                    $result = \singleton\database::getInstance( )->query( 
                                        null,
                                        "   SELECT  JobType.Type,
                                            FROM    JobType"
                                    );
                                    if( $result ){while ($row = sqlsrv_fetch_array( $result ) ){?><option value='<?php echo $row['ID'];?>'><?php echo $row['Type'];?></option><?php }}
                                ?>  
                            </select></th>
                            <th class='text-white border border-white' title='Status'><input class='form-control' text='text'name='Status' placeholder='Status' value='<?php echo isset( $_GET[ 'Status' ] ) ? $_GET[ 'Status' ] : null;?>' /></th>
                        </tr></thead>
				   </table>
			    </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=jobs.php';</script></head></html><?php }?>
