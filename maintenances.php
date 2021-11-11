<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/bin/php/index.php' );
}
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION[ 'User' ],$_SESSION[ 'Hash' ] ) ) {
    $result = \singleton\database::getInstance( )->query(
    	null,
    	"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",
    	array(
    		$_SESSION[ 'User' ],
    		$_SESSION[ 'Hash' ]
    	)
    );
    $Connection = sqlsrv_fetch_array($result);
    $User = \singleton\database::getInstance( )->query(
    	null,
    	"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID = ?",
    	array(
    		$_SESSION[ 'User' ]
    	)
    );
    $User = sqlsrv_fetch_array($User);
    $result = \singleton\database::getInstance( )->query(
    	null,
    	"	SELECT 	  Access_Table,
        			    User_Privilege,
        			    Group_Privilege,
        			    Other_Privilege
        	FROM   	Privilege
        	WHERE  	User_ID = ?;",
        array(
        	$_SESSION[ 'User' ]
        )
    );
    $Privileges = array();
    while($array2 = sqlsrv_fetch_array($result)){$Privileges[$array2['Access_Table']] = $array2;}
    $Privileged = FALSE;
    if(isset($Privileges[ 'Maintenance' ] )
        && $Privileges[ 'Maintenance' ][ 'User_Privilege' ] >= 4
        && $Privileges[ 'Maintenance' ][ 'Group_Privilege' ] >= 4
        && $Privileges[ 'Maintenance' ][ 'Other_Privilege' ] >= 4){$Privileged = TRUE;}
    if(		!isset($Connection[ 'ID' ])
    	|| 	!$Privileged
    ){ require( '401.php' ); }
    else {
    	$result = \singleton\database::getInstance( )->query(
    		null,
    		" INSERT INTO Activity([User], [Date], [Page])
  			  VALUES(?,?,?)
    		;"array($_SESSION['User'],
          date  ("Y-m-d H:i:s"),
              "maintenances.php"));
    	$Object_Name = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC );
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Peter D. Speranza">    <title>Nouveau Texas | Portal</title>
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>
</head>
<body>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require( bin_php . 'element/navigation.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content'>
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-primary">
                        <div class="panel-heading"><h3><?php \singleton\fontawesome::getInstance( )->Unit();?>Required Maintenance</h3></div>
                        <div class="panel-body">
                            <table id='Table_Units' class='display' cellspacing='0' width='100%'>
                                <thead>
                                    <th title="Unit's ID">ID</th>
                                    <th title='Unit State ID'>State</th>
                                    <th title="Unit's Label">Unit</th>
                                    <th title="Type of Unit">Type</th>
                                    <th title="Unit's Location">Location</th>
                                    <th>Route</th>
                                    <th>Division</th>
                                    <th>Worked On Last</th>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=units.php';</script></head></html><?php }?>
