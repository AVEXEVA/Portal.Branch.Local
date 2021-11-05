<?php
session_start( [ 'read_and_close' => true ] );
if(isset($_SESSION['User'],$_SESSION['Hash'])){
  $r = sqlsrv_query($NEI,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
  $array = sqlsrv_fetch_array($r);
  if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
    sqlsrv_query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "ticket.php?ID=New"));
    $r = sqlsrv_query($NEI,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID= ?;",array($_SESSION['User']));
    $My_User = sqlsrv_fetch_array($r);
    $Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
    $r = sqlsrv_query($Portal,"SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege FROM Privilege WHERE User_ID = ?;",array($_SESSION['User']));
    $My_Privileges = array();
    while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
    $Privileged = FALSE;
    if(isset($My_Privileges['Ticket']) && $My_Privileges['Ticket']['User_Privilege'] >= 6){$Privileged = TRUE;}
  }
  if(!isset($array['ID'])  || !$Privileged){require("401.html");}
  else {?><!DOCTYPE html>
<html lang="en">
<head>
  <title>Nouveau Texas | Portal</title>
  <?php require( bin_meta . 'index.php');?>
  <?php require( bin_css . 'index.php');?>
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
  </style>
  <?php require( bin_js . 'index.php');?>
</head>
<body onload="finishLoadingPage();" style='background-color:#1d1d1d;'>
  <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
    <?php require(PROJECT_ROOT.'php/element/navigation/index.php');?>
    <?php require( bin_php . 'element/loading.php');?>
    <div id="page-wrapper" class='content' style='<?php if(isset($_SESSION['Branch']) && $_SESSION['Branch'] == 'Customer'){?>margin:0px !important;<?php }?>'>
      <div class='panel-primary'>
        <div class='panel-heading' onClick='document.location.href="work.php";'><h4 style=''><?php $Icons->Location();?> Location: New</h4></div>
        <div class='panel-body'>
  				<div class='row'>
  					<div class='col-xs-12'>&nbsp;</div>
          </div>
          <div class='row'>
  					<div class='col-xs-4'><?php $Icons->Location(1);?> Customer:</div>
  					<div class='col-xs-8'><button type='button' onClick='selectCustomers(this);' style='width:100%;height:50px;'><?php
            $pass = false;
            if(isset($_GET['Customer']) && is_numeric($_GET['Customer'])){
              $r = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.OwnerWithRol WHERE OwnerWithRol.ID = ?;",array($_GET['Customer']));
              if($r){
                $row = sqlsrv_fetch_array($r);
                if(is_array($row)){
                  $pass = True;
                  echo $row['Name'];
                }
              }
            }
            if(!$pass){?>Select Customer<?php }?></button></div>
            <script>
              function selectCustomers(link){
                $.ajax({
                  url:"cgi-bin/php/element/location/selectCustomers.php",
                  method:"GET",
                  success:function(code){
                    $("body").append(code);
                  }
                });
              }
            </script>
          </div>
          <div class='row'>
            <div class='col-xs-12'>&nbsp;</div>
          </div>
          <div class='row'>
            <div class='col-xs-4'>ID:</div>
            <div class='col-xs-8'><input type='text' name='ID' style='width:100%;' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-4'>Tag:</div>
            <div class='col-xs-8'><input type='text' name='Tag' style='width:100%;' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-4'>Address:</div>
            <div class='col-xs-8'><input type='text' name='Address' style='width:100%;' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-4'>City:</div>
            <div class='col-xs-8'><input type='text' name='City' style='width:100%;' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-4'>State:</div>
            <div class='col-xs-8'><select name='State'>
              <option value="AL">Alabama</option>
            	<option value="AK">Alaska</option>
            	<option value="AZ">Arizona</option>
            	<option value="AR">Arkansas</option>
            	<option value="CA">California</option>
            	<option value="CO">Colorado</option>
            	<option value="CT">Connecticut</option>
            	<option value="DE">Delaware</option>
            	<option value="DC">District Of Columbia</option>
            	<option value="FL">Florida</option>
            	<option value="GA">Georgia</option>
            	<option value="HI">Hawaii</option>
            	<option value="ID">Idaho</option>
            	<option value="IL">Illinois</option>
            	<option value="IN">Indiana</option>
            	<option value="IA">Iowa</option>
            	<option value="KS">Kansas</option>
            	<option value="KY">Kentucky</option>
            	<option value="LA">Louisiana</option>
            	<option value="ME">Maine</option>
            	<option value="MD">Maryland</option>
            	<option value="MA">Massachusetts</option>
            	<option value="MI">Michigan</option>
            	<option value="MN">Minnesota</option>
            	<option value="MS">Mississippi</option>
            	<option value="MO">Missouri</option>
            	<option value="MT">Montana</option>
            	<option value="NE">Nebraska</option>
            	<option value="NV">Nevada</option>
            	<option value="NH">New Hampshire</option>
            	<option value="NJ">New Jersey</option>
            	<option value="NM">New Mexico</option>
            	<option value="NY">New York</option>
            	<option value="NC">North Carolina</option>
            	<option value="ND">North Dakota</option>
            	<option value="OH">Ohio</option>
            	<option value="OK">Oklahoma</option>
            	<option value="OR">Oregon</option>
            	<option value="PA">Pennsylvania</option>
            	<option value="RI">Rhode Island</option>
            	<option value="SC">South Carolina</option>
            	<option value="SD">South Dakota</option>
            	<option value="TN">Tennessee</option>
            	<option value="TX">Texas</option>
            	<option value="UT">Utah</option>
            	<option value="VT">Vermont</option>
            	<option value="VA">Virginia</option>
            	<option value="WA">Washington</option>
            	<option value="WV">West Virginia</option>
            	<option value="WI">Wisconsin</option>
            	<option value="WY">Wyoming</option>
            </select></div>
          </div>
          <div class='row'>
            <div class='col-xs-4'>Zip:</div>
            <div class='col-xs-8'><input type='text' name='Zip' style='width:100%;' /></div>
          </div>
        </div>
        <div class='panel-body'>
          <div class='row'>
            <div class='col-xs-12'>&nbsp;</div>
          </div>
  				<div class='row'>
  					<div class='col-xs-12'><button onClick='saveLocation(this);' style='width:100%;height:50px;' >Save</button></div>
            <script>
            function saveLocation(link){
              var locationData = new FormData();
              locationData.append('Customer','<?php echo isset($_GET['Customer']) ? $_GET['Customer'] : '';?>');
              locationData.append('Tag',$("input[name='Tag']").val());
              locationData.append('Address',$("input[name='Address']").val());
              locationData.append('City',$("input[name='City']").val());
              locationData.append('State',$("select[name='State']").val());
              locationData.append('Zip',$("input[name='Zip']").val());
              if(     locationData.get('Customer') == ''
                  ||  locationData.get('Tag') == ''
                  ||  locationData.get('Address') == ''
                  ||  locationData.get('City') == ''
                  ||  locationData.get('State') == ''
                  ||  locationData.get('Zip') == ''){
                alert('Please fill out the necessasry information in order to continue.');
              } else {
                $.ajax({
                  url:"cgi-bin/php/post/save_new_location.php",
                  cache: false,
                  processData: false,
                  contentType: false,
                  method:"POST",
                  data: locationData,
                  success:function(code){document.location.href='location.php?ID=' + code;}
                });
              }
            }
            </script>
  				</div>
          <div class='row'>
            <div class='col-xs-12'>&nbsp;</div>
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
