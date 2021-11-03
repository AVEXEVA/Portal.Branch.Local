<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( ); 
    require( '/var/www/portal.live.local/html/cgi-bin/php/index.php' );
}
if( isset( $_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
  //Connection
  $result = sqlsrv_query(
    $NEI,
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
  $result = sqlsrv_query(
    $NEI,
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
  $result = sqlsrv_query(
    $NEI,
    " SELECT  Privilege.Access_Table, 
              Privilege.User_Privilege, 
              Privilege.Group_Privilege, 
              Privilege.Other_Privilege 
      FROM    Portal.dbo.Privilege 
      WHERE   Privilege.User_ID = ?;",
    array(
      $_SESSION[ 'User' ]
    )
  );
  if( $result ){ while( $Privilege = sqlsrv_fetch_array( $result ) ){ $Privileges[ $Privilege[ 'Access_Table' ] ] = $Privilege; } }
  
  if( isset( $Privileges[ 'Ticket' ] ) && $Privileges[ 'Ticket' ][ 'User_Privilege' ] >= 6){ $Privileged = TRUE; }
  if( !isset($Connection['ID'])  || !$Privileged ){require("401.html");}
  else {
    sqlsrv_query($NEI,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "ticket.php?ID=New"));
?><!DOCTYPE html>
<html lang="en">
<head>
  <title>Nouveau Elevator | Portal</title>
  <?php require( 'cgi-bin/php/meta.php' );?>
  <?php $_GET[ 'Bootstrap' ] = '5.1';?>
  <?php require( 'cgi-bin/css/index.php' );?>
  <?php require( 'cgi-bin/js/index.php' ); ?>
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
      min-width  : 125px;
      width : auto;
      max-width : 150px;
      text-align : right;
  }
  .form-group>div>button {
    width : 100%;
    max-width : min-content;
    min-width : 200px;
  }
  .form-group>label {
    padding : 5px;
  }
  .panel-heading {
    padding : 5px;
  }
  </style>
  <style>
    div.panel.panel-sync>div.panel-body div.row {
      padding:5px;
    }
    div.panel.panel-sync>div.panel-body div.row>div:nth-child( 2 ):hover {
      background-color:#f0f0f0;
      color:black;
    }
    div.row>div.no-border {
      border:0px !important;
    }
    div.panel.panel-sync>div.panel-body div.row>div.border-bottom {
      border-bottom:1px solid darkgray;
    }
    div.panel.panel-sync>div.panel-body div.row>.border-bottom.v1, div.panel.panel-sync>div.panel-body div.row>.border-bottom.v2 {
      padding:5px;
      padding-bottom:6px;
    }
    div.panel.panel-sync div.panel-body div.row .padding.v1 {
      padding:5px;
      border:1px solid darkgray;
    }
    div.panel.panel-sync>div.panel-body div.row>div>input {
      margin:0px;
      border:0px;
      width:100%; 
      padding:5px;
    }
    div.panel.panel-sync>div.panel-body div.row>div>button {
      margin:5px;
      width:100%;
      padding:5px;
    }
    div.padding-center {
      padding:5px;
      text-align:center;
      border:1px solid darkgray;
    }
    table#Table_Locations tbody tr, table#Table_Locations tbody tr td a {
        color : black !important;
    }
    table#Table_Locations tbody tr:nth-child( even ) {
        background-color : rgba( 240, 240, 240, 1 ) !important;
    }
    table#Table_Locations tbody tr:nth-child( odd ) {
        background-color : rgba( 255, 255, 255, 1 ) !important;
    }
  </style>
