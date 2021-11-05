<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/cgi-bin/php/index.php' );
}
if(isset(
  $_SESSION['User'],
  $_SESSION['Hash'] ) ) {
        $result = sqlsrv_query(
          $NEI,
            "   SELECT  *
                FROM    Connection
                WHERE   Connector = ?
                        AND Hash = ?;",
        array(
          $_SESSION['User'],
          $_SESSION['Hash']
      )
  );
        $array = sqlsrv_fetch_array($result);
        $result= sqlsrv_query(
          $NEI,
          "   SELECT    *, fFirst
              AS        First_Name, Last as Last_Name
              FROM Emp
              WHERE ID= ?",
          array(
            $_SESSION[ 'User' ]
      )
  );
        $User = sqlsrv_fetch_array($result);
        $Field = ($User[ 'Field' ] == 1 && $User[ 'Title' ] != "OFFICE") ? True : False;
        $result = sqlsrv_query(
          $Portal,
          "   SELECT Access_Table,
                     User_Privilege,
                     Group_Privilege,
                     Other_Privilege
              FROM   Privilege
              WHERE  User_ID = ?;",
        array(
          $_SESSION[ 'User' ]
      )
  );
    $Privileges = array();
    while($array2 = sqlsrv_fetch_array($result)){$Privileges[$array2[ 'Access_Table' ]] = $array2;}
    $Privileged = FALSE;
    if(isset($Privileges[ 'Proposal' ]) && $Privileges[ 'Proposal' ][ 'User_Privilege' ] >= 6 && $Privileges[ 'Proposal' ][ 'Group_Privilege' ] >= 4 && $Privileges[ 'Proposal' ][ 'Other_Privilege' ] >= 4){$Privileged = TRUE;}
    else {
        //NEEDS TO INCLUDE SECURITY FOR OTHER PRIVILEGE
    }
    sqlsrv_query(
      $Portal,
      "   INSERT INTO Activity([User], [Date], [Page])
          VALUES(?,?,?);",
      array(
        $_SESSION['User'],
                  date("Y-m-d H:i:s"),
                      "proposal.php"));
    if(!isset($array[ 'ID' ]) || !is_numeric($_GET[ 'ID' ])  || !$Privileged){?><html><head><script></script></head></html><?php }
    else {
        $ID = $_GET['ID'];
        $result = sqlsrv_query(
            $NEI,
            "   SELECT  TOP 1
                        Estimate.ID             AS  ID,
                        Estimate.Name           AS  Contact,
                        Estimate.fDesc          AS  Title,
                        Estimate.fDate          AS  Date,
                        Estimate.Cost           AS  Cost,
                        Estimate.Price          AS  Price,
                        EStimate.Remarks        AS  Remarks,
                        Loc.Tag                 AS  Location,
                        Loc.Address             AS  Street,
                        Loc.State               AS  State,
                        Loc.City                AS  City,
                        Loc.Zip                 AS  Zip,
                        Customer.Name       AS  Customer,
                        Rol.Fax                 AS  Fax,
                        Rol.Phone               AS  Phone,
                        Rol.EMail               AS  Email
                FROM    Estimate
                        LEFT JOIN Loc           ON  Estimate.LocID  = Loc.Loc
                        LEFT JOIN (
                              SELECT  Owner.ID,
                                      Rol.Name,
                                      Owner.Status
                              FROM    Owner
                                      LEFT JOIN Rol ON Owner.Rol = Rol.ID
                          ) AS Customer ON Loc.Owner = Customer.ID
                        LEFT JOIN Rol           ON  Rol.ID          = Estimate.RolID
                WHERE   Estimate.ID = ?;",
            array(
                $_GET[ 'ID' ]
            )
        );
        $Estimate = sqlsrv_fetch_array( $result );
?><!DOCTYPE html>
<html lang="en">
<head>
    <?php require(bin_meta.'index.php');?>
    <title>Nouveau Elevator Portal</title>
    <?php require(bin_css.'index.php');?>
    <?php require(bin_js.'index.php');?>
</head>
<body onload="finishLoadingPage();">
    <div id="wrapper" class="<?php echo isset($_SESSION[ 'Toggle_Menu' ]) ? $_SESSION[ 'Toggle_Menu' ] : null;?>">
        <?php require(bin_php.'element/navigation/index.php');?>
        <?php require(bin_php.'element/loading.php');?>
        <div id="page-wrapper" class='content' style='background-color : white !important; color : black !important;'>
            <div class='row'>
                <div class='col-xs-12' style='text-align:center;'>
                    <img src='cgi-bin/media/logo/nouveau-no-white.jpg' height='150px' />
                </div>
                <!--<div class='col-xs-12'><h1 style='text-align:center;'><b class='BankGothic' >Nouveau Elevator</b></h1></div>-->
            </div>
            <div class='row' style='border-top:1px solid black;margin-top:5px;margin-bottom:5px;height:5px;'>&nbsp;</div>
            <div class='row'>
                <div class='col-xs-12' style='b'><h3 style='text-align:center;margin:0px;padding:5px;'>Proposal #<?php echo $_GET['ID'];?></h3></div>
            </div>
            <div class='row' style='border-top:1px solid black;margin-top:5px;margin-bottom:5px;height:5px;'>&nbsp;</div>
            <div class='row' style=''>
                <div class='col-xs-4'>OFFICE (718) 349-4700</div>
                <div class='col-xs-4'>FAX (718) 349-8932</div>
                <div class='col-xs-4'>proposal@nouveauelevator.com</div>
            </div>
            <div class='row' style='border-top:1px solid black;margin-top:5px;margin-bottom:5px;height:5px;'>&nbsp;</div>
            <div class='row'>
                <div class='col-xs-2'>ATTN:</div>
                <div class='col-xs-4'><?php echo $Estimate[ 'Contact' ];?></div>
                <div class='col-xs-6'>PROPOSAL #<?php echo $_GET['ID'];?></div>
            </div>
            <div lcass='row'>
                <div class='col-xs-2'>PHONE:</div>
                <div class='col-xs-4'><?php echo $Estimate[ 'Phone' ];?></div>
                <div class='col-xs-6'><?PHP echo date( 'm/d/Y', strtotime( $Estimate[ 'Date' ] ) );?></div>
            </div>
            <div class='row'>
                <div class='col-xs-2'>FAX:</div>
                <div class='col-xs-4'><?php echo $Estimate[ 'Fax' ];?></div>
                <div class='col-xs-6'>&nbsp;</div>
            </div>
            <div class='row'>
                <div class='col-xs-2'>EMAIL:</div>
                <div class='col-xs-4'><?php echo $Estimate[ 'Email' ];?></div>
                <div class='col-xs-6'>&nbsp;</div>
            </div>
            <div class='row'>
                <div class='col-xs-2'>FROM:</div>
                <div class='col-xs-4'><?php /*INSERT FROM HERE*/?></div>
                <div class='col-xs-6'>&nbsp;</div>
            </div>
            <div class='row'>
                <div class='col-xs-2'>PREMISE:</div>
                <div class='col-xs-4'><?php echo $Estimate[ 'Location' ];?></div>
                <div class='col-xs-6'>&nbsp;</div>
            </div>
            <div class='row'>
                <div class='col-xs-2'>CUSTOMER:</div>
                <div class='col-xs-4'><?php echo $Estimate[ 'Customer' ];?></div>
                <div class='col-xs-6'>&nbsp;</div>
            </div>
            <div class='row'>
                <div class='col-xs-2'>RE:</div>
                <div class='col-xs-4'><?php echo $Estimate[ 'Title'];?></div>
                <div class='col-xs-6'>&nbsp;</div>
            </div>
            <div class='row' style='border-top:1px solid black;margin-top:5px;margin-bottom:5px;height:5px;'>&nbsp;</div>
            <div class='row'>
                <div class='col-xs-12' style='text-align:center;'><u>WORK DESCRIPTION</u></div>
            </div>
            <div class='row'>
                <div class='col-xs-12'><pre style='padding:25px;font-size:18px;'><?php echo $Estimate[ 'Remarks' ];?></pre></div>
            </div>
            <div class='row'>
                <div class='col-xs-3'>COST NOT TO EXCEED:</div>
                <div class='col-xs-9'>$<?php echo number_format( $Estimate[ 'Price' ], 2 );?> - PLUS ANY APPlICABLE TAXES</div>
            </div>
            <div class='row' style='border-top:1px solid black;margin-top:5px;margin-bottom:5px;height:5px;'>&nbsp;</div>
            <div class='row'>
                <div class='col-xs-12' style='text-align:center;'>THIS PROPOSAL IS VALID FOR 180 DAYS FROM DATE ABOVE</div>
            </div>
            <div class='row'>
                <div class='col-xs-12' style='text-align:center;'>"IF ADDITIONAL WORK IS NEEDED OTHER THAN STATED YOU WILL BE INFORMED IMMEDIATELY"</div>
            </div>
            <div class='row'>
                <div class='col-xs-12' style='text-align:center;'>"WORK TO BE PERFORMED DURING REGULAR HOURS UNLESS OTHERWISE STATED</div>
            </div>
            <div class='row' style='border-top:1px solid black;margin-top:5px;margin-bottom:5px;height:5px;'>&nbsp;</div>
            <div class='row'>
                <div class='col-xs-12' style='text-align:center;'>AUTHORIZATION TO PROCEED WITH WORK AND TERMS DESCRIBED ABOVE</div>
            </div>
            <div class='row'>
                <div class='col-xs-5' style='text-align:right;'>P.O.#</div>
                <div class='col-xs-4' style='border-bottom:1px solid black;'>&nbsp;</div>
            </div>
            <div class='row'>
                <div class='col-xs-5' style='text-align:right;'>NAME</div>
                <div class='col-xs-4' style='border-bottom:1px solid black;'>&nbsp;</div>
            </div>
            <div class='row'>
                <div class='col-xs-5' style='text-align:right;'>TITLE & DATE</div>
                <div class='col-xs-4' style='border-bottom:1px solid black;'>&nbsp;</div>
            </div>
            <div class='row'>
                <div class='col-xs-5' style='text-align:right;'>AUTHORIZED SIGNATURE</div>
                <div class='col-xs-4' style='border-bottom:1px solid black;'>&nbsp;</div>
            </div>
            <div class='row' style='border-top:1px solid black;margin-top:5px;margin-bottom:5px;height:5px;'>&nbsp;</div>
            <div class='row'>
                <div class='col-xs-12' style='text-align:center;'>PLEASE RETURN SIGNED FORM BY FAX WITH YOUR APPROVAL TO PROCEED AS DESCRIBED</div>
            </div>
        </div>
    </div>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=proposal<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
