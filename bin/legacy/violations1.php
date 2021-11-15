<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [
        'read_and_close' => true
    ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    //Connection
    $Connection = \singleton\database::getInstance( )->query(
        null,
        "   SELECT  * 
            FROM    Connection 
            WHERE   Connector = ? 
                    AND Hash = ?;",
        array(
            $_SESSION['User'],
            $_SESSION['Hash']
        )
    );
    $Connection = sqlsrv_fetch_array($Connection);

    //User
    $User = \singleton\database::getInstance( )->query(null,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID= ?",array($_SESSION['User']));
    $User = sqlsrv_fetch_array($User);
    
    //Privileges
    $r = \singleton\database::getInstance( )->query(
        null,
        "   SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Privilege
            WHERE  User_ID = ?;",
        array($_SESSION['User']));
    $Privileges = array();
    while($Privilege = sqlsrv_fetch_array( $r )){ $Privileges[$Privilege['Access_Table']] = $Privilege;}
    $Privileged = FALSE;
    if( isset($Privileges['Violation']) 
        && $Privileges['Violation']['User_Privilege'] >= 4 
        && $Privileges['Violation']['Group_Privilege'] >= 4){$Privileged = TRUE;}

    if(!isset($Connection['ID'])  || !$Privileged){?><html><head><script>document.location.href='../login.php?Forward=violations.php';</script></head></html><?php }
    else {
      \singleton\database::getInstance( )->query(
        null,
        "   INSERT INTO Activity([User], [Date], [Page])
            VALUES(?,?,?);",
        array(
            $_SESSION['User'],
            date("Y-m-d H:i:s"), 
            'violations.php'
        )
    );
?><!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
    <style> 
        table#Table_Violations { font-size:10px;} 
        table#Table_Violations tbody tr { height:50px; }
        td.indent { width:0px; }
        table.dataTable tr.dtrg-group td{background-color:#1d1d1d;color:white;}
        table.dataTable tr.dtrg-group.dtrg-level-0 td{font-weight:bold}
        table.dataTable tr.dtrg-group.dtrg-level-1 td,table.dataTable tr.dtrg-group.dtrg-level-2 td{background-color:#5d5d5d;color:white;padding-top:0.25em;padding-bottom:0.25em;padding-left:2em;font-size:0.9em}
        table.dataTable tr[role='row'] td.sorting_2 {
            background-color:gold !important;
            color:black;
        }
    </style>
    <?php require( bin_meta . 'index.php');?>
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>
</head>
<body onload='finishLoadingPage();' style='background-color:#1d1d1d;'>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require(PROJECT_ROOT.'php/element/navigation.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content'>
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <div class='row'>
                        <div class='col-xs-10'><h4><?php \singleton\fontawesome::getInstance( )->Violation( 1 );?> Violations</div>
                        <div class='col-xs-2'><button style='width:100%;color:black;' onClick="$('#Filters').toggle();">+/-</button></div>
                    </div>
                </div>
                <div class="panel-body no-print" id='Filters' style='border-bottom:1px solid #1d1d1d;'>
                    <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
                    <div class='row'>
                        <div class='col-xs-4'>Search:</div>
                        <div class='col-xs-8'><input type='text' name='Search' placeholder='Search' onChange='redraw( );' /></div>
                    </div>
                    <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
                    <div class='row'>
                        <div class='col-xs-4'>Name:</div>
                        <div class='col-xs-8'><input type='text' name='Name' placeholder='Name' onChange='redraw( );' /></div>
                    </div>
                    <div class='row'>
                        <div class='col-xs-4'>Date:</div>
                        <div class='col-xs-8'><input type='text' name='Date' placeholder='Date' onChange='redraw( );' /></div>
                    </div>
                    <div class='row'>
                        <div class='col-xs-4'>Location:</div>
                        <div class='col-xs-8'><input type='text' name='Location' placeholder='Location' onChange='redraw( );' /></div>
                    </div>
                    <div class='row'>
                        <div class='col-xs-4'>Status:</div>
                        <div class='col-xs-8'><select name='Status' onChange='redraw( );'>
                            <option value=''>Select</option>
                            <option value='0'>Active</option>
                            <option value='1'>Inactive</option>
                        </select></div>
                    </div>
                    <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
                </div>
                <div class='panel-body'>
                    <table id='Table_Violations' class='display' cellspacing='0' width='100%'>
                        <thead><tr>
                            <th title='ID'>ID</th>
                            <th title='Name'>Name</th>
                            <th title="Date">Date</th>
                            <th title='Location'>Location</th>
                            <th title='Status'>Status</th>
                        </tr></thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <?php $_GET[ 'Datatables_Simple' ] = 1; ?>
    <?php require('bin/js/datatables.php');?>
    <script src='https://cdn.datatables.net/rowgroup/1.1.2/js/dataTables.rowGroup.min.js'></script>
    
    <script>
        var grouping_id = 5;
        var grouping_name = 'Status';
        var collapsedGroups = [];
        var groupParent = [];
        var Table_Violations = $('#Table_Violations').DataTable( {
            dom        : 'tlp',
            processing : true,
            serverSide : true,
            responsive : true,
            autoWidth  : false,
            paging     : false,
            searching  : false,
            columns    : [
                {
                    data      : 'ID',
                    className : 'hidden'
                },{
                    data : 'Name',
                    render: function ( d ){
                        return d == null 
                            ?   'Untitled'
                            :   d;
                    }
                },{
                    data : 'Date'
                },{
                    data : 'Location'
                },{
                    data : 'Status'
                }
            ],
            order: [ [ 4, 'asc' ], [2, 'asc' ] ],
            ajax : {
                data : function( d ){ 
                    d = {
                        start : d.start,
                        length : d.length,
                        order : {
                            column : d.order[0].column,
                            dir : d.order[0].dir
                        }
                    };
                    d.Search = $('input[name="Search"]').val( );
                    d.Name = $('input[name="Name"]').val( );
                    d.Location = $('input[name="Location"]').val( );
                    d.Date_Start = $('input[name="Date_Start"]').val( );
                    d.Date_End = $('input[name="Date_End"]').val( );
                    d.Status = $('input[name="Status"]').val( );
                    return d; 
                },
                url : 'bin/php/get/Violations.php'
            },
            drawCallback : function ( settings ) { hrefViolations( ); },
            rowGroup: { 
                // Uses the 'row group' plugin
                dataSrc: [
                  'Status',
                  'Location'
                ],
                startRender: function(rows, group, level) {
                    groupParent[level] = group;

                    var groupAll = '';
                    for (var i = 0; i < level; i++) {groupAll += groupParent[i]; if (collapsedGroups[groupAll]) {return;}}
                    groupAll += group;

                    if ((typeof(collapsedGroups[groupAll]) == 'undefined') || (collapsedGroups[groupAll] === null)) {collapsedGroups[groupAll] = true;} //True = Start collapsed. False = Start expanded.

                    var collapsed = collapsedGroups[groupAll];
                    var newTickets = 0;
                    rows.nodes().each(function(r) {
                        if(( $(r).children(':nth-child(2)').html() != 'On Site' && $(r).children(':nth-child(2)').html() != 'En Route'  && $(r).children(':nth-child(6)').html() != 'Yes') || $(r).children(':nth-child(2)').html() == 'Reviewing' || $(r).children(':nth-child(2)').html() == 'Signed' || $(r).children(':nth-child(2)').html() == 'Completed'){
                          r.style.display = (collapsed ? 'none' : '');
                        }
                        /*var start = new Date();
                        start.setHours(0,0,0,0);
                        var end = new Date();
                        end.setHours(23,59,59,999);

                        if( new Date($(r).children(':nth-child(3)').html()) >= start && new Date($(r).children(':nth-child(3)').html()) < end && $(r).children(':nth-child(2)').html() != 'Reviewing' && $(r).children(':nth-child(2)').html() != 'Signed' && $(r).children(':nth-child(2)').html() != 'Completed'){ newTickets++; }*/
                    });
                    var newString = '';
                    return $('<tr/>').append('<td colspan="'+rows.columns()[0].length+'">' + group  + ' ( ' + rows.count() + ' total' + newString + ' ) </td>').attr('data-name', groupAll).toggleClass('collapsed', collapsed);
                }
            }
        } );
        $('tbody').on('click', 'tr.dtrg-start', function () {
            var name = $(this).data('name');
            collapsedGroups[name] = !collapsedGroups[name];
            Table_Violations.draw( );
        });
        function redraw( ){ Table_Violations.draw( ); }
        function hrefViolations(){hrefRow("Table_Violations","violation");}
    </script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=violations.php';</script></head></html><?php }?>
