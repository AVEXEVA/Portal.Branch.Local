<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
if(isset($_SESSION[ 'User' ],$_SESSION[ 'Hash' ] ) ) {
    $result = $database->query(
        null,
        "SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",
        array(
            $_SESSION[ 'User' ],
            $_SESSION[ 'Hash' ]
        )
    );
    $Connection = sqlsrv_fetch_array($result);
    $User = $database->query(
        null,
        "SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID = ?",
        array(
            $_SESSION[ 'User' ]
        )
    );
    $User = sqlsrv_fetch_array($User);
    $result = $database->query(
        null,
        "   SELECT    Access,
                        Owner,
                        Group,
                        Other
            FROM    Privilege
            WHERE   User_ID = ?;",
        array(
            $_SESSION[ 'User' ]
        )
    );
    $Privileges = array();
    while($array2 = sqlsrv_fetch_array($result)){$Privileges[$array2['Access']] = $array2;}
    $Privileged = FALSE;
    if(isset($Privileges['Location'])
        && $Privileges[ 'Location' ][ 'Owner' ] >= 4
        && $Privileges[ 'Location' ][ 'Group' ] >= 4
        && $Privileges[ 'Location' ][ 'Other' ] >= 4){$Privileged = TRUE;}
    if(!isset($Connection[ 'ID' ])  || !$Privileged){
      ?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
        $r = $database->query(null,
            "SELECT TOP 1
                    Terr.ID   AS Territory_ID,
					Terr.Name AS Territory_Name
			FROM    nei.dbo.Terr
			WHERE   Terr.ID = ?
        ;",array($_GET['ID']));
        $Territory = sqlsrv_fetch_array($r);
        ?>
		
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>