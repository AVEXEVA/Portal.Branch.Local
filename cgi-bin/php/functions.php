<?php 
function connection( $Database, $User_ID, $Hash ){
    $result = sqlsrv_query(
        $Database,
        "   SELECT  * 
            FROM    Connection 
            WHERE   Connection.Connector = ? 
                    AND Connection.Hash = ?;",
        array(
            $User_ID,
            $Hash
        )
    );
    $Connection = sqlsrv_fetch_array( $result );
    /*GET User*/
    $result = sqlsrv_query(
        $Database,
        "   SELECT  *, 
                    Emp.fFirst  AS  First_Name, 
                    Emp.Last    AS  Last_Name,
                    Emp.fWork   AS  Field_ID
            FROM    Emp 
            WHERE   Emp.ID = ?;",
        array(
            $Connection[ 'Connector' ]
        )
    );
    $User = sqlsrv_fetch_array( $result );
    return is_array( $User ) && isset( $User[ 'ID' ] ) && is_numeric( $User[ 'ID' ] ) && $User[ 'ID' ] > 0;
}
function privileges( $Database, $User_ID ){
    /*GET Privleges*/
    $result = sqlsrv_query(
        $Database,
        "   SELECT  Privilege.Access_Table, 
                    Privilege.User_Privilege, 
                    Privilege.Group_Privilege, 
                    Privilege.Other_Privilege
            FROM    Privilege
            WHERE   Privilege.User_ID = ?;",
        array(
            $User_ID
        )
    );
    $Privileges = array( );
    while( $Privilege = sqlsrv_fetch_array( $result ) ){ $Privileges[ $Privilege[ 'Access_Table' ] ] = $Privilege; }
    return is_array( $Privileges ) ? $Privileges : False;
}
function privileged_ticket( $Database, $User_ID, $Privileges, $Ticket_ID ){
    if(     !$Database
        ||  !is_numeric( $User_ID ) 
        ||  !is_array( $Privileges ) 
        ||  ( !is_null ( $Ticket_ID ) && !is_numeric( $Ticket_ID ) ) ){ return false; }
    if( is_null ( $Ticket_ID ) ){
        header( 'Location: new-ticket.php?' . http_build_query( $_GET ) );
        exit;
    }
    if(     isset( $Privileges[ 'Ticket' ] ) 
        &&  $Privileges[ 'Ticket' ][ 'User_Privilege'  ] >= 4 
        &&  $Privileges[ 'Ticket' ][ 'Group_Privilege' ] >= 4 
        &&  $Privileges[ 'Ticket' ][ 'Other_Privilege' ] >= 4){
            return true; }
    elseif( $Privileges[ 'Ticket' ][ 'Group_Privilege' ] >= 4 ){
            $result = sqlsrv_query(
                $NEI,
                "   SELECT  ID
                    FROM    (
                                (
                                    SELECT  ID,
                                            LID AS Loc
                                    FROM    TicketO 
                                ) UNION ALL (
                                    SELECT  ID,
                                            Loc
                                    FROM    TicketD
                                ) UNION ALL (
                                    SELECT  ID,
                                            Loc
                                    FROM    TicketDArchive
                                )
                            ) AS Ticket
                    WHERE   Ticket.ID = ?
                            AND Ticket.Loc IN (
                                SELECT  Loc
                                FROM    (
                                            (
                                                SELECT  LID AS Loc,
                                                        fWork
                                                FROM    TicketO
                                            ) UNION ALL (
                                                SELECT  Loc,
                                                        fWork
                                                FROM    TicketD
                                            ) UNION ALL (
                                                SELECT  Loc,
                                                        fWork
                                                FROM    TicketDArchive
                                            )
                                        ) AS Location_Tickets
                                        LEFT JOIN Emp ON Location_Tickets.fWork = Emp.fWork
                                WHERE   Emp.ID = ?
                            );",
                array( 
                    $Ticket_ID,
                    $User_ID
                )
            );
            if( $result ){ return !empty( sqlsrv_fetch_array( $result ) ); }
    }
    return false;
}
function privileged( $Database, $User_ID ){
    switch( $_SERVER[ 'SCRIPT_NAME' ] ){ 
        case '/portal/ticket.php' : return privileged_ticket( $Database, $User_ID, privileges( $Database, $User_ID ), $_GET[ 'ID' ] );
        case ( preg_match('/\/portal\/cgi-bin\/php\/element\/ticket\//', $_SERVER[ 'SCRIPT_NAME' ] ) ? true : false ) : return privileged_ticket( $Database, $User_ID, privileges( $Database, $User_ID ), $_GET[ 'ID' ] );
        case '/portal/privileges.php' : return isset( privileges( $Database, $User_ID )[ 'Admin' ], privileges( $Database, $User_ID )[ 'Admin' ][ 'Other_Privilege' ] ) && privileges( $Database, $User_ID )[ 'Admin' ][ 'Other_Privilege' ] == 7;
        case '/portal/privileges1.php' : return isset( privileges( $Database, $User_ID )[ 'Admin' ], privileges( $Database, $User_ID )[ 'Admin' ][ 'Other_Privilege' ] ) && privileges( $Database, $User_ID )[ 'Admin' ][ 'Other_Privilege' ] == 7;
        case '/portal/privileges2.php' : return isset( privileges( $Database, $User_ID )[ 'Admin' ], privileges( $Database, $User_ID )[ 'Admin' ][ 'Other_Privilege' ] ) && privileges( $Database, $User_ID )[ 'Admin' ][ 'Other_Privilege' ] == 7;
        case '/portal/privilege.php' : return isset( privileges( $Database, $User_ID )[ 'Admin' ], privileges( $Database, $User_ID )[ 'Admin' ][ 'Other_Privilege' ] ) && privileges( $Database, $User_ID )[ 'Admin' ][ 'Other_Privilege' ] == 7;
        case '/portal/privilege1.php' : return isset( privileges( $Database, $User_ID )[ 'Admin' ], privileges( $Database, $User_ID )[ 'Admin' ][ 'Other_Privilege' ] ) && privileges( $Database, $User_ID )[ 'Admin' ][ 'Other_Privilege' ] == 7;
        case '/portal/privilege2.php' : return isset( privileges( $Database, $User_ID )[ 'Admin' ], privileges( $Database, $User_ID )[ 'Admin' ][ 'Other_Privilege' ] ) && privileges( $Database, $User_ID )[ 'Admin' ][ 'Other_Privilege' ] == 7;
        default : return true;
    }
}
function connection_privileged( $Database, $User, $Connection ){ 
    return      connection( $Database, is_array( $User ) ? $User[ 'ID' ] : $User, is_array( $Connection ) ? $Connection[ 'Hash' ] : $Connection ) 
            &&  privileged( $Database, is_array( $User ) ? $User[ 'ID' ] : $User ); }
