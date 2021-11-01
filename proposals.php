<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( ); 
    require( '/var/www/nouveautexas.com/html/portal/cgi-bin/php/index.php' );
}
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"
		SELECT *
		FROM   Connection
		WHERE  Connection.Connector = ?
		       AND Connection.Hash  = ?
	;",array($_SESSION['User'],$_SESSION['Hash']));
    $My_Connection = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $r = sqlsrv_query($NEI,"
		SELECT *,
		       Emp.fFirst AS First_Name,
			   Emp.Last   AS Last_Name
		FROM   Emp
		WHERE  Emp.ID = ?
	;",array($_SESSION['User']));
    $My_User = sqlsrv_fetch_array($r);
	$r = sqlsrv_query($NEI,"
		SELECT *
		FROM   Privilege
		WHERE  Privilege.User_ID = ?
	;",array($_SESSION['User']));
	$My_Privileges = array();
	if($r){while($My_Privilege = sqlsrv_fetch_array($r)){$My_Privileges[$My_Privilege['Access_Table']] = $My_Privilege;}}
    if(	!isset($My_Connection['ID'])
	   	|| !isset($My_Privileges['Proposal'])
	  		|| $My_Privileges['Proposal']['User_Privilege']  < 4
	  		|| $My_Privileges['Proposal']['Group_Privilege'] < 4
	  	    || $My_Privileges['Proposal']['Other_Privilege'] < 4){
				?><?php require('../404.html');?><?php }
    else {
		sqlsrv_query($NEI,"
			INSERT INTO Portal.dbo.Activity([User], [Date], [Page])
			VALUES(?,?,?)
		;",array($_SESSION['User'],date("Y-m-d H:i:s"), "proposals.php"));
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Peter D. Speranza">
	<title>Nouveau Texas | Portal</title>
	<?php $_GET[ 'Bootstrap' ] = '5.1';?>
    <?php require( '/var/www/nouveautexas.com/html/portal/cgi-bin/css/index.php' );?>
    <style>
        .form-group>label:first-child {
            min-width  : 175px;
            text-align : right;
        }
    </style>
    <?php require( '/var/www/nouveautexas.com/html/portal/cgi-bin/js/index.php' );?>
</head>
<body onload='finishLoadingPage();'>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require(PROJECT_ROOT.'php/element/navigation/index.php');?>
        <?php require(PROJECT_ROOT.'php/element/loading.php');?>
        <div id="page-wrapper" class='content'>
            <div class="panel panel-primary">
                <div class="panel-heading"><h4><?php $Icons->Proposal();?> Proposals</h4></div>
                <div class="panel-body no-print" id='Filters' style='border-bottom:1px solid #1d1d1d;'>
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
                    	<label class='col-auto'>Contact:</label> 
                    	<div class='col-auto'><input type='text' name='Contact' placeholder='Contact' onChange='redraw( );' /></div>
                    </div>
                    <div class='form-group row'>
                    	<label class='col-auto'>Customer:</label>
                    	<div class='col-auto'><input type='text' name='Customer' placeholder='Customer' onChange='redraw( );' /></div>
                    </div>
                    <div class='form-group row'>
                    	<label class='col-auto'>Location:</label>
                    	<div class='col-auto'><input type='text' name='Location' placeholder='Location' onChange='redraw( );' /></div>
                    </div>
                    <div class='form-group row'>
                    	<label class='col-auto'>Job:</label>
                    	<div class='col-auto'><input type='text' name='Job' placeholder='Job' onChange='redraw( );' /></div>
                    </div>
                    <div class='form-group row'>
                    	<label class='col-auto'>Title:</label>
                    	<div class='col-auto'><input type='text' name='Title' placeholder='Title' onChange='redraw( );' /></div>
                    </div>
                    <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
                </div>
                <div class="panel-body">
                	<style>
                    .hoverGray:hover {
                        background-color : gold !important;
                    }
                    table#Table_Proposals tbody tr {
                        color : black !important;
                    }
                    table#Table_Proposals tbody tr:nth-child( even ) {
                        background-color : rgba( 240, 240, 240, 1 ) !important;
                    }
                    table#Table_Proposals tbody tr:nth-child( odd ) {
                        background-color : rgba( 255, 255, 255, 1 ) !important;
                    }
                    .paginate_button {
                    	background-color : rgba( 255, 255, 255, .7 ) !important;
                    }
                    .paginate_button:hover {
                    	color : white !important;
                    }
                    </style>
                    <table id='Table_Proposals' class='display' cellspacing='0' width='100%'>
                        <thead>
                            <th title='ID'>ID</th>
                            <th title='Date'>Date</th>
                            <th title='Contact'>Contact</th>
                            <th title='Customer'>Customer</th>
                            <th title='Location'>Location</th>
                            <th title='Job'>Job</th>
                            <th title='Title'>Title</th>
                            <th title='Cost'>Cost</th>
                            <th title='Price'>Price</th>
                        </thead>
					</table>
                </div>
            </div>
        </div>
    </div>
    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>
    <?php require(PROJECT_ROOT.'js/datatables.php');?>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script>
        var Table_Proposals = $('#Table_Proposals').DataTable( {
        	dom 	   : 'tlp',
	        processing : true,
	        serverSide : true,
	        responsive : true,
	        autoWidth  : false,
			paging     : true,
			searching  : false,
			ajax       : {
	            url : 'cgi-bin/php/get/Proposals2.php',
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
	                d.Contact = $('input[name="Contact"]').val( );
	                d.Customer = $('input[name="Customer"]').val( );
	                d.Location = $('input[name="Location"]').val( );
	                d.Job = $('.input[name="Job"]').val( );
	                d.Title = $('input[name="Title"]').val( );
	                return d; 
	            }
	        },
            columns : [
                { 
                	data : 'ID' 
                },
                {
                    data : 'Date'
                },{ 
                	data : 'Contact' 
                },{ 
                	data : 'Customer',
                },{ 
                	data : 'Location' 
                },{
                	data : 'Job'
                },{ 
                	data : 'Title'
                },{ 
                	data : 'Cost'
                },{ 
                	data : 'Price'
                }
            ],
            language : {
                loadingRecords : "<div style='text-align:center;'><div class='sk-cube-grid' style='display:inline-block;position:relative;';><div class='sk-cube sk-cube1' style='background-color:#cc0000'></div><div class='sk-cube sk-cube2' style='background-color:#cc0000'></div><div class='sk-cube sk-cube3' style='background-color:#cc0000'></div><div class='sk-cube sk-cube4' style='background-color:#cc0000'></div><div class='sk-cube sk-cube5' style='background-color:#cc0000'></div><div class='sk-cube sk-cube6' style='background-color:#cc0000'></div><div class='sk-cube sk-cube7' style='background-color:#cc0000'></div><div class='sk-cube sk-cube8' style='background-color:#cc0000'></div><div class='sk-cube sk-cube9' style='background-color:#cc0000'></div></div><div class='sk-cube-grid' style='display:inline-block;position:relative;top:-45px;'><div class='sk-cube sk-cube1' style='background-color:#00007f'></div><div class='sk-cube sk-cube2' style='background-color:#00007f'></div><div class='sk-cube sk-cube3' style='background-color:#00007f'></div><div class='sk-cube sk-cube4' style='background-color:#00007f'></div><div class='sk-cube sk-cube5' style='background-color:#00007f'></div><div class='sk-cube sk-cube6' style='background-color:#00007f'></div><div class='sk-cube sk-cube7' style='background-color:#00007f'></div><div class='sk-cube sk-cube8' style='background-color:#00007f'></div><div class='sk-cube sk-cube9' style='background-color:#00007f'></div></div><div class='sk-cube-grid' style='display:inline-block;position:relative;top:-84px;'><div class='sk-cube sk-cube1' style='background-color:gold'></div><div class='sk-cube sk-cube2' style='background-color:gold'></div><div class='sk-cube sk-cube3' style='background-color:gold'></div><div class='sk-cube sk-cube4' style='background-color:gold'></div><div class='sk-cube sk-cube5' style='background-color:gold'></div><div class='sk-cube sk-cube6' style='background-color:gold'></div><div class='sk-cube sk-cube7' style='background-color:gold'></div><div class='sk-cube sk-cube8' style='background-color:gold'></div><div class='sk-cube sk-cube9' style='background-color:gold'></div></div></div><div style='font-size:72px;text-align:center;' class='BankGothic'>Nouveau Texas</div><div style='font-size:42px;text-align:center;'><i>Raising Your Life</i></div>"
            },
            initComplete : function(){ }
        } );
        function redraw( ){ Table_Proposals.draw( ); }
		function hrefProposals(){hrefRow('Table_Proposals','proposal');}
		$('Table#Table_Proposals').on('draw.dt',function(){hrefProposals();});
    </script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=proposals.php';</script></head></html><?php }?>
