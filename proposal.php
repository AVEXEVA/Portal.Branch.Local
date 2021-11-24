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
		  FROM    Emp
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
        ||  !isset( $Privileges[ 'Job' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'Job' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        \singleton\database::getInstance( )->query(
            null,
            "   INSERT INTO Activity([User], [Date], [Page] )
                VALUES( ?, ?, ? );",
            array(
                $_SESSION[ 'Connection' ][ 'User' ],
                date('Y-m-d H:i:s'),
                'job.php'
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
            "   SELECT  TOP 1
                        Estimate.ID             AS  ID,
                        Estimate.Name           AS  Contact,
                        Estimate.fDesc          AS  Name,
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
                        Job.ID                  AS  Job,
                        Job.fDesc                AS  Job,
                        Estimate.EstTemplate    AS  EstTemplate,
                        Estimate.STaxRate       AS  Sales_Tax_Rate,
                        Estimate.STax           AS  Sales_Tax,
                        Estimate.SExpense       AS  Sales_Expense,
                        Estimate.Quoted         AS  Quoted,
                        Estimate.Phase          AS  Phase,
                        Estimate.Probability    AS  Probability,
                        Loc.Address             AS  Street,
                        Loc.State               AS  State,
                        Loc.City                AS  City,
                        Loc.Zip                 AS  Zip,
                        Customer.ID             AS  Customer_ID,
                        Customer.Name           AS  Customer_Name,
                        Rol.Contact             AS  Contact,
                        Rol.Fax                 AS  Fax,
                        Rol.Phone               AS  Phone,
                        Rol.EMail               AS  Email
                FROM    Estimate
                        LEFT JOIN Loc ON  Estimate.LocID  = Loc.Loc
                        LEFT JOIN   (
                                      SELECT  Owner.ID,
                                              Rol.Name,
                                              Owner.Status
                                      FROM    Owner
                                              LEFT JOIN Rol ON Owner.Rol = Rol.ID
                                    ) AS Customer ON Loc.Owner = Customer.ID
                        LEFT JOIN Rol ON    Rol.ID = Estimate.RolID
                        LEFT JOIN Job ON    Job.ID = Estimate.Job
                WHERE   Estimate.ID = ?;",
            array(
                $ID
            )
        );
        $Estimate =   (       empty( $ID )
                        &&    !empty( $Name )
                        &&    !$result
                      ) || (  empty( $ID )
                        &&    empty( $Name )
                      )
            ? array(
                'ID' => null,
                'Contact' => null,
                'Name' => null,
                'Date' => null,
                'Type' => null,
                'Template' => null,
                'Remarks' => null,
                'Cost' => null,
                'Hours' => null,
                'Labor' => null,
                'Overhead' => null,
                'Price' => null,
                'Profit' => null,
                'SubTotal_1' => null,
                'SubTotal_2' => null,
                'Job_ID' => null,
                'Job_Name' => null,
                'EstTemplate' => null,
                'Sales_Tax_Rate' => null,
                'Sales_Tax' => null,
                'Sales_Expense' => null,
                'Quoted' => null,
                'Phase' => null,
                'Probability' => null,
                'Street' => null,
                'State' => null,
                'City' => null,
                'Zip' => null,
                'Customer_ID' => null,
                'Customer_Name' => null,
                'Contact' => null,
                'Fax' => null,
                'Phone' => null,
                'Email' => null
            )
            : sqlsrv_fetch_array($result);
?><!DOCTYPE html>
<html lang="en">
<head>
  <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
     <?php  $_GET[ 'Bootstrap' ] = '5.1';?>
     <?php  $_GET[ 'Entity_CSS' ] = 1;?>
     <?php	require( bin_meta . 'index.php');?>
     <?php	require( bin_css  . 'index.php');?>
     <?php  require( bin_js   . 'index.php');?>
</head>
<body>
    <div id="wrapper">
        <?php require(bin_php.'element/navigation.php');?>
        <div id="page-wrapper" class='content'>
            <div class='no-print'>
                <div class='card'>
                    <div class='card-heading'>
                        <div class='row g-0 px-3 py-2'>
                            <div class='col-6'>
                              <h5><?php \singleton\fontawesome::getInstance( )->proposal( 1 );?><a href='proposals.php?<?php
                                echo http_build_query( is_array( $_SESSION[ 'Tables' ][ 'Proposals' ][ 0 ] ) ? $_SESSION[ 'Tables' ][ 'Proposals' ][ 0 ] : array( ) );
                              ?>'>New Proposal</a>: <span><?php
                                echo is_null( $Estimate[ 'ID' ] )
                                  ? 'New'
                                  : $Estimate[ 'Name' ];
                              ?></span></h5>
                            </div>
                            <div class='col-2'></div>
                            <div class='col-2'>
                              <div class='row g-0'>
                                <div class='col-4'>
                                  <button
                                    class='form-control rounded'
                                    onClick="document.location.href='proposal.php';"
                                  >Create</button>
                                </div>
                                <div class='col-4'>
                                  <button
                                    class='form-control rounded'
                                    onClick="document.location.href='proposal.php?ID=<?php echo $Route[ 'ID' ];?>';"
                                  >Refresh</button>
                                </div>
                              </div>
                            </div>
                            <div class='col-2'>
                              <div class='row g-0'>
                                <div class='col-4'><button class='form-control rounded' onClick="document.location.href='proposal.php?ID=<?php echo !is_null( $Job[ 'ID' ] ) ? array_keys( $_SESSION[ 'Tables' ][ 'Proposals' ], true )[ array_search( $Job[ 'ID' ], array_keys( $_SESSION[ 'Tables' ][ 'Proposals' ], true ) ) - 1 ] : null;?>';">Previous</button></div>
                                <div class='col-4'><button class='form-control rounded' onClick="document.location.href='proposals.php?<?php echo http_build_query( is_array( $_SESSION[ 'Tables' ][ 'Proposals' ][ 0 ] ) ? $_SESSION[ 'Tables' ][ 'Proposals' ][ 0 ] : array( ) );?>';">Table</button></div>
                                <div class='col-4'><button class='form-control rounded' onClick="document.location.href='proposal.php?ID=<?php echo !is_null( $Job[ 'ID' ] )? array_keys( $_SESSION[ 'Tables' ][ 'Proposals' ], true )[ array_search( $Job[ 'ID' ], array_keys( $_SESSION[ 'Tables' ][ 'Proposals' ], true ) ) + 1 ] : null;?>';">Next</button></div>
                              </div>
                            </div>
                        </div>
                    </div>
                    <div class='card-body bg-dark text-white'>
                        <div class='card-columns'>
                            <div class='card card-primary my-3'><form action='proposal.php?ID=<?php echo $Estimate[ 'ID' ];?>' method='POST'>
                              <input type='hidden' name='ID' value='<?php echo $Estimate[ 'ID' ];?>' />
                              <div class='card-heading'>
                                <div class='row g-0 px-3 py-2'>
                                  <div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Info( 1 );?><span>Infomation</span></h5></div>
                                  <div class='col-2'>&nbsp;</div>
                                </div>
                              </div>
                              <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Infomation' ] ) && $_SESSION[ 'Cards' ][ 'Infomation' ] == 0 ? "style='display:none;'" : null;?>>
                                <div class='row g-0'>
                                  <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Proposal(1);?>Name:</div>
                                  <div class='col-8'><input type='text' class='form-control edit animation-focus' name='Name' value='<?php echo $Estimate['Name'];?>' /></div>
                                </div>
                                <div class='row g-0'>
                                  <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Job(1);?> Job:</div>
                                  <div class='col-6'>
                                    <input type='text' autocomplete='off' class='form-control edit' name='Job' value='<?php echo $Estimate[ 'Job_Name' ];?>' />
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
                                    if( in_array( $Estimate[ 'Job_ID' ], array( null, 0, '', ' ') ) ){
                                      echo "onClick=\"document.location.href='jobs.php';\"";
                                    } else {
                                      echo "onClick=\"document.location.href='job.php?Name=" . $Estimate[ 'Job_Name' ] . "';\"";
                                    }
                                  ?>><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                              </div>
                              <div class='row g-0'>
                                  <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Proposal(1);?> Proposal:</div>
                                  <div class='col-6'>
                                    <input type='text' autocomplete='off' class='form-control edit' name='Customer' value='<?php echo $Estimate[ 'Customer_Name' ];?>' />
                                    <script>
                                      $( 'input[name="Customer"]' )
                                          .typeahead({
                                              minLength : 4,
                                              hint: true,
                                              highlight: true,
                                              limit : 5,
                                              display : 'FieldValue',
                                              source: function( query, result ){
                                                  $.ajax({
                                                      url : 'bin/php/get/search/Customers.php',
                                                      method : 'GET',
                                                      data    : {
                                                          search :  $('input:visible[name="Customer"]').val( )
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
                                                  $( 'input[name="Customer"]').val( value );
                                                  $( 'input[name="Customer"]').closest( 'form' ).submit( );
                                              }
                                          }
                                      );
                                    </script>
                                  </div>
                                  <div class='col-2'><button class='h-100 w-100' type='button' <?php
                                    if( in_array( $Estimate[ 'Customer_ID' ], array( null, 0, '', ' ') ) ){
                                      echo "onClick=\"document.location.href='customers.php';\"";
                                    } else {
                                      echo "onClick=\"document.location.href='customer.php?Name=" . $Estimate[ 'Customer_Name' ] . "';\"";
                                    }
                                  ?>><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                              </div>
                              <div class='card-footer'>
                                  <div class='row'>
                                      <div class='col-12'><button class='form-control' type='submit'>Save</button></div>
                                  </div>
                              </div>
                            </form></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class='print'>
                <div class='row'>
                    <div class='col-12' style='text-align:center;'>
                        <img src='bin/media/logo/nouveau-no-white.jpg' height='150px' />
                    </div>
                    <!--<div class='col-12'><h1 style='text-align:center;'><b class='BankGothic' >Nouveau Elevator</b></h1></div>-->
                </div>
                <div class='row' style='border-top:1px solid black;margin-top:5px;margin-bottom:5px;height:5px;'>&nbsp;</div>
                <div class='row'>
                    <div class='col-12' style='b'><h3 style='text-align:center;margin:0px;padding:5px;'>Proposal #<?php echo $Estimate[ 'ID' ];?></h3></div>
                </div>
                <div class='row' style='border-top:1px solid black;margin-top:5px;margin-bottom:5px;height:5px;'>&nbsp;</div>
                <div class='row' style=''>
                    <div class='col-4'>OFFICE (718) 349-4700</div>
                    <div class='col-4'>FAX (718) 349-8932</div>
                    <div class='col-4'>proposal@nouveauelevator.com</div>
                </div>
                <div class='row' style='border-top:1px solid black;margin-top:5px;margin-bottom:5px;height:5px;'>&nbsp;</div>
                <div class='row'>
                    <div class='col-2'>ATTN:</div>
                    <div class='col-4'><?php echo $Estimate[ 'Contact' ];?></div>
                    <div class='col-6'>PROPOSAL #<?php echo $Estimate[ 'ID' ];?></div>
                </div>
                <div lcass='row'>
                    <div class='col-2'>PHONE:</div>
                    <div class='col-4'><?php echo $Estimate[ 'Phone' ];?></div>
                    <div class='col-6'><?PHP echo date( 'm/d/Y', strtotime( $Estimate[ 'Date' ] ) );?></div>
                </div>
                <div class='row'>
                    <div class='col-2'>FAX:</div>
                    <div class='col-4'><?php echo $Estimate[ 'Fax' ];?></div>
                    <div class='col-6'>&nbsp;</div>
                </div>
                <div class='row'>
                    <div class='col-2'>EMAIL:</div>
                    <div class='col-4'><?php echo $Estimate[ 'Email' ];?></div>
                    <div class='col-6'>&nbsp;</div>
                </div>
                <div class='row'>
                    <div class='col-2'>FROM:</div>
                    <div class='col-4'><?php /*INSERT FROM HERE*/?></div>
                    <div class='col-6'>&nbsp;</div>
                </div>
                <div class='row'>
                    <div class='col-2'>PREMISE:</div>
                    <div class='col-4'><?php echo $Estimate[ 'Location' ];?></div>
                    <div class='col-6'>&nbsp;</div>
                </div>
                <div class='row'>
                    <div class='col-2'>CUSTOMER:</div>
                    <div class='col-4'><?php echo $Estimate[ 'Customer' ];?></div>
                    <div class='col-6'>&nbsp;</div>
                </div>
                <div class='row'>
                    <div class='col-2'>RE:</div>
                    <div class='col-4'><?php echo $Estimate[ 'Title'];?></div>
                    <div class='col-6'>&nbsp;</div>
                </div>
                <div class='row' style='border-top:1px solid black;margin-top:5px;margin-bottom:5px;height:5px;'>&nbsp;</div>
                <div class='row'>
                    <div class='col-12' style='text-align:center;'><u>WORK DESCRIPTION</u></div>
                </div>
                <div class='row'>
                    <div class='col-12'><pre style='padding:25px;font-size:18px;'><?php echo $Estimate[ 'Remarks' ];?></pre></div>
                </div>
                <div class='row'>
                    <div class='col-3'>COST NOT TO EXCEED:</div>
                    <div class='col-9'>$<?php echo number_format( $Estimate[ 'Price' ], 2 );?> - PLUS ANY APPlICABLE TAXES</div>
                </div>
                <div class='row' style='border-top:1px solid black;margin-top:5px;margin-bottom:5px;height:5px;'>&nbsp;</div>
                <div class='row'>
                    <div class='col-12' style='text-align:center;'>THIS PROPOSAL IS VALID FOR 180 DAYS FROM DATE ABOVE</div>
                </div>
                <div class='row'>
                    <div class='col-12' style='text-align:center;'>"IF ADDITIONAL WORK IS NEEDED OTHER THAN STATED YOU WILL BE INFORMED IMMEDIATELY"</div>
                </div>
                <div class='row'>
                    <div class='col-12' style='text-align:center;'>"WORK TO BE PERFORMED DURING REGULAR HOURS UNLESS OTHERWISE STATED</div>
                </div>
                <div class='row' style='border-top:1px solid black;margin-top:5px;margin-bottom:5px;height:5px;'>&nbsp;</div>
                <div class='row'>
                    <div class='col-12' style='text-align:center;'>AUTHORIZATION TO PROCEED WITH WORK AND TERMS DESCRIBED ABOVE</div>
                </div>
                <div class='row'>
                    <div class='col-5' style='text-align:right;'>P.O.#</div>
                    <div class='col-4' style='border-bottom:1px solid black;'>&nbsp;</div>
                </div>
                <div class='row'>
                    <div class='col-5' style='text-align:right;'>NAME</div>
                    <div class='col-4' style='border-bottom:1px solid black;'>&nbsp;</div>
                </div>
                <div class='row'>
                    <div class='col-5' style='text-align:right;'>TITLE & DATE</div>
                    <div class='col-4' style='border-bottom:1px solid black;'>&nbsp;</div>
                </div>
                <div class='row'>
                    <div class='col-5' style='text-align:right;'>AUTHORIZED SIGNATURE</div>
                    <div class='col-4' style='border-bottom:1px solid black;'>&nbsp;</div>
                </div>
                <div class='row' style='border-top:1px solid black;margin-top:5px;margin-bottom:5px;height:5px;'>&nbsp;</div>
                <div class='row'>
                    <div class='col-12' style='text-align:center;'>PLEASE RETURN SIGNED FORM BY FAX WITH YOUR APPROVAL TO PROCEED AS DESCRIBED</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=proposal<?php echo (!isset($Estimate[ 'ID' ]) || !is_numeric($Estimate[ 'ID' ])) ? "s.php" : ".php?ID={$Estimate[ 'ID' ]}";?>";</script></head></html><?php }?>
