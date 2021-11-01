<?php 
session_start();
require('index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $User = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Emp WHERE ID = ?",array($_GET['User']));
    $User = sqlsrv_fetch_array($User);
    $Field = ($User['Field'] == 1 && $User['Title'] != "OFFICE") ? True : False;
    if(!isset($array['ID']) ){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
        if($_GET['Start_Date'] > 0){$Start_Date = DateTime::createFromFormat('m/d/Y', $_GET['Start_Date'])->format("Y-m-d 00:00:00.000");}
        else{$Start_Date = DateTime::createFromFormat('m/d/Y',"01/01/2017")->format("Y-m-d 00:00:00.000");}

        if($_GET['End_Date'] > 0){$End_Date = DateTime::createFromFormat('m/d/Y', $_GET['End_Date'])->format("Y-m-d 23:59:59.999");}
        else{$End_Date = DateTime::createFromFormat('m/d/Y',"01/01/3000")->format("Y-m-d 23:59:59.999");}

        if(!isset($_GET['Location_ID']) || $_GET['Location_ID'] == "All" || $_GET['Location_ID'] == "" || $_GET['Location_ID'] == ","){$Location_ID = "'1'='1'";}
        else {
            $Location_ID = (isset($_GET['Location_ID'])) ? (strpos($_GET['Location_ID'], ',') !== false) ? explode(',',addslashes($_GET['Location_ID'])) : array(addslashes($_GET['Location_ID'])) : array();
            if(count($Location_ID) > 0){
                $temp = array();
                foreach($Location_ID as $Tag){$temp[] = "Loc.Loc = '" . $Tag . "'";}
                $Location_ID = implode(" OR ",$temp);
            } else {
                $Location_ID = "Loc = '" . $Location_ID . "'";
            }
        }
        if(!isset($_GET['Customer_ID']) || $_GET['Customer_ID'] == "All" || $_GET['Customer_ID'] == "" || $_GET['Customer_ID'] == ","){$Customer_ID = "'1'='1'";}
        else {
            $Customer_ID = (isset($_GET['Customer_ID'])) ? (strpos($_GET['Customer_ID'], ',') !== false) ? explode(',',addslashes($_GET['Customer_ID'])) : array(addslashes($_GET['Customer_ID'])) : array();
            if(count($Customer_ID) > 0){
                $temp = array();
                foreach($Customer_ID as $Tag){$temp[] = "OwnerWithRol.ID = '" . $Tag . "'";}
                $Customer_ID = implode(" OR ",$temp);
            } else {
                $Customer_ID = "OwnerWithRol.ID = '" . $Customer_ID . "'";
            }
        }
        //var_dump($Location_ID);
        $r = sqlsrv_query($NEI,"
            SELECT 
                TicketDArchive.ID           AS ID,
                TicketDArchive.fDesc        AS fDesc,
                TicketDArchive.EDate        AS EDate,
                TicketDArchive.DescRes      AS DescRes,
                TicketDArchive.Total        AS Total,
                Loc.Tag                     AS Tag,
                OwnerWithRol.Name           AS Customer,
                Elev.Unit                   AS Unit_Label,
                Elev.Type                   AS Unit_Type,
                Elev.fDesc                  AS Unit_Description,
                Elev.State                  AS Unit_State
            FROM
                TicketDArchive 
                LEFT JOIN nei.dbo.Loc             ON TicketDArchive.Loc   = Loc.Loc
                LEFT JOIN nei.dbo.OwnerWithRol    ON Loc.Owner            = OwnerWithRol.ID
                LEFT JOIN nei.dbo.Elev            ON TicketDArchive.Elev  = Elev.ID
            WHERE
                    TicketDArchive.EDate    >= ?
                AND TicketDArchive.EDate    <= ?
                AND ({$Location_ID})
                AND ({$Customer_ID})
		;",array($Start_Date,$End_Date));
		$data = array();
        if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
        print json_encode(array('data'=>$data));
    }
}