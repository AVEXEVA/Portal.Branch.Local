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
    if(isset($Privileges[ 'Lead' ] )
        && $Privileges[ 'Lead' ][ 'User_Privilege' ] >= 4
        && $Privileges[ 'Lead' ][ 'Group_Privilege' ] >= 4
        && $Privileges[ 'Lead' ][ 'Other_Privilege' ] >= 4){$Privileged = TRUE;}
    if(		!isset($Connection[ 'ID' ])  
    	|| 	!$Privileged
    ){ require( '401.php' ); } 
    else {
    	$result = \singleton\database::getInstance( )->query(
    		null,
    		"SELECT Lead.ID           AS ID,
				    Lead.fDesc        AS Name,
				    Lead.Address      AS Street,
				    Lead.City         AS City,
				    Lead.State        AS State,
				    Lead.Zip          AS Zip,
				    Customer.ID 	  AS Customer_ID,
				    Customer.Name 	  AS Customer_Name
			FROM     Lead
					 (
                        SELECT  Owner.ID,
                                Rol.Name,
                                Owner.Status 
                        FROM    Owner 
                                LEFT JOIN Rol ON Owner.Rol = Rol.ID
                    ) AS Customer ON Lead.Owner = Customer.ID
			ORDER BY Lead.fDesc ASC",//REPLACE SQL HERE
    		array( )
    	)
    	$Lead = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC );
?>?><!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
    <?php 
    	$_GET[ 'Bootstrap' ] = '5.1';
    	require( bin_meta . 'index.php');
    	require( bin_css  . 'index.php');
    	require( bin_js   . 'index.php');
    ?><style>
    	.link-page {
    		font-size : 14px;
    	}
    </style>
</head>
<body onload='finishLoadingPage();'>
    <div id="wrapper">
        <?php require( bin_php . 'element/navigation.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content'>
        	<div class='card-deck row'>
        		<div clas='card card-primary col-12 border-0'>
        			<div class='card-heading'><h4><a href='lead.php?ID=<?php echo $_GET[ 'ID' ];?>'><?php \singleton\fontawesome::getInstance( )->Lead( );?> Lead : <?php echo $Lead[ 'Name' ];?></a></h4></div>
        			<div class='card-body links-page bg-dark row'>
        				<!--
							ADD LINKS HERE
        				-->
        			</div>
        		</div>
        		<div class='card card-primary col-4 border-0'>
        			<div class='card-heading'><h5>Infomation</h5></div>
        			<div class='card-body'>
        				<div class='row g-0'>
        					<div class='col-4'><?php \singleton\fontawesome::getInstance( )->ID( 1 );?> ID:</div>
        					<div class='col-8'><?php echo $Lead[ 'ID' ];?></div>
        				</div>
        				<div class='row g-0'>
        					<div class='col-4'><?php \singleton\fontawesome::getInstance( )->Name( 1 );?> Name:</div>
        					<div class='col-8'><?php echo $Lead[ 'Name' ];?></div>
        				</div>
        			</div>
        		</div>
        		<div class='card card-primary col-4 border-0'>
        			<div class='card-heading'><h5>Address</h5></div>
        			<div class='card-body'>
        				<div class='row g-0'>
        					<div class='col-4'><?php \singleton\fontawesome::getInstance( )->Address( 1 );?> Street:</div>
        					<div class='col-8'><?php echo $Lead[ 'Street' ];?></div>
        				</div>
        				<div class='row g-0'>
        					<div class='col-4'><?php \singleton\fontawesome::getInstance( )->Blank( 1 );?> City:</div>
        					<div class='col-8'><?php echo $Lead[ 'City' ];?></div>
        				</div>
        				<div class='row g-0'>
        					<div class='col-4'><?php \singleton\fontawesome::getInstance( )->Blank( 1 );?> State:</div>
        					<div class='col-8'><?php echo $Lead[ 'State' ];?></div>
        				</div>
        				<div class='row g-0'>
        					<div class='col-4'><?php \singleton\fontawesome::getInstance( )->Blank( 1 );?> Zip:</div>
        					<div class='col-8'><?php echo $Lead[ 'Zip' ];?></div>
        				</div>
        			</div>
        		</div>
        		<div class='card card-primary col-4 border-0'>
					<div class='card-heading'><?php \singleton\fontawesome::getInstance( )->Customer( 1 );?> Customer</div>
				 	<div class='card-body'>
						<div class='row'>
							<div class='col-4'><?php \singleton\fontawesome::getInstance( )->Customer(1);?> Name:</div>
							<div class='col-8'><a href='customer.php?ID=<?php echo $Lead[ 'Customer_ID' ];?>'><?php echo $Lead['Customer_Name'];?></a></div>
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
}?>

			<div class='panel-body' style='padding:15px;'>
				<div class='col-xs-4' style='text-align:right;'><b>Customer:</b></div>
				<div class='col-xs-8'><?php echo strlen($_GET["Customer"]) > 0 ? proper($_GET['Unit_Type']) : "Unlisted";?></div>
			</div>
		</div>
	</div>
</div>
								
    
    
    <?php require(PROJECT_ROOT.'js/datatables.php');?>
    
    
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=violation<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>