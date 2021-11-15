<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [
        'read_and_close' => true
    ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
if( isset( $_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
  $result = sqlsrv_query(
    $NEI,
    " SELECT  *
      FROM    Connection
      WHERE       Connection.Connector = ?
              AND Connection.Hash  = ?;",
    array(
      $_SESSION[ 'User' ],
      $_SESSION[ 'Hash' ]
    )
  );
  $Connection = sqlsrv_fetch_array( $result );
  //User
  $result = sqlsrv_query(
    $NEI,
    " SELECT  *,
              Emp.fFirst AS First_Name,
              Emp.Last   AS Last_Name
      FROM    Emp
      WHERE   Emp.ID = ?;",
    array(
      $_SESSION[ 'User' ]
    )
  );
  $User = sqlsrv_fetch_array( $result );
  //Privileges
  $result = sqlsrv_query(
    $NEI,
    " SELECT  Privilege.Access_Table,
              Privilege.User_Privilege,
              Privilege.Group_Privilege,
              Privilege.Other_Privilege
      FROM    Privilege
      WHERE   Privilege.User_ID = ?;",
    array(
      $_SESSION[ 'User' ]
    )
  );
  $Privileges = array();
  if( $result ){while( $Privilege = sqlsrv_fetch_array( $result ) ){ $Privileges[ $Privilege[ 'Access_Table' ] ] = $Privilege; } }
  if(     !isset( $Connection[ 'ID' ] )
      ||  !isset($Privileges[ 'Admin' ])
      ||  $Privileges[ 'Admin' ][ 'User_Privilege' ]  < 4
      ||  $Privileges[ 'Admin' ][ 'Group_Privilege' ] < 4
      ||  $Privileges[ 'Admin' ][ 'Other_Privilege' ] < 4
  ){
      ?><?php require( '../404.html' );?><?php
  } else {
    sqlsrv_query(
      $NEI,
      " INSERT INTO Activity( [User], [Date], [Page] )
        VALUES( ?, ?, ? );",
      array(
        $_SESSION[ 'User' ],
        date( 'Y-m-d H:i:s' ),
        'wall.php'
      )
    );
    ///////////////////////////////////////////////////////////////////////////
  }
}
?>
