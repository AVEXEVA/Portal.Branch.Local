<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [
        'read_and_close' => true
    ] );
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/cgi-bin/php/index.php' );
}
if( isset( $_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
  $result = sqlsrv_query(
    $NEI,
    " SELECT  *
      FROM    Connection
      WHERE       Connection.Connector = ?
              AND Connection.Hash  = ?;",
    array(
      $_SESSION[ 'User' ],
      $_SESSION[ 'Hash' ]
    )
  );
  $Connection = sqlsrv_fetch_array( $result );
  //User
  $result = sqlsrv_query(
    $NEI,
    " SELECT  *,
              Emp.fFirst AS First_Name,
              Emp.Last   AS Last_Name
      FROM    Emp
      WHERE   Emp.ID = ?;",
    array(
      $_SESSION[ 'User' ]
    )
  );
  $User = sqlsrv_fetch_array( $result );
  //Privileges
  $result = sqlsrv_query(
    $NEI,
    " SELECT  Privilege.Access_Table,
              Privilege.User_Privilege,
              Privilege.Group_Privilege,
              Privilege.Other_Privilege
      FROM    Privilege
      WHERE   Privilege.User_ID = ?;",
    array(
      $_SESSION[ 'User' ]
    )
  );
  $Privileges = array();
  if( $result ){while( $Privilege = sqlsrv_fetch_array( $result ) ){ $Privileges[ $Privilege[ 'Access_Table' ] ] = $Privilege; } }
  if(     !isset( $Connection[ 'ID' ] )
      ||  !isset($Privileges[ 'Activity' ])
      ||  $Privileges[ 'Activity' ][ 'User_Privilege' ]  < 4
      ||  $Privileges[ 'Activity' ][ 'Group_Privilege' ] < 4
      ||  $Privileges[ 'Activity' ][ 'Other_Privilege' ] < 4
  ){
      ?><?php require( '../404.html' );?><?php
  } else {
    sqlsrv_query(
      $NEI,
      " INSERT INTO Activity( [User], [Date], [Page] )
        VALUES( ?, ?, ? );",
      array(
        $_SESSION[ 'User' ],
        date( 'Y-m-d H:i:s' ),
        'activity.php'
      )
   );
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php require( bin_meta . 'index.php');?>
    <title>Nouveau Texas | Portal</title>    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>
</head>
<body onload=''>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require(PROJECT_ROOT.'html/navigation.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content'>
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-primary">
                        <div class="panel-heading"><h3>Dispatch<div style='float:right'><button onClick='refresh_get();' style='color:black;'>Refresh</button></div></h3></div>
                        <div class="panel-heading" style='background-color:white;color:black;'>
                            <div class='row'>
                                <div class='col-xs-4'>
                                    <div class='row'>
                                        <div class='col-xs-4' style='text-align:right;'>
                                            <label for='Supers' style='text-align:right;'>Departments(s):</label>
                                        </div>
                                        <div class='col-xs-8'>
                                            <?php $Supervisors = (isset($_GET['Supervisors'])) ? (strpos($_GET['Supervisors'], ',') !== false) ? split(',',$_GET['Supervisors']) : array($_GET['Supervisors']) : array();?>
                                            <select id='Departments' name='Departments' multiple='multiple' size='7' style='max-width:100%;'>
                                                <?php
                                                if(!is_array($Supervisors)){$Supervisors = array($Supervisors);}?>
                                                <option value='Division 1' <?php if(in_array('Division 1',$Supervisors) || !isset($_GET['Supervisors']) || $_GET['Supervisors'] == 'undefined'){?>selected='selected'<?php }?>>Division 1</option>
                                                <option value='Division 2' <?php if(in_array('Division 2',$Supervisors)){?>selected='selected'<?php }?>>Division 2</option>
                                                <option value='Division 3' <?php if(in_array('Division 3',$Supervisors)){?>selected='selected'<?php }?>>Division 3</option>
                                                <option value='Division 4' <?php if(in_array('Division 4',$Supervisors)){?>selected='selected'<?php }?>>Division 4</option>
                                                <option value='Modernization' <?php if(in_array('Modernization',$Supervisors)){?>selected='selected'<?php }?>>Modernization</option>
                                                <option value='Repair' <?php if(in_array('Repair',$Supervisors)){?>selected='selected'<?php }?>>Repair</option>
                                                <option value='Escalator' <?php if(in_array('Escalator',$Supervisors)){?>selected='selected'<?php }?>>Escalator</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="panel-body">
                            <table width="100%" class="table table-striped table-bordered table-hover" id="Table_Tickets">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>First Name</th>
                                        <th>Last Name</th>
                                        <th>Location</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th>Scheduled</th>
                                    </tr>
                                </thead>
                                <tfooter>
                                    <tr>
                                        <th>ID</th>
                                        <th>First Name</th>
                                        <th>Last Name</th>
                                        <th>Location</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th>Scheduled</th>
                                    </tr>
                                </tfooter>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class='row'>
                <div class='col-md-12'>
                    <div id="map" style='height:500px;overflow:visible;width:100%;'></div>
                </div>
            </div>
    </div>

    <!-- Bootstrap Core JavaScript -->


    <!-- Metis Menu Plugin JavaScript -->


    <!-- Custom Theme JavaScript -->


    <?php require(PROJECT_ROOT.'js/datatables.php');?>


    <!--Moment JS Date Formatter-->

    <style>
    </style>
    <script>
        function hrefTickets(){
            $("#Table_Tickets tbody tr").each(function(){
                $(this).on('click',function(){
                    document.location.href="ticket.php?ID=" + $(this).children(":first-child").html();
                });
             });
        }
        $(document).ready(function() {
            var Table_Tickets = $('#Table_Tickets').DataTable({
                "responsive": true,
                "ajax":"cgi-bin/php/get/Dispatch.php?Supervisors=" + $("select[name='Departments']").val() + '&Mechanics=' + $("select[name='Mechanics']").val() + "&Start_Date=" + $("input[name='filter_start_date']").val() + "&End_Date=" + $("input[name='filter_end_date']").val(),
                "columns": [
                    {"data" : "ID"},
                    {"data" : "fFirst"},
                    {"data" : "Last"},
                    {"data" : "Tag"},
                    {"data" : "fDesc"},
                    {"data" : "Status"},
                    {"data" : "EDate"}
                ],
                "scrollX":true,
                "lengthMenu":[[10,25,50,100,500,-1],[10,25,50,100,500,"All"]],
                "initComplete":function(){
                    $("tr[role='row']>th:nth-child(5)").click().click();
                    hrefTickets();
                    $("input[type='search'][aria-controls='Table_Tickets']").on('keyup',function(){hrefTickets();});
                    $('#Table_Tickets').on( 'page.dt', function () {setTimeout(function(){hrefTickets();},100);});
                    $("#Table_Tickets th").on("click",function(){setTimeout(function(){hrefTickets();},100);});
                    finishLoadingPage();
                }
            });
        });
    </script>
    <script>
        function refresh_get(){
            var Supervisors = $("select[name='Departments']").val();
            var Mechanics = $("select[name='Mechanics']").val();
            var Start_Date = $("input[name='filter_start_date']").val();
            var End_Date = $("input[name='filter_end_date']").val();
            document.location.href='dispatch.php?Supervisors=' + Supervisors + '&Mechanics=' + Mechanics + "&Start_Date=" + Start_Date + "&End_Date=" + End_Date;
        }
    </script>
    <script>
        $(document).ready(function(){
            $("input.start_date").datepicker({onSelect:function(dateText, inst){refresh_get();}});
            $("input.end_date").datepicker({onSelect:function(dateText, inst){refresh_get();}});
            $("#Mechanics").html($("#Mechanics option").sort(function (a, b) {return a.text == b.text ? 0 : a.text < b.text ? -1 : 1}));
            $("#Departments").html($("#Departments option").sort(function (a, b) {return a.text == b.text ? 0 : a.text < b.text ? -1 : 1}));
        });
    </script>
    <!-- Filters-->

</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='login.php?Forward=dispatch.php';</script></head></html><?php }?>
