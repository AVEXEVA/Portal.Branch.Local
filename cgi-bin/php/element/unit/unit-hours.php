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
            ?><html><head><script>document.location.href="../login.php?Forward=unit<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
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
        $unit = $Unit;
        $data = $Unit;
        $r2 = sqlsrv_query($NEI,"
            SELECT *
            FROM   ElevTItem
            WHERE  ElevTItem.ElevT    = 1
                   AND ElevTItem.Elev = ?
        ;",array($_GET['ID']));
        if($r2){while($array = sqlsrv_fetch_array($r2)){$Unit[$array['fDesc']] = $array['Value'];}}
        ?><div class="panel panel-primary">
			<div class='row' style='border-bottom:3px;padding-top:10px;padding-bottom:10px;color:white !important;'>
				<?php
					$result = sqlsrv_query(
						$NEI,
						"	SELECT 	Sum(Tickets.Regular)	AS Regular,
							   		Sum(Tickets.Overtime) 	AS Overtime,
									Sum(Tickets.Doubletime) AS Doubletime,
							 	 	Sum(Tickets.Total) 		AS Total
							FROM 	(
										(
											SELECT 	TicketD.Reg 		AS Regular,
									   				TicketD.OT  		AS Overtime,
									   				TicketD.DT  		AS Doubletime,
									   				TicketD.Total 		AS Total
											FROM   	TicketD
											WHERE  	TicketD.Job = ?
										)
									) AS Tickets;",
						array( 
							$_GET['ID'] 
						) 
					);
					$Sums = sqlsrv_fetch_array( $result );
				?>
				<div class='col-xs-4'><?php $Icons->Hours(1);?> Regular:</div>
				<div class='col-xs-8'><?php echo strlen($Sums['Regular']) ? $Sums['Regular'] : "&nbsp;";;?></div>
			</div>
			<div class='row' style='border-bottom:3px;padding-top:10px;padding-bottom:10px;color:white !important;'>
				<div class='col-xs-4'><?php $Icons->Blank(1);?> Overtime:</div>
				<div class='col-xs-8'><?php echo strlen($Sums['Overtime']) ? $Sums['Overtime'] : "&nbsp;";;?></div>
			</div>
			<div class='row' style='border-bottom:3px;padding-top:10px;padding-bottom:10px;color:white !important;'>
				<div class='col-xs-4'><?php $Icons->Blank(1);?> Doubletime:</div>
				<div class='col-xs-8'><?php echo strlen($Sums['Doubletime']) ? $Sums['Doubletime'] : "&nbsp;";;?></div>
			</div>
			<div class='row' style='border-bottom:3px;padding-top:10px;padding-bottom:10px;color:white !important;'>
				<div class='col-xs-4'><?php $Icons->Blank(1);?> Total:</div>
				<div class='col-xs-8'><?php echo strlen($Sums['Total']) ? $Sums['Total'] : "&nbsp;";;?></div>
			</div>
			<div class='row' style='border-bottom:3px;padding-top:10px;padding-bottom:10px;color:white !important;'>
				<div class='col-xs-4'><?php $Icons->Blank(1);?> Budgeted:</div>
				<div class='col-xs-8'><?php echo strlen($Job['Job_Budgeted_Hours']) ? $Job['Job_Budgeted_Hours'] : "&nbsp;";;?></div>
			</div>
			<?php /*<div class="panel-heading"><h4><i class="fa fa-bell fa-fw"></i> Job Hours</h4></div>*/?>
			<div class="panel-body white-background BankGothic shadow" style='height:500px;overflow-y:scroll;'>
				<div id='operations-overview-job-hours'><?php require('../../../php/element/loading-active.php');?></div>
				<script>
				$(document).ready(function(){
					$.ajax({
						url:"cgi-bin/php/element/unit/operations-overview-job-hours.php?ID=<?php echo $_GET['ID'];?>",
						method:"GET",
						success:function(code){
							$("div#operations-overview-job-hours").html(code);
						}
					});
				});
				</script>
			</div>
		</div>
	</div>
	</div>

<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
