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
        ||  !isset( $Privileges[ 'Contract' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'Contract' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        \singleton\database::getInstance( )->query(
          null,
          " INSERT INTO Activity([User], [Date], [Page] )
            VALUES( ?, ?, ? );",
          array(
            $_SESSION[ 'Connection' ][ 'User' ],
            date('Y-m-d H:i:s'),
            'contract.php'
        )
      );
      $ID = isset( $_GET[ 'ID' ] )
			? $_GET[ 'ID' ]
			: (
				isset( $_POST[ 'ID' ] )
					? $_POST[ 'ID' ]
					: null
			);
		$Name = isset( $_GET[ 'Name' ] )
    		? $_GET[ 'Name' ]
    		: (
    			isset( $_POST[ 'Name' ] )
    				? $_POST[ 'Name' ]
    				: null
    		);
      $result = \singleton\database::getInstance( )->query(
        null,
          "	   SELECT 	  Contract.ID      AS ID,
                          Contract.Job     AS Job,
                          Contract.Owner   AS Owner,
                          Contract.Type    AS Type,
                          Contract.Phone   AS Phone,
                          Contract.Email   AS Email,
                          Contract.loc     AS location,
                          Contract.City    AS City,
                          Contract.State   AS State,
                          Contract.Zip     AS Zip,
                          Contract.Latt 	 AS Latitude,
                          Contract.fLong   AS Longitude,
                          Contract.Website AS Website,
                          Contract.Geolock AS Geofence
                FROM      dbo.Contract
                    ) AS Contract
            WHERE   	Contract.ID = ?
                OR 	Contract.Name = ?;",
          array(
            $ID,
            $Name
          )
      );
      $Contract =   (       empty( $ID )
                      &&    !empty( $Name )
                      &&    !$result
                    ) || (  empty( $ID )
                      &&    empty( $Name )
                    )  ? array(
        'ID' => null,
        'Name' => null,
        'Contact' => null,
        'Geofence' => null,
        'Type' => null,
        'Status' => null,
        'Website' => null,
        'Internet' => null,
        'Street' => null,
        'City' => null,
        'State' => null,
        'Zip' => null,
        'Latitude' => null,
        'Longitude' => null,
        'Phone'   =>  null,
        'Email'   =>  null,
        'Rolodex' => null,
        'Phone' => null,
        'Email' => null
      ) : sqlsrv_fetch_array($result);


      if( isset( $_POST ) && count( $_POST ) > 0 ){
        $Contract[ 'ID' ] 		= isset( $_POST[ 'ID' ] ) 	 ? $_POST[ 'ID' ] 	 : $Contract[ 'ID' ];
        $Contract[ 'Job' ] 	= isset( $_POST[ 'Job' ] ) ? $_POST[ 'Job' ] : $Contract[ 'Job' ];
        $Contract[ 'Loc' ] 		= isset( $_POST[ 'Loc' ] ) 	 ? $_POST[ 'Loc' ] 	 : $Contract[ 'Loc' ];
        $Contract[ 'Owner' ] 		= isset( $_POST[ 'Owner' ] ) 	 ? $_POST[ 'Owner' ] 	 : $Contract[ 'Owner' ];
        $Contract[ 'Review' ]     = isset( $_POST[ 'Review' ] ) 	   ? $_POST[ 'Review' ] 	   : $Contract[ 'Review' ];
        $Contract[ 'Disc1' ] 	= isset( $_POST[ 'Disc1' ] ) 	 ? $_POST[ 'Disc1' ] 	 : $Contract[ 'Disc1' ];
        $Contract[ 'Disc2' ] 	= isset( $_POST[ 'Disc2' ] ) 	 ? $_POST[ 'Disc2' ] 	 : $Contract[ 'Disc2' ];
        $Contract[ 'Disc3' ] 		= isset( $_POST[ 'Disc3' ] ) 	 ? $_POST[ 'Disc3' ] 	 : $Contract[ 'Disc3' ];
        $Contract[ 'Disc4' ] 		= isset( $_POST[ 'Disc4' ] ) 	 ? $_POST[ 'Disc4' ] 	 : $Contract[ 'Disc4' ];
        $Contract[ 'Disc5' ] 			= isset( $_POST[ 'Disc5' ] ) 		 ? $_POST[ 'Disc5' ] 		 : $Contract[ 'Disc5' ];
        $Contract[ 'Disc6' ] 	= isset( $_POST[ 'Disc6' ] )  ? $_POST[ 'Disc6' ]  : $Contract[ 'Disc6' ];
        $Contract[ 'DiscType' ] 	= isset( $_POST[ 'DiscType' ] ) ? $_POST[ 'DiscType' ] : $Contract[ 'DiscType' ];
        $Contract[ 'DiscRate' ] 	= isset( $_POST[ 'DiscRate' ] ) ? $_POST[ 'DiscRate' ] : $Contract[ 'DiscRate' ];
        $Contract[ 'BCycle' ] 	= isset( $_POST[ 'BCycle' ] ) ? $_POST[ 'BCycle' ] : $Contract[ 'BCycle' ];
        $Contract[ 'BStart' ] 	= isset( $_POST[ 'BStart' ] ) ? $_POST[ 'BStart' ] : $Contract[ 'BStart' ];
        $Contract[ 'BLenght' ] 	= isset( $_POST[ 'BLenght' ] ) ? $_POST[ 'BLenght' ] : $Contract[ 'BLenght' ];
        $Contract[ 'BFinish' ] 	= isset( $_POST[ 'BFinish' ] ) ? $_POST[ 'BFinish' ] : $Contract[ 'BFinish' ];
        $Contract[ 'BAmt' ] 	= isset( $_POST[ 'BAmt' ] ) ? $_POST[ 'BAmt' ] : $Contract[ 'BAmt' ];
        $Contract[ 'BEscCycle' ] 	= isset( $_POST[ 'BEscCycle' ] ) ? $_POST[ 'BEscCycle' ] : $Contract[ 'BEscCycle' ];
        $Contract[ 'BEscFact' ] 	= isset( $_POST[ 'BEscFact' ] ) ? $_POST[ 'BEscFact' ] : $Contract[ 'BEscFact' ];
        $Contract[ 'SCycle' ] 	= isset( $_POST[ 'SCycle' ] ) ? $_POST[ 'SCycle' ] : $Contract[ 'SCycle' ];
        $Contract[ 'SDay' ] 	= isset( $_POST[ 'SDay' ] ) ? $_POST[ 'SDay' ] : $Contract[ 'SDay' ];
        $Contract[ 'SDate' ] 	= isset( $_POST[ 'SDate' ] ) ? $_POST[ 'SDate' ] : $Contract[ 'SDate' ];
        $Contract[ 'STime' ] 	= isset( $_POST[ 'STime' ] ) ? $_POST[ 'STime' ] : $Contract[ 'STime' ];
        $Contract[ 'SWE' ] 	= isset( $_POST[ 'SWE' ] ) ? $_POST[ 'SWE' ] : $Contract[ 'SWE' ];
        $Contract[ 'SStart' ] 	= isset( $_POST[ 'SStart' ] ) ? $_POST[ 'SStart' ] : $Contract[ 'SStart' ];
        $Contract[ 'Detail' ] 	= isset( $_POST[ 'Detail' ] ) ? $_POST[ 'Detail' ] : $Contract[ 'Detail' ];
        $Contract[ 'Cycle' ] 	= isset( $_POST[ 'Cycle' ] ) ? $_POST[ 'Cycle' ] : $Contract[ 'Cycle' ];
        $Contract[ 'EscLast' ] 	= isset( $_POST[ 'EscLast' ] ) ? $_POST[ 'EscLast' ] : $Contract[ 'EscLast' ];
        $Contract[ 'OldAmt' ] 	= isset( $_POST[ 'OldAmt' ] ) ? $_POST[ 'OldAmt' ] : $Contract[ 'OldAmt' ];
        $Contract[ 'WK' ] 	= isset( $_POST[ 'WK' ] ) ? $_POST[ 'WK' ] : $Contract[ 'WK' ];
        $Contract[ 'Skill' ] 	= isset( $_POST[ 'Skill' ] ) ? $_POST[ 'Skill' ] : $Contract[ 'Skill' ];
        $Contract[ 'Status' ] 	= isset( $_POST[ 'Status' ] ) ? $_POST[ 'Status' ] : $Contract[ 'Status' ];
        $Contract[ 'Hours' ] 	= isset( $_POST[ 'Hours' ] ) ? $_POST[ 'Hours' ] : $Contract[ 'Hours' ];
        $Contract[ 'Hour' ] 	= isset( $_POST[ 'Hour' ] ) ? $_POST[ 'Hour' ] : $Contract[ 'Hour' ];
        $Contract[ 'Terms' ] 	= isset( $_POST[ 'Terms' ] ) ? $_POST[ 'Terms' ] : $Contract[ 'Terms' ];
        $Contract[ 'OffService' ] 	= isset( $_POST[ 'OffService' ] ) ? $_POST[ 'OffService' ] : $Contract[ 'OffService' ];
        $Contract[ 'TFMID' ] 	= isset( $_POST[ 'TFMID' ] ) ? $_POST[ 'TFMID' ] : $Contract[ 'TFMID' ];
        $Contract[ 'TFMSource' ] 	= isset( $_POST[ 'TFMSource' ] ) ? $_POST[ 'TFMSource' ] : $Contract[ 'TFMSource' ];
        $Contract[ 'sDay2' ] 	= isset( $_POST[ 'sDay2' ] ) ? $_POST[ 'sDay2' ] : $Contract[ 'sDay2' ];
        $Contract[ 'sTime2' ] 	= isset( $_POST[ 'sTime2' ] ) ? $_POST[ 'sTime2' ] : $Contract[ 'sTime2' ];
        $Contract[ 'sWE2' ] 	= isset( $_POST[ 'sWE2' ] ) ? $_POST[ 'sWE2' ] : $Contract[ 'sWE2' ];
        if( in_array( $_POST[ 'ID' ], array( null, 0, '', ' ' ) ) ){
          $result = \singleton\database::getInstance( )->query(
            null,
            "	DECLARE @MAXID INT;
              SET @MAXID = CASE WHEN ( SELECT Max( ID ) FROM Contract ) IS NULL THEN 0 ELSE ( SELECT Max( ID ) FROM Contract ) END ;
              INSERT INTO Contract(
                [ID],
                [Job],
                [Loc],
                [Owner],
                [Review],
                [Disc1],
                [Disc2],
                [Disc3],
                [Disc4],
                [Disc5],
                [Disc6],
                [DiscType],
                [DiscRate],
                [BCycle],
                [BStart],
                [BLenght],
                [BFinish],
                [BAmt],
                [BEscType],
                [BEscCycle],
                [BEscFact],
                [SCycle],
                [SType],
                [SDay],
                [SDate],
                [STime],
                [SWE],
                [SStart],
                [Detail],
                [Cycle],
                [EscLast],
                [OldAmt],
                [WK],
                [Skill],
                [Status],
                [Hours],
                [Hour],
                [Terms],
                [OffService],
                [TFMID],
                [TFMSource],
                [sDay2],
                [sDate2],
                [sTime2],
                [sWE2]
              )
              VALUES( @MAXID + 1 , " . implode( ',', array_fill( 0, 45, '?' ) ) . " );
              SELECT @MAXID + 1;",
            array(
              $Contract[ 'Type' ],
              $Contract[ 'Name' ],
              $Contract[ 'Website' ],
              $Contract[ 'Phone' ],
              $Contract[ 'Contact'],
              $Contract[ 'Email' ],
              $Contract[ 'Street' ],
              $Contract[ 'City' ],
              $Contract[ 'State' ],
              $Contract[ 'Zip' ],
              $Contract[ 'Latitude' ],
              $Contract[ 'Longitude' ],
              !is_null( $Contract[ 'Geofence' ] ) ? $Contract[ 'Geofence' ] : 0
            )
          );
          sqlsrv_next_result( $result );
          $Contract[ 'ID' ] = sqlsrv_fetch_array( $result )[ 0 ];

          header( 'Location: Contract.php?ID=' . $Contract[ 'ID' ] );
          exit;
        } else {
          \singleton\database::getInstance( )->query(
            null,
            "	UPDATE 	Rol
              SET 	Rol.Name = ?,
                  Rol.Contact = ?,
                  Rol.Type = ?,
                  Rol.Website = ?,
                  Rol.Address = ?,
                  Rol.City = ?,
                  Rol.State = ?,
                  Rol.Zip = ?,
                  Rol.Latt = ?,
                  Rol.fLong = ?,
                  Rol.Phone = ?,
                  Rol.EMail = ?

              WHERE 	Rol.ID = ?;",
            array(
              $Contract[ 'Customer' ],
              $Contract[ 'Location' ],
              $Contract[ 'Job' ],
              $Contract[ 'Start_Date' ],
              $Contract[ 'End_Date' ],
              $Contract[ 'Length' ],
              $Contract[ 'Amount' ],
              $Contract[ 'Cycle' ],
              $Contract[ 'Escalation_Factor' ],
              $Contract[ 'Escalation_Date' ],
            )
          );
        }
      }
?><!DOCTYPE html>
<html lang='en'>
<head>
    <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
    <?php
    	$_GET[ 'Bootstrap' ] = '5.1';
      $_GET[ 'Entity_CSS' ] = 1;
      	require( bin_meta . 'index.php');
      	require( bin_css  . 'index.php');
      	require( bin_js   . 'index.php');
    ?>
</head>
<body>
    <div id="wrapper">
        <?php require( bin_php . 'element/navigation.php' ); ?>
        <div id="page-wrapper" class='content'>
        	<div class='card card-primary'>
        		<div class='card-heading'>
        			<div class='row g-0 px-3 py-2'>
        				<div class='col-6'><h5><?php \singleton\fontawesome::getInstance( )->Customer( 1 );?><a href='contacts.php?<?php echo isset( $_SESSION[ 'Tables' ][ 'Contacts' ][ 0 ] ) ? http_build_query( is_array( $_SESSION[ 'Tables' ][ 'Contacts' ][ 0 ] ) ? $_SESSION[ 'Tables' ][ 'Contracts' ][ 0 ] : array( ) ) : null;?>'>Contracts</a>: <span><?php echo is_null( $Contract[ 'ID' ] ) ? 'New' : $Contract[ 'Name' ];?></span></h5></div>
        				<div class='col-2'></div>
        				<div class='col-2'>
        					<div class='row g-0'>
        						<div class='col-4'><button class='form-control rounded' onClick="document.location.href='contract.php';">Create</button></div>
        						<div class='col-4'><button class='form-control rounded' onClick="document.location.href='contract.php?ID=<?php echo $Contract[ 'ID' ];?>';">Refresh</button></div>
        					</div>
        				</div>
        				<div class='col-2'>
        					<div class='row g-0'>
        						<div class='col-4'><button class='form-control rounded' onClick="document.location.href='contract.php?ID=<?php echo !is_null( $Contract[ 'ID' ] ) ? array_keys( $_SESSION[ 'Tables' ][ 'Contacts' ], true )[ array_search( $Contract[ 'ID' ], array_keys( $_SESSION[ 'Tables' ][ 'Contacts' ], true ) ) - 1 ] : null;?>';">Previous</button></div>
        						<div class='col-4'><button class='form-control rounded' onClick="document.location.href='contract.php?<?php echo http_build_query( is_array( $_SESSION[ 'Tables' ][ 'Contacts' ][ 0 ] ) ? $_SESSION[ 'Tables' ][ 'Contacts' ][ 0 ] : array( ) );?>';">Table</button></div>
        						<div class='col-4'><button class='form-control rounded' onClick="document.location.href='contract.php?ID=<?php echo !is_null( $Contract[ 'ID' ] )? array_keys( $_SESSION[ 'Tables' ][ 'Contacts' ], true )[ array_search( $Contract[ 'ID' ], array_keys( $_SESSION[ 'Tables' ][ 'Contacts' ], true ) ) + 1 ] : null;?>';">Next</button></div>
        					</div>
        				</div>
        			</div>
        		</div>
        		<div class='card-body bg-dark text-white'>
					<div class='card-columns'>
						<div class='card card-primary my-3'><form action='contract.php?ID=<?php echo $Contract[ 'ID' ];?>' method='POST'>
							<div class='card-heading'>
								<div class='row g-0 px-3 py-2'>
									<div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Info( 1 );?><span>New Contract</span></h5></div>
									<div class='col-2'>&nbsp;</div>
								</div>
							</div>
						 	<div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Contracts' ] ) && $_SESSION[ 'Cards' ][ 'Contracts' ] == 0 ? "style='display:none;'" : null;?>
						 		<input type='hidden' name='ID' value='<?php echo $Contract[ 'ID' ];?>' />
								<div class='row g-0'>
									<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Customer(1);?>Customer:</div>
									<div class='col-8'><input type='text' class='form-control edit animation-focus' name='Customer' value='<?php echo $Contract['Customer'];?>' /></div>
								</div>
								<div class='row g-0'>
									<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Web(1);?> Location:</div>
									<div class='col-8'><input type='text' class='form-control edit animation-focus' name='Location' value='<?php echo $Contract['Location'];?>' /></div>
								</div>
                <div class='row'>
                  <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->User( 1 );?> Job:</div>
                  <div class='col-8'><input type='text' class='form-control edit' name='Job' value='<?php echo $Contract[ 'Job' ];?>' /></div>
                </div>
                <div class='row'>
                  <div class='col-4 border-bottom border-white my-auto' ><?php \singleton\fontawesome::getInstance( )->Phone( 1 );?> Start Date:</div>
                  <div class='col-8'><input type='text' class='form-control edit' name='Start_Date' value='<?php echo $Contract[ 'Start_Date' ];?>' /></div>
                </div>
                <div class='row'>
                  <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Email( 1 );?> End Date:</div>
                  <div class='col-8'><input type='text' class='form-control edit' name='End_Date' value='<?php echo $Contract[ 'End_Date' ];?>' /></div>
                </div>
								<div class='row g-0'>
									<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Address(1);?> Length:</div>
                  <div class='col-8'><input type='text' class='form-control edit' name='End_Date' value='<?php echo $Contract[ 'End_Date' ];?>' /></div>
								</div>
                <div class='row g-0'>
                  <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Address(1);?> Amount:</div>
                  <div class='col-8'><input type='text' class='form-control edit' name='Amount' value='<?php echo $Contract[ 'Amount' ];?>' /></div>
                </div>
                <div class='row g-0'>
									<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Address(1);?> Cycle:</div>
                  <div class='col-8'><input type='text' class='form-control edit' name='Cycle' value='<?php echo $Contract[ 'Cycle' ];?>' /></div>
								</div>
                <div class='row g-0'>
                  <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Address(1);?> Escalation Factor:</div>
                  <div class='col-8'><input type='text' class='form-control edit' name='Escalation_Factor' value='<?php echo $Contract[ 'Escalation_Factor' ];?>' /></div>
                </div>
                <div class='row g-0'>
                  <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Address(1);?> Escalation Date:</div>
                  <div class='col-8'><input type='text' class='form-control edit' name='Escalation_Date' value='<?php echo $Contract[ 'Escalation_Date' ];?>' /></div>
                </div>
                <div class='row g-0'>
                  <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Address(1);?> Escalation Cycle:</div>
                  <div class='col-8'><input type='text' class='form-control edit' name='Escalation_Cycle' value='<?php echo $Contract[ 'Escalation_Cycle' ];?>' /></div>
                </div>
                <div class='row g-0'>
                  <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Address(1);?> Link:</div>
                  <div class='col-8'><input type='text' class='form-control edit' name='Link' value='<?php echo $Contract[ 'Link' ];?>' /></div>
                </div>
                <div class='row g-0'>
                  <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Address(1);?> Remarks:</div>
                  <div class='col-8'><input type='text' class='form-control edit' name='Remarks' value='<?php echo $Contract[ 'Remarks' ];?>' /></div>
                </div>
              <div class='card-footer'>
                  <div class='row'>
                      <div class='col-12'><button class='form-control' type='submit'>Save</button></div>
                  </div>
              </div>
						</form></div>
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
} else {?><html><head><script>document.location.href="../login.php?Forward=contact<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
