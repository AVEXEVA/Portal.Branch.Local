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
                        Estimate.fDesc          AS  Name,
                        Estimate.Name           AS  Contact,
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
        $Proposal =   (       empty( $ID )
                        &&    !empty( $Name )
                        &&    !$result
                      ) || (  empty( $ID )
                        &&    empty( $Name )
                      )
            ? array(
                'ID' => null,
                'Name' => null,
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
                'Job_ID' => null,
                'Job_Name' => null,
                'EstTemplate' => null,
                'Sales_Tax_Rate' => null,
                'Sales_Tax' => null,
                'Sales_Expense' => null,
                'Quoted' => null,
                'Phase' => null,
                'Probability' => null,
                'Location_ID' => nll,
                'Location_Name' => null,
                'Street' => null,
                'State' => null,
                'City' => null,
                'Zip' => null,
                'Customer_ID' => null,
                'Customer_Name' => null,
                'Fax' => null,
                'Phone' => null,
                'Email' => null
            )
            : sqlsrv_fetch_array($result);

        if( isset( $_POST ) && count( $_POST ) > 0 ){
          $Proposal[ 'Name' ]             = isset( $_POST[ 'Name' ] )        ? $_POST[ 'Name' ]         : $Proposal[ 'Name' ];
          $Proposal[ 'Contact' ]          = isset( $_POST[ 'Contact' ] )     ? $_POST[ 'Contact' ]      : $Proposal[ 'Contact' ];
          $Proposal[ 'Job_Name' ]         = isset( $_POST[ 'Job' ] )         ? $_POST[ 'Job' ]          : $Proposal[ 'Job_Name' ];
          $Proposal[ 'Location_Name' ]    = isset( $_POST[ 'Location' ] )    ? $_POST[ 'Location' ]     : $Proposal[ 'Location_Name' ];
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
          /*$Proposal[ 'Sales_Tax_Rate' ]      = isset( $_POST[ 'Sales_Tax_Rate' ] )    ? $_POST[ 'Sales_Tax_Rate' ]    : $Proposal[ 'Sales_Tax_Rate' ];
          $Proposal[ 'Sales_Tax' ]      = isset( $_POST[ 'Sales_Tax' ] )    ? $_POST[ 'Sales_Tax' ]    : $Proposal[ 'Sales_Tax' ];*/
          
          if( empty( $_POST[ 'ID' ] ) ){

            $result = \singleton\database::getInstance( )->query(
              null,
              " DECLARE @MAXID INT;
                DECLARE @Job INT;
                DECLARE @Contact INT;
                SET @MAXID = CASE WHEN ( SELECT Max( ID ) FROM Estimate ) IS NULL THEN 0 ELSE ( SELECT Max( ID ) FROM Estimate ) END ;
                SET @Job = ( SELECT Top 1 Job.ID FROM Job WHERE Job.fDesc = ? );
                SET @Contact = ( SELECT Top 1 Rol.ID FROM Rol WHERE Rol.Contact = ? );
                SET @Location = ( SELECT Top 1 Loc.Loc FROM Loc WHERE Loc.Tag = ? );
                INSERT INTO Estimate(
                  ID,
                  Job,
                  RolID,
                  LocID,
                  fDesc,
                  fDate,
                  Type,
                  Remarks,
                  Cost,
                  Hours,
                  Labor,
                  Overhead,
                  Price,
                  Profit
                )
                VALUES ( @MAXID + 1, @Job, @Contact, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? );
                SELECT @MAXID + 1;",
              array(
                $Proposal[ 'Job_Name' ],
                $Proposal[ 'Contact' ],
                $Proposal[ 'Location_Name' ],
                $Proposal[ 'Name' ],
                $Proposal[ 'Date' ],
                $Proposal[ 'Type' ],
                $Proposal[ 'Remarks' ],
                $Proposal[ 'Notes' ],
                $Proposal[ 'Cost' ],
                $Proposal[ 'Hours' ],
                $Proposal[ 'Labor' ],
                $Proposal[ 'Overhead' ],
                $Proposal[ 'Price' ],
                $Proposal[ 'Profit' ]
              )
            );
            sqlsrv_next_result( $result );
            $Proposal[ 'ID' ] = sqlsrv_fetch_array( $result )[ 0 ];

            header( 'Location: proposal.php?ID=' . $Proposal[ 'ID' ] );
            exit;
          } else {
            \singleton\database::getInstance( )->query(
              null,
              " DECLARE @Job INT;
                DECLARE @Contact INT;
                DECLARE @Location INT;
                SET @Job = ( SELECT Top 1 Job.ID FROM Job WHERE Job.fDesc = ? );
                SET @Contact = ( SELECT Top 1 Rol.ID FROM Rol WHERE Rol.Contact = ? );
                SET @Location = ( SELECT Top 1 Loc.Loc FROM Loc WHERE Loc.Tag = ? );
                UPDATE  Estimate
                SET     Estimate.Job = @Job,
                        Estimate.RolID = @Contact,
                        Estimate.LocID = @Location,
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
                $Proposal[ 'Job_Name' ],
                $Proposal[ 'Contact' ],
                $Proposal[ 'Location_Name' ],
                $Proposal[ 'Name' ],
                $Proposal[ 'Contact' ],
                $Proposal[ 'Date' ],
                $Proposal[ 'Type' ],
                $Proposal[ 'Notes' ],
                $Proposal[ 'Cost' ],
                $Proposal[ 'Hours' ],
                $Proposal[ 'Labor' ],
                $Proposal[ 'Overhead' ],
                $Proposal[ 'Price' ],
                $Proposal[ 'Profit' ],
                $Proposal[ 'ID' ]
              )
            );
          }
        }
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
                              <h5><?php \singleton\fontawesome::getInstance( )->Proposal( 1 );?><a href='proposals.php?<?php
                                echo http_build_query( is_array( $_SESSION[ 'Tables' ][ 'Proposals' ][ 0 ] ) ? $_SESSION[ 'Tables' ][ 'Proposals' ][ 0 ] : array( ) );
                              ?>'>Proposal</a>: <span><?php
                                echo is_null( $Proposal[ 'ID' ] )
                                  ? 'New'
                                  : $Proposal[ 'Name' ];
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
                        <div class='card card-primary my-3'><form action='proposal.php?ID=<?php echo $Proposal[ 'ID' ];?>' method='POST'>
                          <input type='hidden' name='ID' value='<?php echo $Proposal[ 'ID' ];?>' />
                          <div class='card-heading'>
                            <div class='row g-0 px-3 py-2'>
                              <div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Info( 1 );?><span>Infomation</span></h5></div>
                              <div class='col-2'>&nbsp;</div>
                            </div>
                          </div>
                          <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Infomation' ] ) && $_SESSION[ 'Cards' ][ 'Infomation' ] == 0 ? "style='display:none;'" : null;?>>
                            <div class='row g-0'>
                              <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Proposal(1);?>Name:</div>
                              <div class='col-8'><input type='text' class='form-control edit' name='Name' value='<?php echo $Proposal['Name'];?>' /></div>
                            </div>
                            <div class='row g-0'>
                              <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank(1);?>Type:</div>
                              <div class='col-8'><select class='form-control edit' name='Type'>
                                <option value=''>Select</option>
                                <?php
                                  $result = \singleton\database::getInstance( )->query(
                                    null,
                                    " SELECT  Job_Type.ID   AS ID,
                                              Job_Type.Type AS Name
                                      FROM    JobType AS Job_Type;"
                                  );
                                  if( $result ){while ( $row = sqlsrv_fetch_array( $result ) ){
                                    ?><option value='<?php echo $row[ 'ID' ];?>' <?php echo $row[ 'ID' ] == $Proposal[ 'Type' ] ? 'selected' : null;?>><?php echo $row[ 'Name' ];?></option><?php
                                  }}
                                ?>
                              </select></div>
                            </div>
                            <div class='row g-0'>
                              <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Calendar(1);?>Date:</div>
                              <div class='col-8'><input type='text' autocomplete='off' class='form-control edit date' name='Date' value='<?php echo !is_null( $Proposal[ 'Date' ] ) ? date( 'm/d/Y', strtotime( $Proposal['Date'] ) ) : null;?>' /></div>
                            </div>
                            <div class='row g-0'>
                              <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Proposal(1);?>Description:</div>
                              <div class='col-8'><textarea class='form-control edit' name='Description' rows='8' value='<?php echo $Proposal['Description'];?>'></textarea></div>
                            </div>
                            <div class='row g-0'>
                              <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->User(1);?> Contact:</div>
                              <div class='col-6'>
                                <input type='text' autocomplete='off' class='form-control edit' name='Contact' value='<?php echo $Proposal[ 'Contact' ];?>' />
                                <script>
                                  $( 'input[name="Contact"]' )
                                      .typeahead({
                                          minLength : 4,
                                          hint: true,
                                          highlight: true,
                                          limit : 5,
                                          display : 'FieldValue',
                                          source: function( query, result ){
                                              $.ajax({
                                                  url : 'bin/php/get/search/Contacts.php',
                                                  method : 'GET',
                                                  data    : {
                                                      search :  $('input:visible[name="Contact"]').val( )
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
                                              $( 'input[name="Contact"]').val( value );
                                              $( 'input[name="Contact"]').closest( 'form' ).submit( );
                                          }
                                      }
                                  );
                                </script>
                              </div>
                              <div class='col-2'><button class='h-100 w-100' type='button' <?php
                                if( in_array( $Proposal[ 'Contact' ], array( null, 0, '', ' ') ) ){
                                  echo "onClick=\"document.location.href='contacts.php';\"";
                                } else {
                                  echo "onClick=\"document.location.href='contact.php?Contact=" . $Proposal[ 'Contact' ] . "';\"";
                                }
                              ?>><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                            </div>
                            <div class='row g-0'>
                              <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Job(1);?> Job:</div>
                              <div class='col-6'>
                                <input type='text' autocomplete='off' class='form-control edit' name='Job' value='<?php echo $Proposal[ 'Job_Name' ];?>' />
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
                                if( in_array( $Proposal[ 'Job_ID' ], array( null, 0, '', ' ') ) ){
                                  echo "onClick=\"document.location.href='jobs.php';\"";
                                } else {
                                  echo "onClick=\"document.location.href='job.php?Name=" . $Proposal[ 'Job_Name' ] . "';\"";
                                }
                              ?>><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                          </div>
                          <div class='row g-0'>
                              <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Proposal(1);?> Location:</div>
                              <div class='col-6'>
                                <input type='text' autocomplete='off' class='form-control edit' name='Location' value='<?php echo $Proposal[ 'Location_Name' ];?>' />
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
                                if( in_array( $Proposal[ 'Location_ID' ], array( null, 0, '', ' ') ) ){
                                  echo "onClick=\"document.location.href='locations.php';\"";
                                } else {
                                  echo "onClick=\"document.location.href='location.php?Name=" . $Proposal[ 'Location_Name' ] . "';\"";
                                }
                              ?>><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                          </div>
                        </div>
                        <div class='card-footer'>
                          <div class='row'>
                              <div class='col-12'><button class='form-control' type='submit'>Save</button></div>
                          </div>
                        </div>
                      </form></div>
                      <div class='card card-primary my-3'><form action='proposal.php?ID=<?php echo $Proposal[ 'ID' ];?>' method='POST'>
                        <input type='hidden' name='ID' value='<?php echo $Proposal[ 'ID' ];?>' />
                        <div class='card-heading'>
                          <div class='row g-0 px-3 py-2'>
                            <div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Info( 1 );?><span>Infomation</span></h5></div>
                            <div class='col-2'>&nbsp;</div>
                          </div>
                        </div>
                        <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Infomation' ] ) && $_SESSION[ 'Cards' ][ 'Infomation' ] == 0 ? "style='display:none;'" : null;?>>
                          <div class='row g-0'>
                            <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Dollar(1);?>Cost:</div>
                            <div class='col-8'><input type='text' class='form-control edit' rows='8' name='Cost' value='<?php echo $Proposal['Cost'];?>' /></div>
                          </div>
                          <div class='row g-0'>
                            <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Dollar(1);?>Hours:</div>
                            <div class='col-8'><input type='text' class='form-control edit' rows='8' name='Hours' value='<?php echo $Proposal['Hours'];?>' /></div>
                          </div>
                          <div class='row g-0'>
                            <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Dollar(1);?>Labor:</div>
                            <div class='col-8'><input type='text' class='form-control edit' rows='8' name='Labor' value='<?php echo $Proposal['Labor'];?>' /></div>
                          </div>
                          <div class='row g-0'>
                            <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Dollar(1);?>Overhead:</div>
                            <div class='col-8'><input type='text' class='form-control edit' rows='8' name='Overhead' value='<?php echo $Proposal['Overhead'];?>' /></div>
                          </div>
                          <div class='row g-0'>
                            <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Dollar(1);?>Price:</div>
                            <div class='col-8'><input type='text' class='form-control edit' rows='8' name='Price' value='<?php echo $Proposal['Price'];?>' /></div>
                          </div>
                        </div>
                        <div class='card-footer'>
                            <div class='row'>
                                <div class='col-12'><button class='form-control' type='submit'>Save</button></div>
                            </div>
                        </div>
                      </form></div>
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
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=proposal<?php echo (!isset($Proposal[ 'ID' ]) || !is_numeric($Proposal[ 'ID' ])) ? "s.php" : ".php?ID={$Proposal[ 'ID' ]}";?>";</script></head></html><?php }?>
