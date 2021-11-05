<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [
        'read_and_close' => true
    ] );
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/cgi-bin/php/index.php' );
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
      ||  !isset($Privileges[ 'Time' ])
      ||  $Privileges[ 'Time' ][ 'User_Privilege' ]  < 4
      ||  $Privileges[ 'Time' ][ 'Group_Privilege' ] < 4
      ||  $Privileges[ 'Time' ][ 'Other_Privilege' ] < 4
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
        'attendance2.php'
      )
    );
?><!DOCTYPE html>
<html lang="en"style="min-height:100%;height:100%;webkit-background-size: cover;-moz-background-size: cover;-o-background-size: cover;background-size: cover;height:100%;">
<head>
    <?php require( bin_meta . 'index.php');?>
    <meta http-equiv="refresh" content="300">
	<title>Nouveau Illinois Portal</title>
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>
    <link rel='stylesheet' href='cgi-bin/libraries/timepicker/jquery.timepicker.min.css' />
    <script src='cgi-bin/libraries/timepicker/jquery.timepicker.min.js'></script>
</head>
<body onload='finishLoadingPage();' style="min-height:100%;background-size:cover;background-color:rgba(255,255,255,.7);height:100%;">
    <div id='container' style='min-height:100%;height:100%;'>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>" style='height:100%;'>
        <?php require( bin_php . 'element/navigation/index.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content'>
          <div class='panel'>
            <div class='panel-heading'>
              <h4>Attendance</h4>
            </div>
            <div class='panel-body'>
              <div class='row'>
              <form action='attendance2.php'>
                <!--<div class='col-xs-12'>Start: <input name='Start' value='<?php echo isset($_GET['Start']) ? $_GET['Start'] : '';?>' /></div>
                <div class='col-xs-12'>End: <input name='End'  value='<?php echo isset($_GET['End']) ? $_GET['End'] : '';?>'   /></div>
                <div class='col-xs-12'><input type='submit' value='Search' /></div>-->
                <div class='col-xs-12'>Supervisor: <select name='Supervisor'><?php
                  $r = \singleton\database::getInstance()->query((
                    null,
                      "   SELECT   tblWork.Super
                          FROM     tblWork
                          GROUP BY tblWork.Super
                          ORDER BY tblWork.Super
                          ASC;");
                  if($r){while($row = sqlsrv_fetch_array($r)){
                    ?><option value='<?php echo $row['Super'];?>' <?php if(isset($_GET['Supervisor']) && $_GET['Supervisor'] == $row['Super']){?>selected<?php }?>><?php echo $row['Super'];?></option><?php
                  }}
                ?></select>
                <div class='col-xs-12'><input type='submit' value='Search' /></div>
              </form>
              </div>
            </div>
          </div>
          <div class='panel-body'>
            <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
            <div class='row'>
              <div class='col-xs-4'>&nbsp;</div>
              <div class='col-xs-1' style='background-color:gold;'>Clocked In</div>
              <div class='col-xs-1' style='background-color:green;color:white;'>Worked Day</div>
              <div class='col-xs-1' style='background-color:red;'>PTO</div>
              <div class='col-xs-1' style='background-color:#282828;color:white;'>Weekend</div>
            </div>
            <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
          </div>
          <div class='panel-body'>
            <div style='margin-left:100px;'>
            <table id='attendance' style=''>
              <!--<?php $i = 1;?><colgroup><?php while($i < 32){?><col style='<?php if($i == intval(date("d"))){?>border:5px solid black !important;<?php }?>'></col><?php $i++;}?></colgroup>-->
              <thead><tr>
                <th>Attendance Sheet - <?php echo isset($_GET['Supervisor']) ? $_GET['Supervisor'] : 'All';?></th>
                <?php $i = 1;
                while($i < 32){?><th><?php echo $i;?></th><?php $i++;}?></tr></thead>
                <thead><tr>
                  <th>Name</th>
                  <?php $i = 1;
                  while($i < 32){?><th><?php echo $i > 10 ? date("D",strtotime(date("Y-m-{$i} 00:00:00.000"))) : date("D",strtotime(date("Y-m-0{$i} 00:00:00.000")));?></th><?php $i++;}?></tr></thead>
                <tbody style=''>
                  <?php
                  if(isset($_GET['Supervisor'])  && strlen($_GET['Supervisor']) > 0) {
                    //$_GET['Start'] = date('Y-m-d H:i:s',strtotime($_GET['Start']));
                    //$_GET['End'] = date('Y-m-d H:i:s',strtotime($_GET['End']));
                    $r = $database->query($Portal,"
                      SELECT Emp.ID AS ID,
                             Emp.fWork AS fWork,
                             Emp.fFirst,
                             Emp.Last
                      FROM   Emp
                             LEFT JOIN nei.dbo.tblWork ON 'A' + convert(varchar(10),Emp.ID) + ',' = tblWork.Members
                      WHERE  tblWork.Super = ?
                             AND Emp.Status = 0
                      ORDER BY Emp.Last ASC
                    ;",array($_GET['Supervisor']));
                  } else {
                    $_GET['Start'] = date('Y-m-d H:i:s',strtotime($_GET['Start']));
                    $_GET['End'] = date('Y-m-d H:i:s',strtotime($_GET['End']));
                    $r = $database->query($Portal,"
                      SELECT Top 25 Emp.ID AS ID,
                             Emp.fWork AS fWork,
                             Emp.fFirst,
                             Emp.Last
                      FROM   Emp
                      ORDER BY Emp.Last ASC
                    ;",array());
                  }
                  $data = array();
                  $sQuery = "SELECT Attendance.[Start], Attendance.[End] FROM Portal.dbo.Attendance WHERE Attendance.[User] = ? ORDER BY Attendance.[ID] DESC;";
                  if($r){while($row = sqlsrv_fetch_array($r)){
                    $r2 = $database->query($Portal, $sQuery, array($row['ID']));
                    if($r2){
                      $row2 = sqlsrv_fetch_array($r2);
                      $row2 = is_array($row2) ? $row2 : array('Start'=>'1899-12-30 00:00:00.000', 'End'=>'1899-12-30 00:00:00.000');
                    }
                    $row['Start'] = $row2['Start'] == '1899-12-30 00:00:00.000' ? '' : date("m/d/Y H:i A",strtotime($row2['Start']));
                    $row['End'] = $row2['End'] == '1899-12-30 00:00:00.000' ? '' : date("m/d/Y H:i A",strtotime($row2['End']));
                    $data[] = $row;
                  }}
                  foreach($data as $user){?><tr>
                    <td style='border:1px solid black;text-align:right;'><a href='user.php?ID=<?php echo $user['ID'];?>'><?php echo $user['Last'] . ", " . $user['fFirst'];?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a></td><?php
                    $i = 1;
                    while($i < 32){
                      $today = $i < 10 ? date("Y-m-0{$i} 00:00:00.000") : date("Y-m-{$i} 00:00:00.000");
                      if($i + 1 < 10){
                        $i2 = $i + 1;
                        $tomorrow = date("Y-m-0{$i2} 00:00:00.000");
                      } elseif($i == 30){
                        $month = date("m") + 1;
                        $tomorrow = date("Y-{$month}-01 00:00:00.000");
                      } else {
                        $i2 = $i + 1;
                        $tomorrow = date("Y-m-{$i2} 00:00:00.000");
                      }
                      $r = $database->query(null,"SELECT Top 1 * FROM Portal.dbo.Attendance WHERE Attendance.[User] = ? AND Attendance.[Start] >= ? AND Attendance.[Start] < ? AND (Attendance.[End] < ? OR Attendance.[End] IS NULL) ORDER BY Attendance.ID DESC;",array($user['ID'],$today, $tomorrow, $tomorrow, $tomorrow));
                      if($r){$row = sqlsrv_fetch_array($r);}
                      if(is_array($row)){
                        if(isset($row['End']) && !is_null($row['End']) && date("N",strtotime($today)) >= 6){
                          $color = 'darkgreen';
                        } elseif(isset($row['End']) && !is_null($row['End'])){
                          $color = 'green';
                        } else {
                          $color = 'yellow';
                        }
                      } else {
                        $r = $database->query(null,"SELECT Top 1 * FROM nei.dbo.Unavailable WHERE Unavailable.Worker = ? AND Unavailable.fDate >= ? AND Unavailable.fDate < ?",array($user['fWork'], $today, $tomorrow));
                        if($r){$row = sqlsrv_fetch_array($r);}
                        if(is_array($row)){
                          $color = 'red';
                        } elseif(intval(date("d")) != $i) {
                          if(date("d") > $i){
                            if(date("N",strtotime($today)) >= 6){
                              $color = '#282828';
                            } else {
                              $color = 'gray';
                            }
                          } else {
                            if(date("N",strtotime($today)) >= 6){
                              $color = '#282828';
                            } else {
                              $color = 'lightgray';
                            }
                          }
                        } else {
                          $color = 'white';
                        }
                      }
                      ?><td onClick="<?php if($color != 'green' && $color !='yellow' && $color != 'darkgreen' && $today >= date("Y-m-d 00:00:00.000",strtotime('yesterday'))){?>schedule_pto('<?php echo $today;?>','<?php echo $user['fWork'];?>','<?php echo $user['fFirst'];?>','<?php echo $user['Last'];?>');<?php }?>" style="border:1px solid black;background-color:<?php echo $color;?>;width:35px !important;<?php echo isset($border) ? $border : '';?>">&nbsp;</td><?php
                      $i++;
                    }
                  }
                  ?></tr>
                </tbody>
            </table>
            </div>
          </div>
        </div>
    </div>
	</div>
<link href="cgi-bin/libraries/fixedHeader.css" rel="stylesheet" type="text/css" media="screen">
</body>
</html>
<?php
    }
} else {?><html><head><script>
  document.location.href="../login.php?Forward=attendance2.php?<?php if(count($_GET) > 0){$variables = array();foreach($_GET AS $key=>$value){$variables[] = "{$key}={$value}";}echo implode('&',$variables);}?>";
</script></head></html><?php }?>
