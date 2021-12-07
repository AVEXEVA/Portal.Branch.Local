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
            "   INSERT INTO Activity([User], [Date], [Page] )
                VALUES( ?, ?, ? );",
            array(
                $_SESSION[ 'Connection' ][ 'User' ],
                date('Y-m-d H:i:s'),
                'post/violation.php'
            )
        );
		if(isset($_POST['action']) && $_POST['action'] == 'edit'){
            if(isset($_POST['data']) && count($_POST['data']) > 0){
                foreach( $_POST[ 'data' ] as $ID => $Record ){
                    foreach( $Record as $k=>$v ){ 
                        if( in_array( $k, array( 'ID', 'Access' )  ) ){ continue; }
                        $Record[ $k ] = $v == 'true' ? true : false; 
                    }
                    $Record[ 'Other_Read' ] = false;
                    $Record[ 'Other_Write' ] = false;
                    $Record[ 'Other_Execute' ] = false;
                    $Record[ 'Other_Delete' ] = false;
                    $Record[ 'Token_Read' ] = false;
                    $Record[ 'Token_Write' ] = false;
                    $Record[ 'Token_Execute' ] = false;
                    $Record[ 'Token_Delete' ] = false;
                    $Record[ 'Internet_Read' ] = false;
                    $Record[ 'Internet_Write' ] = false;
                    $Record[ 'Internet_Execute' ] = false;
                    $Record[ 'Internet_Delete' ] = false;
                    \singleton\database::getInstance( )->query(
                        'Portal',
                        "   UPDATE  dbo.[Privilege] 
                            SET     [Privilege].[Access] = ?,
                                    [Privilege].[Owner] = ?,
                                    [Privilege].[Group] = ?,
                                    [Privilege].[Department] = ?,
                                    [Privilege].[Database] = ?,
                                    [Privilege].[Server] = ?,
                                    [Privilege].[Other] = ?,
                                    [Privilege].[Token] = ?,
                                    [Privilege].[Internet] = ?
                            WHERE   [Privilege].[ID] = ?;",
                        array(
                            $Record[ 'Access' ],
                            ( $Record[ 'Owner_Read' ] ? 8 : 0 ) +
                            ( $Record[ 'Owner_Write' ] ? 4 : 0 ) +
                            ( $Record[ 'Owner_Execute' ] ? 2 : 0 ) +
                            ( $Record[ 'Owner_Delete' ] ? 1 : 0 ),
                            ( $Record[ 'Group_Read' ] ? 8 : 0 ) +
                            ( $Record[ 'Group_Write' ] ? 4 : 0 ) +
                            ( $Record[ 'Group_Execute' ] ? 2 : 0 ) +
                            ( $Record[ 'Group_Delete' ] ? 1 : 0 ),
                            ( $Record[ 'Department_Read' ] ? 8 : 0 ) +
                            ( $Record[ 'Department_Write' ] ? 4 : 0 ) +
                            ( $Record[ 'Department_Execute' ] ? 2 : 0 ) +
                            ( $Record[ 'Department_Delete' ] ? 1 : 0 ),
                            ( $Record[ 'Database_Read' ] ? 8 : 0 ) +
                            ( $Record[ 'Database_Write' ] ? 4 : 0 ) +
                            ( $Record[ 'Database_Execute' ] ? 2 : 0 ) +
                            ( $Record[ 'Database_Delete' ] ? 1 : 0 ),
                            ( $Record[ 'Server_Read' ] ? 8 : 0 ) +
                            ( $Record[ 'Server_Write' ] ? 4 : 0 ) +
                            ( $Record[ 'Server_Execute' ] ? 2 : 0 ) +
                            ( $Record[ 'Server_Delete' ] ? 1 : 0 ),
                            ( $Record[ 'Other_Read' ] ? 8 : 0 ) +
                            ( $Record[ 'Other_Write' ] ? 4 : 0 ) +
                            ( $Record[ 'Other_Execute' ] ? 2 : 0 ) +
                            ( $Record[ 'Other_Delete' ] ? 1 : 0 ),
                            ( $Record[ 'Token_Read' ] ? 8 : 0 ) +
                            ( $Record[ 'Token_Write' ] ? 4 : 0 ) +
                            ( $Record[ 'Token_Execute' ] ? 2 : 0 ) +
                            ( $Record[ 'Token_Delete' ] ? 1 : 0 ),
                            ( $Record[ 'Internet_Read' ] ? 8 : 0 ) +
                            ( $Record[ 'Internet_Write' ] ? 4 : 0 ) +
                            ( $Record[ 'Internet_Execute' ] ? 2 : 0 ) +
                            ( $Record[ 'Internet_Delete' ] ? 1 : 0 ),
                            $ID
                        )
                    );
                }
                print json_encode(array('data'=> $Record ) );
            }
        } elseif(isset($_POST['action']) && $_POST['action'] == 'access_field'){
            if( isset( $_POST[ 'User' ] ) && is_numeric( $_POST[ 'User' ] ) ){
                \singleton\database::getInstance( )->query(
                    'Portal',
                    "   DELETE FROM dbo.Privilege 
                        WHERE       Privilege.[User] = ?;",
                    array(
                        $_POST[ 'User' ]
                    )
                );
                foreach( array(
                    'Route',
                    'Location',
                    'Unit',
                    'Job',
                    'Ticket',
                    'Violation',
                    'Map',
                    'Time'
                ) AS $Access ){
                    \singleton\database::getInstance( )->query(
                        'Portal',
                        "   INSERT INTO dbo.Privilege( [User], [Access], [Owner], [Group], [Department] )
                            VALUES ( ?, ?, ?, ?, ? );",
                        array(
                            $_POST[ 'User' ],
                            $Access,
                            13,
                            12,
                            12
                        )
                    );
                    var_dump( sqlsrv_errors( ) );
                }
            }
        } elseif(isset($_POST['action']) && $_POST['action'] == 'access_office'){
            if( isset( $_POST[ 'User' ] ) && is_numeric( $_POST[ 'User' ] ) ){
                \singleton\database::getInstance( )->query(
                    'Portal',
                    "   DELETE FROM dbo.Privilege 
                        WHERE       Privilege.[User] = ?;",
                    array(
                        $_POST[ 'User' ]
                    )
                );
                foreach( array(
                    'Route',
                    'Customer',
                    'Location',
                    'Unit',
                    'Job',
                    'Ticket',
                    'Violation',
                    'Invoice',
                    'Lead',
                    'Proposal',
                    'Map',
                    'Time'
                ) AS $Access ){
                    \singleton\database::getInstance( )->query(
                        'Portal',
                        "   INSERT INTO dbo.Privilege( [User], [Access], [Owner], [Group], [Department] )
                            VALUES ( ?, ?, ?, ?, ? );",
                        array(
                            $_POST[ 'User' ],
                            $Access,
                            14,
                            14,
                            14
                        )
                    );
                    var_dump( sqlsrv_errors( ) );
                }
            }
        } elseif(isset($_POST['action']) && $_POST['action'] == 'delete'){
            if(isset($_POST['data']) && count($_POST['data']) > 0){
                foreach($_POST['data'] as $ID){
                    $database->query(
                        null,
                        "   DELETE FROM dbo.[Violation] 
                            WHERE       [Violation].[ID] = ?;",
                        array(
                            $ID
                        )
                    );
                }
                print json_encode(array('data'=>array()));
            }
        }
    }
}?>
