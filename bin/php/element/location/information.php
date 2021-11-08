<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [ 'read_and_close' => true ] ); 
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/bin/php/index.php' );
}
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    //Connection
    $Connection = $database->query(
        null,
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
    $User = $database->query(
        null,
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
    $r = $database->query(
        null,
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
    if( isset($Privileges['Location']) 
        && $Privileges['Location']['User_Privilege'] >= 4 
        && $Privileges['Location']['Group_Privilege'] >= 4 
        && $Privileges['Location']['Other_Privilege'] >= 4){$Privileged = TRUE;}
    elseif($Privileges['Location']['User_Privilege'] >= 4 && is_numeric($_GET['ID'])){
        $r = $database->query(  
            null,
            "   SELECT  Count( Ticket.ID ) AS Count 
                FROM    (
                            SELECT  Ticket.ID,
                                    Ticket.Location,
                                    Ticket.Field,
                                    Sum( Ticket.Count ) AS Count
                            FROM (
                                (
                                    SELECT      TicketO.ID,
                                                TicketO.LID AS Location,
                                                TicketO.fWork AS Field,
                                                Count( TicketO.ID ) AS Count
                                    FROM        TicketO
                                    GROUP BY    TicketO.ID,
                                                TicketO.LID,
                                                TicketO.fWork
                                ) UNION ALL (
                                    SELECT      TicketD.ID,
                                                TicketD.Loc AS Location,
                                                TicketD.fWork AS Field, 
                                                Count( TicketD.ID ) AS Count
                                    FROM        TicketD
                                    GROUP BY    TicketD.ID,
                                                TicketD.Loc,
                                                TicketD.fWork
                                )
                            ) AS Ticket
                            GROUP BY    Ticket.ID,
                                        Ticket.Location,
                                        Ticket.Field
                        ) AS Ticket
                        LEFT JOIN Emp AS Employee ON Ticket.Field = Employee.fWork
                WHERE   Employee.ID = ?
                        AND Ticket.Location = ?;",
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
            /*?><html><head><script>document.location.href="https://beta.nouveauelevator.com/login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php*/ }
    else {
        $database->query(
            null,
            "   INSERT INTO Activity([User], [Date], [Page]) 
                VALUES(?,?,?);",
            array(
                $_SESSION['User'],
                date('Y-m-d H:i:s'), 
                'location/information.php?ID=' . $_GET[ 'ID' ]
            )
        );
        $r = $database->query(
            null,
            "SELECT TOP 1
                    Loc.Loc              AS Location_ID,
                    Loc.ID               AS Location_Name,
                    Loc.Tag              AS Location_Tag,
                    Loc.Address          AS Location_Street,
                    Loc.City             AS Location_City,
                    Loc.State            AS Location_State,
                    Loc.Zip              AS Location_Zip,
                    Loc.Balance          AS Location_Balance,
                    Loc.Latt             AS Latitude,
                    Loc.fLong            AS Longitude,
                    Zone.Name            AS Division,
                    Zone.ID              AS Division_ID,
                    Loc.Route            AS Route_ID,
                    Emp.ID               AS Route_Mechanic_ID,
                    Emp.fFirst           AS Route_Mechanic_First_Name,
                    Emp.Last             AS Route_Mechanic_Last_Name,
                    Loc.Owner            AS Customer_ID,
                    OwnerRol.Name        AS Customer_Name,
                    OWner.Balance        AS Customer_Balance,
                    OwnerRol.Address     AS Customer_Street,
                    OwnerRol.City        AS Customer_City,
                    OwnerRol.State       AS Customer_State,
                    OwnerRol.Zip         AS Customer_Zip,
                    OwnerRol.Contact     AS Customer_Contact,
                    Terr.Name            AS Territory_Domain,
                    Terr.Name            AS Territory_Name,
                    Loc.Custom8          AS Resident_Mechanic,
                    Units.Count          AS Units
            FROM    Loc
                    LEFT JOIN Zone              ON Loc.Zone    = Zone.ID
                    LEFT JOIN Route             ON Loc.Route   = Route.ID
                    LEFT JOIN Emp               ON Route.Mech  = Emp.fWork
                    LEFT JOIN Owner             ON Owner.ID    = Loc.Owner
                    LEFT JOIN Rol AS OwnerRol   ON OwnerRol.ID = Owner.Rol
                    LEFT JOIN Terr              ON Terr.ID     = Loc.Terr
                    LEFT JOIN Rol               ON Emp.Rol     = Rol.ID
                    LEFT JOIN (
                        SELECT      Elev.Loc AS Location,
                                    Count( Elev.ID ) AS Count
                        FROM        Elev
                        GROUP BY    Elev.Loc
                    ) AS Units ON Units.Location = Loc.Loc
            WHERE   Loc.Loc = ?;",
            array(
                $_GET[ 'ID' ]
            )
        );
        $Location = sqlsrv_fetch_array($r);
?><div class='panel panel-primary' id='location/information'>
    <div class='panel-heading'><?php \singleton\fontawesome::getInstance( )->Customer( 1 );?> Customer</h4></div>
    <div class='panel-body'>
        <div class='row'>
            <div class='col-xs-3'><?php \singleton\fontawesome::getInstance( )->Blank( 1 );?> Name:</div>
            <div class='col-xs-9'><?php
                echo $Privileges['Customer']['Other_Privilege'] >= 4
                    ?   "<div class='row'><div class='col-xs-9'><input disabled type='text' value='" . proper( $Location['Customer_Name'] ) . "' /></div><div class='col-xs-3'><button onClick=\"document.location.href='division.php?ID=" . $Location['Customer_ID'] . "';\"><i class='fa fa-search fa-fw fa-1x'></i></button></div></div>"
                    :   proper( $Location['Customer_Name'] );
            ?></div>
        </div>
    </div>
    <?php if( !in_array( Location[ 'Latitude' ], array( null, 0 ) ) && !in_array( $Location['Longitude' ], array( null, 0 ) ) ){
        ?><div class='panel-heading'><?php \singleton\fontawesome::getInstance( )->Map( 1 );?> Map</div>
        <div class='panel-body'>
            <div class='row'><div id='map'>&nbsp;</div></div>
            <script type="text/javascript">
                var map;
                function initialize() {
                     map = new google.maps.Map(
                        document.getElementById( 'map' ),
                        {
                          zoom: 10,
                          center: new google.maps.LatLng( 29.481137, -98.7945945 ),
                          mapTypeId: google.maps.MapTypeId.ROADMAP
                        }
                    );
                    var markers = [];
                    markers[0] = new google.maps.Marker({
                        position: {
                            lat:<?php echo $Location['Latitude'];?>,
                            lng:<?php echo $Location['Longitude'];?>
                        },
                        map: map,
                        title: '<?php echo $Location[ 'Location_Name' ];?>'
                    });
                }
                $(document).ready(function(){ initialize(); });
            </script>
        </div>
    <?php }?>
    <div class='panel-heading'><?php \singleton\fontawesome::getInstance( )->Location( 1 );?> Location</div>
    <div class='panel-body'>
        <div class='row'>
            <div class='col-xs-3'><?php \singleton\fontawesome::getInstance( )->Address(1);?> Street:</div>
            <div class='col-xs-9'><input disabled type='text' name='Street' value='<?php echo strlen($Location['Location_Street']) ? $Location['Location_Street'] : "&nbsp;";?>' /></div>
        </div>
        <div class='row'>
            <div class='col-xs-3'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> City:</div>
            <div class='col-xs-9'><input disabled type='text' name='City' value='<?php echo strlen($Location['Location_City']) ? $Location['Location_City'] : "&nbsp;";?>' /></div>
        </div>
        <div class='row'>
            <div class='col-xs-3'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> State:</div>
            <div class='col-xs-9'><input disabled type='text' name='State' value='<?php echo strlen($Location['Location_State']) ? $Location['Location_State'] : "&nbsp;";?>' /></div>
        </div>
        <div class='row'>
            <div class='col-xs-3'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Zip:</div>
            <div class='col-xs-9'><input disabled type='text' name='Zip' value='<?php echo strlen($Location['Location_Zip']) ? $Location['Location_Zip'] : "&nbsp;";?>' /></div>
        </div>
    </div>
    <div class='panel-heading'><?php \singleton\fontawesome::getInstance( )->Maintenance( 1 );?> Operations</div>
    <div class='panel-body'>
        <div class='row'> 
            <div class='col-xs-3'><?php \singleton\fontawesome::getInstance( )->Route();?> Route:</div>
            <div class='col-xs-9'><?php  
                echo $Privileges['Route']['Other_Privilege'] >= 4 || $User[ 'ID' ] == $Location['Route_Mechanic_ID'] 
                    ?   "<div class='row'><div class='col-xs-9'><input disabled type='text' value='" . proper( $Location['Route_Mechanic_First_Name'] . ' ' . $Location['Route_Mechanic_Last_Name'] ) . "' /></div><div class='col-xs-3'><button onClick=\"document.location.href='route.php?ID=" . $Location['Route_ID'] . "';\"><i class='fa fa-search fa-fw fa-1x'></i></button></div></div>"
                    :   proper( $Location['Route_Mechanic_First_Name'] . ' ' . $Location['Route_Mechanic_Last_Name'] );
            ?></div>
        </div>
        <div class='row'>
            <div class='col-xs-3'><?php \singleton\fontawesome::getInstance( )->Resident(1);?> Resident:</div> 
            <div class='col-xs-9'><input disabled type='text' name='Resident' value='<?php echo isset($Location['Resident_Mechanic']) && $Location['Resident_Mechanic'] != '' ? proper($Location['Resident_Mechanic']) : "No";?>' /></div>
        </div>
        <div class='row'>
            <div class='col-xs-3'><?php \singleton\fontawesome::getInstance( )->Division(1);?> Division:</div>
            <div class='col-xs-9'><?php 
                echo $Privileges['Division']['Other_Privilege'] >= 4
                    ?   "<div class='row'><div class='col-xs-9'><input disabled type='text' value='" . proper( $Location['Division'] ) . "' /></div><div class='col-xs-3'><button onClick=\"document.location.href='division.php?ID=" . $Location['Division_ID'] . "';\"><i class='fa fa-search fa-fw fa-1x'></i></button></div></div>"
                    :   "<input disabled type='text' name='Division' value='" . proper( $Location['Division'] ) . "' />";?></div>
        </div>
        <div class='row'>
            <div class='col-xs-3'><?php \singleton\fontawesome::getInstance( )->Unit(1);?> Units</div>
            <div class='col-xs-9'><?php
                $r = $database->query(
                    null,
                    "   SELECT  Count(Unit.ID) AS Count 
                        FROM    Elev AS Unit
                        WHERE   Unit.Loc = ?;", 
                    array( 
                        $_GET[ 'ID' ] 
                    ) 
                );
                echo $r 
                    ?   "<div class='row'><div class='col-xs-9'><input disabled type='text' value='" . number_format(sqlsrv_fetch_array($r)['Count']) . "' /></div><div class='col-xs-3'><button tab='units' onClick=\"linkTab('units');\"><i class='fa fa-search fa-fw fa-1x'></i></button></div></div>" 
                    :   0;
            ?></div>
        </div>
        <div class='row'>
            <div class='col-xs-3'><?php \singleton\fontawesome::getInstance( )->Job(1);?> Jobs</div>
            <div class='col-xs-9'><?php
                $r = $database->query(
                    null,
                    "   SELECT  Count(Job.ID) AS Count 
                        FROM    Job 
                        WHERE   Job.Loc = ?;",
                    array( 
                        $_GET[ 'ID' ] 
                    )
                );
                echo $r 
                    ?   "<div class='row'><div class='col-xs-9'><input disabled type='text' value='" . number_format(sqlsrv_fetch_array($r)['Count']) . "' /></div><div class='col-xs-3'><button onClick=\"linkTab('jobs');\"><i class='fa fa-search fa-fw fa-1x'></i></button></div></div>" 
                    :   0;
            ?></div>
        </div>
        <div class='row'>
            <div class='col-xs-3'><?php \singleton\fontawesome::getInstance( )->Violation(1);?> Violations</div>
            <div class='col-xs-9'><?php
                $r = $database->query(
                    null,
                    "   SELECT  Count(ID) AS Count 
                        FROM    Violation 
                        WHERE   Violation.Loc = ?;",
                    array( 
                        $_GET[ 'ID' ]
                    )
                );
                echo $r 
                    ?   "<div class='row'><div class='col-xs-9'><input disabled type='text' value='" . number_format(sqlsrv_fetch_array($r)['Count']) . "' /></div><div class='col-xs-3'><button onClick=\"linkTab('violations');\"><i class='fa fa-search fa-fw fa-1x'></i></button></div></div>" 
                    :   0;
            ?></div>
        </div>
        <div class='row'>
            <div class='col-xs-3'><?php \singleton\fontawesome::getInstance( )->Ticket(1);?> Tickets</div>
            <div class='col-xs-9'><?php
                $r = $database->query(
                    null,
                    "   SELECT  Count(Ticket.ID) AS Count
                        FROM    TicketO AS Ticket
                        WHERE   Ticket.LID = ?;",
                    array(
                        $_GET[ 'ID' ]
                    )
                ); 
                echo $r 
                    ?   "<div class='row'><div class='col-xs-9'><input disabled type='text' value='" . number_format(sqlsrv_fetch_array($r)['Count']) . "' /></div><div class='col-xs-3'><button onClick=\"linkTab('tickets');\"><i class='fa fa-search fa-fw fa-1x'></i></button></div></div>" 
                    :   0;
            ?></div>
        </div>
    </div>
    <?php if(isset($Privileges['Finances']) && $Privileges['Finances']['Other_Privilege'] >= 4){?>
    <div class='panel-heading'><h4><?php \singleton\fontawesome::getInstance( )->Blank( 1 );?> Sales</h4></div>
    <div class='panel-body'>
        <div class='row'>
            <div class='col-xs-3'><?php \singleton\fontawesome::getInstance( )->Territory(1);?> Territory:</div>
            <div class='col-xs-9'>
                <div class='row'>
                    <div class='col-xs-9'><input disabled type='text' value='<?php echo $Location[ 'Territory_Name'];?>' /></div>
                    <div class='col-xs-3'>
                        <button onClick="someFunction(this,'proposals.php?ID=<?php echo $Location['Location_ID'];?>">
                            <i class='fa fa-search fa-fw fa-1x'></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class='row'>
            <div class='col-xs-3'><?php \singleton\fontawesome::getInstance( )->Proposal(1);?> Proposals</div>
            <div class='col-xs-9'><?php 
                $r = $database->query(null,"
                    SELECT Count(Estimate.ID) AS Count 
                    FROM   Estimate
                    WHERE  Estimate.LocID = ?
                ;",array($_GET['ID']));
                echo $r 
                    ?   "<div class='row'><div class='col-xs-9'><input disabled type='text' value='" . number_format(sqlsrv_fetch_array($r)['Count']) . "' /></div><div class='col-xs-3'><button onClick=\"someFunction(this,'proposals.php?ID=" . $Location['Location_ID'] . "');\"><i class='fa fa-search fa-fw fa-1x'></i></button></div></div>" 
                    :   0;
            ?></div>
        </div>
        <div class='row'>
            <div class='col-xs-3'><?php \singleton\fontawesome::getInstance( )->Collection(1);?> Balance:</div>
            <div class='col-xs-9'><input disabled type='text' name='Balance' value='<?php echo isset($Location['Location_Balance']) && $Location['Location_Balance'] != '' ? money_format('%.2n',$Location['Location_Balance']) : "&nbsp;";?>' /></div>
        </div>
        <div class='row'>
            <div class='col-xs-3'><?php \singleton\fontawesome::getInstance( )->Invoice(1);?> Collection</div>
            <div class='col-xs-9'><?php 
                $r = $database->query(
                    null,
                    "   SELECT  Count( OpenAR.Ref ) AS Count
                        FROM    OpenAR
                                LEFT JOIN Invoice ON  OpenAR.Ref = Invoice.Ref
                        WHERE   OpenAR.Loc = ? 
                                AND Invoice.Status = 1;",
                    array(
                        $_GET['ID']
                    )
                ); 
                echo $r 
                    ?   "<div class='row'><div class='col-xs-9'><input disabled type='text' value='" . number_format(sqlsrv_fetch_array($r)['Count']) . "' /></div><div class='col-xs-3'><button onClick=\"someFunction(this,'proposals.php?ID=" . $Location['Location_ID'] . "');\"><i class='fa fa-search fa-fw fa-1x'></i></button></div></div>" 
                    :   0;
            ?></div>
        </div>
    </div>
    <?php }?>
</div>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
