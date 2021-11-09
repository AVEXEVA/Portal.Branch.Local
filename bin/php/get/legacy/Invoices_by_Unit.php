<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [ 'read_and_close' => true ] ); 
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/bin/php/index.php' );
}
if( isset( $_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
    //Establish Connection
    $result = $database->query(
        null,
        "   SELECT  * 
            FROM    Connection 
            WHERE       Connection.Connector = ? 
                    AND Connection.Hash = ?;", 
        array(
            $_SESSION['User'],
            $_SESSION['Hash']
        )
    );
    $Connection = sqlsrv_fetch_array($result);
    //Establish User
    $result    = $database->query(
        null,
        "   SELECT  Emp.*, 
                    Emp.fFirst AS First_Name, 
                    Emp.Last   AS Last_Name 
            FROM    Emp
            WHERE   Emp.ID = ?;", 
        array(
            $_SESSION[ 'User' ]
        )
    );
    $User = sqlsrv_fetch_array( $result ); 
    //Establish Priivleges
    $result = $database->query(
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
    $Privileges = array( );
    $Privileged = False;
    if( $result ){ while( $Privilege = sqlsrv_fetch_array( $result ) ){ $Privileges[ $Privilege[ 'Access_Table' ] ] = $Privilege;} }
    if( isset( $Privileges[ 'Invoice' ], $Privileges[ 'Location' ] ) 
        &&  $Privileges[ 'Invoice' ][ 'Other_Privilege' ]  >= 4
	  	&&  $Privileges[ 'Location' ][ 'Other_Privilege' ] >= 4){
                $Privileged = True;}
    if(     !isset( $Connection[ 'ID' ] )  
        ||  !is_numeric( $_GET[ 'ID' ] ) 
        ||  !$Privileged
    ){          print json_encode(
                    array(
                        'data' => array( ) 
                    ) 
                );
    } else {
        $result = $database->query(
            null,
            "   SELECT  Invoice.Ref         AS  ID,
                        Invoice.fDesc       AS  Description,
                        Invoice.Total       AS  Total,
                        Job.fDesc           AS  Job,
                        Loc.Tag             AS  Location,
                        Invoice.fDate       AS  fDate,
                        Invoice.Status      AS  Status
                FROM    Invoice
                        LEFT JOIN Loc ON Invoice.Loc = Loc.Loc
                        LEFT JOIN Job ON Invoice.Job = Job.ID
                WHERE   Job.Elev =   ?",
            array(
                $_GET[ 'ID' ]
            )
        );
        $data = array( );
        if($result){ while( $row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)){ $data[ ] = $row; } }   
        print json_encode(
            array(
                'data' => $data
            )
        );
    }
}?>