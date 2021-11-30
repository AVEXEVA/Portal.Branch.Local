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
        $ID = isset( $_GET[ 'ID' ] )
            ? $_GET[ 'ID' ]
            : (
            isset( $_POST[ 'ID' ] )
                ? $_POST[ 'ID' ]
                : null
            );
        $City_ID = isset( $_GET[ 'City_ID' ] )
            ? $_GET[ 'City_ID' ]
            : (
            isset( $_POST[ 'City_ID' ] )
                ? $_POST[ 'City_ID' ]
                : null
            );
$result = \singleton\database::getInstance( )->query(
        null,
    " SELECT  TOP 1
                    Unit.ID,
                    Unit.fDesc          AS Description,
                    Unit.Remarks        AS Note,
                    Unit.Building_ID    AS Units,
                    Unit.City_ID        AS State,
                    Unit.Loc            AS Location,
                    Unit.Cat            AS Category,
                    Unit.Type           AS Type,
                    Unit.Location_Category       AS Building,
                    Unit.Manuf       AS     Manufacturer,
                    Unit.Install       AS     Installation,
                    Unit.InstallBy       AS     Installer,
                    Unit.Since          AS Created,
                    Unit.Last           AS Maintained,
                    Unit.Price          AS Price,
                    Unit.Loc             AS Location,
                    Unit.Owner             AS Customer,
                    Unit.fGroup             AS Bank,
                    Unit.Serial             AS Serial,
                    Unit.Template           AS Template,
                    Unit.Status             AS Status,
                    Unit.TFMID              AS TFMID,
                    Unit.TFMSource          AS TFMSource
            FROM    Unit
                    LEFT JOIN Loc           ON Unit.Loc = Loc.Loc
                     LEFT JOIN Owner           ON Unit.Owner = Owner.ID    
            WHERE      Unit.ID = ?
                    OR Unit.City_ID = ?;",
            array(
                isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null,
                isset( $_GET[ 'City_ID' ] ) ? $_GET[ 'City_ID' ] : null
            )
        );

$Unit =   (  empty( $ID )
    &&  !empty( $City_ID )
    &&  !$result
)    || (empty( $ID )
    &&  empty( $City_ID )
)    ? array(
    'ID' => null,
    'Description' => null,
    'Note' => null,
    'Units' => null,
    'State' => null,
    'Location' => null,
    'Category' => null,
    'Type' => null,
    'Building' => null,
    'Manufacturer' => null,
    'Installation' => null,
    'Installer' => null,
    'Created' => null,
    'Maintained' => null,
    'Price' => null,
    'Customer'   =>  null,
    'Bank'   =>  null,
    'Serial' => null,
    'Template' => null,
    'TFMID' => null,
    'TFMSource' => null,
    'Status' => null
) : sqlsrv_fetch_array($result);

