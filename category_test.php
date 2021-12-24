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
        ||  !isset( $Privileges[ 'User' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'User' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        \singleton\database::getInstance( )->query(
          null,
          " INSERT INTO Activity([User], [Date], [Page] )
            VALUES( ?, ?, ? );",
          array(
            $_SESSION[ 'Connection' ][ 'User' ],
            date('Y-m-d H:i:s'),
            'category_test.php'
        )
      );
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
      " SELECT  Top 1
                TestCategory.ID,
                TestCategory.Name
                FROM TestCategory
                WHERE TestCategory.ID = ?;",
      array(
        $ID
      )
    );
    $TestCategory = empty( $ID ) ? array(
      'ID' => null,
      'Name'=> null,
    ) : sqlsrv_fetch_array($result);
    
    
    if( isset( $_POST ) && count( $_POST ) > 0 ){
     echo $TestCategory[ 'Name' ] = isset( $_POST[ 'Name' ] )? $_POST[ 'Name' ]: $TestCategory[ 'Name' ];
      $TestCategory[ 'ID' ] = isset( $_POST[ 'ID' ] )? $_POST[ 'ID' ]: $TestCategory[ 'ID' ];
     
    

      if( in_array( $_POST[ 'ID' ], array( null, 0, '', ' ' ) ) ){
        $result = \singleton\database::getInstance( )->query(
          null,
          "INSERT INTO TestCategory(
              Name
            )
            VALUES( ? );
            SELECT Max( ID ) FROM dbo.[TestCategory];",
          array(
            $TestCategory[ 'Name' ]
          )
        )or die(print_r(sqlsrv_errors()));
        sqlsrv_next_result( $result );
        $TestCategory[ 'ID' ] = sqlsrv_fetch_array( $result )[ 0 ];
        header( 'Location: category_test.php?ID=' . $TestCategory[ 'ID' ] );
        exit;
      } else {
      ;
        \singleton\database::getInstance( )->query(
          null,
          " UPDATE  TestCategory
            SET     TestCategory.Name = ?             
            WHERE   TestCategory.ID = ?;",
          array(
            $TestCategory[ 'Name' ],
            $TestCategory[ 'ID' ]
          )
        )or die(print_r(sqlsrv_errors()));;
      }
    }
?><!DOCTYPE html>
<html lang='en'>
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
    <?php require( bin_php . 'element/navigation.php' ); ?>
    <div id="page-wrapper" class='content'>
      <div class='card card-primary'><form action='category_test.php?ID=<?php echo $TestCategory[ 'ID' ];?>' method='POST'>
        <input type='hidden' name='ID' value='<?php echo $TestCategory[ 'ID' ];?>' />
        <?php \singleton\bootstrap::getInstance( )->primary_card_header( 'category_test', 'Category_Tests', $TestCategory[ 'ID' ] );?>
        <div class='card-body bg-dark text-white'>
          <div class='row g-0' data-masonry='{"percentPosition": true }'>
            <div class='card card-primary my-3 col-12 col-lg-3'>
              <?php \singleton\bootstrap::getInstance( )->card_header( 'Information' ); ?>
              <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Infomation' ] ) && $_SESSION[ 'Cards' ][ 'Infomation' ] == 0 ? "style='display:none;'" : null;?>>
                <?php
                  \singleton\bootstrap::getInstance( )->card_row_form_input( 'Name', $TestCategory[ 'Name' ] );
                            
                ?>
              </div>
            </div>
          </div>
        </div><
      </form></div>
    </div>
  </div>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=category_test<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
