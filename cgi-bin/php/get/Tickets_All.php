<?php 
session_start();
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
	 if( isset($My_Privileges['Ticket'],$My_Privileges['User']) 
        && (
				$My_Privileges['Ticket']['User_Privilege'] >= 4
			||	$My_Privileges['Ticket']['Group_Privilege'] >= 4
			||	$My_Privileges['Ticket']['Other_Privilege'] >= 4)){
            $Privileged = True;}
    if(!isset($Connection['ID']) || !$Privileged){print json_encode(array('data'=>array()));}
	else {
		$serverName = "172.16.12.45"; //serverName\instanceName
		$connectionInfo = array( 
			"Database"=>"nei", 
			"UID"=>"sa", 
			"PWD"=>"SQLABC!23456",
			'ReturnDatesAsStrings'=>true
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
		$aColumns = array( 'ID','fDesc','CDate','DDate','EDate','TimeSite','TimeComp','Who','fBy','Level','Job','fWork');
		/* Indexed column (used for fast and accurate table cardinality) */
		$sIndexColumn = "ID";

		/* DB table to use */
		$sTable = array("TicketO", "TicketD", "TicketDArchive");


		/* 
		 * Paging
		 */
		$sLimit = "";
		$_GET['iDisplayStart'] = isset($_GET['start']) ? $_GET['start'] : 0;
		$_GET['iDisplayLength'] = isset($_GET['length']) ? $_GET['length'] : '-1';

		$Start = $_GET['iDisplayStart'];
		$Length = $_GET['iDisplayLength'];

		$End = $Length == '-1' ? 9999999999 : intval($Start) + intval($Length);

		/*
		 * Ordering
		 */

		$sOrder = ""; 
		if ( isset( $_GET['order'][0]['column'] ) )
		{
			$sOrder = "ORDER BY  CAST(";
			$sOrder .= $aColumns[$_GET['order'][0]['column']] . " AS NVARCHAR(100))" . $_GET['order'][0]['dir'];
			/*for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
			{
				if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
				{
					$sOrder .= $aColumns[ intval( $_GET['iSortCol_'.$i] ) ]."
						".addslashes( $_GET['sSortDir_'.$i] ) .", ";
				}
			}

			$sOrder = substr_replace( $sOrder, "", -2 );
			if ( $sOrder == "ORDER BY" )
			{
				$sOrder = "";
			}*/
		}


		/* 
		 * Filtering
		 * NOTE this does not match the built-in DataTables filtering which does it
		 * word by word on any field. It's possible to do here, but concerned about efficiency
		 * on very large tables, and MySQL's regex functionality is very limited
		 */
		$sWhere = "";
		$_GET['sSearch'] = isset($_GET['search']['value']) ? $_GET['search']['value'] : "";
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
		$sQuery = "
		SELECT *
		FROM
		 (
			SELECT ROW_NUMBER() OVER ($sOrder) AS ROW_COUNT, Tickets.*
			FROM (
				(SELECT TicketO." . str_replace(" ,", " ", implode(", TicketO.",$aColumns)) . "
				FROM TicketO)
				UNION ALL
				(SELECT TicketD." . str_replace(" ,", " ", implode(", TicketD.",$aColumns)) . "
				FROM TicketD)
				UNION ALL
				(SELECT TicketDArchive." . str_replace(" ,", " ", implode(", TicketDArchive.",$aColumns)) . "
				FROM TicketDArchive)
			) AS Tickets
			$sWhere
		 ) A
		WHERE A.ROW_COUNT BETWEEN $Start AND $End
		";
		//echo $sQuery;

		$rResult = sqlsrv_query($conn,  $sQuery ) or die(print_r(sqlsrv_errors()));

		$sWhere =$pWhere;
		/* Data set length after filtering */
		$sQueryRow = "
			SELECT ".str_replace(" , ", " ", implode(", ", $aColumns))."
			FROM   (
				(SELECT TicketO." . str_replace(", ", " ", implode(", TicketO.",$aColumns)) . "
				FROM TicketO)
				UNION ALL
				(SELECT TicketD." . str_replace(", ", " ", implode(", TicketD.",$aColumns)) . "
				FROM TicketD)
				UNION ALL
				(SELECT TicketDArchive." . str_replace(", ", " ", implode(", TicketDArchive.",$aColumns)) . "
				FROM TicketDArchive)
			) AS Tickets
			$sWhere
		";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
		$stmt = sqlsrv_query( $conn, $sQueryRow , $params, $options );

		$iFilteredTotal = sqlsrv_num_rows( $stmt );


		//echo "TOTAL " . $iFilteredTotal;
		/* Total data set length */
		$sQuery = "
			SELECT COUNT(".$sIndexColumn.")
			FROM   (
				(SELECT TicketO.ID
				FROM TicketO)
				UNION ALL
				(SELECT TicketD.ID
				FROM TicketD)
				UNION ALL
				(SELECT TicketDArchive.ID
				FROM TicketDArchive)
			) AS Tickets
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
			$row = array();
			for ( $i=0 ; $i<count($aColumns) ; $i++ )
			{
				if ( $aColumns[$i] == "version" )
				{
					/* Special output formatting for 'version' column */
					$row[] = ($aRow[ $aColumns[$i] ]=="0") ? '-' : $aRow[ $aColumns[$i] ];
				}
				else if ( $aColumns[$i] != ' ' )
				{
					/* General output */
					$row[] = $aRow[ $aColumns[$i] ];
				}
			}
			$output['aaData'][] = $row;
		}

		echo json_encode( $output );
	}
}?>
<?php /*
session_start();
require('index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $User = sqlsrv_query($NEI,"SELECT * FROM Emp WHERE ID = ?",array($_GET['User']));
    $User = sqlsrv_fetch_array($User);
    $Field = ($User['Field'] == 1 && "OFFICE" != $User['Title']) ? True : False;
    $r = sqlsrv_query($Portal,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
    $My_Privileges = array();
    while($array2 = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$My_Privileges[$array2['Access_Table']] = $array2;}
    $Privileged = false;
    if(isset($My_Privileges['Invoice']) && $My_Privileges['Invoice']['User_Privilege'] >= 4){$Privileged = true;}
    if(!isset($array['ID'])  || !$Privileged){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
        $r = sqlsrv_query($NEI,"
            SELECT Invoice.Ref       AS  ID,
                   Invoice.fDesc     AS  Description,
                   Invoice.Total     AS  Total,
                   Job.fDesc         AS  Job,
                   Loc.Tag           AS  Location,
                   Invoice.fDate     AS  fDate,
                   OwnerWithRol.Name AS Customer
            FROM   nei.dbo.Invoice
                   LEFT JOIN nei.dbo.Loc   ON  Invoice.Loc           = Loc.Loc
                   LEFT JOIN nei.dbo.Job   ON  Invoice.Job           = Job.ID
                   LEFT JOIN nei.dbo.OwnerWithRol ON OwnerWithRol.ID = Loc.Owner
        ;");
        $data = array();
        if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
        print json_encode(array('data'=>$data));   }
}*/?>
<?php/* 
session_start();
require('index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    if(!isset($array['ID'],$_GET['ID']) 
	   || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
        $r = sqlsrv_query($NEI,"
			SELECT Tickets.*,
				   Tickets.ID                  AS Ticket_ID,
				   Loc.ID                      AS Location_Identifier,
				   Loc.Loc                     AS Location_ID,
				   Loc.Tag                     AS Location,
				   Loc.Address                 AS Address,
				   Loc.Address                 AS Street,
				   Loc.City                    AS City,
				   Loc.State                   AS State,
				   Loc.Zip                     AS Zip,
				   Route.Name 		           AS Route,
				   Zone.Name 		           AS Division,
				   Loc.Maint 		           AS Maintenance,
				   Job.ID                      AS Job_ID,
				   Job.fDesc                   AS Job_Description,
				   OwnerWithRol.ID             AS Owner_ID,
				   OwnerWithRol.ID             AS Customer_ID,
				   OwnerWithRol.Name           AS Customer,
				   Elev.ID                     AS Unit_ID,
				   Elev.Unit                   AS Unit_Label,
				   Elev.State                  AS Unit_State,
				   Elev.fDesc				   AS Unit_Description,
				   Elev.Type 				   AS Unit_Type,
				   Emp.ID                      AS User_ID,
				   Emp.fFirst                  AS First_Name,
				   Emp.Last                    AS Last_Name,
				   Emp.fFirst + ' ' + Emp.Last AS Mechanic,
				   'Unknown'                   AS ClearPR,
				   JobType.Type                AS Job_Type
			FROM (
					(SELECT TicketO.ID       AS ID,
							TicketO.fDesc    AS Description,
							''               AS Resolution,
							TicketO.CDate    AS Created,
							TicketO.DDate    AS Dispatched,
							TicketO.EDate    AS Worked,
							TicketO.TimeSite AS On_Site,
							TicketO.TimeComp AS Completed,
							TicketO.Who 	 AS Caller,
							TicketO.fBy      AS Reciever,
							TicketO.Level    AS Level,
							TicketO.Cat      AS Category,
							TicketO.LID      AS Location,
							TicketO.Job      AS Job,
							TicketO.LElev    AS Unit,
							TicketO.Owner    AS Owner,
							TicketO.fWork    AS Mechanic,
							TickOStatus.Type AS Status,
							0                AS Total,
							0                AS Regular,
							0                AS Overtime,
							0                AS Doubletime,
							TicketO.fBy      AS Taken_By
					 FROM   nei.dbo.TicketO
							LEFT JOIN TickOStatus ON TicketO.Assigned = TickOStatus.Ref
					 WHERE  TicketO.LID = ?
					)
					UNION ALL
					(SELECT TicketD.ID       AS ID,
							TicketD.fDesc    AS Description,
							TicketD.DescRes  AS Resolution,
							TicketD.CDate    AS Created,
							TicketD.DDate    AS Dispatched,
							TicketD.EDate    AS Worked,
							TicketD.TimeSite AS On_Site,
							TicketD.TimeComp AS Completed,
							TicketD.Who 	 AS Caller,
							TicketD.fBy      AS Reciever,
							TicketD.Level    AS Level,
							TicketD.Cat      AS Category,
							TicketD.Loc      AS Location,
							TicketD.Job      AS Job,
							TicketD.Elev     AS Unit,
							Loc.Owner        AS Owner,
							TicketD.fWork    AS Mechanic,
							'Completed'      AS Status,
							TicketD.Total    AS Total,
							TicketD.Reg      AS Regular,
							TicketD.OT       AS Overtime,
							TicketD.DT       AS Doubletime,
							TicketD.fBy      AS Taken_By
					 FROM   nei.dbo.TicketD
							LEFT JOIN nei.dbo.Loc ON TicketD.Loc = Loc.Loc
					)
					UNION ALL
					(SELECT TicketDArchive.ID       AS ID,
							TicketDArchive.fDesc    AS Description,
							TicketDArchive.DescRes  AS Resolution,
							TicketDArchive.CDate    AS Created,
							TicketDArchive.DDate    AS Dispatched,
							TicketDArchive.EDate    AS Worked,
							TicketDArchive.TimeSite AS On_Site,
							TicketDArchive.TimeComp AS Completed,
							TicketDArchive.Who 	    AS Caller,
							TicketDArchive.fBy      AS Reciever,
							TicketDArchive.Level    AS Level,
							TicketDArchive.Cat      AS Category,
							TicketDArchive.Loc      AS Location,
							TicketDArchive.Job      AS Job,
							TicketDArchive.Elev     AS Unit,
							Loc.Owner               AS Owner,
							TicketDArchive.fWork    AS Mechanic,
							'Completed'             AS Status,
							TicketDArchive.Total    AS Total,
							TicketDArchive.Reg      AS Regular,
							TicketDArchive.OT       AS Overtime,
							TicketDArchive.DT       AS Doubletime,
							TicketDArchive.fBy      AS Taken_By
					 FROM   nei.dbo.TicketDArchive
							LEFT JOIN nei.dbo.Loc ON TicketDArchive.Loc = Loc.Loc
					)
				) AS Tickets
				LEFT JOIN nei.dbo.Loc          ON Tickets.Location = Loc.Loc
				LEFT JOIN nei.dbo.Job          ON Tickets.Job      = Job.ID
				LEFT JOIN nei.dbo.Elev         ON Tickets.Unit     = Elev.ID
				LEFT JOIN nei.dbo.OwnerWithRol ON Tickets.Owner    = OwnerWithRol.ID
				LEFT JOIN Emp          ON Tickets.Mechanic = Emp.fWork
				LEFT JOIN nei.dbo.JobType      ON Job.Type         = JobType.ID
				LEFT JOIN nei.dbo.Zone 		   ON Zone.ID          = Loc.Zone
				LEFT JOIN nei.dbo.Route		   ON Route.ID		   = Loc.Route
			ORDER BY Tickets.ID DESC
		",array($_GET['ID'],$_GET['ID'],$_GET['ID'],$_GET['ID']),array("Scrollable"=>SQLSRV_CURSOR_KEYSET));
		$data = array();
		$row_count = sqlsrv_num_rows( $r );
		if($r){
			while($i < $row_count){
				$Ticket = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
				if(is_array($Ticket) && $Ticket != array()){
					//Tags
					$Tags = array();
					if(strpos($Ticket['Description'],"s/d") || strpos($Ticket['Description'],"S/D") || strpos($Ticket['Description'],"shutdown")){
						$Tags[] = "Shutdown";
					}
					if($Ticket['Level'] == 10){
						$Tags[] = "Maintenance";
					}
					if($Ticket['Level'] == 1){
						$Tags[] = "Service Call";
					}
					$Ticket['Tags'] = implode(", ",$Tags);
					
					//On Site / Completed Time
					if($Ticket['On_Site'] == NULL || $Ticket['On_Site'] == ''){
						$Ticket['On_Site'] = 'None';
					} else {
						$Ticket['On_Site'] = date("H:i:s",strtotime($Ticket['On_Site']));
					}
					if($Ticket['Completed'] == NULL || $Ticket['Completed'] == ''){
						$Ticket['Completed'] = 'None';
					} else {
						$Ticket['Completed'] = date("H:i:s",strtotime($Ticket['Completed']));
					}
					if($Ticket['Created'] == NULL || $Ticket['Created'] == ''){
						$Ticket['Created'] = 'None';
					} else {
						$Ticket['Created'] = date("m/d/Y H:i:s",strtotime($Ticket['Created']));
					}
					if($Ticket['Dispatched'] == NULL || $Ticket['Dispatched'] == ''){
						$Ticket['Dispatched'] = 'Unknown';
					} else {
						$Ticket['Dispatched'] = date("m/d/Y H:i:s",strtotime($Ticket['Dispatched']));
					}
					$data[] = $Ticket;
				}
				$i++;
			}
		}
		print json_encode(array('data'=>$data));  
    }
}
sqlsrv_close($NEI);*/
?>