<?php
session_start( [ 'read_and_close' => true ] );
require('index.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"
        SELECT *
        FROM   Connection
        WHERE  Connection.Connector = ?
               AND Connection.Hash = ?
    ;", array($_SESSION['User'],$_SESSION['Hash']));
    $Connection = sqlsrv_fetch_array($r);
    $My_User    = sqlsrv_query($NEI,"
        SELECT Emp.*,
               Emp.fFirst AS First_Name,
               Emp.Last   AS Last_Name
        FROM   Emp
        WHERE  Emp.ID = ?
    ;", array($_SESSION['User']));
    $My_User = sqlsrv_fetch_array($My_User);
    $My_Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
    $r = sqlsrv_query($Portal,"
        SELECT Privilege.Access_Table,
               Privilege.User_Privilege,
               Privilege.Group_Privilege,
               Privilege.Other_Privilege
        FROM   Privilege
        WHERE  Privilege.User_ID = ?
    ;",array($_SESSION['User']));
    $My_Privileges = array();
    while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
    $Privileged = False;
    if( isset($My_Privileges['Unit'])
        && (
				$My_Privileges['Unit']['Other_Privilege'] >= 4
			||	$My_Privileges['Unit']['Group_Privilege'] >= 4
			||  $My_Privileges['Unit']['User_Privilege'] >= 4
		)
	 ){
            $Privileged = True;}
    if(!isset($Connection['ID']) || !$Privileged){print json_encode(array('data'=>array()));}
    else {
$serverName = "172.16.12.45"; //serverName\instanceName
$connectionInfo = array(
	"Database"=>"N-CT",
	"UID"=>"sa",
	"PWD"=>"SQLABC!23456"
);
$conn = sqlsrv_connect( $serverName, $connectionInfo);

    /*
     * Script:    DataTables server-side script for PHP and MySQL
     * Copyright: 2010 - Allan Jardine
     * License:   GPL v2 or BSD (3-point)
     */

    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * Easy set variables
     */

    /* Array of database columns which should be read and sent back to DataTables. Use a space where
     * you want to insert a non-database field (for example a counter or static image)
     */
	$Tables = array(
		"Elev"=>array(
			'ID',
			'State',
			'Unit',
			'Type',
			'Status',
			'Loc'
		),
		"Loc"=>array(
			'Loc',
			'Tag'
		)
	);
	$sIndexColumn = 'Elev.Loc';
	$aColumns   = array();
	$aTables    = array();
	$leftTables = array();
	$i = 0;
	foreach($Tables as $Table=>$Columns){
		$aTables[] = "nei.dbo." . $Table;

		if($i == 0){$sTable = "nei.dbo." . $Table;}
		elseif($i != 0){$leftTables[] = "LEFT JOIN nei.dbo." . $Table . " ON Elev.Loc = Loc.Loc";}

		foreach($Columns as $index=>$Column){
			if(in_array("Elev." . $Column,$aColumns) || in_array("Loc." . $Column,$aColumns)){continue;}
			$aColumns[] = $Table . "." . $Column;
		}

		$i++;
	}
    /*
     * Paging
     */
    $sLimit = "";
	$_GET['iDisplayStart'] = isset($_GET['start']) ? $_GET['start'] : 0;
	$_GET['iDisplayLength'] = isset($_GET['length']) ? $_GET['length'] : '-1';

    $Start = $_GET['iDisplayStart'];
    $Length = $_GET['iDisplayLength'];

    $End = $Length == '-1' ? 9999999999 : intval($Start) + intval($Length);

    if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
    {
    $sLimit = "OFFSET  ".$_GET['iDisplayStart']." ROWS
                                FETCH NEXT ".$_GET['iDisplayLength']." ROWS ONLY ";
    }


    /*
     * Ordering
     */

    $sOrder = "";
    if ( isset( $_GET['order'][0]['column'] ) )
    {
        $sOrder = "ORDER BY  ";
        $sOrder .= $aColumns[$_GET['order'][0]['column']] . " " . $_GET['order'][0]['dir'];
    }


    /*
     * Filtering
     * NOTE this does not match the built-in DataTables filtering which does it
     * word by word on any field. It's possible to do here, but concerned about efficiency
     * on very large tables, and MySQL's regex functionality is very limited
     */
    $sWhere = "";
	$_GET['sSearch'] = isset($_GET['search']['value']) ? $_GET['search']['value'] : "";
	if(isset($_SESSION['Forward-Backward'],$_SESSION['Forward-Backward']['Locations'])) {
		$_SESSION['Forward-Backward']['Units'] = $_GET['search']['value'];
	} else {
		$_SESSION['Forward-Backward'] = array('Units'=>'');
	}
    if ( $_GET['sSearch'] != "" )
    {
        $sWhere = "WHERE (";
        for ( $i=0 ; $i<count($aColumns) ; $i++ )
        {
            $sWhere .= $aColumns[$i]." LIKE '%".addslashes( $_GET['sSearch'] )."%' OR ";
        }
        $sWhere = substr_replace( $sWhere, "", -3 );
        $sWhere .= ')';
    }

    /* Individual column filtering */
    for ( $i=0 ; $i<count($aColumns) ; $i++ )
    {
        if ( $_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '' )
        {
            if ( $sWhere == "" )
            {
                $sWhere = "WHERE ";
            }
            else
            {
                $sWhere .= " AND ";
            }
            $sWhere .= $aColumns[$i]." LIKE '%".addslashes($_GET['sSearch_'.$i])."%' ";
        }
    }


    /*
     * SQL queries
     * Get data to display
     */
    $pWhere = $sWhere;
    $sWhere = !isset($sWhere) || $sWhere == '' ? "WHERE '1'='1'" : $sWhere;
    $sql_leftTables = implode(" ",$leftTables);
    $params = array();
    if(isset($_GET['Loc'])){
      $sWhere .= " AND Elev.Loc = ?";
      array_push($params, $_GET['Loc']);
    }
	if($My_Privileges['Unit']['Other_Privilege'] >= 4){

		$sQuery = "
			SELECT *
			FROM
			 (
				SELECT ROW_NUMBER() OVER ($sOrder) AS ROW_COUNT," . str_replace(" , ", " ", implode(", ", $aColumns)) . "
				FROM   {$aTables[0]}
					   {$sql_leftTables}
				$sWhere
			) A
			WHERE A.ROW_COUNT BETWEEN $Start AND $End
		";
		//echo $sQuery;
	} else {
		$sQuery = "
			SELECT *
			FROM
			 (
				SELECT ROW_NUMBER() OVER ($sOrder) AS ROW_COUNT," . str_replace(" , ", " ", implode(", ", $aColumns)) . "
				FROM {$aTables[0]}
					 {$sql_leftTables}
				$sWhere
				AND Elev.Loc IN
					(
						SELECT Tickets.Location_ID
						FROM
						(
							(
								SELECT   TicketO.LID AS Location_ID
								FROM     nei.dbo.TicketO
										 LEFT JOIN Emp ON TicketO.fWork = Emp.fWork
								WHERE    Emp.ID = {$_SESSION['User']}
								GROUP BY TicketO.LID
							)
							UNION ALL
							(
								SELECT   TicketD.Loc AS Location_ID
								FROM     nei.dbo.TicketD
										 LEFT JOIN Emp ON TicketD.fWork = Emp.fWork
								WHERE    Emp.ID = {$_SESSION['User']}
								GROUP BY TicketD.Loc
							)
						) AS Tickets
						GROUP BY Tickets.Location_ID
					)
			 ) A
			WHERE A.ROW_COUNT BETWEEN $Start AND $End
		";
	}

    $rResult = sqlsrv_query($conn,  $sQuery, $params ) or die(print_r(sqlsrv_errors()));


    /* Data set length after filtering */
	if($My_Privileges['Unit']['Other_Privilege'] >= 4){
		$sQueryRow = "
			SELECT ".str_replace(" , ", " ", implode(", ", $aColumns))."
			FROM   {$aTables[0]}
				   {$sql_leftTables}
			$sWhere
		;";
		//echo $sQueryRow;
	} else {
		$sQueryRow = "
			SELECT ".str_replace(" , ", " ", implode(", ", $aColumns))."
			FROM    {$aTables[0]}
					{$sql_leftTables}
			$sWhere
			AND  Elev.Loc IN
					(
						SELECT Tickets.Location_ID
						FROM
						(
							(
								SELECT   TicketO.LID AS Location_ID
								FROM     nei.dbo.TicketO
										 LEFT JOIN Emp ON TicketO.fWork = Emp.fWork
								WHERE    Emp.ID = {$_SESSION['User']}
								GROUP BY TicketO.LID
							)
							UNION ALL
							(
								SELECT   TicketD.Loc AS Location_ID
								FROM     nei.dbo.TicketD
										 LEFT JOIN Emp ON TicketD.fWork = Emp.fWork
								WHERE    Emp.ID = {$_SESSION['User']}
								GROUP BY TicketD.Loc
							)
						) AS Tickets
						GROUP BY Tickets.Location_ID
					)
		";
	}

    $options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
    $stmt = sqlsrv_query( $conn, $sQueryRow , $params, $options );

    $iFilteredTotal = sqlsrv_num_rows( $stmt );


    //echo "TOTAL " . $iFilteredTotal;
    /* Total data set length */
    $sQuery = "
        SELECT COUNT(".$sIndexColumn.")
        FROM   $sTable
    ";
    $rResultTotal = sqlsrv_query($conn,  $sQuery ) or die(print_r(sqlsrv_errors()));
    $aResultTotal = sqlsrv_fetch_array($rResultTotal);
    $iTotal = $aResultTotal[0];




    /*
     * Output
     */
    $output = array(
        "sEcho" => intval($_GET['sEcho']),
        "iTotalRecords" => $iTotal,
        "iTotalDisplayRecords" => $iFilteredTotal,
        "aaData" => array()
    );

    while ( $aRow = sqlsrv_fetch_array( $rResult ) )
    {
    	$aRow[0] = $aRow[1];
    	unset($aRow['ROW_COUNT']);
		$output['aaData'][] = $aRow;
    }

    echo json_encode( $output );
?>
<?php
/*
session_start( [ 'read_and_close' => true ] );
require('index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $User = sqlsrv_query($NEI,"SELECT * FROM Emp WHERE ID = ?",array($_GET['User']));
    $User = sqlsrv_fetch_array($User);
    $Field = ($User['Field'] == 1 && $User['Title'] != "OFFICE") ? True : False;
    $r = sqlsrv_query($Portal,"
        SELECT User_Privilege, Group_Privilege, Other_Privilege
        FROM   Privilege
        WHERE  User_ID = ? AND Access_Table='Location'
    ;",array($_SESSION['User']));
    $My_Privileges = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    if(!isset($array['ID'])){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
        $data = array();
        if($My_Privileges['User_Privilege'] >= 4 && $My_Privileges['Group_Privilege'] >= 4 && $My_Privileges['Other_Privilege'] >= 4){
            $data = array();
			$r = sqlsrv_query($NEI,"
				SELECT Loc.Loc           AS ID,
					   Loc.ID            AS Name,
					   Loc.Tag           AS Tag,
					   Loc.Address       AS Street,
					   Loc.City          AS City,
					   Loc.State         AS State,
					   Loc.Zip           AS Zip,
					   Route.Name        AS Route,
					   Zone.Name         AS Division,
					   Loc.Maint         AS Maintenance,
					   Terr.Name         AS Territory,
					   Loc.sTax          AS Sales_Tax,
					   Rol.Contact       AS Contact_Name,
					   Rol.Phone         AS Contact_Phone,
					   Rol.Fax           AS Contact_Fax,
					   Rol.Cellular      AS Contact_Cellular,
					   Rol.Email         AS Contact_Email,
					   Rol.Website       AS Contact_Website,
					   Loc.fLong         AS Longitude,
					   Loc.Latt          AS Latitude,
					   Loc.Custom1  	 AS Collector,
					   OwnerWithRol.Name AS Customer
				FROM   nei.dbo.Loc
					   LEFT JOIN nei.dbo.Zone         ON Zone.ID         = Loc.Zone
					   LEFT JOIN nei.dbo.Route        ON Route.ID        = Loc.Route
					   LEFT JOIN nei.dbo.Terr         ON Terr.ID         = Loc.Terr
					   LEFT JOIN nei.dbo.Rol          ON Loc.Rol         = Rol.ID
					   LEFT JOIN nei.dbo.OwnerWithRol ON OwnerWithRol.ID = Loc.Owner
			;",array($_GET['ID']));
			if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
        } else {
            $SQL_Locations = array();
            if($My_Privileges['Group_Privilege'] >= 4){
                $r = sqlsrv_query($NEI,"
                    SELECT LID AS Location
                    FROM   nei.dbo.TicketO
                           LEFT JOIN Emp ON TicketO.fWork = Emp.fWork
                    WHERE  Emp.ID = ?
                ;",array($_SESSION['User']));

                while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$SQL_Locations[] = "Loc.Loc='{$array['Location']}'";}
                $r = sqlsrv_query($NEI,"
                    SELECT Loc AS Location
                    FROM   nei.dbo.TicketD
                           LEFT JOIN Emp ON TicketD.fWork = Emp.fWork
                    WHERE  Emp.ID = ?
                ;",array($_SESSION['User']));

                while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$SQL_Locations[] = "Loc.Loc='{$array['Location']}'";}

				$r = sqlsrv_query($NEI,"
                    SELECT Loc AS Location
                    FROM   nei.dbo.TicketDArchive
                           LEFT JOIN Emp ON TicketDArchive.fWork = Emp.fWork
                    WHERE  Emp.ID = ?
                ;",array($_SESSION['User']));

                while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$SQL_Locations[] = "Loc.Loc='{$array['Location']}'";}
            }
            if($My_Privileges['User_Privilege'] >= 4){
                $r = sqlsrv_query($NEI,"
                    SELECT Loc.Loc AS Location
                    FROM   nei.dbo.Loc
                           LEFT JOIN nei.dbo.Route ON Loc.Route = Route.ID
                           LEFT JOIN Emp   ON Route.Mech = Emp.fWork
                    WHERE  Emp.ID = ?
                ;",array($_SESSION['User']));
                while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$SQL_Locations[] = "Loc.Loc='{$array['Location']}'";}
            }
            $SQL_Locations = array_unique($SQL_Locations);
            if(count($SQL_Locations) > 0){
                $SQL_Locations = implode(' OR ',$SQL_Locations);
                $r = sqlsrv_query($NEI,"
                    SELECT Loc.Loc           AS ID,
					       Loc.ID            AS Name,
					       Loc.Tag           AS Tag,
					       Loc.Address       AS Street,
					       Loc.City          AS City,
					       Loc.State         AS State,
					       Loc.Zip           AS Zip,
					       Route.Name        AS Route,
					       Zone.Name         AS Division,
					       Loc.Maint         AS Maintenance,
					       Terr.Name         AS Territory,
					       Loc.sTax          AS Sales_Tax,
					       Rol.Contact       AS Contact_Name,
					       Rol.Phone         AS Contact_Phone,
					       Rol.Fax           AS Contact_Fax,
					       Rol.Cellular      AS Contact_Cellular,
					       Rol.Email         AS Contact_Email,
					       Rol.Website       AS Contact_Website,
					       Loc.fLong         AS Longitude,
					       Loc.Latt          AS Latitude,
					       Loc.Custom1  	 AS Collector,
					       OwnerWithRol.Name AS Customer
					FROM   nei.dbo.Loc
						   LEFT JOIN nei.dbo.Zone         ON Zone.ID         = Loc.Zone
						   LEFT JOIN nei.dbo.Route        ON Route.ID        = Loc.Route
						   LEFT JOIN nei.dbo.Terr         ON Terr.ID         = Loc.Terr
						   LEFT JOIN nei.dbo.Rol          ON Loc.Rol         = Rol.ID
						   LEFT JOIN nei.dbo.OwnerWithRol ON OwnerWithRol.ID = Loc.Owner
					WHERE  Loc.Maint = 1
						   AND ({$SQL_Locations})
                ;");
                while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}
            }
        }
        print json_encode(array('data'=>$data));  }
}
*/
}}
?>