function activity( $Database, $Server, $Connection ){
    sqlsrv_query(
        $Database, 
        "   INSERT INTO Portal.dbo.History( [Page], [Parameters], [Database], [Branch], [Branch_ID], [IP], [Agent] )
            VALUES( ?, ?, ?, ?, ?, ?, ? );",
        array( 
            $Server[ 'SCRIPT_NAME' ],
            json_encode( $_GET ),
            $Connection[ 'Database' ],
            $Connection[ 'Branch' ],
            $Connection[ 'Branch_ID' ],
            $Server[ 'REMOTE_ADDR' ],
            $Server[ 'HTTP_USER_AGENT' ]
        )
    );
}
function proper($string){return ucwords(strtolower($string));}
function utf8ize($d) {
    if (is_array($d)) {
        foreach ($d as $k => $v) {
            $d[$k] = utf8ize($v);
        }
    } else if (is_string ($d)) {
        return utf8_encode($d);
    }
    return $d;
}
function isMobile() {
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
}
function createHomeScreenOption($Icons,$My_Privileges,$From_Page,$To_Page,$Parameters = NULL,$Icon = NULL){
	$Icon = is_null($Icon) ? $To_Page : $Icon;
	if(isset($My_Privileges[proper($To_Page)]) && $My_Privileges[proper($To_Page)]['User_Privilege'] >= 4){
	?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='<?php echo $From_Page;?>-<?php echo strtolower($To_Page);?>.php?ID=<?php echo is_null($Parameters) ? $_GET['ID'] : $Parameters;?>'">
		<div class='nav-icon'><?php call_user_func_array(array($Icons, str_replace("-","_",$Icon)), array(3));?></div>
			<div class ='nav-text'><?php echo proper($To_Page);?></div> 
	</div><?php }
}
function mssql_escape($data) {
    if(is_numeric($data))
        return $data;
    $unpacked = unpack('H*hex', $data);
    return '0x' . $unpacked['hex'];
}
function fixArrayKey(&$arr)
{
	$arr=array_combine(array_map(function($str){return str_replace("_"," ",$str);},array_keys($arr)),array_values($arr));
	foreach($arr as $key=>$val)
	{
		if(is_array($val)) fixArrayKey($arr[$key]);
	}
}?>