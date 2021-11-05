<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/cgi-bin/php/index.php' );
}
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $result = $database->query(
      null,
      "   SELECT    *
		      FROM      Connection
		      WHERE     Connection.Connector = ?
		      AND       Connection.Hash  = ?;",
    array(
        $_SESSION[ 'User' ],
        $_SESSION[ 'Hash' ]
    )
);
    Connection = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
    $result = $database->query(
      null,
      "   SELECT    *,
		                Emp.fFirst AS First_Name,
			              Emp.Last   AS Last_Name
      		FROM      Emp
      		WHERE     Emp.ID = ?;",
      array(
        $_SESSION['User']
    )
);
    User = sqlsrv_fetch_array($result);
	$result = $database->query(
      null,
      "   SELECT    *
		      FROM   Privilege
		      WHERE  Privilege.User_ID = ?;",
  array(
    $_SESSION[ 'User' ]
    )
);
	Privileges = array();
	if($result){while(Privilege = sqlsrv_fetch_array($result)){Privileges[Privilege[ 'Access_Table' ]] = Privilege;}}
    if(	!isset(Connection[ 'ID' ])
	   	|| !isset(Privileges[ 'Proposal' ])
	  		|| Privileges[ 'Proposal' ][ 'User_Privilege' ]  < 4
	  		|| Privileges[ 'Proposal' ][ 'Group_Privilege' ] < 4
	  	    || Privileges[ 'Proposal' ][ 'Other_Privilege' ] < 4){
				?><?php require('../404.html');?><?php }
    else {
		$database->query(
      null,
      "   INSERT INTO Activity([User], [Date], [Page])
			    VALUES(?,?,?);",
    array(
      $_SESSION[ 'User' ],
          date("Y-m-d H:i:s"),
              "proposals.php")
 );
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
    <?php require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/cgi-bin/css/index.php' );?>
    <?php require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/cgi-bin/js/index.php' );?>
</head>
<body onload='finishLoadingPage();'>
    <div id="wrapper" class="<?php echo isset($_SESSION[ 'Toggle_Menu' ]) ? $_SESSION[ 'Toggle_Menu' ] : null;?>">
        <?php require(bin_php.'element/navigation/index.php');?>
        <?php require(bin_php.'element/loading.php');?>
        <div id="page-wrapper" class='content'>
            <div class="panel panel-primary">
                <div class="panel-heading"><h4><?php \singleton\fontawesome::getInstance( )->Proposal();?> Proposals</h4></div>
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
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=proposals.php';</script></head></html><?php }?>
