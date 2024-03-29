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
        ||  !isset( $Privileges[ 'Request' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'Request' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        \singleton\database::getInstance( )->query(
          null,
          " INSERT INTO Activity([User], [Date], [Page] )
            VALUES( ?, ?, ? );",
          array(
            $_SESSION[ 'Connection' ][ 'User' ],
            date('Y-m-d H:i:s'),
            'request.php'
        )
      );
    if(!isset($array['ID']) ){?><html><head><script>document.location.href='../login.php?Forward=personnel_request.php';</script></head></html><?php }
    else {
$Mechanic = is_numeric($_SESSION['User']) ? $_SESSION['User'] : -1;

if($Mechanic > 0){
    $Call_Sign = "";
    $r = \singleton\database::getInstance( )->query(
      null,
        " SELECT
              Emp.*,
              Emp.Last as Last_Name,
              Rol.*,
              PRWage.Reg as Wage_Regular,
              PRWage.OT1 as Wage_Overtime,
              PRWage.OT2 as Wage_Double_Time
          FROM
              (Emp LEFT JOIN PRWage ON Emp.WageCat = PRWage.ID)
              LEFT JOIN Rol ON Emp.Rol = Rol.ID
          WHERE Emp.ID = " . $_SESSION['User']);
    $User = sqlsrv_fetch_array($r);
    $Call_Sign = $array['CallSign'];
    $Alias = $array['fFirst'][0] . $array['Last'];
    $Employee_ID = $array['fWork'];
    while($a= sqlsrv_fetch_array($r)){}
}?><!DOCTYPE html>
<html lang="en">
<head>
  <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
     <?php  $_GET[ 'Bootstrap' ] = '5.1';?>
     <?php  $_GET[ 'Entity_CSS' ] = 1;?>
     <?php	require( bin_meta . 'index.php');?>
     <?php	require( bin_css  . 'index.php');?>
     <?php  require( bin_js   . 'index.php');?>
</head>

<body onload="finishLoadingPage();">
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
       <?php require( bin_php . 'element/navigation.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content'>
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Personnel Request</h1>
                </div>
            </div>
            <div class='col-md-12'><form>
                    <div class='panel panel-red'>
                        <div class='panel-heading'>
                            Request Details
                        </div>
                        <div class='panel-body'>
                            <div class='row'>
                                <div class='col-xs-4'>
                                    <b>Name</b>
                                </div>
                                <div class='col-xs-8 input-group'>
                                    <input type='text' class='form-control' name='Full_Name' value='<?php echo proper($User['Last_Name']);?>, <?php echo proper($User['fFirst']);?> <?php echo proper($User['Middle']);?>' disabled='disabled' />
                                </div>
                            </div>
                            <div class='row'>
                                <div class='col-xs-4'>
                                    <b>Date of Request</b>
                                </div>
                                <div class='col-xs-8 input-group'>
                                    <input class='form-control' type='text' name='Date_of_Request' size='10' />
                                </div>
                            </div>
                            <div class='row'>
                                <div class='col-xs-4'>
                                    <b>Type of Support</b>
                                </div>
                                <div class='col-xs-8 input-group'>
                                    <select name='Type_of_Support' class='form-control'>
                                        <option value='Foreman'>Foreman</option>
                                        <option value='Engineer'>Engineer</option>
                                        <option value='Team'>Team</option>
                                        <option value='Mechanic'>Mechanic</option>
                                        <option value='Helper' selected='selected'>Helper</option>
                                        <option value='Other'>Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class='row'>
                                <div class='col-xs-4'><b>Job:</b></div>
                                <div class='col-xs-8 input-group'>
                                    <input id="job" name='Job'" class='form-control'>
                                    <input name='Job' type="hidden" id="job-id">
                                    <style>p#job-description{margin:0px;}</style>
                                    <p id="job-description"></p>
                                </div>
                            </div>
                            <div class='row'>
                                <div class='col-xs-4'>
                                    <b>Reason</b>
                                </div>
                                <div class='col-xs-8 input-group'>
                                    <textarea name='Reason' class='form-control' col='10'></textarea>
                                </div>
                            </div>
                            <br />
                            <hr />
                            <br />
                            <div class='row'>
                                <div class='col-xs-12 input-group'>
                                    <input type='submit' class='form-control' value='Submit Safety Report' />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form></div>
        </div>
    </div>
    <!-- Bootstrap Core JavaScript -->


    <!-- Metis Menu Plugin JavaScript -->


    <!-- Custom Theme JavaScript -->


    <!-- JQUERY UI Javascript -->


    <script>
        var reset_loc = 0;
        $(document).ready(function(){
            $("input[name='Date_of_Incident']").datepicker({});
        });
    </script>
    <script>
    $(document).ready(function(){
        var Tickets = [<?php
                $r = $database->query(null,"SELECT Job.ID, Job.fDesc as Description, JobType.Type as Type, Loc.Tag FROM Job LEFT JOIN nei.dbo.JobType ON Job.Type = JobType.ID LEFT JOIN TicketD ON Job.ID = TicketD.Job LEFT JOIN nei.dbo.Loc ON Job.Loc = Loc.Loc LEFT JOIN Emp ON TicketD.fWork = Emp.fWork where Emp.ID='{$_SESSION['User']}'");
                $Jobs = array();
                while($Job = sqlsrv_fetch_array($r)){
                    $Job['fDesc'] = preg_replace( "/\r|\n/", "; ", $Job['fDesc'] );
                    $Jobs[] = $Job;}
                $Duplicate_Jobs = array();
                $Selected_Jobs = array();
                foreach($Jobs as $Job){if(!in_array($Job['ID'], $Duplicate_Jobs)){$Selected_Jobs[] = "{value:'{$Job['ID']}', label:'{$Job['ID']}', desc:'{$Job['Tag']}; {$Job['Type']}; {$Job['Description']}'}";$Duplicate_Jobs[]=$Job['ID'];}}
                echo implode(",",$Selected_Jobs);
                unset($Selected_Jobs,$Duplicate_Jobs,$Jobs);
            ?>];
            $( "#job" ).autocomplete({
              minLength: 0,
              source: Tickets,
              focus: function( event, ui ) {
                $( "#job" ).val( ui.item.label );
                return false;
              },
              select: function( event, ui ) {
                $( "#job" ).val( ui.item.label );
                $( "#job-id" ).val( ui.item.value );
                $( "#job-description" ).html( ui.item.desc );
                return false;
              }
            })
            .autocomplete( "instance" )._renderItem = function( ul, item ) {
              return $( "<li>" )
                .append( "<div>" + item.label + "<br>" + item.desc + "</div>" )
                .appendTo( ul );
            };
        });
    </script>
</body>
</html>
 <?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=personnel_request.php';</script></head></html><?php }?>
