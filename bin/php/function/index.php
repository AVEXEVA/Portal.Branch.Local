<?php
/*
require( __dir__ . '/loading.php' );
require( __dir__ . '/error.php' );
require( __dir__ . '/success.php' );
require( __dir__ . '/load.php' );*/
/*
require(__dir__ . '/exists.php');
load('function/autoloader.php');
load('function/sql/index.php');
load('function/session/index.php');
load('function/finances/index.php');
load('function/directory/index.php');
load('function/differences/index.php');
load('function/detection/index.php');
load('function/debugging/index.php');
load('function/conversion/index.php');
load('function/architecture/index.php');*/
require( bin_php . 'function/check.php' );

function connection( $db, $dataserver, $User_ID, $Hash ){
    $result = $dataserver->query(
        $db,
        "   SELECT  * 
            FROM    Connection 
            WHERE   Connection.Connector = ? 
                    AND Connection.Hash = ?;",
        array(
            $User_ID,
            $Hash
        )
    );
    $Connection = sqlsrv_fetch_array( $result );
    /*GET User*/
    $result = $dataserver->query(
        $db,
        "   SELECT  *, 
                    Emp.fFirst  AS  First_Name, 
                    Emp.Last    AS  Last_Name,
                    Emp.fWork   AS  Field_ID
            FROM    Emp 
            WHERE   Emp.ID = ?;",
        array(
            $Connection[ 'Connector' ]
        )
    );
    $User = sqlsrv_fetch_array( $result );
    return is_array( $User ) && isset( $User[ 'ID' ] ) && is_numeric( $User[ 'ID' ] ) && $User[ 'ID' ] > 0;
}
function privileges( $db, $dataserver, $User_ID ){
    /*GET Privleges*/
    $result = $dataserver->query(
        $db,
        "   SELECT  Privilege.Access_Table, 
                    Privilege.User_Privilege, 
                    Privilege.Group_Privilege, 
                    Privilege.Other_Privilege
            FROM    Privilege
            WHERE   Privilege.User_ID = ?;",
        array(
            $User_ID
        )
    );
    $Privileges = array( );
    while( $Privilege = sqlsrv_fetch_array( $result ) ){ $Privileges[ $Privilege[ 'Access_Table' ] ] = $Privilege; }
    return is_array( $Privileges ) ? $Privileges : False;
}
function activity( $Database, $Server, $Connection ){
    $database->query(
        $Database, 
        "   INSERT INTO Portal.dbo.History( [Page], [Parameters], [Database], [Branch], [Branch_ID], [IP], [Agent] )
            VALUES( ?, ?, ?, ?, ?, ?, ? );",
        array( 
            $Server[ 'SCRIPT_NAME' ],
            json_encode( $_GET ),
            $Connection[ 'Database' ],
            $Connection[ 'Branch' ],
            $Connection[ 'Branch_ID' ],
            $Server[ 'REMOTE_ADDR' ],
            $Server[ 'HTTP_USER_AGENT' ]
        )
    );
}
function proper($string){return ucwords(strtolower($string));}
function utf8ize($d) {
    if (is_array($d)) {
        foreach ($d as $k => $v) {
            $d[$k] = utf8ize($v);
        }
    } else if (is_string ($d)) {
        return utf8_encode($d);
    }
    return $d;
}
function isMobile() {
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
}
function createHomeScreenOption($Icons = null,$My_Privileges,$From_Page,$To_Page,$Parameters = NULL,$Icon = NULL){
	$Icon = is_null($Icon) ? $To_Page : $Icon;
	if(isset($My_Privileges[proper($To_Page)]) && $My_Privileges[proper($To_Page)]['User_Privilege'] >= 4){
	?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='<?php echo $From_Page;?>-<?php echo strtolower($To_Page);?>.php?ID=<?php echo is_null($Parameters) ? $_GET['ID'] : $Parameters;?>'">
		<div class='nav-icon'><?php call_user_func_array(array(\singleton\fontawesome::getInstance( ), str_replace("-","_",$Icon)), array(3));?></div>
			<div class ='nav-text'><?php echo proper($To_Page);?></div> 
	</div><?php }
}
function fixArrayKey(&$arr)
{
	$arr=array_combine(array_map(function($str){return str_replace("_"," ",$str);},array_keys($arr)),array_values($arr));
	foreach($arr as $key=>$val)
	{
		if(is_array($val)) fixArrayKey($arr[$key]);
	}
}?>