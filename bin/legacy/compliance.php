<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [
        'read_and_close' => true
    ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
if( isset( $_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
  $result = sqlsrv_query(
    $NEI,
    " SELECT  *
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
  $result = sqlsrv_query(
    $NEI,
    " SELECT  *,
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
  $result = sqlsrv_query(
    $NEI,
    " SELECT  Privilege.Access_Table,
              Privilege.User_Privilege,
              Privilege.Group_Privilege,
              Privilege.Other_Privilege
      FROM    Privilege
      WHERE   Privilege.User_ID = ?;",
    array(
      $_SESSION[ 'User' ]
    )
  );
  $Privileges = array();
  if( $result ){while( $Privilege = sqlsrv_fetch_array( $result ) ){ $Privileges[ $Privilege[ 'Access_Table' ] ] = $Privilege; } }
  if(     !isset( $Connection[ 'ID' ] )
      ||  !isset($Privileges[ 'Violation' ])
      ||  $Privileges[ 'Violation' ][ 'User_Privilege' ]  < 4
      ||  $Privileges[ 'Violation' ][ 'Group_Privilege' ] < 4
      ||  $Privileges[ 'Violation' ][ 'Other_Privilege' ] < 4
  ){
      ?><?php require( '../404.html' );?><?php
  } else {
    sqlsrv_query(
      $NEI,
      " INSERT INTO Activity( [User], [Date], [Page] )
        VALUES( ?, ?, ? );",
      array(
        $_SESSION[ 'User' ],
        date( 'Y-m-d H:i:s' ),
        'compliance.php'
      )
    );
?><!DOCTYPE html>
<html lang="en">
<head>
    <?php require( bin_meta . 'index.php');?>
    <title>Nouveau Texas | Portal</title>
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>
</head>
<body onload='finishLoadingPage();'>
<div id='container'>
  <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
      <?php require( bin_php . 'element/navigation.php');?>
      <?php require( bin_php . 'element/loading.php');?>
      <div id="page-wrapper" class='content' style='margin-right:0px !important;'>
        <div class='panel-panel-primary'>
          <div class='panel-heading'><h2>Code Compliance</h2></div>
          <div class='panel-body'>
            <div class='row'>
              <div class='col-xs-3' style='background-color:black;color:white;border:1px solid white;'>Division #1</div>
              <div class='col-xs-3' style='border:1px solid black;'>Division #2</div>
              <div class='col-xs-3' style='background-color:black;color:white;border:1px solid white;'>Division #3</div>
              <div class='col-xs-3' style='border:1px solid black;'>Division #4</div>
            </div>
            <div class='row'>
              <div class='col-xs-3' style='background-color:black;color:white;border:1px solid white;'>Total: <?
                $r = $database->query(null,
                  " SELECT  Count(Violation.ID) AS Count
                    FROM    Violation
                            LEFT JOIN Loc ON Loc.Loc = Violation.Loc
                            LEFT JOIN Zone ON Loc.Zone = Zone.ID
                    WHERE   Zone.Name = 'DIVISION #1'
                            AND Violation.Status <> 'Dismissed'
                            AND Violation.Status <> 'Preliminary Report'
                  ;");
                echo $r ? sqlsrv_fetch_array($r)['Count'] : 0;
              ?></div>
              <div class='col-xs-3' style='border:1px solid black;'>Total: <?
                $r = $database->query(null,
                  " SELECT  Count(Violation.ID) AS Count
                    FROM    Violation
                            LEFT JOIN Loc ON Loc.Loc = Violation.Loc
                            LEFT JOIN Zone ON Loc.Zone = Zone.ID
                    WHERE   Zone.Name = 'DIVISION #2'
                            AND Violation.Status <> 'Dismissed'
                            AND Violation.Status <> 'Preliminary Report'

                  ;");
                echo $r ? sqlsrv_fetch_array($r)['Count'] : 0;
              ?></div>
              <div class='col-xs-3' style='background-color:black;color:white;border:1px solid white;'>Total: <?
                $r = $database->query(null,
                  " SELECT  Count(Violation.ID) AS Count
                    FROM    Violation
                            LEFT JOIN Loc ON Loc.Loc = Violation.Loc
                            LEFT JOIN Zone ON Loc.Zone = Zone.ID
                    WHERE   Zone.Name = 'DIVISION #3'
                            AND Violation.Status <> 'Dismissed'
                            AND Violation.Status <> 'Preliminary Report'
                  ;");
                echo $r ? sqlsrv_fetch_array($r)['Count'] : 0;
              ?></div>
              <div class='col-xs-3' style='border:1px solid black;'>Total: <?
                $r = $database->query(null,
                  " SELECT  Count(Violation.ID) AS Count
                    FROM    Violation
                            LEFT JOIN Loc ON Loc.Loc = Violation.Loc
                            LEFT JOIN Zone ON Loc.Zone = Zone.ID
                    WHERE   Zone.Name = 'DIVISION #4'
                            AND Violation.Status <> 'Dismissed'
                            AND Violation.Status <> 'Preliminary Report'
                  ;");
                echo $r ? sqlsrv_fetch_array($r)['Count'] : 0;
              ?></div>
            </div>
            <div class='row'>
              <?php $Division_Name = "DIVISION #1";?>
              <div class='col-xs-1' style='background-color:black;color:white;border:1px solid white;'>Code:<?
                $r = $database->query(null,
                  " SELECT  Count(Violation.ID) AS Count
                    FROM    Violation
                            LEFT JOIN Loc ON Loc.Loc = Violation.Loc
                            LEFT JOIN Zone ON Loc.Zone = Zone.ID
                    WHERE   Zone.Name = ?
                            AND Violation.Status = 'Code'
                  ;",array($Division_Name));
                echo $r ? sqlsrv_fetch_array($r)['Count'] : 0;
              ?></div>
              <div class='col-xs-1' style='background-color:black;color:white;border:1px solid white;'>Sales:<?
                $r = $database->query(null,
                  " SELECT  Count(Violation.ID) AS Count
                    FROM    Violation
                            LEFT JOIN Loc ON Loc.Loc = Violation.Loc
                            LEFT JOIN Zone ON Loc.Zone = Zone.ID
                    WHERE   Zone.Name = ?
                            AND Violation.Status = 'To Sales'
                  ;",array($Division_Name));
                echo $r ? sqlsrv_fetch_array($r)['Count'] : 0;
              ?></div>
              <div class='col-xs-1' style='background-color:black;color:white;border:1px solid white;'>Job Created:<?
                $r = $database->query(null,
                  " SELECT  Count(Violation.ID) AS Count
                    FROM    Violation
                            LEFT JOIN Loc ON Loc.Loc = Violation.Loc
                            LEFT JOIN Zone ON Loc.Zone = Zone.ID
                    WHERE   Zone.Name = ?
                            AND Violation.Status = 'Job Created'
                  ;",array($Division_Name));
                echo $r ? sqlsrv_fetch_array($r)['Count'] : 0;
              ?></div>
              <?php $Division_Name = "DIVISION #2";?>
              <div class='col-xs-1' style='border:1px solid black;'>Code:<?
                $r = $database->query(null,
                  " SELECT  Count(Violation.ID) AS Count
                    FROM    Violation
                            LEFT JOIN Loc ON Loc.Loc = Violation.Loc
                            LEFT JOIN Zone ON Loc.Zone = Zone.ID
                    WHERE   Zone.Name = ?
                            AND Violation.Status = 'Code'
                  ;",array($Division_Name));
                echo $r ? sqlsrv_fetch_array($r)['Count'] : 0;
              ?></div>
              <div class='col-xs-1' style='border:1px solid black;'>Sales:<?
                $r = $database->query(null,
                  " SELECT  Count(Violation.ID) AS Count
                    FROM    Violation
                            LEFT JOIN Loc ON Loc.Loc = Violation.Loc
                            LEFT JOIN Zone ON Loc.Zone = Zone.ID
                    WHERE   Zone.Name = ?
                            AND Violation.Status = 'To Sales'
                  ;",array($Division_Name));
                echo $r ? sqlsrv_fetch_array($r)['Count'] : 0;
              ?></div>
              <div class='col-xs-1' style='border:1px solid black;'>Job Created:<?
                $r = $database->query(null,
                  " SELECT  Count(Violation.ID) AS Count
                    FROM    Violation
                            LEFT JOIN Loc ON Loc.Loc = Violation.Loc
                            LEFT JOIN Zone ON Loc.Zone = Zone.ID
                    WHERE   Zone.Name = ?
                            AND Violation.Status = 'Job Created'
                  ;",array($Division_Name));
                echo $r ? sqlsrv_fetch_array($r)['Count'] : 0;
              ?></div>
              <?php $Division_Name = "DIVISION #3";?>
              <div class='col-xs-1' style='background-color:black;color:white;border:1px solid white;'>Code:<?
                $r = $database->query(null,
                  " SELECT  Count(Violation.ID) AS Count
                    FROM    Violation
                            LEFT JOIN Loc ON Loc.Loc = Violation.Loc
                            LEFT JOIN Zone ON Loc.Zone = Zone.ID
                    WHERE   Zone.Name = ?
                            AND Violation.Status = 'Code'
                  ;",array($Division_Name));
                echo $r ? sqlsrv_fetch_array($r)['Count'] : 0;
              ?></div>
              <div class='col-xs-1' style='background-color:black;color:white;border:1px solid white;'>Sales:<?
                $r = $database->query(null,
                  " SELECT  Count(Violation.ID) AS Count
                    FROM    Violation
                            LEFT JOIN Loc ON Loc.Loc = Violation.Loc
                            LEFT JOIN Zone ON Loc.Zone = Zone.ID
                    WHERE   Zone.Name = ?
                            AND Violation.Status = 'To Sales'
                  ;",array($Division_Name));
                echo $r ? sqlsrv_fetch_array($r)['Count'] : 0;
              ?></div>
              <div class='col-xs-1' style='background-color:black;color:white;border:1px solid white;'>Job Created:<?
                $r = $database->query(null,
                  " SELECT  Count(Violation.ID) AS Count
                    FROM    Violation
                            LEFT JOIN Loc ON Loc.Loc = Violation.Loc
                            LEFT JOIN Zone ON Loc.Zone = Zone.ID
                    WHERE   Zone.Name = ?
                            AND Violation.Status = 'Job Created'
                  ;",array($Division_Name));
                echo $r ? sqlsrv_fetch_array($r)['Count'] : 0;
              ?></div>
              <?php $Division_Name = "DIVISION #4";?>
              <div class='col-xs-1' style='border:1px solid black;'>Code:<?
                $r = $database->query(null,
                  " SELECT  Count(Violation.ID) AS Count
                    FROM    Violation
                            LEFT JOIN Loc ON Loc.Loc = Violation.Loc
                            LEFT JOIN Zone ON Loc.Zone = Zone.ID
                    WHERE   Zone.Name = ?
                            AND Violation.Status = 'Code'
                  ;",array($Division_Name));
                echo $r ? sqlsrv_fetch_array($r)['Count'] : 0;
              ?></div>
              <div class='col-xs-1' style='border:1px solid black;'>Sales:<?
                $r = $database->query(null,
                  " SELECT  Count(Violation.ID) AS Count
                    FROM    Violation
                            LEFT JOIN Loc ON Loc.Loc = Violation.Loc
                            LEFT JOIN Zone ON Loc.Zone = Zone.ID
                    WHERE   Zone.Name = ?
                            AND Violation.Status = 'To Sales'
                  ;",array($Division_Name));
                echo $r ? sqlsrv_fetch_array($r)['Count'] : 0;
              ?></div>
              <div class='col-xs-1' style='border:1px solid black;'>Job Created:<?
                $r = $database->query(null,
                  " SELECT  Count(Violation.ID) AS Count
                    FROM    Violation
                            LEFT JOIN Loc ON Loc.Loc = Violation.Loc
                            LEFT JOIN Zone ON Loc.Zone = Zone.ID
                    WHERE   Zone.Name = ?
                            AND Violation.Status = 'Job Created'
                  ;",array($Division_Name));
                echo $r ? sqlsrv_fetch_array($r)['Count'] : 0;
              ?></div>
            </div>
            <div class='row'>
              <?php $Division_Name = "DIVISION #1";?>
              <div class='col-xs-1' style='background-color:black;color:white;border:1px solid white;'>DOB:<?
                $r = $database->query(null,
                  " SELECT  Count(Violation.ID) AS Count
                    FROM    Violation
                            LEFT JOIN Loc ON Loc.Loc = Violation.Loc
                            LEFT JOIN Zone ON Loc.Zone = Zone.ID
                    WHERE   Zone.Name = ?
                            AND (
                              Violation.Status = 'Forms to DOB'
                              OR Violation.Status = 'Help-Tkt DoB'
                            )
                  ;",array($Division_Name));
                echo $r ? sqlsrv_fetch_array($r)['Count'] : 0;
              ?></div>
              <div class='col-xs-1' style='background-color:black;color:white;border:1px solid white;'>E-Filed:<?
                $r = $database->query(null,
                  " SELECT  Count(Violation.ID) AS Count
                    FROM    Violation
                            LEFT JOIN Loc ON Loc.Loc = Violation.Loc
                            LEFT JOIN Zone ON Loc.Zone = Zone.ID
                    WHERE   Zone.Name = ?
                            AND Violation.Status = 'AOC E-filed'
                  ;",array($Divsion_Name));
                echo $r ? sqlsrv_fetch_array($r)['Count'] : 0;
              ?></div>
              <div class='col-xs-1' style='background-color:black;color:white;border:1px solid white;'>Contract Canceled:<?
                $r = $database->query(null,
                  " SELECT  Count(Violation.ID) AS Count
                    FROM    Violation
                            LEFT JOIN Loc ON Loc.Loc = Violation.Loc
                            LEFT JOIN Zone ON Loc.Zone = Zone.ID
                    WHERE   Zone.Name = ?
                            AND Violation.Status = 'CONTRACT CANCELLED'
                  ;",array($Division_Name));
                echo $r ? sqlsrv_fetch_array($r)['Count'] : 0;
              ?></div>
              <?php $Division_Name = "DIVISION #2";?>
              <div class='col-xs-1' style='border:1px solid black;'>DOB:<?
                $r = $database->query(null,
                  " SELECT  Count(Violation.ID) AS Count
                    FROM    Violation
                            LEFT JOIN Loc ON Loc.Loc = Violation.Loc
                            LEFT JOIN Zone ON Loc.Zone = Zone.ID
                    WHERE   Zone.Name = ?
                            AND (
                              Violation.Status = 'Forms to DOB'
                              OR Violation.Status = 'Help-Tkt DoB'
                            )
                  ;",array($Division_Name));
                echo $r ? sqlsrv_fetch_array($r)['Count'] : 0;
              ?></div>
              <div class='col-xs-1' style='border:1px solid black;'>E-Filed:<?
                $r = $database->query(null,
                  " SELECT  Count(Violation.ID) AS Count
                    FROM    Violation
                            LEFT JOIN Loc ON Loc.Loc = Violation.Loc
                            LEFT JOIN Zone ON Loc.Zone = Zone.ID
                    WHERE   Zone.Name = ?
                            AND Violation.Status = 'AOC E-filed'
                  ;",array($Divsion_Name));
                echo $r ? sqlsrv_fetch_array($r)['Count'] : 0;
              ?></div>
              <div class='col-xs-1' style='border:1px solid black;'>Contract Canceled:<?
                $r = $database->query(null,
                  " SELECT  Count(Violation.ID) AS Count
                    FROM    Violation
                            LEFT JOIN Loc ON Loc.Loc = Violation.Loc
                            LEFT JOIN Zone ON Loc.Zone = Zone.ID
                    WHERE   Zone.Name = ?
                            AND Violation.Status = 'CONTRACT CANCELLED'
                  ;",array($Division_Name));
                echo $r ? sqlsrv_fetch_array($r)['Count'] : 0;
              ?></div>
              <?php $Division_Name = "DIVISION #3";?>
              <div class='col-xs-1' style='background-color:black;color:white;border:1px solid white;'>DOB:<?
                $r = $database->query(null,
                  " SELECT  Count(Violation.ID) AS Count
                    FROM    Violation
                            LEFT JOIN Loc ON Loc.Loc = Violation.Loc
                            LEFT JOIN Zone ON Loc.Zone = Zone.ID
                    WHERE   Zone.Name = ?
                            AND (
                              Violation.Status = 'Forms to DOB'
                              OR Violation.Status = 'Help-Tkt DoB'
                            )
                  ;",array($Division_Name));
                echo $r ? sqlsrv_fetch_array($r)['Count'] : 0;
              ?></div>
              <div class='col-xs-1' style='background-color:black;color:white;border:1px solid white;'>E-Filed:<?
                $r = $database->query(null,
                  " SELECT  Count(Violation.ID) AS Count
                    FROM    Violation
                            LEFT JOIN Loc ON Loc.Loc = Violation.Loc
                            LEFT JOIN Zone ON Loc.Zone = Zone.ID
                    WHERE   Zone.Name = ?
                            AND Violation.Status = 'AOC E-filed'
                  ;",array($Divsion_Name));
                echo $r ? sqlsrv_fetch_array($r)['Count'] : 0;
              ?></div>
              <div class='col-xs-1' style='background-color:black;color:white;border:1px solid white;'>Contract Canceled:<?
                $r = $database->query(null,
                  " SELECT  Count(Violation.ID) AS Count
                    FROM    Violation
                            LEFT JOIN Loc ON Loc.Loc = Violation.Loc
                            LEFT JOIN Zone ON Loc.Zone = Zone.ID
                    WHERE   Zone.Name = ?
                            AND Violation.Status = 'CONTRACT CANCELLED'
                  ;",array($Division_Name));
                echo $r ? sqlsrv_fetch_array($r)['Count'] : 0;
              ?></div>
              <?php $Division_Name = "DIVISION #4";?>
              <div class='col-xs-1' style='border:1px solid black;'>DOB:<?
                $r = $database->query(null,
                  " SELECT  Count(Violation.ID) AS Count
                    FROM    Violation
                            LEFT JOIN Loc ON Loc.Loc = Violation.Loc
                            LEFT JOIN Zone ON Loc.Zone = Zone.ID
                    WHERE   Zone.Name = ?
                            AND (
                              Violation.Status = 'Forms to DOB'
                              OR Violation.Status = 'Help-Tkt DoB'
                            )
                  ;",array($Division_Name));
                echo $r ? sqlsrv_fetch_array($r)['Count'] : 0;
              ?></div>
              <div class='col-xs-1' style='border:1px solid black;'>E-Filed:<?
                $r = $database->query(null,
                  " SELECT  Count(Violation.ID) AS Count
                    FROM    Violation
                            LEFT JOIN Loc ON Loc.Loc = Violation.Loc
                            LEFT JOIN Zone ON Loc.Zone = Zone.ID
                    WHERE   Zone.Name = ?
                            AND Violation.Status = 'AOC E-filed'
                  ;",array($Divsion_Name));
                echo $r ? sqlsrv_fetch_array($r)['Count'] : 0;
              ?></div>
              <div class='col-xs-1' style='border:1px solid black;'>Contract Canceled:<?
                $r = $database->query(null,
                  " SELECT  Count(Violation.ID) AS Count
                    FROM    Violation
                            LEFT JOIN Loc ON Loc.Loc = Violation.Loc
                            LEFT JOIN Zone ON Loc.Zone = Zone.ID
                    WHERE   Zone.Name = ?
                            AND Violation.Status = 'CONTRACT CANCELLED'
                  ;",array($Division_Name));
                echo $r ? sqlsrv_fetch_array($r)['Count'] : 0;
              ?></div>
            </div>
            <!--<div class='row'><div class='col-xs-12'>&nbsp;</div></div>-->
            <div class='row'>
              <div class='col-xs-8' style='border:3px solid black;'><h3>Timeline</h3></div>
              <div class='col-xs-4' style='border:3px solid black;'><h3>Violations by Location</h3></div>
            </div>
            <div class='row'>
              <div class='col-xs-8' id='Timeline' style='height:600px;overflow-y:scroll;border:3px solid black;'>
              </div>
              <div class='col-xs-4' id='Locations' style='height:600px;overflow-y:scroll;border:3px solid black;'><?php
                $r = $database->query(null,
                  " SELECT    Loc.Tag AS Location_Tag,
                              Zone.Name AS Division_Name,
                              Count(Violation.ID) AS Count
                    FROM      Violation
                              LEFT JOIN Loc ON Loc.Loc = Violation.Loc
                              LEFT JOIN Zone ON Loc.Zone = Zone.ID
                    WHERE     Violation.Status <> 'Dismissed'
                              AND Zone.Name <> 'Repair'
                    GROUP BY  Loc.Tag, Zone.Name
                    ORDER BY  Count(Violation.ID) DESC
                  ;");
                if($r){while($row = sqlsrv_fetch_array($r)){
                  ?><div class='row'>
                    <div class='col-xs-3'><?php echo $row['Division_Name'];?></div>
                    <div class='col-xs-6'><?php echo $row['Location_Tag'];?></div>
                    <div class='col-xs-3'><?php echo $row['Count'];?></div>
                  </div><?php
                }
              }
              ?></div>
            </div>
            <div class='row'>
              <?php $rows = array();
              $r = $database->query(null,
              "   SELECT  *
                  FROM
                  (
                        (
                          SELECT Violation.ID            AS ID,
                             Violation.Name              AS Name,
                             Violation.fdate             AS fDate,
                             Violation.Status            AS Status,
                             Loc.Tag                     AS Location,
                             Elev.State                  AS Unit,
                             Zone.Name                   AS Division,
                             Emp.fFirst + ' ' + Emp.Last AS Mechanic,
                             Violation.Job 			   AS Job,
                             /*SUBSTRING(Violation.Remarks,CHARINDEX('DUE: ',Violation.Remarks)+5,8) AS Due_Date,*/
                             Violation.Custom7 AS Due_Date,
                             '' 						   AS Remarks,
                                         Terr.Name                   AS Territory
                             FROM   Violation
                             LEFT JOIN Elev  ON Violation.Elev = Elev.ID
                             LEFT JOIN Loc   ON Violation.Loc  = Loc.Loc
                             LEFT JOIN Zone  ON Loc.Zone       = Zone.ID
                             LEFT JOIN Route ON Loc.Route      = Route.ID
                             LEFT JOIN Emp   ON Route.Mech     = Emp.fWork
                             LEFT JOIN Job   ON Violation.Job  = Job.ID
                                         LEFT JOIN Terr  ON Loc.Terr       = Terr.ID
                          WHERE
                              Violation.Status <> 'Dismissed'
                              AND Violation.ID     <> 0
                              AND (Violation.Job = 0
                              OR
                                (Violation.Job > 0
                                AND Job.Status = 0))
                          )
                    ) AS Violations
                    ORDER BY ID DESC
              ;",array());
              if($r){while($row = sqlsrv_fetch_array($r)){
                $rows[] = $row;
              }}
              usort($rows, function ($a, $b) {
                  return date("Y-m-d",strtotime($a['Due_Date'])) <=> date("Y-m-d",strtotime($b['Due_Date']));
              });
              $count = 0;
              foreach($rows as $row){
                if(date("Y-m-d",strtotime($row['Due_Date'])) > date("Y-m-d")){continue;}
                $count++;
              }?>
              <div class='col-xs-6' style='border:3px solid black;'><h3><?php echo $count;?> Due Violations</h3></div>
              <?php
              $r = $database->query(null,"SELECT Count(Violation.ID) AS Count FROM Violation WHERE Violation.Status = 'Code';");
              $count = sqlsrv_fetch_array($r)['Count'];
              ?>
              <div class='col-xs-6' style='border:3px solid black;'><h3><?php echo $count;?> Violations in Code Department</h3></div>
            </div>
            <div class='row'>
              <div class='col-xs-6' style='height:600px;overflow-y:scroll;border:3px solid black;'><?php
              if(count($rows) > 0){foreach($rows AS $row){
                if(date("Y-m-d",strtotime($row['Due_Date'])) > date("Y-m-d")){continue;}
                ?><div class='row' ondblclick='popupViolation(<?php echo $row['ID'];?>);'>
                  <div class='col-xs-1'><?php echo $row['ID'];?></div>
                  <div class='col-xs-3'><?php echo $row['Location'];?></div>
                  <div class='col-xs-1'><?php echo $row['Unit'];?></div>
                  <div class='col-xs-3'><?php echo $row['Status'];?></div>
                  <div class='col-xs-2'><?php echo date("m/d/Y",strtotime($row['Due_Date']));?></div>
                  <div class='col-xs-1'><?php echo $row['Job'];?></div>
                </div><?php
              }}
              ?></div>
              <?php
              $r = $database->query(null,"SELECT Violation.*, Loc.Tag AS Location_Tag, Zone.Name AS Division_Name, Elev.State AS Unit_State FROM Violation LEFT JOIN Loc ON Violation.Loc = Loc.Loc LEFT JOIN Zone ON Zone.ID = Loc.Zone LEFT JOIN Elev ON Violation.Elev = Elev.ID WHERE Violation.Status = 'Code' ORDER BY Zone.NAme ASC, Loc.Tag ASC;");
              $rows = array();
              if($r){while($row = sqlsrv_fetch_array($r)){
                $rows[] = $row;
              }}
              ?>
              <div class='col-xs-6' style='height:600px;overflow-y:scroll;border:3px solid black;'><?php
                if(count($rows) > 0){foreach($rows as $row){
                  ?><div class='row' ondblclick='popupViolation(<?php echo $row['ID'];?>);'>
                    <div class='col-xs-2'>Violation #<?php echo $row['ID'];?></div>
                    <div class='col-xs-5'><?php echo $row['Location_Tag'];?></div>
                    <div class='col-xs-1'><?php echo $row['Unit_State'];?></div>
                    <div class='col-xs-2'><?php echo $row['Division_Name'];?></div>
                    <div class='col-xs-2'><?php echo $row['Job'];?></div>
                  </div><?php
                }}
              ?></div>
            </div>
            <div class='row'>
              <div class='col-xs-6' style='border:3px solid black;'><h3> Violation Oppourtnities</h3></div>
              <div class='col-xs-6' style='border:3px solid black;'><h3>Open Tickets</h3></div>
            </div>
            <div class='row'>
              <div class='col-xs-6' style='height:600px;overflow-y:scroll;border:3px solid black;'><?php
                $r = $database->query(null,
                  " SELECT  Loc.Tag AS Location_Tag,
                            Count(Violation.ID) AS Violation_Count,
                            Emp.fFirst + ' ' + Emp.Last AS Employee_Name,
                            Zone.Name AS Division,
                            tblWork.Super AS Supervisor,
                            TicketO.Level AS Level,
                            TicketO.ID AS ID
                    FROM    TicketO
                            LEFT JOIN Loc ON TicketO.LID = Loc.Loc
                            LEFT JOIN Emp ON Emp.fWork = TicketO.fWork
                            LEFT JOIN Violation ON Violation.Loc = Loc.Loc
                            LEFT JOIN Job ON Violation.Job = Job.ID
                            LEFT JOIN Zone ON Zone.ID = Loc.Zone
                            LEFT JOIN tblWork ON 'A' + convert(varchar(10),Emp.ID) + ',' = tblWork.Members
                    WHERE   Violation.Status = 'Job Created'
                            AND Job.Status = 0
                            AND TicketO.Assigned = 3
                            AND tblWork.Super LIKE '%division%'
                    GROUP BY Loc.Tag, Zone.Name, Emp.fFirst + ' ' + Emp.Last, Zone.Name, tblWork.Super, TicketO.Level, TicketO.ID
                    ORDER BY Zone.Name ASC
                  ;",array());
                $rows = array();
                if($r){while($row = sqlsrv_fetch_array($r)){
                    $rows[] = $row;
                }}
                if(count($rows) > 0){foreach($rows as $row){?>
                  <div class='row' ondblclick="popupTicket(<?php echo $row['ID'];?>);">
                    <div class='col-xs-4'><?php echo $row['Location_Tag'];?></div>
                    <div class='col-xs-1'><?php echo $row['Violation_Count'];?></div>
                    <div class='col-xs-3'><?php echo $row['Employee_Name'];?></div>
                    <div class='col-xs-2'><?php echo $row['Division'];?></div>
                    <div class='col-xs-2'><?php echo $row['Level'] == 4 ? 'Violations' : 'Other';?></div>
                  </div><?php
                }}
              ?></div>
              <div class='col-xs-6' style='height:600px;overflow-y:scroll;border:3px solid black;'><?php
                $r = $database->query(null,"SELECT TicketO.*, Loc.Tag AS Location_Tag, Zone.Name AS Division_Name, Emp.fFirst + ' ' + Emp.Last AS Employee_Name, TickOStatus.Type AS Status FROM TicketO LEFT JOIN TickOStatus ON TicketO.Assigned = TickOStatus.Ref LEFT JOIN Loc ON TicketO.LID = Loc.Loc LEFT JOIN Zone ON Loc.Zone = Zone.ID LEFT JOIN Emp ON TicketO.fWork = Emp.fWork WHERE TicketO.Level = 4 ORDER BY TicketO.CDate ASC;");
                if($r){while($row = sqlsrv_fetch_array($r)){
                  ?><div class='row' ondblclick="popupTicket(<?php echo $row['ID'];?>);">
                    <div class='col-xs-2'><?php echo date("m/d/Y",strtotime($row['CDate']));?></div>
                    <div class='col-xs-2'><?php echo $row['Division_Name'];?></div>
                    <div class='col-xs-3'><?php echo $row['Location_Tag'];?></div>
                    <div class='col-xs-3'><?php echo $row['Employee_Name'];?></div>
                    <div class='col-xs-2'><?php echo $row['Status'];?></div>
                  </div><?php
                }}
              ?></div>
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
} else {?><html><head><script>document.location.href='../login.php?Forward=compliance.php';</script></head></html><?php }?>
