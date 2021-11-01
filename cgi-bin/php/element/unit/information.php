<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( ); 
    require( '/var/www/nouveautexas.com/html/portal/cgi-bin/php/index.php' );
}
if( isset($_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
    //Connection
    $Connection = sqlsrv_query(
        $NEI,
        "   SELECT  Connection.* 
            FROM    Connection 
            WHERE   Connection.Connector = ? 
                    AND Connection.Hash = ?;",
        array(
            $_SESSION['User'],
            $_SESSION['Hash']
        )
    );
    $Connection = sqlsrv_fetch_array($Connection);

    //User
    $User = sqlsrv_query(
        $NEI,
        "   SELECT  Emp.*, 
                    Emp.fFirst  AS First_Name, 
                    Emp.Last    AS Last_Name 
            FROM    Emp 
            WHERE   Emp.ID = ?;",
        array(
            $_SESSION['User']
        )
    );
    $User = sqlsrv_fetch_array($User);

    //Privileges
    $r = sqlsrv_query(
        $NEI,
        "   SELECT  Privilege.Access_Table, 
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
    while($Privilege = sqlsrv_fetch_array($r)){$Privileges[$Privilege['Access_Table']] = $Privilege;}
    $Privileged = FALSE;
    if( isset($Privileges['Unit']) 
        && $Privileges['Unit']['User_Privilege'] >= 4 
        && $Privileges['Unit']['Group_Privilege'] >= 4 
        && $Privileges['Unit']['Other_Privilege'] >= 4){$Privileged = TRUE;}
    elseif($Privileges['Unit']['User_Privilege'] >= 4 && is_numeric($_GET['ID'])){
        $r = sqlsrv_query(  
            $NEI,
            "   SELECT  Count( Ticket.Count ) AS Count 
                FROM    (
                            SELECT  Ticket.Unit,
                                    Ticket.Field,
                                    Sum( Ticket.Count ) AS Count
                            FROM (
                                (
                                    SELECT      TicketO.LElev AS Unit,
                                                TicketO.fWork AS Field,
                                                Count( TicketO.ID ) AS Count
                                    FROM        TicketO
                                    GROUP BY    TicketO.LElev,
                                                TicketO.fWork
                                ) UNION ALL (
                                    SELECT      TicketD.Elev AS Unit,
                                                TicketD.fWork AS Field, 
                                                Count( TicketD.ID ) AS Count
                                    FROM        TicketD
                                    GROUP BY    TicketD.Elev,
                                                TicketD.fWork
                                )
                            ) AS Ticket
                            GROUP BY    Ticket.Unit,
                                        Ticket.Field
                        ) AS Ticket
                        LEFT JOIN Emp AS Employee ON Ticket.Field = Employee.fWork
                WHERE   Employee.ID = ?
                        AND Ticket.Unit = ?;",
            array( 
                $_SESSION[ 'User' ],
                $_GET[ 'ID' ]
            )
        );
        $Tickets = 0;
        if ( $r ){ $Tickets = sqlsrv_fetch_array( $r )[ 'Count' ]; }
        $Privileged =  $Tickets > 0 ? true : false;
    }
    if(     !isset( $Connection[ 'ID' ] )  
        ||  !$Privileged 
        ||  !is_numeric( $_GET[ 'ID' ] ) ){
            /*?><html><head><script>document.location.href="../login.php?Forward=unit<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php*/ }
    else {
        $r = sqlsrv_query($NEI,
          " SELECT  TOP 1
                    Elev.ID,
                    Elev.Unit           AS Building_ID,
                    Elev.State          AS City_ID,
                    Elev.Cat            AS Category,
                    Elev.Type           AS Type,
                    Elev.Building       AS Building,
                    Elev.Since          AS Since,
                    Elev.Last           AS Last,
                    Elev.Price          AS Price,
                    Elev.fDesc          AS Description,
                    Loc.Loc             AS Location_ID,
                    Loc.ID              AS Name,
                    Loc.Tag             AS Tag,
                    Loc.Tag             AS Location_Tag,
                    Loc.Address         AS Street,
                    Loc.City            AS City,
                    Loc.State           AS Location_State,
                    Loc.Zip             AS Zip,
                    Loc.Route           AS Route,
                    Zone.Name           AS Zone,
                    Owner.ID            AS Customer_ID,
                    OwnerRol.Name       AS Customer_Name,
                    OwnerRol.Contact    AS Customer_Contact,
                    OwnerRol.Address    AS Customer_Street,
                    OwnerRol.City       AS Customer_City,
                    OwnerRol.State      AS Customer_State,
                    Emp.ID              AS Route_Mechanic_ID,
                    Emp.fFirst          AS Route_Mechanic_First_Name,
                    Emp.Last            AS Route_Mechanic_Last_Name
            FROM    Elev
                    LEFT JOIN Loc               ON Elev.Loc     = Loc.Loc
                    LEFT JOIN Zone              ON Loc.Zone     = Zone.ID
                    LEFT JOIN OwnerWithRol      ON Loc.Owner    = OwnerWithRol.ID
                    LEFT JOIN Route             ON Loc.Route    = Route.ID
                    LEFT JOIN Emp               ON Route.Mech   = Emp.fWork
                    LEFT JOIN Owner             ON Loc.Owner    = Owner.ID 
                    LEFT JOIN Rol AS OwnerRol   ON OwnerRol.ID  = Owner.Rol
            WHERE   Elev.ID = ?;",
          array(
            $_GET[ 'ID' ]
          )
        );
        $Unit = sqlsrv_fetch_array($r);
        $r = sqlsrv_query(
          $NEI,
          " SELECT  *
            FROM    ElevTItem
            WHERE   ElevTItem.ElevT    = 1
                    AND ElevTItem.Elev = ?;",
          array(
            $_GET[ 'ID' ]
          )
        );
        if( $r ){while( $array = sqlsrv_fetch_array( $r ) ){ $Unit[ $array[ 'fDesc' ] ] = $array[ 'Value' ]; } }
?><!DOCTYPE html>
            <div class="panel panel-primary">
                <div class="panel-heading"><h4><?php $Icons->Info( 1 );?> Information</h4></div>
                <div class='panel-body'>
                    <div class='row'>
                        <div class='col-xs-4'><?php $Icons->Description( 1 );?> City ID:</div>
                        <div class='col-xs-8'><?php echo strlen( $Unit[ 'City_ID' ] ) > 0 ? $Unit[ 'City_ID' ] : "&nbsp;";?></div>
          </div>
          <div class='row'>
                        <div class='col-xs-4'><?php $Icons->Blank( 1 );?> Building ID:</div>
                        <div class='col-xs-8'><?php echo strlen( $Unit[ 'Building_ID' ] ) > 0 ? $Unit[ 'Building_ID' ] : "&nbsp;";?></div>
          </div>
          <div class='row'>
                        <div class='col-xs-4'><?php $Icons->Unit( 1 );?> Type:</div>
                        <div class='col-xs-8'><?php echo strlen($Unit[ 'Type' ])>0 ? $Unit[ 'Type' ] : "&nbsp;";?></div>
          </div>
          <div class='row'>
                        <?php if( isset( $Privileges[ 'Invoice' ] ) && $Privileges[ 'Invoice' ][ 'Other_Privilege' ] >= 4){
              ?><div class='col-xs-4'><?php $Icons->Collection(1);?> Price:</div>
                          <div class='col-xs-8'><?php echo strlen($Unit[ 'Price' ])>0 ? money_format( '%.2n', $Unit[ 'Price' ] ): "&nbsp;";?></div><?php 
            }?>
          </div>
          <div class='row'>
                        <div class='col-xs-4'><?php $Icons->Note( 1 );?> Notes:</div>
                        <div class='col-xs-8'><?php echo strlen( $Unit[ 'Description'] )>0 ? $Unit[ 'Description' ] : "&nbsp;";?></div>
                    </div>
        </div>
        <div class="panel-heading"><h4><?php $Icons->Location( 1 );?> Location</h4></div>
        <div class='panel-body'>
                    <div class='row'>
                        <div class='col-xs-4'><?php $Icons->Location(1);?> Name:</div>
                        <div class='col-xs-8'><?php 
              echo $Privileges['Location']['Other_Privilege'] >= 4 
                    ?   "<div class='row'><div class='col-xs-8'><input disabled type='text' value='" . proper( $Unit['Location_Tag'] ) . "' /></div><div class='col-xs-4'><button onClick=\"document.location.href='location.php?ID=" . $Unit['Location_ID'] . "';\"><i class='fa fa-search fa-fw fa-1x'></i></button></div></div>"
                    :   proper( $Unit['Location_Tag'] );
            ?></div>
          </div>
          <div class='row'>
                        <div class='col-xs-4'><?php $Icons->Blank(1);?> Street:</div>
                        <div class='col-xs-8'><?php echo strlen( $Unit[ 'Street' ]) > 0 ? $Unit[ 'Street' ] : "&nbsp;";?></div>
          </div>
          <div class='row'>
                        <div class='col-xs-4'><?php $Icons->Blank(1);?> City:</div>
                        <div class='col-xs-8'><?php echo strlen( $Unit[ 'City' ] ) > 0 ? $Unit[ 'City' ] : "&nbsp;";?></div>
          </div>
          <div class='row'>
                        <div class='col-xs-4'><?php $Icons->Blank(1);?> State:</div>
                        <div class='col-xs-8'><?php echo strlen( $Unit[ 'Location_State' ] ) > 0 ? $Unit[ 'Location_State' ] : "&nbsp;";?></div>
          </div>
          <div class='row'>
                        <div class='col-xs-4'><?php $Icons->Blank(1);?> Zip:</div>
                        <div class='col-xs-8'><?php echo strlen( $Unit[ 'Zip' ] ) > 0 ? $Unit[ 'Zip' ] : "&nbsp;";?></div>
          </div>
                </div>
        <div class="panel-heading"><h4><?php $Icons->Customer( 1 );?> Customer</h4></div>
        <div class='panel-body'>
                <div class='row'>
                    <div class='col-xs-4'><?php $Icons->Customer(1);?> Name: </div>
                    <div class='col-xs-8'><?php 
              echo $Privileges['Customer']['Other_Privilege'] >= 4 
                    ?   "<div class='row'><div class='col-xs-8'><input disabled type='text' value='" . proper( $Unit['Customer_Name'] ) . "' /></div><div class='col-xs-4'><button onClick=\"document.location.href='customer.php?ID=" . $Unit['Customer_ID'] . "';\"><i class='fa fa-search fa-fw fa-1x'></i></button></div></div>"
                    :   proper( $Unit['Customer_Name'] );
            ?></div>
          </div>
          <div class='row'>
                    <div class='col-xs-4'><?php $Icons->Blank(1);?> Contact:</div>
                    <div class='col-xs-8'><?php echo $Unit['Customer_Contact'];?></div>
          </div>
          <div class='row'>
                    <div class='col-xs-4'><?php $Icons->Blank(1);?> Street:</div>
                    <div class='col-xs-8'><?php echo $Unit['Customer_Street'];?></div>
                </div>
          <div class='row'>
                    <div class='col-xs-4'><?php $Icons->Blank(1);?> City:</div>
                    <div class='col-xs-8'><?php echo $Unit['Customer_City'];?></div>
                </div>
          <div class='row'>
                    <div class='col-xs-4'><?php $Icons->Blank(1);?> State:</div>
                    <div class='col-xs-8'><?php echo $Unit['Customer_State'];?></div>
                </div>
            </div>
      </div>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
