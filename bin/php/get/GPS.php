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
      ||  !isset( $Privileges[ 'Map' ] )
      ||  !check( privilege_read, level_group, $Privileges[ 'Map' ] )
  ){ ?><?php require('404.html');?><?php }
  else {
    $result = \singleton\database::getInstance( )->query(
      'Portal',
      " SELECT    [GPS].[ID]          AS ID,
                  [GPS].[User]        AS User_ID,
                  [User].[Branch]     AS User_Branch,
                  [User].[Branch_ID]  AS User_Branch_ID,
                  [GPS].[Latitude]    AS Latitude,
                  [GPS].[Longitude]   AS Longitude,
                  [GPS].[Altitude]    AS Altitude,
                  [GPS].[Accuracy]    AS Accuracy,
                  [GPS].[Time_Stamp]  AS Time_Stamp
        FROM      dbo.[GPS]
                  LEFT JOIN dbo.[User] ON [GPS].[User] = [User].[ID]
        ORDER BY  GPS.ID DESC;",
      array( ) 
    );
    $rows = array( );
    if( $result ){ while( $row  = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC ) ){
        $Branches = array(
          'Development' => 'demo'
        );
        $result = \singleton\database::getInstance( )->query(
          $Branches[ $row[ 'User_Branch' ] ],
          " SELECT  Employee.fFirst AS First_Name,
                    Employee.Last   AS Last_Name
            FROM    Emp AS Employee 
            WHERE   Employee.ID = ?;",
          array(
            $row[ 'User_Branch_ID' ]
          )
        );
        $row = array_merge( $row, sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC ) );
        $row['Time_Stamp'] = date('Y-m-d H:i:s',strtotime('-5 hours',strtotime($row['Time_Stamp'])));
        $row['Title'] = $row['First_Name'] . " " . $row['Last_Name'] . " - " . date('m/d/Y h:i A', strtotime($row['Time_Stamp']));
        $rows[ ] = $row;
      }
      print json_encode( $rows );
    }
  }
}?>
