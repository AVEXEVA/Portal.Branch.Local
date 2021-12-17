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
                'proposal.php'
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
                        Estimate.Name           AS  Name,
                        Estimate.fDesc          AS  Description,
                        Estimate.fDate          AS  Date,
                        Estimate.Type           AS  Type,
                        Estimate.Template       AS  Template,
                        EStimate.Remarks        AS  Notes,
                        Estimate.Cost           AS  Cost,
                        Estimate.Hours          AS  Hours,
                        Estimate.Labor          AS  Labor,
                        Estimate.Overhead       AS  Overhead,
                        Estimate.Price          AS  Price,
                        Estimate.Profit         AS  Profit,
                        Estimate.SubTotal1      AS  SubTotal_1,
                        Estimate.SubTotal2      AS  SubTotal_2,
                        Estimate.fFor           AS  [For],
                        Estimate.Category       AS  Category,
                        Job.ID                  AS  Job_ID,
                        Job.fDesc               AS  Job_Name,
                        Estimate.EstTemplate    AS  EstTemplate,
                        Estimate.STaxRate       AS  Sales_Tax_Rate,
                        Estimate.STax           AS  Sales_Tax,
                        Estimate.SExpense       AS  Sales_Expense,
                        Estimate.Quoted         AS  Quoted,
                        Estimate.Phase          AS  Phase,
                        Estimate.Probability    AS  Probability,
                        Loc.Loc                 AS  Location_ID,
                        Loc.Tag                 AS  Location_Name,
                        Loc.Address             AS  Street,
                        Loc.State               AS  State,
                        Loc.City                AS  City,
                        Loc.Zip                 AS  Zip,
                        Customer.ID             AS  Customer_ID,
                        Customer.Name           AS  Customer_Name,
                        Rol.ID 					        AS  Contact_ID,
                        Rol.Contact             AS  Contact_Name,
                        Rol.Fax                 AS  Contact_Fax,
                        Rol.Phone               AS  Contact_Phone,
                        Rol.EMail               AS  Contact_Email,
                        Emp.ID                  AS  Employee_ID,
                        Emp.fFirst + ' ' + Emp.Last AS Employee_Name
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
                        LEFT JOIN Emp ON    Emp.ID = Estimate.EmpID
                WHERE   Estimate.ID = ?;",
            array(
                $ID
            )
        );
        $Proposal =   (       empty( $ID )
                        &&    !empty( $Name )
                        &&    !$result
                      ) || (  empty( $ID )
                        &&    empty( $Name )
                      )
            ? array(

                'ID' => null,
                'Name' => null,
                'Description' => null,
                'Contact' => null,
                'Date' => null,
                'Type' => null,
                'Template' => null,
                'Notes' => null,
                'Cost' => null,
                'Hours' => null,
                'Labor' => null,
                'Overhead' => null,
                'Price' => null,
                'Profit' => null,
                'SubTotal_1' => null,
                'SubTotal_2' => null,
                'For' => null,
                'Category' => null,
                'Employee' => null,
                'Remarks' => null,
                'Proposal' => null,
                'Job_ID' => isset( $_GET[ 'Job_ID' ] ) ? $_GET[ 'Job_ID' ] : null,
                'Job_Name' => isset( $_GET[ 'Job_Name' ] ) ? $_GET[ 'Job_Name' ] : null,
                'EstTemplate' => null,
                'Sales_Tax_Rate' => null,
                'Sales_Tax' => null,
                'Sales_Expense' => null,
                'Quoted' => null,
                'Phase' => null,
                'Probability' => null,
                'Location_ID' => isset( $_GET[ 'Location_ID' ] ) ? $_GET[ 'Location_ID' ] : null,
                'Location_Name' => isset( $_GET[ 'Location_Name' ] ) ? $_GET[ 'Location_Name' ] : null,
                'Street' => null,
                'State' => null,
                'City' => null,
                'Zip' => null,
                'Customer_ID' => isset( $_GET[ 'Customer_ID' ] ) ? $_GET[ 'Customer_ID' ] : null,
                'Customer_Name' => isset( $_GET[ 'Customer_Name' ] ) ? $_GET[ 'Customer_Name' ] : null,
                'Fax' => null,
                'Phone' => null,
                'Email' => null,
                'Employee_ID' => isset( $_GET[ 'Employee_ID' ] ) ? $_GET[ 'Employee_ID' ] : null,
                'Employee_Name' => isset( $_GET[ 'Employee_Name' ] ) ? $_GET[ 'Employee_Name' ] : null,
                'Contact_ID' => isset( $_GET[ 'Contact_ID' ] ) ? $_GET[ 'Contact_ID' ] : null,
                'Contact_Name' => isset( $_GET[ 'Contact_Name' ] ) ? $_GET[ 'Contact_Name' ] : null

            )
            : sqlsrv_fetch_array($result);
        if( isset( $_POST ) && count( $_POST ) > 0 ){
          $Proposal[ 'Name' ]             = isset( $_POST[ 'Name' ] )        ? $_POST[ 'Name' ]         : $Proposal[ 'Name' ];
          $Proposal[ 'Contact_ID' ]       = isset( $_POST[ 'Contact_ID' ] )     ? $_POST[ 'Contact_ID' ]      : $Proposal[ 'Contact_ID' ];
          $Proposal[ 'Job_ID' ]           = isset( $_POST[ 'Job_ID' ] )         ? $_POST[ 'Job_ID' ]          : $Proposal[ 'Job_ID' ];
          $Proposal[ 'Location_ID' ]      = isset( $_POST[ 'Location_ID' ] )    ? $_POST[ 'Location_ID' ]     : $Proposal[ 'Location_ID' ];
          $Proposal[ 'Employee_ID' ]      = isset( $_POST[ 'Employee_ID' ] )    ? $_POST[ 'Employee_ID' ]     : $Proposal[ 'Employee_ID' ];
          $Proposal[ 'Date' ]             = isset( $_POST[ 'Date' ] )        ? $_POST[ 'Date' ]         : $Proposal[ 'Date' ];
          $Proposal[ 'Type' ]             = isset( $_POST[ 'Type' ] )        ? $_POST[ 'Type' ]         : $Proposal[ 'Type' ];
          $Proposal[ 'Notes' ]            = isset( $_POST[ 'Notes' ] )       ? $_POST[ 'Notes' ]        : $Proposal[ 'Notes' ];
          $Proposal[ 'Probability' ]      = isset( $_POST[ 'Probability' ] ) ? $_POST[ 'Probability' ]  : $Proposal[ 'Probability' ];
          $Proposal[ 'Cost' ]             = isset( $_POST[ 'Cost' ] )        ? $_POST[ 'Cost' ]         : $Proposal[ 'Cost' ];
          $Proposal[ 'Hours' ]            = isset( $_POST[ 'Hours' ] )       ? $_POST[ 'Hours' ]        : $Proposal[ 'Hours' ];
          $Proposal[ 'Labor' ]            = isset( $_POST[ 'Labor' ] )       ? $_POST[ 'Labor' ]        : $Proposal[ 'Labor' ];
          $Proposal[ 'Overhead' ]         = isset( $_POST[ 'Overhead' ] )    ? $_POST[ 'Overhead' ]     : $Proposal[ 'Overhead' ];
          $Proposal[ 'Price' ]            = isset( $_POST[ 'Price' ] )       ? $_POST[ 'Price' ]        : $Proposal[ 'Price' ];
          $Proposal[ 'Profit' ]           = isset( $_POST[ 'Profit' ] )      ? $_POST[ 'Profit' ]       : $Proposal[ 'Profit' ];
          $Proposal[ 'Notes' ]            = isset( $_POST[ 'Remarks' ] )     ? $_POST[ 'Remarks' ]      : $Proposal[ 'Notes' ];
          /*$Proposal[ 'Sales_Tax_Rate' ]      = isset( $_POST[ 'Sales_Tax_Rate' ] )    ? $_POST[ 'Sales_Tax_Rate' ]    : $Proposal[ 'Sales_Tax_Rate' ];
          $Proposal[ 'Sales_Tax' ]      = isset( $_POST[ 'Sales_Tax' ] )    ? $_POST[ 'Sales_Tax' ]    : $Proposal[ 'Sales_Tax' ];*/
          if( empty( $_POST[ 'ID' ] ) ){

            $result = \singleton\database::getInstance( )->query(
              null,
              " DECLARE @MAXID INT;
                SET @MAXID = CASE WHEN ( SELECT Max( ID ) FROM Estimate ) IS NULL THEN 0 ELSE ( SELECT Max( ID ) FROM Estimate ) END ;
                INSERT INTO Estimate(
                  ID,
                  Job,
                  RolID,
                  LocID,
                  EmpID,
                  Name,
                  fDesc,
                  fDate,
                  Type,
                  Remarks,
                  Category,
                  fFor,
                  Cost,
                  Hours,
                  Labor,
                  Overhead,
                  Price,
                  Profit
                )
                VALUES ( @MAXID + 1 , ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? );
                SELECT @MAXID + 1;",
              array(
                !empty( $Proposal[ 'Job_ID' ] )       ? $Proposal[ 'Job_ID' ] : null,
                !empty( $Proposal[ 'Contact_ID' ] )   ? $Proposal[ 'Contact_ID' ] : null,
                !empty( $Proposal[ 'Location_ID' ] )  ? $Proposal[ 'Location_ID' ] : null,
                !empty( $Proposal[ 'Employee_ID' ] )  ? $Proposal[ 'Employee_ID' ] : null,
                !empty( $Proposal[ 'Name' ] )         ? $Proposal[ 'Name' ] : null,
                !empty( $Proposal[ 'Description' ] )  ? $Proposal[ 'Description' ] : null,
                !empty( $Proposal[ 'Date' ] )         ? $Proposal[ 'Date' ] : null,
                !empty( $Proposal[ 'Type' ] )         ? $Proposal[ 'Type' ] : null,
                !empty( $Proposal[ 'Notes' ] )        ? $Proposal[ 'Notes' ] : null,
                !empty( $Proposal[ 'Category' ] )     ? $Proposal[ 'Category' ] : null,
                !empty( $Proposal[ 'For' ] )          ? $Proposal[ 'For' ] : null,
                !empty( $Proposal[ 'Cost' ] )         ? $Proposal[ 'Cost' ] : null,
                !empty( $Proposal[ 'Hours' ] )        ? $Proposal[ 'Hours' ] : null,
                !empty( $Proposal[ 'Labor' ] )        ? $Proposal[ 'Labor' ] : null,
                !empty( $Proposal[ 'Overhead' ] )     ? $Proposal[ 'Overhead' ] : null,
                !empty( $Proposal[ 'Price' ] )        ? $Proposal[ 'Price' ] : null,
                !empty( $Proposal[ 'Profit' ] )       ? $Proposal[ 'Profit' ] : null
              )
            );
            sqlsrv_next_result( $result );
            $Proposal[ 'ID' ] = sqlsrv_fetch_array( $result )[ 0 ];
            header( 'Location: proposal.php?ID=' . $Proposal[ 'ID' ] );
            exit;
          } else {
            \singleton\database::getInstance( )->query(
              null,
              " UPDATE  Estimate
                SET     Estimate.Job = ?,
                        Estimate.RolID = ?,
                        Estimate.LocID = ?,
                        Estimate.EmpID = ?,
                        Estimate.fDesc = ?,
                        Estimate.Name = ?,
                        Estimate.fDate = ?,
                        Estimate.Type = ?,
                        Estimate.Remarks = ?,
                        Estimate.Cost = ?,
                        Estimate.Hours = ?,
                        Estimate.Labor = ?,
                        Estimate.Overhead = ?,
                        Estimate.Price = ?,
                        Estimate.Profit = ?
                WHERE   Estimate.ID = ?;",
                array(
                  $Proposal[ 'Job_ID' ],
                  $Proposal[ 'Contact_ID' ],
                  $Proposal[ 'Location_ID' ],
                  $Proposal[ 'Employee_ID'],
                  $Proposal[ 'Description' ],
                  $Proposal[ 'Name' ],
                  $Proposal[ 'Date' ],
                  $Proposal[ 'Type' ],
                  $Proposal[ 'Notes' ],
                  !empty($Proposal['Cost']) ?  $Proposal[ 'Cost' ] : null,
                  !empty($Proposal['Hours']) ?  $Proposal[ 'Hours' ] : null,
                  !empty($Proposal['Labor']) ?  $Proposal[ 'Labor' ] : null,
                  !empty($Proposal['Overhead']) ?  $Proposal[ 'Overhead' ] : null,
                  !empty($Proposal['Price']) ?  $Proposal[ 'Price' ] : null,
                  !empty($Proposal['Profit']) ?  $Proposal[ 'Profit' ] : null,
                  $Proposal[ 'ID' ]
                )
              );
            }
          }
