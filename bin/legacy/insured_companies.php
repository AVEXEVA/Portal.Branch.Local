<?php
session_start( [ 'read_and_close' => true ] );
require('bin/php/index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = \singleton\database::getInstance( )->query(
    	null,
      " SELECT *
		    FROM    Connection
		    WHERE   Connection.Connector = ?
		    AND     Connection.Hash  = ?
	;",array($_SESSION['User'],$_SESSION['Hash']));
    $Connection = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $r = \singleton\database::getInstance( )->query(
    	null,
      " SELECT *,
		           Emp.fFirst AS First_Name,
			         Emp.Last   AS Last_Name
		    FROM   Emp
		    WHERE  Emp.ID = ?
	;",array($_SESSION['User']));
    $User = sqlsrv_fetch_array($r);
	$r = \singleton\database::getInstance( )->query(
    null,
    " SELECT *
		  FROM    Privilege
		  WHERE   Privilege.User_ID = ?
	;",array($_SESSION['User']));
	$Privileges = array();
	if($r){while($Privilege = sqlsrv_fetch_array($r)){$Privileges[$Privilege['Access']] = $Privilege;}}
    if(	!isset($Connection['ID'])
	   	|| !isset($Privileges['Job'])
	  		|| $Privileges['Job']['Owner']  < 4
	  		|| $Privileges['Job']['Group'] < 4
	  		|| $Privileges['Job']['Other'] < 4){
				?><?php require('../404.html');?><?php }
    else {
      \singleton\database::getInstance( )->query(
      	null,
      " INSERT INTO Activity([User], [Date], [Page])
			  VALUES(?,?,?)
		;",array($_SESSION['User'],
        date( "Y-m-d H:i:s" ),
              "insured_companies.php"));
        if(count($_POST) > 0){
            if(isset($_POST['Type'])){
                if(strlen($_POST['Start_Date']) > 0){$_POST['Start_Date'] = date_format(date_create_from_format('m/d/Y',$_POST['Start_Date']),'Y-m-d 00:00:00.000');}
                if(strlen($_POST['End_Date']) > 0){$_POST['End_Date'] = date_format(date_create_from_format('m/d/Y',$_POST['End_Date']),'Y-m-d 00:00:00.000');}
                $_POST['Company'] = intval($_POST['Company']);
                $r = \singleton\database::getInstance( )->query(
                	null,
                  " SELECT *
                    FROM Insurance
                    WHERE Company=?
                    AND Type = ?",
                array($_POST['Company'],$_POST['Type']));
                if($r && sqlsrv_fetch_array($r)){
                  \singleton\database::getInstance( )->query(
                    null,
                      " UPDATE Insurance
                        SET Start_Date = ?, End_Date = ?
                        WHERE Company = ?
                        AND Type = ?",array($_POST['Start_Date'],$_POST['End_Date'],$_POST['Company'],$_POST['Type']));
                } else {
                  \singleton\database::getInstance( )->query(
                    null,
                        " INSERT INTO Insurance(Company, Start_Date, End_Date, Type)
                          VALUES(?,?,?,?);",
                    array($_POST['Company'],$_POST['Start_Date'],$_POST['End_Date'],$_POST['Type']));
                }
            } elseif(isset($_POST['Company_Name'])){
              \singleton\database::getInstance( )->query(
                null,
                  " INSERT INTO Insured_Company(Company)
                    VALUES(?)",array($_POST['Company_Name']));
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
        <?php require( bin_php . 'element/navigation.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content'>
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-primary">
                        <div class='panel-heading'><h4>
                            <div style='display:inline-block;'>
                                <span onClick="document.location.href='purchasing.php'" style='cursor:pointer;'><?php \singleton\fontawesome::getInstance( )->Unit();?>Tracked Permits / Insurances</span>
                                <span class='hidden' onClick="modernizationTracker('modernization_equipment');" style='cursor:pointer;'><span id='modernization_equipment'> > Equipment Entity</span></span>
                            </div>
                            <div style='clear:both;'></div>
                        </h4></div>
                        <div class="panel-body" id='content'>
                            <table id='Table_Insured_Companies' class='display' cellspacing='0' width='100%'>
                                <thead>
                                    <th>Insured Company / Permit</th>
                                    <th>Workman's Compensation</th>
                                    <th>Auto</th>
                                    <th>Liability</th>
                                    <th>Umbrella</th>
                                    <th>DOB</th>
                                    <th>Links</th>
                                    <th>Active</th>
                                    <th></th>
                                </thead>
                                <script>
                                    function newInsurance(link){
                                        var type = $(link).parent().attr('class');
                                        var Company = $(link).parent().parent().children("td:first-child").attr('rel');
                                        $(link).parent().html("<form action='insured_companies.php' method='post'><input type='hidden' name='Type' value='" + type + "' /><input type='hidden' name='Company' value='" + Company + "' /><input type='text' name='Start_Date' placeholder='Start Date' /> <input type='text' name='End_Date' placeholder='End Date' /><input type='submit' value='submit' /></form>");
                                        $(document).ready(function(){
                                            $("input[name='Start_Date']").datepicker({
                                                onSelect:function(dateText, inst){}
                                            });
                                            $("input[name='End_Date']").datepicker({
                                                onSelect:function(dateText, inst){}
                                            });
                                        });
                                    }
                                    function renewInsurance(link){
                                        newInsurance(link);
                                    }
                                    function newCompany(){
                                        var Company = prompt("What is the Company's Name?");
                                        if(Company == null){return;}
                                        var string = "Company_Name=" + encodeURIComponent(Company);
                                        $.ajax({
                                            url:"insured_companies.php",
                                            data:string,
                                            method:"POST",
                                            success:function(code){document.location.href='insured_companies.php';}
                                        });
                                    }
                                    function deleteRow(link){
                                        var Company = decodeURIComponent($(link).parent().parent().children("td:first-child").attr('Company'));
                                        var response = confirm("Would you like to delete the company '" + Company + "'?");
                                        if(response){
                                            $.ajax({
                                                url:"bin/php/post/deleteInsuredCompany.php",
                                                method:"POST",
                                                data:"ID=" + $(link).attr('rel'),
                                                success:function(code){document.location.href='insured_companies.php';}
                                            });
                                        }
                                    }
                                </script>
                                <tbody>
                                    <tr><td colspan='9' style='text-align:center;background-color:#9eabcd;font-weight:bold;cursor:pointer;' onClick="newCompany();">Add New Insured Company</td></tr>
                                    <?php
                                    $r = $database->query($Portal,"SELECT * FROM Insured_Company ORDER BY Company ASC");
                                    $Insured_Companis = array();
                                    $date = new DateTime("now");
                                    if($r){while($array = sqlsrv_fetch_array($r)){$Insured_Companies[$array['ID']] = $array;}}
                                    foreach($Insured_Companies as $ID=>$data){?><tr class='Insurance'>
                                        <td rel='<?php echo $data['ID'];?>' Company='<?php echo $data['Company'];?>'><?php echo $data['Company'];?></td>
                                        <td style='display:none;'><input type='checkbox' <?php if($data['Active'] == 1){?> checked='checked' <?php }?> /></td>
                                        <?php if(sqlsrv_fetch_array($database->query($Portal,"SELECT * FROM Insurance WHERE Company = ? AND Insurance.Type = 'DOB'",array($data['ID']))) == FALSE && strtolower(substr($data['Company'],0,2)) != 'ea' && strtolower(substr($data['Company'],0,3)) != 'ebn'){?>
                                        <td class='Worksmans'><?php
                                            $r = $database->query($Portal,"
                                                SELECT *
                                                FROM Insurance
                                                WHERE Company = ? AND Insurance.Type='Worksmans'
                                            ;",array($data['ID']));
                                            if($r){
                                                $array = sqlsrv_fetch_array($r);
                                                $Start_Date = $array['Start_Date'] != '' ? new DateTime($array['Start_Date']) : '';
                                                $End_Date = $array['End_Date'] != '' ? new DateTime($array['End_Date']) : '';
                                                if($Start_Date != "" && $End_Date != "" && $Start_Date <= $date && $date <= $End_Date){?>
                                                    <button onClick="renewInsurance(this);" style='background-color:green;color:white;'>Expires <?php echo $End_Date->format("m/d/Y");?></button>
                                                <?php } elseif($Start_Date != "" && $End_Date != "" && $date <= $Start_Date) {?>
                                                    <button onClick="renewInsurance(this);" style='background-color:red;color:white;'>No Insurance Until <?php echo $End_Date->format("m/d/Y");?></button>
                                                <?php } elseif($Start_Date != "" && $End_Date != "") {?>
                                                    <button onClick="renewInsurance(this);" style='background-color:red;color:white;'>Expired <?php echo $End_Date->format("m/d/Y");?></button>
                                                <?php } else {?>
                                                    <button onClick="newInsurance(this);" style='background-color:yellow;color:black;'>No Insurance</button>
                                                <?php }
                                            } else {?><button onClick="newInsurance(this);" style='background-color:yellow;color:black;'>No Insurance</button><?php }
                                        ?></td>
                                        <td class='Auto'><?php
                                            $r = $database->query($Portal,"
                                                SELECT *
                                                FROM Insurance
                                                WHERE Company = ? AND Insurance.Type='Auto'
                                            ;",array($data['ID']));
                                            if($r){
                                                $array = sqlsrv_fetch_array($r);
                                                $Start_Date = $array['Start_Date'] != '' ? new DateTime($array['Start_Date']) : '';
                                                $End_Date = $array['End_Date'] != '' ? new DateTime($array['End_Date']) : '';
                                                if($Start_Date != "" && $End_Date != "" && $Start_Date <= $date && $date <= $End_Date){?>
                                                    <button onClick="renewInsurance(this);" style='background-color:green;color:white;'>Expires <?php echo $End_Date->format("m/d/Y");?></button>
                                                <?php } elseif($Start_Date != "" && $End_Date != "" && $date <= $Start_Date) {?>
                                                    <button onClick="renewInsurance(this);" style='background-color:red;color:white;'>No Insurance Until <?php echo $End_Date->format("m/d/Y");?></button>
                                                <?php } elseif($Start_Date != "" && $End_Date != "") {?>
                                                    <button onClick="renewInsurance(this);" style='background-color:red;color:white;'>Expired <?php echo $End_Date->format("m/d/Y");?></button>
                                                <?php } else {?>
                                                    <button onClick="newInsurance(this);" style='background-color:yellow;color:black;'>No Insurance</button>
                                                <?php }
                                            } else {?><button onClick="newInsurance(this);" style='background-color:yellow;color:black;'>No Insurance</button><?php }
                                        ?></td>
                                        <td class='Liability'><?php
                                            $r = $database->query($Portal,"
                                                SELECT *
                                                FROM Insurance
                                                WHERE Company = ? AND Insurance.Type='Liability'
                                            ;",array($data['ID']));
                                            if($r){
                                                $array = sqlsrv_fetch_array($r);
                                                $Start_Date = $array['Start_Date'] != '' ? new DateTime($array['Start_Date']) : '';
                                                $End_Date = $array['End_Date'] != '' ? new DateTime($array['End_Date']) : '';
                                                if($Start_Date != "" && $End_Date != "" && $Start_Date <= $date && $date <= $End_Date){?>
                                                    <button onClick="renewInsurance(this);" style='background-color:green;color:white;'>Expires <?php echo $End_Date->format("m/d/Y");?></button>
                                                <?php } elseif($Start_Date != "" && $End_Date != "" && $date <= $Start_Date) {?>
                                                    <button onClick="renewInsurance(this);" style='background-color:red;color:white;'>No Insurance Until <?php echo $End_Date->format("m/d/Y");?></button>
                                                <?php } elseif($Start_Date != "" && $End_Date != "") {?>
                                                    <button onClick="renewInsurance(this);" style='background-color:red;color:white;'>Expired <?php echo $End_Date->format("m/d/Y");?></button>
                                                <?php } else {?>
                                                    <button onClick="newInsurance(this);" style='background-color:yellow;color:black;'>No Insurance</button>
                                                <?php }
                                            } else {?><button onClick="newInsurance(this);" style='background-color:yellow;color:black;'>No Insurance</button><?php }
                                        ?></td>
                                        <td class='Umbrella'><?php
                                            $r = $database->query($Portal,
                                                " SELECT *
                                                  FROM    Insurance
                                                  WHERE   Company = ?
                                                  AND     Insurance.Type='Umbrella'
                                            ;",array($data['ID']));
                                            if($r){
                                                $array = sqlsrv_fetch_array($r);
                                                $Start_Date = $array['Start_Date'] != '' ? new DateTime($array['Start_Date']) : '';
                                                $End_Date = $array['End_Date'] != '' ? new DateTime($array['End_Date']) : '';
                                                if($Start_Date != "" && $End_Date != "" && $Start_Date <= $date && $date <= $End_Date){?>
                                                    <button onClick="renewInsurance(this);" style='background-color:green;color:white;'>Expires <?php echo $End_Date->format("m/d/Y");?></button>
                                                <?php } elseif($Start_Date != "" && $End_Date != "" && $date <= $Start_Date) {?>
                                                    <button onClick="renewInsurance(this);" style='background-color:red;color:white;'>No Insurance Until <?php echo $End_Date->format("m/d/Y");?></button>
                                                <?php } elseif($Start_Date != "" && $End_Date != "") {?>
                                                    <button onClick="renewInsurance(this);" style='background-color:red;color:white;'>Expired <?php echo $End_Date->format("m/d/Y");?></button>
                                                <?php } else {?>
                                                    <button onClick="newInsurance(this);" style='background-color:yellow;color:black;'>No Insurance</button>
                                                <?php }
                                            } else {?><button onClick="newInsurance(this);" style='background-color:yellow;color:black;'>No Insurance</button><?php }
                                        ?></td><td></td>
                                        <?php } else {?>
                                        <td colspan='4'></td>
                                        <td class='DOB'><?php
                                            $r = $database->query($Portal,"
                                                SELECT *
                                                FROM Insurance
                                                WHERE Company = ? AND Insurance.Type='DOB'
                                            ;",array($data['ID']));
                                            if($r){
                                                $array = sqlsrv_fetch_array($r);
                                                $Start_Date = $array['Start_Date'] != '' ? new DateTime($array['Start_Date']) : '';
                                                $End_Date = $array['End_Date'] != '' ? new DateTime($array['End_Date']) : '';
                                                if($Start_Date != "" && $End_Date != "" && $Start_Date <= $date && $date <= $End_Date){?>
                                                    <button onClick="renewInsurance(this);" style='background-color:green;color:white;'>Expires <?php echo $End_Date->format("m/d/Y");?></button>
                                                <?php } elseif($Start_Date != "" && $End_Date != "" && $date <= $Start_Date) {?>
                                                    <button onClick="renewInsurance(this);" style='background-color:red;color:white;'>No Insurance Until <?php echo $End_Date->format("m/d/Y");?></button>
                                                <?php } elseif($Start_Date != "" && $End_Date != "") {?>
                                                    <button onClick="renewInsurance(this);" style='background-color:red;color:white;'>Expired <?php echo $End_Date->format("m/d/Y");?></button>
                                                <?php } else {?>
                                                    <button onClick="newInsurance(this);" style='background-color:yellow;color:black;'>No Permit</button>
                                                <?php }
                                            } else {?><button onClick="newInsurance(this);" style='background-color:yellow;color:black;'>No Permit</button><?php }
                                        ?></td><?php }?>
                                        <td style='position:relative;'><input type='text' value='<?php echo trim($data['Hyperlink']);?>' ondblclick="hyperlinkInput(this);" onchange="updateHyperlink(this);" rel="<?php echo $data['ID'];?>" /></td>
                                        <td><form><input type='hidden' value="0" name="Active" /><input type='checkbox' value="1" name="Active" onClick="updateActive(this);" rel="<?php echo $data['ID'];?>" <?php if($data['Active'] == 1){?>checked='checked'<?php }?> /></form></td>
                                        <td><button onClick="deleteRow(this);" rel='<?php echo $data['ID'];?>'>Delete</button>
                                    </tr><?php }?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php require(PROJECT_ROOT.'js/datatables.php');?>
    <script>
        $(document).ready(function(){
            var Table_Insured_Companies = $("#Table_Insured_Companies").DataTable();
        });
    </script>
    <style>
    Table#Table_Modernizations td.hide_column { display:none; }
    </style>
    <!-- Custom Date Filters-->

    <style>
    div.column {display:inline-block;vertical-align:top;}
    div.label1 {display:inline-block;font-weight:bold;width:150px;vertical-align:top;}
    div.data {display:inline-block;width:300px;vertical-align:top;}
    </style>
    <script>
    $(document).ready(function(){
        finishLoadingPage();
    });
    function hyperlinkInput(link){
        if($(link).val().includes("http://") || $(link).val().includes("https://")){
            $(link).parent().append("<div class='miniPopup' style='position:absolute;top:-25px;width:100%;height:25px;;background-color:white;padding-left:10px;'><a href='" + $(link).val() + "' target='_blank' style='text-decoration:underline;color:blue;'>" + $(link).val() + "</a></div>");
        }
    }
    function updateHyperlink(link){
        var ID = $(link).attr('rel');
        var hyperlink = $(link).val();
        var string = "ID=" + ID + "&Hyperlink=" + encodeURIComponent(hyperlink);
        $.ajax({
            url:"bin/php/post/updateHyperlink.php",
            data:string,
            method:"POST",
            success:function(code){}
        });
    }
    function updateActive(link){
        var form_data = $(link).parent().serialize() + "&ID=" + $(link).attr('rel');
        $.ajax({
            url:"bin/php/post/updateActive.php",
            data:form_data,
            method:"POST",
            success:function(code){}
        });
    }
    $(document).ready(function(){
        $(document).on("click",function(){
            $(".miniPopup").remove();
        });
    });
    </script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=modernizations.php';</script></head></html><?php }?>
