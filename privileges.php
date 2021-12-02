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
        $ID=$ID = isset( $_GET[ 'UserID' ] )
            ? $_GET[ 'UserID' ]
            : (
                isset( $_POST[ 'UserID' ] )
                    ? $_POST[ 'UserID' ]
                    : null
                );
           $priv=array();
                 $sQueryRow ="SELECT Privilege.User_Privilege FROM   Privilege WHERE  User_ID=? AND Access_Table='User'";
            $parameters= array(    
                    $ID   
                    ); 

           $r = \singleton\database::getInstance( )->query(  
                  null,     
                  $sQueryRow ,  
                  $parameters   
                ) or die(print_r(sqlsrv_errors())); 
    
        while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){
         $priv[]=   $array['User_Privilege'];
        }
  //      print_r($_POST);
      
      if(isset($_POST['UserID']) &&( $_POST['UserID'] > 0 )){
         $sQueryRow ="Delete FROM   Privilege  WHERE  User_ID= ? And Access_Table='User'";
            $parameters= array(    
                    $ID   
                    ); 

           $r = \singleton\database::getInstance( )->query(  
                  null,     
                  $sQueryRow ,  
                  $parameters   
                ) or die(print_r(sqlsrv_errors())); 
     
     $permissionArray =array();
     $permissionArray = $_POST['User_Privilege'];
       foreach($permissionArray as $val){
            $query= "INSERT INTO Privilege(User_ID,Access_Table,User_Privilege,Group_Privilege,Other_Privilege)
                VALUES({$ID},'{$_POST['Access_Table']}',$val,{$_POST['Group_Privilege']},{$_POST['Other_Privilege']});";
            $r =  \singleton\database::getInstance( )->query(null, $query             
            );

       }
       
           header( 'Location: user.php?ID=' . $_POST['UserID'] );
                exit;

    }



        }?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
  <?php
        $_GET[ 'Bootstrap' ] = '5.1';
        $_GET[ 'Entity_CSS' ] = 1;
        require( bin_meta . 'index.php' );
        require( bin_css  . 'index.php' );
        require( bin_js   . 'index.php' );
    ?>
    <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyCNrTryEaTEDRz-XDSg890ajL_JRPnLgzc"></script>
</head>
<body>
  <div id="wrapper">
    <?php require(PROJECT_ROOT.'php/element/navigation.php');?>
    <div id="page-wrapper" class='content' style='height:100%;overflow-y:scroll;'>
      <div class='card card-primary'>
        <div class='card-heading'>
          <div class='row g-0 px-3 py-2'>
             <div class="card card-primary my-3">
        <form name ="privilege" action="privileges.php" method="POST">
                <input type="hidden" name="UserID" value="<?php echo $ID;?>">
                <input type="hidden" name="Access_Table" value="User">
                <input type="hidden" name="Group_Privilege" value="0">
                <input type="hidden" name="Other_Privilege" value="0">
              <div class="card-heading">
                <div class="row g-0 px-3 py-2">
                  <div class="col-10"><h5><i class="fa fa-user fa-fw fa-1x" aria-hidden="true"></i><span>Privileges</span></h5></div>
                
                  <div class="col-2"></div>
                </div>
              </div>
              <div class="card-body bg-dark text-white">
                <div class="row">
                  <div class="col-4 col-4 border-bottom border-white my-auto"><i class="fa fa-user fa-fw fa-1x" aria-hidden="true"></i> Permissions:</div>
                  <div class="col-8"><select name="User_Privilege[]" class="form-control"  multiple="mutilple">
                                    
                    <option value="8" <?php if(in_array(8,$priv)){echo 'style="background-color: #da8d7096;" selected' ;} ?>>Read</option>
                    <option value="4" <?php if(in_array(4,$priv)){echo 'style="background-color: #da8d7096;" selected';} ?>>Write</option>
                     <option value="1" <?php if(in_array(1,$priv)){echo ' style="background-color: #da8d7096;" selected';} ?>>Execute</option>
                      <option value="2" <?php if(in_array(2,$priv)){echo ' style="background-color: #da8d7096;" selected';} ?>>Delete</option>
                                    </select></div>

                  </div>
             
              </div>
              <div class="card-footer">
                  <div class="row">
                      <div class="col-12"><button class="form-control" type="submit">Save</button></div>
                  </div>
              </div>
            </form>
            </div>
          </div>
         </div>
       </div>   
    </div>
  </div>  
       
        <?php
   
    
}?>