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
<body onload='finishLoadingPage();'>
    <div id="wrapper">
        <?php require( bin_php . 'element/navigation.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content'>
            <div class="card card-full card-primary border-0">
                <div class="card-heading"><h4><?php \singleton\fontawesome::getInstance( )->Users( 1 );?> Users</h4></div>
                  <div class="card-body bg-dark">
                    <table id='Table_Users' class='display' cellspacing='0' width='100%'>
                        <thead><tr>
                            <th class='text-white border border-white'  title='ID'><?php \singleton\fontawesome::getInstance( )->Proposal();?>ID</th>
                            <th class='text-white border border-white'  title='Email'><?php \singleton\fontawesome::getInstance( )->Email();?>Email</th>
                            <th class='text-white border border-white'  title='Verified'><?php \singleton\fontawesome::getInstance( )->Description();?>Verified</th>
                            <th class='text-white border border-white'  title='Branch'><?php \singleton\fontawesome::getInstance( )->Division();?>Branch</th>
                            <th class='text-white border border-white'  title='Branch_Type'><?php \singleton\fontawesome::getInstance( )->Note();?>Type</th>
                            <th class='text-white border border-white'  title='Branch_ID'><?php \singleton\fontawesome::getInstance( )->Review();?>Reference</th>
                            <th class='text-white border border-white'  title='Picture'><?php \singleton\fontawesome::getInstance( )->Birthday();?>Picture</th>
                        </tr><tr>
                            <th class='text-white border border-white' title='ID'><input class='redraw form-control' type='text' name='ID' value='<?php echo isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null; ?>' /></th>
                            <th class='text-white border border-white' title='Email'><input class='redraw form-control' type='text' name='Email' value='<?php echo isset( $_GET[ 'Email' ] ) ? $_GET[ 'Email' ] : null; ?>' /></th>
                            <th class='text-white border border-white'  title='Verified'><input class='redraw form-control' type='text' name='Verified' value='<?php echo isset( $_GET[ 'Verified' ] ) ? $_GET[ 'Verified' ] : null; ?>' /></th>
                            <th class='text-white border border-white'  title='Branch'><input class='redraw form-control' type='text' name='Branch' value='<?php echo isset( $_GET[ 'Branch' ] ) ? $_GET[ 'Branch' ] : null; ?>' /></th>
                            <th class='text-white border border-white'  title='Branch_Type'><input class='redraw form-control' type='text' name='Type' value='<?php echo isset( $_GET[ 'Type' ] ) ? $_GET[ 'Type' ] : null; ?>' /></th>
                            <th class='text-white border border-white'  title='Branch_ID'><input class='redraw form-control' type='text' name='Reference' value='<?php echo isset( $_GET[ 'Reference' ] ) ? $_GET[ 'Reference' ] : null; ?>' /></th>
                              <th class='text-white border border-white'  title='Branch_ID'><input class='redraw form-control' type='text' /></th>
                        </tr></thead>
                    </table>
                  </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php }
}?>
