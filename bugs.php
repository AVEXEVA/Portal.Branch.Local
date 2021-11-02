<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( );
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
    <?php require(PROJECT_ROOT.'php/element/loading.php');?>
    <div id="page-wrapper" class='content'>
      <div class="panel panel-primary">
        <div class="panel-heading"><h2>Bugs</h2></div>
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
                <tbody><?php
            $result = sqlsrv_query($Portal,
                "   SELECT  Bug.ID,
                            Bug.Name,
                            Bug.Description,
                            Bug.Resolution,
                            Bug.Fixed,
                            Bug.Suggestion,
                            Severity.Name AS Severity
                    FROM    Bug
                            LEFT JOIN Severity ON Bug.Severity = Severity.ID;"
          );
          if($result){while($Bug = sqlsrv_fetch_array($result)){
            ?><tr>
                <td><?php echo $Bug['Name'];?></td>
                <td><?php echo $Bug['Severity'];?></td>
                <td><?php echo $Bug['Description'];?></td>
                <td><?php echo strlen($Bug['Suggestion']) > 0 ? $Bug['Suggestion'] : '&nbsp;';?></td>
                <td><?php echo strlen($Bug['Resolution']) > 0 ? $Bug['Resolution'] : '&nbsp;';?></td>
                <td><?php echo strlen($Bug['Fixed']) > 0 ? $Bug['Fixed'] : '&nbsp;';?></td>
            </tr>
            <?php
          }}?></tbody></table>
        </div>
      </div>
    </div>
  </div>
  <script src="https://www.nouveauelevator.com/vendor/bootstrap/js/bootstrap.min.js"></script>
  <?php require(PROJECT_ROOT.'js/datatables.php');?>
  <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
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
        url : 'cgi-bin/php/get/Contracts2.php',
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
                d.Customer = $('input[name="Customer"]').val( );
                d.Location = $('input[name="Location"]').val( );
                d.Start_Date = $('input[name="Start_Date"]').val( );
                d.End_Date = $('input[name="End_Date"]').val( );
                d.Cycle = $('select[name="Cycle"]').val( );
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
    } );
  </script>
</body>
</html>
 <?php
    }
} else {
?><html><head><script>document.location.href='../login.php?Forward=profile.php';</script></head></html><?php }?>
