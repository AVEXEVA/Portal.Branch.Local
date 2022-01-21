<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( );
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
		  FROM    Emp
		  WHERE   Emp.ID = ?;",
		array(
		  	$_SESSION[ 'Connection' ][ 'User' ]
		)
	);
	$User   = $result ? sqlsrv_fetch_array( $result ) : null;
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
		  	$_SESSION[ 'Connection' ][ 'User' ]
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
    if(!isset( $Connection[ 'ID' ] ) ){ ?><?php
      header( 'Location: ../index.php' );
      exit;
    ?><?php }
    else {
      \singleton\database::getInstance( )->query(
        null,
        " INSERT INTO Activity([User], [Date], [Page] )
          VALUES( ?, ?, ? );",
        array(
          $_SESSION[ 'Connection' ][ 'User' ],
          date('Y-m-d H:i:s'),
          'index.php'
        )
      );
      $result = $database->query(
        'Portal',
        " SELECT  [User].[ID],
                  [User].[Email],
                  [User].[Branch_Type],
                  [User].[Picture],
                  [User].[Picture_Type]
          FROM    [User]
          WHERE   [User].[ID] = ?;",
        array(
          $_SESSION[ 'Connection' ][ 'User' ]
        )
      );
      $User = $result ? sqlsrv_fetch_array( $result ) : null;
?><!DOCTYPE html>
<html lang='en'>
<head>
  <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
  <?php
    $_GET[ 'Bootstrap' ] = '5.1';
    $_GET[ 'Entity_CSS' ] = 1;
    require( bin_meta . 'index.php');
    require( bin_css  . 'index.php');
    require( bin_js   . 'index.php');
  ?>
</head>
<body>
  <?php require( bin_php . 'element/navigation.php');?>
  <div id='page-wrapper' class='content'>
    <section id='account-menu' style='padding:50px;background-color:#0f0f0f;'>
      <div class='row'>
        <?php
          if( !is_null( $User ) && isset( $User[ 'Picture' ] ) && !empty( $User[ 'Picture' ] ) ){
            ?><div class='offset-3 col-6'><button class='slim' onClick="browseProfilePicture( );"><img class='round border border-white' src='<?php print "data:" . $User[ 'Picture_Type' ] . ";base64, " . $User[ 'Picture' ];?>'  style='max-width:100%;max-height:200px;' /><div class="text-white"><?php echo $User[ 'Email'  ];  ?></div></button></div><?php
          } else {
            ?><div class='offset-3 col-6 text-center'>
              <button class='slim' onClick="browseProfilePicture( );">
                <img src='bin/media/image/avatar.png'  style='max-width:100%;max-height:150px;' />
              </button>
              <div class="text-white"><?php echo $User[ 'Email' ];  ?></div>
            </div><?php
          }
        ?>
      </div>
    </section>
    <?php if( $User[ 'Branch_Type' ] == 'Field' ){
      $r = $database->query(null, "SELECT * FROM Attendance WHERE Attendance.[User] = ? AND Attendance.[End] IS NULL",array($_SESSION[ 'Connection' ][ 'User' ]));
      if($r){$Attendance_Activity = sqlsrv_fetch_array($r);}
    ?><div class='card bg-darker text-light'>
      <div class='card-header bg-white text-black text-center'>Field Work</div>
      <div class='card-body'>
        <div class='row'>
          <div class='offset-md-3 col-md-3 col-6 text-center'>
            <div class='bg-darker border border-white'><i class="fas fa-business-time fa-fw fa-1x"></i> Clock In</div>
            <?php if(is_array($Attendance_Activity) && isset($Attendance_Activity['Start'])){
              ?><div><?php echo date("m/d/Y h:i A",strtotime($Attendance_Activity['Start']));?></div><?php
            } else {
              ?><button class='bg-light text-center' rel='in' onClick='attendance_clock(this);'>Start</button><?php
            }?>
          </div>
          <div class='col-md-3 col-6 text-center'>
            <div class='bg-darker border border-white'><i class="fas fa-clipboard-list"></i> Clock Out</div><?php
            if(is_array($Attendance_Activity) && isset($Attendance_Activity['Start'])){
              ?><button class='bg-light text-center' rel='out' onClick='attendance_clock(this);' >Finish</button><?php
            } else {
              ?><button class='bg-light text-center text-muted' disabled rel='out' onClick='attendance_clock(this);'>Finish</button><?php
            }?>
          </div>
        </div>
      </div>
    </div>
    <div class='dashboard card bg-darker p-1 border-0 text-white'>
      <div class='card-heading bg-secondary'>
        <ul>
          <li class='border-start border-white active' onClick="changePanel( this );" card='Tickets'><?php \singleton\fontawesome::getInstance()->Ticket( 1 );?> Tickets</li>
          <li class='border-start border-white' onClick="changePanel( this );" card='Locations'><?php \singleton\fontawesome::getInstance( )->Location( 1 );?> Locations</li>
        </ul>
      </div>
      <div class='card-body active bg-darker' card='Tickets'>
        <table id='Table_Tickets' class='display' cellspacing='0' width='100%' style='<?php if(isMobile()){?>font-size:10px;<?php }?>'>
          <thead><tr>
            <th class='border border-white' title='Location'></th>
            <th class='border border-white' title='ID'>ID</th>
            <th class='border border-white' title='Status'>Status</th>
            <th class='border border-white' title='Date'>Date</th>
            <th class='border border-white' title='Unit'>Unit</th>
            <th class='border border-white' title='Type'>Type</th>
            <th class='border border-white' title='Priority'>Priority</th>
          </tr></thead>
          <tfoot><tr>
            <th class='border border-white' colspan='5' onClick="document.location.href='tickets.php';" style='cursor:pointer;'><?php \singleton\fontawesome::getInstance( )->Ticket( 1 );?> Go to All Tickets</th>
            <th class='border border-white' colspan='2' onClick='document.location.href="ticket.php";'><i class='fa fa-plus fa-fw fa-1x'></i> New</th>
          </tr></tfoot>
        </table>
      </div>
      <div class="card-body bg-darker no-print filters" card='Locations' style='border-bottom:1px solid #1d1d1d;'>
        <div class='row'><div class='col-12'>&nbsp;</div></div>
        <div class='row'>
            <div class='col-4'>Search:</div>
            <div class='col-8'><input type='text' name='Search' placeholder='Search' onChange='redrawLocations( );' /></div>
        </div>
      </div>
      <div class='card-body bg-darker' card='Locations'>
        <table id='Table_Locations' class='display' cellspacing='0' width='100%'>
          <thead><tr>
            <th class='border border-white' title='ID'>ID</th>
            <th class='border border-white' title='Name'>Name</th>
            <th class='border border-white' title='Customer'>Customer</th>
            <th class='border border-white' title='City'>City</th>
            <th class='border border-white' title='Street'>Street</th>
            <th class='border border-white' title='Maintained'>Maintained</th>
            <th class='border border-white' title='Status'>Status</th>
          </tr></thead>
        </table>
      </div>
    </div>
    <?php } ?>
    <secton id='main-menu' class='row g-2 bg-darker'>
      <script>
        function togglePanel( link ){ link.parentNode.parentNode.classList.toggle('open'); }
      </script>
      <?php if( check( privilege_read, level_group, isset( $Privileges[ 'Contact' ] ) ? $Privileges[ 'Contact' ] : 0 ) ){
        ?><div class='link-page text-white col-xl-1 col-3 btn-three' onclick="document.location.href='contacts.php'">
        <div class='p-1 border'>
          <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Contacts(3);?></div>
          <div class ='nav-text'>Contacts</div>
        </div>
      </div><?php } ?>
      <?php if( check( privilege_read, level_group, isset( $Privileges[ 'Collection' ] ) ? $Privileges[ 'Collection' ] : 0 ) ){
        ?><div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='collections.php'">
        <div class='p-1 border'>
          <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Collection(3);?></div>
          <div class ='nav-text'>Collections</div>
        </div>
      </div><?php } ?>
      <?php if( check( privilege_read, level_group, isset( $Privileges[ 'Contract' ] ) ? $Privileges[ 'Contract' ] : 0 ) ){
        ?><div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='contracts.php'">
        <div class='p-1 border'>
          <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Contract(3);?></div>
          <div class ='nav-text'>Contracts</div>
        </div>
      </div><?php } ?>
      <?php if( check( privilege_read, level_group, isset( $Privileges[ 'Customer' ] ) ? $Privileges[ 'Customer' ] : 0 ) ){
        ?><div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='customers.php'">
        <div class='p-1 border'>
          <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Customer(3);?></div>
          <div class ='nav-text'>Customers</div>
        </div>
      </div><?php } ?>
      <?php if( check( privilege_read, level_group, isset( $Privileges[ 'Ticket' ] ) ? $Privileges[ 'Ticket' ] : 0 ) ){?><div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='work_orders.php'">
        <div class='p-1 border'>
          <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Ticket(3);?></div>
          <div class ='nav-text'>Dispatch</div>
        </div>
      </div><?php } ?>
      <?php if( check( privilege_read, level_group, isset( $Privileges[ 'Division' ] ) ? $Privileges[ 'Division' ] : 0 ) ){
        ?><div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='divisions.php'">
        <div class='p-1 border'>
          <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Web(3);?></div>
          <div class ='nav-text'>Divisions</div>
        </div>
      </div><?php } ?>
      <?php if( check( privilege_read, level_group, isset( $Privileges[ 'User' ] ) ? $Privileges[ 'User' ] : 0 ) ){?><div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='employees.php'">
        <div class='p-1 border'>
          <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Employees(3);?></div>
          <div class ='nav-text'>Employees</div>
        </div>
      </div><?php }?>
      <?php if( check( privilege_read, level_group, isset( $Privileges[ 'Invoice' ] ) ? $Privileges[ 'Invoice' ] : 0 ) ){
        ?><div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='invoices.php'">
        <div class='p-1 border'>
          <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Invoice(3);?></div>
          <div class ='nav-text'>Invoices</div>
        </div>
      </div><?php } ?>
      <?php if( check( privilege_read, level_group, isset( $Privileges[ 'Job' ] ) ? $Privileges[ 'Job' ] : 0 ) ){
        ?><div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='jobs.php'">
        <div class='p-1 border'>
          <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Job(3);?></div>
          <div class ='nav-text'>Jobs</div>
        </div>
      </div><?php } ?>
      <?php if( check( privilege_read, level_group, isset( $Privileges[ 'Lead' ] ) ? $Privileges[ 'Lead' ] : 0 ) ){
        ?><div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='leads.php'">
        <div class='p-1 border'>
          <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Leads(3);?></div>
          <div class ='nav-text'>Leads</div>
        </div>
      </div><?php } ?>
      <?php if( check( privilege_read, level_group, isset( $Privileges[ 'Location' ] ) ? $Privileges[ 'Location' ] : 0 ) ){
        ?><div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='locations.php'">
        <div class='p-1 border'>
          <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Location(3);?></div>
          <div class ='nav-text'>Locations</div>
        </div>
      </div><?php } ?>
      <?php if( check( privilege_read, level_group, isset( $Privileges[ 'Map' ] ) ? $Privileges[ 'Map' ] : 0 ) ){
        ?><div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='map.php'">
        <div class='p-1 border'>
          <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Map(3);?></div>
          <div class ='nav-text'>Map</div>
        </div>
      </div><?php } ?>
      <?php if( check( privilege_read, level_group, isset( $Privileges[ 'Proposal' ] ) ? $Privileges[ 'Proposal' ] : 0 ) ){?><div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='proposals.php'">
        <div class='p-1 border'>
          <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Proposal(3);?></div>
          <div class ='nav-text'>Proposals</div>
        </div>
      </div><?php } ?>
      <?php if( check( privilege_read, level_group, isset( $Privileges[ 'Requisition' ] ) ? $Privileges[ 'Requisition' ] : 0 ) ){?><div class='link-page text-white col-xl-2 col-6' onclick="document.location.href='requisitions.php'">
        <div class='p-1 border'>
          <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Requisition(3);?></div>
          <div class ='nav-text'>Requisitions</div>
        </div>
      </div><?php } ?>
      <?php if( check( privilege_read, level_group, isset( $Privileges[ 'Route' ] ) ? $Privileges[ 'Route' ] : 0 ) ){?><div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='routes.php'">
        <div class='p-1 border'>
          <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Route(3);?></div>
          <div class ='nav-text'>Routes</div>
        </div>
      </div><?php } ?>
      <?php if( check( privilege_read, level_group, isset( $Privileges[ 'Ticket' ] ) ? $Privileges[ 'Ticket' ] : 0 ) ){?><div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='tickets.php'">
        <div class='p-1 border'>
          <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Ticket(3);?></div>
          <div class ='nav-text'>Tickets</div>
        </div>
      </div><?php } ?>
      <?php if( check( privilege_read, level_group, isset( $Privileges[ 'Territory' ] ) ? $Privileges[ 'Territory' ] : 0 ) ){?><div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='territories.php'">
        <div class='p-1 border'>
          <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Territory(3);?></div>
          <div class ='nav-text'>Territories</div>
        </div>
      </div><?php }?>
      <?php if( check( privilege_read, level_group, isset( $Privileges[ 'Code' ] ) ? $Privileges[ 'Code' ] : 0 ) ){?><div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='category_tests.php'">
        <div class='p-1 border'>
          <div class='nav-icon'><i class="fa fa-clipboard fa-3x fa-fw" aria-hidden="true"></i></div>
          <div class ='nav-text'>Tests</div>
        </div>
      </div><?php }?>
      <?php if( check( privilege_read, level_group, isset( $Privileges[ 'Unit' ] ) ? $Privileges[ 'Unit' ] : 0 ) ){?><div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='units.php'">
        <div class='p-1 border'>
          <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Unit(3);?></div>
          <div class ='nav-text'>Units</div>
        </div>
      </div><?php } ?>
      <?php if( check( privilege_read, level_group, isset( $Privileges[ 'User' ] ) ? $Privileges[ 'User' ] : 0 ) ){?><div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='users.php'">
        <div class='p-1 border'>
          <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Users(3);?></div>
          <div class ='nav-text'>Users</div>
        </div>
      </div><?php } ?>
      <?php if( check( privilege_read, level_group, isset( $Privileges[ 'Violation' ] ) ? $Privileges[ 'Violation' ] : 0 ) ){?><div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='violations.php'">
        <div class='p-1 border'>
          <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Violation(3);?></div>
          <div class ='nav-text'>Violations</div>
        </div>
      </div><?php } ?>

    </section>
  </div>
</div>
</body>
</html><?php }
} else {?><script>document.location.href='../index.php';</script><?php }?>
