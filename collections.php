<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( );
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/cgi-bin/php/index.php' );
}
if(isset($_SESSION['User'],$_SESSION['Hash'])){
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
    $My_Connection = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
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
    $My_User = sqlsrv_fetch_array($result);
    //Privileges
  $result = sqlsrv_query(
    $NEI,
    "  SELECT *
    	 FROM   Privilege
    	 WHERE  Privilege.User_ID = ?;",
  array(
       $_SESSION[ 'User' ]
     )
  );
    	$My_Privileges = array();
    	if( $result ){while( $My_Privilege = sqlsrv_fetch_array( $result ) ){ $My_Privileges [ $My_Privilege [ 'Access_Table' ] ] = $My_Privilege;} }
        if(!isset( $My_Connection [ 'ID' ] )
    	   	|| !isset( $My_Privileges ['Invoice' ] )
    	  		|| $My_Privileges['Invoice']['User_Privilege']  < 4
    	  		|| $My_Privileges['Invoice']['Group_Privilege'] < 4
    	  		|| $My_Privileges['Invoice']['Other_Privilege'] < 4){
    				?><?php require('../404.html');?><?php }
        else {
    		sqlsrv_query(
          $NEI,
          "   INSERT INTO Activity([User], [Date], [Page])
    			    VALUES(?, ?, ?);",
          array(
              $_SESSION[ 'User' ],
              date('Y-m-d H:i:s'),
              'collections.php'
          )
    );
?><!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <meta name='description' content=''>
    <meta name='author' content='Peter D. Speranza'>
    <title>Nouveau Texas | Portal</title>
    <?php require(PROJECT_ROOT.'css/index.php');?>
    <?php require(PROJECT_ROOT.'js/index.php');?>
</head>
<body onload='finishLoadingPage();' style='background-color:#1d1d1d;'>
    <div id='wrapper' class=''>
        <?php require(PROJECT_ROOT.'php/element/navigation/index.php');?>
        <?php require(PROJECT_ROOT.'php/element/loading.php');?>
        <div id='page-wrapper' class='content'>
            <div class='panel panel-primary'>
                <div class="panel-heading">
                    <div class='row'>
                        <div class='col-xs-10'><h4><?php $Icons->Invoice( 1 );?> Collections</div>
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
                    	<div class='col-xs-4'>Customer:</div>
                    	<div class='col-xs-8'><input type='text' name='Customer' placeholder='Customer' onChange='redraw( );' /></div>
                    </div>
                    <div class='row'>
                    	<div class='col-xs-4'>Location:</div>
                    	<div class='col-xs-8'><input type='text' name='Location' placeholder='Location' onChange='redraw( );' /></div>
                    </div>
                    <div class='row'>
                    	<div class='col-xs-4'>Job:</div>
                    	<div class='col-xs-8'><input type='text' name='Job' placeholder='Job' onChange='redraw( );' /></div>
                    </div>
                    <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
                </div>
                <div class='panel-body'>
                    <table id='Table_Collections' class='display' cellspacing='0' width='100%' style='font-size:12px;'>
                        <thead>
                            <th>Invoice #</th>
                            <th>Customer</th>
                            <th>Location</th>
                            <th>Job</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Due</th>
                            <th>Original</th>
                            <th>Balance</th>
                            <th>Description</th>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src='https://www.nouveauelevator.com/vendor/bootstrap/js/bootstrap.min.js'></script>
	<?php require('cgi-bin/js/datatables.php');?>
    <script src='https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js'></script>
    <script>
        function hrefCollection(){hrefRow('Table_Collections','invoice');}
		    var Table_Collections = $('#Table_Collections').DataTable( {
          dom 	     : 'tp',
          processing : true,
          serverSide : true,
          responsive : true,
          autoWidth  : false,
          paging    : true,
          searching : false,
          ajax: {
            url     : 'cgi-bin/php/get/Collections2.php',
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
              d.Customer = $('input[name="Customer"]').val( );
              d.Location = $('input[name="Location"]').val( );
              d.Job = $('input[name="Job"]').val( );
              return d;
          }
        },
  			columns: [
  				{
  					data : 'ID' ,
  				},{
  					data : 'Customer'
  				},{
  					data : 'Location'
  				},{
  					data : 'Job'
  				},{
  					data : 'Type'
  				},{
  					data   : 'Date'
  				},{
  					data   : 'Due'
  				},{
  					data      : 'Original',
  					className :'sum'
  				},{
  					data      : 'Balance',
  					className : 'sum'
  				},{
  					data : 'Description'
  				}
  			],
  			language:{
  				loadingRecords : "<div style='text-align:center;'><div class='sk-cube-grid' style='display:inline-block;position:relative;';><div class='sk-cube sk-cube1' style='background-color:#cc0000'></div><div class='sk-cube sk-cube2' style='background-color:#cc0000'></div><div class='sk-cube sk-cube3' style='background-color:#cc0000'></div><div class='sk-cube sk-cube4' style='background-color:#cc0000'></div><div class='sk-cube sk-cube5' style='background-color:#cc0000'></div><div class='sk-cube sk-cube6' style='background-color:#cc0000'></div><div class='sk-cube sk-cube7' style='background-color:#cc0000'></div><div class='sk-cube sk-cube8' style='background-color:#cc0000'></div><div class='sk-cube sk-cube9' style='background-color:#cc0000'></div></div><div class='sk-cube-grid' style='display:inline-block;position:relative;top:-45px;'><div class='sk-cube sk-cube1' style='background-color:#00007f'></div><div class='sk-cube sk-cube2' style='background-color:#00007f'></div><div class='sk-cube sk-cube3' style='background-color:#00007f'></div><div class='sk-cube sk-cube4' style='background-color:#00007f'></div><div class='sk-cube sk-cube5' style='background-color:#00007f'></div><div class='sk-cube sk-cube6' style='background-color:#00007f'></div><div class='sk-cube sk-cube7' style='background-color:#00007f'></div><div class='sk-cube sk-cube8' style='background-color:#00007f'></div><div class='sk-cube sk-cube9' style='background-color:#00007f'></div></div><div class='sk-cube-grid' style='display:inline-block;position:relative;top:-84px;'><div class='sk-cube sk-cube1' style='background-color:gold'></div><div class='sk-cube sk-cube2' style='background-color:gold'></div><div class='sk-cube sk-cube3' style='background-color:gold'></div><div class='sk-cube sk-cube4' style='background-color:gold'></div><div class='sk-cube sk-cube5' style='background-color:gold'></div><div class='sk-cube sk-cube6' style='background-color:gold'></div><div class='sk-cube sk-cube7' style='background-color:gold'></div><div class='sk-cube sk-cube8' style='background-color:gold'></div><div class='sk-cube sk-cube9' style='background-color:gold'></div></div></div><div style='font-size:72px;text-align:center;' class='BankGothic'>Nouveau Elevator</div><div style='font-size:42px;text-align:center;'><i>Raising Your Life</i></div>"
  			}
      } );
      $('#Table_Collections tbody').on('click', 'td.details-control', function () {
        var tr = $(this).closest('tr');
        var row = Table_Collections.row( tr );
        if ( row.child.isShown() ) {
          row.child.hide();
          tr.removeClass('shown');
        } else {
          row.child( formatCollection(row.data()) ).show();
          tr.addClass('shown');
			}
		} );
		function hrefCollections(){hrefRow('Table_Collections','invoice');}
		$('Table#Table_Collections').on('draw.dt',function(){hrefCollections();});
		function redraw( ){ Table_Collections.draw( ); }
    </script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=collections.php';</script></head></html><?php }?>