if( isset( $_POST ) && count( $_POST ) > 0 ){

    $Unit[ 'fDesc' ] 		= isset( $_POST[ 'fDesc' ] ) 	 ? $_POST[ 'fDesc' ] 	 : $Unit[ 'Description' ];
    $Unit[ 'Remarks' ] 	= isset( $_POST[ 'Remarks' ] ) 	 ? $_POST[ 'Remarks' ] 	 : $Unit[ 'Note' ];
    $Unit[ 'Building_ID' ] 		=isset( $_POST[ 'Building_ID' ] ) 	 ? $_POST[ 'Building_ID' ] 	 : $Unit[ 'Units' ];
    $Unit[ 'City_ID' ] 		= isset( $_POST[ 'City_ID' ] ) 	 ? $_POST[ 'City_ID' ] 	 : $Unit[ 'State' ];
    $Unit[ 'Type' ] 		= isset( $_POST[ 'Type' ] ) 	 ? $_POST[ 'Type' ] 	 : $Unit[ 'Type' ];
    $Unit[ 'Manuf' ] = isset( $_POST[ 'Manuf' ] ) 	 ? $_POST[ 'Manuf' ] 	 : $Unit[ 'Manufacturer' ];
    $Unit[ 'InstallBy' ] = isset( $_POST[ 'InstallBy' ] ) 	 ? $_POST[ 'InstallBy' ] 	 : $Unit[ 'Installation' ];
    $Unit[ 'Last' ]     = isset( $_POST[ 'Last' ] ) 	 ? $_POST[ 'Last' ] 	 : $Unit[ 'Last' ];
    $Unit[ 'Owner' ] 	= isset( $_POST[ 'Owner' ] ) 	 ? $_POST[ 'Owner' ] 	 : $Unit[ 'Customer' ];
    $Unit[ 'Serial' ] 	= isset( $_POST[ 'Serial' ] ) 	 ? $_POST[ 'Serial' ] 	 : $Unit[ 'Serial' ];
    $Unit[ 'Status' ] = isset( $_POST[ 'Status' ] ) 	 ? $_POST[ 'Status' ] 	 : $Unit[ 'Status' ];
    $Unit[ 'TFMID' ] 	= isset( $_POST[ 'TFMID' ] ) 	 ? $_POST[ 'TFMID' ] 	 : $Unit[ 'TFMID' ];
    $Unit[ 'Loc' ] 		= isset( $_POST[ 'Loc' ] ) 	 ? $_POST[ 'Loc' ] 	 : $Unit[ 'Location' ];
    $Unit[ 'Cat' ] 		= isset( $_POST[ 'Cat' ] ) 	 ? $_POST[ 'Cat' ] 	 : $Unit[ 'Category' ];
    $Unit[ 'Location_Category' ] 			= isset( $_POST[ 'Location_Category' ] ) 	 ? $_POST[ 'Location_Category' ] 	 : $Unit[ 'Building' ];
    $Unit[ 'Install' ] 	= isset( $_POST[ 'Install' ] ) 	 ? $_POST[ 'Install' ] 	 : $Unit[ 'Install' ];
    $Unit[ 'Since' ] 	= isset( $_POST[ 'Since' ] ) 	 ? $_POST[ 'Since' ] 	 : $Unit[ 'Since' ];
    $Unit[ 'Price' ] 	= isset( $_POST[ 'Price' ] ) 	 ? $_POST[ 'Price' ] 	 : $Unit[ 'Price' ];
    $Unit[ 'Bank' ] 	= isset( $_POST[ 'Bank' ] ) 	 ? $_POST[ 'Bank' ] 	 : $Unit[ 'Bank' ];
    $Unit[ 'Template' ] 	= isset( $_POST[ 'Template' ] ) 	 ? $_POST[ 'Template' ] 	 : $Unit[ 'Template' ];
    $Unit[ 'TFMSource' ] 	= isset( $_POST[ 'TFMSource' ] ) 	 ? $_POST[ 'TFMSource' ] 	 : $Unit[ 'TFMSource' ];

    if( in_array( $_POST[ 'ID' ], array( null, 0, '', ' ' ) ) ){

        $result = \singleton\database::getInstance( )->query(
            null,
            "	DECLARE @MAXID INT;
        				SET @MAXID = CASE WHEN ( SELECT Max( ID ) FROM Unit ) IS NULL THEN 0 ELSE ( SELECT Max( ID ) FROM Unit ) END ;
        				INSERT INTO Unit(
    						  ID,
        					fDesc,
        					Remarks,
        					Building_ID,
        					City_ID,
        					Loc,
        					Cat,
        					Type,
        					Location_Category,
        					Manuf,
        					Install,
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
        					TFMSource
        				)
        				VALUES( @MAXID + 1 , ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,? );
        				SELECT @MAXID + 1;",
            array(
                $Unit[ 'fDesc' ],
                $Unit[ 'Remarks' ],
                $Unit[ 'Building_ID' ],
                $Unit[ 'City_ID' ],
                $Unit[ 'Loc' ],
                $Unit[ 'Cat' ],
                $Unit[ 'Type' ],
                $Unit[ 'Location_Category' ],
                $Unit[ 'Manuf' ],
                $Unit[ 'Install' ],
                $Unit[ 'InstallBy' ],
                $Unit[ 'Since' ],
                $Unit[ 'Last' ],
                $Unit[ 'Price' ],
                $Unit[ 'Owner' ],
                $Unit[ 'Bank' ],
                $Unit[ 'Serial' ],
                $Unit[ 'Template' ],
                $Unit[ 'Status' ],
                $Unit[ 'TFMID' ],
                $Unit[ 'TFMSource' ]

            )
        );

        sqlsrv_next_result( $result );
        $finalResult = sqlsrv_fetch_array( $result );

        $Unit[ 'ID' ] = $finalResult[ 0 ];

        header( 'Location: unit.php?ID=' . $Unit[ 'ID' ] );
        exit;
    }
    else{


        \singleton\database::getInstance( )->query(
            null,
            "	UPDATE 	Unit
	        			SET 	
	        					Unit.fDesc  = ?,
        					Unit.Remarks = ?,
        					Unit.Building_ID = ?,
        					Unit.City_ID = ?,
        					Unit.Loc = ?,
        					Unit.Cat = ?,
        					Unit.Type = ?,
        					Unit.Location_Category = ?,
        					Unit.Manuf = ?,
        					Unit.Install = ?,
        					Unit.InstallBy = ?,
        					Unit.Since = ?,
        					Unit.Last = ?,
        					Unit.Price = ?,
        					Unit.Owner = ?,
        					Unit.fGroup = ?,
        					Unit.Serial = ?,
        					Unit.Template = ?,
        					Unit.Status = ?,
        					Unit.TFMID = ?,
        					Unit.TFMSource  = ?

	        			WHERE 	Unit.ID = ?;",
            array(
                $Unit[ 'fDesc' ],
                $Unit[ 'Remarks' ],
                $Unit[ 'Building_ID' ],
                $Unit[ 'City_ID' ],
                $Unit[ 'Loc' ],
                $Unit[ 'Cat' ],
                $Unit[ 'Type' ],
                $Unit[ 'Location_Category' ],
                $Unit[ 'Manuf' ],
                $Unit[ 'Install' ],
                $Unit[ 'InstallBy' ],
                $Unit[ 'Since' ],
                $Unit[ 'Last' ],
                $Unit[ 'Price' ],
                $Unit[ 'Owner' ],
                $Unit[ 'Bank' ],
                $Unit[ 'Serial' ],
                $Unit[ 'Template' ],
                $Unit[ 'Status' ],
                $Unit[ 'TFMID' ],
                $Unit[ 'TFMSource' ],
                $Unit[ 'ID' ]
            )
        );
        header( 'Location: unit.php?ID=' . $Unit[ 'ID' ] );
    }
}

