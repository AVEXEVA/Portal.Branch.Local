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
  if( isset( $Connection[ 'ID' ] ) ){
    $result = \singleton\database::getInstance( )->query(
      'Portal',
      " SELECT  [User].[Branch],
                [User].[Branch_ID]
        FROM    [User]
                LEFT JOIN [Database] ON [User].[Branch] = [Database].[Name]
        WHERE       [User].[ID] = ?
                AND [User].[Branch_ID] > 0
                AND [Database].[ID] IS NOT NULL;",
      array(
        $_SESSION[ 'Connection' ][ 'User' ]
      )
    );
    if( $result ){
      $User = sqlsrv_fetch_array( $result );
      if(     is_array( $User ) 
          &&  isset( $User[ 'Branch' ] ) && !empty( $User[ 'Branch' ] )
          &&  isset( $User[ 'Branch_ID' ] ) && !empty( $User[ 'Branch_ID' ] )
      ){
        \singleton\database::getInstance( )->query(
          $User[ 'Branch' ],
          "     UPDATE  tblWork
                   SET  tblWork.Latt  = ?,
                        tblWork.fLong = ?
                  FROM  tblWork
                        INNER JOIN dbo.Emp AS Employee ON 'A' + convert(varchar(10), Employee.ID) + ',' = tblWork.Members
                 WHERE  Employee.ID = ?;",
            array(
              $_POST[ 'Latitude' ],
              $_POST[ 'Longitude' ],
              $User[ 'Branch_ID' ]
            )
        );
        \singleton\database::getInstance( )->query( 
          $User[ 'Branch' ],
          " INSERT INTO GPS( [Employee_ID], Latitude, Longitude, Altitude, Accuracy, Time_Stamp, User_Agent ) 
            VALUES(?, ?, ?, ?, ?, ?, ?);", 
          array(
            $User[ 'Branch_ID' ], 
            $_POST['Latitude'], 
            $_POST['Longitude'], 
            0, 
            0, 
            date( 'Y-m-d H:i:s' ),
            $_SERVER[ 'HTTP_USER_AGENT' ]
          )
        );
        var_dump( sqlsrv_errors( ) );
      }
    }
  }
}?>
