<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [ 'read_and_close' => true ] ); 
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
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
        $r = $database->query(null,"
            SELECT Loc.Loc      AS ID,
                   Loc.ID       AS Name,
                   Loc.Tag      AS Tag,
                   Loc.Address  AS Street,
                   Loc.City     AS City,
                   Loc.State    AS State,
                   Loc.Zip      AS Zip,
                   Route.Name   AS Route,
                   Zone.Name    AS Division,
                   Loc.Maint    AS Maintenance,
				   Terr.Name    AS Territory,
				   Loc.sTax     AS Sales_Tax,
				   Rol.Contact  AS Contact_Name,
				   Rol.Phone    AS Contact_Phone,
				   Rol.Fax      AS Contact_Fax,
				   Rol.Cellular AS Contact_Cellular,
				   Rol.Email    AS Contact_Email,
				   Rol.Website  AS Contact_Website,
				   Loc.fLong    AS Longitude,
				   Loc.Latt     AS Latitude,
				   Loc.Custom1  AS Collector
            FROM   Loc
			       LEFT JOIN Zone ON Zone.ID	= Loc.Zone
				   LEFT JOIN Route ON Route.ID 	= Loc.Route
				   LEFT JOIN Terr ON Terr.ID 	= Loc.Terr
				   LEFT JOIN Rol ON Loc.Rol 	= Rol.ID
            WHERE  Loc.Owner = ?
        ;",array($_GET['ID']));
        if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
        print json_encode(array('data'=>utf8ize($data)));  
	}
}?>