$locations = \singleton\database::getInstance( )->query(

    null,
    " SELECT  Loc,Tag FROM    Loc",[]
);
$locationArr = array();
$finalLoc= [];
if( $locations ) {
    while ($locationArr = sqlsrv_fetch_array($locations, SQLSRV_FETCH_ASSOC)) {


        $finalLoc[] = ['ID'=>$locationArr['Loc'],'Tag'=>$locationArr['Tag']];
    }
}


$owners = \singleton\database::getInstance( )->query(

    null,
    " SELECT  Owner.ID        AS ID,
                                    Rol.Name        AS Name,
                                    Rol.Address     AS Street,
                                    Rol.City        AS City,
                                    Rol.State       AS State,
                                    Rol.Contact       AS Contact,
                                    Rol.Zip         AS Zip,
                                    Owner.Status    AS Status,
                                    Rol.Website     AS Website
                            FROM    Owner
                            LEFT JOIN Rol ON Owner.Rol          = Rol.ID",[]
);
$ownerArr = array();
$finalOwner= [];
if( $owners ) {
    while ($ownerArr = sqlsrv_fetch_array($owners, SQLSRV_FETCH_ASSOC)) {
        $finalOwner[] = ['ID'=>$ownerArr['ID'],'Name'=>$ownerArr['Name']];
    }
}




?><!DOCTYPE html>
<html lang="en">
<head>
  <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
     <?php  $_GET[ 'Bootstrap' ] = '5.1';?>
     <?php  $_GET[ 'Entity_CSS' ] = 1;?>
     <?php	require( bin_meta . 'index.php');?>
     <?php	require( bin_css  . 'index.php');?>
     <?php  require( bin_js   . 'index.php');?>
