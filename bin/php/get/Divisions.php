<?php
session_start( [ 'read_and_close' => true ] );
require('index.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $result = \singleton\database::getInstance( )->query(
        null,
      " SELECT *
        FROM   Connection
        WHERE  Connection.Connector = ?
               AND Connection.Hash = ?
    ;", array($_SESSION['User'],$_SESSION['Hash']));
    $Connection = sqlsrv_fetch_array($result);
    $User    = \singleton\database::getInstance( )->query(
        null,
      " SELECT Emp.*,
               Emp.fFirst AS First_Name,
               Emp.Last   AS Last_Name
        FROM   Emp
        WHERE  Emp.ID = ?
    ;", array($_SESSION['User']));
    $User = sqlsrv_fetch_array($User);
    $Field = ($User['Field'] == 1 && $User['Title'] != "OFFICE") ? True : False;
    $result = \singleton\database::getInstance( )->query(
      'Portal',
      "   SELECT  [Privilege].[Access],
                  [Privilege].[Owner],
                  [Privilege].[Group],
                  [Privilege].[Other]
        FROM      dbo.[Privilege]
        WHERE     Privilege.[User] = ?;",
      array(
        $_SESSION[ 'Connection' ][ 'User' ]
      )
    );
    $Privileges = array();
    while($array2 = sqlsrv_fetch_array($result)){$Privileges[$array2['Access_Table']] = $array2;}
    $Privileged = False;
    if( isset($Privileges['Legal'])
        && (
				$Privileges['Divisions']['Other_Privilege'] >= 4
		)
	 ){
            $Privileged = True;}
    if(!isset($Connection['ID']) || !$Privileged){print json_encode(array('data'=>array()));}
    else {
        $data = array();
        $result = \singleton\database::getInstance( )->query(
            null,
          " SELECT *
            FROM (
                SELECT ROW_NUMBER() OVER ($Order) ($Direction) AS ROW_COUNT,
                Zone.ID,
                Zone.Name,
                Locations.Count  AS Location,
                Units.Count     AS Units,
                Violation.Count AS Violation,
                Tickets.Count   AS tickets
            FROM (
                 SELECT  Zone.ID
                         Rol.Name,
                 FROM    Zone
                         LEFT JOIN
           ) AS Zone
            LEFT JOIN (
              SELECT     Location.Zone AS Zone,
                         COUNT( Location.Loc ) AS Count
              FROM       Loc AS Location
              GROUP BY   Location.Zone
          ) AS Locations ON Locations.Zone = Zone.ID
            LEFT JOIN (
              SELECT     Loc.Zone AS Zone,
                        COUNT ( Unit.ID ) AS Count
              FROM       Elev AS Unit
                         LEFT JOIN Loc ON Elev.Loc = Loc.Loc
              GROUP BY   Loc.Zone
          ) AS    Units ON Units.Zone = Zone.ID
            LEFT JOIN (
              SELECT     Loc.Zone      AS Zone,
                         COUNT ( Violation.ID ) AS Count
              FROM       Violation AS Violation
                         LEFT JOIN Job ON Violation.Job = Job.ID
                         LEFT JOIN Loc ON Loc.Loc = Job.Loc
              GROUP BY   Loc.Zone
          ) AS Violations ON Violations.Zone = Zone.ID
            LEFT JOIN (
              SELECT     Loc.Zone      AS Zone,
                         COUNT ( Tickets.ID ) AS Count
              FROM       Tickets AS Tickets
                         LEFT JOIN Job ON Violation.Job = Job.ID
                         LEFT JOIN Loc ON Loc.Loc = Job.Loc
              GROUP BY   Loc.Zone
          ) AS Tickets ON Tickets.Zone = Zone.ID
      WHERE {$conditions}
   ) AS Tbl
   WHERE tbl.ROW_COUNT BETWEEN ? AND ?;";

   $sQueryRow =
     " SELECT Zone.ID
       FROM (
            SELECT  Zone.ID
                    Rol.Name,
            FROM    Zone
                    LEFT JOIN
      ) AS Zone
       LEFT JOIN (
         SELECT     Location.Zone AS Zone,
                    COUNT( Location.Loc ) AS Count
         FROM       Loc AS Location
         GROUP BY   Location.Zone
     ) AS Locations ON Locations.Zone = Zone.ID
       LEFT JOIN (
         SELECT     Loc.Zone AS Zone,
                   COUNT ( Unit.ID ) AS Count
         FROM       Elev AS Unit
                    LEFT JOIN Loc ON Elev.Loc = Loc.Loc
         GROUP BY   Loc.Zone
     ) AS    Units ON Units.Zone = Zone.ID
       LEFT JOIN (
         SELECT     Loc.Zone      AS Zone,
                    COUNT ( Violation.ID ) AS Count
         FROM       Violation AS Violation
                    LEFT JOIN Job ON Violation.Job = Job.ID
                    LEFT JOIN Loc ON Loc.Loc = Job.Loc
         GROUP BY   Loc.Zone
     ) AS Violations ON Violations.Zone = Zone.ID
       LEFT JOIN (
         SELECT     Loc.Zone      AS Zone,
                    COUNT ( Tickets.ID ) AS Count
         FROM       Tickets AS Tickets
                    LEFT JOIN Job ON Violation.Job = Job.ID
                    LEFT JOIN Loc ON Loc.Loc = Job.Loc
         GROUP BY   Loc.Zone
     ) AS Tickets ON Tickets.Zone = Zone.ID
 WHERE {$conditions};";

 $fResult = \singleton\database::getInstance( )->query( null, $sQueryRow , $parameters ) or die(print_r(sqlsrv_errors()));


 $iFilteredTotal = 0;
 $_SESSION[ 'Tables' ] = isset( $_SESSION[ 'Tables' ] ) ? $_SESSION[ 'Tables' ] : array( );
 $_SESSION[ 'Tables' ][ 'Divisions' ] = isset( $_SESSION[ 'Tables' ][ 'Divisions' ]  ) ? $_SESSION[ 'Tables' ][ 'Divisions' ] : array( );
 if( count( $_SESSION[ 'Tables' ][ 'Divisions' ] ) > 0 ){ foreach( $_SESSION[ 'Tables' ][ 'Divisions' ] as &$Value ){ $Value = false; } }
 $_SESSION[ 'Tables' ][ 'Divisions' ][ 0 ] = $_GET;
 while( $Row = sqlsrv_fetch_array( $fResult ) ){
     $_SESSION[ 'Tables' ][ 'Divisions' ][ $Row[ 'ID' ] ] = true;
     $iFilteredTotal++;
 }

 $parameters = array( );
 $sQuery = " SELECT  COUNT(Zone.ID)
             FROM    Zone;";
 $rResultTotal = \singleton\database::getInstance( )->query(null,  $sQuery, $parameters ) or die(print_r(sqlsrv_errors()));
 $aResultTotal = sqlsrv_fetch_array($rResultTotal);
 $iTotal = $aResultTotal[0];

 $output = array(
     'sEcho'         =>  intval( $_GET[ 'draw' ] ),
     'iTotalRecords'     =>  $iTotal,
     'iTotalDisplayRecords'  =>  $iFilteredTotal,
     'aaData'        =>  array()
 );

 while ( $Row = sqlsrv_fetch_array( $rResult ) ){
   $output['aaData'][]       = $Row;
 }
 echo json_encode( $output );
}
}
?>
