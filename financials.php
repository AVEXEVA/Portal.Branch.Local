<?php 
session_start( [ 'read_and_close' => true ] );
require('cgi-bin/php/index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"
		SELECT * 
		FROM   Connection 
		WHERE  Connection.Connector = ? 
		       AND Connection.Hash  = ?
	;",array($_SESSION['User'],$_SESSION['Hash']));
    $My_Connection = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $r = sqlsrv_query($NEI,"
		SELECT *,
		       Emp.fFirst AS First_Name,
			   Emp.Last   AS Last_Name
		FROM   Emp 
		WHERE  Emp.ID = ?
	;",array($_SESSION['User']));
    $My_User = sqlsrv_fetch_array($r);
	$r = sqlsrv_query($NEI,"
		SELECT * 
		FROM   Privilege 
		WHERE  Privilege.User_ID = ?
	;",array($_SESSION['User']));
	$My_Privileges = array();
	if($r){while($My_Privilege = sqlsrv_fetch_array($r)){$My_Privileges[$My_Privilege['Access_Table']] = $My_Privilege;}}
    if(	!isset($My_Connection['ID']) 
	   	|| !isset($My_Privileges['Financials'])
	  		|| $My_Privileges['Financials']['User_Privilege']  < 4
	  		|| $My_Privileges['Financials']['Group_Privilege'] < 4
	  		|| $My_Privileges['Financials']['Other_Privilege'] < 4){
				?><?php require('../404.html');?><?php }
    else {
		sqlsrv_query($NEI,"
			INSERT INTO Portal.dbo.Activity([User], [Date], [Page]) 
			VALUES(?,?,?)
		;",array($_SESSION['User'],date("Y-m-d H:i:s"), "financials.php"));
        if(count($_POST) > 0){
            if(isset($_POST['Type'])){
                if(strlen($_POST['Start_Date']) > 0){$_POST['Start_Date'] = date_format(date_create_from_format('m/d/Y',$_POST['Start_Date']),'Y-m-d 00:00:00.000');}
                if(strlen($_POST['End_Date']) > 0){$_POST['End_Date'] = date_format(date_create_from_format('m/d/Y',$_POST['End_Date']),'Y-m-d 00:00:00.000');}
                $_POST['Company'] = intval($_POST['Company']);
                $r = sqlsrv_query($Portal,"SELECT * FROM Insurance WHERE Company=? AND Type = ?",array($_POST['Company'],$_POST['Type']));
                if($r && sqlsrv_fetch_array($r)){
                    sqlsrv_query($Portal,"UPDATE Insurance SET Start_Date = ?, End_Date = ? WHERE Company = ? AND Type = ?",array($_POST['Start_Date'],$_POST['End_Date'],$_POST['Company'],$_POST['Type']));
                } else {
                    sqlsrv_query($Portal,"INSERT INTO Insurance(Company, Start_Date, End_Date, Type) VALUES(?,?,?,?);",array($_POST['Company'],$_POST['Start_Date'],$_POST['End_Date'],$_POST['Type']));
                }
            } elseif(isset($_POST['Company_Name'])){
                sqlsrv_query($Portal,"INSERT INTO Insured_Company(Company) VALUES(?)",array($_POST['Company_Name']));
            }
        }?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Peter D. Speranza">    <title>Nouveau Texas | Portal</title>    
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>
</head>
<body>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require( bin_php . 'element/navigation/index.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content'>
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-primary">
                        <div class='panel-heading'><h4>
                            <div style='display:inline-block;'>
                                <span onClick="document.location.href='purchasing.php'" style='cursor:pointer;'><?php $Icons->Unit();?>Customized Finances</span>
                                <span class='hidden' onClick="modernizationTracker('modernization_equipment');" style='cursor:pointer;'><span id='modernization_equipment'> > Equipment Entity</span></span>
                            </div>
                            <div style='clear:both;'></div>
                        </h4></div>
                        <div class="panel-body" id='content'>
                        	<div class='filters' class='no-print'><button onClick="resetSession();">Reset Session</button></div>
                        	<hr />
                        	<div id='tables'>
                        	<style>
                        	.topHeader {
                        		background-color:#57efac;
                        	}
                        	table td, table th {
                        		padding:5px;
                        	}
                        	</style>
                        		<script>
                        		function resetSession(){
                        			$.ajax({
                        				url:"cgi-bin/php/post/resetSessionFinancials.php",
                        				method:"POST",
                        				success:function(){
                        					$("div#subcontent").html("");
                        					$("table#filters tbody").html("");
                                  $("select[Name='Location'] option[value='']").attr('selected','selected');
                                  $("select[Name='JobType'] option[value='']").attr('selected','selected');
                                  $("select[Name='Job'] option[value='']").attr('selected','selected');
                                  $("select[Name='Unit'] option[value='']").attr('selected','selected');
                        				}
                        			});
                        		}
                        		function selectCustomer(link){
                        			var Customer = $(link).val();
                        			if(Customer != ""){
	                        			$("input[name='Location'] option").hide();
	                        			$("input[name='Location'] option[customer='" + Customer + "']").show();
	                        		} else {
	                        			$("input[name='Location'] option").show(); 
	                        			$("select[name='Job'] option").show();
                        				$("select[name='Unit'] option").show();
	                        		}
                              lookupLocations(link);
                        		}
                            function lookupJobs(link){
                                $.ajax({
                                    method:"GET",
                                    url:"cgi-bin/php/element/select_Job_by_Location.php?ID=" + $(link).val(),
                                    success:function(code){$("th#Jobs").html(code);}
                                });
                                $.ajax({
                                    method:"GET",
                                    url:"cgi-bin/php/element/select_Unit_by_Location.php?ID=" + $(link).val(),
                                    success:function(code){$("th#Units").html(code);}
                                });
                            }
                            function lookupLocations(link){
                              $.ajax({
                                  method:"GET",
                                  url:"cgi-bin/php/element/select_Location_by_Customer.php?ID=" + $(link).val(),
                                  success:function(code){
                                      $("th#Locations").html(code);
                                  }
                              })
                            }
                        		function selectLocation(link){
                        			var Location = $(link).val();
                        			if(Location != ""){
                        				$("select[name='Job'] option").hide();
	                        			$("select[name='Job'] option[location='" + Location + "']").show();
	                        			$("select[name='Unit'] option").hide();
	                        			$("select[name='Unit'] option[location='" + Location + "']").show();	
                        			} else {
                        				$("select[name='Job'] option").show();
                        				$("select[name='Unit'] option").show();
                        			}
                        			
                        		}
                        		function selectJobType(link){
                        			var JobType = $(link).val();
                        			if(JobType != ""){
	                        			$("select[name='Job'] option").hide();
	                        			$("select[name='Job'] option[type='" + JobType + "']").show();
	                        		} else {
	                        			$("select[name='Job'] option").show();
	                        		}
                        		}
                        		function selectJob(link){
                        			var Job = $(link).val();
                        			if(Job != ""){
	                        			$("select[name='Unit'] option").hide();
	                        			$("select[name='Unit'] option[jobs*='" + Job + "']").show();
	                        		} else {
	                        			$("select[name='Unit'] option").show();
	                        		}
                        		}
                        		function selectUnit(link){
                        			var Unit = $(link).val();
                        		}
                        		function addFilter(link){
                        			var Operator = $("select[name='Operator']").val();
                        			var Customer = $("select[name='Customer']").val();
                        			var Location = $("input[name='Location']").val();
                        			var JobType  = $("select[name='JobType']").val();
                        			var Job      = $("select[name='Job']").val();
                        			var Unit     = $("select[name='Unit']").val();     	
                        			var formdata = "Operator=" + Operator + "&Customer=" + Customer + "&Location=" + Location + "&JobType=" + JobType + "&Job=" + Job + "&Unit=" + Unit;
                        			$.ajax({
                        				url:"cgi-bin/php/post/financials.php",
                        				metohd:"POST",
                        				data:formdata,
                        				success:function(code){
                        					var Operator = $("select[name='Operator'] option:selected").text();
		                        			var Customer = $("select[name='Customer'] option:selected").text();
		                        			var Location = $("input[name='Location'] option:selected").text();
		                        			var JobType  = $("select[name='JobType']  option:selected").text();
		                        			var Job      = $("select[name='Job']      option:selected").text();
		                        			var Unit     = $("select[name='Unit']     option:selected").text();
		                        			$("table#filters tbody").append("<tr><td>" + Operator + "</td><td>" + Customer + "</td><td>" + Location + "</td><td>" + JobType + "</td><td>" + Job + "</td><td>" + Unit + "</td></tr>");
                        					$("div#subcontent").html(code);}
                        			});
                        		}	
                        		</script>
                        		<table id='filters' class='no-print'>
                        			<thead>
                        				<tr class='topHeader'>
                        					<th></th>
                        					<th>Customer</th>
                        					<th>Location</th>
                        					<th>Job Type</th>
                        					<th>Job</th> 
                        					<th>Unit</th>
                        					<th></th>
                        				</tr>
                        				<tr>
                        					<th><select name="Operator"><option value="Add">Add</option><option value="Subtract">Subtract</option></select></th>
                   							<th><select name='Customer' style='width:300px;' onchange='selectCustomer(this);'>
                   								<option value=''>Select</option>
                   								<?php 
                   									$r = sqlsrv_query($NEI,"SELECT * FROM OwnerWithRol ORDER BY Name");
                   									if($r){
                   										while($array = sqlsrv_fetch_array($r)){
                   											?><option value='<?php echo $array['ID'];?>'><?php echo $array['Name'];?></option><?php
                   										}
                   									}
                   								?></select></th>
                   							<th id='Locations'>
                                  <input id='Locations' placeholder='Location' type='text' />
                                  <input id='Location' name='Location' type='hidden' />
                                </th>
                   							<th><select name='JobType' style='width:150px;' onchange='selectJobType(this);'>
                   								<option value='' selected='selceted'>Select</option>
                   								<?php 
                   									$r = sqlsrv_query($NEI,"SELECT * FROM JobType ORDER BY Type");
                   									if($r){
                   										while($array = sqlsrv_fetch_array($r)){
                   											?><option class='option' value='<?php echo $array['ID'];?>'><?php echo $array['Type'];?></option><?php
                   										}
                   									}
                   								?></select></th>
                   							<th id='Jobs'><select name='Job' style='width:300px;' onchange='selectJob(this);'>
                   								<option value=''>Select</option>
                   								<?php 
                   									$r = sqlsrv_query($NEI,"SELECT * FROM Job ORDER BY Job.fDesc");
                   									if($r){
                   										while($array = sqlsrv_fetch_array($r)){
                   											?><option class='option' location='<?php echo $array['Loc'];?>' type='<?php echo $array['Type'];?> value='<?php echo $array['ID'];?>'><?php echo $array['fDesc'];?></option><?php
                   										}
                   									}
                   								?></select></th>
                   							<th id='Units'><select name='Unit' style='width:150px;' onchange='selectCustomer(this);'>
                   								<option value=''>Select</option>
                   								<?php 
                   									$r = sqlsrv_query($NEI,"SELECT * FROM Elev ORDER BY Elev.State");
                   									if($r){
                   										while($array = sqlsrv_fetch_array($r)){
                   											?><option class='option' location='<?php echo $array['Loc'];?>' value='<?php echo $array['ID'];?>'><?php echo strlen($array['State'] > 0) ? $array['State'] : $array['Unit'];?></option><?php
                   										}
                   									}
                   								?></select></th>
                   								<th><button onClick="addFilter();">Submit</button></th>
                   						</tr>
                        			</thead>
                        			<thead><tr><th colspan='6'><hr /></th></tr></thead>
                        			<tbody>
                        			</tbody>
                        		</table>
                        	</div>
                          <hr />
                          <h3>Financials</h3>
                        	<div id='subcontent'></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- /#wrapper -->


    <!-- Bootstrap Core JavaScript -->
    

    <!-- Metis Menu Plugin JavaScript -->
        

    <?php require('cgi-bin/js/datatables.php');?>
    
    <!-- Custom Theme JavaScript -->
    

    <!--Moment JS Date Formatter-->
    

    <!-- JQUERY UI Javascript -->
    

    <script src="https://www.nouveauelevator.com/vendor/flot/excanvas.min.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/flot/jquery.flot.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/flot/jquery.flot.pie.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/flot/jquery.flot.resize.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/flot/jquery.flot.time.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/flot/jquery.flot.symbol.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/flot/jquery.flot.axislabels.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/flot-tooltip/jquery.flot.tooltip.min.js"></script>
    
    <script>
    $(document).ready(function(){
    	finishLoadingPage();
    });
    </script>
    <script>
      var availableLocations = [<?php 
        $r = sqlsrv_query($NEI,"SELECT Loc.Loc AS ID, Loc.Tag as Name FROM Loc ORDER BY Loc.Tag ASC");
        $Locations = array();
        if($r){while($Location = sqlsrv_fetch_array($r)){$Locations[$Location['ID']] = $Location['Name'];}}
        $data = array();
        if(count($Locations) > 0){foreach($Locations as $id=>$name){
          $name = str_replace("'","",$name);
          $data[] = '{value:' . '"'. $id . '"' . ', label:' . '"' . $name . '"' . '}';
        }}
        if(count($data) > 0){echo implode(",",$data);}
        ?>
      ];
      $(document).ready(function(){
        $("input#Locations").autocomplete({
          minLength: 3,
          source: availableLocations,
          focus: function( event, ui ) {
            $("input#Locations").val( ui.item.label );
            return false;
          },
          select: function( event, ui ) {
            $("input#Locations").val( ui.item.label );
            $("input#Location").val( ui.item.value );
            return false;
          }
        })
        .autocomplete( "instance" )._renderItem = function( ul, item ) {
          return $( "<li>" )
            .append( "<div>" + item.label + "</div>" )
            .appendTo( ul );
        };
      });
    </script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=modernizations.php';</script></head></html><?php }?>