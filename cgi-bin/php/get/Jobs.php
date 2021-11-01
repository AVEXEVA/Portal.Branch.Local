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
    if( isset($My_Privileges['Job'])
        && (
				$My_Privileges['Job']['Other_Privilege'] >= 4
			||	$My_Privileges['Job']['Group_Privlege'] >= 4
			||  $My_Privileges['Job']['User_Privilege'] >= 4
		)
	 ){
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
    $aColumns = array( 'ID', 'fDesc', 'Type', 'fDate', 'Status','Loc');

    /* Indexed column (used for fast and accurate table cardinality) */
    $sIndexColumn = "ID";

    /* DB table to use */
    $sTable = "Job";


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
	if(isset($_SESSION['Forward-Backward'],$_SESSION['Forward-Backward']['Jobs'])) {
		$_SESSION['Forward-Backward']['Jobs'] = $_GET['search']['value'];
	} else {
		$_SESSION['Forward-Backward'] = array('Jobs'=>'');
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

    $params = array();
    if(isset($_GET['Loc'])){
      $sWhere .= " AND Job.Loc = ?";
      array_push($params, $_GET['Loc']);
    }
	if($My_Privileges['Job']['Other_Privilege'] >= 4){
		$sQuery = "
			SELECT *
			FROM
			 (
				SELECT ROW_NUMBER() OVER ($sOrder) AS ROW_COUNT," . str_replace(" , ", " ", implode(", ", $aColumns)) . "
				FROM $sTable
				$sWhere
        AND Job.Type <> 9 AND Job.Type <> 12
			) A
			WHERE A.ROW_COUNT BETWEEN $Start AND $End
		";
	} else {
		$sQuery = "
			SELECT *
			FROM
			 (
				SELECT ROW_NUMBER() OVER ($sOrder) AS ROW_COUNT," . str_replace(" , ", " ", implode(", ", $aColumns)) . "
				FROM $sTable
				$sWhere
        AND Job.Type <> 9 AND Job.Type <> 12
				AND Job.Loc IN
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
	if($My_Privileges['Job']['Other_Privilege'] >= 4){
		$sQueryRow = "
			SELECT ".str_replace(" , ", " ", implode(", ", $aColumns))."
			FROM   $sTable
			$sWhere
      AND Job.Type <> 9 AND Job.Type <> 12
		;";
	} else {
		$sQueryRow = "
			SELECT ".str_replace(" , ", " ", implode(", ", $aColumns))."
			FROM   $sTable
			$sWhere
      AND Job.Type <> 9 AND Job.Type <> 12
			AND  Job.Loc IN
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
}
?>
