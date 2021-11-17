<?php
session_start( [ 'read_and_close' => true ] );
require('index.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = \singleton\database::getInstance( )->query(
        null,
      " SELECT *
        FROM   Connection
        WHERE  Connection.Connector = ?
               AND Connection.Hash = ?;",
    array($_SESSION['User'],$_SESSION['Hash']));
    $Connection = sqlsrv_fetch_array($r);
    $User    = \singleton\database::getInstance( )->query(
        null,
      " SELECT Emp.*,
               Emp.fFirst AS First_Name,
               Emp.Last   AS Last_Name
        FROM   Emp
        WHERE  Emp.ID = ?;",
    array($_SESSION['User']));
    $User = sqlsrv_fetch_array($User);
    $Field = ($User['Field'] == 1 && $User['Title'] != "OFFICE") ? True : False;
    $r = \singleton\database::getInstance( )->query(
        null,
      " SELECT Privilege.Access,
               Privilege.Owner,
               Privilege.Group,
               Privilege.Other
        FROM   Privilege
        WHERE  Privilege.User_ID = ?;",
    array($_SESSION['User']));
    $Privileges = array();
    while($array2 = sqlsrv_fetch_array($r)){$Privileges[$array2['Access']] = $array2;}
    $Privileged = False;
    if( isset($Privileges['Admin'])
        && (
			$Privileges['Admin']['Owner'] >= 4
  && $Privileges['Admin']['Group'] >= 4
  && $Privileges['Admin']['Other'] >= 4
 		)
	 ){
            $Privileged = True;}
    if(!isset($Connection['ID']) || !$Privileged){print json_encode(array('data'=>array()));}
    else {

      if( isset($_GET[ 'ID' ] ) && !in_array( $_GET[ 'ID' ], array( '', ' ', null ) ) ){
			$parameters[] = $_GET['ID'];
			$conditions[] = "Privilege.ID LIKE '%' + ? + '%'";
		}

      if( isset($_GET[ 'Table' ] ) && !in_array( $_GET[ 'Table' ], array( '', ' ', null ) ) ){
			$parameters[] = $_GET['Table'];
			$conditions[] = "Privilege.Access LIKE '%' + ? + '%'";
		}

      if( isset($_GET[ 'User' ] ) && !in_array( $_GET[ 'User' ], array( '', ' ', null ) ) ){
			$parameters[] = $_GET['User'];
			$conditions[] = "Privilege.Owner LIKE '%' + ? + '%'";
		}

      if( isset($_GET[ 'Group' ] ) && !in_array( $_GET[ 'Group' ], array( '', ' ', null ) ) ){
			$parameters[] = $_GET['Group'];
			$conditions[] = "Privilege.Group LIKE '%' + ? + '%'";
		}

      if( isset($_GET[ 'Other' ] ) && !in_array( $_GET[ 'Other' ], array( '', ' ', null ) ) ){
			$parameters[] = $_GET['Other'];
			$conditions[] = "Privilege.Other LIKE '%' + ? + '%'";
		}


        $r = \singleton\database::getInstance( )->query(
            null,
          " SELECT Emp.ID          AS ID,
                   Emp.fFirst      AS First_Name,
                   Emp.Last        AS Last_Name
            FROM   Emp
            WHERE  Emp.Status='0';");
        $data = array();
        if($r){
            while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){
                if(isset($array['ID']) && is_numeric($array['ID'])){
                  $r2 = \singleton\database::getInstance( )->query(
                      null,
                    " SELECT Privilege.*
                      FROM   Privilege
                      WHERE  Privilege.User_ID = ?;",
                      array($array['ID']));
                  if($r2){while($array2 = sqlsrv_fetch_array($r2)){
                      if(is_array($array2)){
                        $array['Privileges'][] = $array2;
                        if($array2['Access'] == 'Beta'){
                            $array['Beta'] = $array2['Owner'] . $array2['Group'] . $array2['Other'];
                        }
                      }
                  }}
                  if(!isset($array['Beta'])){$array['Beta'] = '000';}
                  $data[] = $array;
                }
            }
        }
        print json_encode(array('data'=>$data));	}
}?>
