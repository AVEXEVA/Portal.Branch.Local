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
    while( $Privilege = sqlsrv_fetch_array( $result ) ){ $Privileges[ $Privilege[ 'Access' ] ] = $Privilege; }
    if(     isset($Privileges['Invoice']) 
        &&  $Privileges[ 'Invoice' ][ 'Owner' ]  >= 4 
        &&  $Privileges[ 'Invoice' ][ 'Group' ] >= 4 
        &&  $Privileges[ 'Invoice' ][ 'Other' ] >= 4){
                $Privileged = true;}
    if(     !isset($Connection['ID'])  
        ||  !is_numeric($_GET['ID']) 
        || !$Privileged 
    ){ print json_encode( array( 'data' => array( ) ) ); }
    else {
        $result = $database->query(
            null,
            "   SELECT OpenAR.Ref      AS  Invoice,
                       OpenAR.fDate    AS  Dated,
                       OpenAR.Due      AS  Due,
                       OpenAR.fDesc    AS  Description,
                       OpenAR.Original AS  Original,
                       OpenAR.Balance  AS  Balance,
                       Loc.Tag         AS  Tag
                FROM   OpenAR
                       LEFT JOIN Loc ON OpenAR.Loc = Loc.Loc
                WHERE  Loc.Owner = ?;",
            array(
                $_GET[ 'ID' ]
            )
        );
        $data = array();
        if( $result ){while( $row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC ) ){$data[] = $row;}}
        print json_encode(
            array(
                'data' => $data
            )
        );   
    }
}?>