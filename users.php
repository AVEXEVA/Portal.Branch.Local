<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [ 'read_and_close' => true ] ); 
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/bin/php/index.php' );
}
if( isset( $_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
	//Connection
    $result = $database->query(
    	null,
    	"	SELECT 	* 
    		FROM 	Connection 
    		WHERE 		Connector = ? 
    				AND Hash = ?;",
    	array(
    		$_SESSION['User'],
    		$_SESSION['Hash']
    	)
    );
    $Connection = sqlsrv_fetch_array( $result );
    $User = $database->query(null,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID= ?",array($_SESSION['User']));
    $User         = sqlsrv_fetch_array($User);
    $Field        = ($User['Field'] == 1 && $User['Title'] != 'OFFICE') ? True : False;
    $r            = $database->query(null,"
        SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
        FROM   Privilege
        WHERE User_ID = '{$_SESSION['User']}'
    ;");
    $Privileges   = array();
    while($array2 = sqlsrv_fetch_array($r)){$Privileges[$array2['Access_Table']] = $array2;}
    $Privileged   = FALSE;
    if(isset($Privileges['Ticket']) && $Privileges['Ticket']['User_Privilege'] >= 4 && $Privileges['Ticket']['Group_Privilege'] >= 4 && $Privileges['Ticket']['Other_Privilege'] >= 4){$Privileged = TRUE;}
    $database->query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "users.php"));
    if(!isset($Connection['ID'])  || !$Privileged){?><html><head><script>document.location.href='../login.php?Forward=users.php';</script></head></html><?php }
    else {
?><!DOCTYPE html>
<html lang="en">
<head>
    <?php require( PROJECT_ROOT . 'php/meta.php');?>
    <title>Nouveau Elevator Portal</title>
    <?php require( PROJECT_ROOT . 'css/index.php');?>
    <style>#Filters { max-width: 500px; }</style>
    <?php require( PROJECT_ROOT . 'js/index.php' );?>

</head>
<body onload='finishLoadingPage();' style='background-color:#3d3d3d;'>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require(PROJECT_ROOT.'php/element/navigation/index.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content'>
            <div class="panel panel-primary" style='margin-bottom:0px;'>
                <div class="panel-heading">
                    <div class='row'>
                        <div class='col-xs-10'><?php \singleton\fontawesome::getInstance( )->Users( 1 );?> Users</div>
                        <div class='col-xs-2'><button style='width:100%;color:black;' onClick="$('#Filters').toggle();">+/-</button></div>
                    </div>
                </div>
                <div class="panel-body no-print" id='Filters' style='border-bottom:1px solid #1d1d1d;'>
                    <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
                    <div class='row'>
                        <div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Search( 1 );?> Search:</div>
                        <div class='col-xs-8'><input type='text' name='Search' placeholder='Search' onChange='redraw( );' /></div>
                    </div>
                    <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
                    <div class="row"> 
                        <div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Supervisor( 1 );?> Supervisor:</div>
                        <div class='col-xs-8'><select name='Supervisor' onChange='redraw( );'><option value=''>Select</option><?php 
                            $result = $database->query(
                                null,
                                "   SELECT      tblWork.Super AS Supervisor
                                    FROM        tblWork 
                                    GROUP BY    tblWork.Super
                                    ORDER BY    tblWork.Super ASC;",
                                array( )
                            );
                            if( $result ){while( $row = sqlsrv_fetch_Array( $result ) ){?><option value='<?php echo $row[ 'Supervisor' ];?>'><?php echo $row[ 'Supervisor' ];?></option><?php 
                            }}
                        ?></select></div>
                    </div>
                    
                    <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
                </div>
                <style>
                    @media screen and ( max-width : 780px ){
                        #Table_Users {
                            font-size:8px;    
                        }
                    }
                </style>
                <div class="panel-body">
                    <table id='Table_Users' class='display' cellspacing='0' width='100%'>
                        <thead><tr>
                            <th title='ID'>ID</th>
                            <th title='First Name'>First Name</th>
                            <th title='Last Name'>Last Name</th>
                            <th title='Email'>Email</th>
                        </tr></thead>
                        <tfoot><tr>
                            <th title='ID'>ID</th>
                            <th title='First Name'>First Name</th>
                            <th title='Last Name'>Last Name</th>
                            <th title='Email'>Email</th>
                        </tr></tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>
    <?php require(PROJECT_ROOT.'js/datatables.php');?>
    
    <script>

    var Table_Users = $('#Table_Users').DataTable( {
        processing : true,
        serverSide : true,
        responsive : true,
        dom : 'tp',
        ajax: {
                url     : 'bin/php/get/Users2.php',
                data:function(d){
                    d = {
                        start : d.start, 
                        length : d.length,
                        order : {
                            column : d.order[0].column,
                            dir : d.order[0].dir
                        },
                        
                    };
                    d.Search        = $('input[name="Search"]').val()
                    d.Supervisor    = $('select[name="Supervisor"]').val();
                    return d;
                }
        },
        columns: [
            {
                data      : 'Branch_ID',
                className : 'hidden'
            },{
                data : 'First_Name'
            },{
                data : 'Last_Name'
            },{
                data : 'Email'
            }
        ],
        lengthMenu : [ 
            [ 10, 25, 50, 100, 500, -1 ],
            [ 10, 25, 50, 100, 500, 'All' ]
        ],
        lengthChange : false,
        order : [[0, 'asc']],
        language : { 'loadingRecords' : ''},
        initComplete : function(){ },
        drawCallback : function ( settings ) {
            hrefUsers(this.api());
        }
    } );
    function redraw(){
        Table_Users.draw( );
    }
    function hrefUsers( tbl ){
      $( 'table#Table_Users tbody tr' ).each( function( ){
        $( this ).on( 'click' , function( ){ document.location.href = 'user.php?ID=' + tbl.row(this).data().Branch_ID; });
      });
    }
    </script>
</body>
</html>
<?php }
}?>