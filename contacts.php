<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
if( isset( $_SESSION[ 'Connection' ][ 'User' ], $_SESSION[ 'Connection' ][ 'Hash' ] ) ){
  //Connection
    $result = \singleton\database::getInstance( )->query(
      'Portal',
      " SELECT  [Connection].[ID]
        FROM    dbo.[Connection]
        WHERE       [Connection].[User] = ?
                AND [Connection].[Hash] = ?;",
      array(
        $_SESSION[ 'Connection' ][ 'User' ],
        $_SESSION[ 'Connection' ][ 'Hash' ]
      )
    );
    $Connection = sqlsrv_fetch_array($result);
    //User
	$result = \singleton\database::getInstance( )->query(
		null,
		" SELECT  Emp.fFirst  AS First_Name,
		          Emp.Last    AS Last_Name,
		          Emp.fFirst + ' ' + Emp.Last AS Name,
		          Emp.Title AS Title,
		          Emp.Field   AS Field
		  FROM  Emp
		  WHERE   Emp.ID = ?;",
		array(
		  	$_SESSION[ 'Connection' ][ 'User' ]
		)
	);
	$User   = sqlsrv_fetch_array( $result );
	//Privileges
	$Access = 0;
	$Hex = 0;
	$result = \singleton\database::getInstance( )->query(
		'Portal',
		"   SELECT  [Privilege].[Access],
                    [Privilege].[Owner],
                    [Privilege].[Group],
                    [Privilege].[Department],
                    [Privilege].[Database],
                    [Privilege].[Server],
                    [Privilege].[Other],
                    [Privilege].[Token],
                    [Privilege].[Internet]
		  FROM      dbo.[Privilege]
		  WHERE     Privilege.[User] = ?;",
		array(
		  	$_SESSION[ 'Connection' ][ 'User' ],
		)
	);
    $Privileges = array();
    if( $result ){while( $Privilege = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC ) ){

        $key = $Privilege['Access'];
        unset( $Privilege[ 'Access' ] );
        $Privileges[ $key ] = implode( '', array(
        	dechex( $Privilege[ 'Owner' ] ),
        	dechex( $Privilege[ 'Group' ] ),
        	dechex( $Privilege[ 'Department' ] ),
        	dechex( $Privilege[ 'Database' ] ),
        	dechex( $Privilege[ 'Server' ] ),
        	dechex( $Privilege[ 'Other' ] ),
        	dechex( $Privilege[ 'Token' ] ),
        	dechex( $Privilege[ 'Internet' ] )
        ) );
    }}
    if( 	!isset( $Connection[ 'ID' ] )
        ||  !isset( $Privileges[ 'Contact' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'Contact' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        \singleton\database::getInstance( )->query(
          null,
          " INSERT INTO Activity([User], [Date], [Page] )
            VALUES( ?, ?, ? );",
          array(
            $_SESSION[ 'Connection' ][ 'User' ],
            date('Y-m-d H:i:s'),
            'contacts.php'
        )
      );
?><!DOCTYPE html>
<html lang='en'>
<head>
  <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
     <?php $_GET[ 'Bootstrap' ] = '5.1';?>
     <?php  $_GET[ 'Entity_CSS' ] = 1;?>
     <?php	require( bin_meta . 'index.php');?>
     <?php	require( bin_css  . 'index.php');?>
     <?php require( bin_js   . 'index.php');?>
</head>
<body onload='finishLoadingPage();'>
  	<div id='wrapper'>
	    <?php require( bin_php . 'element/navigation.php' );?>
	    <?php require( bin_php . 'element/loading.php' );?>
	    <div id='page-wrapper' class='content'>
			<div class='card card-full card-primary border-0'>
				<div class='card-heading bg-white text-black'><h4><?php \singleton\fontawesome::getInstance( )->Users( );?> Contacts</h4></div>
				<div class="form-mobile card-body bg-dark text-white"><form method='GET' action='contacts.php'>
			        <div class='row'><div class='col-12'>&nbsp;</div></div>
			        <div class='row'>
			            <div class='col-4'>Search:</div>
			            <div class='col-8'><input type='text' name='Search' placeholder='Search' class='redraw form-input'/></div>
			        </div>
			        <div class='row'><div class='col-12'>&nbsp;</div></div>
			        <div class='row'>
			            <div class='col-4'>ID:</div>
			            <div class='col-8'><input type='text' name='ID' placeholder='ID' class='redraw form-input'value='<?php echo $_GET[ 'ID' ];?>' /></div>
			        </div>
			        <div class='row'>
			            <div class='col-4'>Name:</div>
			            <div class='col-8'><input type='text' name='Name' placeholder='Name' class='redraw form-input'value='<?php echo $_GET[ 'Name' ];?>' /></div>
			        </div>
			        <div class='row'>
			            <div class='col-4'>Entity:</div>
			            <div class='col-8'><input type='text' name='Name' placeholder='Name' class='redraw form-input'value='<?php echo $_GET[ 'Name' ];?>' /></div>
			        </div>
			        <div class='row'>
			            <div class='col-4'>Type:</div>
			            <div class='col-8'><input type='text' name='Customer' placeholder='Customer' class='redraw form-input'value='<?php echo $_GET[ 'Customer' ];?>' /></div>
			        </div>
			        <div class='row'>
			            <div class='col-4'>Position:</div>
			            <div class='col-8'><input type='text' name='Position' placeholder='Position' class='redraw form-input'value='<?php echo $_GET[ 'Position' ];?>' /></div>
			        </div>
			        <div class='row'>
			            <div class='col-4'>Phone:</div>
			            <div class='col-8'><input type='text' name='Phone' placeholder='Phone' class='redraw form-input'value='<?php echo $_GET[ 'Phone' ];?>' /></div>
			        </div>
			        <div class='row'>
			            <div class='col-4'>Email:</div>
			            <div class='col-8'><input type='text' name='Email' placeholder='Email' class='redraw form-input'value='<?php echo $_GET[ 'Email' ];?>' /></div>
			        </div>
			        <div class='row'>
			            <div class='col-4'>Street:</div>
			            <div class='col-8'><input type='text' name='Street' placeholder='Street' class='redraw form-input'value='<?php echo $_GET[ 'Street' ];?>' /></div>
			        </div>
			        <div class='row'>
			            <div class='col-4'>City:</div>
			            <div class='col-8'><input type='text' name='City' placeholder='City' class='redraw form-input'value='<?php echo $_GET[ 'City' ];?>' /></div>
			        </div>
			        <div class='row'>
			            <div class='col-4'>State:</div>
			            <div class='col-8'><input type='text' name='State' placeholder='State' class='redraw form-input'value='<?php echo $_GET[ 'State' ];?>' /></div>
			        </div>
			        <div class='row'>
			            <div class='col-4'>Zip:</div>
			            <div class='col-8'><input type='text' name='Zip' placeholder='Zip' class='redraw form-input'value='<?php echo $_GET[ 'Zip' ];?>' /></div>
			        </div>
			        <div class='row'><div class='col-12'>&nbsp;</div></div>
			        <div class='row'>
			            <div class='col-12'><input type='submit' value='Submit' /></div>
			        </div>
		        </form></div>
				<!--<div class='card-body bg-darker'>
					<div id='Form_Contacts'>
						<div class='card'>
							<div class='card-heading'></div>
							<div class='card-body'>
								<div>
									<fieldset >
										<legend>Contact</legend>
										<editor-field name='ID'></editor-field>
										<editor-field name='Entity'></editor-field>
										<editor-field name='Type'></editor-field>
										<editor-field name='Name'></editor-field>
										<editor-field name='Position'></editor-field>
										<editor-field name='Phone'></editor-field>
										<editor-field name='Email'></editor-field>
										<editor-field name='Street'></editor-field>
										<editor-field name='City'></editor-field>
										<editor-field name='State'></editor-field>
										<editor-field name='Zip'></editor-field>
									</fieldset>
								</div>
							</div>
						</div>
					</div>
				</div>-->
				<div class='card-body bg-darker'>
					<table id='Table_Contacts' class='display' cellspacing='0' width='100%'>
						<thead class='text-white border border-white'><tr>
							<th class='text-white border border-white'>ID</th>
							<th class='text-white border border-white'>Name</th>
							<th class='text-white border border-white'>Type</th>
							<th class='text-white border border-white'>Entity</th>
							<th class='text-white border border-white'>Position</th>
							<th class='text-white border border-white'>Phone</th>
							<th class='text-white border border-white'>Email</th>
							<th class='text-white border border-white'>Address</th>
			            </tr>
			            <tr class='form-desktop'>
							<th class='text-white border border-white'><input class='redraw form-control' type='text' name='ID' placeholder='ID' value='<?php echo isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null;?>' /></th>
							<th class='text-white border border-white'><input class='redraw form-control' type='text' name='Name' placeholder='Name' value='<?php echo isset( $_GET[ 'Name' ] ) ? $_GET[ 'Name' ] : null;?>' /></th>
							<th class='text-white border border-white'><select name='Type' class='redraw form-control'>
								<option value=''>Select</option>
								<option value='0' <?php echo isset( $_GET[ 'Type' ] ) && $_GET[ 'Type' ] == 0 ? 'selected' : true;?>>Customer</option>
								<option value='4' <?php echo isset( $_GET[ 'Type' ] ) && $_GET[ 'Type' ] == 4 ? 'selected' : true;?>>Location</option>
							</select></th>
							<th class='text-white border border-white'><input class='redraw form-control' type='text' name='Entity' placeholder='Entity' value='<?php echo isset( $_GET[ 'Entity' ] ) ? $_GET[ 'Entity' ] : null;?>' /></th>
							<th class='text-white border border-white'><input class='redraw form-control' type='text' name='Position' placeholder='Position' value='<?php echo isset( $_GET[ 'Position' ] ) ? $_GET[ 'Position' ] : null;?>' /></th>
							<th class='text-white border border-white'><input class='redraw form-control' type='text' name='Phone' placeholder='Phone' value='<?php echo isset( $_GET[ 'Phone' ] ) ? $_GET[ 'Phone' ] : null;?>' /></th>
							<th class='text-white border border-white'><input class='redraw form-control' type='text' name='Email' placeholder='Email' value='<?php echo isset( $_GET[ 'Email' ] ) ? $_GET[ 'Email' ] : null;?>' /></th>
							<th class='text-white border border-white'><input class='redraw form-control' type='text' name='Address' placeholder='Address' value='<?php echo isset( $_GET[ 'Address' ] ) ? $_GET[ 'Address' ] : null;?>' /></th>
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
} else {?><html><head><script>document.location.href='../login.php?Forward=contacts.php';</script></head></html><?php }?>
