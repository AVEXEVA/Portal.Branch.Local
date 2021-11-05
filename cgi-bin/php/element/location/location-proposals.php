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
    if(isset($My_Privileges['Location']) && $My_Privileges['Location']['User_Privilege'] >= 4 && $My_Privileges['Location']['Group_Privilege'] >= 4 && $My_Privileges['Location']['Other_Privilege'] >= 4){$Privileged = TRUE;}
    elseif($My_Privileges['Location']['User_Privilege'] >= 4 && is_numeric($_GET['ID'])){
        $r = sqlsrv_query(  $NEI,"
		SELECT 	*
		FROM 	TicketO
		WHERE 	TicketO.LID='{$_GET['ID']}'
				AND fWork='{$My_User['fWork']}'");
        $r2 = sqlsrv_query( $NEI,"
		SELECT 	*
		FROM 	TicketD
		WHERE 	TicketD.Loc='{$_GET['ID']}'
				AND fWork='{$My_User['fWork']}'");
        $r3 = sqlsrv_query( $NEI,"
		SELECT 	*
		FROM 	TicketDArchive
		WHERE 	TicketDArchive.Loc='{$_GET['ID']}'
				AND fWork='{$My_User['fWork']}'");
        $r = sqlsrv_fetch_array($r);
        $r2 = sqlsrv_fetch_array($r2);
		$r3 = sqlsrv_fetch_array($r3);
        $Privileged = (is_array($r) || is_array($r2) || is_array($r3)) ? TRUE : FALSE;
    }
    sqlsrv_query($NEI,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "location.php"));
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
                    LEFT JOIN Terr         		   ON Terr.ID    = Loc.Terr
            WHERE
                    Loc.Loc = ?
        ;",array($_GET['ID']));
        $Location = sqlsrv_fetch_array($r);
        $data = $Location;?>
<div class="panel panel-primary">
    <!--<div class="panel-heading"><h3><i class="fa fa-bell fa-fw"></i> Proposals Table</h3></div>-->
    <div class="panel-body  BankGothic shadow">
        <div id='Form_Proposal'>
            <div class="panel panel-primary">
                <div class="panel-heading" style='position:fixed;width:750px;z-index:999;'><h2 style='display:block;'>Location Form</h2></div>
                <div class="panel-body  BankGothic shadow" style='padding-top:100px;'>
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
                FROM     Loc
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
    "ajax": "cgi-bin/php/get/Proposals_by_Location.php?ID=<?php echo $_GET['ID'];?>",
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
