<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/cgi-bin/php/index.php' );
}
if( isset( $_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
    $result = sqlsrv_query(
        $NEI,
        "   SELECT  *
		        FROM        Connection
            WHERE       Connection.Connector = ?
            AND         Connection.Hash  = ?;",
        array(
            $_SESSION[ 'User' ],
            $_SESSION[ 'Hash' ]
        )
    );
    $Connection = sqlsrv_fetch_array( $result );
    //User
    $result = sqlsrv_query(
        $NEI,
        "   SELECT  *,
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
        "   SELECT  *
            FROM    Privilege
            WHERE   Privilege.User_ID = ?;",
        array(
            $_SESSION[ 'User' ]
        )
    );
	$Privileges = array();
	if( $result ){while( $Privilege = sqlsrv_fetch_array( $result ) ){ $Privileges[ $Privilege[ 'Access_Table' ] ] = $Privilege; } }
    if(	!isset( $Connection[ 'ID' ] )
	   	|| !isset($Privileges[ 'Admin' ])
	  		|| $Privileges[ 'Admin' ][ 'User_Privilege' ]  < 4
	  		|| $Privileges[ 'Admin' ][ 'Group_Privilege' ] < 4
        || $Privileges[ 'Admin' ][ 'Other_Privilege' ] < 4){
				?><?php require( '../404.html' );?><?php }
    else {
if(     count( $_POST ) > 0
    &&  is_numeric( $_POST[ 'Severity' ] )
    &&  strlen( $_POST[ 'Name' ] ) > 0
    && strlen( $_POST[ 'Description' ] ) > 0
) {
  $Name        = $_POST[ 'Name' ];
  $Severity    = $_POST[ 'Severity' ];
  $Description = $_POST[ 'Description' ];
  $Suggestion  = $_POST[ 'Suggestion' ];
  $Parameters  = array(
    $Name,
    $Severity,
    $Description,
    $Suggestion
  );
  $result = sqlsrv_query(
    $Portal,
    " INSERT INTO Bug(Name, Severity, Description, Suggestion)
      VALUES(?,?,?,?);",
    $Parameters
  );
}?><!DOCTYPE html>
<html lang="en">
<head>
    <?php require( PROJECT_ROOT . 'php/meta.php' );?>
    <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
    <?php $_GET[ 'Bootstrap' ] = '5.1';?>
    <?php require('cgi-bin/css/index.php');?>
    <style>
      .form-group>label:first-child {
          min-width  : 175px;
          text-align : right;
      }
      .hoverGray:hover {
          background-color : gold !important;
      }
      table#Table_Bugs tbody tr, table#Table_Bugs tbody tr td a {
          color : black !important;
      }
      table#Table_Bugs tbody tr:nth-child( even ) {
          background-color : rgba( 240, 240, 240, 1 ) !important;
      }
      table#Table_Bugs tbody tr:nth-child( odd ) {
          background-color : rgba( 255, 255, 255, 1 ) !important;
      }
      .paginate_button {
        background-color : rgba( 255, 255, 255, .7 ) !important;
      }
      .paginate_button:hover {
        color : white !important;
      }
      </style>
    <?php require( PROJECT_ROOT . 'js/index.php' );?>
</head>
<body onload='finishLoadingPage();'>
    <div id="wrapper">
        <?php require(PROJECT_ROOT.'php/element/navigation/index.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content'>
          <div class="panel panel-primary">
            <div class='panel-heading'><h4><?php $Icons->Contract( );?> Contracts</h4></div>
                <div class='panel-body no-print' id='Filters' style='border-bottom:1px solid #1d1d1d;'>
                    <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
                    <div class='form-group row'>
                        <label class='col-auto'>Search:</label>
                        <div class='col-auto'><input type='text' name='Search' placeholder='Search' onChange='redraw( );' /></div>
                    </div>
                    <div class='form-group row'><div class='col-xs-12'>&nbsp;</div></div>
                    <div class='form-group row'>
                        <label class='col-auto'>ID:</label>
                        <div class='col-auto'><input type='text' name='ID' placeholder='ID' onChange='redraw( );' /></div>
                    </div>
                    <div class='form-group row'>
                        <label class='col-auto'>Name:</label>
                        <div class='col-auto'><input type='text' name='Name' placeholder='Name' onChange='redraw( );' /></div>
                    </div>
                    <div class='form-group row'>
                        <label class='col-auto'>Description:</label>
                        <div class='col-auto'><input type='text' name='Description' placeholder='Description' onChange='redraw( );' /></div>
                    </div>
                    <div class='form-group row'>
                        <label class='col-auto'>Severity:</label>
                        <div class='col-auto'><select name='Severity' onChange='redraw( );'>
                            <option value=''>Select</option>
                            <?php 
                                $result = sqlsrv_query( 
                                    $Portal,
                                    "   SELECT  *
                                        FROM    Portal.dbo.Severity;",
                                    array( )
                                ); 
                                if( $result ){ while( $row = sqlsrv_Fetch_array( $result ) ){ ?><option value='<?php echo $row['ID'];?>'><?php echo $row['Name'];?></option><?php }}
                            ?>
                        </select></div>
                    </div>
                    <div class='form-group row'><div class='col-auto'>&nbsp;</div></div>
                </div>
                <div class='panel-body'>
                    <table id='Table_Bugs' class='display' cellspacing='0' width='100%'>
                        <thead><tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Severity</th>
                            <th>Suggestion</th>
                            <th>Resolution</th>
                            <th>Fixed</th>
                        </tr></thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
  
  <?php require(PROJECT_ROOT.'js/datatables.php');?>
  
  <script>
    var Table_Bugs = $('#Table_Bugs').DataTable( {
        dom        : 'tp',
        processing : true,
        serverSide : true,
        responsive : true,
        autoWidth  : false,
        paging     : true,
        searching  : false,
        ajax       : {
        url : 'cgi-bin/php/get/Bugs.php',
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
                d.ID = $('input[name="ID"]').val( );
                d.Name = $('input[name="Name"]').val( );
                d.Description = $('input[name="Description"]').val( );
                d.Severity = $('select[name="Severity"]').val( );
                return d;
            }
        },
        columns: [
            {
                data    : 'ID'
            },{
                data    : 'Name'
            },{
                data    : 'Description'
            },{
                data    : 'Severity'
            },{
                data    : 'Suggestion'
            },{
                data    : 'Resolution'
            },{
                data    : 'Fixed'
            }
        ]
    } );
    function redraw( ){ Table_Bugs.draw(); }
    function hrefBugs(){hrefRow('Table_Bugs','bug');}
    $('Table#Table_Bugs').on('draw.dt',function(){hrefBugs();});
  </script>
</body>
</html>
 <?php
    }
} else {
?><html><head><script>document.location.href='../login.php?Forward=profile.php';</script></head></html><?php }?>
