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
    if(     !isset( $Connection[ 'ID' ] )
        ||  !isset( $Privileges[ 'Unit' ] )
        ||  !check( privilege_read, level_group, $Privileges[ 'User' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
         $ID = isset( $_GET[ 'ID' ] )
            ? $_GET[ 'ID' ]
            : (
                isset( $_POST[ 'ID' ] )
                    ? $_POST[ 'ID' ]
                    : null
                );
        $Email = isset( $_GET[ 'Email' ] )
            ? $_GET[ 'Email' ]
            : (
                isset( $_POST[ 'Email' ] )
                    ? $_POST[ 'Email' ]
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
     
  
        $result = $database->query(
            'Portal',
            "   SELECT  Top 1
                        *
                FROM    dbo.[User]
                WHERE   [User].[ID] = ?;",
          array(
            $ID,
            $Email
          )
        );
        $User =   (       empty( $ID )
                        &&    !empty( $Name )
                        &&    !$result
                      ) || (  empty( $ID )
                        &&    empty( $Name )
                      )  ? array(
            'ID' => null,
            'Email' => null,
            'Password' => null,
            'Branch' => null,
            'Branch_Type' => null,
            'Branch_ID' => null, 
            'Picture' => null,  
            'Picture_Type' => null
        ) : sqlsrv_fetch_array( $result );
        if( isset( $_POST ) && count( $_POST ) > 0 ){
            $User[ 'Email' ] = isset( $_POST[ 'Email' ] ) ? $_POST[ 'Email' ] : $User[ 'Email' ];
            $User[ 'Password' ] = isset( $_POST[ 'Password' ] ) ? $_POST[ 'Password' ] : $User[ 'Password' ];
            $User[ 'Branch' ] = isset( $_POST[ 'Branch' ] ) ? $_POST[ 'Branch' ] : $User[ 'Branch' ];
            $User[ 'Branch_Type' ] = isset( $_POST[ 'Branch_Type' ] ) ? $_POST[ 'Branch_Type' ] : $User[ 'Branch_Type' ];
            $User[ 'Branch_ID' ] = isset( $_POST[ 'Branch_ID' ] ) ? $_POST[ 'Branch_ID' ] : $User[ 'Branch_ID' ];
                        $picture=empty( $_POST[ 'ID' ] )?0:$User[ 'Picture' ];  
            $picture_type=empty( $_POST[ 'ID' ] )?'':$User[ 'Picture_Type' ];;  
                
                
            if(isset($_FILES[ 'Picture' ] ) &&  ( $_FILES[ 'Picture' ][ 'tmp_name' ]!="" ) &&  (strlen( $_FILES[ 'Picture' ][ 'tmp_name' ] ) > 1) ) 
    {   
    ob_start( );    
  $image = imagecreatefromstring( file_get_contents( $_FILES[ 'Picture' ][ 'tmp_name' ] ) );    
  imagejpeg( $image, null, 50 );    
  $image = ob_get_clean( ); 
  $image = base64_encode( $image );     
        
          $picture=  array( 
        $image,     
        SQLSRV_PARAM_IN,    
        SQLSRV_PHPTYPE_STREAM(SQLSRV_ENC_BINARY),   
        SQLSRV_SQLTYPE_VARBINARY('max') 
      );    
        
        $picture_type=  $_FILES[ 'Picture' ][ 'type' ]; 
            
        if( ( $ID )>0 ){    
          $sQueryRow = "UPDATE  [User]  
                        SET     [User].[Picture] = ?,   
                                [User].[Picture_Type] = ?   
                                WHERE   [User].[ID] = ? 
                         "; 
         $parameters= array(    
                    $picture,   
                       $picture_type,   
                       $ID  
                    );  
            
                $fResult = \singleton\database::getInstance( )->query(  
      'Portal',     
      $sQueryRow ,  
      $parameters   
    ) or die(print_r(sqlsrv_errors())); 
                    
        }   
      }
            if( empty( $_POST[ 'ID' ] ) ){
	            $result = \singleton\database::getInstance( )->query(
	              'Portal',
	              " INSERT INTO dbo.[User]( Email, Password, Verified, Branch, Branch_Type, Branch_ID )
	                VALUES( ?, ?, 0, ?, ?, ? );
	                SELECT Max( ID ) FROM dbo.[User];",
	                array(
	                    $User[ 'Email' ],
	                    $User[ 'Password' ],
                      $User[ 'Branch' ],
                      $User[ 'Branch_Type' ],
                      $User[ 'Branch_ID' ]
	                )
	            );

	            sqlsrv_next_result( $result );
	            $User[ 'ID' ] = sqlsrv_fetch_array( $result )[ 0 ];
                if(strlen( $picture_type )>1){  
                   $sQueryRow = "UPDATE  dbo.[User] 
                        SET     [User].[Picture] = ?,   
                                [User].[Picture_Type] = ?   
                                WHERE   [User].[ID] = ?;    
                         "; 
         $parameters= array(    
                    $picture,   
                       $picture_type,   
                       $User[ 'ID' ]    
                    );  
            
                $fResult = \singleton\database::getInstance( )->query(  
                  'Portal',     
                  $sQueryRow ,  
                  $parameters   
                ) or die(print_r(sqlsrv_errors())); 
                }

	            header( 'Location: user.php?ID=' . $User[ 'ID' ] );
	            exit;
	        } else {
	               
                   $sQueryRow = "UPDATE  [User] 
                        SET     [User].[Email] = ?, 
                                [User].[Branch] = ?,    
                                [User].[Branch_Type] = ?,   
                          [User].[Branch_ID] = ?    
                                WHERE   [User].[ID] = ?;    
                         "; 
         $parameters= array(    
                          $User[ 'Email' ], 
                       $User[ 'Branch' ],   
                      $User[ 'Branch_Type' ],   
                      $User[ 'Branch_ID' ], 
                      $ID   
                    );  
            
                $fResult = \singleton\database::getInstance( )->query(  
                  'Portal',     
                  $sQueryRow ,  
                  $parameters   
                ) or die(print_r(sqlsrv_errors())); 

                
           
	        }
	    }
?><!DOCTYPE html>
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
            <div class='col-6'>
              <h5><?php \singleton\fontawesome::getInstance( )->User( 1 );?><a href='users.php?<?php
                echo http_build_query( isset( $_SESSION[ 'Tables' ][ 'Users' ][ 0 ] ) && is_array( $_SESSION[ 'Tables' ][ 'Users' ][ 0 ] ) ? $_SESSION[ 'Tables' ][ 'Users' ][ 0 ] : array( ) );
              ?>'>User</a>: <span><?php
                echo is_null( $User[ 'ID' ] )
                  ? 'New'
                  : $User[ 'Email' ];
              ?></span></h5>
            </div>
            <div class='col-2'></div>
            <div class='col-2'>
              <div class='row g-0'>
                <div class='col-4'>
                  <button
                    class='form-control rounded'
                    onClick="document.location.href='user.php';"
                  >Create</button>
                </div>
                <div class='col-4'>
                  <button
                    class='form-control rounded'
                    onClick="document.location.href='user.php?ID=<?php echo $User[ 'ID' ];?>';"
                  >Refresh</button>
                </div>
              </div>
            </div>
            <div class='col-2'>
              <div class='row g-0'>
                <div class='col-4'><button class='form-control rounded' onClick="document.location.href='user.php?ID=<?php echo !is_null( $User[ 'ID' ] ) ? array_keys( $_SESSION[ 'Tables' ][ 'Users' ], true )[ array_search( $User[ 'ID' ], array_keys( $_SESSION[ 'Tables' ][ 'Users' ], true ) ) - 1 ] : null;?>';">Previous</button></div>
                <div class='col-4'><button class='form-control rounded' onClick="document.location.href='users.php?<?php echo http_build_query( is_array( $_SESSION[ 'Tables' ][ 'Users' ][ 0 ] ) ? $_SESSION[ 'Tables' ][ 'Users' ][ 0 ] : array( ) );?>';">Table</button></div>
                <div class='col-4'><button class='form-control rounded' onClick="document.location.href='user.php?ID=<?php echo !is_null( $User[ 'ID' ] )? array_keys( $_SESSION[ 'Tables' ][ 'Users' ], true )[ array_search( $User[ 'ID' ], array_keys( $_SESSION[ 'Tables' ][ 'Users' ], true ) ) + 1 ] : null;?>';">Next</button></div>
              </div>
            </div>
          </div>
        </div>
        <div class='card-body bg-dark text-white'>
            <div class='card-columns'>
                <div class='card card-primary my-3'><form action='user.php?ID=<?php echo $User[ 'ID' ];?>' method='POST' enctype="multipart/form-data">
                    <input type='hidden' value='<?php echo $User[ 'ID' ];?>' name='ID' />
                    <div class='card-heading'>
                      <div class='row g-0 px-3 py-2'>
                        <div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Info( 1 );?><span>Infomation</span></h5></div>
                        <div class='col-2'>&nbsp;</div>
                      </div>
                    </div>
                    <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Infomation' ] ) && $_SESSION[ 'Cards' ][ 'Infomation' ] == 0 ? "style='display:none;'" : null;?>>
                        <div class='row' style='padding-top:10px;padding-bottom:10px;'>
                            <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Email:</div>
                            <div class='col-8'><input type='text' name='Email' class='form-control edit' placeholder='Email' value='<?php echo $User[ 'Email' ];?>' /></div>
                        </div>
                        <div class='row' style='padding-top:10px;padding-bottom:10px;'>
                            <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Password:</div>
                            <div class='col-8'><input type='password' name='Password' class='form-control edit' placeholder='Password' value='<?php echo str_repeat( '*', strlen( $User[ 'Password' ] ) );?>' /></div>
                        </div>
                        <div class='row' style='padding-top:10px;padding-bottom:10px;'>
                            <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Branch:</div>
                            <div class='col-8'><input type='text' name='Branch' class='form-control edit' placeholder='Branch' value='<?php echo $User[ 'Branch' ];?>' /></div>
                        </div>
                        <div class='row' style='padding-top:10px;padding-bottom:10px;'>
                            <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?>Type:</div>
                            <div class='col-8'><input type='text' name='Branch_Type' class='form-control edit' placeholder='Type' value='<?php echo $User[ 'Branch_Type' ];?>' /></div>
                        </div>
                        <div class='row' style='padding-top:10px;padding-bottom:10px;'>
                            <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?>ID:</div>
                            <div class='col-8'><input type='text' name='Branch_ID' class='form-control edit' placeholder='ID' value='<?php echo $User[ 'Branch_ID' ];?>' /></div>
                        </div>
                                                 <div class='row'>  
                              <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?>Image:</div>  
                              <div class='col-8'><?php if(isset($User['Picture']) && strlen($User['Picture']) > 0){?><img width='100%' src="<?php   
                                print "data:" . $User['Picture_Type'] . ";base64, " . $User['Picture']; 
                              ?>" /><?php }?><input type='file' name='Picture' class='form-control edit' /></div><?php ?>   
                            </div>
                    </div>
                    <div class='card-footer'>
	                  	<div class='row'>
	                      	<div class='col-12'><button class='form-control' type='submit'>Save</button></div>
	                  	</div>
	              	</div>
                </div>
</form>

                <div class="card card-primary my-3">
        
                <input type="hidden" name="UserID" value="<?php echo $User[ 'ID' ];?>">
                <input type="hidden" name="Access_Table" value="User">
                <input type="hidden" name="Group_Privilege" value="0">
                <input type="hidden" name="Other_Privilege" value="0">
              <div class="card-heading">
                <div class="row g-0 px-3 py-2">
                  <div class="col-10"><h5><i class="fa fa-user fa-fw fa-1x" aria-hidden="true"></i><span>Privileges</span></h5></div>
                
                  <div class="col-2"><button class="h-100 w-100" onclick="document.location.href='privileges.php?UserID=<?php echo $User[ 'ID' ];?>';"><i class="fa fa-search fa-fw fa-1x" aria-hidden="true"></i></button></div>
                </div>
              </div>
              <?php if ($User[ 'ID' ]>0) { ?>
              <div class="card-body bg-dark">
                <div class="row">
                  <div class="col-4"><i class="fa fa-user fa-fw fa-1x" aria-hidden="true"></i> Permissions:</div>
                  <div class="col-8"><select name="User_Privilege[]" class="form-control"  multiple="mutilple">
                                    
                <?php if(in_array(8,$priv)){ echo '<option value="8" >Read</option>'; }?>
                 <?php if(in_array(4,$priv)){echo '<option value="4"  >Write</option>';} ?>
                  <?php if(in_array(1,$priv)){echo '<option value="1"  >Execute</option>';} ?>
                    <?php if(in_array(2,$priv)){echo '<option value="2"  >Delete</option>';} ?>
                                    </select></div>

                  </div>
             
              </div>
             <?php } ?>
          
            </div>

                      </div>
                      </div>
                        </div>
                          </div>

            </div>
        </div>
    </div>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=user<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
