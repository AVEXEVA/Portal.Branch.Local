<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
if( isset( $_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
  //Connection
  $result = $database->query(
    null,
    " SELECT  Connection.*
      FROM    Connection
      WHERE       Connection.Connector = ?
              AND Connection.Hash = ?;",
    array(
      $_SESSION[ 'User' ],
      $_SESSION[ 'Hash' ]
    )
  );
  $Connection = sqlsrv_fetch_array( $result );
  //User
  $result = $database->query(
    null,
    " SELECT  Emp.*,
              Emp.fFirst AS First_Name,
              Emp.Last as Last_Name
      FROM    Emp
      WHERE   Emp.ID = ?;",
    array(
      $_SESSION[ 'User' ]
    )
  );
  $User = sqlsrv_fetch_array( $result );
  //Privileges
  $Privileges = array( );
  $Privileged = false;
  $result = \singleton\database::getInstance( )->query(
    'Portal',
    "   SELECT  [Privilege].[Access],
                [Privilege].[Owner],
                [Privilege].[Group],
                [Privilege].[Other]
      FROM      dbo.[Privilege]
      WHERE     Privilege.[User] = ?;",
    array(
      $_SESSION[ 'Connection' ][ 'User' ]
    )
  );
  if( $result ){ while( $Privilege = sqlsrv_fetch_array( $result ) ){ $Privileges[ $Privilege[ 'Access' ] ] = $Privilege; } }

  if( isset( $Privileges[ 'Ticket' ] ) && $Privileges[ 'Ticket' ][ 'Owner' ] >= 6){ $Privileged = TRUE; }
  if( !isset($Connection['ID'])  || !$Privileged ){require("401.html");}
  else {
    $database->query(null,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "ticket.php?ID=New"));
?><!DOCTYPE html>
<html lang="en">
<head>
  <title>Nouveau Texas | Portal</title>
  <?php require( bin_meta . 'index.php' );?>
  <?php $_GET[ 'Bootstrap' ] = '5.1';?>
  <?php require( bin_css  . 'index.php' );?>
  <?php require( bin_js   . 'index.php' );?>
  <style>
  .popup {
    z-index:999999999;
    position:absolute;
    margin-top:50px;
    top:0;
    left:0;
    background-color:#1d1d1d;
    height:100%;
    width:100%;
  }
  .form-group>label:first-child {
      min-width  : 100px;
      text-align : right;
  }
  .form-group>div>button {
    width : 100%;
    max-width : 300px;
  }
  .form-group>label {
    padding : 5px;
  }
  </style>
</head>
<body onload='finishLoadingPage();'>
  <div id='wrapper'>
    <?php require( bin_php . 'element/navigation.php'); ?>
    <?php require( bin_php . 'element/loading.php'); ?>
    <div id='page-wrapper' class='content' >
      <div class='panel-primary'>
        <div class='panel-heading' onClick='document.location.href="work.php";'><h4><?php \singleton\fontawesome::getInstance( )->Ticket( );?> Ticket Creation</h4></div>
        <div class='panel-body'>
  				<div class='row form-group'>
  					<div class='col-sm-12'>&nbsp;</div>
          </div>
          <div class='row form-group'>
  					<label class='col-auto'><?php \singleton\fontawesome::getInstance( )->User(1);?> Worker:</label>
  					<div class='col-auto'><?php echo $User['First_Name'] . " " . $User['Last_Name'];?></div>
          </div>
          <div class='row form-group'>
  					<label class='col-auto'><?php \singleton\fontawesome::getInstance( )->Calendar(1);?> Date:</label>
  					<div class='col-auto'><input name='Date' value='<?php echo isset($_GET['Date']) ? $_GET['Date'] : date('m/d/Y');?>'/></label>
          </div>
          <div class='row form-group'>
  					<label class='col-auto'><?php \singleton\fontawesome::getInstance( )->Location(1);?> Location:</label>
  					<div class='col-auto'><button type='button' onClick='selectLocations(this);' '><?php
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
            <script>
              function selectLocations(link){
                $.ajax({
                  url:"bin/php/element/ticket/selectLocations.php",
                  method:"GET",
                  success:function(code){
                    $("body").append(code);
                  }
                });
              }
            </script>
          </div>
          <div class='row form-group'>
            <label class='col-auto'><?php \singleton\fontawesome::getInstance( )->Unit(1);?> Unit:</label>
            <div class='col-auto'><button type='button' onClick='selectUnits(this);' '><?php
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
            <script>
              function selectUnits(link){
                $.ajax({
                  url:"bin/php/element/ticket/selectUnits.php?Location=<?php echo $_GET['Location'];?>",
                  method:"GET",
                  success:function(code){
                    $("body").append(code);
                  }
                });
              }
            </script>
          </div>
          <div class='row form-group'>
            <label class='col-auto'><?php \singleton\fontawesome::getInstance( )->Job(1);?> Job:</label>
            <div class='col-auto'><button type='button' onClick='selectJobs(this);' '><?php
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
            <script>
              function selectJobs(link){
                $.ajax({
                  url:"bin/php/element/ticket/selectJobs.php?Location=<?php echo $_GET['Location'];?>&Unit=<?php echo $_GET['Unit'];?>",
                  method:"GET",
                  success:function(code){
                    $("body").append(code);
                  }
                });
              }
            </script>
          </div>
          <div class='row form-group'>
            <label class='col-auto'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Level:</label>
            <div class='col-auto'><select style='width:100%;' name='Level'>
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
          <hr />
          <div class='row form-group'>
            <label class='col-auto'><?php \singleton\fontawesome::getInstance( )->Description(1);?> Description:</label>
          </div>
          <div class='row form-group'>
            <div class='col-sm-12'><textarea style='width:100%;' rows='8' name='Description'></textarea></div>
          </div>
        </div>
        <div class='panel-body'>
          <div class='row form-group'>
            <div class='col-sm-12'>&nbsp;</div>
          </div>
  				<div class='row form-group'>
  					<div class='col-sm-12'><button onClick='saveTicket(this);' ' >Save</button></div>
            <script>
            function saveTicket(link){
              $(link).html("Saving <img src='media/images/spinner.gif' height='25px' width='auto' />");
              var ticketData = new FormData();
              ticketData.append('Date',$("input[name='Date']").val());
              ticketData.append('Location','<?php echo isset($_GET['Location']) ? $_GET['Location'] : '';?>');
              ticketData.append('Unit','<?php echo isset($_GET['Unit']) ? $_GET['Unit'] : '';?>');
              ticketData.append('Job','<?php echo isset($_GET['Job']) ? $_GET['Job'] : '';?>');
              ticketData.append('Description',$("textarea[name='Description']").val());
              ticketData.append('Level',$("select[name='Level']").val());
              if(ticketData.get('Date') == '' || ticketData.get('Location') == '' || ticketData.get('Job') == '' || ticketData.get('Description') == ''){
                alert('Please fill out the necessasry information in order to continue.');
              } else {
                $.ajax({
                  url:"bin/php/post/save_new_ticket.php",
                  cache: false,
                  processData: false,
                  contentType: false,
                  method:"POST",
                  data: ticketData,
                  success:function(code){document.location.href='ticket.php?ID=' + code;}
                });
              }
            }
            </script>
  				</div>
          <div class='row form-group'>
            <div class='col-sm-12'>&nbsp;</div>
          </div>
    		</div>
    	</div>
    </div>
  </div>
	<style>
	  .ui-autocomplete {
		max-height: 100px;
		overflow-y: auto;
		/* prevent horizontal scrollbar */
		overflow-x: hidden;
	  }
	  /* IE 6 doesn't support max-height
	   * we use height instead, but this forces the menu to always be this tall
	   */
	  * html .ui-autocomplete {
		height: 100px;
	  }
	  </style>
	  <!-- Bootstrap Core JavaScript -->


    <?php require(PROJECT_ROOT.'js/datatables.php');?>

    <!-- Custom Theme JavaScript -->


    <!-- JQUERY UI Javascript -->


	<script>
	$(document).ready(function(){$("input[name='Date']").datepicker();});
	function closePopup(link){$(".popup").remove();}
	</script>
</body>
</html>
<?php }
}?>