</head>
<body onload='finishLoadingPage();'>
    <div id="wrapper">
        <?php require( bin_php . 'element/navigation.php'); ?>
		<div id="page-wrapper" class='content'>
            <div class="card card-primary">
                <div class="card-heading">
                    <div class="row g-0 px-3 py-2">
                        <div class="col-6"><h5><i class="fa fa-link fa-fw fa-1x" aria-hidden="true"></i><a href="units.php?">Unit</a>: <span>New</span></h5></div>
                        <div class="col-4"></div>
                        <div class="col-2">
                            <div class='row g-0'>
                                <div class='col-4'><button class='form-control rounded' onClick="document.location.href='unit.php';">Create</button></div>
                                <div class='col-4'><button class='form-control rounded' onClick="document.location.href='unit.php?ID=<?php echo $Unit[ 'ID' ];?>';">Refresh</button></div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="card-body bg-dark text-white">
                    <div class="card-columns">

                            <div class='card card-primary'>
                                <form action='unit.php?ID=<?php echo $Unit[ 'ID' ];?>' method='POST'>
                                <div class='card-heading'>
                                    <div class='row g-0 px-3 py-2'>
                                        <div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Info( 1 );?><span>Infomation</span></h5>
                                        </div>
                                        <input type='hidden' name='ID' value='<?php echo $Unit[ 'ID' ];?>' />

                                    </div>
                                </div>

                                <div class='card-body bg-dark text-white'>
                                    <!--                    <input type='hidden' name='ID' value='--><?php //echo $Unit[ 'ID' ];?><!--' />-->




                                            <div class='row g-0'>
                                                <div class='col-4 my-auto'> Description <label>*</label>:</div>
                                                <div class='col-xs-8'>
                                                    <textarea class="form-control" name="fDesc" required><?php echo $Unit['Description'];  ?></textarea>
                                                </div>
                                            </div>

                                            <div class='row g-0'>
                                                <div class='col-4 my-auto'> City<label>*</label>:</div>
                                                <div class='col-xs-8'>
                                                    <select class="form-control" name="City_ID" required>
                                                        <option value="">Select</option>
                                                        <option <?php echo $Unit[ 'State' ] == 'AL' ? 'selected' : null;?> value='AL'>Alabama</option>
                                                        <option <?php echo $Unit[ 'State' ] == 'AK' ? 'selected' : null;?> value='AK'>Alaska</option>
                                                        <option <?php echo $Unit[ 'State' ] == 'AZ' ? 'selected' : null;?> value='AZ'>Arizona</option>
                                                        <option <?php echo $Unit[ 'State' ] == 'AR' ? 'selected' : null;?> value='AR'>Arkansas</option>
                                                        <option <?php echo $Unit[ 'State' ] == 'CA' ? 'selected' : null;?> value='CA'>California</option>
                                                        <option <?php echo $Unit[ 'State' ] == 'CO' ? 'selected' : null;?> value='CO'>Colorado</option>
                                                        <option <?php echo $Unit[ 'State' ] == 'CT' ? 'selected' : null;?> value='CT'>Connecticut</option>
                                                        <option <?php echo $Unit[ 'State' ] == 'DE' ? 'selected' : null;?> value='DE'>Delaware</option>
                                                        <option <?php echo $Unit[ 'State' ] == 'DC' ? 'selected' : null;?> value='DC'>District Of Columbia</option>
                                                        <option <?php echo $Unit[ 'State' ] == 'FL' ? 'selected' : null;?> value='FL'>Florida</option>
                                                        <option <?php echo $Unit[ 'State' ] == 'GA' ? 'selected' : null;?> value='GA'>Georgia</option>
                                                        <option <?php echo $Unit[ 'State' ] == 'HI' ? 'selected' : null;?> value='HI'>Hawaii</option>
                                                        <option <?php echo $Unit[ 'State' ] == 'ID' ? 'selected' : null;?> value='ID'>Idaho</option>
                                                        <option <?php echo $Unit[ 'State' ] == 'IL' ? 'selected' : null;?> value='IL'>Illinois</option>
                                                        <option <?php echo $Unit[ 'State' ] == 'IN' ? 'selected' : null;?> value='IN'>Indiana</option>
                                                        <option <?php echo $Unit[ 'State' ] == 'IA' ? 'selected' : null;?> value='IA'>Iowa</option>
                                                        <option <?php echo $Unit[ 'State' ] == 'KS' ? 'selected' : null;?> value='KS'>Kansas</option>
                                                        <option <?php echo $Unit[ 'State' ] == 'KY' ? 'selected' : null;?> value='KY'>Kentucky</option>
                                                        <option <?php echo $Unit[ 'State' ] == 'LA' ? 'selected' : null;?> value='LA'>Louisiana</option>
                                                        <option <?php echo $Unit[ 'State' ] == 'ME' ? 'selected' : null;?> value='ME'>Maine</option>
                                                        <option <?php echo $Unit[ 'State' ] == 'MD' ? 'selected' : null;?> value='MD'>Maryland</option>
                                                        <option <?php echo $Unit[ 'State' ] == 'MA' ? 'selected' : null;?> value='MA'>Massachusetts</option>
                                                        <option <?php echo $Unit[ 'State' ] == 'MI' ? 'selected' : null;?> value='MI'>Michigan</option>
                                                        <option <?php echo $Unit[ 'State' ] == 'MN' ? 'selected' : null;?> value='MN'>Minnesota</option>
                                                        <option <?php echo $Unit[ 'State' ] == 'MS' ? 'selected' : null;?> value='MS'>Mississippi</option>
                                                        <option <?php echo $Unit[ 'State' ] == 'MO' ? 'selected' : null;?> value='MO'>Missouri</option>
                                                        <option <?php echo $Unit[ 'State' ] == 'MT' ? 'selected' : null;?> value='MT'>Montana</option>
                                                        <option <?php echo $Unit[ 'State' ] == 'NE' ? 'selected' : null;?> value='NE'>Nebraska</option>
                                                        <option <?php echo $Unit[ 'State' ] == 'NV' ? 'selected' : null;?> value='NV'>Nevada</option>
                                                        <option <?php echo $Unit[ 'State' ] == 'NH' ? 'selected' : null;?> value='NH'>New Hampshire</option>
                                                        <option <?php echo $Unit[ 'State' ] == 'NJ' ? 'selected' : null;?> value='NJ'>New Jersey</option>
                                                        <option <?php echo $Unit[ 'State' ] == 'NM' ? 'selected' : null;?> value='NM'>New Mexico</option>
                                                        <option <?php echo $Unit[ 'State' ] == 'NY' ? 'selected' : null;?> value='NY'>New York</option>
                                                        <option <?php echo $Unit[ 'State' ] == 'NC' ? 'selected' : null;?> value='NC'>North Carolina</option>
                                                        <option <?php echo $Unit[ 'State' ] == 'ND' ? 'selected' : null;?> value='ND'>North Dakota</option>
                                                        <option <?php echo $Unit[ 'State' ] == 'OH' ? 'selected' : null;?> value='OH'>Ohio</option>
                                                        <option <?php echo $Unit[ 'State' ] == 'OK' ? 'selected' : null;?> value='OK'>Oklahoma</option>
                                                        <option <?php echo $Unit[ 'State' ] == 'OR' ? 'selected' : null;?> value='OR'>Oregon</option>
                                                        <option <?php echo $Unit[ 'State' ] == 'PA' ? 'selected' : null;?> value='PA'>Pennsylvania</option>
                                                        <option <?php echo $Unit[ 'State' ] == 'RI' ? 'selected' : null;?> value='RI'>Rhode Island</option>
                                                        <option <?php echo $Unit[ 'State' ] == 'SC' ? 'selected' : null;?> value='SC'>South Carolina</option>
                                                        <option <?php echo $Unit[ 'State' ] == 'SD' ? 'selected' : null;?> value='SD'>South Dakota</option>
                                                        <option <?php echo $Unit[ 'State' ] == 'TN' ? 'selected' : null;?> value='TN'>Tennessee</option>
                                                        <option <?php echo $Unit[ 'State' ] == 'TX' ? 'selected' : null;?> value='TX'>Texas</option>
                                                        <option <?php echo $Unit[ 'State' ] == 'UT' ? 'selected' : null;?> value='UT'>Utah</option>
                                                        <option <?php echo $Unit[ 'State' ] == 'VT' ? 'selected' : null;?> value='VT'>Vermont</option>
                                                        <option <?php echo $Unit[ 'State' ] == 'VA' ? 'selected' : null;?> value='VA'>Virginia</option>
                                                        <option <?php echo $Unit[ 'State' ] == 'WA' ? 'selected' : null;?> value='WA'>Washington</option>
                                                        <option <?php echo $Unit[ 'State' ] == 'WV' ? 'selected' : null;?> value='WV'>West Virginia</option>
                                                        <option <?php echo $Unit[ 'State' ] == 'WI' ? 'selected' : null;?> value='WI'>Wisconsin</option>
                                                        <option <?php echo $Unit[ 'State' ] == 'WY' ? 'selected' : null;?> value='WY'>Wyoming</option>

                                                    </select>
                                                </div>
                                            </div>
                                            <div class='row g-0'>
                                                <div class='col-4 my-auto'> Type<label>*</label>:</div>
                                                <div class='col-xs-8'>
                                                    <select class="form-control" name="Type" required>
                                                        <option value="">Select</option>
                                                        <option value='General' <?php echo $Unit[ 'Type' ] == 'General' ? 'selected' : null;?>>General</option>
                                                        <option value='Bank' <?php echo $Unit[ 'Type' ] == 'Bank' ? 'selected' : null;?>>Bank</option>
                                                        <option value='Churches' <?php echo $Unit[ 'Type' ] == 'Churches' ? 'selected' : null;?>>Churches</option>
                                                        <option value='Commercial' <?php echo $Unit[ 'Type' ] == 'Commercial' ? 'selected' : null;?>>Commercial</option>
                                                        <option value='Hospitals' <?php echo $Unit[ 'Type' ] == 'Hospitals' ? 'selected' : null;?>>General</option>
                                                        <option value='Property Manage' <?php echo $Unit[ 'Type' ] == 'Property Manage' ? 'selected' : null;?>>Property Manage</option>
                                                        <option value='Restaraunts' <?php echo $Unit[ 'Type' ] == 'General' ? 'selected' : null;?>>Restaraunts</option>
                                                        <option value='Schools' <?php echo $Unit[ 'Type' ] == 'Schools' ? 'selected' : null;?>>Schools</option>

                                                    </select>
                                                </div>
                                            </div>
                                            <div class='row g-0'>
                                                <div class='col-4 my-auto'> Manufacturer<label>*</label>:</div>
                                                <div class='col-xs-8'>
                                                    <input type="text" class="form-control" name="Manuf" value="<?php echo $Unit['Manufacturer'];  ?>" required>

                                                </div>
                                            </div>
                                            <div class='row g-0'>
                                                <div class='col-4 my-auto'> Installer<label>*</label>:</div>
                                                <div class='col-xs-8'>
                                                    <input type="text" class="form-control" name="InstallBy" value="<?php echo $Unit['Installer'];  ?>" required>

                                                </div>
                                            </div>
                                            <div class='row g-0'>
                                                <div class='col-4 my-auto'> Maintained<label>*</label>:</div>
                                                <div class='col-xs-8'>
                                                    <input type="text" class="form-control" name="Last" value="<?php echo $Unit['Maintained'];  ?>" required>

                                                </div>
                                            </div>
                                            <div class='row g-0'>
                                                <div class='col-4 my-auto'> Customer<label>*</label>:</div>
                                                <div class='col-xs-8'>
                                                    <select class="form-control" name="Owner" required><option value="">Select</option>
                                                        <?php  foreach($finalOwner as $finalOwners){ ?>
                                                            <option value="<?php echo $finalOwners['ID'];  ?>" <?php if($Unit['Customer'] == $finalOwners['ID']){ echo "selected"; }  ?>><?php echo $finalOwners['Name'];  ?></option>
                                                        <?php  }  ?>
                                                    </select>


                                                </div>
                                            </div>
                                            <div class='row g-0'>
                                                <div class='col-4 my-auto'> Serial<label>*</label>:</div>
                                                <div class='col-xs-8'>
                                                    <input type="text" class="form-control" name="Serial" value="<?php echo $Unit['Serial'];  ?>" required>

                                                </div>
                                            </div>
                                            <div class='row g-0'>
                                                <div class='col-4 my-auto'> Status<label>*</label>:</div>
                                                <div class='col-xs-8'>
                                                    <select class="form-control" name="Status" required><option value="">Select</option>

                                                        <option value='0' <?php echo $Unit[ 'Status' ] == 1 ? 'selected' : '';?>>Inactive</option>
                                                        <option value='1' <?php echo $Unit[ 'Status' ] == 0 ? 'selected' : '';?>>Active</option>
                                                    </select>

                                                </div>
                                            </div>
                                            <div class='row g-0'>
                                                <div class='col-4 my-auto'> Unit<label>*</label>:</div>
                                                <div class='col-xs-8'>

                                                    <select class="form-control" name="Building_ID" ><option value="">Select</option>
                                                        <option value="Elevator" <?php echo $Unit[ 'Units' ] == 'Elevator' ? 'selected' : null;?>>Elevator</option>
                                                    </select>

                                                </div>
                                            </div>
                                            <div class='row g-0'>
                                                <div class='col-4 my-auto'> TFMID:</div>
                                                <div class='col-xs-8'>
                                                    <input type="text" class="form-control" name="TFMID" value="<?php echo $Unit['TFMID'];  ?>">

                                                </div>
                                            </div>


                                            <div class='row g-0'>
                                                <div class='col-4 my-auto'> Note<label>*</label>:</div>
                                                <div class='col-xs-8'>
                                                    <textarea class="form-control" name="Remarks" required><?php echo $Unit['Note'];  ?></textarea>
                                                </div>
                                            </div>
                                            <div class='row g-0'>
                                                <div class='col-4 my-auto'> Location<label>*</label>:</div>
                                                <div class='col-xs-8'>
                                                    <select class="form-control" name="Loc" required >
                                                        <option value="">Select</option>
                                                        <?php  foreach($finalLoc as $locationList){ ?>
                                                            <option value="<?php echo $locationList['ID'];  ?>" <?php if($Unit['Location'] == $locationList['ID']){ echo "selected"; }  ?>><?php echo $locationList['Tag'];  ?></option>
                                                        <?php  }  ?>

                                                    </select>
                                                </div>
                                            </div>
                                            <div class='row g-0'>
                                                <div class='col-4 my-auto'> Unit Category<label>*</label>:</div>
                                                <div class='col-xs-8'>
                                                    <select class="form-control" name="Cat" required><option value="">Select</option>
                                                        <option value="Consultant" <?php echo $Unit[ 'Category' ] == 'Consultant' ? 'selected' : '';?>>Consultant</option>
                                                        <option value="Other" <?php echo $Unit[ 'Category' ] == 'Other' ? 'selected' : '';?>>Other</option>
                                                        <option value="Public" <?php echo $Unit[ 'Category' ] == 'Public' ? 'selected' : '';?>>Public</option>
                                                        <option value="N/A" <?php echo $Unit[ 'Category' ] == 'N/A' ? 'selected' : '';?>>N/A</option>
                                                        <option value="Service" <?php echo $Unit[ 'Category' ] == 'Service' ? 'selected' : '';?>>Service</option>
                                                        <option value="Private" <?php echo $Unit[ 'Category' ] == 'Private' ? 'selected' : '';?>>Private</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class='row g-0'>
                                                <div class='col-4 my-auto'> Building<label>*</label>:</div>
                                                <div class='col-xs-8'>
                                                    <select class="form-control" name="Location_Category" required><option value="">Select</option>
                                                        <option value="Government" <?php echo $Unit[ 'Building' ] == 'Government' ? 'selected' : '';?>>Government</option>
                                                        <option value="Hospital" <?php echo $Unit[ 'Building' ] == 'Hospital' ? 'selected' : '';?>>Hospital</option>
                                                        <option value="School" <?php echo $Unit[ 'Building' ] == 'School' ? 'selected' : '';?>>School</option>
                                                        <option value="Commercial" <?php echo $Unit[ 'Building' ] == 'Commercial' ? 'selected' : '';?>>Commercial</option>
                                                        <option value="Residence" <?php echo $Unit[ 'Building' ] == 'Residence' ? 'selected' : '';?>>Residence</option>
                                                        <option value="Funeral Homes" <?php echo $Unit[ 'Building' ] == 'Funeral Homes' ? 'selected' : '';?>>Funeral Homes</option>
                                                        <option value="Utility-Powerplants" <?php echo $Unit[ 'Building' ] == 'Utility-Powerplants' ? 'selected' : '';?>>Utility-Powerplants</option>
                                                        <option value="Other" <?php echo $Unit[ 'Building' ] == 'Other' ? 'selected' : '';?>>Other</option>
                                                        <option value="Catering Hall" <?php echo $Unit[ 'Building' ] == 'Catering Hall' ? 'selected' : '';?>>Catering Hall</option>
                                                        <option value="Apartment / Residence" <?php echo $Unit[ 'Building' ] == 'Apartment / Residence' ? 'selected' : '';?>>Apartment / Residence</option>
                                                        <option value="Office / Commercial" <?php echo $Unit[ 'Building' ] == 'Office / Commercial' ? 'selected' : '';?>>Office / Commercial</option>
                                                        <option value="Warehouse" <?php echo $Unit[ 'Building' ] == 'Warehouse' ? 'selected' : '';?>>Warehouse</option>
                                                        <option value="Store / Retail" <?php echo $Unit[ 'Building' ] == 'Store / Retail' ? 'selected' : '';?>>Store / Retail</option>
                                                        <option value="Bank" <?php echo $Unit[ 'Building' ] == 'Bank' ? 'selected' : '';?>>Bank</option>
                                                        <option value="Parking Structure" <?php echo $Unit[ 'Building' ] == 'Parking Structure' ? 'selected' : '';?>>Parking Structure
                                                        </option>
                                                        <option value="Club/Museum" <?php echo $Unit[ 'Building' ] == 'Club/Museum' ? 'selected' : '';?>>Club/Museum
                                                        </option>
                                                        <option value="Hospital" <?php echo $Unit[ 'Building' ] == 'Hospital' ? 'selected' : '';?>>Hospital
                                                        </option>
                                                        <option value="Nursing Home" <?php echo $Unit[ 'Building' ] == 'Nursing Home' ? 'selected' : '';?>>Nursing Home
                                                        </option>
                                                        <option value="Airport" <?php echo $Unit[ 'Building' ] == 'Airport' ? 'selected' : '';?>>Airport
                                                        </option>
                                                        <option value="Church" <?php echo $Unit[ 'Building' ] == 'Church' ? 'selected' : '';?>>Church
                                                        </option>
                                                        <option value="Hotel" <?php echo $Unit[ 'Building' ] == 'Hotel' ? 'selected' : '';?>>Hotel
                                                        </option>
                                                        <option value="Post Office" <?php echo $Unit[ 'Building' ] == 'Post Office' ? 'selected' : '';?>>Post Office
                                                        </option>
                                                        <option value="Mission" <?php echo $Unit[ 'Building' ] == 'Mission' ? 'selected' : '';?>>Mission
                                                        </option>


                                                    </select>
                                                </div>
                                            </div>
                                            <div class='row g-0'>
                                                <div class='col-4 my-auto'> Installation<label>*</label>:</div>
                                                <div class='col-xs-8'>
                                                    <input type="text" class="form-control" name="Install" value="<?php echo $Unit['Installation'];  ?>" required >

                                                </div>
                                            </div>
                                            <div class='row g-0'>
                                                <div class='col-4 my-auto'> Created<label>*</label>:</div>
                                                <div class='col-xs-8'>
                                                    <input type="text" class="form-control" name="Since" value="<?php echo $Unit['Created'];  ?>" required>

                                                </div>
                                            </div>
                                            <div class='row g-0'>
                                                <div class='col-4 my-auto'> Price<label>*</label>:</div>
                                                <div class='col-xs-8'>
                                                    <input type="text" class="form-control" name="Price"  value="<?php echo $Unit['Price'];  ?>" required>

                                                </div>
                                            </div>
                                            <div class='row g-0'>
                                                <div class='col-4 my-auto'> Bank<label>*</label>:</div>
                                                <div class='col-xs-8'>
                                                    <input type="text" class="form-control" name="Bank" value="<?php echo $Unit['Bank'];  ?>" required>

                                                </div>
                                            </div>
                                            <div class='row g-0'>
                                                <div class='col-4 my-auto'> Template:</div>
                                                <div class='col-xs-8'>
                                                    <select class="form-control" name="Template"><option value="">Select</option>
                                                    </select>

                                                </div>
                                            </div>
                                            <div class='row g-0'>
                                                <div class='col-4 my-auto'> TFMSource:</div>
                                                <div class='col-xs-8'>
                                                    <input type="text" class="form-control" name="TFMSource" value="<?php echo $Unit['TFMSource'];  ?>">

                                                </div>
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
</body>
</html>
<?php
	}
} else {?><html><head><script>document.location.href="../login.php?Forward=unit<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
