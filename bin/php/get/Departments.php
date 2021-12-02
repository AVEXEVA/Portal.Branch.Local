<?php
session_start( [ 'read_and_close' => true ] );
require('index.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $result = \singleton\database::getInstance( )->query(null,
      " SELECT *
        FROM   Connection
        WHERE  Connection.Connector = ?
               AND Connection.Hash = ?
    ;", array($_SESSION['User'],$_SESSION['Hash']));
    $Connection = sqlsrv_fetch_array($result);
    $User    = \singleton\database::getInstance( )->query(null,
      " SELECT Emp.*,
               Emp.fFirst AS First_Name,
               Emp.Last   AS Last_Name
        FROM   Emp
        WHERE  Emp.ID = ?
    ;", array($_SESSION[ 'User' ] ) );
    $User = sqlsrv_fetch_array($User);
    $Field = ($User['Field'] == 1 && $User['Title'] != "OFFICE") ? True : False;
    $result = \singleton\database::getInstance( )->query(
      'Portal',
      "   SELECT  [Privilege].[Access],
                  [Privilege].[Owner],
                  [Privilege].[Group],
                  [Privilege].[Other]
        FROM      dbo.[Privilege]
        WHERE     Privilege.[User] = ?;",
      array(
        $_SESSION[ 'Connection' ][ 'User' ]
      )
    );
    $Privileges = array();
    while($array2 = sqlsrv_fetch_array($result)){$Privileges[$array2['Access_Table']] = $array2;}
    $Privileged = False;
    if( isset($Privleges['Department'])
	  	&& $Privileges['Department']['User_Privilege'] >= 4){
      && $Privileges['Department']['Group_Privilege'] >= 4){
      && $Privileges['Department']['Other_Privilege'] >= 4){
            $Privileged = True;}
    if(!isset($Connection['ID'])  || !is_numeric($_GET['ID']) || !$Privileged){print json_encode(array('data'=>array()));}
    else {
        $result = \singleton\database::getInstance( )->query(null,
        "  SELECT tblWork.ID, tblWork.Super AS Department
           FROM nei.dbo.tblWork;");
        $Departments = array();
        if($result){
            while($Record = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)){
                if(!is_null($Record['Department']) && trim($Record['Department']) != ''){ $Departments[] = ucfirst(strtolower($Record['Department'])); }
                else {continue;}}
            $Departments = array_unique($Departments, SORT_REGULAR);
        }
        echo json_encode($Departments);
    }
}?>
