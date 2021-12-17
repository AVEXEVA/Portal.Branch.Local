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
    $conditions=array();
    $parameters=array();
     if( isset( $_GET[ 'Territory' ] ) && (  $_GET[ 'Territory' ]>0 ) ){
        $parameters[] = $_GET['Territory'];
        $conditions[] = "Location.Terr = ? ";
      }
      if( isset( $_GET[ 'route' ] ) &&  ($_GET[ 'route' ]>0) ){
      $parameters[] = $_GET['route'];
      $conditions[] = "Location.Route = ? ";
      }
      if( isset( $_GET[ 'division' ] ) && (  $_GET[ 'division' ]>0) ){
        $parameters[] = $_GET['division'];
        $conditions[] = "Location.Zone = ?";
      }
    /*Concatenate Filters*/
    $conditions = $conditions == array( ) ? "NULL IS NULL" : implode( ' AND ', $conditions );
    $rows = array( );
    $r = $database->query(null,"
      SELECT 
      Rol.fLong as Longitude,
      Rol.Latt as Latitude,
      Rol.Name as Name,
      Emp.fFirst AS First_Name,
      Emp.Last   AS Last_Name,
      Emp.Last,
      Emp.fWork  AS Employee_Work_ID,
      Emp.ID as Employee_ID
    FROM
      Emp
      Inner JOIN Rol ON Rol.ID = Emp.Rol where Rol.Latt > 0;",array());
  
  while($row = sqlsrv_fetch_array($r)){
  
     
        $row['Time_Stamp'] = '';

        $row['Title'] = $row['First_Name'] . " " . $row['Last_Name'] ;
        $row['Type'] ='Employee';
        $rows[ ] = $row;
      }


 $query=" SELECT 
      Location.fLong as Longitude,
      Location.Latt as Latitude,
      Location.Tag as Name
     FROM Loc as Location
        LEFT JOIN Zone            AS Division ON Location.Zone    = Division.ID
         LEFT JOIN Route           AS Route    ON Location.Route   = Route.ID
      LEFT JOIN Terr               ON Terr.ID    = Location.Terr
      where ({$conditions}) AND Location.Latt > 0";
      $filter = $database->query(null,$query,$parameters);
  
  while($row = sqlsrv_fetch_array($filter)){
  
     
        $row['Time_Stamp'] = '';

        $row['Title'] =$row['Name'];
           $row['Type'] ='Location';
        $rows[ ] = $row;
      }
      print json_encode( $rows );
    }
  }
?>
