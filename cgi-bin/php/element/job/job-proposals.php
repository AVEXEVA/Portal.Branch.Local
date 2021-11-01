<?php
session_start();
require('../../../php/index.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
    	$My_User = sqlsrv_query($NEI,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID = ?",array($_SESSION['User']));
        $My_User = sqlsrv_fetch_array($My_User);
        $Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
        $r = sqlsrv_query($Portal,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Portal.dbo.Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Job']) && $My_Privileges['Job']['User_Privilege'] >= 4 && $My_Privileges['Job']['Group_Privilege'] >= 4 && $My_Privileges['Job']['Other_Privilege'] >= 4){$Privileged = TRUE;}
        elseif($My_Privileges['Job']['User_Privilege'] >= 4 && is_numeric($_GET['ID'])){
			$a = sqlsrv_query($NEI,"
				SELECT Job.Loc
				FROM nei.dbo.Job
				WHERE Job.ID = ?
			;",array($_GET['ID']));
			$loc = sqlsrv_fetch_array($a)['Loc'];
            $r = sqlsrv_query(  $NEI,"
				SELECT *
				FROM 		nei.dbo.Job
				LEFT JOIN 	nei.dbo.TicketO ON Job.ID = TicketO.Job
				WHERE 		TicketO.LID= ?
					AND 	TicketO.fWork= ?
			;",array($loc,$My_User['fWork']));
            $r2 = sqlsrv_query( $NEI,"
				SELECT *
				FROM 		nei.dbo.Job
				LEFT JOIN 	nei.dbo.TicketD ON Job.ID = TicketD.Job
				WHERE 		TicketD.Loc= ?
							AND TicketD.fWork= ?
			;",array($loc,$My_User['fWork']));
			$r3 = sqlsrv_query( $NEI,"
				SELECT *
				FROM 		nei.dbo.Job
				LEFT JOIN 	nei.dbo.TicketDArchive ON Job.ID = TicketDArchive.Loc
				WHERE 		TicketDArchive.Loc= ?
							AND TicketDArchive.fWork= ?
			;",array($loc,$My_User['fWork']));
            $r = sqlsrv_fetch_array($r);
            $r2 = sqlsrv_fetch_array($r2);
			$r3 = sqlsrv_fetch_array($r3);
            $Privileged = (is_array($r) || is_array($r2) || is_array($r3)) ? TRUE : FALSE;
		}
    }
    //
    if(!isset($array['ID'])  || !is_numeric($_GET['ID']) || !$Privileged ){require("401.html");}
    else {
       $r = sqlsrv_query($NEI,"
			SELECT TOP 1
                Job.ID                AS Job_ID,
                Job.fDesc             AS Job_Name,
                Job.fDate             AS Job_Start_Date,
                Job.BHour             AS Job_Budgeted_Hours,
                JobType.Type          AS Job_Type,
				Job.Remarks 		  AS Job_Remarks,
                Loc.Loc               AS Location_ID,
                Loc.ID                AS Location_Name,
                Loc.Tag               AS Location_Tag,
                Loc.Address           AS Location_Street,
                Loc.City              AS Location_City,
                Loc.State             AS Location_State,
                Loc.Zip               AS Location_Zip,
                Loc.Route             AS Route,
                Zone.Name             AS Division,
                OwnerWithRol.ID       AS Customer_ID,
                OwnerWithRol.Name     AS Customer_Name,
                OwnerWithRol.Status   AS Customer_Status,
                OwnerWithRol.Elevs    AS Customer_Elevators,
                OwnerWithRol.Address  AS Customer_Street,
                OwnerWithRol.City     AS Customer_City,
                OwnerWithRol.State    AS Customer_State,
                OwnerWithRol.Zip      AS Customer_Zip,
                OwnerWithRol.Contact  AS Customer_Contact,
                OwnerWithRol.Remarks  AS Customer_Remarks,
                OwnerWithRol.Email    AS Customer_Email,
                OwnerWithRol.Cellular AS Customer_Cellular,
                Elev.ID               AS Unit_ID,
                Elev.Unit             AS Unit_Label,
                Elev.State            AS Unit_State,
                Elev.Cat              AS Unit_Category,
                Elev.Type             AS Unit_Type,
                Emp.fFirst            AS Mechanic_First_Name,
                Emp.Last              AS Mechanic_Last_Name,
                Route.ID              AS Route_ID,
				Violation.ID          AS Violation_ID,
				Violation.fdate       AS Violation_Date,
				Violation.Status      AS Violation_Status,
				Violation.Remarks     AS Violation_Remarks
            FROM
                Job
                LEFT JOIN nei.dbo.Loc           ON Job.Loc      = Loc.Loc
                LEFT JOIN nei.dbo.Zone          ON Loc.Zone     = Zone.ID
                LEFT JOIN nei.dbo.JobType       ON Job.Type     = JobType.ID
                LEFT JOIN nei.dbo.OwnerWithRol  ON Job.Owner    = OwnerWithRol.ID
                LEFT JOIN nei.dbo.Elev          ON Job.Elev     = Elev.ID
                LEFT JOIN nei.dbo.Route         ON Loc.Route    = Route.ID
                LEFT JOIN nei.dbo.Emp           ON Emp.fWork    = Route.Mech
				LEFT JOIN nei.dbo.Violation     ON Job.ID       = Violation.Job
            WHERE
                Job.ID = ?
        ;",array($_GET['ID']));
        $Job = sqlsrv_fetch_array($r);?>
<!-- PROPOSALS TABLE -->
<div class="panel panel-primary">
    <!--<div class="panel-heading"><h3><i class="fa fa-bell fa-fw"></i> Proposals Table</h3></div>-->
    <div class="panel-body">
        <div id='Form_Proposal'>
            <div class="panel panel-primary">
                <div class="panel-heading" style='position:fixed;width:750px;z-index:999;'><h2 style='display:block;'>Location Form</h2></div>
                <div class="panel-body white-background BankGothic shadow" style='padding-top:100px;'>
                    <div style='display:block !important;'>
                        <fieldset >
                            <legend>Proposal Information</legend>
                            <editor-field name='ID'></editor-field>
                            <editor-field name='fDate'></editor-field>
                            <editor-field name='Contact'></editor-field>
                            <editor-field name='Location'></editor-field>
                            <editor-field name='Title'></editor-field>
                            <editor-field name='Cost'></editor-field>
                            <editor-field name='Price'></editor-field>
                        </fieldset>
                    </div>
                </div>
            </div>
        </div>
        <table id='Table_Proposals' class='display' cellspacing='0' width='100%' style='font-size:12px;'>
            <thead>
                <th title='ID of the Proposal'>ID</th>
                <th title='Date of the Proposal'>Date</th>
                <th title='Proposal Contact'>Contact</th>
                <th title='Location of the Proposal'>Location</th>
                <th title='Title of the Proposal'>Title</th>
                <?php if($_SESSION['Branch'] != 'Customer'){?><th title="Proposed Cost">Cost</th><?php }?>
                <th title='Proposed Amount'>Price</th>
            </thead>
        </table>
    </div>
</div>
</div>
<script>
var Editor_Proposals = new $.fn.dataTable.Editor({
    ajax: "php/post/Collection.php?ID=<?php echo $_GET['ID'];?>",
    table: "#Table_Proposals",
    template: '#Form_Proposal',
    formOptions: {
        inline: {
            submit: "allIfChanged"
        }
    },
    idSrc: "ID",
    fields : [{
        label: "Date",
        name: "fDate",
        type:"datetime"
    },{
        label:"ID",
        name:"ID"
    },{
        label:"Contact",
        name:"Contact"
    },{
        label:"Location",
        name:"Location",
        type:"select",
        options: [<?php
            $r = sqlsrv_query($NEI,"
                SELECT   Loc.Tag AS Location
                FROM     nei.dbo.Loc
                WHERE    Loc.Owner = ?
                GROUP BY Loc.Tag
                ORDER BY Loc.Tag ASC
            ;",array($_GET['ID']));
            $Locations = array();
            if($r){while($Location = sqlsrv_fetch_array($r)){$Locations[] = '{' . "label: '{$Location['Location']}', value:'{$Location['Location']}'" . '}';}}
            echo implode(",",$Locations);
        ?>]
    },{
        label:"Title",
        name:"Title"
    },{
        label:"Cost",
        name:"Cost"
    },{
        label:"Price",
        name:"Price"
    }]
});
Editor_Proposals.field('ID').disable();
//Editor_Collections.field('Invoice').hide();
/*$('#Table_Proposals').on( 'click', 'tbody td:not(:first-child)', function (e) {
Editor_Proposals.inline( this );
} );*/
var Table_Proposals = $('#Table_Proposals').DataTable( {
    "ajax": "cgi-bin/php/get/Proposals_by_Job.php?ID=<?php echo $_GET['ID'];?>",
    "columns": [
        {
            "data": "ID",
            "className":"hidden"
        },{
            "data": "fDate",
            "defaultContent":"Undated",
            render: function(data){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}
        },{
            "data": "Contact"
        },{
            "data": "Location",
            "className":"hidden"
        },{
            "data": "Title"
        },{
            "data": "Cost"
        },{
            "data": "Price"
        }

    ],
    "searching":false,
    "paging":false
} );
	function hrefProposals(){hrefRow("Table_Proposals","proposal");}
	$("Table#Table_Proposals").on("draw.dt",function(){hrefProposals();});
</script>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
