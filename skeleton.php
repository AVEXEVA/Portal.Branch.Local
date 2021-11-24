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
    if(     !isset( $Connection[ 'ID' ] )
        ||  !isset( $Privileges[ 'Unit' ] )
        ||  !check( privilege_read, level_group, $Privileges[ 'User' ] )
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
        $result = $database->query(
            'Portal',
            "   SELECT  Top 1
                        *
                FROM    dbo.[User]
                WHERE   [User].[ID] = ?;",
          array(
            $ID,
            $Email
          )
        );
        $User =   (       empty( $ID )
                        &&    !empty( $Name )
                        &&    !$result
                      ) || (  empty( $ID )
                        &&    empty( $Name )
                      )  ? array(
            'ID' => null,
            'Email' => null
        ) : sqlsrv_fetch_array( $result );
        if( isset( $_POST ) && count( $_POST ) > 0 ){
            $User[ 'Email' ] = isset( $_POST[ 'Email' ] ) ? $_POST[ 'Email' ] : $User[ 'Email' ];
            if( empty( $_POST[ 'ID' ] ) ){
                $result = \singleton\database::getInstance( )->query(
                  'Portal',
                  " INSERT INTO dbo.[User]( Email )
                    VALUES( ? );
                    SELECT Max( ID ) FROM dbo.[User];",
                    array( 
                        $_POST[ 'Email' ]
                    )
                );
                sqlsrv_next_result( $result );
                $User[ 'ID' ] = sqlsrv_fetch_array( $result )[ 0 ];
                header( 'Location: user.php?ID=' . $User[ 'ID' ] );
                exit;
            } else {
                \singleton\database::getInstance( )->query(
                    'Portal',
                    "   UPDATE  dbo.[User]
                        SET     [User].[Email] = ?,
                        WHERE   [User].[ID] = ?;",
                    array(
                        $User[ 'Email' ],
                        $User[ 'ID' ]
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
        require( bin_meta . 'index.php' );
        require( bin_css  . 'index.php' );
        require( bin_js   . 'index.php' );
    ?>
    <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyCNrTryEaTEDRz-XDSg890ajL_JRPnLgzc"></script>
</head>
<body>
  <div id="wrapper">
    <?php require(PROJECT_ROOT.'php/element/navigation.php');?>
    <div id="page-wrapper" class='content' style='height:100%;overflow-y:scroll;'>
      <div class='card card-primary'>
        <div class='card-heading'>
          <div class='row g-0 px-3 py-2'>
            <div class='col-6'>
              <h5><?php \singleton\fontawesome::getInstance( )->User( 1 );?><a href='users.php?<?php
                echo http_build_query( is_array( $_SESSION[ 'Tables' ][ 'Users' ][ 0 ] ) ? $_SESSION[ 'Tables' ][ 'Users' ][ 0 ] : array( ) );
              ?>'>User</a>: <span><?php
                echo is_null( $User[ 'ID' ] )
                  ? 'New'
                  : $User[ 'Email' ];
              ?></span></h5>
            </div>
            <div class='col-2'></div>
            <div class='col-2'>
              <div class='row g-0'>
                <div class='col-4'>
                  <button
                    class='form-control rounded'
                    onClick="document.location.href='user.php';"
                  >Create</button>
                </div>
                <div class='col-4'>
                  <button
                    class='form-control rounded'
                    onClick="document.location.href='job.php?ID=<?php echo $User[ 'ID' ];?>';"
                  >Refresh</button>
                </div>
              </div>
            </div>
            <div class='col-2'>
              <div class='row g-0'>
                <div class='col-4'><button class='form-control rounded' onClick="document.location.href='user.php?ID=<?php echo !is_null( $User[ 'ID' ] ) ? array_keys( $_SESSION[ 'Tables' ][ 'Users' ], true )[ array_search( $User[ 'ID' ], array_keys( $_SESSION[ 'Tables' ][ 'Users' ], true ) ) - 1 ] : null;?>';">Previous</button></div>
                <div class='col-4'><button class='form-control rounded' onClick="document.location.href='users.php?<?php echo http_build_query( is_array( $_SESSION[ 'Tables' ][ 'Users' ][ 0 ] ) ? $_SESSION[ 'Tables' ][ 'Users' ][ 0 ] : array( ) );?>';">Table</button></div>
                <div class='col-4'><button class='form-control rounded' onClick="document.location.href='user.php?ID=<?php echo !is_null( $User[ 'ID' ] )? array_keys( $_SESSION[ 'Tables' ][ 'Users' ], true )[ array_search( $User[ 'ID' ], array_keys( $_SESSION[ 'Tables' ][ 'Users' ], true ) ) + 1 ] : null;?>';">Next</button></div>
              </div>
            </div>
          </div>
        </div>
        <div class='card-body bg-dark text-white'>
            <div class='card-columns'>
                <div class='card card-primary my-3'><form action='job.php?ID=<?php echo $User[ 'ID' ];?>' method='POST'>
                    <input type='hidden' value='<?php echo $User[ 'ID' ];?>' name='ID' />
                    <div class='card-heading'>
                      <div class='row g-0 px-3 py-2'>
                        <div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Info( 1 );?><span>Infomation</span></h5></div>
                        <div class='col-2'>&nbsp;</div>
                      </div>
                    </div>
                    <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Infomation' ] ) && $_SESSION[ 'Cards' ][ 'Infomation' ] == 0 ? "style='display:none;'" : null;?>>
                        <div class='row' style='padding-top:10px;padding-bottom:10px;'>
                            <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Email:</div>
                            <div class='col-8'><input type='text' name='Email' class='form-control edit' placeholder='Email' value='<?php echo $User[ 'Email' ];?>' /></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=unit<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
