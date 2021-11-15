<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $result = \singleton\database::getInstance( )->query(
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
    $Connection = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
    $result = \singleton\database::getInstance( )->query(
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
    $User = sqlsrv_fetch_array($result);
	$result = \singleton\database::getInstance( )->query(
      null,
      "   SELECT    *
		      FROM   Privilege
		      WHERE  Privilege.User_ID = ?;",
  array(
    $_SESSION[ 'User' ]
    )
);
	$Privileges = array();
	if($result){while($Privilege = sqlsrv_fetch_array($result)){$Privileges[$Privilege[ 'Access_Table' ]] = $Privilege;}}
    if(	!isset($Connection[ 'ID' ])
	   	|| !isset($Privileges[ 'Proposal' ])
	  		|| $Privileges[ 'Proposal' ][ 'User_Privilege' ]  < 4
	  		|| $Privileges[ 'Proposal' ][ 'Group_Privilege' ] < 4
	  	    || $Privileges[ 'Proposal' ][ 'Other_Privilege' ] < 4){
				?><?php require('../404.html');?><?php }
    else {
		\singleton\database::getInstance( )->query(
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
	<title><?php echo $_SESSION['Connection']['Branch'];?> | Portal </title>
	<?php $_GET[ 'Bootstrap' ] = '5.1';?>
    <?php require( bin_meta . 'index.php' );?>
    <?php require( bin_css  . 'index.php' );?>
    <?php require( bin_js   . 'index.php' );?>
</head>
<body onload='finishLoadingPage();'>
    <div id="wrapper" class="<?php echo isset($_SESSION[ 'Toggle_Menu' ]) ? $_SESSION[ 'Toggle_Menu' ] : null;?>">
        <?php require( bin_php.'element/navigation.php');?>
        <?php require( bin_php.'element/loading.php');?>
        <div id="page-wrapper" class='content'>
            <div class="card card-full card-primary border-0">
                <div class="card-heading"><h4><?php \singleton\fontawesome::getInstance( )->Proposal();?> Proposals</h4></div>
                <div class="form-mobile card-body bg-dark text-white"><form method='GET' action='locations.php'>
                    <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
                    <div class='form-group row'>
                        <label class='col-auto'>Search:</label>
                        <div class='col-auto'><input type='text' name='Search' placeholder='Search'value='<?php echo isset( $_GET[ 'Search' ] ) ? $_GET[ 'Search' ] : null;?>' /></div>
                    </div>
                    <div class='form-group row'><div class='col-xs-12'>&nbsp;</div></div>
                    <div class='form-group row'>
                    	<label class='col-auto'>ID:</label>
                    	<div class='col-auto'><input type='text' name='ID' placeholder='ID' value='<?php echo isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null;?>' /></div>
                    </div>
                    <div class='form-group row'>
                    	<label class='col-auto'>Contact:</label>
                    	<div class='col-auto'><input type='text' name='Contact' placeholder='Contact' value='<?php echo isset( $_GET[ 'Contact' ] ) ? $_GET[ 'Contact' ] : null;?>' /></div>
                    </div>
                    <div class='form-group row'>
                    	<label class='col-auto'>Customer:</label>
                    	<div class='col-auto'><input type='text' name='Customer' placeholder='Customer' value='<?php echo isset( $_GET[ 'Customer' ] ) ? $_GET[ 'Customer' ] : null;?>' /></div>
                    </div>
                    <div class='form-group row'>
                    	<label class='col-auto'>Location:</label>
                    	<div class='col-auto'><input type='text' name='Location' placeholder='Location' value='<?php echo isset( $_GET[ 'Location' ] ) ? $_GET[ 'Location' ] : null;?>' /></div>
                    </div>
                    <div class='form-group row'>
                    	<label class='col-auto'>Job:</label>
                    	<div class='col-auto'><input type='text' name='Job' placeholder='Job' value='<?php echo isset( $_GET[ 'Job' ] ) ? $_GET[ 'Job' ] : null;?>' /></div>
                    </div>
                    <div class='form-group row'>
                    	<label class='col-auto'>Title:</label>
                    	<div class='col-auto'><input type='text' name='Title' placeholder='Title' value='<?php echo isset( $_GET[ 'Title' ] ) ? $_GET[ 'Title' ] : null;?>' /></div>
                    </div>
                    <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
                </div>
                <div class="card-body bg-dark text-white">
                    <table id='Table_Proposals' class='display' cellspacing='0' width='100%'>
                        <thead><tr>
                            <th title='ID'>ID</th>
                            <th title='Territory'>Territory</th>
                            <th title='Contact'>Contact</th>
                            <th title='Title'>Title</th>
                            <th title='Status'>Status</th>
                            <th title='Phone'>Phone</th>
                            <th title='Email'>Email</th>
                            <th title='Address'>Address</th>
                            <th title='Date'>Date</th>
                            <th title='Customer'>Customer</th>
                            <th title='Location'>Location</th>
                            <th title='Job'>Job</th>
                            <th title='Cost'>Cost</th>
                            <th title='Price'>Price</th>
                        </tr><tr class='form-desktop'>
                            <th title='ID'><input class='redraw form-control' type='text' name='ID' placeholder='ID' value='<?php echo isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null;?>' /></th>
                            <th title='Territory'><input class='redraw form-control' type='text' name='Territory' placeholder='Territory' value='<?php echo isset( $_GET[ 'Territory' ] ) ? $_GET[ 'Territory' ] : null;?>' /></th>
                            <th title='Contact'><input class='redraw form-control' type='text' name='Contact' placeholder='Contact' value='<?php echo isset( $_GET[ 'Contact' ] ) ? $_GET[ 'Contact' ] : null;?>' /></th>
                            <th title='Title'><input class='redraw form-control' type='text' name='Title' placeholder='Title' value='<?php echo isset( $_GET[ 'Title' ] ) ? $_GET[ 'Title' ] : null;?>' /></th>
                            <th title='Status'><select class='redraw form-control' name='Status'>
                                <option value=''  <?php echo isset( $_GET[ 'Status' ] ) && $_GET[ 'Status' ] == null ? 'selected' : null;?>>Select</option>
                                <option value='0' <?php echo isset( $_GET[ 'Status' ] ) && $_GET[ 'Status' ] == 0 ? 'selected' : null;?>>Open</option>
                                <option value='1' <?php echo isset( $_GET[ 'Status' ] ) && $_GET[ 'Status' ] == 1 ? 'selected' : null;?>>Canceled</option>
                                <option value='2' <?php echo isset( $_GET[ 'Status' ] ) && $_GET[ 'Status' ] == 2 ? 'selected' : null;?>>Withdrawn</option>
                                <option value='3' <?php echo isset( $_GET[ 'Status' ] ) && $_GET[ 'Status' ] == 3 ? 'selected' : null;?>>Disqualified</option>
                                <option value='4' <?php echo isset( $_GET[ 'Status' ] ) && $_GET[ 'Status' ] == 4 ? 'selected' : null;?>>Award Successful</option>
                            </select></th>
                            <th title='Phone'><input class='redraw form-control' type='text' name='Email' placeholder='Email' value='<?php echo isset( $_GET[ 'Email' ] ) ? $_GET[ 'Email' ] : null;?>' /></th>
                            <th title='Email'><input class='redraw form-control' type='text' name='Phone' placeholder='Phone' value='<?php echo isset( $_GET[ 'Phone' ] ) ? $_GET[ 'Phone' ] : null;?>' /></th>
                            <th title='Address'><input class='redraw form-control' type='text' name='Address' placeholder='Address' value='<?php echo isset( $_GET[ 'Address' ] ) ? $_GET[ 'Address' ] : null;?>' /></th>
                            <th title='Date'><input class='redraw form-control' type='text' name='Date' placeholder='Date' value='<?php echo isset( $_GET[ 'Date' ] ) ? $_GET[ 'Date' ] : null;?>' /></th>
                            <th title='Customer'><input class='redraw form-control' type='text' name='Customer' placeholder='Customer' value='<?php echo isset( $_GET[ 'Customer' ] ) ? $_GET[ 'Customer' ] : null;?>' /></th>
                            <th title='Location'><input class='redraw form-control' type='text' name='Location' placeholder='Location' value='<?php echo isset( $_GET[ 'Location' ] ) ? $_GET[ 'Location' ] : null;?>' /></th>
                            <th title='Job'><input class='redraw form-control' type='text' name='Job' placeholder='Job' value='<?php echo isset( $_GET[ 'Job' ] ) ? $_GET[ 'Job' ] : null;?>' /></th>
                            <th title='Cost'><input class='redraw form-control' type='text' name='Cost' placeholder='Cost' value='<?php echo isset( $_GET[ 'Cost' ] ) ? $_GET[ 'Cost' ] : null;?>' /></th>
                            <th title='Price'><input class='redraw form-control' type='text' name='Price' placeholder='Price' value='<?php echo isset( $_GET[ 'Price' ] ) ? $_GET[ 'Price' ] : null;?>' /></th>
                        </tr></thead>
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