</head>
<body onload='finishLoadingPage();'>
  <div id='wrapper'>
    <?php require( 'cgi-bin/php/element/navigation/index.php'); ?>
    <?php require( 'cgi-bin/php/element/loading.php'); ?>
    <div id='page-wrapper' class='content' >
      <div class='panel panel-primary panel-sync'><form id='Ticket' action='new-ticket.php' method='POST'>
        <div class='panel-heading' onClick='document.location.href="work.php";'><h4><?php $Icons->Ticket( );?> Ticket Creation</h4></div>
        <div class='panel-body'>
          <div class='row g-0'>
            <div class='col-lg-6 col-md-12'>
              <div class='row form-group'>
                <div class='col-sm-12'>&nbsp;</div>
              </div>
              <div class='row form-group'>
                <label class='col-auto border-bottom v1'><?php $Icons->User(1);?> Worker:</label>
                <div class='col-auto padding v1'><input type='text' disabled value='<?php echo $User['First_Name'] . " " . $User['Last_Name'];?>' /></div>
              </div>
              <div class='row form-group'>
                <label class='col-auto border-bottom v1'><?php $Icons->Calendar(1);?> Date:</label>
                <div class='col-auto padding v1'><input name='Date' value='<?php echo isset($_GET['Date']) ? $_GET['Date'] : date('m/d/Y');?>'/></label>
              </div>
              <div class='row form-group'>
                <label class='col-auto border-bottom v1'><?php $Icons->Location(1);?> Location:</label>
                <div class='col-auto padding v1'><button type='button' onClick='selectLocations(this);'><?php
                $pass = false; 
                if(isset($_GET['Location']) && is_numeric($_GET['Location'])){
                  $r = sqlsrv_query($NEI,"SELECT * FROM Loc WHERE Loc.Loc = ?;",array($_GET['Location']));
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
                      url:"cgi-bin/php/element/ticket/selectLocations.php",
                      method:"GET",
                      success:function(code){
                        $("body").append(code);
                      }
                    });
                  }
                </script>
              </div>
              <div class='row form-group'>
                <label class='col-auto border-bottom v1'><?php $Icons->Unit(1);?> Unit:</label>
                <div class='col-auto padding v1'><button type='button' onClick='selectUnits(this);'><?php
                $pass = false;
                if(isset($_GET['Unit']) && is_numeric($_GET['Unit'])){
                  $r = sqlsrv_query($NEI,"SELECT * FROM Elev WHERE Elev.ID = ?;",array($_GET['Unit']));
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
                      url:"cgi-bin/php/element/ticket/selectUnits.php?Location=<?php echo $_GET['Location'];?>",
                      method:"GET",
                      success:function(code){
                        $("body").append(code);
                      }
                    });
                  }
                </script>
              </div>
              <div class='row form-group'>
                <label class='col-auto border-bottom v1'><?php $Icons->Job(1);?> Job:</label>
                <div class='col-auto padding v1'><button type='button' onClick='selectJobs(this);'><?php
                $pass = false;
                if(isset($_GET['Job']) && is_numeric($_GET['Job'])){
                  $r = sqlsrv_query($NEI,"SELECT * FROM Job WHERE Job.ID = ?;",array($_GET['Job']));
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
                      url:"cgi-bin/php/element/ticket/selectJobs.php?Location=<?php echo $_GET['Location'];?>&Unit=<?php echo $_GET['Unit'];?>",
                      method:"GET",
                      success:function(code){
                        $("body").append(code);
                      }
                    });
                  }
                </script>
              </div>
              <div class='row form-group'>
                <label class='col-auto border-bottom v1'><?php $Icons->Blank(1);?> Level:</label>
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
              <div class='row form-group'>
                <label class='col-auto border-bottom v1'><?php $Icons->Description(1);?> Description:</label>
                <textarea style='width:100%;max-width:100%;' rows='8' name='Description'></textarea>
              </div>
            </div>
            <div class='row g-0'><div class='col-sm-12'>&nbsp;</div></div>
            <div class='row form-group'>
              <div class='col-sm-12'><button onClick='saveTicket(this);' style='width:100%;height:35px;max-width:100%;'>Save</button></div>
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
                    url:"cgi-bin/php/post/save_new_ticket.php",
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
          </div>
        </div>
      </form></div>
    </div>
  </div>
  <style>
    .ui-autocomplete {
      max-height: 100px;
      overflow-y: auto;
      overflow-x: hidden;
    }
    * html .ui-autocomplete {
      height: 100px;
    }
  </style>
  <script src="https://www.nouveauelevator.com/vendor/bootstrap/js/bootstrap.min.js"></script>
  <?php require(PROJECT_ROOT.'js/datatables.php');?>
  <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
  <script>
    $(document).ready(function(){$("input[name='Date']").datepicker();});
    function closePopup(link){$(".popup").remove();}
  </script>
</body>
</html>
<?php }
}?>
