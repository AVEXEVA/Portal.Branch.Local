<?php
session_start( [ 'read_and_close' => true ] );
require('../../../../cgi-bin/php/index.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    $My_User = sqlsrv_query($NEI,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID = ?",array($_SESSION['User']));
    $My_User = sqlsrv_fetch_array($My_User);
    $Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
    $r = sqlsrv_query($NEI,"
        SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
        FROM   Privilege
        WHERE  User_ID = ?
    ;",array($_SESSION['User']));
    $My_Privileges = array();
    while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
    $Privileged = FALSE;
    if(     isset($My_Privileges['Location']) 
        &&  $My_Privileges['Location']['User_Privilege']    >= 4 
        &&  $My_Privileges['Location']['Group_Privilege']   >= 4 
        &&  $My_Privileges['Location']['Other_Privilege']   >= 4){
                $Privileged = TRUE;
    }
    /*elseif($My_Privileges['Location']['User_Privilege'] >= 4 && is_numeric($_GET['ID'])){
        $r = sqlsrv_query(  $NEI,"
        SELECT  *
        FROM    TicketO
        WHERE   TicketO.LID='{$_GET['ID']}'
                AND fWork='{$My_User['fWork']}'");
        $r2 = sqlsrv_query( $NEI,"
        SELECT  *
        FROM    TicketD
        WHERE   TicketD.Loc='{$_GET['ID']}'
                AND fWork='{$My_User['fWork']}'");
        $r3 = sqlsrv_query( $NEI,"
        SELECT  *
        FROM    TicketDArchive
        WHERE   TicketDArchive.Loc='{$_GET['ID']}'
                AND fWork='{$My_User['fWork']}'");
        $r = sqlsrv_fetch_array($r);
        $r2 = sqlsrv_fetch_array($r2);
        $r3 = sqlsrv_fetch_array($r3);
        $Privileged = (is_array($r) || is_array($r2) || is_array($r3)) ? TRUE : FALSE;
    }*/
    sqlsrv_query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "location.php"));
    if(!isset($array['ID'])  || !$Privileged || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
        $ID = $_GET['ID'];
        $r = sqlsrv_query($NEI,
            "SELECT TOP 1
                    Loc.Loc              AS Location_ID,
                    Loc.ID               AS Name,
                    Loc.Tag              AS Tag,
                    Loc.Address          AS Street,
                    Loc.City             AS City,
                    Loc.State            AS State,
                    Loc.Zip              AS Zip,
                    Loc.Balance          as Location_Balance,
                    Zone.Name            AS Zone,
                    Loc.Route            AS Route_ID,
                    Emp.ID               AS Route_Mechanic_ID,
                    Emp.fFirst           AS Route_Mechanic_First_Name,
                    Emp.Last             AS Route_Mechanic_Last_Name,
                    Loc.Owner            AS Customer_ID,
                    OwnerWithRol.Name    AS Customer_Name,
                    OwnerWithRol.Balance AS Customer_Balance,
                    Terr.Name            AS Territory_Domain/*,
                    Sum(SELECT Location.ID FROM Loc AS Location WHERE Location.Owner='Loc.Owner') AS Customer_Locations*/
            FROM    Loc
                    LEFT JOIN Zone         ON Loc.Zone   = Zone.ID
                    LEFT JOIN Route        ON Loc.Route  = Route.ID
                    LEFT JOIN Emp          ON Route.Mech = Emp.fWork
                    LEFT JOIN OwnerWithRol ON Loc.Owner  = OwnerWithRol.ID
                    LEFT JOIN Terr                 ON Terr.ID    = Loc.Terr
            WHERE
                    Loc.Loc = ?
        ;",array($_GET['ID']));
        $Location = sqlsrv_fetch_array($r);
        $data = $Location;
        $job_result = sqlsrv_query($NEI,"
            SELECT Job.ID AS ID
            FROM   Job
            WHERE  Job.Loc = ?
        ;",array($_GET['ID']));
        if($job_result){
            $Jobs = array();
            $dates = array();
            $totals = array();
            while($array = sqlsrv_fetch_array($job_result)){$Jobs[] = "[JOBLABOR].[JOB #]='{$array['ID']}'";}
            $SQL_Jobs = implode(" OR ",$Jobs);
        }?>
                <div class="panel panel-primary" style='margin-bottom:0px;'>
                    <div class="panel-body">
                        <div class="panel panel-primary">
                            <!--<div class="panel-heading"><h3><?php \singleton\fontawesome::getInstance( )->Territory();?>Active Contracts</h3></div>-->
                            <div class='panel-body  BankGothic shadow'>
                                <table id='Table_Contracts' class='display' cellspacing='0' width='100%' style='font-size:12px;'>
                                    <thead><tr>
                                        <th title=''>Location</th>
                                        <th title=''>Amount</th>
                                        <th title=''>Start</th>
                                        <th title=''>Cycle</th>
                                        <th title=''>Months</th>
										<th>Link</th>
                                    </tr></thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

    <script>
        var Table_Contracts = $('#Table_Contracts').DataTable( {
            "ajax": "cgi-bin/php/get/Contracts_by_Location.php?ID=<?php echo $_GET['ID'];?>",
            "columns": [
                {
                    "data": "Location",
                    "visible":false
                },{
                    "data": "Contract_Amount",
					render:function(data){return "$" + parseFloat(data).toLocaleString();}
                },{
                    "data": "Contract_Start",
                    render: function(data){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}
                },{
                    "data": "Contract_Billing_Cycle",
                    render:function(data){
                        switch(data){
                            case 0:return 'Monthly';
                            case 1:return 'Bi-Monthly';
                            case 2:return 'Quarterly';
                            case 3:return 'Trimester';
                            case 4:return 'Semi-Annualy';
                            case 5:return 'Annually';
                            case 6:return 'Never';}}
                },{
                    "data": "Contract_Length"
                },{
					"data" : "Link",
					"className":"hidden"
				}
            ],
            "order": [[1, 'asc']],
            "language":{
                "loadingRecords":""
            },
            "initComplete":function(){},
            "paging":false,
            "searching":false
        } );
		$("Table#Table_Contracts").on("draw.dt",function(){
			$("Table#Table_Contracts tbody tr").on("click",function(){
				document.location.href = $(this).children(":last-child").html();
			});
		});
        </script>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
