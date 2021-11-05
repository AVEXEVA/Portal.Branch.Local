<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [ 'read_and_close' => true ] ); 
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/cgi-bin/php/index.php' );
}
if( isset( $_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
  //Connection
  $result = $database->query(
    null,
    " SELECT  * 
      FROM    Connection 
      WHERE       Connector = ? 
              AND Hash = ?;",
    array(
      $_SESSION[ 'User' ],
      $_SESSION[ 'Hash' ]
    )
  );
  $Connection = sqlsrv_fetch_array( $result );
  //User
  $result = $database->query(
    null,
    " SELECT  *, 
              fFirst AS First_Name, 
              Last as Last_Name 
      FROM    Emp 
      WHERE   ID = ?;",
    array(
      $_SESSION[ 'User' ]
    )
  );
  $User = sqlsrv_fetch_array( $result );
  //Privileges
  $result = $database->query(
    null,
    " SELECT  Access_Table, 
              User_Privilege, 
              Group_Privilege, 
              Other_Privilege
      FROM    Privilege
      WHERE   User_ID = ?;",
    array( 
      $_SESSION[ 'User' ] 
    ) 
  );
  $Privileges = array( );
  if( $result ){ while( $Privilege = sqlsrv_fetch_array( $result )){ $Privilege[ $Privilege[ 'Access_Table' ] ] = $Privilege; } }
  if(!isset($Connection['ID'])){?><html><head></head></html><?php }
  else {
    $database->query(
      null,
      " INSERT INTO Activity([User], [Date], [Page]) 
        VALUES( ?, ?, ? );",
      array(
        $_SESSION['User'],
        date('Y-m-d H:i:s'), 
        'ticket.php'
      )
    );
    $_POST[ 'Notes' ] = isset( $_POST[ 'Notes' ] ) ? $_POST[ 'Notes' ] : '';
    $database->query(
      null, 
      " INSERT INTO Attendance( [User], [Start], [Start_Notes] ) 
        VALUES( ?, ?, ? );",
      array(
        $_SESSION[ 'User' ],
        date( 'Y-m-d H:i:s' ),
        $_POST[ 'Notes' ]
      )
    );
  }
}?>
