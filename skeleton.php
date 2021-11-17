<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
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
    	"	SELECT 	  Access,
        			    Owner,
        			    Group,
        			    Other
        	FROM   	Privilege
        	WHERE  	User_ID = ?;",
        array(
        	$_SESSION[ 'User' ]
        )
    );
    $Privileges = array();
    while($array2 = sqlsrv_fetch_array($result)){$Privileges[$array2['Access']] = $array2;}
    $Privileged = FALSE;
    if(isset($Privileges[ 'Object_Name' ] )
        && $Privileges[ 'Object_Name' ][ 'Owner' ] >= 4
        && $Privileges[ 'Object_Name' ][ 'Group' ] >= 4
        && $Privileges[ 'Object_Name' ][ 'Other' ] >= 4){$Privileged = TRUE;}
    if(		!isset($Connection[ 'ID' ])
    	|| 	!$Privileged
    ){ require( '401.php' ); }
    else {
    	$result = \singleton\database::getInstance( )->query(
    		null,
    		"",//REPLACE SQL HERE
    		array( )
    	)
    	$Object_Name = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC );
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
        			<div class='card-heading'><h4><a href='object_name.php?ID=<?php echo $_GET[ 'ID' ];?>'><?php \singleton\fontawesome::getInstance( )->Object_Name( );?> Object_Name : <?php echo $Object_Name[ 'Name' ];?></a></h4></div>
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
        					<div class='col-8'><?php echo $Object_Name[ 'ID' ];?></div>
        				</div>
        				<div class='row g-0'>
        					<div class='col-4'><?php \singleton\fontawesome::getInstance( )->Name( 1 );?> Name:</div>
        					<div class='col-8'><?php echo $Object_Name[ 'Name' ];?></div>
        				</div>
        				<!--
							ADD OTHER INFOMATION HERE
        				-->

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
