<?php
session_start( [ 'read_and_close' => true ] );
require('index.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $result = $database->query(null,"
        SELECT *
        FROM   Connection
        WHERE  Connection.Connector = ?
               AND Connection.Hash = ?
    ;", array($_SESSION['User'],$_SESSION['Hash']));
    $Connection = sqlsrv_fetch_array($result);
    $User    = $database->query(null,"
        SELECT Emp.*,
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
    if( isset($Privileges['Connection'])
        && $Privileges['Connection']['Other_Privilege'] >= 4){
            $Privileged = True;}
    if(!isset($Connection['ID'])  || !$Privileged){print json_encode(array('data'=>array()));}
    else {
        $data = array();
        $result = $database->query(null,"
            SELECT   Connection.ID          AS ID,
                     Connection.Timestamped AS TimeStamped,
                     Emp.fFirst             AS First_Name,
                     Emp.Last               AS Last_Name,
                     Emp.ID                 AS User_ID
            FROM     Connection
                     LEFT JOIN Emp ON Connection.Connector = Emp.ID
            ORDER BY Connection.Timestamped
        ;");
        if($result){while($array = sqlsrv_fetch_array($result)){$data[] = $array;}}
        print json_encode(array('data'=>$data));
    }
}?>
