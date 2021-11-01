<?php
session_start();
require('index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"
        SELECT *
        FROM   nei.dbo.Connection
        WHERE  Connection.Connector = ?
               AND Connection.Hash = ?
    ;", array($_SESSION['User'],$_SESSION['Hash']));
    $Connection = sqlsrv_fetch_array($r);
    $My_User    = sqlsrv_query($NEI,"
        SELECT Emp.*,
               Emp.fFirst AS First_Name,
               Emp.Last   AS Last_Name
        FROM   nei.dbo.Emp
        WHERE  Emp.ID = ?
    ;", array($_SESSION['User']));
    $My_User = sqlsrv_fetch_array($My_User);
    $My_Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
    $r = sqlsrv_query($Portal,"
        SELECT Privilege.Access_Table,
               Privilege.User_Privilege,
               Privilege.Group_Privilege,
               Privilege.Other_Privilege
        FROM   Portal.dbo.Privilege
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
          " SELECT  Deficiency.ID AS ID,
                    Category_Elevator_Part.Name AS Part,
                    Category_Remedy.Name AS Remedy,
                    Category_Violation_Condition.Name AS Condition,
                    Elev.State + ' - ' + Elev.Unit AS Unit,
                    Loc.Tag AS Location,
                    Terr.Name AS Territory,
                    CASE WHEN Contract_Category_Item.Covered = 1 THEN Emp.fFirst + ' ' + Emp.Last ELSE Emp.fFirst + ' ' + Emp.Last END AS [User],
                    CASE WHEN Contract_Category_Item.Covered = 1
                      THEN Contract_Category_Item.Covered
                      ELSE Reviewed_Category_Item.Approval
                    END AS Approval,
                    Reviewed_Category_Item.Responsibility AS Responsibility,
                    Deficiency.Comments AS Comments,
                    Job.ID AS Job,
                    Reviewed_Category_Item.Proposal AS Proposal,
                    Phone.Email AS Email,
                    Zone.Name AS Division
            FROM    Portal.dbo.Deficiency
                    LEFT JOIN Portal.dbo.Category_Elevator_Part ON Deficiency.Elevator_Part = Category_Elevator_Part.ID
                    LEFT JOIN Portal.dbo.Category_Remedy ON Deficiency.Remedy = Category_Remedy.ID
                    LEFT JOIN Portal.dbo.Category_Violation_Condition ON Deficiency.Violation = Category_Violation_Condition.ID
                    LEFT JOIN Portal.dbo.Reviewed_Category_Item ON Reviewed_Category_Item.Deficiency = Deficiency.ID
                    LEFT JOIN nei.dbo.Job ON Deficiency.Job = Job.ID
                    LEFT JOIN nei.dbo.Elev ON Job.Elev = Elev.ID
                    LEFT JOIN nei.dbo.Loc ON Job.Loc = Loc.Loc
                    LEFT JOIN nei.dbo.Terr ON Loc.Terr = Terr.ID
                    LEFT JOIN Portal.dbo.[Contract_Category_Item] ON [Contract_Category_Item].Elevator_Part = Deficiency.Elevator_Part AND [Contract_Category_Item].Condition = Deficiency.Violation AND [Contract_Category_Item].Remedy = Deficiency.Remedy AND [Contract_Category_Item].Unit = Job.Elev
                    LEFT JOIN nei.dbo.Emp ON Reviewed_Category_Item.[User] = Emp.ID
                    LEFT JOIN nei.dbo.Rol ON Rol.Name = Loc.Tag
                    LEFT JOIN nei.dbo.Phone ON Rol.ID = Phone.Rol AND Phone.fDesc LIKE '%E-Filing%'
                    LEFT JOIN nei.dbo.Zone ON Loc.Zone = Zone.ID
            WHERE   Job.Status = 0
          ;");
        $data = array();
        if($resource){while($array = sqlsrv_fetch_array($resource,SQLSRV_FETCH_ASSOC)){
          //$array['Unit_Part_Covered'] = $array['Unit_Part_Covered'] = 1 ? 'True' : 'False';
          $data[] = $array;
        }}
        print json_encode(array('data'=>$data));
	}
}?>
