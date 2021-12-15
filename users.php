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
    $Check = check(
        privilege_read,
        level_group,
        isset( $Privileges[ 'User' ] )
            ? $Privileges[ 'User' ]
            : 0
    );

    if( !isset($Connection['ID'])  || !$Check ){?><html><head><script>document.location.href='../login.php?Forward=users.php';</script></head></html><?php }
    else {
      \singleton\database::getInstance( )->query(
        null,
    	    	"	INSERT INTO Activity( [User], [Date], [Page] )
              VALUES( ?, ?, ? );",
    	    	array(
    	    		$_SESSION[ 'Connection' ][ 'User' ],
    	    		date( 'Y-m-d H:i:s' ),
    	    		           'users.php'
    	    	)
    	    );
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
    <div id="wrapper">
        <?php require( bin_php . 'element/navigation.php');?>
        <div id="page-wrapper" class='content'>
            <div class="card card-full card-primary border-0">
                <div class="card-heading"><h4><?php \singleton\fontawesome::getInstance( )->Users( 1 );?> Users</h4></div>
                  <div class="mobile card-body bg-dark  text-white"><form action='users.php'>
                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'ID', isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null, false, false, false, 'redraw' );?>
                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Email', isset( $_GET[ 'Email' ] ) ? $_GET[ 'Email' ] : null, false, false, false, 'redraw' );?>
                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Name', isset( $_GET[ 'Name' ] ) ? $_GET[ 'Name' ] : null, false, false, false, 'redraw' );?>
                  </div>
                    <div class='card-body bg-dark'>
                      <table id='Table_Users' class='display' cellspacing='0' width='100%'>
                        <thead class='text-white border border-white'><?php
                          \singleton\table::getInstance( )->th( 'ID', 'ID' );
                          \singleton\table::getInstance( )->th( 'Email', 'Email' );
                          \singleton\table::getInstance( )->th( 'Verified', 'Verified' );
                          \singleton\table::getInstance( )->th( 'Branch', 'Branch' );
                          \singleton\table::getInstance( )->th( 'Branch_Type', 'Branch_Type' );
                          \singleton\table::getInstance( )->th( 'Branch_ID', 'Branch_ID' );
                          \singleton\table::getInstance( )->th( 'Picture', 'Picture' );
                        ?><tr class='desktop'><?php
                        \singleton\table::getInstance( )->th_input( 'ID', isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null );
                        \singleton\table::getInstance( )->th_input( 'Email', isset( $_GET[ 'Email' ] ) ? $_GET[ 'Email' ] : null );
                        \singleton\table::getInstance( )->th_input( 'Verified', isset( $_GET[ 'Verified' ] ) ? $_GET[ 'Verified' ] : null );
                        \singleton\table::getInstance( )->th_input( 'Branch', isset( $_GET[ 'Branch' ] ) ? $_GET[ 'Branch' ] : null );
                        \singleton\table::getInstance( )->th_input( 'Branch_Type', isset( $_GET[ 'Branch_Type' ] ) ? $_GET[ 'Branch_Type' ] : null );
                        \singleton\table::getInstance( )->th_input( 'Branch_ID', isset( $_GET[ 'Branch_ID' ] ) ? $_GET[ 'Branch_ID' ] : null );
                        \singleton\table::getInstance( )->th_input( 'Picture', isset( $_GET[ 'Picture' ] ) ? $_GET[ 'Picture' ] : null );
                        ?></tr></thead>
                    </table>
                  </div>
                </form></div>
            </div>
        </div>
    </div>
</body>
</html>
<?php }
}?>
