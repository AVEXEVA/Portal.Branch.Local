<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
if( isset( $_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
	//Connection
    $result = \singleton\database::getInstance( )->query(
      null,
    	"	SELECT 	*
    		FROM 	Connection
    		WHERE 		Connector = ?
    				AND Hash = ?;",
    	array(
    		$_SESSION['User'],
    		$_SESSION['Hash']
    	)
    );
    $Connection = sqlsrv_fetch_array( $result );
    $User = $database->query(null,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID= ?",array($_SESSION['User']));
    $User = sqlsrv_fetch_array($User);
    $r = \singleton\database::getInstance( )->query(
      null,
    	"	SELECT 	Privilege.Access_Table,
    				Privilege.User_Privilege,
    				Privilege.Group_Privilege,
    				Privilege.Other_Privilege
    		FROM   	Privilege
        	WHERE 	Privilege.User_ID = ?;",
        array(
        	$_SESSION[ 'User' ]
        )
    );
    $Privileges   = array();
    while($array2 = sqlsrv_fetch_array($r)){$Privileges[$array2['Access_Table']] = $array2;}
    $Privileged   = FALSE;
    if(isset($Privileges['Ticket'])
    && $Privileges['Ticket']['User_Privilege'] >= 4 
    && $Privileges['Ticket']['Group_Privilege'] >= 4
    && $Privileges['Ticket']['Other_Privilege'] >= 4){$Privileged = TRUE;}

    if(!isset($Connection['ID'])  || !$Privileged){?><html><head><script>document.location.href='../login.php?Forward=users.php';</script></head></html><?php }
    else {
      \singleton\database::getInstance( )->query(
        null,
    	    	"	INSERT INTO Activity( [User], [Date], [Page] )
              VALUES( ?, ?, ? );",
    	    	array(
    	    		$_SESSION[ 'User' ],
    	    		date( 'Y-m-d H:i:s' ),
    	    		'users.php'
    	    	)
    	    );
?><!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
    <?php $_GET[ 'Bootstrap' ] = '5.1';?>
    <?php require( bin_meta .'index.php');?>
    <?php require( bin_css  . 'index.php');?>
    <?php require( bin_js   .'index.php' );?>
</head>
<body onload='finishLoadingPage();'>
    <div id="wrapper">
        <?php require( bin_php . 'element/navigation.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content'>
            <div class="card card-full card-primary border-0">
                <div class="card-heading"><h4><?php \singleton\fontawesome::getInstance( )->Users( 1 );?> Users</h4></div>
                <div class="card-body bg-dark">
                    <table id='Table_Users' class='display' cellspacing='0' width='100%'>
                        <thead><tr>
                            <th class='text-white border border-white'  title='ID'>ID</th>
                            <th class='text-white border border-white'  title='First Name'>First Name</th>
                            <th class='text-white border border-white'  title='Last Name'>Last Name</th>
                        </tr><tr>
                            <th class='text-white border border-white' title='ID'><input class='redraw form-control' type='text' name='ID' value='<?php echo isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null; ?>' /></th>
                            <th class='text-white border border-white' title='First Name'><input class='redraw form-control' type='text' name='First_Name' value='<?php echo isset( $_GET[ 'First_Name' ] ) ? $_GET[ 'First_Name' ] : null; ?>' /></th>
                            <th class='text-white border border-white' title='Last Name'><input class='redraw form-control' type='text' name='Last_Name' value='<?php echo isset( $_GET[ 'Last_Name' ] ) ? $_GET[ 'Last_Name' ] : null; ?>' /></th>
                        </tr></thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php }
}?>
