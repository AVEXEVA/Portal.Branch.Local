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
        ||  !isset( $Privileges[ 'Ticket' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'Ticket' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        \singleton\database::getInstance( )->query(
          null,
          " INSERT INTO Activity([User], [Date], [Page] )
            VALUES( ?, ?, ? );",
          array(
            $_SESSION[ 'Connection' ][ 'User' ],
            date('Y-m-d H:i:s'),
            'ticket.php'
        )
      );
?><!DOCTYPE html>
<html lang="en">
<head>
  <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
     <?php  $_GET[ 'Bootstrap' ] = '5.1';?>
     <?php  $_GET[ 'Entity_CSS' ] = 1;?>
     <?php	require( bin_meta . 'index.php');?>
     <?php	require( bin_css  . 'index.php');?>
     <?php  require( bin_js   . 'index.php');?>
</head>
<body onload='finishLoadingPage();'>
  <div id='wrapper'>
    <?php require( 'bin/php/element/navigation.php'); ?>
    <?php require( 'bin/php/element/loading.php'); ?>
    <div id='page-wrapper' class='content' >
      <div class='row'>
        <div class='offset-md-3 col-md-6 panel panel-primary panel-sync'><form id='Ticket' action='new-ticket.php' method='POST'>
          <div class='panel-heading' onClick='document.location.href="work.php";'><h4><?php \singleton\fontawesome::getInstance( )->Ticket( );?> Ticket Creation</h4></div>
          <div class='panel-body'>
            <div class='row g-0'>
              <div class='col-lg-12 col-xl-6'>
                <div class='row form-group g-0'><div class='col-sm-12'>&nbsp;</div></div>
                <div class='row form-group g-0'>
                  <label class='col-auto border-bottom v1'><?php \singleton\fontawesome::getInstance( )->User(1);?> Worker:</label>
                  <div class='col-auto padding v1'><input type='text' disabled value='<?php echo $User['First_Name'] . " " . $User['Last_Name'];?>' /></div>
                </div>
                <div class='row form-group g-0'>
                  <label class='col-auto border-bottom v1'><?php \singleton\fontawesome::getInstance( )->Calendar(1);?> Date:</label>
                  <div class='col-auto padding v1'><input name='Date' value='<?php echo isset($_GET['Date']) ? $_GET['Date'] : date('m/d/Y');?>'/></div>
                </div>
                <div class='row form-group g-0'>
                  <label class='col-auto border-bottom v1'><?php \singleton\fontawesome::getInstance( )->Location(1);?> Location:</label>
                  <div class='col-auto padding v1'><button type='button' onClick='selectLocations(this);'><?php
                  $pass = false;
                  if(isset($_GET['Location']) && is_numeric($_GET['Location'])){
                    $r = $database->query(null,"SELECT * FROM Loc WHERE Loc.Loc = ?;",array($_GET['Location']));
                    if($r){
                      $row = sqlsrv_fetch_array($r);
                      if(is_array($row)){
                        $pass = True;
                        echo $row['Tag'];
                      }
                    }
                  }
                  if(!$pass){?>Select Location<?php }?></button></div>

                </div>
                <div class='row form-group g-0'>
                  <label class='col-auto border-bottom v1'><?php \singleton\fontawesome::getInstance( )->Unit(1);?> Unit:</label>
                  <div class='col-auto padding v1'><button type='button' onClick='selectUnits(this);'><?php
                  $pass = false;
                  if(isset($_GET['Unit']) && is_numeric($_GET['Unit'])){
                    $r = $database->query(null,"SELECT * FROM Elev WHERE Elev.ID = ?;",array($_GET['Unit']));
                    if($r){
                      $row = sqlsrv_fetch_array($r);
                      if(is_array($row)){
                        $pass = True;
                        echo isset($row['State']) && strlen($row['State']) > 0 ? $row['State'] . ' - ' . $row['Unit'] : $row['Unit'];
                      }
                    }
                  }
                  if(!$pass){?>Select Unit<?php }?></button></div>

                </div>
                <div class='row form-group g-0'>
                  <label class='col-auto border-bottom v1'><?php \singleton\fontawesome::getInstance( )->Job(1);?> Job:</label>
                  <div class='col-auto padding v1'><button type='button' onClick='selectJobs(this);'><?php
                  $pass = false;
                  if(isset($_GET['Job']) && is_numeric($_GET['Job'])){
                    $r = $database->query(null,"SELECT * FROM Job WHERE Job.ID = ?;",array($_GET['Job']));
                    if($r){
                      $row = sqlsrv_fetch_array($r);
                      if(is_array($row)){
                        $pass = True;
                        echo $row['fDesc'];
                      }
                    }
                  }
                  if(!$pass){?>Select Job<?php }?></button></div>
                </div>
                <div class='row form-group g-0'>
                  <label class='col-auto border-bottom v1'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Level:</label>
                  <div class='col-auto padding v1'><select style='width:100%;' name='Level'>
                    <option value=''>Select</option>
                    <option value='1'>Service Call</option>
                    <option value='2'>Trucking</option>
                    <option value='3'>Modernization</option>
                    <option value='4'>Violations</option>
                    <option value='5'>Door Lock Monitoring</option>
                    <option value='6'>Repair</option>
                    <option value='7'>Annual Test</option>
                    <option value='10'>Preventative Maintenance</option>
                    <option value='11'>Survey</option>
                    <option value='12'>Engineering</option>
                    <option value='13'>Support</option>
                    <option value='14'>M&R</option>'
                  </select></div>
                </div>
              </div>
              <div class='col-md-12'>
                <div class='row form-group g-0'>
                  <label class='col-auto border-bottom v1'><?php \singleton\fontawesome::getInstance( )->Description(1);?> Description:</label>
                  <textarea class='col-sm-12' rows='8' name='Description'></textarea>
                </div>
              </div>
            </div>
            <div class='row'><div class='col-sm-12'>&nbsp;</div></div>
            <div class='row form-group g-0'>
              <div class='col-sm-12'><button onClick='saveTicket(this);' style='width:100%;height:35px;max-width:100%;'>Save</button></div>
            </div>
          </div>
        </form></div>
      </div>
  </div>
</body>
</html>
<?php }
}?>
