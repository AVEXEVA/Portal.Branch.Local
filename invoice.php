<?php
session_start( [ 'read_and_close' => true ] );
require('/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/cgi-bin/php/index.php');
setlocale(LC_MONETARY, 'en_US');
  if(isset($_SESSION[ 'User' ],
           $_SESSION[ 'Hash' ] ) ) {
        $result = sqlsrv_query(
            $NEI,
          '     SELECT  *
          FROM      Connection
          WHERE         Connector = ?
                        AND Hash = ?;',
          array(
                $_SESSION['User'],
                $_SESSION['Hash']
          )
    );
          $array = sqlsrv_fetch_array( $result );
          if(!isset(
              $_SESSION[ 'Branch' ]) || $_SESSION[ 'Branch' ] == 'Nouveau Elevator'){
            $result= sqlsrv_query(
              $NEI,
            '      SELECT   *,
                          Emp.fFirst AS First_Name,
                          Emp.Last   AS Last_Name
                   FROM   Emp
                   WHERE  Emp.ID= ?',
          array(
            $_SESSION[ 'User' ]
          )
    );
          $User = sqlsrv_fetch_array( $result );
          $Field = ($User[ 'Field' ] == 1
              && $User[ 'Title' ] != 'OFFICE') ? True : False;
          $result = sqlsrv_query(
            $NEI,
          '       SELECT Access_Table,
                         User_Privilege,
                         Group_Privilege,
                         Other_Privilege
                  FROM   Privilege
                  WHERE  User_ID = ?;',
          array(
            $_SESSION[ 'User' ]
          )
    );
  $Privileges = array();
    while($array2 = sqlsrv_fetch_array($result)){$Privileges[$array2[ 'Access_Table' ]] = $array2;}
    $Privileged = FALSE;
      if(isset($Privileges[ 'Invoice' ])
        && $Privileges[ 'Invoice' ][ 'User_Privilege' ] >= 6
        && $Privileges[' Invoice' ][ 'Group_Privilege' ] >= 4
        && $Privileges[ 'Invoice' ][ 'Other_Privilege' ] >= 4) {$Privileged = TRUE;}
        else {
      //NEEDS TO INCLUDE SECURITY FOR OTHER PRIVILEGE
    }
    } elseif(
      $_SESSION[ 'Branch' ] == 'Customer' && is_numeric($_GET[ 'ID' ] ) ) {
            $result =  sqlsrv_query(
               $NEI,
               '    SELECT Ref
                    FROM  Invoice LEFT JOIN Loc ON Invoice.Loc = Loc.Loc
                    WHERE Invoice.Ref='{$_GET[ 'ID' ] }' AND Loc.Owner = '{$_SESSION[ 'Branch_ID' ]}';');
               $Privileged = $result ? TRUE : FALSE;
    }
  sqlsrv_query($Portal,'INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);',
  array(
    $_SESSION[ 'User' ],
      date('Y-m-d H:i:s'),
           'invoice.php')
    );
    if(!isset($array['ID']) || !is_numeric($_GET['ID'])  || !$Privileged){?><html><head><script></script></head></html><?php }
    else {
        $result = sqlsrv_query($NEI,
            'SELECT
                TOP 1
                Invoice.Ref 			AS ID,
                Invoice.fDesc 			AS Description,
                Invoice.Amount 			AS Amount,
                Invoice.Taxable         AS Taxable,
                Invoice.STax 			AS STax,
                Invoice.Total 			AS Total,
                Invoice.fDate 			AS fDate,
                Job.fDesc 				AS Job,
                Job.ID                  AS Job_ID,
                JobType.Type            AS Job_Type,
                Loc.Loc 				AS Location_ID,
                Loc.ID 					AS Location_Name,
                Loc.Tag 				AS Location_Tag,
                Loc.Address 			AS Street,
                Loc.City 				AS City,
                Loc.State 				AS State,
                Loc.Zip 				AS Zip,
                Zone.Name 				AS Zone,
                Route.ID 				AS Route_ID,
                Route_Mechanic.fFirst 	AS Route_Mechanic_First_Name,
                Route_Mechanic.Last 	AS Route_Mechanic_Last_Name,
                OwnerWithRol.ID         AS Customer_ID,
                OwnerWithRol.Name       AS Customer_Name,
                OwnerWithRol.Address    AS Customer_Street,
                OwnerWithRol.City       AS Customer_City,
                OwnerWithRol.State      AS Customer_State,
                OwnerWithRol.Zip        AS Customer_Zip,
                OwnerWithRol.Contact    AS Customer_Contact
            FROM
                Invoice
                LEFT JOIN Loc 					ON Invoice.Loc = Loc.Loc
                LEFT JOIN Job 					ON Invoice.Job = Job.ID
                LEFT JOIN Zone 					ON Loc.Zone = Zone.ID
                LEFT JOIN Route 				ON Loc.Route = Route.ID
                LEFT JOIN OwnerWithRol          ON Loc.Owner = OwnerWithRol.ID
                LEFT JOIN Emp AS Route_Mechanic ON Route.Mech = Route_Mechanic.fWork
                LEFT JOIN JobType               ON Job.Type = JobType.ID
            WHERE
                Invoice.Ref='{$_GET[ 'ID' ]}'');
        $data = sqlsrv_fetch_array($result);

?><!DOCTYPE html>
<html lang='en'>
<head>
    <?php require(bin_meta.'index.php');?>
    <title>Nouveau Texas | Portal</title>
    <?php require(bin_css.'index.php');?>
    <?php require(bin_js.'index.php');?>
</head>
<body onload='finishLoadingPage();'>
    <div id='wrapper' style='overflow:auto !important;' class='<?php echo isset($_SESSION[ 'Toggle_Menu' ]) ? $_SESSION['Toggle_Menu'] : null;?>'>
        <?php require(PROJECT_ROOT.'php/element/navigation/index.php');?>
        <?php require(PROJECT_ROOT.'php/element/loading.php');?>
        <div id='page-wrapper' class='content' style='overflow:auto !important;'>
            <div class='' style='display:none;'>
                <div class='panel panel-primary'>
                    <div class='panel-heading'><h3><?php $Icons->Invoice();?> Invoice</h3></div>
                    <div class='panel-body'>
                        <div class='col-md-4'>
                            <div class='panel panel-red'>
                                <div class='panel-heading'>
                                    Invoice Details
                                </div>
                                <div class='panel-body'>
                                    <div class='row'>
                                        <div class='col-xs-4'><div><b>ID:</b></div></div>
                                        <div class='col-xs-8'><?php echo $data[ 'ID' ];?></div>
                                    </div>
                                    <div class='row'>
                                        <div class='col-xs-4'><b>Description:</b></div>
                                        <div class='col-xs-8'><?php echo $data[ 'Description' ];?></div>
                                    </div>
                                    <div class='row'>
                                        <div class='col-xs-4'><b>Date:</b></div>
                                        <div class='col-xs-8'><?php echo $data['fDate'];?></div>
                                    </div>
                                    <div class='row'>
                                        <div class='col-xs-4'><b>Amount:</b></div>
                                        <div class='col-xs-8'><?php echo $data['Amount'];?></div>
                                    </div>
                                    <div class='row'>
                                        <div class='col-xs-4'><b>Sales Tax:</b></div>
                                        <div class='col-xs-8'><?php echo $data['STax'];?></div>
                                    </div>
                                    <div class='row'>
                                        <div class='col-xs-4'><b>Total:</b></div>
                                        <div class='col-xs-8'><?php echo $data['Total'];?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class='col-md-4'>
                            <div class='panel panel-blue'>
                                <div class='panel-heading'>Customer Details</div>
                                <div class='panel-body'>
                                    <div class='row'><div class='col-xs-4'><div><b>ID:</b></div></div><div class='col-xs-8'><?php echo $data['Customer_ID'];?></div></div>
                                    <div class='row'><div class='col-xs-4'><b>Name:</b></div><div class='col-xs-8'><a href='customer.php?ID=<?php echo $data['Customer_ID'];?>'><?php echo $data['Customer_Name'];?></a></div></div>
                                    <div class='row'><div class='col-xs-4'><b>Street:</b></div><div class='col-xs-8'><?php echo $data['Customer_Street'];?></div></div>
                                    <div class='row'><div class='col-xs-4'><b>City:</b></div><div class='col-xs-8'><?php echo $data['Customer_City'];?></div></div>
                                    <div class='row'><div class='col-xs-4'><b>State:</b></div><div class='col-xs-8'><?php echo $data['Customer_State'];?></div></div>
                                    <div class='row'><div class='col-xs-4'><b>Zip:</b></div><div class='col-xs-8'><?php echo $data['Customer_Zip'];?></div></div>
                                </div>
                            </div>
                        </div>
                        <div class='col-md-4'>
                            <div class='panel panel-yellow'>
                                <div class='panel-heading'>Location Details</div>
                                <div class='panel-body'>
                                    <div class='row'>
                                        <div class='col-xs-4'><div><b>ID:</b></div></div>
                                        <div class='col-xs-8'><?php echo $data['Location_ID'];?></div>
                                    </div>
                                    <div class='row'>
                                        <div class='col-xs-4'><b>Tag:</b></div>
                                        <div class='col-xs-8'><a href='<?php echo (strlen($data['Location_ID']) > 0) ? 'location.php?ID=' . $data['Location_ID'] : '#';?>'><?php echo $data['Location_Tag'];?></a></div>
                                    </div>
                                    <div class='row'>
                                        <div class='col-xs-4'><b>Street:</b></div>
                                        <div class='col-xs-8'><?php echo $data['Street'];?></div>
                                    </div>
                                    <div class='row'>
                                        <div class='col-xs-4'><b>City:</b></div>
                                        <div class='col-xs-8'><?php echo $data['City'];?></div>
                                    </div>
                                    <div class='row'>
                                        <div class='col-xs-4'><b>State:</b></div>
                                        <div class='col-xs-8'><?php echo $data['State'];?></div>
                                    </div>
                                    <div class='row'>
                                        <div class='col-xs-4'><b>Zip:</b></div>
                                        <div class='col-xs-8'><?php echo $data['Zip'];?></div>
                                    </div>
                                    <div class='row'>
                                        <div class='col-xs-4'><b>Route:</b></div>
                                        <div class='col-xs-8'><?php if(!$Field || $_SESSION['User'] == $data['Route_Mechanic_ID']){?><a href='route.php?ID=<?php echo $data['Route_ID'];?>'><?php echo $data['Route_Mechanic_First_Name'] . ' ' . $data['Route_Mechanic_Last_Name'];?></a><?php } else {?><?php echo $data['Route_Mechanic_First_Name'] . ' ' . $data['Route_Mechanic_Last_Name'];?><?php }?></div>
                                    </div>
                                    <div class='row'>
                                        <div class='col-xs-4'><b>Zone:</b></div>
                                        <div class='col-xs-8'><?php echo $data['Zone'];?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class='row'>
                        <div class='col-md-12'>
                            <div class='panel panel-green'>
                                <div class='panel-heading'>
                                    Invoice Items
                                </div>
                                <div class='panel-body'>
                                    <table id='Table_Invoice_Items' class='display' cellspacing='0' width='100%'>
                                        <thead>
                                            <th title='Date'>Date</th>
                                            <th title='Invoice'>Description</th>
                                            <th>Amount</th>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class='row'>
                        <div class='col-md-12'>
                            <div class='panel panel-red'>
                                <div class='panel-heading'>
                                    Payments
                                </div>
                                <div class='panel-body'>
                                    <table id='Table_Payments' class='display' cellspacing='0' width='100%'>
                                        <thead>
                                            <th title='Date'>Date</th>
                                            <th title='Invoice'>Description</th>
                                            <th>Amount</th>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class='' style='display:block;background-color:white;color:black;'>
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
                <h4 style='text-align:center;'><b><u>Invoice #<?php echo $data['ID'];?></u></b></h4>
                <div class='row'>&nbsp;</div>
                <div class='row' style='font-size:12px;'>
                    <div class='col-xs-2'><b>Bill To:</b></div>
                    <div class='col-xs-4'><?php echo $data['Customer_Name'];?></div>
                    <div class='col-xs-2' style='text-align:right;'><b>Account ID:</b></div>
                    <div class='col-xs-4'><?php echo $data['Location_Name'];?></div>
                </div>
                <div class='row' style='font-size:12px;'>
                    <div class='col-xs-2'>&nbsp;</div>
                    <div class='col-xs-4'>ATTN:<?php echo $data['Customer_Contact'];?></div>
                    <div class='col-xs-2'>&nbsp;</div>
                    <div class='col-xs-4'><?php echo $data['Location_Tag'];?></div>
                </div>
                <div class='row' style='font-size:12px;'>
                    <div class='col-xs-2'>&nbsp;</div>
                    <div class='col-xs-4'><?php echo $data['Customer_Street'];?></div>
                    <div class='col-xs-2' style='text-align:right;'><b>Invoice #:</b></div>
                    <div class='col-xs-4'><?php echo $data['ID'];?></div>
                </div>
                <div class='row' style='font-size:12px;'>
                    <div class='col-xs-2'>&nbsp;</div>
                    <div class='col-xs-4'><?php echo $data['Customer_City'] . ', ' . $data['Customer_State'] . ' ' . $data['Customer_Zip'];?></div>
                    <div class='col-xs-2' style='text-align:right;'><b>Amount:</b></div>
                    <div class='col-xs-4'><?php echo substr(money_format('%.2n',$data['Amount']),0);?></div>
                </div>
                <div class='row' style='font-size:12px;'>
                    <div class='col-xs-6'>&nbsp;</div>
                    <div class='col-xs-2' style='text-align:right;'><b>Paid:</b></div>
                    <div class='col-xs-4'><?php echo isset($data['Paid']) ? substr(money_format('%.2n',$data['Paid']),0) : '$0.00';?></div>
                </div>
                <div class='row'>&nbsp;</div>
                <div class='row' style='border:2px solid black;'>
                    <div class='col-xs-2' style='background-color:#9a9a9a !important;'><b>Date:</b></div>
                    <div class='col-xs-2' style='border-left:2px solid black;border-right:2px solid black;'><?php echo substr($data['fDate'],0,10);?></div>
                    <div class='col-xs-2' style='background-color:#9a9a9a !important;'><b>Terms:</b></div>
                    <div class='col-xs-2' style='border-left:2px solid black;border-right:2px solid black;'>Net 30 Days</div>
                    <div class='col-xs-2' style='background-color:#9a9a9a !important;'><b>Job:</b></div>
                    <div class='col-xs-2' style='border-left:2px solid black;'><a href='job.php?ID=<?php echo $data['Job_ID'];?>' style='color:black;text-decoration:none;'><?php echo $data['Job_ID'];?></a></div>
                    <div style='clear:both;'></div>
                </div>
                <div class='row' style='border-left:2px solid black;border-right:2px solid black;border-bottom:2px solid black;'>
                    <div class='col-xs-2' style='background-color:#9a9a9a !important;'><b>Amount:</b></div>
                    <div class='col-xs-2' style='border-left:2px solid black;border-right:2px solid black;'><?php echo substr(money_format('%.2n',$data['Total']),0);?></div>
                    <div class='col-xs-2' style='background-color:#9a9a9a !important;'><b>P.O. #:</b></div>
                    <div class='col-xs-2' style='border-left:2px solid black;border-right:2px solid black;'>&nbsp;</div>
                    <div class='col-xs-2' style='background-color:#9a9a9a !important;'><b>Type:</b></div>
                    <div class='col-xs-2' style='border-left:2px solid black;'><?php echo proper($data['Job_Type']);?></div>
                    <div style='clear:both;'></div>
                </div>
              <div class='row'>&nbsp;</div>
                <div class='row'>
                    <table id='Table_Invoice' cellpadding='5px' width='100%' style='border:2px solid black;'>
                        <thead>
                            <tr style='background-color:#9a9a9a;border-bottom:1px solid black;'>
                                <th style='text-align:center;background-color:#9a9a9a;padding:3px;'>Quantity</th>
                                <th style='text-align:center;background-color:#9a9a9a;padding:3px;'>Description</th>
                                <th style='text-align:center;background-color:#9a9a9a;padding:3px;'>Unit</th>
                                <th style='text-align:center;background-color:#9a9a9a;padding:3px;'>Price</th>
                                <th style='text-align:center;background-color:#9a9a9a;padding:3px;'>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan='2' style='border-right:1px solid black;'><pre><?php echo $data['Description'];?></pre></td>
                                <td style='text-align:center;border:1px solid black;'>hr.</td>
                                <td style='text-align:center;border:1px solid black;'><?php echo substr(money_format('%.2n',$data['Amount']),0);?></td>
                                <td style='text-align:center;border:1px solid black;'><?php echo substr(money_format('%.2n',$data['Total']),0);?></td>
                            </tr>
                            <tr>
                                <td colspan='2'>&nbsp;</td>
                                <td colspan='2' style='border:1px solid black;text-align:right;'>Taxable:</td>
                                <td style='border:1px solid black;text-align:center;'><?php echo substr(money_format('%.2n',$data['Taxable']),0);?></td>
                            </tr>
                            <tr>
                                <td colspan='2'>&nbsp;</td>
                                <td colspan='2' style='border:1px solid black;text-align:right;'>Non-Taxable:</td>
                                <td style='border:1px solid black;text-align:center;'><?php echo substr(money_format('%.2n',$data['Amount'] - $data['Taxable']),0);?></td>
                            </tr>
                            <tr>
                                <td colspan='2'>&nbsp;</td>
                                <td colspan='2' style='border:1px solid black;text-align:right;'><b>Sub-Total:</b></td>
                                <td style='border:1px solid black;text-align:center;'><?php echo substr(money_format('%.2n',$data['Amount']),0);?></td>
                            </tr>
                            <tr>
                                <td colspan='2'>&nbsp;</td>
                                <td colspan='2' style='border:1px solid black;text-align:right;'>Sales Tax:</td>
                                <td style='border:1px solid black;text-align:center;'><?php echo substr(money_format('%.2n',$data['STax']),0);?></td>
                            </tr>
                            <tr>
                                <td colspan='2'>&nbsp;</td>
                                <td colspan='2' style='border:1px solid black;text-align:right;'><b>Total:</b></td>
                                <td style='border:1px solid black;text-align:center;'><?php echo substr(money_format('%.2n',$data['Total']),0);?></td>
                            </tr>
                            <tr style='border-top:2px solid black;'><td colspan='5' style='padding:10px;text-align:center;'>Invoices not paid within terms may be subject to a service charge of 1.5% per month, or the maximum permitted by law.</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class='row' style='position:fixed;bottom:0;width:100%;'>
                    <h4 style='text-align:center;'><b>Nouveau Elevator Industries, Inc.</b></h4>
                    <div style='text-align:center;'>47-55 37th Street LIC, NY 11101 TEL:718.349.4700 FAX: 718.383.3218</div>
                </div>
           </div>
        </div>
    </div>
<?php require(PROJECT_ROOT.'php/js/chart/invoice_history.php');?>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=invoice<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? 's.php' : '.php?ID={$_GET['ID']}';?>';</script></head></html><?php }?>
