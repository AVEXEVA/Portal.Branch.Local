<?php
session_start( [ 'read_and_close' => true ] );
require('../index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    sqlsrv_query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "ticket.php"));
    $r = sqlsrv_query($NEI,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID= ?",array($_SESSION['User']));
    $My_User = sqlsrv_fetch_array($r);
    $Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
    $r = sqlsrv_query($Portal,"
        SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
        FROM   Privilege
        WHERE  User_ID = ?
    ;",array($_SESSION['User']));
    $My_Privileges = array();
    while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
    if(!isset($array['ID'])){?><html><head></head></html><?php }
    else {
      $_POST['Notes'] = isset($_POST['Notes']) ? $_POST['Notes'] : '';
      $r = sqlsrv_query($NEI, "UPDATE Attendance SET Attendance.[End] = ?, Attendance.[End_Notes] = ? WHERE Attendance.[User] = ? AND Attendance.[End] IS NULL;",array(date("Y-m-d H:i:s"),$_POST['Notes'],$_SESSION['User']));
      if( ($errors = sqlsrv_errors() ) != null) {
          foreach( $errors as $error ) {
              echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
              echo "code: ".$error[ 'code']."<br />";
              echo "message: ".$error[ 'message']."<br />";
          }
      }
      echo date("m/d/Y h:i A");
    }
}?>
