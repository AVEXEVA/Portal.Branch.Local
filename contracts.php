<?php
if( session_id( ) == '' || !isset( $_SESSION ) ) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/cgi-bin/php/index.php' );
}
if( isset( $_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
    $result = sqlsrv_query(
        $NEI,
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
            $_SESSION['User']
        )
    );
	$Privileges = array();
	if( $result ){while( $Privilege = sqlsrv_fetch_array( $result ) ){ $Privileges[ $Privilege[ 'Access_Table' ] ] = $Privilege; } }
    if(	!isset( $Connection[ 'ID' ] )
	   	|| !isset($Privileges[ 'Contract' ])
	  		|| $Privileges[ 'Contract' ][ 'User_Privilege' ]  < 4
	  		|| $Privileges[ 'Contract' ][ 'Group_Privilege' ] < 4
        || $Privileges[ 'Contract' ][ 'Other_Privilege' ] < 4){
				?><?php require( '../404.html' );?><?php }
    else {
  		sqlsrv_query(
          $NEI,
          "   INSERT INTO Activity([User], [Date], [Page])
              VALUES( ?, ?, ? );",
          array(
              $_SESSION['User'],
              date( 'Y-m-d H:i:s' ),
              'contracts.php'
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
	    <?php require( 'cgi-bin/php/element/navigation/index.php' );?>
	    <?php require( 'cgi-bin/php/element/loading.php' );?>
	    <div id='page-wrapper' class='content'>
			<div class='card card-full card-primary border-0'>
				<div class='card-heading bg-white text-black'><h4><?php $Icons->Contract( );?> Contracts</h4></div>
				<div class='card-body bg-dark'>
					<div id='Form_Lead'>
						<div class='card'>
							<div class='card-heading'></div>
							<div class='card-body'>
								<div style='display:block !important;'>
									<fieldset >
										<legend>Contract</legend>
										<editor-field name='ID'></editor-field>
										<editor-field name='Customer'></editor-field>
										<editor-field name='Location'></editor-field>
										<editor-field name='Job'></editor-field>
										<editor-field name='Start_Date'></editor-field>
										<editor-field name='End_Date'></editor-field>
										<editor-field name='Amount'></editor-field>
										<editor-field name='Cycle'></editor-field>
										<editor-field name='Escalation_Factor'></editor-field>
										<editor-field name='Escalation_Date'></editor-field>
										<editor-field name='Escalation_Type'></editor-field>
										<editor-field name='Escalation_Cycle'></editor-field>
										<editor-field name='Escalation_Link'></editor-field>
										<editor-field name='Escalation_Remarks'></editor-field>
									</fieldset>
								</div>
							</div>
						</div>
					</div>
					<table id='Table_Contracts' class='display' cellspacing='0' width='100%'>
						<thead class='text-white border border-white'><tr>
							<th class='text-white border border-white'>ID</th>
							<th class='text-white border border-white'>Customer</th>
							<th class='text-white border border-white'>Location</th>
							<th class='text-white border border-white'>Job</th>
							<th class='text-white border border-white'>Start</th>
							<th class='text-white border border-white'>End</th>
							<th class='text-white border border-white'>Length</th>
							<th class='text-white border border-white'>Amount</th>
							<th class='text-white border border-white'>Cycle</th>
							<th class='text-white border border-white'>Esc. Factor</th>
							<th class='text-white border border-white'>Esc. Date</th>
							<th class='text-white border border-white'>Esc. Type</th>
							<th class='text-white border border-white'>Esc. Cycle</th>
							<th class='text-white border border-white'>Link</th>
							<th class='text-white border border-white'>Remarks</th>
			            </tr>
			            <tr>
							<th class='text-white border border-white'><input class='redraw' type='text' name='ID' placeholder='ID' /></th>
							<th class='text-white border border-white'><input class='redraw' type='text' name='Customer' placeholder='Customer' /></th>
							<th class='text-white border border-white'><input class='redraw' type='text' name='Location' placeholder='Location' /></th>
							<th class='text-white border border-white'><input class='redraw' type='text' name='Job' placeholder='Job' /></th>
							<th class='text-white border border-white'><input class='redraw date' type='text' name='Start' placeholder='Start' /></th>
							<th class='text-white border border-white'><input class='redraw date' type='text' name='End' placeholder='End' /></th>
							<th class='text-white border border-white'><input class='redraw' type='text' name='Length' placeholder='Length' /></th>
							<th class='text-white border border-white'><input class='redraw' type='text' name='Amount' placeholder='Amount' /></th>
							<th class='text-white border border-white'><input class='redraw' type='text' name='Cycle' placeholder='Cycle' /></th>
							<th class='text-white border border-white'><input class='redraw' type='text' name='Esc. Factor' placeholder='Esc. Factor' /></th>
							<th class='text-white border border-white'><input class='redraw' type='text' name='Esc. Date' placeholder='Esc. Date' /></th>
							<th class='text-white border border-white'><input class='redraw' type='text' name='Esc. Type' placeholder='Esc. Type' /></th>
							<th class='text-white border border-white'><input class='redraw' type='text' name='Esc. Cycle' placeholder='Esc. Cycle' /></th>
							<th class='text-white border border-white'><input class='redraw' type='text' name='Link' placeholder='Link' /></th>
							<th class='text-white border border-white'><input class='redraw' type='text' name='Remarks' placeholder='Remarks' /></th>
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
} else {?><html><head><script>document.location.href='../login.php?Forward=contracts.php';</script></head></html><?php }?>
