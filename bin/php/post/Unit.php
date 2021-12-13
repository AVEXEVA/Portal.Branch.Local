<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
if( isset( $_SESSION[ 'Connection' ][ 'User' ], $_SESSION[ 'Connection' ][ 'Hash' ] ) ){
    //Connection
    $result = \singleton\database::getInstance( )->query(
        'Portal',
        " SELECT  [Connection].[ID]
        FROM    dbo.[Connection]
        WHERE       [Connection].[User] = ?
                AND [Connection].[Hash] = ?;",
        array(
            $_SESSION[ 'Connection' ][ 'User' ],
            $_SESSION[ 'Connection' ][ 'Hash' ]
        )
    );
    $Connection = sqlsrv_fetch_array($result);
    //User
    $result = \singleton\database::getInstance( )->query(
        null,
        " SELECT  Emp.fFirst  AS First_Name,
		          Emp.Last    AS Last_Name,
		          Emp.fFirst + ' ' + Emp.Last AS Name,
		          Emp.Title AS Title,
		          Emp.Field   AS Field
		  FROM  Emp
		  WHERE   Emp.ID = ?;",
        array(
            $_SESSION[ 'Connection' ][ 'User' ]
        )
    );
    $User   = sqlsrv_fetch_array( $result );
    //Privileges
    $Access = 0;
    $Hex = 0;
    $result = \singleton\database::getInstance( )->query(
        'Portal',
        "   SELECT  [Privilege].[Access],
                    [Privilege].[Owner],
                    [Privilege].[Group],
                    [Privilege].[Department],
                    [Privilege].[Database],
                    [Privilege].[Server],
                    [Privilege].[Other],
                    [Privilege].[Token],
                    [Privilege].[Internet]
		  FROM      dbo.[Privilege]
		  WHERE     Privilege.[User] = ?;",
        array(
            $_SESSION[ 'Connection' ][ 'User' ],
        )
    );
    $Privileges = array();
    if( $result ){while( $Privilege = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC ) ){
        $key = $Privilege['Access'];
        unset( $Privilege[ 'Access' ] );
        $Privileges[ $key ] = implode( '', array(
            dechex( $Privilege[ 'Owner' ] ),
            dechex( $Privilege[ 'Group' ] ),
            dechex( $Privilege[ 'Department' ] ),
            dechex( $Privilege[ 'Database' ] ),
            dechex( $Privilege[ 'Server' ] ),
            dechex( $Privilege[ 'Other' ] ),
            dechex( $Privilege[ 'Token' ] ),
            dechex( $Privilege[ 'Internet' ] )
        ) );
    }}
    if( 	!isset( $Connection[ 'ID' ] )
        ||  !isset( $Privileges[ 'Customer' ] )
        || 	!check( privilege_delete, level_group, $Privileges[ 'User' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        \singleton\database::getInstance( )->query(
            null,
            " INSERT INTO Activity([User], [Date], [Page] )
            VALUES( ?, ?, ? );",
            array(
                $_SESSION[ 'Connection' ][ 'User' ],
                date('Y-m-d H:i:s'),
                'post/customer.php'
            )
        );
        if(isset($_POST['action']) && $_POST['action'] == 'delete'){
            if(isset($_POST['data']) && count($_POST['data']) > 0){
                foreach($_POST['data'] as $ID){
                    $database->query(
                        null,
                        "	DELETE FROM dbo.[Unit]
							WHERE 		[Unit].[ID] = ?;",
                        array(
                            $ID
                        )
                    );
                }
                print json_encode(array('data'=>array()));
            }
        }
        elseif(isset($_POST['action']) && $_POST['action'] == 'create'){
            if(isset($_POST['data']) && count($_POST['data']) > 0){
                $data = array();
                foreach($_POST['data'] as $ID){
                    $r = $database->query(null,
                      " SELECT *
            						FROM   [Unit]
            						WHERE  [Unit].[ID] = ?
					;",array($ID));
                    if($r){$Unit = sqlsrv_fetch_array($r);}

                    $resource = $database->query(null,"SELECT Max(Unit.ID) AS ID FROM Unit;");
                    $Unit_Primary_Key = sqlsrv_fetch_array($resource)['ID'];
                    $Unit_Primary_Key++;
                    $resource = $database->query(null,
                    " INSERT INTO
                        Unit(ID,
                        fDesc,
                        Remarks,
                        Building_ID,
                        City_ID,
                        Loc,
                        Cat,
                        Type,
                        Location_Category,
                        Manuf, Install,
                        InstallBy,
                        Since,
                        Last,
                        Price,
                        Owner,
                        fGroup,
                        Serial,
                        Template,
                        Status,
                        TFMID,
                        TFMSource)
						VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
					;",array($Unit_Primary_Key,
                   $Unit['fDesc'],
                   $Unit['Remarks'],
                   $Unit['Building_ID'],
                   $Unit['City_ID'],
                   $Unit['Loc'],
                   $Unit['Cat'],
                   $Unit['Type'],
                   $Unit['Location_Category'],
                   $Unit['Manuf'],
                   $Unit['Install'],
                   $Unit['InstallBy'],
                   $Unit['Since'],
                   $Unit['Last'],
                   $Unit['Price'],
                   $Unit['Owner'],
                   $Unit['fGroup'],
                   $Unit['Serial'],
                   $Unit['Template'],
                   $Unit['Status'],
                   $Unit['TFMID'],
                   $Unit['TFMSource']));
                    if( ($errors = sqlsrv_errors() ) != null) {
                        foreach( $errors as $error ) {
                            echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
                            echo "code: ".$error[ 'code']."<br />";
                            echo "message: ".$error[ 'message']."<br />";
                        }
                    }
                    $Unit['ID'] = $Unit_Primary_Key;
                    $data[] = $Unit;
                }
                print json_encode(array('data'=>array()));
            }
        }
    }
}?>
