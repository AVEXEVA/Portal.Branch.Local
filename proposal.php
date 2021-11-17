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
        ||  !isset( $Privileges[ 'Proposal' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'Proposal' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        \singleton\database::getInstance( )->query(
          null,
          " INSERT INTO Activity([User], [Date], [Page] )
            VALUES( ?, ?, ? );",
          array(
            $_SESSION[ 'Connection' ][ 'User' ],
            date('Y-m-d H:i:s'),
            'proposal.php'
        )
      );
    if(!isset($array[ 'ID' ]) || !is_numeric($_GET[ 'ID' ])  || !$Privileged){?><html><head><script></script></head></html><?php }
    else {
        $ID = $_GET['ID'];
        $result = \singleton\database::getInstance( )->query(
        	null,
            "   SELECT  TOP 1
                        Estimate.ID             AS  ID,
                        Estimate.Name           AS  Contact,
                        Estimate.fDesc          AS  Title,
                        Estimate.fDate          AS  Date,
                        Estimate.Type           AS  Type,
                        Estimate.Template       AS  Template,
                        EStimate.Remarks        AS  Remarks,
                        Estimate.Cost           AS  Cost,
                        Estimate.Hours          AS  Hours,
                        Estimate.Labor          AS  Labor,
                        Estimate.Overhead       AS  Overhead,
                        Estimate.Price          AS  Price,
                        Estimate.Profit         AS  Profit,
                        Estimate.SubTotal1      AS  SubTotal_1,
                        Estimate.SubTotal2      AS  SubTotal_2,
                        Estimate.Job            AS  Job,
                        Estimate.EstTemplate    AS  EstTemplate,
                        Estimate.STaxRate       AS  Sales_Tax_Rate,
                        Estimate.STax           AS  Sales_Tax,
                        Estimate.SExpense       AS  Sales_Expense,
                        Estimate.Quoted         AS  Quoted,
                        Estimate.Phase          AS  Phase,
                        Estimate.Probability    AS  Porbability,
                        Loc.Address             AS  Street,
                        Loc.State               AS  State,
                        Loc.City                AS  City,
                        Loc.Zip                 AS  Zip,
                        Customer.Name           AS  Customer,
                        Rol.Contact             AS  Contact,
                        Rol.Fax                 AS  Fax,
                        Rol.Phone               AS  Phone,
                        Rol.EMail               AS  Email
                FROM    Estimate
                        LEFT JOIN Loc           ON  Estimate.LocID  = Loc.Loc
                        LEFT JOIN (
                              SELECT  Owner.ID,
                                      Rol.Name,
                                      Owner.Status
                              FROM    Owner
                                      LEFT JOIN Rol ON Owner.Rol = Rol.ID
                          ) AS Customer ON Loc.Owner = Customer.ID
                        LEFT JOIN Rol           ON  Rol.ID          = Estimate.RolID
                WHERE   Estimate.ID = ?;",
            array(
                $_GET[ 'ID' ]
            )
        );
        $Estimate = sqlsrv_fetch_array( $result );
?><!DOCTYPE html>
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
<body onload="finishLoadingPage();">
    <div id="wrapper" class="<?php echo isset($_SESSION[ 'Toggle_Menu' ]) ? $_SESSION[ 'Toggle_Menu' ] : null;?>">
        <?php require(bin_php.'element/navigation.php');?>
        <?php require(bin_php.'element/loading.php');?>
        <div id="page-wrapper" class='content' style='background-color : white !important; color : black !important;'>
            <div class='row'>
                <div class='col-xs-12' style='text-align:center;'>
                    <img src='bin/media/logo/nouveau-no-white.jpg' height='150px' />
                </div>
                <!--<div class='col-xs-12'><h1 style='text-align:center;'><b class='BankGothic' >Nouveau Elevator</b></h1></div>-->
            </div>
            <div class='row' style='border-top:1px solid black;margin-top:5px;margin-bottom:5px;height:5px;'>&nbsp;</div>
            <div class='row'>
                <div class='col-xs-12' style='b'><h3 style='text-align:center;margin:0px;padding:5px;'>Proposal #<?php echo $_GET['ID'];?></h3></div>
            </div>
            <div class='row' style='border-top:1px solid black;margin-top:5px;margin-bottom:5px;height:5px;'>&nbsp;</div>
            <div class='row' style=''>
                <div class='col-xs-4'>OFFICE (718) 349-4700</div>
                <div class='col-xs-4'>FAX (718) 349-8932</div>
                <div class='col-xs-4'>proposal@nouveauelevator.com</div>
            </div>
            <div class='row' style='border-top:1px solid black;margin-top:5px;margin-bottom:5px;height:5px;'>&nbsp;</div>
            <div class='row'>
                <div class='col-xs-2'>ATTN:</div>
                <div class='col-xs-4'><?php echo $Estimate[ 'Contact' ];?></div>
                <div class='col-xs-6'>PROPOSAL #<?php echo $_GET['ID'];?></div>
            </div>
            <div lcass='row'>
                <div class='col-xs-2'>PHONE:</div>
                <div class='col-xs-4'><?php echo $Estimate[ 'Phone' ];?></div>
                <div class='col-xs-6'><?PHP echo date( 'm/d/Y', strtotime( $Estimate[ 'Date' ] ) );?></div>
            </div>
            <div class='row'>
                <div class='col-xs-2'>FAX:</div>
                <div class='col-xs-4'><?php echo $Estimate[ 'Fax' ];?></div>
                <div class='col-xs-6'>&nbsp;</div>
            </div>
            <div class='row'>
                <div class='col-xs-2'>EMAIL:</div>
                <div class='col-xs-4'><?php echo $Estimate[ 'Email' ];?></div>
                <div class='col-xs-6'>&nbsp;</div>
            </div>
            <div class='row'>
                <div class='col-xs-2'>FROM:</div>
                <div class='col-xs-4'><?php /*INSERT FROM HERE*/?></div>
                <div class='col-xs-6'>&nbsp;</div>
            </div>
            <div class='row'>
                <div class='col-xs-2'>PREMISE:</div>
                <div class='col-xs-4'><?php echo $Estimate[ 'Location' ];?></div>
                <div class='col-xs-6'>&nbsp;</div>
            </div>
            <div class='row'>
                <div class='col-xs-2'>CUSTOMER:</div>
                <div class='col-xs-4'><?php echo $Estimate[ 'Customer' ];?></div>
                <div class='col-xs-6'>&nbsp;</div>
            </div>
            <div class='row'>
                <div class='col-xs-2'>RE:</div>
                <div class='col-xs-4'><?php echo $Estimate[ 'Title'];?></div>
                <div class='col-xs-6'>&nbsp;</div>
            </div>
            <div class='row' style='border-top:1px solid black;margin-top:5px;margin-bottom:5px;height:5px;'>&nbsp;</div>
            <div class='row'>
                <div class='col-xs-12' style='text-align:center;'><u>WORK DESCRIPTION</u></div>
            </div>
            <div class='row'>
                <div class='col-xs-12'><pre style='padding:25px;font-size:18px;'><?php echo $Estimate[ 'Remarks' ];?></pre></div>
            </div>
            <div class='row'>
                <div class='col-xs-3'>COST NOT TO EXCEED:</div>
                <div class='col-xs-9'>$<?php echo number_format( $Estimate[ 'Price' ], 2 );?> - PLUS ANY APPlICABLE TAXES</div>
            </div>
            <div class='row' style='border-top:1px solid black;margin-top:5px;margin-bottom:5px;height:5px;'>&nbsp;</div>
            <div class='row'>
                <div class='col-xs-12' style='text-align:center;'>THIS PROPOSAL IS VALID FOR 180 DAYS FROM DATE ABOVE</div>
            </div>
            <div class='row'>
                <div class='col-xs-12' style='text-align:center;'>"IF ADDITIONAL WORK IS NEEDED OTHER THAN STATED YOU WILL BE INFORMED IMMEDIATELY"</div>
            </div>
            <div class='row'>
                <div class='col-xs-12' style='text-align:center;'>"WORK TO BE PERFORMED DURING REGULAR HOURS UNLESS OTHERWISE STATED</div>
            </div>
            <div class='row' style='border-top:1px solid black;margin-top:5px;margin-bottom:5px;height:5px;'>&nbsp;</div>
            <div class='row'>
                <div class='col-xs-12' style='text-align:center;'>AUTHORIZATION TO PROCEED WITH WORK AND TERMS DESCRIBED ABOVE</div>
            </div>
            <div class='row'>
                <div class='col-xs-5' style='text-align:right;'>P.O.#</div>
                <div class='col-xs-4' style='border-bottom:1px solid black;'>&nbsp;</div>
            </div>
            <div class='row'>
                <div class='col-xs-5' style='text-align:right;'>NAME</div>
                <div class='col-xs-4' style='border-bottom:1px solid black;'>&nbsp;</div>
            </div>
            <div class='row'>
                <div class='col-xs-5' style='text-align:right;'>TITLE & DATE</div>
                <div class='col-xs-4' style='border-bottom:1px solid black;'>&nbsp;</div>
            </div>
            <div class='row'>
                <div class='col-xs-5' style='text-align:right;'>AUTHORIZED SIGNATURE</div>
                <div class='col-xs-4' style='border-bottom:1px solid black;'>&nbsp;</div>
            </div>
            <div class='row' style='border-top:1px solid black;margin-top:5px;margin-bottom:5px;height:5px;'>&nbsp;</div>
            <div class='row'>
                <div class='col-xs-12' style='text-align:center;'>PLEASE RETURN SIGNED FORM BY FAX WITH YOUR APPROVAL TO PROCEED AS DESCRIBED</div>
            </div>
        </div>
    </div>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=proposal<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
