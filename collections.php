<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $result = \singleton\database::getInstance( )->query(
      null,
      " SELECT  *
		    FROM    Connection
		    WHERE       Connection.Connector = ?
		            AND Connection.Hash  = ?;",
      array(
        $_SESSION[ 'User' ],
        $_SESSION[ 'Hash' ]
      )
    );
    $Connection = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
    $result = \singleton\database::getInstance( )->query(
      null,
      " SELECT  *,
                Emp.fFirst AS First_Name,
			          Emp.Last   AS Last_Name
        FROM    Emp
        WHERE   Emp.ID = ?;",
      array(
        $_SESSION[ 'User' ]
      )
    );
    $User = sqlsrv_fetch_array($result);
    //Privileges
  $result = \singleton\database::getInstance( )->query(
    null,
    "  SELECT *
    	 FROM   Privilege
    	 WHERE  Privilege.User_ID = ?;",
  array(
       $_SESSION[ 'User' ]
     )
  );
    	$Privileges = array();
    	if( $result ){while( $Privilege = sqlsrv_fetch_array( $result ) ){ $Privileges [ $Privilege [ 'Access_Table' ] ] = $Privilege;} }
        if(!isset( $Connection [ 'ID' ] )
    	   	|| !isset( $Privileges ['Invoice' ] )
    	  		|| $Privileges['Invoice']['User_Privilege']  < 4
    	  		|| $Privileges['Invoice']['Group_Privilege'] < 4
    	  		|| $Privileges['Invoice']['Other_Privilege'] < 4){
    				?><?php require('../404.html');?><?php }
        else {
    		\singleton\database::getInstance( )->query(
                  null,
                  "     INSERT INTO Activity([User], [Date], [Page])
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
    <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
    <?php 
        $_GET[ 'Bootstrap' ] = '5.1';
        require( bin_meta . 'index.php');
        require( bin_css . 'index.php');
        require( bin_js  . 'index.php');
    ?>
</head>
<body onload='finishLoadingPage();'>
    <div id='wrapper'>
        <?php require( bin_php . 'element/navigation.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id='page-wrapper' class='content'>
            <div class='card card-full card-primary border-0'>
                <div class="card-heading"><h4><?php \singleton\fontawesome::getInstance( )->Invoice( 1 );?> Collections</h4></div>
				<div class="form-mobile card-body bg-darker text-white"><form method='GET' action='collections.php'>
                    <div class='row'><div class='col-12'>&nbsp;</div></div>
                    <div class='row'>
                        <div class='col-4'>Search:</div>
                        <div class='col-8'><input type='text' name='Search' placeholder='Search' onChange='redraw( );' value='<?php echo isset( $_GET[ 'Search' ] ) ? $_GET[ 'Search' ] : null;?>'  /></div>
                    </div>
                    <div class='row'><div class='col-12'>&nbsp;</div></div>
                    <div class='row'>
                    	<div class='col-4'><?php \singleton\fontawesome::getInstance( 1 )->Customer( 1 );?> Customer:</div>
                    	<div class='col-8'><input type='text' name='Customer' placeholder='Customer' onChange='redraw( );' value='<?php echo isset( $_GET[ 'Customer' ] ) ? $_GET[ 'Customer' ] : null;?>' /></div>
                    </div>
                    <div class='row'>
                    	<div class='col-4'><?php \singleton\fontawesome::getInstance( 1 )->Location( 1 );?> Location:</div>
                    	<div class='col-8'><input type='text' name='Location' placeholder='Location' onChange='redraw( );' value='<?php echo isset( $_GET[ 'Location' ] ) ? $_GET[ 'Location' ] : null;?>' /></div>
                    </div>
                    <div class='row'>
                        <div class='col-4'><?php \singleton\fontawesome::getInstance( 1 )->Job( 1 );?> Job:</div>
                        <div class='col-8'><input type='text' name='Job' placeholder='Job' onChange='redraw( );' value='<?php echo isset( $_GET[ 'Job' ] ) ? $_GET[ 'Job' ] : null;?>' /></div>
                    </div>
                    <div class='row'>
                        <div class='col-4'><?php \singleton\fontawesome::getInstance( 1 )->Blank( 1 );?> Type:</div>
                        <div class='col-8'><input type='text' name='Type' placeholder='Type' onChange='redraw( );' value='<?php echo isset( $_GET[ 'Type' ] ) ? $_GET[ 'Type' ] : null;?>' /></div>
                    </div>
                    <div class='row'>
                        <div class='col-4'><?php \singleton\fontawesome::getInstance( 1 )->Blank( 1 );?> Date:</div>
                        <div class='col-8'><input type='text' name='Date' placeholder='Date' onChange='redraw( );' value='<?php echo isset( $_GET[ 'Date' ] ) ? $_GET[ 'Date' ] : null;?>' /></div>
                    </div>
                    <div class='row'>
                        <div class='col-4'><?php \singleton\fontawesome::getInstance( 1 )->Blank( 1 );?> Due:</div>
                        <div class='col-8'><input type='text' name='Due' placeholder='Due' onChange='redraw( );' value='<?php echo isset( $_GET[ 'Due' ] ) ? $_GET[ 'Due' ] : null;?>' /></div>
                    </div>
                    <div class='row'>
                        <div class='col-4'><?php \singleton\fontawesome::getInstance( 1 )->Blank( 1 );?> Original:</div>
                        <div class='col-8'><input type='text' name='Original' placeholder='Original' onChange='redraw( );' value='<?php echo isset( $_GET[ 'Original' ] ) ? $_GET[ 'Original' ] : null;?>' /></div>
                    </div>
                    
                    <div class='row'>
                        <div class='col-4'><?php \singleton\fontawesome::getInstance( 1 )->Blank( 1 );?>Balance:</div>
                        <div class='col-8'><input type='text' name='Balance' placeholder='Balance' onChange='redraw( );' value='<?php echo isset( $_GET[ 'Balance' ] ) ? $_GET[ 'Balance' ] : null;?>' /></div>
                    </div>
                    <div class='row'>
                        <div class='col-4'><?php \singleton\fontawesome::getInstance( 1 )->Description( 1 );?>Description:</div>
                        <div class='col-8'><input type='text' name='Description' placeholder='Description' onChange='redraw( );' value='<?php echo isset( $_GET[ 'Description' ] ) ? $_GET[ 'Description' ] : null;?>' /></div>
                    </div>
                    <div class='row'><div class='col-12'>&nbsp;</div></div>
                    <div class='row'><div class='col-12'><input type='submit' value='Submit' /></div></div>
                </form></div>
                <div class='card-body card-body bg-darker'>
                    <table id='Table_Collections' class='display' cellspacing='0' width='100%'>
                        <thead><tr class='text-white text-center'>
                            <th class='border border-white'>ID</th>
                            <th class='border border-white'>Territory</th>
                            <th class='border border-white'>Customer</th>
                            <th class='border border-white'>Location</th>
                            <th class='border border-white'>Job</th>
                            <th class='border border-white'>Type</th>
                            <th class='border border-white'>Date</th>
                            <th class='border border-white'>Due</th>
                            <th class='border border-white'>Original</th>
                            <th class='border border-white'>Balance</th>
                            <th class='border border-white'>Description</th>
                        </tr><tr class='form-desktop'>
                            <th class='border border-white'><input class='redraw form-control' type='text' name='ID' placeholder='ID' value='<?php echo isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null;?>' /></th>
                            <th class='border border-white'><select name='redraw form-control' name='Territory'>
                                <option value=''>Select</option>
                                <?php 
                                $result = \singleton\database::getInstance( )->query(
                                    null,
                                    "   SELECT  Territory.ID,
                                                Territory.Name 
                                        FROM    Terr AS Territory
                                    ;",
                                    array( )
                                );
                                if( $result ){ while( $row = sqlsrv_fetch_array( $result ) ){?><option value='<?php echo $row[ 'Name' ];?>' name'Te><?php echo $row[ 'Name' ];?></option><?php } }?>
                            </select></th>
                            <th class='border border-white'><input class='redraw form-control' type='text' name='Customer' placeholder='Customer' value='<?php echo isset( $_GET[ 'Customer' ] ) ? $_GET[ 'Customer' ] : null;?>' /></th>
                            <th class='border border-white'><input class='redraw form-control' type='text' name='Location' placeholder='Location' value='<?php echo isset( $_GET[ 'Location' ] ) ? $_GET[ 'Location' ] : null;?>' /></th>
                            <th class='border border-white'><input class='redraw form-control' type='text' name='Job' placeholder='Job' value='<?php echo isset( $_GET[ 'Job' ] ) ? $_GET[ 'Job' ] : null;?>' /></th>
                            <th class='border border-white'><input class='redraw form-control' type='text' name='Type' placeholder='Type' value='<?php echo isset( $_GET[ 'Type' ] ) ? $_GET[ 'Type' ] : null;?>' /></th>
                            <th class='border border-white'><div class='row g-0'>
                                <div class='col-12'><input class='redraw date' type='text' name='Date_Start' placeholder='Date Start' value='<?php echo isset( $_GET[ 'Date_Start' ] ) ? $_GET[ 'Date_Start' ] : null;?>' /></div>
                                <div class='col-12'><input class='redraw date' type='text' name='Date_End' placeholder='Date End' value='<?php echo isset( $_GET[ 'Date_End' ] ) ? $_GET[ 'Date_End' ] : null;?>' /></div>
                            </div></th>
                            <th class='border border-white'><div class='row g-0'>
                                <div class='col-12'><input class='redraw date' type='text' name='Due_Start' placeholder='Due Start' value='<?php echo isset( $_GET[ 'Due_Start' ] ) ? $_GET[ 'Due_Start' ] : null;?>' /></div>
                                <div class='col-12'><input class='redraw date' type='text' name='Due_End' placeholder='Due End' value='<?php echo isset( $_GET[ 'Due_End' ] ) ? $_GET[ 'Due_End' ] : null;?>' /></div>
                            </div></th>
                            <th class='border border-white'><div class='row g-0'>
                                <div class='col-12'><input class='redraw' type='text' name='Original_Start' placeholder='OriginalStart' value='<?php echo isset( $_GET[ 'Original_Start' ] ) ? $_GET[ 'Original_Start' ] : null;?>' /></div>
                                <div class='col-12'><input class='redraw' type='text' name='Original_End' placeholder='Original End' value='<?php echo isset( $_GET[ 'Original_End' ] ) ? $_GET[ 'Original_End' ] : null;?>' /></div>
                            </div></th>
                            <th class='border border-white'><div class='row g-0'>
                                <div class='col-12'><input class='redraw' type='text' name='Balance_Start' placeholder='Balance Start' value='<?php echo isset( $_GET[ 'Balance_Start' ] ) ? $_GET[ 'Balance_Start' ] : null;?>' /></div>
                                <div class='col-12'><input class='redraw' type='text' name='Balance_End' placeholder='Balance End' value='<?php echo isset( $_GET[ 'Balance_End' ] ) ? $_GET[ 'Balance_End' ] : null;?>' /></div>
                            </div></th>
                            <th class='border border-white'><input class='redraw form-control' type='text' name='Description' placeholder='Description' value='<?php echo isset( $_GET[ 'Description' ] ) ? $_GET[ 'Description' ] : null;?>' /></th>
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
} else {?><html><head><script>document.location.href='../login.php?Forward=collections.php';</script></head></html><?php }?>,
array( )
