<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [ 'read_and_close' => true ] ); 
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/cgi-bin/php/index.php' );
}
if(     !isset( $_SESSION[ 'User' ], $_SESSION[ 'Connection' ] )
    ||  !connection( 'Demo', \singleton\database::getInstance( ), $_SESSION[ 'Connection' ][ 'Branch_ID' ], $_SESSION[ 'Connection' ][ 'Hash' ] ) ){
        header( 'Location: https://beta.nouveauelevator.com/login.php' );
        exit; }
$result = $database->query(
  null,
  " SELECT *,
          Emp.fFirst AS First_Name,
          Emp.Last   AS Last_Name,
          Emp.Field  AS Field
    FROM  Emp
    WHERE Emp.ID = ?;",
  array(
    $_SESSION[ 'Connection' ][ 'Branch_ID' ]
  )
);
$User = sqlsrv_fetch_array( $result );

$Privileges = privileges( null, \singleton\database::getInstance( ), $_SESSION[ 'Connection' ][ 'Branch_ID' ] );

$image_result = $database->query( 
  'Portal',
  " SELECT  Picture,
            Picture_Type AS Type 
    FROM    Portal.dbo.Portal 
    WHERE   Portal.Branch = ? 
            AND Portal.Branch_ID = ?;",
  array( 
    $_SESSION[ 'Connection' ][ 'Branch' ],
    $_SESSION[ 'User' ]
  )
);

?><!DOCTYPE html>
<html lang='en'>
<head>
  <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
  <?php require( bin_meta . 'index.php' );?>
  <?php $_GET[ 'Bootstrap' ] = '5.1';?>
  <?php require( bin_css  . 'index.php' );?>
  <?php require( bin_js   . 'index.php' );?>
