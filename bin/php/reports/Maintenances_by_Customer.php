<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [ 'read_and_close' => true ] ); 
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/bin/php/index.php' );
}
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    //Connection
    $result = $database->query(
        null, 
        "   SELECT  * 
            FROM    Connection 
            WHERE       Connector = ? 
                    AND Hash = ?;",
        array(
            $_SESSION['User'],
            $_SESSION['Hash']
        )
    );
    $Connection = sqlsrv_fetch_array($result);
    //User
    $result = $database->query(
        null,
        "   SELECT  *, 
                    fFirst AS First_Name, 
                    Last as Last_Name 
            FROM    Emp 
            WHERE   ID= ?;",
        array(
            $_SESSION[ 'User' ]
        )
    );
    $User   = sqlsrv_fetch_array( $result );
    //Privileges
    $result = $database->query(null,
        "   SELECT  Privilege.*
            FROM    Privilege
            WHERE   Privilege.User_ID = ?;",
        array(
            $_SESSION[ 'User' ]
        )
    );
    $Privileges = array();
    $Privileged = false;
    while( $Privilege = sqlsrv_fetch_array( $result ) ){ $Privileges[ $Privilege[ 'Access_Table' ] ] = $Privilege; }
    if(     isset($Privileges['Customer']) 
        &&  $Privileges[ 'Customer' ][ 'User_Privilege' ]  >= 4 
        &&  $Privileges[ 'Customer' ][ 'Group_Privilege' ] >= 4 
        &&  $Privileges[ 'Customer' ][ 'Other_Privilege' ] >= 4){
                $Privileged = true;}
    if(     !isset($Connection['ID'])  
        ||  !is_numeric($_GET['ID']) 
        || !$Privileged 
    ){ print json_encode( array( 'data' => array( ) ) ); }
    else {
        $data = array();
        $Tickets = array();
        $r = $database->query(null,"
                SELECT   Elev.ID AS ID
                FROM     TicketO 
                         LEFT JOIN Elev ON  TicketO.LElev = Elev.ID
                         LEFT JOIN Job  ON  TicketO.Job   = Job.ID
						 LEFT JOIN Loc  ON  Elev.Loc      = Loc.Loc
                WHERE    TicketO.Type      = 0
                         AND TicketO.Level = 10
						 AND Loc.Owner = ?
                GROUP BY Elev.ID
        ;",array($_GET['ID']));
        $data2 = array();
        if($r){while($array = sqlsrv_fetch_array($r)){$data2[$array['ID']] = $array;}}
        $sql = array();
        foreach($data2 as $key=>$variable){$sql[] = "Elev.ID = '{$variable['ID']}'";}
        $sql = implode(" OR ",$sql);
        $r = $database->query(null,"
                SELECT   Max(TicketD.EDate) AS Last_Date,
                         Elev.ID          	AS ID
                FROM     TicketD 
                         LEFT JOIN Elev ON TicketD.Elev = Elev.ID
                         LEFT JOIN Job  ON TicketD.Job  = Job.ID
						 LEFT JOIN Loc  ON Elev.Loc     = Loc.Loc
                WHERE    Job.Type = 0
						 AND Loc.Owner = ?
                GROUP BY Elev.ID
        ;",array($_GET['ID']));
        if($r){
            $date = date('m');
            while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){
                if(intval(substr($array['Last_Date'],5,2)) != $date){
                    if(isset($data2[$array['ID']])){
                        $data2[$array['ID']]['Last_Date'] = $array['Last_Date'];
                    }
                }
            }
        }
        $r = $database->query(null,"
            SELECT Elev.ID         			   AS ID,
                   Elev.State      			   AS State, 
                   Elev.Unit       			   AS Unit,
                   Elev.Type                   AS Type,
                   Loc.Tag        			   AS Location,
                   Zone.Name       			   AS Zone,
                   Emp.fFirst + ' ' + Emp.Last AS Route
            FROM   Elev
                   LEFT JOIN Loc   ON Elev.Loc 	 = Loc.Loc
                   LEFT JOIN Zone  ON Loc.Zone 	 = Zone.ID
                   LEFT JOIN Route ON Loc.Route  = Route.ID
                   LEFT JOIN Emp   ON Route.Mech = Emp.fWork
            WHERE  Loc.Maint = 1
                   AND ({$sql})
        ;",array($_GET['ID']));
        if($r){
            while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){
                $array['Last_Date'] = substr($data2[$array['ID']]['Last_Date'],0,10);
                $data[] = $array;
            }
        }
        print json_encode(array('data'=>utf8ize($data)));	
    }
}?>