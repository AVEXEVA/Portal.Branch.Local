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
        $Email = isset( $_GET[ 'Email' ] )
            ? $_GET[ 'Email' ]
            : (
                isset( $_POST[ 'Email' ] )
                    ? $_POST[ 'Email' ]
                    : null
            );
        $result = \singleton\database::getInstance( )->query(
            'Portal',
            "   SELECT  Top 1
                        [User].[ID] AS ID,
                        [User].[Email] AS Email,
                        [User].[Password] AS Password,
                        [User].[Verified] AS Verified,
                        [User].[Branch] AS Branch,
                        [User].[Branch_Type] AS Branch_Type,
                        [User].[Branch_ID] AS Branch_ID,
                        [User].[Picture] AS Picture,
                        [User].[Picture_Type] AS Picture_Type
                FROM    dbo.[User]
                WHERE       [User].[ID] = ?
                        OR  [User].Email = ?;",
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
            'Email' => null,
            'Password' => null,
            'Verified' => null,
            'Branch' => null,
            'Branch_Type' => null,
            'Branch_ID' => null,
            'Picture' => null,
            'Picture_Type' => null
        ) : sqlsrv_fetch_array( $result );
        $result = \singleton\database::getInstance( )->query(
          $User[ 'Branch' ],
          " SELECT  Employee.ID                           AS Employee_ID,
                    Employee.fWork                        AS Employee_Work_ID,
                    Employee.fFirst + ' ' + Employee.Last AS Employee_Name,
                    Employee.fFirst                       AS Employee_First_Name,
                    Employee.Last                         AS Employee_Last_Name
            FROM    Emp AS Employee 
            WHERE   Employee.ID = ?;",
          array( 
            $User[ 'Branch_ID' ]
          )
        );
        //var_dump( sqlsrv_errors( ) );
        $User = ( !$result ) 
          ? array_merge( $User, array( 
            'Employee_ID' => null,
            'Employee_Work_ID' => null,
            'Employee_Name' => null,
            'Employee_First_Name' => null,
            'Employee_Last_Name' => null
          ) )
          : array_merge( $User, sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC ) );
        if( isset( $_POST ) && count( $_POST ) > 0 ){
            $User[ 'Email' ] = isset( $_POST[ 'Email' ] ) ? $_POST[ 'Email' ] : $User[ 'Email' ];
            $User[ 'Password' ] = isset( $_POST[ 'Password' ] ) ? $_POST[ 'Password' ] : $User[ 'Password' ];
            $User[ 'Verified' ] = isset( $_POST[ 'Verified' ] ) ? $_POST[ 'Verified' ] : $User[ 'Verified' ];
            $User[ 'Branch' ] = isset( $_POST[ 'Branch' ] ) ? $_POST[ 'Branch' ] : $User[ 'Branch' ];
            $User[ 'Branch_Type' ] = isset( $_POST[ 'Branch_Type' ] ) ? $_POST[ 'Branch_Type' ] : $User[ 'Branch_Type' ];
            $User[ 'Branch_ID' ] = isset( $_POST[ 'Branch_ID' ] ) ? $_POST[ 'Branch_ID' ] : $User[ 'Branch_ID' ];
            $User[ 'Picture' ] = isset($_FILES[ 'Picture' ] ) &&  ( $_FILES[ 'Picture' ][ 'tmp_name' ]!="" ) &&  (strlen( $_FILES[ 'Picture' ][ 'tmp_name' ] ) > 1) ? base64_encode( file_get_contents( $_FILES[ 'Picture' ][ 'tmp_name' ] ) ) : $User[ 'Picture' ];
            $User[ 'Picture_Type' ] = isset($_FILES[ 'Picture' ] ) &&  ( $_FILES[ 'Picture' ][ 'tmp_name' ]!="" ) &&  (strlen( $_FILES[ 'Picture' ][ 'tmp_name' ] ) > 1) ? $_POST[ 'Picture_Type' ] : $User[ 'Picture_Type' ];
            if( empty( $_POST[ 'ID' ] ) ){
	            $result = \singleton\database::getInstance( )->query(
	              'Portal',
	              " INSERT INTO dbo.[User]( Email, Password, Verified, Branch, Branch_Type, Branch_ID, Picture, Picture_Type )
	                VALUES( ?, ?, ?, ?, ?, ?, ? , ? );
	                SELECT Max( ID ) FROM dbo.[User];",
	                array(
	                    $User[ 'Email' ],
	                    $User[ 'Password' ],
                      is_null( $User[ 'Verified' ] ) ? 0 : $User[ 'Verified' ],
                      $User[ 'Branch' ],
                      $User[ 'Branch_Type' ],
                      $User[ 'Branch_ID' ],
                      array(
                        $User[ 'Picture' ],
                        SQLSRV_PARAM_IN,
                        SQLSRV_PHPTYPE_STREAM(SQLSRV_ENC_BINARY),
                        SQLSRV_SQLTYPE_VARBINARY('max')
                      ),
                      $User[ 'Picture_Type' ]
	                )
	            );
	            sqlsrv_next_result( $result );
	            $User[ 'ID' ] = sqlsrv_fetch_array( $result )[ 0 ];
	            header( 'Location: user.php?ID=' . $User[ 'ID' ] );
	            exit;
	        } else {
            $result = \singleton\database::getInstance( )->query(
              'Portal',
              " UPDATE  [User]
                SET     [User].[Email] = ?,
                        [User].[Branch] = ?,
                        [User].[Branch_Type] = ?,
                        [User].[Branch_ID] = ?,
                        [User].[Picture] = ?,
                        [User].[Picture_Type] = ?
                WHERE   [User].[ID] = ?;",
              array(
                $User[ 'Email' ],
                $User[ 'Branch' ],
                $User[ 'Branch_Type' ],
                $User[ 'Branch_ID' ],
                array(
                  $User[ 'Picture' ],
                  SQLSRV_PARAM_IN,
                  SQLSRV_PHPTYPE_STREAM(SQLSRV_ENC_BINARY),
                  SQLSRV_SQLTYPE_VARBINARY('max')
                ),
                $User[ 'Picture_Type' ],
                $User[ 'ID' ]
              )
            ) or die(print_r(sqlsrv_errors()));
            if( isset( $_POST[ 'Privilege' ][ 'Access' ] ) ){
              $result = \singleton\database::getInstance( )->query(
                'Portal',
                " INSERT INTO dbo.Privilege( [User], [Access] )
                  VALUES( ?, ? );",
                array(
                  $User[ 'ID' ],
                  $_POST[ 'Privilege' ][ 'Access' ]
                )
              );
            }
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
    <div id="page-wrapper" class='content'>
      <div class='card card-primary'><form action='user.php?ID=<?php echo $User[ 'ID' ];?>' method='POST' enctype="multipart/form-data">
        <input type='hidden' value='<?php echo $User[ 'ID' ];?>' name='ID' />
        <?php \singleton\bootstrap::getInstance( )->primary_card_header( 'User', 'Users', $User[ 'ID' ] );?>
        <div class='card-body bg-dark text-white'>
          <div class='row g-1' >
            <div class='card card-primary my-3 col-3'>
              <div class='card-heading'>
                <div class='row g-0 px-3 py-2'>
                  <div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Info( 1 );?><span>Infomation</span></h5></div>
                  <div class='col-2'>&nbsp;</div>
                </div>
              </div>
              <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Infomation' ] ) && $_SESSION[ 'Cards' ][ 'Infomation' ] == 0 ? "style='display:none;'" : null;?>>
                <?php \singleton\bootstrap::getInstance( )->card_row_form_input_email( 'Email', $User[ 'Email' ] );?>
                <?php \singleton\bootstrap::getInstance( )->card_row_form_input_password( 'Password', $User[ 'Password' ] );?>
                <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Branch', $User[ 'Branch' ] );?>
                <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Branch_Type', $User[ 'Branch_Type' ] );?>
                <?php \singleton\bootstrap::getInstance( )->card_row_form_autocomplete( 'Employee', 'Employees', $User[ 'Employee_ID' ], $User[ 'Employee_Name' ] );?>
                <div class='row'>
                  <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Blank( 1 );?>Image:</div>
                  <div class='col-8'><?php if(isset($User['Picture']) && strlen($User['Picture']) > 0){?><img width='100%' src="<?php
                    print "data:" . $User['Picture_Type'] . ";base64, " . $User['Picture'];
                  ?>" /><?php }?><input type='file' name='Picture' class='form-control edit' /></div><?php ?>
                </div>
              </div>
            </div>
            <div class='card card-primary my-y col-9'>
                <div class='card-heading'>
                  <div class='row g-0 px-3 py-2'>
                    <div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Privilege( 1 );?><span>Privileges</span></h5></div>
                    <div class='col-2'>&nbsp;</div>
                  </div>
                </div>
                <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Privileges' ] ) && $_SESSION[ 'Cards' ][ 'Privileges' ] == 0 ? "style='display:none;'" : null;?>>
                  <table id='Table_Privileges' class='display' cellspacing='0' width='100%'>
                    <thead><tr>
                      <th>ID</th>
                      <th>Access</th>
                      <th colspan='4'>Owner</th>
                      <th colspan='4'>Group</th>
                      <th colspan='4'>Department</th>
                      <th colspan='4'>Database</th>
                      <th colspan='4'>Server</th>
                    </tr><tr>
                      <th>&nbsp;</th>
                      <th>&nbsp;</th>
                      <th>Read</th>
                      <th>Write</th>
                      <th>Execute</th>
                      <th>Delete</th>
                      <th>Read</th>
                      <th>Write</th>
                      <th>Execute</th>
                      <th>Delete</th>
                      <th>Read</th>
                      <th>Write</th>
                      <th>Execute</th>
                      <th>Delete</th>
                      <th>Read</th>
                      <th>Write</th>
                      <th>Execute</th>
                      <th>Delete</th>
                      <th>Read</th>
                      <th>Write</th>
                      <th>Execute</th>
                      <th>Delete</th>
                    </tr><tr>
                      <th>&nbsp;</th>
                      <th><input type='text' class='form-input redraw' name='Access' placeholder='Access' value='<?php echo isset( $_GET[ 'Access'] ) ? $_GET[ 'Access' ] : null;?>' /></th>
                      <th><input type='checkbox' class='form-input redraw' name='Owner_Read' placeholder='Owner_Read' value='<?php echo isset( $_GET[ 'Owner_Read'] ) ? $_GET[ 'Owner_Read' ] : null;?>' /></th>
                      <th><input type='checkbox' class='form-input redraw' name='Owner_Write' placeholder='Owner_Write' value='<?php echo isset( $_GET[ 'Owner_Write'] ) ? $_GET[ 'Owner_Write' ] : null;?>' /></th>
                      <th><input type='checkbox' class='form-input redraw' name='Owner_Execute' placeholder='Owner_Execute' value='<?php echo isset( $_GET[ 'Owner_Execute'] ) ? $_GET[ 'Owner_Execute' ] : null;?>' /></th>
                      <th><input type='checkbox' class='form-input redraw' name='Owner_Delete' placeholder='Owner_Delete' value='<?php echo isset( $_GET[ 'Owner_Delete'] ) ? $_GET[ 'Owner_Delete' ] : null;?>' /></th>
                      <th><input type='checkbox' class='form-input redraw' name='Group_Read' placeholder='Group_Read' value='<?php echo isset( $_GET[ 'Group_Read'] ) ? $_GET[ 'Group_Read' ] : null;?>' /></th>
                      <th><input type='checkbox' class='form-input redraw' name='Group_Write' placeholder='Group_Write' value='<?php echo isset( $_GET[ 'Group_Write'] ) ? $_GET[ 'Group_Write' ] : null;?>' /></th>
                      <th><input type='checkbox' class='form-input redraw' name='Group_Execute' placeholder='Group_Execute' value='<?php echo isset( $_GET[ 'Group_Execute'] ) ? $_GET[ 'Group_Execute' ] : null;?>' /></th>
                      <th><input type='checkbox' class='form-input redraw' name='Group_Delete' placeholder='Group_Delete' value='<?php echo isset( $_GET[ 'Group_Delete'] ) ? $_GET[ 'Group_Delete' ] : null;?>' /></th>
                      <th><input type='checkbox' class='form-input redraw' name='Department_Read' placeholder='Department_Read' value='<?php echo isset( $_GET[ 'Department_Read'] ) ? $_GET[ 'Department_Read' ] : null;?>' /></th>
                      <th><input type='checkbox' class='form-input redraw' name='Department_Write' placeholder='Department_Write' value='<?php echo isset( $_GET[ 'Department_Write'] ) ? $_GET[ 'Department_Write' ] : null;?>' /></th>
                      <th><input type='checkbox' class='form-input redraw' name='Department_Execute' placeholder='Department_Execute' value='<?php echo isset( $_GET[ 'Department_Execute'] ) ? $_GET[ 'Department_Execute' ] : null;?>' /></th>
                      <th><input type='checkbox' class='form-input redraw' name='Department_Delete' placeholder='Department_Delete' value='<?php echo isset( $_GET[ 'Department_Delete'] ) ? $_GET[ 'Department_Delete' ] : null;?>' /></th>
                      <th><input type='checkbox' class='form-input redraw' name='Database_Read' placeholder='Database_Read' value='<?php echo isset( $_GET[ 'Database_Read'] ) ? $_GET[ 'Database_Read' ] : null;?>' /></th>
                      <th><input type='checkbox' class='form-input redraw' name='Database_Write' placeholder='Database_Write' value='<?php echo isset( $_GET[ 'Database_Write'] ) ? $_GET[ 'Database_Write' ] : null;?>' /></th>
                      <th><input type='checkbox' class='form-input redraw' name='Database_Execute' placeholder='Database_Execute' value='<?php echo isset( $_GET[ 'Database_Execute'] ) ? $_GET[ 'Database_Execute' ] : null;?>' /></th>
                      <th><input type='checkbox' class='form-input redraw' name='Database_Delete' placeholder='Database_Delete' value='<?php echo isset( $_GET[ 'Database_Delete'] ) ? $_GET[ 'Database_Delete' ] : null;?>' /></th>
                      <th><input type='checkbox' class='form-input redraw' name='Server_Read' placeholder='Server_Read' value='<?php echo isset( $_GET[ 'Server_Read'] ) ? $_GET[ 'Server_Read' ] : null;?>' /></th>
                      <th><input type='checkbox' class='form-input redraw' name='Server_Write' placeholder='Server_Write' value='<?php echo isset( $_GET[ 'Server_Write'] ) ? $_GET[ 'Server_Write' ] : null;?>' /></th>
                      <th><input type='checkbox' class='form-input redraw' name='Server_Execute' placeholder='Server_Execute' value='<?php echo isset( $_GET[ 'Server_Execute'] ) ? $_GET[ 'Server_Execute' ] : null;?>' /></th>
                      <th><input type='checkbox' class='form-input redraw' name='Server_Delete' placeholder='Server_Delete' value='<?php echo isset( $_GET[ 'Server_Delete'] ) ? $_GET[ 'Server_Delete' ] : null;?>' /></th>
                    </tr></thead>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </form></div>
    </div>
  </div>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=user<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
