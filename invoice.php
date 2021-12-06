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
        ||  !isset( $Privileges[ 'Invoice' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'Invoice' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        \singleton\database::getInstance( )->query(
            null,
            "   INSERT INTO Activity([User], [Date], [Page] )
                VALUES( ?, ?, ? );",
            array(
                $_SESSION[ 'Connection' ][ 'User' ],
                date( 'Y-m-d H:i:s' ),
                'invoice.php'
            )
        );
        $ID = isset( $_GET[ 'ID' ] )
            ? $_GET[ 'ID' ]
            : (
                isset( $_POST[ 'ID' ] )
                    ? $_POST[ 'ID' ]
                    : null
            );
        $result = \singleton\database::getInstance( )->query(
      		null,
            "SELECT TOP 1
                    Invoice.Ref               AS ID,
                    Invoice.fDesc             AS Description,
                    Invoice.fDate             AS Date,
                    Invoice.Amount 	          AS Amount,
                    Invoice.STax              AS Sales_Tax,
                    Invoice.Total             AS Total,
                    Invoice.Taxable           AS Taxable,
                    Customer.ID               AS Customer_ID,
                    Customer.Name             AS Customer_Name,
                    Customer.Street           AS Customer_Street,
                    Customer.City             AS Customer_City,
                    Customer.State            AS Customer_State,
                    Customer.Zip              AS Customer_Zip,
                    Customer.Contact          AS Customer_Contact,
                    Location.Loc              AS Location_ID,
                    Location.Tag              AS Location_Name,
                    Location.Address          AS Location_Street,
                    Location.City             AS Location_City,
                    Location.State            AS Location_State,
                    Location.Zip              AS Location_Zip,
                    Job.ID                    AS Job_ID,
                    Job.fDesc                 AS Job_Name,
                    Job_Type.Type             AS Job_Type,
                    Division.ID               AS Division_ID,
                    Division.Name             AS Division_Name,
                    Route.ID 				  AS Route_ID,
                    Route.Name                AS Route_Name,
                    Employee.ID               AS Employee_ID,
                    Employee.fFirst           AS Employee_First_Name,
                    Employee.Last             AS Employee_Last_Name
            FROM    Invoice
                    LEFT JOIN Loc             AS Location ON Invoice.Loc      = Location.Loc
                    LEFT JOIN Job 	          AS Job	  ON Invoice.Job      = Job.ID
                    LEFT JOIN Zone 	          AS Division ON Location.Zone    = Division.ID
                    LEFT JOIN Route           AS Route	  ON Location.Route   = Route.ID
                    LEFT JOIN (
                      SELECT  Customer.ID     AS ID,
                              Rolodex.Name    AS Name,
                              Rolodex.Contact AS Contact,
                              Rolodex.Address AS Street,
                              Rolodex.City    AS City,
                              Rolodex.State   AS State,
                              Rolodex.Zip     AS Zip
                      FROM    Owner           AS Customer
                              LEFT JOIN Rol   AS Rolodex  ON Customer.Rol     = Rolodex.ID
                    ) AS Customer                         ON Location.Owner   = Customer.ID
                    LEFT JOIN Emp             AS Employee ON Route.Mech       = Employee.fWork
                    LEFT JOIN JobType         AS Job_Type ON Job.Type         = Job_Type.ID
            WHERE   Invoice.Ref = ?;",
            array(
                $ID
            )
        );
        $Invoice =   (  empty( $ID )
                           &&  !$result
                      )    || (empty( $ID ) )    ? array(
                'ID'                   => null,
                'Description'          => null,
                'Date'                 => null,
                'Amount'               => null,
                'Sales_Tax'            => null,
                'Total'                => null,
                'Taxable'              => null,
                'Customer_ID'          => null,
                'Customer_Name'        => null,
                'Customer_Street'      => null,
                'Customer_City'        => null,
                'Customer_State'       => null,
                'Customer_Zip'         => null,
                'Customer_Contact'     => null,
                'Location_ID'          => null,
                'Location_Name'        => null,
                'Location_Street'      => null,
                'Location_City'        => null,
                'Location_State'       => null,
                'Location_Zip'         => null,
                'Job_ID'               => null,
                'Job_Name'             => null,
                'Job_Type'             => null,
                'Division_ID'          => null,
                'Division_Name'        => null,
                'Route_ID'             => null,
                'Route_Name'           => null,
                'Employee_ID'          => null,
                'Employee_First_Name'  => null,
                'Employee_Last_Name'   => null
        ) : sqlsrv_fetch_array($result);
        if( isset( $_POST ) && count( $_POST ) > 0 ){
            $Invoice[ 'Location_Name' ] = isset( $_POST[ 'Location' ] )  ? $_POST[ 'Location' ]       : $Invoice[ 'Location_Name' ];
            $Invoice[ 'Job_Name' ]      = isset( $_POST[ 'Job' ] )       ? $_POST[ 'Job' ]            : $Invoice[ 'Job_Name' ];
            $Invoice[ 'Description' ] 	= isset( $_POST[ 'Description' ] ) ? $_POST[ 'Description' ]  : $Invoice[ 'Description' ];
            $Invoice[ 'Date' ]          = isset( $_POST[ 'Date' ] )      ? $_POST[ 'Date' ]           : $Invoice[ 'Date' ];
            $Invoice[ 'Amount' ] 		= isset( $_POST[ 'Amount' ] ) 	 ? $_POST[ 'Amount' ] 	      : $Invoice[ 'Amount' ];
            $Invoice[ 'Taxable' ] 		= isset( $_POST[ 'Taxable' ] ) 	 ? $_POST[ 'Taxable' ] 	      : $Invoice[ 'Taxable' ];
            $Invoice[ 'Sales_Tax' ]     = isset( $_POST[ 'Sales_Tax' ] ) ? $_POST[ 'Sales_Tax' ] 	  : $Invoice[ 'Sales_Tax' ];
            $Invoice[ 'Total' ] 	    = isset( $_POST[ 'Total' ] ) 	 ? $_POST[ 'Total' ]          : $Invoice[ 'Total' ];
            if( in_array( $_POST[ 'ID' ], array( null, 0, '', ' ' ) ) ){
                $result = \singleton\database::getInstance( )->query(
                    null,
                    "	DECLARE @MAXID INT;
                        SET @MAXID = CASE WHEN ( SELECT Max( Ref ) FROM Invoice ) IS NULL THEN 0 ELSE ( SELECT Max( Ref ) FROM Invoice ) END ;
                        INSERT INTO Invoice(
                            Ref,
                            Loc,
                            Job,
                            fDate,
                            fDesc,
                            Amount,
                            Taxable,
                            STax,
                            TFMID,
                            TFMSource,
                            EMailStatus
                        )
                        VALUES( 
                            @MAXID + 1, 
                            ( SELECT Top 1 Loc.Loc  FROM Loc WHERE Loc.Tag = ? ), 
                            ( SELECT Top 1 Job.ID   FROM Job WHERE Job.fDesc = ? ), 
                            ?, 
                            ?, 
                            ?, 
                            ?, 
                            ?, 
                            '', 
                            '', 
                            0 
                        );
                        SELECT @MAXID + 1;",
                    array(
                        $Invoice[ 'Location_Name' ],
                        $Invoice[ 'Job_Name' ],
                        !empty( $Invoice[ 'Date' ] ) ? $Invoice[ 'Date' ] : date( 'Y-m-d h:i:s' ),
                        $Invoice[ 'Description' ],
                        !empty( $Invoice[ 'Amount' ] ) ? $Invoice[ 'Amount' ] : 0,
                        !empty( $Invoice[ 'Taxable' ] ) ? $Invoice[ 'Taxable' ] : 0,
                        !empty( $Invoice[ 'Sales_Tax' ] ) ? $Invoice[ 'Sales_Tax' ] : 0
                    )
                );
                sqlsrv_next_result( $result );
                $Invoice[ 'ID' ] = sqlsrv_fetch_array( $result )[ 0 ];
                header( 'Location: invoice.php?ID=' . $Invoice[ 'ID' ] );
                exit;
            } else {
                \singleton\database::getInstance( )->query(
                    null,
                    "	UPDATE  Invoice
                        SET     Invoice.Loc     =   ( SELECT Top 1 Loc.Loc  FROM Loc WHERE Loc.Tag = ? ),
                                Invoice.Job     =   ( SELECT Top 1 Job.ID   FROM Job WHERE Job.fDesc = ? ),
                                Invoice.fDesc 	=   ?,
                                Invoice.fDate   =   ?,
                                Invoice.Amount 	=   ?,
                                Invoice.Total 	=   ?
                        WHERE   Invoice.Ref     =   ?;",
                    array(
                        $Invoice[ 'Location_Name' ],
                        $Invoice[ 'Job_Name' ],
                        $Invoice[ 'Description' ],
                        $Invoice[ 'fDate' ],
                        $Invoice[ 'Amount' ],
                        $Invoice[ 'Total' ],
                        $Invoice[ 'ID' ]
                    )
                );
            }
        }
?><!DOCTYPE html>
<html>
<head>
    <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
    <?php  $_GET[ 'Bootstrap' ] = '5.1';?>
    <?php  $_GET[ 'Entity_CSS' ] = 1;?>
    <?php  require( bin_meta . 'index.php');?>
    <?php  require( bin_css  . 'index.php');?>
    <?php  require( bin_js   . 'index.php');?>
</head>
<body>
    <div id="wrapper">
        <?php require( bin_php . 'element/navigation.php'); ?>
        <div id="page-wrapper" class='content'>
            <div class='no-print card card-primary'><form action='invoice.php?ID=<?php echo $Invoice[ 'ID' ];?>' method='POST'>
                <input type='hidden' class='form-control' name='ID' value='<?php echo $Invoice[ 'ID' ];?>' />
                <div class='card-heading'>
                    <div class='row g-0 px-3 py-2'>
                        <div class='col-6'>
                            <h5>
                                <?php \singleton\fontawesome::getInstance( )->Invoice( 1 );?>
                                <a href='invoices.php?<?php echo isset( $_SESSION[ 'Tables' ][ 'Invoice' ][ 0 ] ) 
                                    ? http_build_query( 
                                        is_array( $_SESSION[ 'Tables' ][ 'Invoices' ][ 0 ] ) 
                                            ? $_SESSION[ 'Tables' ][ 'Invoices' ][ 0 ] 
                                            : array( ) ) 
                                    : null;?>'
                                >Invoice</a>: 
                                #<span><?php 
                                    echo is_null( 
                                        $Invoice[ 'ID' ] ) 
                                            ?   'New' 
                                            :   $Invoice[ 'ID' ];
                                ?></span>
                            </h5>
                        </div>
                        <div class='col-2'></div>
                        <div class='col-2'>
                            <div class='row g-0'>
                                <div class='col-4'><button class='form-control rounded' onClick="document.location.href='invoice.php';">Create</button></div>
                                <div class='col-4'><button class='form-control rounded' onClick="document.location.href='invoice.php?ID=<?php echo $Invoice[ 'ID' ];?>';">Refresh</button></div>
                                <div class='col-4'><button class='form-control rounded' onClick="window.print();return false;"/>Print</div>
                            </div>
                        </div>
                        <div class='col-2'>
                            <div class='row g-0'>
                                <div class='col-4'><button class='form-control rounded' onClick="document.location.href='invoice.php?ID=<?php echo !is_null( $Invoice[ 'ID' ] ) ? array_keys( $_SESSION[ 'Tables' ][ 'Invoices' ], true )[ array_search( $Invoice[ 'ID' ], array_keys( $_SESSION[ 'Tables' ][ 'Invoices' ], true ) ) - 1 ] : null;?>';">Previous</button></div>
                                <div class='col-4'><button class='form-control rounded' onClick="document.location.href='invoices.php?<?php echo http_build_query( is_array( $_SESSION[ 'Tables' ][ 'Invoices' ][ 0 ] ) ? $_SESSION[ 'Tables' ][ 'Invoices' ][ 0 ] : array( ) );?>';">Table</button></div>
                                <div class='col-4'><button class='form-control rounded' onClick="document.location.href='invoice.php?ID=<?php echo !is_null( $Invoice[ 'ID' ] )? array_keys( $_SESSION[ 'Tables' ][ 'Invoices' ], true )[ array_search( $Invoice[ 'ID' ], array_keys( $_SESSION[ 'Tables' ][ 'Customers' ], true ) ) + 1 ] : null;?>';">Next</button></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class='card-body bg-dark text-white'>
                    <div class='card-columns'>
                        <div class='card card-primary my-3'>
                            <div class='card-heading'>
                                <div class='row g-0 px-3 py-2'>
                                    <div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Info( 1 );?><span>Infomation</span></h5></div>
                                    <div class='col-2'>&nbsp;</div>
                                </div>    
                            </div>
                            <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Infomation' ] ) && $_SESSION[ 'Cards' ][ 'Infomation' ] == 0 ? "style='display:none;'" : null;?>>
                                <div class='row g-0'>
                                    <label class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Location(1);?> Location:</label>
                                    <div class='col-6'>
                                        <input placeholder='Location' type='text' autocomplete='off' class='form-control edit' name='Location' value='<?php echo $Invoice[ 'Location_Name' ];?>' />
                                        <script>
                                            $( 'input[name="Location"]' )
                                                .typeahead({
                                                    minLength : 4,
                                                    hint: true,
                                                    highlight: true,
                                                    limit : 5,
                                                    display : 'FieldValue',
                                                    source: function( query, result ){
                                                        $.ajax({
                                                            url : 'bin/php/get/search/Locations.php',
                                                            method : 'GET',
                                                            data    : {
                                                                search :  $('input:visible[name="Location"]').val( )
                                                            },
                                                            dataType : 'json',
                                                            beforeSend : function( ){
                                                                abort( );
                                                            },
                                                            success : function( data ){
                                                                result( $.map( data, function( item ){
                                                                    return item.FieldValue;
                                                                } ) );
                                                            }
                                                        });
                                                    },
                                                    afterSelect: function( value ){
                                                        $( 'input[name="Location"]').val( value );
                                                        $( 'input[name="Location"]').closest( 'form' ).submit( );
                                                    }
                                                }
                                            );
                                        </script>
                                    </div>
                                    <div class='col-2'><button class='h-100 w-100' type='button' <?php
                                    if( in_array( $Invoice[ 'Location_ID' ], array( null, 0, '', ' ') ) ){
                                            echo "onClick=\"document.location.href='locations.php';\"";
                                        } else {
                                            echo "onClick=\"document.location.href='location.php?ID=" . $Invoice[ 'Location_ID' ] . "';\"";
                                        }
                                    ?>><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                                </div>
                                <div class='row g-0'>
                                    <label class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Job(1);?> Job:</label>
                                    <div class='col-6'>
                                        <input placeholder='Job' type='text' autocomplete='off' class='form-control edit' name='Job' value='<?php echo $Invoice[ 'Job_Name' ];?>' />
                                        <script>
                                            $( 'input[name="Job"]' )
                                                .typeahead({
                                                    minLength : 4,
                                                    hint: true,
                                                    highlight: true,
                                                    limit : 5,
                                                    display : 'FieldValue',
                                                    source: function( query, result ){
                                                        $.ajax({
                                                            url : 'bin/php/get/search/Jobs.php',
                                                            method : 'GET',
                                                            data    : {
                                                                search :  $('input:visible[name="Job"]').val( )
                                                            },
                                                            dataType : 'json',
                                                            beforeSend : function( ){
                                                                abort( );
                                                            },
                                                            success : function( data ){
                                                                result( $.map( data, function( item ){
                                                                    return item.FieldValue;
                                                                } ) );
                                                            }
                                                        });
                                                    },
                                                    afterSelect: function( value ){
                                                        $( 'input[name="Job"]').val( value );
                                                        $( 'input[name="Job"]').closest( 'form' ).submit( );
                                                    }
                                                }
                                            );
                                        </script>
                                    </div>
                                    <div class='col-2'><button class='h-100 w-100' type='button' <?php
                                        if( in_array( $Invoice[ 'Job_ID' ], array( null, 0, '', ' ') ) ){
                                            echo "onClick=\"document.location.href='jobs.php';\"";
                                        } else {
                                            echo "onClick=\"document.location.href='job.php?ID=" . $Invoice[ 'Job_ID' ] . "';\"";
                                        }
                                    ?>><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                                </div>
                                <div class='row g-0'>
                                    <label class='col-4 border-bottom border-white my-auto' for='Description'><?php \singleton\fontawesome::getInstance( )->Paragraph( 1 );?> Description:</label>
                                    <div class='col-12'><textarea class='form-control' name='Description' rows='8' placeholder='Description'><?php echo $Invoice[ 'Description' ];?></textarea></div>
                                </div>
                                <div class='row g-0'>
                                    <label class='col-4 border-bottom border-white my-auto' for='Date'><?php \singleton\fontawesome::getInstance( )->Calendar( 1 );?> Date:</label>
                                    <div class='col-8'><input placeholder='mm/dd/yy' class='form-control date' autocomplete='off' name='Date' value='<?php echo empty( $Invoice[ 'Date' ] ) ? null : date( 'm/d/Y', strtotime( $Invoice[ 'Date' ] ) );?>' /></div>
                                </div>
                                <div class='row g-0'>
                                    <label class='col-4 border-bottom border-white my-auto' for='Amount'><?php \singleton\fontawesome::getInstance( )->Dollar( 1 );?> Amount:</label>
                                    <div class='col-8'><input placeholder='$.00' class='form-control' name='Amount' value='<?php echo empty( $Invoice[ 'Amount' ] ) ? null : $Invoice[ 'Amount' ];?>' /></div>
                                </div>
                                <div class='row g-0'>
                                    <label class='col-4 border-bottom border-white my-auto' for='Sales_Tax'><?php \singleton\fontawesome::getInstance( )->Dollar( 1 );?> Sales Tax:</label>
                                    <div class='col-8'><input placeholder='$.00' class='form-control' name='Sales_Tax' value='<?php echo empty( $Invoice[ 'Sales_Tax' ] ) ? null : $Invoice[ 'Sales_Tax' ];?>' /></div>
                                </div>
                                <div class='row g-0'>
                                    <label class='col-4 border-bottom border-white my-auto' for='Total'><?php \singleton\fontawesome::getInstance( )->Dollar( 1 );?> Total:</label>
                                    <div class='col-8'><input placeholder='$.00' readonly class='form-control' name='Total' value='<?php echo empty( $Invoice[ 'Total' ] ) ? null : $Invoice[ 'Total' ];?>' /></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form></div>
            <div class='print'>
                <div class='row'>
                    <div class='col-6'>
                        <div><img src='http://www.nouveauelevator.com/Images/Icons/logo.png' width='25px' style='position:relative;left:110px;' /></div>
                        <h3 style='text-align:left;' class='BankGothic'>Nouveau Elevator</h3>
                    </div>
                    <div class='col-6' style='text-align:right;'>
                        <div clsas='row' style='font-size:12px;'>
                            <div class='col-12'>47-55 37th Street LIC, NY 11101</div>
                        </div>
                        <div clsas='row' style='font-size:12px;'>
                            <div class='col-12'>Tel:(718)349-4700 Fax:383:3218</div>
                        </div>
                        <div clsas='row' style='font-size:12px;'>
                            <div class='col-12'>www.NouveauElevator.com</div>
                        </div>
                    </div>
                </div>
                <h4 style='text-align:center;'><b><u>Invoice #<?php echo $Invoice['ID'];?></u></b></h4>
                <div class='row'>&nbsp;</div>
                <div class='row' style='font-size:12px;'>
                    <div class='col-2'><b>Bill To:</b></div>
                    <div class='col-4'><?php echo $Invoice['Customer_Name'];?></div>
                    <div class='col-2' style='text-align:right;'><b>Account ID:</b></div>
                    <div class='col-4'><?php echo $Invoice['Location_Name'];?></div>
                </div>
                <div class='row' style='font-size:12px;'>
                    <div class='col-2'>&nbsp;</div>
                    <div class='col-4'>ATTN:<?php echo $Invoice['Customer_Contact'];?></div>
                    <div class='col-2'>&nbsp;</div>
                    <div class='col-4'><?php echo $Invoice['Location_Name'];?></div>
                </div>
                <div class='row' style='font-size:12px;'>
                    <div class='col-2'>&nbsp;</div>
                    <div class='col-4'><?php echo $Invoice['Customer_Street'];?></div>
                    <div class='col-2' style='text-align:right;'><b>Invoice #:</b></div>
                    <div class='col-4'><?php echo $Invoice['ID'];?></div>
                </div>
                <div class='row' style='font-size:12px;'>
                    <div class='col-2'>&nbsp;</div>
                    <div class='col-4'><?php echo $Invoice['Customer_City'] . ', ' . $Invoice['Customer_State'] . ' ' . $Invoice['Customer_Zip'];?></div>
                    <div class='col-2' style='text-align:right;'><b>Amount:</b></div>
                    <div class='col-4'><?php echo substr(money_format('%.2n',$Invoice['Amount']),0);?></div>
                </div>
                <div class='row' style='font-size:12px;'>
                    <div class='col-6'>&nbsp;</div>
                    <div class='col-2' style='text-align:right;'><b>Paid:</b></div>
                    <div class='col-4'><?php echo isset($Invoice['Paid']) ? substr(money_format('%.2n',$Invoice['Paid']),0) : '$0.00';?></div>
                </div>
                <div class='row'>&nbsp;</div>
                <div class='row' style='border:2px solid black;'>
                    <div class='col-2' style='background-color:#9a9a9a !important;'><b>Date:</b></div>
                    <div class='col-2' style='border-left:2px solid black;border-right:2px solid black;'><?php echo substr($Invoice['Date'],0,10);?></div>
                    <div class='col-2' style='background-color:#9a9a9a !important;'><b>Terms:</b></div>
                    <div class='col-2' style='border-left:2px solid black;border-right:2px solid black;'>Net 30 Days</div>
                    <div class='col-2' style='background-color:#9a9a9a !important;'><b>Job:</b></div>
                    <div class='col-2' style='border-left:2px solid black;'><a href='job.php?ID=<?php echo $Invoice['Job_ID'];?>' style='color:black;text-decoration:none;'><?php echo $Invoice['Job_ID'];?></a></div>
                    <div style='clear:both;'></div>
                </div>
                <div class='row' style='border-left:2px solid black;border-right:2px solid black;border-bottom:2px solid black;'>
                    <div class='col-2' style='background-color:#9a9a9a !important;'><b>Amount:</b></div>
                    <div class='col-2' style='border-left:2px solid black;border-right:2px solid black;'><?php echo substr(money_format('%.2n',$Invoice['Total']),0);?></div>
                    <div class='col-2' style='background-color:#9a9a9a !important;'><b>P.O. #:</b></div>
                    <div class='col-2' style='border-left:2px solid black;border-right:2px solid black;'>&nbsp;</div>
                    <div class='col-2' style='background-color:#9a9a9a !important;'><b>Type:</b></div>
                    <div class='col-2' style='border-left:2px solid black;'><?php echo proper($Invoice['Job_Type']);?></div>
                    <div style='clear:both;'></div>
                </div>
                <div class='row'>&nbsp;</div>
                <div class='row'>
                    <table id='Table_Invoice' cellpadding='5px' width='100%' style='border:2px solid black;'>
                        <thead>
                            <tr style='background-color:#9a9a9a;border-bottom:1px solid black;'>
                                <th style='text-align:center;background-color:#9a9a9a;padding:3px;'>Quantity</th>
                                <th style='text-align:center;background-color:#9a9a9a;padding:3px;'>Description</th>
                                <th style='text-align:center;background-color:#9a9a9a;padding:3px;'>Unit</th>
                                <th style='text-align:center;background-color:#9a9a9a;padding:3px;'>Price</th>
                                <th style='text-align:center;background-color:#9a9a9a;padding:3px;'>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan='2' style='border-right:1px solid black;'><pre><?php echo $Invoice['Description'];?></pre></td>
                                <td style='text-align:center;border:1px solid black;'>hr.</td>
                                <td style='text-align:center;border:1px solid black;'><?php echo substr(money_format('%.2n',$Invoice['Amount']),0);?></td>
                                <td style='text-align:center;border:1px solid black;'><?php echo substr(money_format('%.2n',$Invoice['Total']),0);?></td>
                            </tr>
                            <tr>
                                <td colspan='2'>&nbsp;</td>
                                <td colspan='2' style='border:1px solid black;text-align:right;'>Taxable:</td>
                                <td style='border:1px solid black;text-align:center;'><?php echo substr(money_format('%.2n',$Invoice['Taxable']),0);?></td>
                            </tr>
                            <tr>
                                <td colspan='2'>&nbsp;</td>
                                <td colspan='2' style='border:1px solid black;text-align:right;'>Non-Taxable:</td>
                                <td style='border:1px solid black;text-align:center;'><?php echo substr(money_format('%.2n',$Invoice['Amount'] - $Invoice['Taxable']),0);?></td>
                            </tr>
                            <tr>
                                <td colspan='2'>&nbsp;</td>
                                <td colspan='2' style='border:1px solid black;text-align:right;'><b>Sub-Total:</b></td>
                                <td style='border:1px solid black;text-align:center;'><?php echo substr(money_format('%.2n',$Invoice['Amount']),0);?></td>
                            </tr>
                            <tr>
                                <td colspan='2'>&nbsp;</td>
                                <td colspan='2' style='border:1px solid black;text-align:right;'>Sales Tax:</td>
                                <td style='border:1px solid black;text-align:center;'><?php echo substr(money_format('%.2n',$Invoice['Sales_Tax']),0);?></td>
                            </tr>
                            <tr>
                                <td colspan='2'>&nbsp;</td>
                                <td colspan='2' style='border:1px solid black;text-align:right;'><b>Total:</b></td>
                                <td style='border:1px solid black;text-align:center;'><?php echo substr(money_format('%.2n',$Invoice['Total']),0);?></td>
                            </tr>
                            <tr style='border-top:2px solid black;'><td colspan='5' style='padding:10px;text-align:center;'>Invoices not paid within terms may be subject to a service charge of 1.5% per month, or the maximum permitted by law.</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class='row' style='position:fixed;bottom:0;width:100%;'>
                    <h4 style='text-align:center;'><b>Nouveau Elevator Industries, Inc.</b></h4>
                    <div style='text-align:center;'>47-55 37th Street LIC, NY 11101 TEL:718.349.4700 FAX: 718.383.3218</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html><?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=invoice<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? 's.php' : ".php?ID={$_GET['ID']}";?>';</script></head></html><?php }?>
