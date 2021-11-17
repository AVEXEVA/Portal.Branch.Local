<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
if( isset( $_SESSION[ 'Connection' ][ 'User' ], $_SESSION[ 'Connection' ][ 'Hash' ] ) ){
  //Connection
    $result = \singleton\database::getInstance( )->query(
      'Portal',
      " SELECT  [Connection].[ID]
        FROM    dbo.[Connection]
        WHERE       [Connection].[User] = ?
                AND [Connection].[Hash] = ?;",
      array(
        $_SESSION[ 'Connection' ][ 'User' ],
        $_SESSION[ 'Connection' ][ 'Hash' ]
      )
    );
    $Connection = sqlsrv_fetch_array($result);
    //User
	$result = \singleton\database::getInstance( )->query(
		null,
		" SELECT  Emp.fFirst  AS First_Name,
		          Emp.Last    AS Last_Name,
		          Emp.fFirst + ' ' + Emp.Last AS Name,
		          Emp.Title AS Title,
		          Emp.Field   AS Field
		  FROM  Emp
		  WHERE   Emp.ID = ?;",
		array(
		  	$_SESSION[ 'Connection' ][ 'User' ]
		)
	);
	$User   = sqlsrv_fetch_array( $result );
	//Privileges
	$Access = 0;
	$Hex = 0;
	$result = \singleton\database::getInstance( )->query(
		'Portal',
		"   SELECT  [Privilege].[Access],
                    [Privilege].[Owner],
                    [Privilege].[Group],
                    [Privilege].[Department],
                    [Privilege].[Database],
                    [Privilege].[Server],
                    [Privilege].[Other],
                    [Privilege].[Token],
                    [Privilege].[Internet]
		  FROM      dbo.[Privilege]
		  WHERE     Privilege.[User] = ?;",
		array(
		  	$_SESSION[ 'Connection' ][ 'User' ],
		)
	);
    $Privileges = array();
    if( $result ){while( $Privilege = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC ) ){

        $key = $Privilege['Access'];
        unset( $Privilege[ 'Access' ] );
        $Privileges[ $key ] = implode( '', array(
        	dechex( $Privilege[ 'Owner' ] ),
        	dechex( $Privilege[ 'Group' ] ),
        	dechex( $Privilege[ 'Department' ] ),
        	dechex( $Privilege[ 'Database' ] ),
        	dechex( $Privilege[ 'Server' ] ),
        	dechex( $Privilege[ 'Other' ] ),
        	dechex( $Privilege[ 'Token' ] ),
        	dechex( $Privilege[ 'Internet' ] )
        ) );
    }}
    if( 	!isset( $Connection[ 'ID' ] )
        ||  !isset( $Privileges[ 'Customer' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'Archive' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        \singleton\database::getInstance( )->query(
          null,
          " INSERT INTO Activity([User], [Date], [Page] )
            VALUES( ?, ?, ? );",
          array(
            $_SESSION[ 'Connection' ][ 'User' ],
            date('Y-m-d H:i:s'),
            'customers.php'
        )
      );
?><!DOCTYPE html>
<html lang="en">
<head>
    <?php require( bin_meta . 'index.php');?>
    <title>Nouveau Illinois Portal</title>    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>
</head>
<?php
if(is_numeric($_GET['Mechanic'])){
    $r = $database->query($conn,"SELECT Emp.* FROM Emp WHERE Emp.ID='" . $_GET['Mechanic']. "';");
    $r = sqlsrv_fetch_array($r);
    $Mechanic = $r;}
else {  $Mechanic = $User;  }?>
<body>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require( bin_php . 'element/navigation.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content'>
            <div class="row">
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-primary">
                        <div class="panel-heading"><h3><?php \singleton\fontawesome::getInstance( )->Archive();?>Archive<div style='float:right'><button onClick='refreshGet();' style='color:black;'>Refresh</button></div></h3></div>
                        </div>
                        <div class='panel-heading' style='background-color:white;color:black;'>
                            <div class='row'>
                                <div class='col-md-4'>
                                    <div class='row'>
                                        <div class='col-xs-4'><label class='date' for="filter_start_date">Start Date:</label></div>
                                        <div class='col-xs-8 input-group'><input class='start_date form-control' size='10'  style='max-width:80%;' name='filter_start_date' value='<?php echo strlen($_GET['Start_Date']) > 1 ? $_GET['Start_Date'] : '1/1/1980';?>' /></div>
                                    </div>
                                    <div class='row'>
                                        <div class='col-xs-4'><label class='date' for="filter_end_date">End Date:</label></div>
                                        <div class='col-xs-8 input-group'><input class='end_date form-control' size='10'  style='max-width:80%;' name='filter_end_date'  value='<?php echo strlen($_GET['End_Date']) > 1 ? $_GET['End_Date'] : '12/31/2017';?>'/><br /></div>
                                    </div>
                                    <hr>
                                </div>
                                <div class='col-md-4'>
                                    <script>
                                    function addCustomer(link){$("div#Customers").append("<div class='row Customer' rel='" + $(link).val() + "'><div class='col-xs-2'>&nbsp;</div><div class='col-xs-8'>" + $(link).children(":selected").text() + "</div><div class='col-xs-2 RemoveCustomer' onClick='removeCustomer(this);'>X</div></div>");}
                                    function removeCustomer(link){$(link).parent().remove();}
                                    </script>
                                    <div class='row'>
                                        <div class='col-xs-4' style='text-align:right;'>Customer:</div>
                                        <div class='col-xs-8'><select name="Customer" onChange='addCustomer(this);' class='form-control'>
                                        <?php
                                        $r = $database->query(null,"
                                            SELECT
                                                OwnerWithRol.ID,
                                                OwnerWithRol.Name
                                            FROM OwnerWithRol
                                            ORDER BY OwnerWithRol.Name");
                                        while($array = sqlsrv_fetch_array($r)){?><option value='<?php echo $array['ID'];?>'><?php echo proper($array['Name']);?></option><?php }?>
                                        </select></div>
                                    </div>
                                    <div class='row' id='Customers'><?php
                                        if(!isset($_GET['Customer_ID']) || $_GET['Customer_ID'] == "All" || $_GET['Customer_ID'] == "" || $_GET['Customer_ID'] == ","){}
                                        else {
                                            $Customer_ID = (isset($_GET['Customer_ID'])) ? (strpos($_GET['Customer_ID'], ',') !== false) ? explode(',',addslashes($_GET['Customer_ID'])) : array(addslashes($_GET['Customer_ID'])) : array();
                                            if(count($Customer_ID) > 0){
                                                $temp = array();
                                                foreach($Customer_ID as $Tag){$temp[] = "OwnerWithRol.ID = '" . $Tag . "'";}
                                                $Customer_ID = implode(" OR ",$temp);
                                            } else {
                                                $Customer_ID = "OwnerWithRol.ID = '" . $Customer_ID . "'";
                                            }
                                            $r = FALSE;
                                            $r = $database->query($conn,"
                                                SELECT *
                                                FROM OwnerWithRol
                                                WHERE {$Customer_ID}
                                            ;");
                                            if($r){
                                                while($array = sqlsrv_fetch_array($r)){?><div class='row Customer' rel='<?php echo $array['ID'];?>'><div class='col-xs-2'>&nbsp;</div><div class='col-xs-8'><?php echo $array['Name'];?></div><div class='col-xs-2 RemoveCustomer' onClick='removeCustomer(this);'>X</div></div><?php }
                                            }
                                        }
                                    ?></div>
                                </div>
                                <div class='col-md-4'>
                                    <script>
                                    function addLocation(link){$("div#Locations").append("<div class='row Location' rel='" + $(link).val() + "'><div class='col-xs-2'>&nbsp;</div><div class='col-xs-8'>" + $(link).children(":selected").text() + "</div><div class='col-xs-2 RemoveLocation' onClick='removeLocation(this);'>X</div></div>");}
                                    function removeLocation(link){$(link).parent().remove();}
                                    </script>
                                    <style>.RemoveLocation:hover {color:red;cursor:pointer;}.Location {padding:5px;border:1px solid black;}</style>
                                    <div class='row'>
                                        <div class='col-xs-4' style='text-align:right;'>Location:</div>
                                        <div class='col-xs-8'><select class='form-control' name="Location" onChange='addLocation(this);'>
                                        <?php
                                        $r = $database->query(null,"
                                            SELECT
                                                Loc.Loc,
                                                Loc.Tag
                                            FROM Loc
                                            WHERE Loc.Maint=1
                                            ORDER BY Loc.Tag");
                                        while($array = sqlsrv_fetch_array($r)){?><option value='<?php echo $array['Loc'];?>'><?php echo proper($array['Tag']);?></option><?php }?>
                                        </select></div>
                                    </div>
                                    <div class='row' id='Locations'><?php
                                        if(!isset($_GET['Location_ID']) || $_GET['Location_ID'] == "All" || $_GET['Location_ID'] == "" || $_GET['Location_ID'] == ","){}
                                        else {
                                            $Location_ID = (isset($_GET['Location_ID'])) ? (strpos($_GET['Location_ID'], ',') !== false) ? explode(',',addslashes($_GET['Location_ID'])) : array(addslashes($_GET['Location_ID'])) : array();
                                            if(count($Location_ID) > 0){
                                                $temp = array();
                                                foreach($Location_ID as $Tag){$temp[] = "Loc.Loc = '" . $Tag . "'";}
                                                $Location_ID = implode(" OR ",$temp);
                                            } else {
                                                $Location_ID = "Loc = '" . $Location_ID . "'";
                                            }
                                            $r = FALSE;
                                            $r = $database->query(null,"
                                                SELECT *
                                                FROM Loc
                                                WHERE {$Location_ID}
                                            ;");
                                            if($r){
                                                while($array = sqlsrv_fetch_array($r)){?><div class='row Location' rel='<?php echo $array['Loc'];?>'><div class='col-xs-2'>&nbsp;</div><div class='col-xs-8'><?php echo $array['Tag'];?></div><div class='col-xs-2 RemoveLocation' onClick='removeLocation(this);'>X</div></div><?php }
                                            }
                                        }
                                    ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="panel-body">
                            <table id='Table_Archive_Tickets' class='display' cellspacing='0' width='100%'>
                                <thead>
                                    <th title='ID of the Ticket'>ID</th>
                                    <th title='Location of the Ticket'>Location</th>
                                    <th title='Description of the Ticket'>Description</th>
                                    <th title='Description of the Ticket'>Resolution</th>
                                    <th title='Completed Work Time'>Completed</th>
                                    <th title='Total Hours'>Hours</th>
                                    <th>Unit State</th>
                                    <th>Unit Label</th>
                                    <th>Unit Description</th>
                                </thead>
                               <tfooter><th title='ID of the Ticket'>ID</th><th title='Location of the Ticket'>Location</th><th title='Description of the Ticket'>Description</th><th title='Description of the Ticket'>Resolution</th><th title='Completed Work Time'>Completed</th><th title='Total Hours'>Hours</th><th>Unit State</th><th>Unit Label</th><th>Unit Description</th></tfooter>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <?php require(PROJECT_ROOT.'js/datatables.php');?>




    <!-- Custom Date Filters-->

    <script>
        var reset_loc = 0;
        $(document).ready(function(){
            $("input.start_date").datepicker({
                onSelect:function(dateText, inst){
                    document.location.href="archive.php?Dashboard=Mechanic&Mechanic=<?php echo (isset($_GET['Mechanic'])) ? $_GET['Mechanic'] : $_SESSION['User'];?>&Start_Date=" + dateText + "&End_Date=" + $("input.end_date").val() + "&Location_Tag=" + $("select[name='filter_location_tag']").val() + "&Status=<?php echo $_GET['Status'];?>" + "&Show_Hours=" + $("input#show_hours[type='radio']:checked").val() + "&Show_Tickets=" + $("input#show_tickets[type='radio']:checked").val();
                }
            });
            $("input.end_date").datepicker({
                onSelect:function(dateText, inst){
                    document.location.href="archive.php?Dashboard=Mechanic&Mechanic=<?php echo (isset($_GET['Mechanic'])) ? $_GET['Mechanic'] : $_SESSION['User'];?>&Start_Date=" + $("input.start_date").val() + "&End_Date=" + dateText + "&Location_Tag=" + $("select[name='filter_location_tag']").val() + "&Status=<?php echo $_GET['Status'];?>" + "&Show_Hours=" + $("input#show_hours[type='radio']:checked").val() + "&Show_Tickets=" + $("input#show_tickets[type='radio']:checked").val();
                }
            });
        });

        function filter_location(){refresh_get();}
        function toggle_hours(){refresh_get();}
        function toggle_tickets(){refresh_get();}
        function format ( d ) {
            return '<table cellpadding="5" cellspacing="0" border="0" style="padding-left:50px;">'+
                '<tr>'+
                    '<td>Full name:</td>'+
                    '<td>'+d.name+'</td>'+
                '</tr>'+
                '<tr>'+
                    '<td>Extension number:</td>'+
                    '<td>'+d.extn+'</td>'+
                '</tr>'+
                '<tr>'+
                    '<td>Extra info:</td>'+
                    '<td>And any further details here (images etc)...</td>'+
                '</tr>'+
            '</table>';
        }
        function hrefTickets(){$("#Table_Archive_Tickets tbody tr").each(function(){$(this).on('click',function(){document.location.href="ticket.php?ID=" + $(this).children(":first-child").html();});});}
        var Location_Tags = "";
        function refreshLocationTags(){
            Location_Tags = "";
            $(".Location").each(function(){
                Location_Tags += "," + $(this).attr('rel');
            });
            Location_Tags = Location_Tags.substring(1);
            return Location_Tags;
        }
        var Customer_Tags = "";
        function refreshCustomerTags(){
            Customer_Tags = "";
            $(".Customer").each(function(){
                Customer_Tags += "," + $(this).attr('rel');
            });
            Customer_Tags = Customer_Tags.substring(1);
            return Customer_Tags;
        }
        var table = null;
        $(document).ready(function() {
            refreshLocationTags();
            <?php /*if(isset($_GET['deferLoading'])){?>finishLoadingPage();<?php }*/?>
            <?php if(count($_GET) > 0){?>var Table_Archive_Tickets = $('#Table_Archive_Tickets').DataTable( {
                "ajax": {
                    url:"bin/php/get/archive.php",
                    type: "GET",
                    data:function(d){
                        d.Start_Date = $("input.start_date").val();
                        d.End_Date = $("input.end_date").val();
                        d.Location_ID = refreshLocationTags();
                        d.Customer_ID = refreshCustomerTags();
                    },
                    complete:function(){
                        setTimeout(function(){
                            //$("tr[role='row']>th:nth-child(5)").click().click();
                            hrefTickets();
                            $("input[type='search'][aria-controls='Table_Archive_Tickets']").on('keyup',function(){hrefTickets();});
                            $('#Table_Archive_Tickets').on( 'page.dt', function () {setTimeout(function(){hrefTickets();},100);});
                            $("#Table_Archive_Tickets th").on("click",function(){setTimeout(function(){hrefTickets();},100);});
                        },100);
                    },
                    "dataSrc":function(json){
                        if(!json.data){json.data = [];}
                        return json.data;
                    }
                },
                <?php if(isset($_GET['deferLoading'])){?>"deferLoading":0,<?php }?>
                "columns": [
                    {   "data"         : "ID" },
                    {   "data"         : "Tag"},
                    {   "data"         : "fDesc"},
                    {   "data"         : "DescRes"},
                    {   "data"         : "EDate"},
                    {
                        "data"           : "Total",
                        "defaultContent" : "0"
                    },
                    {
                        "data"           : "Unit_State",
                        "visible"        : false,
                        "searchable"     : true
                    },
                    {
                        "data"           : "Unit_Label",
                        "visible"        : false,
                        "searchable"     : true
                    },
                    {
                        "data"           : "Unit_Description",
                        "visible"        : false,
                        "searchable"     : true
                    }
                ],
                "order": [[1, 'asc']],
                "language":{"loadingRecords":""},
                "lengthMenu": [[10,25,50,100,500,-1],[10,25,50,100,500,"All"]],
                "initComplete":function(){
                    //$("tr[role='row']>th:nth-child(5)").click().click();
                    hrefTickets();
                    $("input[type='search'][aria-controls='Table_Archive_Tickets']").on('keyup',function(){hrefTickets();});
                    $('#Table_Archive_Tickets').on( 'page.dt', function () {setTimeout(function(){hrefTickets();},100);});
                    $("#Table_Archive_Tickets th").on("click",function(){setTimeout(function(){hrefTickets();},100);});
                    $("select[name='Table_Archive_Tickets_length']").on("click",function(){setTimeout(function(){hrefTickets();},100);});
                    finishLoadingPage();
                },
                "deferLoading":0
            } );<?php } else {?>finishLoadingPage();var Table_Archive_Tickets = $('#Table_Archive_Tickets').DataTable( {} );<?php }?>
        } );
        function refreshGet(){
            //refreshLocationTags();
            //table.ajax.reload(null,false);
            document.location.href="archive.php?Start_Date=" + $("input.start_date").val() + "&End_Date=" + $("input.end_date").val() + "&Customer_ID=" + refreshCustomerTags() + "&Location_ID=" + refreshLocationTags();
        }

    </script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=archive.php';</script></head></html><?php }?>
