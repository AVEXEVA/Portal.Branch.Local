<?php
session_start( [ 'read_and_close' => true ] );
require('bin/php/index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"
		SELECT *
		FROM   Connection
		WHERE  Connection.Connector = ?
		       AND Connection.Hash  = ?
	;",array($_SESSION['User'],$_SESSION['Hash']));
    $Connection = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $r = $database->query(null,"
		SELECT *,
		       Emp.fFirst AS First_Name,
			   Emp.Last   AS Last_Name
		FROM   Emp
		WHERE  Emp.ID = ?
	;",array($_SESSION['User']));
    $User = sqlsrv_fetch_array($r);
	$r = $database->query('Portal',"
		SELECT *
		FROM   Privilege
		WHERE  Privilege.User_ID = ?
	;",array($_SESSION['User']));
	$Privileges = array();
	if($r){while($Privilege = sqlsrv_fetch_array($r)){$Privileges[$Privilege['Access']] = $Privilege;}}
    if(	!isset($Connection['ID']) ){?><?php require('404.html');?><?php }
    else {
		$database->query(null,"
			INSERT INTO Portal.dbo.Activity([User], [Date], [Page])
			VALUES(?,?,?)
		;",array($_SESSION['User'],date("Y-m-d H:i:s"), "user.php"));
$Mechanic = is_numeric($_SESSION['User']) ? $_SESSION['User'] : -1;
if(!isset($_GET['ID'])){$_GET['ID'] = $_SESSION['User'];$ASDF=FALSE;				}
else {$ASDF=TRUE;}
if($Mechanic > 0){
    $Call_Sign = "";
    $r = $database->query(null,"
        SELECT
            Emp.*,
            Emp.Last as Last_Name,
            Emp.Last AS Last,
            Rol.*,
            PRWage.Reg as Wage_Regular,
            PRWage.OT1 as Wage_Overtime,
            PRWage.OT2 as Wage_Double_Time
        FROM
            (Emp LEFT JOIN PRWage ON Emp.WageCat = PRWage.ID)
            LEFT JOIN Rol ON Emp.Rol = Rol.ID
        WHERE Emp.ID = ?;",array($_GET['ID']));
    $User = sqlsrv_fetch_array($r);
	$r = $database->query(null,"SELECT Email FROM Portal.dbo.Portal WHERE Portal.Branch_ID = ? AND Portal.Branch = 'Nouveau Texas';",array($_GET['ID']));
	$Email = sqlsrv_fetch_array($r)['Email'];
    while($a= sqlsrv_fetch_array($r)){}
}?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Nouveau Texas | Portal</title>
    <?php require('bin/css/index.php');?>
    <?php require('bin/js/index.php');?>
    <style>
    label.file-upload{
      position:relative;
      overflow:hidden}
    label.file-upload input[type=file]{
      position:absolute;
      top:0;
      right:0;
      min-width:100%;
      min-height:100%;
      font-size:100px;
      text-align:right;
      filter:alpha(opacity=0);
      opacity:0;
      outline:0;
      background:#fff;
    }
    </style>
</head>
<body>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>" style='overflow-x:hidden;'>
        <?php require(PROJECT_ROOT.'php/element/navigation.php');?>
        <div id="page-wrapper">
				<script>
					function ($) {
						'use strict';
						var FileUpload = function (element) {
							this.element = $(element);
							var defaultText = this.element.text();
							var label = this.element.text();
							var input = $('input', this.element);
							this.element.text('');
							this.element.append('<span class="file-upload-text"></span>');
							$('.file-upload-text', this.element).text(label);
							this.element.append(input);
							this.element.on('change', ':file', function() {
								var input = $(this);
								if (input.val()) {
									var label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
									$('.file-upload-text', $(this).parent('label')).text(label);
								}
								else {
									$('.file-upload-text', $(this).parent('label')).text(defaultText);
								}
							});
						};
						function Plugin() {
							return this.each(function () {
								var $this = $(this);
								var data  = $this.data('bs.file-upload');
								if (!data) {
									$this.data('bs.file_upload', (data = new FileUpload(this)));
								}
							});
						}
						var old = $.fn.file_upload;
						$.fn.file_upload = Plugin;
						$.fn.file_upload.Constructor = FileUpload;
						$.fn.file_upload.noConflict = function () {
							$.fn.file_upload = old;
							return this;
						};
					}(jQuery);
				</script>
				<?php
				if(file_exists("bin/uploads/" . $_GET['ID'] . ".jpg") || file_exists("bin/uploads/" . $_GET['ID'] . ".png") || file_exists("bin/uploads/" . $_GET['ID'] . ".jpeg")) {
					if(file_exists("bin/uploads/" . $_GET['ID'] . ".jpg")) {
					$fildir = "bin/uploads/" . $_GET['ID'] . ".jpg";}
					else if(file_exists("bin/uploads/" . $_GET['ID'] . ".png")){$fildir = "bin/uploads/" . $_GET['ID'] . ".png";}
					else{$fildir = "bin/uploads/" . $_GET['ID'] . ".jpeg";}
				} else if($_SESSION['User'] == $_GET['ID']){?>
				<script type="text/javascript">
					$(document).ready(function() {
						$('.file-upload').file_upload();
					});
				</script>
			<!--<div class="row">
				<div class="panel panel-primary">
					<div class='panel-heading'> Hi <?php echo proper($User['fFirst']) ?> !  Upload a Profile Picture!</div>
					<br>
				</div>
				<form class="form-horizontal" enctype="multipart/form-data" action="uploader.php" method="POST" style="margin-left: 0px !important">
					<div class="form-group">
						<div class="col-sm-offset-1 col-sm-10">
							<label class="file-upload btn btn-primary">
								<input type="hidden" accept='image/*' name="MAX_FILE_SIZE" value="150000" id="input-b2" name="input-b2" />
								Choose a file to upload: <input name="uploadedfile" type="file" /><br />
							</label>
							<input type="submit" value="Upload File" />
						</div>
					</div>
				</form>
				<div class="panel panel-default">
					<ul>
						<div class="panel-heading"><strong>Rules for Picture Submission</strong></div>
						<li><p>Picture should be of the format .jpg or .png only.</p></li>
						<li><p>Picture should not be more than 150kb.</p></li>
						<li><p>Picture should be of you. If there are multple people, you should be clearly distinguishable.</p></li>
					</ul>
				</div>
			</div>-->
			<?php } ?>
			<div class='panel panel-primary'>
				<div class='panel-heading'><?php echo proper($User['fFirst'] . " " . $User['Last_Name']);?>'s Information</div>
				<!--<div class='panel-body' >
					<?php
					if(file_exists("bin/uploads/" . $_GET['ID'] . ".jpg") || file_exists("bin/uploads/" . $_GET['ID'] . ".png") || file_exists("bin/uploads/" . $_GET['ID'] . ".jpeg")) {
  					if(file_exists("bin/uploads/" . $_GET['ID'] . ".jpg")) {
  					$fildir = "bin/uploads/" . $_GET['ID'] . ".jpg";}
  					else if(file_exists("bin/uploads/" . $_GET['ID'] . ".png")){$fildir = "bin/uploads/" . $_GET['ID'] . ".png";}
  					else{$fildir = "bin/uploads/" . $_GET['ID'] . ".jpeg";}?>
  					 <div class="img" align="center">
               <div class='col-md-12 col-xs-12'>
                 <img style="display: inline-block;<?php if(isMobile()) { ?>  width: 100px;height: 100px; border-radius: 50px;<?php } if(!isMobile()) { ?> width: 200px; height 300px;<?php } ?> background-repeat: no-repeat; background-position: center center;background-size: cover;" src= <?php echo $fildir; ?> alt="<?php echo $User['Name'];?>">
    					 </div>
  					</div>
					<?php } ?>
					<?php if(file_exists("bin/uploads/" . $_GET['ID'] . ".jpg")
                  || file_exists("bin/uploads/" . $_GET['ID'] . ".png")
                  || file_exists("bin/uploads/" . $_GET['ID'] . ".jpeg")
                  && $_SESSION['User'] == $User['ID'])  {?>
						<div class="container">
							<form enctype="multipart/form-data" action="uploader.php" method="POST" style="margin-left: 0px !important">
								<script type="text/javascript">$(document).ready(function() {$('.file-upload').file_upload();});</script>
								<div class="form-group">
									<div class="col-sm-offset-4 col-sm-4">
										<label class="file-upload btn btn-primary">
											<input type="hidden" accept='image/*' name="MAX_FILE_SIZE" value="150000" id="input-b2" name="input-b2" />
											Change Picture <input name="uploadedfile" type="file" /><br />
										</label>
										<input type="submit" id="input-b2" name="input-b2" value="Upload File" />
										<button type="button" onClick="location.href='uploader.php'" class="btn btn-primary">Remove Picture</button>
									</div>
								</div>
							</form>
						</div>
					<?php } ?>
				</div>-->
				<!--<div class='panel-heading'>&nbsp;</div>-->
				<div class='panel-body' style='padding:25px;'>
					<div class='row'>
						<div class='col-md-2 col-xs-4'><b>Name</b></div>
						<div class='col-md-10 col-xs-8'><?php echo proper($User['Last_Name']);?>, <?php echo proper($User['fFirst']);?> <?php echo proper($User['Middle']);?></div>
					</div>
					<?php if($_SESSION['User'] == $_GET['ID']){?><div class='row'>
						<div class='col-md-2 col-xs-4'><b>Birthdate</b></div>
						<div class='col-md-10 col-xs-8'><?php echo substr($User['DBirth'],5,2) . "/" . substr($User['DBirth'],8,2) . "/" . substr($User['DBirth'],0,4);?></div>
					</div><?php }?>
					<div class='row'>
						<div class='col-md-2 col-xs-4'><b>Email</b></div>
						<div class='col-md-10 col-xs-8'><a style='color:white !important;' href="mailto:<?php echo $Email;?>"><?php echo strlen($Email) > 1 ? $Email : "&nbsp;";?></a></div>
					</div>
					<?php if($_SESSION['User'] == $_GET['ID']){?><div class='row'>
						<div class='col-md-2 col-xs-4'><b>Phone</b></div>
						<div class='col-md-10 col-xs-8'><?php echo strlen($User['Phone']) > 1 ? $User['Phone'] : "Unlisted";?></div>
					</div><?php }?>
					<?php if($_SESSION['User'] == $_GET['ID']){?><div class='row'>
						<div class='col-md-2 col-xs-4'><b>Address</b></div>
						<div class='col-md-10 col-xs-8'><?php echo proper($User['Address']);?></div>
					</div>
					<div class='row'>
						<div class='col-md-2 col-xs-4'>
							<b>City</b>
						</div>
						<div class='col-md-10 col-xs-8'><?php echo proper($User['City']); ?></div>
					</div>
					<div class='row'>
						<div class='col-xs-4 col-md-2'>
							<b>State</b>
						</div>
						<div class='col-xs-8 col-md-10'><?php echo $User['State']?></div>
					</div>
					<div class='row'>
						<div class='col-md-2 col-xs-4'><b>Zip</b></div>
						<div class='col-md-10 col-xs-8'><?php echo $User['Zip']?></div>
					</div><?php }?>
					<div class='row'>
						<div class='col-md-2 col-xs-4'><b>Call Sign</b></div>
						<div class='col-md-10 col-xs-8'><?php echo strlen($User['CallSign']) > 1 ? $User['CallSign'] : "Unlisted";?></div>
					</div>
					<?php if($_SESSION['User'] == $_GET['ID']){?><div class='row'>
						<div class='col-md-2 col-xs-4'><b>Hired</b></div>
						<div class='col-md-10 col-xs-8'><?php echo substr($User['DHired'],5,2) . "/" . substr($User['DHired'],8,2) . "/" . substr($User['DHired'],0,4);?></div>
					</div><?php }?>
					<?php if($_SESSION['User'] == $_GET['ID']){?><div class='row'>
						<div class='col-md-2 col-xs-4'><b>Title</b></div>
						<div class='col-md-10 col-xs-8'><?php echo proper($User['Title']);?></div>
					</div><?php }?>
					<?php if($_SESSION['User'] == $_GET['ID']){?><div class='row'>
						<div class='col-md-2 col-xs-4'><b>Wage</b></div>
						<div class='col-md-10 col-xs-8'>$<?php echo money_format('%i',$User['Wage_Regular']);?></div>
					</div>
					<div class='row'>
						<div class='col-md-2 col-xs-4'><b>Overtime</b></div>
						<div class='col-md-10 col-xs-8'>$<?php echo money_format('%i',$User['Wage_Overtime']);?></div>
					</div>
					<div class='row'>
						<div class='col-md-2 col-xs-4'><b>Doubletime</b></div>
						<div class='col-md-10 col-xs-8'>$<?php echo money_format('%i',$User['Wage_Double_Time']);?></div>
					</div><?php }?>
				</div>
				<?php
					$serverName = "172.16.12.45";
					nullectionOptions = array(
						"Database" => "ATTENDANCE",
						"Uid" => "sa",
						"PWD" => "SQLABC!23456",
						'ReturnDatesAsStrings'=>true
					);
					//Establishes the connection
					$c2 = sqlsrv_connect($serverName, nullectionOptions);
					$r = $database->query($c2,"select * from Employee where EmpID='" .$User['Ref'] . "'");
					$Attendance = sqlsrv_fetch_array($r);
					while($temp = sqlsrv_fetch_array($r));
				?>
        <?php if($_SESSION['User'] == 895){?><div class='panel-heading'>Skillset</div>
        <div class='panel-body'>
          <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
          <div class='row'>
            <div class='col-xs-1'>&nbsp;</div>
            <div class='col-xs-5'><select name='Skill'>
              <option value=''>Select</option>
              <?php
                $r = $database->query($Portal,
                  " SELECT    *
                    FROM      Portal.dbo.Skill
                    ORDER BY  Skill.Name ASC;");
                if($r){while($row = sqlsrv_fetch_array($r)){
                  ?><option value='<?php echo $row['ID'];?>'><?php echo $row['Name'];?></option><?php
                }}
              ?>
            </select></div>
            <div class='col-xs-4'><select name='Skill'>
              <option value=''>Select</option>
              <?php
                $r = $database->query($Portal,
                  " SELECT    *
                    FROM      Portal.dbo.Proficiency
                    ORDER BY  Proficiency.ID ASC;");
                if($r){while($row = sqlsrv_fetch_array($r)){
                  ?><option value='<?php echo $row['ID'];?>'><?php echo $row['Name'];?></option><?php
                }}
              ?>
            </select></div>
            <div class='col-xs-2'><button onClick='addSkillset(this);' style='width:100%;'>Add</button></div>
          </div>
          <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
          <?php
            $r = $database->query($Portal,
              " SELECT  Skill.Name AS Skill,
                        Proficiency.Name AS Proficiency
                FROM    Portal.dbo.Skillset
                        LEFT JOIN Portal.dbo.Skill ON Skillset.Skill = Skill.ID
                        LEFT JOIN Portal.dbo.Proficiency ON Skillset.Proficiency = Proficiency.ID
                WHERE   Skillset.[User] = ?
              ;",array($_SESSION['User']));
            $i = 1;
            if($r){while($row = sqlsrv_fetch_array($r)){
              ?><div class='row'>
                <div class='col-xs-1'><?php echo $i;?></div>
                <div class='col-xs-5'><?php echo $row['Skill'];?></div>
                <div class='col-xs-4'><?php echo $row['Proficiency'];?></div>
                <div class='col-xs-2'><button onClick='editSkillset(this);' style='width:100%;'>Edit</button></div>
              </div><?php
              $i++;
            }}
          ?>
          <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
        </div><?php }?>
				<?php if($_SESSION['User'] == $_GET['ID']){?><div class='panel-heading'>Time Paid Off</div>
				<div class='panel-body'>
					<div class='row'>
						<div class='col-lg-3'>
							<?php if(strlen($Attendance['UnionDate']) > 1){?>
								<table spacing='3' style='width:100%;'>
									<thead>
										<th></th>
										<th style='text-align:center;'><b>Available</b></th>
										<th style='text-align:center;'><b>Allowed</b></th>
									</thead>
									<tbody style='color:white !important;'>
                    <tr><td style='color:white !important;padding:5px;'><b>Sick Days</b></td><td style='text-align:center;color:white !important;padding:5px;'><?php echo $Attendance['SickAvail'];?></td><td style='text-align:center;color:white !important;padding:5px;'><?php echo $Attendance['SickAllowed'];?></td></tr>
                    <tr><td style='color:white !important;padding:5px;'><b>Vacation Days</b></td><td style='text-align:center;color:white !important;padding:5px;'><?php echo $Attendance['VacAvail'];?></td><td style='text-align:center;color:white !important;padding:5px;'><?php echo $Attendance['VacAllowed'];?></td></tr>
                    <tr><td style='color:white !important;padding:5px;'><b>Medical Days</b></td><td style='text-align:center;color:white !important;padding:5px;'><?php echo $Attendance['MedAvail'];?></td><td style='text-align:center;color:white !important;padding:5px;'><?php echo $Attendance['MedicalDayAllowed'];?></td></tr>
                    <tr><td style='color:white !important;padding:5px;'><b>Lieu Days</b></td><td style='text-align:center;color:white !important;padding:5px;'><?php echo $Attendance['LieuAvail'];?></td><td style='text-align:center;color:white !important;padding:5px;'><?php echo $Attendance['LieuDayAllowed'];?></td></tr>
									</tbody>
								</table><?php } else {?>
								<table spacing='3' style='width:100%;'>
									<thead>
										<th></th>
										<th style='text-align:center;'>Available</th>
										<th style='text-align:center;'>Allowed</th>
									</thead>
									<tbody style='color:white !important;'>
										<tr><td><b>Hours</b></td><td style='text-align:center;'><?php echo $Attendance['NONUNIONHoursAvail'];?></td><td style='text-align:center;'><?php echo $Attendance['NONUNIONHoursAllowed'];?></td></tr>
									</tbody>
								</table>
								<?php }?>
							</div>
						</div>
					</div>
          <div class='panel-body'>
            <div class='row'>
              <div class='col-xs-12'>&nbsp;</div>
              <div class='col-xs-12'>&nbsp;</div>
              <div class='col-xs-12'>&nbsp;</div>
            </div>
          </div>
          <?php }?>
                <?php if(!$ASDF || (isset($Privileges['Time']) && $_SESSION['ID'] != $_GET['ID'] && $Privileges['Time']['Other'] >= 4)){?>
                <div class='panel-heading'>Attendance</div>
                <div class='panel-body'>
                  <?php
                      require("bin/php/class/calendar.php");
                      $calendar = new Calendar();
                      echo $calendar->show();
                    ?>
                  <script>
                  $(document).ready(function(){
                    var width = ($("div#calendar").width() / 7) - 10;
                    $("div#calendar ul.dates li").each(function(){$(this).css("width",width + "px");});
                    $("div#calendar ul.label li").each(function(){$(this).css("width",width + "px");});
                    <?php
                    $_GET['month'] = !isset($_GET['month']) ? date("m") : $_GET['month'];
                    $_GET['year'] = !isset($_GET['year']) ? date("Y") : $_GET['year'];
                    $user = isset($_GET['ID']) ? $_GET['ID'] : $_SESSION['User'];
                    if(isset($_GET['month'],$_GET['year'])){
                      $prefix = $_GET['year'] . "-" . $_GET['month'];
                      $max = cal_days_in_month(CAL_GREGORIAN, $_GET['month'], $_GET['year']);
                      $i = 0;
                      while($i < $max){
                        $i++;
                        $day = $i < 10 ? '0' . $i : $i;
                        $tomorrow = $i + 1 < 10 ? '0' . ($i + 1) : $i + 1;
                        ?>$("#li-<?php echo $_GET['year'];?>-<?php echo $_GET['month'];?>-<?php echo $day;?>").css('background-color','<?php
                          $attendance = $database->query($Portal,"SELECT * FROM Attendance WHERE Attendance.[Start] >= ? AND Attendance.[Start] < ? AND Attendance.[User] = ?;",array("{$prefix}-{$day} 00:00:00.000",date("Y-m-d 00:00:00.000",strtotime('tomorrow',strtotime("{$prefix}-{$day} 00:00:00.000"))),$user));
                          $unavailable = $database->query(null,"SELECT * FROM nei.dbo.Unavailable LEFT JOIN Emp ON Unavailable.Worker = Emp.fWork WHERE Unavailable.fDate = ? AND Emp.ID = ?;",array($prefix . "-" . $day, $user));
                          if($attendance && is_array(sqlsrv_fetch_array($attendance))){echo "green";}
                          elseif($unavailable && is_array(sqlsrv_fetch_array($unavailable))) {echo "red";}
                          else {echo "white";}
                        ?>');<?php
                      }
                    }?>
                  });
                  </script>
                </div>
                <div class='panel-heading'>Attendance</div>
                <div class='panel-body'>
                  <table id='attendance' style='color:white !important;font-size:10px;'>
                    <thead><tr>
                      <th>Check In</th>
                      <th>Check Out</th>
                      <th>Total</th>
                    </tr></thead>
                  </table>
                  <script>
                  var table = $('#attendance').DataTable( {
                      "ajax": {
                          "url":"bin/php/get/attendance.php?User=<?php echo $_GET['ID'];?>",
                          "dataSrc":function(json){if(!json.data){json.data = [];}return json.data;}
                      },
                      "columns": [
                          { "data": "Start"},
                          { "data": "End"},
                          { "data": "Total"}
                      ],
                      "order": [[0, 'desc']],
                      "language":{"loadingRecords":""},
                      "initComplete":function(){finishLoadingPage();}
                  } );
                  </script>
              </div><?php }?>
                <?php
                if(isset($Privileges['Admin']['Other'])){
                	?><div class='panel panel-primary'>
                		<div class='panel-heading'>Privileges</div>
                		<div class='panel-body'>
                			<table id='Privileges_Table' class='display' cellspacing='0' width='95%'>
                                <thead>
                                	<tr>
	                                    <th title="Access Table">Access Table</th>
	                                    <th title="User Privilege">User Privilege</th>
	                                    <th title="Group Privilege">Group Privilege</th>
	                                    <th title="Other Privilege">Other Privilege</th>
	                                </tr>
                                </thead>
                            </table>
                            <script>
						        $(document).ready(function() {
						            var table = $('#Privileges_Table').DataTable( {
						                "ajax": {
						                    "url":"bin/php/get/Privilege.php?ID=<?php echo $_GET['ID'];?>",
						                    "dataSrc":function(json){if(!json.data){json.data = [];}return json.data;}
						                },
						                "columns": [
						                    { "data": "Access"},
						                    { "data": "Owner"},
						                    { "data": "Group"},
						                    { "data": "Other"}
						                ],
						                "order": [[1, 'asc']],
						                "language":{"loadingRecords":""},
						                "initComplete":function(){finishLoadingPage();}
						            } );
						        } );
						    </script>
                		</div>
                		<div class='panel-heading'>SET PRIVILEGES</div>
                		<div class='panel-body'>
                			<div class='row'>
	                			<div class='col-xs-4 col-md-3 col-lg-2'><button>Set Basic Field Work</button></div>
	                			<div class='col-xs-4 col-md-3 col-lg-2'><button>Set Advanced Field Work</button></div>
	                			<div class='col-xs-4 col-md-3 col-lg-2'><button>Set Basic Office</button></div>
	                			<div class='col-xs-4 col-md-3 col-lg-2'><button>Set Advanced Office</button></div>
	                			<div class='col-xs-4 col-md-3 col-lg-2'><button>Set Advanced Admin</button></div>
	                		</div>
                		</div>
                <?php }?>

			</div>
        </div>
    </div>
    <!-- Bootstrap Core JavaScript -->


    <!-- Metis Menu Plugin JavaScript -->
	<?php require('bin/js/dropdown-scroll.js');?>

    <!-- Custom Theme JavaScript -->


    <?php require("bin/js/datatables.php");?>
</body>
</html>
 <?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=user.php';</script></head></html><?php }?>
