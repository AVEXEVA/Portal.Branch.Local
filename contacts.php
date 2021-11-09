<?php
if( session_id( ) == '' || !isset( $_SESSION ) ) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/bin/php/index.php' );
}
if( isset( $_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
    $result = $database->query(
        null,
        "   SELECT  *
		        FROM    Connection
		        WHERE       Connection.Connector = ?
                    AND Connection.Hash  = ?;",
        array(
            $_SESSION[ 'User' ],
            $_SESSION[ 'Hash' ]
        )
    );
    $Connection = sqlsrv_fetch_array( $result );
    //User
    $result = $database->query(
        null,
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
	$result = $database->query(
        null,
        "   SELECT  *
            FROM    Privilege
            WHERE   Privilege.User_ID = ?;",
        array(
            $_SESSION['User']
        )
    );
	$Privileges = array();
	if( $result ){while( $Privilege = sqlsrv_fetch_array( $result ) ){ $Privileges[ $Privilege[ 'Access_Table' ] ] = $Privilege; } }
    if(	!isset( $Connection[ 'ID' ] )
	   	|| !isset($Privileges[ 'Customer' ])
	  		|| $Privileges[ 'Customer' ][ 'User_Privilege' ]  < 4
	  		|| $Privileges[ 'Customer' ][ 'Group_Privilege' ] < 4
        	|| $Privileges[ 'Customer' ][ 'Other_Privilege' ] < 4){
				?><?php require( '../404.html' );?><?php }
    else {
  		$database->query(
          null,
          "   INSERT INTO Activity([User], [Date], [Page])
              VALUES( ?, ?, ? );",
          array(
              $_SESSION['User'],
              date( 'Y-m-d H:i:s' ),
              'contacts.php'
          )
      );
?><!DOCTYPE html>
<html lang='en'>
<head>
    <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
    <?php $_GET[ 'Bootstrap' ] = '5.1';?>
    <?php require( bin_meta . 'index.php');?>
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js  . 'index.php');?>
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
			            <div class='col-4'>Entity:</div>
			            <div class='col-8'><input type='text' name='Name' placeholder='Name' class='redraw form-input'value='<?php echo $_GET[ 'Name' ];?>' /></div>
			        </div>
			        <div class='row'>
			            <div class='col-4'>Type:</div>
			            <div class='col-8'><input type='text' name='Customer' placeholder='Customer' class='redraw form-input'value='<?php echo $_GET[ 'Customer' ];?>' /></div>
			        </div>
			        <div class='row'>
			            <div class='col-4'>Name:</div>
			            <div class='col-8'><input type='text' name='Name' placeholder='Name' class='redraw form-input'value='<?php echo $_GET[ 'Name' ];?>' /></div>
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
				<div class='card-body bg-darker'>
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
				</div>
				<div class='card-body bg-darker'>
					<table id='Table_Contacts' class='display' cellspacing='0' width='100%'>
						<thead class='text-white border border-white'><tr>
							<th class='text-white border border-white'>ID</th>
							<th class='text-white border border-white'>Entity</th>
							<th class='text-white border border-white'>Type</th>
							<th class='text-white border border-white'>Name</th>
							<th class='text-white border border-white'>Position</th>
							<th class='text-white border border-white'>Phone</th>
							<th class='text-white border border-white'>Email</th>
							<th class='text-white border border-white'>Street</th>
							<th class='text-white border border-white'>City</th>
							<th class='text-white border border-white'>State</th>
							<th class='text-white border border-white'>Zip</th>
			            </tr>
			            <tr class='form-desktop'>
							<th class='text-white border border-white'><input class='redraw form-control' type='text' name='ID' placeholder='ID' value='<?php echo isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null;?>' /></th>
							<th class='text-white border border-white'><input class='redraw form-control' type='text' name='Entity' placeholder='Entity' value='<?php echo isset( $_GET[ 'Entity' ] ) ? $_GET[ 'Entity' ] : null;?>' /></th>
							<th class='text-white border border-white'><input class='redraw form-control' type='text' name='Type' placeholder='Type' value='<?php echo isset( $_GET[ 'Type' ] ) ? $_GET[ 'Type' ] : null;?>' /></th>
							<th class='text-white border border-white'><input class='redraw form-control' type='text' name='Name' placeholder='Name' value='<?php echo isset( $_GET[ 'Name' ] ) ? $_GET[ 'Name' ] : null;?>' /></th>
							<th class='text-white border border-white'><input class='redraw form-control' type='text' name='Street' placeholder='Street' value='<?php echo isset( $_GET[ 'Street' ] ) ? $_GET[ 'Street' ] : null;?>' /></th>
							<th class='text-white border border-white'><input class='redraw form-control' type='text' name='Phone' placeholder='Phone' value='<?php echo isset( $_GET[ 'Phone' ] ) ? $_GET[ 'Phone' ] : null;?>' /></th>
							<th class='text-white border border-white'><input class='redraw form-control' type='text' name='Email' placeholder='Email' value='<?php echo isset( $_GET[ 'Email' ] ) ? $_GET[ 'Email' ] : null;?>' /></th>
							<th class='text-white border border-white'><input class='redraw form-control' type='text' name='Position' placeholder='Position' value='<?php echo isset( $_GET[ 'Position' ] ) ? $_GET[ 'Position' ] : null;?>' /></th>
							<th class='text-white border border-white'><input class='redraw form-control' type='text' name='City' placeholder='City' value='<?php echo isset( $_GET[ 'City' ] ) ? $_GET[ 'City' ] : null;?>' /></th>
							<th class='text-white border border-white'><input class='redraw form-control' type='text' name='State' placeholder='State' value='<?php echo isset( $_GET[ 'State' ] ) ? $_GET[ 'State' ] : null;?>' /></th>
							<th class='text-white border border-white'><input class='redraw form-control' type='text' name='Zip' placeholder='Zip' value='<?php echo isset( $_GET[ 'Zip' ] ) ? $_GET[ 'Zip' ] : null;?>' /></th>
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