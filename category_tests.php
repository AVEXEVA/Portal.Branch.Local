<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [
        'read_and_close' => true
    ] ); 
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/bin/php/index.php' );
}
if( isset( $_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
    $result = $database->query(
        null,
        "   SELECT  *
                FROM    Connection
                WHERE       Connection.Connector = ?
                    AND Connection.Hash  = ?;",
        array(
            $_SESSION[ 'User' ],
            $_SESSION[ 'Hash' ]
        )
    );
    $Connection = sqlsrv_fetch_array( $result );
    //User
    $result = $database->query(
        null,
        "   SELECT  *,
                    Emp.fFirst AS First_Name,
                    Emp.Last   AS Last_Name
            FROM    Emp
            WHERE   Emp.ID = ?;",
        array(
            $_SESSION[ 'User' ]
        )
    );
    $User = sqlsrv_fetch_array( $result );
    //Privileges
    $result = $database->query(
        null,
        "   SELECT  *
            FROM    Privilege
            WHERE   Privilege.User_ID = ?;",
        array(
            $_SESSION['User']
        )
    );
    $Privileges = array();
    if( $result ){while( $Privilege = sqlsrv_fetch_array( $result ) ){ $Privileges[ $Privilege[ 'Access_Table' ] ] = $Privilege; } }
    if( !isset( $Connection[ 'ID' ] )
        || !isset($Privileges[ 'Category_Test' ])
            || $Privileges[ 'Category_Test' ][ 'User_Privilege' ]  < 4
            || $Privileges[ 'Category_Test' ][ 'Group_Privilege' ] < 4
            || $Privileges[ 'Category_Test' ][ 'Other_Privilege' ] < 4
    ){      
        ?><?php require( '../404.html' );?><?php 
    } else {
        $database->query(
          null,
          "   INSERT INTO Activity([User], [Date], [Page])
              VALUES( ?, ?, ? );",
          array(
              $_SESSION['User'],
              date( 'Y-m-d H:i:s' ),
              'category_tests.php'
          )
      );
?><!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
    <?php $_GET[ 'Bootstrap' ] = '5.1';?>
    <?php require( bin_meta . 'index.php');?>
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js  . 'index.php' );?>
</head>
<body onload='finishLoadingPage();'>
    <div id="wrapper">
        <?php require( bin_php . 'element/navigation.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content'>
            <div class="card card-full card-primary border-0">
                <div class="card-heading"><h4><?php \singleton\fontawesome::getInstance( )->Inspection( 1 );?> Category Tests</h4></div>
                <div class="form-mobile card-body bg-dark text-white"><form method='GET' action='category_tests.php'>
                  <div class='row'><div class='col-12'>&nbsp;</div></div>
                  <div class='row'>
                      <div class='col-4'>Search:</div>
                      <div class='col-8'><input type='text' name='Search' placeholder='Search' class='redraw' /></div>
                  </div>
                  <div class='row'><div class='col-12'>&nbsp;</div></div>
                  <div class='row'>
                    <div class='col-4'>ID:</div>
                    <div class='col-8'><input type='text' name='ID' placeholder='ID' class='redraw' value='<?php echo $_GET[ 'ID' ];?>' /></div>
                  </div>
                  <div class='row'>
                    <div class='col-4'>Name:</div>
                    <div class='col-8'><input type='text' name='Name' placeholder='Name' class='redraw' value='<?php echo $_GET[ 'Name' ];?>' /></div>
                  </div>
                </form></div>
                <div class="card-body bg-dark">
                    <table id='Table_Category_Tests' class='display' cellspacing='0' width='100%'>
                        <thead><tr class='text-center'>
                            <th class='text-white border border-white' title='ID'>ID</th>
                            <th class='text-white border border-white' title='Name'>Name</th>
                        </tr><tr class='form-desktop'>
                            <th class='text-white border border-white' title='ID'><input class='redraw form-control' type='text' name='ID' value='<?php echo isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null; ?>' /></th>
                            <th class='text-white border border-white' title='Name'><input class='redraw form-control' type='text' name='Name' value='<?php echo isset( $_GET[ 'Name' ] ) ? $_GET[ 'Name' ] : null; ?>' /></th>
                        </tr></thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php }
} else {?><script>document.location.href='../login.php?Forward=Category_Tests.php&<?php echo http_build_query( $_GET );?>';</script><?php }?>