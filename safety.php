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
        ||  !isset( $Privileges[ 'Unit' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'Unit' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Peter D. Speranza">
    <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
       <?php  $_GET[ 'Bootstrap' ] = '5.1';?>
       <?php  $_GET[ 'Entity_CSS' ] = 1;?>
       <?php	require( bin_meta . 'index.php');?>
       <?php	require( bin_css  . 'index.php');?>
       <?php  require( bin_js   . 'index.php');?>
</head>
<body onload='finishLoadingPage();' style='background-color:#1d1d1d;'>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
      <?php require(bin_php .  'element/navigation.php');?>
      <?php require( bin_php . 'element/loading.php');?>
      <div id="page-wrapper" class='content'>
  			<div class="panel panel-primary">
  				<div class='panel-heading'>Safety Form</div>
  				<div class="panel-body"><form id='form-safety'>
            <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
            <div class='row'>
              <div class='col-xs-3'>Name:</div>
              <div class='col-xs-9'><input name='Name' type='text' value='<?php echo $User['First_Name'] . ' ' . $User['Last_Name'];?>' /></div>
            </div>
            <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
            <div class='row'>
              <div class='col-xs-3'>Anonymous:</div>
              <div class='col-xs-9'><input type='checkbox' name='Anonymous' style='width:25px;height:25px;' onChange='toggleAnonymity();' /></div>
            </div>
            <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
            <div class='row'>
              <div class='col-xs-12'>Report:</div>
              <div class='col-xs-12'><textarea style='width:100%;' rows='9'></textarea></div>
            </div>
            <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
            <div class='row'><div class='col-xs-12'><button onClick='submitSafetyForm(this);' style='width:100%;height:42px;'>Submit</button></div></div>
            <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
            <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
          </form></div>
        </div>
      </div>
    </div>
    <!-- Bootstrap Core JavaScript -->


    <script>
      function toggleAnonymity(){
        var toggle = $("input[name='Anonymous']").prop('checked');
        if(toggle){
          $("input[name='Name']").val("ANONYMOUS");
        } else {
          $("input[name='Name']").val("<?php echo $User['First_Name'] . ' ' . $User['Last_Name'];?>");
        }
      }
      function submitSafetyForm(link){
        $(link).html("Saving <img src='media/images/spinner.gif' height='25px' width='auto' />");
        $(link).attr('disabled','disabled');
        var formData = new FormData();
        formData.append("Report",$("textarea").val());
        formData.append("Anonymous",$("input[name='Anonymous']").prop('checked'));
        $.ajax({
          cache: false,
          processData: false,
          contentType: false,
          timeout:15000,
          error:function(XMLHttpRequest, textStatus, errorThrown){
            alert('Your ticket did not save. Please check your internet.')
            $(link).html("Save");
            $(link).prop('disabled',false);
          },
          url:"bin/php/post/safety_form.php",
          method:"POST",
          data:formData,
          success:function(code){
            alert("Success");
            $(link).prop("disabled",false);
          }
        });
      }
    </script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=units.php';</script></head></html><?php }?>