</head>
<body onload='finishLoadingPage();' >
  <?php require( bin_php . 'element/navigation/index.php');?>
  <?php require( bin_php . 'element/loading.php');?>
  <div id='page-wrapper' class='content'>
    <section id='account-menu' style='padding:50px;background-color:#0f0f0f;'>
      <div class='row'>
        <?php 
          if( $image_result ){
            $row = sqlsrv_fetch_array( $image_result );
            if( is_null( $row[ 'Picture' ] ) ){
              ?><div class='offset-3 col-6'><button class='slim' style='text-align:center;' onClick="browseProfilePicture( );"><img src='media/images/icons/avatar.png'  style='max-width:100%;max-height:200px;' /></button></div><?php
            } else {
              ?><div class='offset-3 col-6'><button class='slim' style='text-align:center;' onClick="browseProfilePicture( );"><img class='round border border-white' src='<?php print "data:" . $row['Type'] . ";base64, " . $row['Picture'];?>'  style='max-width:100%;max-height:200px;' /></button></div><?php
            }
          } else {?><div class='offset-3 col-6'><button class='slim' style='text-align:center;' onClick="browseProfilePicture( );"><img src='media/images/icons/avatar.png'  style='max-width:100%;max-height:200px;' /></button></div><?php }?>
        <div class='col-3'><button class='slim text-center text-white' onClick="document.location.href='settings.php';" style='text-align:right;'><i class="fas fa-user-cog fa-2x"></i></button></div>
      </div>
      <div style='height:5px;'>&nbsp;</div>
      <div class='row'>
        <div class='col-2'>&nbsp;</div>
        <div class='col-8 text-center text-white'><?php echo $User[ 'Name' ];?></div>
        <div class='col-2'>&nbsp;</div>
      </div>
      <div class='row'>
        <div class='col-2'>&nbsp;</div>
        <div class='col-8 text-center text-white'><?php echo $User[ 'Title' ];?></div>
        <div class='col-2'>&nbsp;</div>
      </div>
    </section>
    <?php if( $User[ 'Field' ] == 1 ){
        $r = $database->query(null, "SELECT * FROM Attendance WHERE Attendance.[User] = ? AND Attendance.[End] IS NULL",array($_SESSION['User']));
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
      <?php if(isset($Ticket) && is_array($Ticket)){?><div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='ticket.php?ID=<?php echo $Ticket['ID'];?>';">
        <div class='p-1 border border-white'>
          <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Ticket(3);?></div>
          <div class ='nav-text'>Active Ticket</div>
        </div>
      </div><?php }?>
      <?php if(isset($Privileges['Time']) && $Privileges['Time']['Other_Privilege'] >= 4){?><div class='link-page text-white col-xl-2 col-6' onclick="document.location.href='scheduler.php'">
        <div class='p-1 border border-white'>
          <div class='nav-icon'><i class="fa fa-question-circle fa-3x fa-fw" aria-hidden="true"></i></div>
          <div class ='nav-text'>Attendance</div>
        </div>
      </div><?php }?>
      <div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='bugs.php'">
        <div class='p-1 border border-white'>
          <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Customer(3);?></div>
          <div class ='nav-text'>Errors</div>
        </div>
      </div>
      <?php if(isset($Privileges['Invoice']) && $Privileges['Invoice']['User_Privilege'] >=4){?><div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='collections.php'">
        <div class='p-1 border border-white'>
          <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Collection(3);?></div>
          <div class ='nav-text'>Collections</div>
        </div>
      </div><?php } ?>
      <?php if(isset($Privileges['Contract']) && $Privileges['Contract']['User_Privilege'] >=4){?><div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='contracts.php'">
        <div class='p-1 border border-white'>
          <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Contract(3);?></div>
          <div class ='nav-text'>Contracts</div>
        </div>
      </div><?php } ?>
      <?php if(isset($Privileges['Customer']) && $Privileges['Customer']['User_Privilege'] >=4){?><div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='customers.php'">
        <div class='p-1 border border-white'>
          <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Customer(3);?></div>
          <div class ='nav-text'>Customers</div>
        </div>
      </div><?php } ?>
      <?php /*if(isset($Privileges['Dispatch']) && $Privileges['Ticket']['Other_Privilege'] >=4){?><div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='dispatch.php'">
        <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Dispatch(3);?></div>
        <div class ='nav-text'>Dispatch</div>
      </div><?php } */?>
      <?php /*if(isset($Privileges['Ticket']) && $Privileges['Ticket']['Other_Privilege'] >=7){?><div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='gps_locations.php'">
        <div class='nav-icon'><i class="fa fa-tencent-weibo fa-3x" aria-hidden="true"></i></div>
        <div class ='nav-text'>Geofence</div>
      </div><?php }*/ ?>
      <?php if(isset($User['Title']) && strpos($User['Title'], 'SUPER') === false && ($User['Title'] != 'OFFICE' || in_array($User['ID'],array(895,250)))){?><div class='link-page text-white col-xl-2 col-6' onclick="window.open('https://docs.google.com/forms/d/1kqijgH7gnxEVwYaobgCn8nbjNFG-vXXpecXMHkqy0GA/viewform?edit_requested=true');">
        <div class='p-1 border border-white'>
          <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Safety_Report(3);?></div>
          <div class ='nav-text'>Incident Report</div>
        </div>
      </div><?php } ?>
      <?php if(isset($User['Title']) && strpos($User['Title'], 'SUPER') !== false && ($User['Title'] != 'OFFICE' || in_array($User['ID'],array(895,250)))){?><div class='link-page text-white col-xl-1 col-3' onclick="window.open('https://docs.google.com/a/nouveauelevator.com/forms/d/1yeaJSLEJMkt8HYnx_fzGHJtBjU_iOlXCNtQT6r5pXTE/edit?usp=drive_web');">
        <div class='p-1 border border-white'>
          <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Safety_Report(3);?></div>
          <div class ='nav-text'>Incident Report</div>
        </div>
      </div><?php } ?>
      <?php if(isset($Privileges['Invoice']) && $Privileges['Invoice']['User_Privilege'] >=4){?><div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='invoices.php'">
        <div class='p-1 border border-white'>
          <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Invoice(3);?></div>
          <div class ='nav-text'>Invoices</div>
        </div>
      </div><?php } ?>
      <?php if(isset($Privileges['Job']) && $Privileges['Job']['User_Privilege'] >=4){?><div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='jobs.php'">
        <div class='p-1 border border-white'>
          <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Job(3);?></div>
          <div class ='nav-text'>Jobs</div>
        </div>
      </div><?php } ?>
      <?php if(isset($Privileges['Lead']) && $Privileges['Lead']['User_Privilege'] >=4){?><div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='leads.php'">
        <div class='p-1 border border-white'>
          <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Customer(3);?></div>
          <div class ='nav-text'>Leads</div>
        </div>
      </div><?php } ?>
      <?php if(isset($Privileges['Location']) && $Privileges['Location']['User_Privilege'] >=4){?><div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='locations.php'">
        <div class='p-1 border border-white'>
          <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Location(3);?></div>
          <div class ='nav-text'>Locations</div>
        </div>
      </div><?php } ?>
      <div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='../login.php?Logout=TRUE'">
        <div class='p-1 border border-white'>
          <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Logout(3);?></div>
          <div class ='nav-text'>Logout</div>
        </div>
      </div>
      <?php if(isset($Privileges['Map']) && $Privileges['Map']['Other_Privilege'] >= 4){?><div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='map.php'">
        <div class='p-1 border border-white'>
          <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Map(3);?></div>
          <div class ='nav-text'>Map</div>
        </div>
      </div><?php }?>
      <?php if(isset( $Privileges['Admin'] ) && $Privileges['Admin']['Other_Privilege'] >= 7 ){?><div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='payroll.php'">
        <div class='p-1 border border-white'>
          <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Invoice(3);?></div>
          <div class ='nav-text'>Payroll</div> 
        </div>
      </div><?php }?>
      <?php if(isset($Privileges['Privilege']) && $Privileges['Privilege']['User_Privilege'] >=4){?><div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='privileges.php'">
        <div class='p-1 border border-white'>
          <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Privilege(3);?></div>
          <div class ='nav-text'>Privileges</div> 
        </div>
      </div><?php } ?>
      <?php if((isset($Privileges['Code']) && $Privileges['Code']['Other_Privilege'] >= 4) && $_SESSION['User'] != 975){?><div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='batch_process_deficiencies2.php'">
        <div class='p-1 border border-white'>
          <div class='nav-icon'><i class="fa fa-clipboard fa-3x fa-fw" aria-hidden="true"></i></div>
          <div class ='nav-text'>Processing</div>
        </div>
      </div><?php }?>
      <div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='user.php'">
          <div class='p-1 border border-white'>
            <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->User(3);?></div>
            <div class ='nav-text'>Profile</div>
          </div>
      </div>
      <?php if(isset($Privileges['Sales_Admin']) && $Privileges['Sales_Admin']['Other_Privilege'] >= 4){?><div class='link-page text-white col-xl-2 col-6' onclick="document.location.href='accounts_v2019.php'">
        <div class='p-1 border border-white'>
          <div class='nav-icon'><i class="fa fa-dollar fa-3x fa-fw" aria-hidden="true"></i></div>
          <div class ='nav-text'>Profitability</div>
        </div>
      </div><?php }?>
      <?php if(isset($Privileges['Invoice']) && $Privileges['Invoice']['Other_Privilege'] >= 4 ){?><div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='proposals.php'">
        <div class='p-1 border border-white'>
          <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Proposal(3);?></div>
          <div class ='nav-text'>Proposals</div>
        </div>
      </div><?php } ?>
      <?php /*<div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='pto.php'">
        <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Calendar(3);?></div>
        <div class ='nav-text'>PTO</div>
      </div>*/?>
      <?php if(isset( $Privileges['Admin'] ) && $Privileges['Admin']['Other_Privilege'] >= 7 ){?><div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='elevt_report.php'">
        <div class='p-1 border border-white'>
          <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Customer(3);?></div>
          <div class ='nav-text'>Questions</div>
        </div>
      </div><?php }?>
      <?php if(isset($Privileges['Requisition']) && $Privileges['Requisition']['User_Privilege'] >=4){?><div class='link-page text-white col-xl-2 col-6' onclick="document.location.href='requisitions.php'">
        <div class='p-1 border border-white'>
          <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Requisition(3);?></div>
          <div class ='nav-text'>Requisitions</div>
        </div>
      </div><?php } ?>
      <?php if(isset($Privileges['Time']) && $Privileges['Time']['Other_Privilege'] >= 4){?><div class='link-page text-white col-xl-2 col-6' onclick="document.location.href='review.php'">
        <div class='p-1 border border-white'>
          <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Customer( 3 );?></div>
          <div class ='nav-text'>Review</div>
        </div>
      </div><?php }?>
      <?php if(isset($Privileges['Invoice']) && $Privileges['Invoice']['User_Privilege'] >= 7){?><div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='invoice-registrar-1.php'">
        <div class='p-1 border border-white'>
          <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Invoice(3);?></div>
          <div class ='nav-text'>Registrar</div>
        </div>
      </div><?php } ?>
      <?php if(isset($Privileges['Route']) && $Privileges['Route']['Other_Privilege'] >=4){?><div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='routes.php'">
        <div class='p-1 border border-white'>
          <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Route(3);?></div>
          <div class ='nav-text'>Routes</div>
        </div>
      </div><?php } ?>
      <?php
      $result = $database->query(
        null,
        " SELECT Route.ID
          FROM   Route
                 LEFT JOIN Emp ON Route.Mech = Emp.fWork
          WHERE  Emp.ID = ?;",
        array( 
          $_SESSION['User']
        )
      );
      $RouteNav = sqlsrv_fetch_array($result);
      if(isset($Privileges['Route']) && $Privileges['Route']['User_Privilege'] >= 4 && is_array($RouteNav) && isset($RouteNav['ID']) && $RouteNav['ID'] > 0 ){?><div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='route.php?ID=<?php echo $RouteNav['ID'];?>'">
        <div class='p-1 border border-white'>
          <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Route(3);?></div>
          <div class ='nav-text'>Route</div>
        </div>
      </div><?php } ?>
      <?php if(isset($Privileges['Safety_Report']) && $Privileges['Safety_Report']['User_Privilege'] >= 4){?><div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='reports.php'">
        <div class='p-1 border border-white'>
          <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Report(3);?></div>
          <div class ='nav-text'>Reports</div>
        </div>
      </div><?php } ?>
      <?php if(False){?><div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='settings.php'">
        <div class='p-1 border border-white'>
          <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Unit(3);?></div>
          <div class ='nav-text'>Settings</div>
        </div>
      </div><?php }?>
      <?php if(isset($Privileges['Admin']) && $Privileges['Admin']['User_Privilege'] >= 4){?><div class='link-page text-white col-xl-2 col-6' onclick="document.location.href='supervising.php'">
        <div class='p-1 border border-white'>
          <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Customer(3);?></div>
          <div class ='nav-text'>Supervising</div>
        </div>
      </div><?php } ?>
      <?php if(isset($Privileges['Ticket']) && $Privileges['Ticket']['Other_Privilege'] >= 4){?><div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='support.php'">
        <div class='p-1 border border-white'>
          <div class='nav-icon'><i class="fa fa-question-circle fa-3x fa-fw" aria-hidden="true"></i></div>
          <div class ='nav-text'>Support</div>
        </div>
      </div><?php }?>
      <?php if(isset($Privileges['Ticket']) && $Privileges['Ticket']['Other_Privilege'] >= 4){?><div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='tickets.php'">
        <div class='p-1 border border-white'>
          <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Ticket(3);?></div>
          <div class ='nav-text'>Tickets</div>
        </div>
      </div><?php } ?>
      <?php if(isset($Privileges['Territory']) && $Privileges['Territory']['User_Privilege'] >= 4){?><div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='territories.php'">
        <div class='p-1 border border-white'>
          <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Territory(3);?></div>
          <div class ='nav-text'>Territories</div>
        </div>
      </div><?php }?>
      <?php if((isset($Privileges['Code']) && $Privileges['Code']['Other_Privilege'] >= 4)){?><div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='category_tests.php'">
        <div class='p-1 border border-white'>
          <div class='nav-icon'><i class="fa fa-clipboard fa-3x fa-fw" aria-hidden="true"></i></div>
          <div class ='nav-text'>Tests</div>
        </div>
      </div><?php }?>
      <?php if((isset($Privileges['Testing_Admin']) && $Privileges['Testing_Admin']['Other_Privilege'] >= 4)){?><div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='draft_category_tests.php'">
        <div class='p-1 border border-white'>
          <div class='nav-icon'><i class="fa fa-clipboard fa-3x fa-fw" aria-hidden="true"></i></div>
          <div class ='nav-text'>Testing</div>
        </div>
      </div><?php }?>
      <?php if(isset($Privileges['Time']) && $Privileges['Time']['User_Privilege'] >= 4){?><div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='timesheet.php'">
        <div class='p-1 border border-white'>
          <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Timesheet(3);?></div>
          <div class ='nav-text'>Timesheet</div>
        </div>
      </div><?php }?>
      <?php if(isset($Privileges['Unit']) && $Privileges['Unit']['User_Privilege'] >= 4 || $Privileges['Unit']['Group_Privilege'] >= 4 || $Privileges['Unit']['Other_Privilege'] >= 4){?><div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='units.php'">
        <div class='p-1 border border-white'>
          <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Unit(3);?></div>
          <div class ='nav-text'>Units</div>
        </div>
      </div><?php } ?>
      <?php if(isset($Privileges['User']) && $Privileges['User']['Other_Privilege'] >= 7){?><div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='users.php'">
        <div class='p-1 border border-white'>
          <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Users(3);?></div>
          <div class ='nav-text'>Users</div>
        </div>
      </div><?php } ?>
      <?php if(isset($Privileges['Violation']) && $Privileges['Violation']['User_Privilege'] >=4){?><div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='violations.php'">
        <div class='p-1 border border-white'>
          <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Violation(3);?></div>
          <div class ='nav-text'>Violations</div>
        </div>
      </div><?php } ?>
      <div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='https://www.nouveauelevator.com/';">
        <div class='p-1 border border-white'>
          <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Web(3);?></div>
          <div class ='nav-text'>Website</div>
        </div>
      </div>
      <div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='work.php'">
        <div class='p-1 border border-white'>
          <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Ticket(3);?></div>
          <div class ='nav-text'>Work</div>
        </div>
      </div>
      <?php if(isset($Privileges['Admin']) && $Privileges['Admin']['Other_Privilege'] >= 7){?><div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='interface.php'">
        <div class='p-1 border border-white'>
          <div class='nav-icon'><i class="fa fa-user-secret fa-3x fa-fw" aria-hidden="true"></i></div>
          <div class ='nav-text'>Beta</div>
        </div>
      </div><?php }?>
      <?php if(isset($Privileges['Admin']) && $Privileges['Admin']['Other_Privilege'] >= 7){?><div class='link-page text-white col-xl-1 col-3' onclick="document.location.href='../portal2/'">
        <div class='p-1 border border-white'>
          <div class='nav-icon'><i class="fa fa-user-secret fa-3x fa-fw" aria-hidden="true"></i></div>
          <div class ='nav-text'>Legacy</div>
        </div>
      </div><?php }?>
    </section>
  </div>
</div>
</body>
</html>