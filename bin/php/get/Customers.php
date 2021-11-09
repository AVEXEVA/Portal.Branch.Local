<?php
session_start( [ 'read_and_close' => true ] );
require('index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = \singleton\database::getInstance( )->query(null,"
        SELECT *
        FROM   Connection
        WHERE  Connection.Connector = ?
               AND Connection.Hash = ?
    ;", array($_SESSION['User'],$_SESSION['Hash']));
    $Connection = sqlsrv_fetch_array($r);
    $User    = \singleton\database::getInstance( )->query(null,"
        SELECT Emp.*,
               Emp.fFirst AS First_Name,
               Emp.Last   AS Last_Name 
        FROM   Emp
        WHERE  Emp.ID = ?
    ;", array($_SESSION['User']));
    $User = sqlsrv_fetch_array($User);
    $Field = ($User['Field'] == 1 && $User['Title'] != "OFFICE") ? True : False;
    $r = \singleton\database::getInstance( )->query($Portal,"
        SELECT Privilege.Access_Table,
               Privilege.User_Privilege,
               Privilege.Group_Privilege,
               Privilege.Other_Privilege
        FROM   Privilege
        WHERE  Privilege.User_ID = ?
    ;",array($_SESSION['User']));
    $Privileges = array();
    while($array2 = sqlsrv_fetch_array($r)){$Privileges[$array2['Access_Table']] = $array2;}
    $Privileged = False;
    if( isset($Privileges['Customer'])
	  	  && $Privileges['Customer']['User_Privilege'] >= 4){
        && $Privileges['Customer']['Group_Privilege'] >= 4){
        && $Privileges['Customer']['Other_Privilege'] >= 4){
            $Privileged = True;}
    if(!isset($Connection['ID'])  || !$Privileged){print json_encode(array('data'=>array()));}
    else {
$serverName = "172.16.12.45"; //serverName\instanceName
$connectionInfo = array(
	"Database"=>"nei",
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
    $aColumns = array( 'ID', 'Name', 'Status');

    /* Indexed column (used for fast and accurate table cardinality) */
    $sIndexColumn = "ID";

    /* DB table to use */
    $sTable = "OwnerWithRol";


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
    $sQuery =
    " SELECT *
      FROM
       (
          SELECT ROW_NUMBER() OVER ($sOrder) AS ROW_COUNT," . str_replace(" , ", " ", implode(", ", $aColumns)) . "
          FROM $sTable
          $sWhere
       ) A
      WHERE A.ROW_COUNT BETWEEN $Start AND $End
    ";
    //echo $sQuery;

    $rResult = \singleton\database::getInstance( )->query($conn,  $sQuery ) or die(print_r(sqlsrv_errors()));

    $sWhere =$pWhere;
    /* Data set length after filtering */
    $sQueryRow = "
        SELECT ".str_replace(" , ", " ", implode(", ", $aColumns))."
        FROM   $sTable
        $sWhere
    ";
    $params = array();
    $options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
    $stmt = \singleton\database::getInstance( )->query( $conn, $sQueryRow , $params, $options );

    $iFilteredTotal = sqlsrv_num_rows( $stmt );


    //echo "TOTAL " . $iFilteredTotal;
    /* Total data set length */
    $sQuery = "
        SELECT COUNT(".$sIndexColumn.")
        FROM   $sTable
    ";
    $rResultTotal = \singleton\database::getInstance( )->query($conn,  $sQuery ) or die(print_r(sqlsrv_errors()));
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
