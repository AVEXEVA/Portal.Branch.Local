<?php 
session_start();
require('cgi-bin/php/index.php');
$serverName = "172.16.12.45";
$NEIectionOptions = array(
    "Database" => "nei",
    "Uid" => "sa",
    "PWD" => "SQLABC!23456",
    'ReturnDatesAsStrings'=>true
);
//Establishes the connection
$NEI = sqlsrv_connect($serverName, $NEIectionOptions);
$NEIectionOptions['Database'] = 'Portal';
$Portal = sqlsrv_connect($serverName, $NEIectionOptions);
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    $User = sqlsrv_query($NEI,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID= ?",array($_SESSION['User']));
    $User = sqlsrv_fetch_array($User);
    $Field = ($User['Field'] == 1 && $User['Title'] != "OFFICE") ? True : False;
    $r = sqlsrv_query($Portal,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Portal.dbo.Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
    $My_Privileges = array();
    while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
    $Privileged = FALSE;
    if(isset($My_Privileges['Job']) && $My_Privileges['Job']['User_Privilege'] >= 4 && $My_Privileges['Job']['Group_Privilege'] >= 4 && $My_Privileges['Job']['Other_Privilege'] >= 4){$Privileged = TRUE;}
    if(!isset($array['ID'])  || !$Privileged){?><html><head><script>document.location.href='../login.php?Forward=modernizations.php';</script></head></html><?php }
    else {
        $r = sqlsrv_query($Portal,"SELECT * FROM Insured_Company WHERE ID=?",array($_GET['ID']));
        $Insured_Companis = array();
        $date = new DateTime("now");
        if($r){while($array = sqlsrv_fetch_array($r)){$Insured_Companies[$array['ID']] = $array;}}
        foreach($Insured_Companies as $ID=>$data){?><tr class='Insurance'>
            <td><?php echo $data['Company'];?></td>
            <td><input type='checkbox' <?php if($data['Active'] == 1){?> checked='checked' <?php }?> /></td>
            <td class='Worksmans'><?php 
                $r = sqlsrv_query($Portal,"
                    SELECT * 
                    FROM Insurance 
                    WHERE Company = ? AND Insurance.Type='Worksmans'
                ;",array($data['ID']));
                if($r){
                    $array = sqlsrv_fetch_array($r);
                    $Start_Date = $array['Start_Date'] != '' ? new DateTime($array['Start_Date']) : '';
                    $End_Date = $array['End_Date'] != '' ? new DateTime($array['End_Date']) : '';
                    if($Start_Date != "" && $End_Date != "" && $Start_Date <= $date && $date <= $End_Date){?>
                        <button onClick="renewInsurance();" style='background-color:green;color:white;'>Expires <?php echo $End_Date->format("m/d/Y");?></button>
                    <?php } elseif($Start_Date != "" && $End_Date != "") {?>
                        <button onClick="renewInsurance();" style='background-color:red;color:white;'>Expired <?php echo $End_Date->format("m/d/Y");?></button>
                    <?php } else {?>
                        <button onClick="newInsurance();" style='background-color:yellow;color:black;'>No Insurance</button>
                    <?php }
                } else {?><button onClick="newInsurance();" style='background-color:yellow;color:black;'>No Insurance</button><?php }
            ?></td>
            <td class='Auto'><?php 
                $r = sqlsrv_query($Portal,"
                    SELECT * 
                    FROM Insurance 
                    WHERE Company = ? AND Insurance.Type='Auto'
                ;",array($data['ID']));
                if($r){
                    $array = sqlsrv_fetch_array($r);
                    $Start_Date = $array['Start_Date'] != '' ? new DateTime($array['Start_Date']) : '';
                    $End_Date = $array['End_Date'] != '' ? new DateTime($array['End_Date']) : '';
                    if($Start_Date != "" && $End_Date != "" && $Start_Date <= $date && $date <= $End_Date){?>
                        <button onClick="renewInsurance();">Expires <?php echo $End_Date->format("m/d/Y");?></button>
                    <?php } elseif($Start_Date != "" && $End_Date != "") {?>
                        <button onClick="renewInsurance();" style='background-color:red;color:white;'>Expired <?php echo $End_Date->format("m/d/Y");?></button>
                    <?php } else {?>
                        <button onClick="newInsurance();" style='background-color:yellow;color:black;'>No Insurance</button>
                    <?php }
                } else {?><button onClick="newInsurance();" style='background-color:yellow;color:black;'>No Insurance</button><?php }
            ?></td>
            <td class='Liability'><?php 
                $r = sqlsrv_query($Portal,"
                    SELECT * 
                    FROM Insurance 
                    WHERE Company = ? AND Insurance.Type='Liability'
                ;",array($data['ID']));
                if($r){
                    $array = sqlsrv_fetch_array($r);
                    $Start_Date = $array['Start_Date'] != '' ? new DateTime($array['Start_Date']) : '';
                    $End_Date = $array['End_Date'] != '' ? new DateTime($array['End_Date']) : '';
                    if($Start_Date != "" && $End_Date != "" && $Start_Date <= $date && $date <= $End_Date){?>
                        <button onClick="renewInsurance();">Expires <?php echo $End_Date->format("m/d/Y");?></button>
                    <?php } elseif($Start_Date != "" && $End_Date != "") {?>
                        <button onClick="renewInsurance();" style='background-color:red;color:white;'>Expired <?php echo $End_Date->format("m/d/Y");?></button>
                    <?php } else {?>
                        <button onClick="newInsurance();" style='background-color:yellow;color:black;'>No Insurance</button>
                    <?php }
                } else {?><button onClick="newInsurance();" style='background-color:yellow;color:black;'>No Insurance</button><?php }
            ?></td>
            <td class='Umbrella'><?php 
                $r = sqlsrv_query($Portal,"
                    SELECT * 
                    FROM Insurance 
                    WHERE Company = ? AND Insurance.Type='Umbrella'
                ;",array($data['ID']));
                if($r){
                    $array = sqlsrv_fetch_array($r);
                    $Start_Date = $array['Start_Date'] != '' ? new DateTime($array['Start_Date']) : '';
                    $End_Date = $array['End_Date'] != '' ? new DateTime($array['End_Date']) : '';
                    if($Start_Date != "" && $End_Date != "" && $Start_Date <= $date && $date <= $End_Date){?>
                        <button onClick="renewInsurance();">Expires <?php echo $End_Date->format("m/d/Y");?></button>
                    <?php } elseif($Start_Date != "" && $End_Date != "") {?>
                        <button onClick="renewInsurance();" style='background-color:red;color:white;'>Expired <?php echo $End_Date->format("m/d/Y");?></button>
                    <?php } else {?>
                        <button onClick="newInsurance();" style='background-color:yellow;color:black;'>No Insurance</button>
                    <?php }
                } else {?><button onClick="newInsurance();" style='background-color:yellow;color:black;'>No Insurance</button><?php }
            ?></td>
            <td><?php echo $data['Hyperlink'];?></td>
            <td><button onClick="deleteRow(this);">Edit</button></td>
            <td><button onClick="deleteRow(this);">Delete</button></td>
        </tr><?php }?>
        <?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=modernizations.php';</script></head></html><?php }?>