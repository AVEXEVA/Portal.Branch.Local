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
    if(   !isset( $Connection[ 'ID' ] )
        ||  !isset( $Privileges[ 'Lead' ] )
        ||  !check( privilege_read, level_group, $Privileges[ 'Lead' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
      $ID = isset( $_GET[ 'ID' ] )
      ? $_GET[ 'ID' ]
      : (
        isset( $_POST[ 'ID' ] )
          ? $_POST[ 'ID' ]
          : null
      );
      $Name = isset( $_GET[ 'Name' ] )
          ? $_GET[ 'Name' ]
          : (
            isset( $_POST[ 'Name' ] )
              ? $_POST[ 'Name' ]
              : null
      );
    	$result = \singleton\database::getInstance( )->query(
    		null,
    		"SELECT Lead.ID       AS ID,
				    Lead.fDesc        AS Name,
				    Lead.Address      AS Street,
				    Lead.City         AS City,
				    Lead.State        AS State,
				    Lead.Zip          AS Zip,
				    Customer.ID 	    AS Customer_ID,
				    Customer.Name 	  AS Customer_Name
			FROM     Lead
					  LEFT JOIN (
                SELECT  Owner.ID,
                        Rol.Name,
                        Owner.Status
                FROM    Owner
                        LEFT JOIN Rol ON Owner.Rol = Rol.ID
            ) AS Customer ON Lead.Owner = Customer.ID
			WHERE Lead.ID = ?",
    		array( 
          $ID,
          $Name
        )
    	);
    	$Lead = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC );
?><!DOCTYPE html>
<html lang="en" style="min-height:100%;height:100%;webkit-background-size: cover;-moz-background-size: cover;-o-background-size: cover;background-size: cover;height:100%;">
<head>
  <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
     <?php  $_GET[ 'Bootstrap' ] = '5.1';?>
     <?php  $_GET[ 'Entity_CSS' ] = 1;?>
     <?php  require( bin_meta . 'index.php');?>
     <?php  require( bin_css  . 'index.php');?>
     <?php  require( bin_js   . 'index.php');?>
    <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyCNrTryEaTEDRz-XDSg890ajL_JRPnLgzc"></script>
</head>
<body onload='finishLoadingPage();'>
  <div id="wrapper">
    <?php require( bin_php . 'element/navigation.php'); ?>
    <div id="page-wrapper" class='content'>
      <div class='card card-primary'>
        <div class='card-heading'>
          <div class='row g-0 px-3 py-2'>
            <div class='col-6'><h5><?php \singleton\fontawesome::getInstance( )->Customer( 1 );?><a href='leads.php?<?php echo isset( $_SESSION[ 'Tables' ][ 'Customer' ][ 0 ] ) ? http_build_query( is_array( $_SESSION[ 'Tables' ][ 'Leads' ][ 0 ] ) ? $_SESSION[ 'Tables' ][ 'Leads' ][ 0 ] : array( ) ) : null;?>'>Lead</a>: <span><?php echo is_null( $Lead[ 'ID' ] ) ? 'New' : $Lead[ 'Name' ];?></span></h5></div>
            <div class='col-2'></div>
            <div class='col-2'>
              <div class='row g-0'>
                <div class='col-4'><button class='form-control rounded' onClick="document.location.href='lead.php';">Create</button></div>
                <div class='col-4'><button class='form-control rounded' onClick="document.location.href='lead.php?ID=<?php echo $Lead[ 'ID' ];?>';">Refresh</button></div>
              </div>
            </div>
            <div class='col-2'>
              <div class='row g-0'>
                <div class='col-4'><button class='form-control rounded' onClick="document.location.href='lead.php?ID=<?php echo !is_null( $Lead[ 'ID' ] ) ? array_keys( $_SESSION[ 'Tables' ][ 'Leads' ], true )[ array_search( $Lead[ 'ID' ], array_keys( $_SESSION[ 'Tables' ][ 'Leads' ], true ) ) - 1 ] : null;?>';">Previous</button></div>
                <div class='col-4'><button class='form-control rounded' onClick="document.location.href='leads.php?<?php echo http_build_query( is_array( $_SESSION[ 'Tables' ][ 'Leads' ][ 0 ] ) ? $_SESSION[ 'Tables' ][ 'Leads' ][ 0 ] : array( ) );?>';">Table</button></div>
                <div class='col-4'><button class='form-control rounded' onClick="document.location.href='lead.php?ID=<?php echo !is_null( $Lead[ 'ID' ] )? array_keys( $_SESSION[ 'Tables' ][ 'Leads' ], true )[ array_search( $Lead[ 'ID' ], array_keys( $_SESSION[ 'Tables' ][ 'Leads' ], true ) ) + 1 ] : null;?>';">Next</button></div>
              </div>
            </div>
          </div>
        </div>
        <div class='card-body bg-dark text-white'>
          <div class='card-columns'>
        		<div class='card card-primary my-3'><form action='lead.php?ID=<?php echo $Lead[ 'ID' ];?>' method='POST'>
              <div class='card-heading'>
                <div class='row g-0 px-3 py-2'>
                  <div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Info( 1 );?><span>Infomation</span></h5></div>
                  <div class='col-2'>&nbsp;</div>
                </div>
              </div>
              <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Infomation' ] ) && $_SESSION[ 'Cards' ][ 'Infomation' ] == 0 ? "style='display:none;'" : null;?>>
                <input type='hidden' name='ID' value='<?php echo $Lead[ 'ID' ];?>' />
                <div class='row g-0'>
                  <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> ID:</div>
                  <div class='col-8'><input type='text' class='form-control edit animation-focus' name='ID' value='<?php echo $Lead['ID'];?>' /></div>
                </div>
                <div class='row g-0'>
                  <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Name:</div>
                  <div class='col-8'><input type='text' class='form-control edit animation-focus' name='Name' value='<?php echo $Lead['Name'];?>' /></div>
                </div>
                <div class='row g-0'>
                  <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Customer(1);?> Customer:</div>
                  <div class='col-6'>
                    <input type='text' autocomplete='off' class='form-control edit' name='Customer' value='<?php echo $Lead[ 'Customer_Name' ];?>' />
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
                                      url : 'bin/php/get/search/Locations.php',
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
                    if( in_array( $Lead[ 'Customer_ID' ], array( null, 0, '', ' ') ) ){
                      echo "onClick=\"document.location.href='customers.php';\"";
                    } else {
                      echo "onClick=\"document.location.href='customer.php?ID=" . $Lead[ 'Customer_ID' ] . "';\"";
                    }
                  ?>><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
              </div>
        			</div>
        		</form></div>
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