?><!DOCTYPE html>
<html lang="en">
<head>
  <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
  <?php
    $_GET[ 'Bootstrap' ] = '5.1';
    $_GET[ 'Entity_CSS' ] = 1;
    require( bin_meta . 'index.php');
    require( bin_css  . 'index.php');
    require( bin_js   . 'index.php');
  ?>
</head>
<body>
    <div id="wrapper">
        <?php require(bin_php.'element/navigation.php');?>
        <div id="page-wrapper" class='content'>
            <div class='no-print'>
                <div class='card card-primary'>
                  <form action='proposal.php?ID=<?php echo $Proposal[ 'ID' ];?>' method='POST'>
                    <input type='hidden' name='ID' value='<?php echo $Proposal[ 'ID' ];?>' />
                    <?php \singleton\bootstrap::getInstance( )->primary_card_header( 'Proposal', 'Proposals', $Proposal[ 'ID' ] );?>
                    <div class='card-body bg-dark text-white'>
                      	<div class='row g-0' data-masonry='{"percentPosition": true }'>
                      		<div class='card card-primary my-3 col-12 col-lg-3'>
                      			<?php \singleton\bootstrap::getInstance( )->card_header( 'Information' ); ?>
                      			<div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Infomation' ] ) && $_SESSION[ 'Cards' ][ 'Infomation' ] == 0 ? "style='display:none;'" : null;?>>
                      				<?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Name', $Proposal[ 'Name' ] );?>
                      				<?php
					                	$result = \singleton\database::getInstance( )->query(
					                	  null,
					                	  " SELECT  Job_Type.ID   AS ID,
					                	            Job_Type.Type AS Name
					                	    FROM    JobType AS Job_Type;"
					                	);
					                	if( $result ){while ( $row = sqlsrv_fetch_array( $result ) ){ $Job_Types[ $row[ 'ID' ] ] = $row[ 'Name' ]; } }
					                	\singleton\bootstrap::getInstance( )->card_row_form_select( 'Type', $Proposal[ 'Type' ], $Job_Types );
					             	?>
					             	<?php \singleton\bootstrap::getInstance( )->card_row_form_input_date( 'Date', $Proposal[ 'Date' ] );?>
                            		<?php \singleton\bootstrap::getInstance( )->card_row_form_autocomplete( 'Contact', 'Contacts', $Proposal[ 'Contact_ID' ], $Proposal[ 'Contact_Name' ] );?>
                            		<?php \singleton\bootstrap::getInstance( )->card_row_form_autocomplete( 'Job', 'Jobs', $Proposal[ 'Job_ID' ], $Proposal[ 'Job_Name' ] );?>
                            		<?php \singleton\bootstrap::getInstance( )->card_row_form_autocomplete( 'Location', 'Locations', $Proposal[ 'Location_ID' ], $Proposal[ 'Location_Name' ] );?>
                            		<?php \singleton\bootstrap::getInstance( )->card_row_form_textarea( 'Notes', $Proposal[ 'Notes' ] );?>
                            		<?php \singleton\bootstrap::getInstance( )->card_row_form_input_number( 'Hours', $Proposal[ 'Hours' ] );?>
                        		</div>
                        	</div>
	                        <div class='card card-primary my-3 col-3'>
	                        	<?php \singleton\bootstrap::getInstance( )->card_header( 'Accounting' ); ?>
	                        	<div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Billing' ] ) && $_SESSION[ 'Cards' ][ 'Billing' ] == 0 ? "style='display:none;'" : null;?>>
	                        		<?php \singleton\bootstrap::getInstance( )->card_row_form_input_currency( 'Cost', $Proposal[ 'Cost' ] );?>
	                        		<?php \singleton\bootstrap::getInstance( )->card_row_form_input_currency( 'Labor', $Proposal[ 'Labor' ] );?>
	                        		<?php \singleton\bootstrap::getInstance( )->card_row_form_input_currency( 'Overhead', $Proposal[ 'Overhead' ] );?>
	                        		<?php \singleton\bootstrap::getInstance( )->card_row_form_input_currency( 'Price', $Proposal[ 'Price' ] );?>
	                        	</div>
	                      	</div>
	                    </div>
	                </div>
                </form>
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
	                    <div class='col-12' style='b'><h3 style='text-align:center;margin:0px;padding:5px;'>Proposal #<?php echo $Proposal[ 'ID' ];?></h3></div>
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
	                    <div class='col-4'><?php echo $Proposal[ 'Contact' ];?></div>
	                    <div class='col-6'>PROPOSAL #<?php echo $Proposal[ 'ID' ];?></div>
	                </div>
	                <div lcass='row'>
	                    <div class='col-2'>PHONE:</div>
	                    <div class='col-4'><?php echo $Proposal[ 'Phone' ];?></div>
	                    <div class='col-6'><?PHP echo date( 'm/d/Y', strtotime( $Proposal[ 'Date' ] ) );?></div>
	                </div>
	                <div class='row'>
	                    <div class='col-2'>FAX:</div>
	                    <div class='col-4'><?php echo $Proposal[ 'Fax' ];?></div>
	                    <div class='col-6'>&nbsp;</div>
	                </div>
	                <div class='row'>
	                    <div class='col-2'>EMAIL:</div>
	                    <div class='col-4'><?php echo $Proposal[ 'Email' ];?></div>
	                    <div class='col-6'>&nbsp;</div>
	                </div>
	                <div class='row'>
	                    <div class='col-2'>FROM:</div>
	                    <div class='col-4'><?php /*INSERT FROM HERE*/?></div>
	                    <div class='col-6'>&nbsp;</div>
	                </div>
	                <div class='row'>
	                    <div class='col-2'>PREMISE:</div>
	                    <div class='col-4'><?php echo $Proposal[ 'Location' ];?></div>
	                    <div class='col-6'>&nbsp;</div>
	                </div>
	                <div class='row'>
	                    <div class='col-2'>CUSTOMER:</div>
	                    <div class='col-4'><?php echo $Proposal[ 'Customer' ];?></div>
	                    <div class='col-6'>&nbsp;</div>
	                </div>
	                <div class='row'>
	                    <div class='col-2'>RE:</div>
	                    <div class='col-4'><?php echo $Proposal[ 'Title'];?></div>
	                    <div class='col-6'>&nbsp;</div>
	                </div>
	                <div class='row' style='border-top:1px solid black;margin-top:5px;margin-bottom:5px;height:5px;'>&nbsp;</div>
	                <div class='row'>
	                    <div class='col-12' style='text-align:center;'><u>WORK DESCRIPTION</u></div>
	                </div>
	                <div class='row'>
	                    <div class='col-12'><pre style='padding:25px;font-size:18px;'><?php echo $Proposal[ 'Remarks' ];?></pre></div>
	                </div>
	                <div class='row'>
	                    <div class='col-3'>COST NOT TO EXCEED:</div>
	                    <div class='col-9'>$<?php echo number_format( $Proposal[ 'Price' ], 2 );?> - PLUS ANY APPlICABLE TAXES</div>
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
    </div>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=proposal<?php echo (!isset($Proposal[ 'ID' ]) || !is_numeric($Proposal[ 'ID' ])) ? "s.php" : ".php?ID={$Proposal[ 'ID' ]}";?>";</script></head></html><?php }?>
