<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [
        'read_and_close' => true
    ] );
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/cgi-bin/php/index.php' );
}
if( isset( $_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
  $result = \singleton\database::getInstance()->query(
    null,
    " SELECT  *
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
    null,
    " SELECT  *,
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
  $result = \singleton\database::getInstance()->query(
    null,
    " SELECT  Privilege.Access_Table,
              Privilege.User_Privilege,
              Privilege.Group_Privilege,
              Privilege.Other_Privilege
      FROM    Privilege
      WHERE   Privilege.User_ID = ?;",
    array(
      $_SESSION[ 'User' ]
    )
  );
  $Privileges = array();
  if( $result ){while( $Privilege = sqlsrv_fetch_array( $result ) ){ $Privileges[ $Privilege[ 'Access_Table' ] ] = $Privilege; } }
  if(     !isset( $Connection[ 'ID' ] )
      ||  !isset($Privileges[ 'Requisition' ])
      ||  $Privileges[ 'Requisition' ][ 'User_Privilege' ]  < 4
      ||  $Privileges[ 'Requisition' ][ 'Group_Privilege' ] < 4
      ||  $Privileges[ 'Requisition' ][ 'Other_Privilege' ] < 4
  ){
      ?><?php require( '../404.html' );?><?php
  } else {
    \singleton\database::getInstance()->query(
      null,
      " INSERT INTO Activity( [User], [Date], [Page] )
        VALUES( ?, ?, ? );",
      array(
        $_SESSION[ 'User' ],
        date( 'Y-m-d H:i:s' ),
        'wall.php'
      )
    );
    $r = \singleton\database::getInstance()->query($Portal,
      " SELECT  Requisition.*,
                Loc.Tag AS Location_Tag,
                DropOff.Tag AS DropOff_Tag,
                Elev.State AS Unit_State,
                Elev.Unit AS Unit_Label,
                Job.fDesc AS Job_Name,
                Job.ID AS Job_ID,
                Loc.Address AS Location_Street,
                Loc.City AS Location_City,
                Loc.State AS Location_State,
                Loc.Zip AS Location_Zip,
                DropOff.Address AS DropOff_Street,
                DropOff.City AS DropOff_City,
                DropOff.State AS DropOff_State,
                DropOff.Zip AS DropOff_Zip,
                JobType.Type AS Job_Type,
                Emp.fFirst + ' ' + Emp.Last AS User_Name
        FROM    Portal.dbo.Requisition
                LEFT JOIN Loc ON Requisition.Location = Loc.Loc
                LEFT JOIN Loc AS DropOff ON Requisition.DropOff = DropOff.Loc
                LEFT JOIN Elev ON Requisition.Unit = Elev.ID
                LEFT JOIN Job ON Requisition.Job = Job.ID
                LEFT JOIN JobType ON JobType.ID = Job.Type
                LEFT JOIN Emp ON Emp.ID = Requisition.[User]
        WHERE Requisition.ID = ?
      ;",array($_GET['ID']));
    $Requisition = sqlsrv_fetch_array($r);
    $r = $database->query($Portal,"SELECT * FROM Portal.dbo.Requisition_Item WHERE Requisition_Item.Requisition = ?;",array($_GET['ID']));
    $Requisition_Items = array();
    if($r){while($row = sqlsrv_fetch_array($r)){$Requisition_Items[] = $row;}}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Peter D. Speranza">    <title>Nouveau Texas | Portal</title>
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>
	<!--  base structure css  -->
	<link href="css/ufd-base.css" rel="stylesheet" type="text/css" />

	<!--  plain css skin  -->
	<link href="css/plain.css" rel="stylesheet" type="text/css" />

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
  @media print
  {
      .no-print, .no-print *
      {
          display: none !important;
      }
      .print {
          display: block !important;
      }
  }
  .print {display:none;}
  .noprint {display:block;}
  </style>
</head>
<body onload='finishLoadingPage();' style='background-color:#1d1d1d;'>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require(PROJECT_ROOT.'php/element/navigation/index.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content'>
          <div class='print' style='overflow:hidden;'>
            <div class='row'>
              <div class='row'>
                  <div class='col-xs-6'>
                      <div><img src='http://www.nouveauelevator.com/Images/Icons/logo.png' width='25px' style='position:relative;left:110px;' /></div>
                      <h3 style='text-align:left;' class='BankGothic'>Nouveau Elevator</h3>
                  </div>
                  <div class='col-xs-6' style='text-align:right;'>
                      <div clsas='row' style='font-size:12px;'>
                          <div class='col-xs-12'>47-55 37th Street LIC, NY 11101</div>
                      </div>
                      <div clsas='row' style='font-size:12px;'>
                          <div class='col-xs-12'>Tel:(718)349-4700 Fax:383:3218</div>
                      </div>
                      <div clsas='row' style='font-size:12px;'>
                          <div class='col-xs-12'>www.NouveauElevator.com</div>
                      </div>
                  </div>
              </div>
            </div>
            <hr/>
            <div class='row' style='background-color:#1d1d1d;color:white;text-align:center;text-decoration:underline;'><h3>Parts Requisition #<?php echo $_GET['ID'];?></h3></div>
            <hr/>
            <div class='row'>
              <div class='col-xs-6'>
                <div class='row'>
                  <div class='col-xs-12' style='text-decoration:underline;'><h4>Location</h4></div>
                  <div class='col-xs-6' style='text-align:right;'>Tag:</div>
                  <div class='col-xs-6'><?php echo $Requisition['Location_Tag'];?></div>
                  <div class='col-xs-6' style='text-align:right;'>Street:</div>
                  <div class='col-xs-6'><?php echo $Requisition['Location_Street'];?></div>
                  <div class='col-xs-6' style='text-align:right;'>City:</div>
                  <div class='col-xs-6'><?php echo $Requisition['Location_City'];?></div>
                  <div class='col-xs-6' style='text-align:right;'>State:</div>
                  <div class='col-xs-6'><?php echo $Requisition['Location_State'];?></div>
                  <div class='col-xs-6' style='text-align:right;'>Zip:</div>
                  <div class='col-xs-6'><?php echo $Requisition['Location_Zip'];?></div>
                </div>
              </div>
              <div class='col-xs-6'>
                <div class='row'>
                  <div class='col-xs-12' style='text-decoration:underline;'><h4>Drop Off Point</h4></div>
                  <div class='col-xs-6' style='text-align:right;'>Tag:</div>
                  <div class='col-xs-6'><?php echo $Requisition['DropOff_Tag'];?></div>
                  <div class='col-xs-6' style='text-align:right;'>Street:</div>
                  <div class='col-xs-6'><?php echo $Requisition['DropOff_Street'];?></div>
                  <div class='col-xs-6' style='text-align:right;'>City:</div>
                  <div class='col-xs-6'><?php echo $Requisition['DropOff_City'];?></div>
                  <div class='col-xs-6' style='text-align:right;'>State:</div>
                  <div class='col-xs-6'><?php echo $Requisition['DropOff_State'];?></div>
                  <div class='col-xs-6' style='text-align:right;'>Zip:</div>
                  <div class='col-xs-6'><?php echo $Requisition['DropOff_Zip'];?></div>
                </div>
              </div>
            </div>
            <hr/>
            <div class='row'>
              <div class='col-xs-6'>
                <div class='row'>
                  <div class='col-xs-12' style='text-decoration:underline;'><h4>Job</h4></div>
                  <div class='col-xs-6' style='text-align:right;'>ID:</div>
                  <div class='col-xs-6'><?php echo $Requisition['Job_ID'];?></div>
                  <div class='col-xs-6' style='text-align:right;'>Description:</div>
                  <div class='col-xs-6'><?php echo $Requisition['Job_Name'];?></div>
                  <div class='col-xs-6' style='text-align:right;'>Type:</div>
                  <div class='col-xs-6'><?php echo $Requisition['Job_Type'];?></div>
                </div>
              </div>
              <div class='col-xs-6'>
                <div class='row'>
                  <div class='col-xs-12' style='text-decoration:underline;'><h4>Unit</h4></div>
                  <div class='col-xs-6' style='text-align:right;'>City ID:</div>
                  <div class='col-xs-6'><?php echo $Requisition['Unit_State'];?></div>
                  <div class='col-xs-6' style='text-align:right;'>Building ID:</div>
                  <div class='col-xs-6'><?php echo $Requisition['Unit_Label'];?></div>
                </div>
              </div>
            </div>
            <hr/>
            <div class='row'>
              <div class='col-xs-12' style='text-decoration:underline;'><h4>Details</h4></div>
              <div class='col-xs-3' style='text-align:right;'>Requested By:</div>
              <div class='col-xs-3'><?php echo $Requisition['User_Name'];?></div>
              <div class='col-xs-3' style='text-align:right;'>Date:</div>
              <div class='col-xs-3'><?php echo date("m/d/Y",strtotime($Requisition['Date']));?></div>
              <div class='col-xs-3' style='text-align:right;'>Required:</div>
              <div class='col-xs-3'><?php echo date("m/d/Y",strtotime($Requisition['Required']));?></div>
              <div class='col-xs-3' style='text-align:right;'>Shutdown:</div>
              <div class='col-xs-3'><input type='checkbox' disabled name='Shutdown' <?php echo isset($Requisition['Shutdown']) && $Requisition['Shutdown'] == 1 ? 'checked' : '';?>  /></div>
              <div class='col-xs-3' style='text-align:right;'>A.S.A.P.:</div>
              <div class='col-xs-3'><input type='checkbox' disabled name='ASAP' <?php echo isset($Requisition['ASAP']) && $Requisition['ASAP'] == 1 ? 'checked' : '';?>  /></div>
              <div class='col-xs-3' style='text-align:right;'>Rush:</div>
              <div class='col-xs-3'><input type='checkbox' disabled name='Rush' <?php echo isset($Requisition['Rush']) && $Requisition['Rush'] == 1 ? 'checked' : '';?>  /></div>
              <div class='col-xs-3' style='text-align:right;'>L/S/D:</div>
              <div class='col-xs-3'><input type='checkbox' disabled name='LSD' <?php echo isset($Requisition['LSD']) && $Requisition['LSD'] == 1 ? 'checked' : '';?>  /></div>
              <div class='col-xs-3' style='text-align:right;'>F.R.M.:</div>
              <div class='col-xs-3'><input type='checkbox' disabled name='FRM' <?php echo isset($Requisition['FRM']) && $Requisition['FRM'] == 1 ? 'checked' : '';?>  /></div>
            </div>
            <hr/>
            <div class='row'><div class='col-xs-12'><h4 style='text-decoration:underline;'>Items</h4></div></div>
            <div class='row'>
              <div class='col-xs-12'>&nbsp;</div>
            </div>
            <div class='row'>
              <div class='col-xs-12' style='padding:0px;margin:0px;'>
                <div class='row Item-Header'>
                  <div class='col-xs-1'><b><i>#</i></b></div>
                  <div class='col-xs-1'><b><i>Quantity</i></b></div>
                  <div class='col-xs-6'><b><i>Description</i></b></div>
                  <div class='col-xs-2'><b><i>B/O</i></b></div>
                  <div class='col-xs-2'><b><i>Quantity Out</i></b></div>
                </div>
                <?php
                if(is_array($Requisition_Items) && count($Requisition_Items) > 0){
                  $i = 0;
                  foreach($Requisition_Items AS $array){
                    $i++;
                    ?><div class='row Item'>
                      <div class='col-xs-1'><?php echo $i;?></div>
                      <div class='col-xs-1'><input type='text' name='Quantity' disabled value='<?php echo $array['Quantity'];?>' style='width:100%;' /></div>
                      <div class='col-xs-6'><input type='text' name='Comments' disabled value='<?php echo $array['Item_Description'];?>' style='width:100%;' /></div>
                      <div class='col-xs-2'><input type='text' name='B/O' disabled style='wdith:100%;' /></div>
                      <div class='col-xs-2'><input type='text' name='Quantity_Out' disabled style='wdith:100%;' /></div>
                    </div><?php
                  }
                }
                ?>
              </div>
            </div>
            </hr>
            </br>
            </br>
            </br>
            <div class='row'><div class='col-xs-12'><h4 style='text-decoration:underline;'>Approval</h4></div></div>
            <div class='row'>
              <div class='col-xs-3' style='text-align:right;'>Approved By:</div>
              <div class='col-xs-3' style=''>_______________________________</div>
              <div class='col-xs-3' style='text-align:right;'>Date Approved:</div>
              <div class='col-xs-3' style=''>_______________________________</div>
            </div>
            <div style='page-break-before:always;'>
              <?php
              if(is_array($Requisition_Items) && count($Requisition_Items) > 0){
                $i = 0;
                foreach($Requisition_Items AS $array){
                  $i++;
                  ?><div class='row Item'>
                    <div class='col-xs-12'><h1><?php echo $array['Item_Description'];?></h1></div>
                    <div class='col-xs-12'><img height='500px' src="<?php
                      print "data:" . $array['Image_Type'] . ";base64, " . $array['Image'];
                    ?>" /></div>
                  </div><?php
                }
              }
              ?>
            </div>
          </div>
    			<div class="panel panel-primary no-print">
    				<div class="panel-heading" onClick="document.location.href='requisitions.php'"><h3 style='margin:0px;'><?php \singleton\fontawesome::getInstance( )->Requisition();?> Requisition #<?php echo $Requisition['ID'];?></h3></div>
    				<div class="panel-body" style=''>
    					<div class='row'><div class='col-xs-12'>&nbsp;</div></div>
              <div class="row">
    						<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->User(1);?> User:</div>
    						<div class='col-xs-8'><input disabled type='text' name='User_Name' size='15' value='<?php echo $Requisition['User_Name'];?>' /></div>
    					</div>
    					<div class="row">
    						<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Calendar(1);?> Date:</div>
    						<div class='col-xs-8'><input disabled type='text' name='Date' size='15' value='<?php echo date("m/d/Y",strtotime($Requisition['Date']));?>' /></div>
    					</div>
    					<div class="row">
    						<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Required</div>
    						<div class='col-xs-8'><input disabled type='text' name='Required' size='15' value='<?php echo date("m/d/Y",strtotime($Requisition['Required']));?>' /></div>
    					</div>
              <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
              <div class='row'>
      					<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Location(1);?> Location:</div>
      					<div class='col-xs-8'><?php echo $Requisition['Location_Tag'];?></div>
              </div>
              <div class='row'>
      					<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Location(1);?> Drop Off:</div>
      					<div class='col-xs-8'><?php echo $Requisition['DropOff_Tag'];?></div>
              </div>
              <div class='row'>
                <div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Unit(1);?> Unit:</div>
                <div class='col-xs-8'><?php echo $Requisition['Unit_State'];?></div>
              </div>
              <div class='row'>
                <div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Job(1);?> Job:</div>
                <div class='col-xs-8'><?php echo $Requisition['Job_Name'];?></div>
              </div>
              <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
    					<div class='row Labels' >
    						<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Shutdown:</div>
    						<div class='col-xs-8'><input type='checkbox' disabled name='Shutdown' <?php echo isset($Requisition['Shutdown']) && $Requisition['Shutdown'] == 1 ? 'checked' : '';?> /></div>
    					</div>
    					<div class='row Labels' >
    						<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> A.S.A.P.:</div>
    						<div class='col-xs-8'><input type='checkbox' disabled name='ASAP' <?php echo isset($Requisition['ASAP']) && $Requisition['ASAP'] == 1 ? 'checked' : '';?>  /></div>
    					</div>
              <div class='row Labels' >
    						<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Rush:</div>
    						<div class='col-xs-8'><input type='checkbox' disabled name='Rush' <?php echo isset($Requisition['Rush']) && $Requisition['Rush'] == 1 ? 'checked' : '';?>  /></div>
    					</div>
              <div class='row Labels' >
    						<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> L/S/D.:</div>
    						<div class='col-xs-8'><input type='checkbox' disabled name='LSD' <?php echo isset($Requisition['LSD']) && $Requisition['LSD'] == 1 ? 'checked' : '';?>  /></div>
    					</div>
              <div class='row Labels' >
    						<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> F.R.M.:</div>
    						<div class='col-xs-8'><input type='checkbox' disabled name='FRM' <?php echo isset($Requisition['FRM']) && $Requisition['FRM'] == 1 ? 'checked' : '';?>  /></div>
    					</div>
              <div class='row Labels' >
                <div class='col-xs-12'><?php \singleton\fontawesome::getInstance( )->Paragraph(1);?> Notes:</div>
                <div class='col-xs-12'><textarea name='Notes' style='width:100%;' rows='9' disabled><?php echo isset($Requisition['Notes']) ? $Requisition['Notes'] : NULL;?></textarea></div>
              </div>
              <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
            </div>
            <div class='panel-heading'><h3><?php \singleton\fontawesome::getInstance( )->Purchase();?> Items</h3></div>
            <div class='panel-body'>
              <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
    					<div class='row'>
    						<div class='col-xs-12' style='padding:0px;margin:0px;'>
    							<div class='row Item-Header'>
    								<div class='col-xs-1'><b><i>#</i></b></div>
    								<div class='col-xs-2'><b><i>Quantity</i></b></div>
    								<div class='col-xs-6'><b><i>Description</i></b></div>
                    <div class='col-xs-3'><b><i>Image</i></b></div>
    							</div>
    							<?php
                  if(is_array($Requisition_Items) && count($Requisition_Items) > 0){
                    $i = 0;
                    foreach($Requisition_Items AS $array){
                      $i++;
                      ?><div class='row Item'>
        								<div class='col-xs-1'><?php echo $i;?></div>
        								<div class='col-xs-2'><input type='text' name='Quantity' disabled value='<?php echo $array['Quantity'];?>' style='width:100%;' /></div>
        								<div class='col-xs-6'><input type='text' name='Comments' disabled value='<?php echo $array['Item_Description'];?>' style='width:100%;' /></div>
                        <div class='col-xs-3'><img height='25px' src="<?php print "data:" . $array['Image_Type'] . ";base64, " . $array['Image'];?>" /></div>
        							</div><?php
                    }
                  }
                  ?>
    							<div class='row New-Item'>
    								<div class='col-xs-12'><div class='col-xs-12'>&nbsp;</div></div>
                    <div class='col-xs-12'><div class='col-xs-12'>&nbsp;</div></div>
    								<div class='col-xs-12'><div class='col-xs-12'>&nbsp;</div></div>
                    <div class='col-xs-12'><button onClick='saveRequisition();' style='width:100%;height:50px;'>Save</button></div>
                    <div class='col-xs-12'><div class='col-xs-12'>&nbsp;</div></div>
                    <div class='col-xs-12'><div class='col-xs-12'>&nbsp;</div></div>
                    <div class='col-xs-12'><div class='col-xs-12'>&nbsp;</div></div>
    							</div>
    						</div>
    					</div>
    				</div>
          </div>
        </div>
    </div>
    <!-- Bootstrap Core JavaScript -->


    <!-- Metis Menu Plugin JavaScript -->


    <?php require(PROJECT_ROOT.'js/datatables.php');?>

    <!-- Custom Theme JavaScript -->


    <!--Moment JS Date Formatter-->


    <!-- JQUERY UI Javascript -->


    <!-- Custom Date Filters-->


	<!--  if you want iE6 not to poke select boxes thru your dropdowns, you need ... -->
	<script type="text/javascript" src="js/jquery.bgiframe.min.js"></script>

	<!-- Plugin source development location, distribution location: only 1 of 2 is there..	 -->
	<script type="text/javascript" src="js/jquery.ui.ufd.js"></script>
    <script>
	var Item_Count = 5;
	function newItem(){
		Item_Index = Item_Count - 1;
		$(".New-Item").before("<div class='row Item'><div class='col-xs-1'>" + Item_Count.toString() + "</div><div class='col-xs-2'><input type='text' name='Quantity[" + Item_Index.toString() + "]' style='width:100%;' /></div><div class='col-xs-9'><input type='text' name='Description[" + Item_Index.toString() + "]' style='width:100%;' /></div>");
		Item_Count = Item_Count + 1;
	}
	$(document).ready(function(){
		$("input[name='Date']").datepicker();
		$("input[name='Required']").datepicker();
		$("input[name='Date']").datepicker("setDate",new Date());
	});
	$(document).ready(function(){
		$("select[name='Location']").ufd({log:true});
	});
  function closePopup(link){$(".popup").remove();}
  function saveRequisition(){
    var requisitionData = new FormData();
    requisitionData.append("Required",$("input[name='Required']").val());
    requisitionData.append('Location','<?php echo isset($_GET['Location']) ? $_GET['Location'] : '';?>');
    requisitionData.append('DropOff','<?php echo isset($_GET['DropOff']) ? $_GET['DropOff'] : '';?>');
    requisitionData.append('Unit','<?php echo isset($_GET['Unit']) ? $_GET['Unit'] : '';?>');
    requisitionData.append('Job','<?php echo isset($_GET['Job']) ? $_GET['Job'] : '';?>');
    requisitionData.append("Shutdown",$("input[name='Shutdown']").prop('checked'));
    requisitionData.append("ASAP",$("input[name='ASAP']").prop('checked'));
    requisitionData.append("Rush",$("input[name='Rush']").prop('checked'));
    requisitionData.append("LSD",$("input[name='LSD']").prop('checked'));
    requisitionData.append("FRM",$("input[name='FRM']").prop('checked'));
    var itemArray = [];
    var count = 0;
    $(".row.Item").each(function(){
      requisitionData.append("Item[" + count + "][Quantity]",$(this).find("input[name='Quantity']").val());
      requisitionData.append("Item[" + count + "][Comments]",$(this).find("input[name='Comments']").val());
      count++;
    });
    $.ajax({
      url:"cgi-bin/php/post/save_requisition.php",
      cache: false,
      processData: false,
      contentType: false,
      data: requisitionData,
      timeout:15000,
      error:function(XMLHttpRequest, textStatus, errorThrown){
        alert('Your ticket did not save. Please check your internet.')
        $(tempLink).html("Save");
        $(tempLink).prop('disabled',false);
      },
      method:"POST",
      success:function(code){
        document.location.href='requisition.php?ID=' + code;
      }
    });
  }
	</script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=units.php';</script></head></html><?php }?>
