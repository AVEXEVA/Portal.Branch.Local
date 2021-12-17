<?php
session_start( [ 'read_and_close' => true ] );
require('../index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
  $r = $database->query(null,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
  $array = sqlsrv_fetch_array($r);
  $Privileged = FALSE;
  if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
      $r = $database->query(null,"SELECT * FROM Emp WHERE ID = ?",array($_SESSION['User']));
      $My_User = sqlsrv_fetch_array($r);
      $Field = ($User['Field'] == 1 && $User['Title'] != "OFFICE") ? True : False;
      $r = $database->query($Portal,"
          SELECT Access, Owner, Group, Other
          FROM   Privilege
          WHERE  User_ID = ?
      ;",array($_SESSION['User']));
      $My_Privileges = array();
      while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access']] = $array2;}
      $Privileged = FALSE;
      if(isset($My_Privileges['Admin']) && $My_Privileges['Admin']['Owner'] >= 6){$Privileged = TRUE;}
  }
  if(!$Privileged || count($_POST) == 0){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
  else {
    $Territory = isset($_POST['Territory']) ? $_POST['Territory'] : NULL;
    $Customer  = isset($_POST['Customer'])  ? $_POST['Customer']  : NULL;
    $Location  = isset($_POST['Location'])  ? $_POST['Location']  : NULL;
    $Unit      = isset($_POST['Unit'])      ? $_POST['Unit']      : NULL;

    $Elevator_Part = isset($_POST['Elevator_Part']) ? $_POST['Elevator_Part'] : NULL;
    $Condition     = isset($_POST['Condition'])     ? $_POST['Condition']     : NULL;
    $Remedy        = isset($_POST['Remedy'])        ? $_POST['Remedy']        : NULL;

    if(!is_null($Elevator_Part) && !is_null($Condition) && !is_null($Remedy)){
      if(!is_null($Territory)){
        $resource = $database->query(null,
          " SELECT
                    Contract.Job AS Contract,
                    Elev.ID      AS Unit
            FROM    nei.dbo.Contract
                    LEFT JOIN nei.dbo.Loc   ON Contract.Loc = Loc.Loc
                    LEFT JOIN nei.dbo.Elev  ON Elev.Loc     = Loc.Loc
            WHERE   Loc.Terr = ?
                    AND Contract.BFinish >= ?
                    AND Elev.[Status] = 0
          ;",array($Territory,date("Y-m-d H:i:s",strtotime('now'))));
        if( ($errors = sqlsrv_errors() ) != null) {
            foreach( $errors as $error ) {
                echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
                echo "code: ".$error[ 'code']."<br />";
                echo "message: ".$error[ 'message']."<br />";
            }
        }
        if($resource){while($row = sqlsrv_fetch_array($resource)){
          $r = $database->query(null,
            " SELECT  *
              FROM    Portal.dbo.Contract_Category_Item AS Contract_Item
              WHERE   Contract_Item.[Unit] = ?
                      AND Contract_Item.[Elevator_Part] = ?
                      AND Contract_Item.[Condition]     = ?
                      AND Contract_Item.[Remedy]        = ?
            ;",array($row['Unit'], $Elevator_Part, $Condition, $Remedy));
          if( ($errors = sqlsrv_errors() ) != null) {
              foreach( $errors as $error ) {
                  echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
                  echo "code: ".$error[ 'code']."<br />";
                  echo "message: ".$error[ 'message']."<br />";
              }
          }
          if($r && is_array(sqlsrv_fetch_array($r))){}
          else {
            $database->query(null,
              " INSERT INTO Portal.dbo.Contract_Category_Item(Contract, Unit, Elevator_Part, Condition, Remedy, Covered)
                VALUES(?, ?, ?, ?, ?, ?)
              ;", array($row['Contract'], $row['Unit'], $Elevator_Part, $Condition, $Remedy, 1));
            if( ($errors = sqlsrv_errors() ) != null) {
                foreach( $errors as $error ) {
                    echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
                    echo "code: ".$error[ 'code']."<br />";
                    echo "message: ".$error[ 'message']."<br />";
                }
            }
          }
        }}
      }
    }
  }
}?>
