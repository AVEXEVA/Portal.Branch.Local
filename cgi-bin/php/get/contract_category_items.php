<?php
session_start();
require('index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"
        SELECT *
        FROM   Connection
        WHERE  Connection.Connector = ?
               AND Connection.Hash = ?
    ;", array($_SESSION['User'],$_SESSION['Hash']));
    $Connection = sqlsrv_fetch_array($r);
    $My_User    = sqlsrv_query($NEI,"
        SELECT Emp.*,
               Emp.fFirst AS First_Name,
               Emp.Last   AS Last_Name
        FROM   Emp
        WHERE  Emp.ID = ?
    ;", array($_SESSION['User']));
    $My_User = sqlsrv_fetch_array($My_User);
    $My_Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
    $r = sqlsrv_query($Portal,"
        SELECT Privilege.Access_Table,
               Privilege.User_Privilege,
               Privilege.Group_Privilege,
               Privilege.Other_Privilege
        FROM   Privilege
        WHERE  Privilege.User_ID = ?
    ;",array($_SESSION['User']));
    $My_Privileges = array();
    while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
    $Privileged = False;
    if( isset($My_Privileges['Admin'])
        && $My_Privileges['Admin']['Other_Privilege'] >= 4){
            $Privileged = True;}
    if(!isset($Connection['ID'])  || !$Privileged){print json_encode(array('data'=>array()));}
    else {
        /*$resource = sqlsrv_query($NEI,"SELECT Elev.ID AS ID FROM nei.dbo.Contract LEFT JOIN nei.dbo.Loc ON Contract.Loc = Loc.Loc LEFT JOIN nei.dbo.Elev ON Loc.Loc = Elev.Loc WHERE Contract.BFinish >= ? AND Elev.[Status] = 0;",array(date("Y-m-d H:i:s",strtotime('now'))));
        if($resource){while($row = sqlsrv_fetch_array($resource)){
          $r = sqlsrv_query($NEI,"SELECT * FROM Portal.dbo.Contract_Category_Item AS Contract_Item WHERE Contract_Item.[Unit] = ?;",array($row['ID']));
          if($r && is_array(sqlsrv_fetch_array($r))){}
          else {}
        }}*/
        $resource = sqlsrv_query($NEI,
          " SELECT  Job.fDesc                           AS  Contract,
                    OwnerWithRol.Name                   AS  Customer,
                    Loc.Tag                             AS  Location,
                    Elev.State + ' - ' + Elev.Unit      AS  Unit,
                    Elev.Price                          AS  Unit_Price,
                    Category_Elevator_Part.Name         AS  Unit_Part,
                    Category_Violation_Condition.Name   AS  Unit_Part_Condition,
                    Category_Remedy.Name                AS  Unit_Part_Remedy,
                    Contract_Item.[Covered]             AS  Unit_Part_Covered
            FROM    Portal.dbo.Contract_Category_Item   AS Contract_Item
                    LEFT JOIN nei.dbo.Contract      ON Contract_Item.Contract  =  Contract.Job
                    LEFT JOIN nei.dbo.Job           ON Job.ID                  =  Contract.Job
                    LEFT JOIN nei.dbo.OwnerWithRol  ON Job.Owner               =  OwnerWithRol.ID
                    LEFT JOIN nei.dbo.Loc           ON Loc.Loc                 =  Contract.Loc
                    LEFT JOIN nei.dbo.Elev          ON Contract_Item.Unit      =  Elev.ID
                    LEFT JOIN Portal.dbo.Category_Elevator_Part       ON Category_Elevator_Part.ID        = Contract_Item.Elevator_Part
                    LEFT JOIN Portal.dbo.Category_Violation_Condition ON Category_Violation_Condition.ID  = Contract_Item.Condition
                    LEFT JOIN Portal.dbo.Category_Remedy              ON Category_Remedy.ID               = Contract_Item.Remedy
          ;");
          if( ($errors = sqlsrv_errors() ) != null) {
              foreach( $errors as $error ) {
                  echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
                  echo "code: ".$error[ 'code']."<br />";
                  echo "message: ".$error[ 'message']."<br />";
              }
          }
        $data = array();
        if($resource){while($array = sqlsrv_fetch_array($resource,SQLSRV_FETCH_ASSOC)){
          $array['Unit_Part_Covered'] = $array['Unit_Part_Covered'] = 1 ? 'True' : 'False';
          $data[] = $array;
        }}
        print json_encode(array('data'=>$data));
	}
}?>
