<?php 
session_start( [ 'read_and_close' => true ] );
require('index.php');
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
    $Connection = sqlsrv_fetch_array( $result );
    $User = $database->query(
        null,
        "   SELECT  * 
            FROM    Emp 
            WHERE   ID = ?;",
        array(
            $_SESSION[ 'User' ]
        )
    );
    $User = sqlsrv_fetch_array($User);
    //Privileges
    $result = $database->query(
        null,
        "   SELECT  Access, 
                    Owner, 
                    Group, 
                    Other
            FROM    Privilege
            WHERE   User_ID = ?;",
        array(
            $_SESSION[ 'User' ]
        )
    );
    $Privileges = array();
    if( $result ){ while( $Privilege = sqlsrv_fetch_array( $result ) ){ $Privileges[ $Privilege[ 'Access' ] ] = $Privilege; } }
    $Privileged = false;
    if(     isset( $Privileges['Admin']) 
        &&  $Privileges['Admin']['Owner'] >= 4
    ){ $Privileged = true; }
    if(     !isset($Connection['ID'])  
        || !$Privileged
    ){ print json_encode( array( 'data' => array( ) ) ); }
    else {
        $r = $database->query(null,"
             SELECT Emp.fFirst + ' ' + Emp.Last   AS Name,
			 		Emp.DBirth                    AS Birthday
			 FROM   Emp
			 WHERE       DATEPART( Week, DATEADD( Year, DATEPART( Year, GETDATE()) - DATEPART( Year, DBirth), DBirth)) = DATEPART( Week, GETDATE())
	  		 	    AND  Status = 0 
					AND  Title  = 'office';
        ;");
        $data = array();
        if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
        print json_encode(array('data'=>$data));   }
}?>