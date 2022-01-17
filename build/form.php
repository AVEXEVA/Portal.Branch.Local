<?php
?><!DOCTYPE html>
<html lang='en'>
<head>
	<title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
    <?php  	$_GET[ 'Bootstrap' ] = '5.1';?>
	<?php  	$_GET[ 'Entity_CSS' ] = 1;?>
	<?php	require( bin_meta . 'index.php');?>
	<?php	require( bin_css  . 'index.php');?>
	<?php  	require( bin_js   . 'index.php');?>
	<script>
		function updateDatabases( ){
			var formData = new FormData( $( 'form#Databases' )[ 0 ] );
			$.ajax( {
				processData: false,
				contentType: false,
				url : 'build/post/database.php',
				data : formData,
				method : 'POST',
				success : function( code ){ }
			} );
		}
    </script>
</head>
<body>
	<?php require( bin_php . 'element/navigation.php');?>
  	<div id='page-wrapper' class='content'>
  		<div class='card card-primary p-3 col-lg-3 text-white'>
  			<div class='card-heading'>Databases</div>
  			<div class='card-body'>
			  	<form id='Databases' action='index.php' method='POST' class='p-3'><?php 
				  	$result = \singleton\database::getInstance( )->query( 
						Portal,
						"	SELECT 	[Database].[Name] 
							FROM 	[Database];"
					);
					if( $result ){ while( $row = sqlsrv_fetch_array( $result ) ){
						if( $row[ 'Name' ] == 'Portal' ){
							?><div class='row'>
								<label class='col-4'><?php echo $row[ 'Name' ];?></label>
								<input class='col-8' name='Portal' type='checkbox' value='1' checked disabled />
							</div><?php
						} else {
							?><div class='row'>
								<label class='col-4'><?php echo $row[ 'Name' ];?></label>
								<input onChange='updateDatabases( );' class='col-8' name='Databases[ ]' type='checkbox' value='<?php echo $row[ 'Name' ];?>' />
							</div><?php
						}
					}}?>
				</form>
			</div>
		</div>
		<div class='card card-primary p-3 col-lg-12 text-white'>
			<div class='row'>
				<div class='col-12'>
					<button type='button' onClick="document.location.href='index.php';">Save Databases' Design</button>
				</div>
			</div>
		</div>
	</div>
</body>
</html><?php
?>