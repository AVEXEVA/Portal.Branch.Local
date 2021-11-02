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
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    $User = sqlsrv_query($NEI,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID= ?",array($_SESSION['User']));
    $User = sqlsrv_fetch_array($User);
    $Field = ($User['Field'] == 1 && $User['Title'] != "OFFICE") ? True : False;
    sqlsrv_query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "personnel_request.php"));
    if(!isset($array['ID']) ){?><html><head><script>document.location.href='../login.php?Forward=personnel_request.php';</script></head></html><?php }
    else {
$Mechanic = is_numeric($_SESSION['User']) ? $_SESSION['User'] : -1;

if($Mechanic > 0){
    $Call_Sign = "";
    $r = sqlsrv_query($NEI,"
        SELECT 
            Emp.*, 
            Emp.Last as Last_Name, 
            Rol.*, 
            PRWage.Reg as Wage_Regular, 
            PRWage.OT1 as Wage_Overtime, 
            PRWage.OT2 as Wage_Double_Time 
        FROM 
            (Emp LEFT JOIN PRWage ON Emp.WageCat = PRWage.ID) 
            LEFT JOIN Rol ON Emp.Rol = Rol.ID 
        WHERE Emp.ID = " . $_SESSION['User']);
    $User = sqlsrv_fetch_array($r);
    $Call_Sign = $array['CallSign'];
    $Alias = $array['fFirst'][0] . $array['Last'];
    $Employee_ID = $array['fWork'];
    while($a= sqlsrv_fetch_array($r)){}
}?><!DOCTYPE html>
<html lang="en">
<head>
    <?php require(PROJECT_ROOT.'php/meta.php');?>    
    <title>Nouveau Texas | Portal</title>    
    <?php require(PROJECT_ROOT."css/index.php");?>
    <?php require(PROJECT_ROOT.'js/index.php');?>
</head>

<body onload="finishLoadingPage();">
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
       <?php require(PROJECT_ROOT.'php/element/navigation/index2.php');?>
        <?php require(PROJECT_ROOT.'php/element/loading.php');?>
        <div id="page-wrapper" class='content'>
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Personnel Request</h1>
                </div>
            </div>
            <div class='col-md-12'><form>
                    <div class='panel panel-red'>
                        <div class='panel-heading'>
                            Request Details
                        </div>
                        <div class='panel-body'>
                            <div class='row'>
                                <div class='col-xs-4'>
                                    <b>Name</b>
                                </div>
                                <div class='col-xs-8 input-group'>
                                    <input type='text' class='form-control' name='Full_Name' value='<?php echo proper($User['Last_Name']);?>, <?php echo proper($User['fFirst']);?> <?php echo proper($User['Middle']);?>' disabled='disabled' />
                                </div>
                            </div>
                            <div class='row'>
                                <div class='col-xs-4'>
                                    <b>Date of Request</b>
                                </div>
                                <div class='col-xs-8 input-group'>
                                    <input class='form-control' type='text' name='Date_of_Request' size='10' />
                                </div>
                            </div>
                            <div class='row'>
                                <div class='col-xs-4'>
                                    <b>Type of Support</b>
                                </div>
                                <div class='col-xs-8 input-group'>
                                    <select name='Type_of_Support' class='form-control'>
                                        <option value='Foreman'>Foreman</option>
                                        <option value='Engineer'>Engineer</option>
                                        <option value='Team'>Team</option>
                                        <option value='Mechanic'>Mechanic</option>
                                        <option value='Helper' selected='selected'>Helper</option>
                                        <option value='Other'>Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class='row'>   
                                <div class='col-xs-4'><b>Job:</b></div>
                                <div class='col-xs-8 input-group'>
                                    <input id="job" name='Job'" class='form-control'>
                                    <input name='Job' type="hidden" id="job-id">
                                    <style>p#job-description{margin:0px;}</style>
                                    <p id="job-description"></p>
                                </div>
                            </div>
                            <div class='row'>
                                <div class='col-xs-4'>
                                    <b>Reason</b>
                                </div>
                                <div class='col-xs-8 input-group'>
                                    <textarea name='Reason' class='form-control' col='10'></textarea>
                                </div>
                            </div>
                            <br />
                            <hr />
                            <br />
                            <div class='row'>
                                <div class='col-xs-12 input-group'>
                                    <input type='submit' class='form-control' value='Submit Safety Report' />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form></div>
        </div>
    </div>
    <!-- Bootstrap Core JavaScript -->
    <script src="https://www.nouveauelevator.com/vendor/bootstrap/js/bootstrap.min.js"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="https://www.nouveauelevator.com/vendor/metisMenu/metisMenu.js"></script>

    <!-- Custom Theme JavaScript -->
    <script src="../dist/js/sb-admin-2.js"></script>

    <!-- JQUERY UI Javascript -->
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

    <script>
        var reset_loc = 0;
        $(document).ready(function(){
            $("input[name='Date_of_Incident']").datepicker({});
        });
    </script>
    <script>
    $(document).ready(function(){
        var Tickets = [<?php 
                $r = sqlsrv_query($NEI,"SELECT Job.ID, Job.fDesc as Description, JobType.Type as Type, Loc.Tag FROM Job LEFT JOIN nei.dbo.JobType ON Job.Type = JobType.ID LEFT JOIN TicketD ON Job.ID = TicketD.Job LEFT JOIN nei.dbo.Loc ON Job.Loc = Loc.Loc LEFT JOIN Emp ON TicketD.fWork = Emp.fWork where Emp.ID='{$_SESSION['User']}'");
                $Jobs = array();
                while($Job = sqlsrv_fetch_array($r)){
                    $Job['fDesc'] = preg_replace( "/\r|\n/", "; ", $Job['fDesc'] );
                    $Jobs[] = $Job;}
                $Duplicate_Jobs = array();
                $Selected_Jobs = array();
                foreach($Jobs as $Job){if(!in_array($Job['ID'], $Duplicate_Jobs)){$Selected_Jobs[] = "{value:'{$Job['ID']}', label:'{$Job['ID']}', desc:'{$Job['Tag']}; {$Job['Type']}; {$Job['Description']}'}";$Duplicate_Jobs[]=$Job['ID'];}}
                echo implode(",",$Selected_Jobs);
                unset($Selected_Jobs,$Duplicate_Jobs,$Jobs);
            ?>];
            $( "#job" ).autocomplete({
              minLength: 0,
              source: Tickets,
              focus: function( event, ui ) {
                $( "#job" ).val( ui.item.label );
                return false;
              },
              select: function( event, ui ) {
                $( "#job" ).val( ui.item.label );
                $( "#job-id" ).val( ui.item.value );
                $( "#job-description" ).html( ui.item.desc );
                return false;
              }
            })
            .autocomplete( "instance" )._renderItem = function( ul, item ) {
              return $( "<li>" )
                .append( "<div>" + item.label + "<br>" + item.desc + "</div>" )
                .appendTo( ul );
            };
        });
    </script>
</body>
</html>
 <?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=personnel_request.php';</script></head></html><?php }?>