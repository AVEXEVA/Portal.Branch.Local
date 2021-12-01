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
    }
}
    if(     !isset( $Connection[ 'ID' ] )
        ||  !isset( $Privileges[ 'Unit' ] )
        ||  !check( privilege_read, level_group, $Privileges[ 'User' ] )
    ){ ?><?php require('404.html');?><?php }
  
    else {
         $sQueryRow ="SELECT Privilege.*
            FROM   Privilege
            WHERE  User_ID=?";
            $parameters= array(    
                    $_POST['UserID']   
                    ); 

           $r = \singleton\database::getInstance( )->query(  
                  null,     
                  $sQueryRow ,  
                  $parameters   
                ) or die(print_r(sqlsrv_errors())); 
     
        $array = sqlsrv_fetch_array($r);
     
         if(isset($array['ID']) && $array['ID'] > 0){
            $r =  \singleton\database::getInstance( )->query(null,"
                UPDATE Privilege
                SET 
                    Access_Table='{$_POST['Access_Table']}',
                    Group_Privilege='{$_POST['Group_Privilege']}',
                    Other_Privilege='{$_POST['Other_Privilege']}'
                WHERE 
                    User_ID=? ;",array($_POST['UserID'] ));
        } else {
            $query= "INSERT INTO Privilege(User_ID,Access_Table,User_Privilege,Group_Privilege,Other_Privilege)
                VALUES({$_POST['UserID']},'{$_POST['Access_Table']}',{$_POST['User_Privilege']},{$_POST['Group_Privilege']},{$_POST['Other_Privilege']});";
            $r =  \singleton\database::getInstance( )->query(null, $query             
            );

       
        
        }
           header( 'Location: user.php?ID=' . $_POST['UserID'] );
                exit;
        }
   
    
}?>