<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( ); 
    require( '/var/www/nouveautexas.com/html/portal/cgi-bin/php/index.php' );
}
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    //Connection
    $result = sqlsrv_query(
        $NEI,
        "   SELECT  * 
            FROM    Connection 
            WHERE       Connector = ? 
                    AND Hash = ?;",
        array(
            $_SESSION['User'],
            $_SESSION['Hash']
        )
    );
    $Connection = sqlsrv_fetch_array($result);
    //User
    $result = sqlsrv_query(
        $NEI,
        "   SELECT  *, 
                    fFirst AS First_Name, 
                    Last as Last_Name 
            FROM    Emp 
            WHERE   ID= ?;",
        array(
            $_SESSION[ 'User' ]
        )
    );
    $User   = sqlsrv_fetch_array( $result );
    //Privileges
    $result = sqlsrv_query($NEI,
        "   SELECT  Privilege.*
            FROM    Privilege
            WHERE   Privilege.User_ID = ?;",
        array(
            $_SESSION[ 'User' ]
        )
    );
    $Privileges = array();
    $Privileged = false;
    while( $Privilege = sqlsrv_fetch_array( $result ) ){ $Privileges[ $Privilege[ 'Access_Table' ] ] = $Privilege; }
    if(     isset($Privileges['Customer']) 
        &&  $Privileges[ 'Customer' ][ 'User_Privilege' ]  >= 4 
        &&  $Privileges[ 'Customer' ][ 'Group_Privilege' ] >= 4 
        &&  $Privileges[ 'Customer' ][ 'Other_Privilege' ] >= 4){
                $Privileged = true;}
    if(     !isset($Connection['ID'])  
        ||  !is_numeric($_GET['ID']) 
        || !$Privileged 
    ){ ?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
        sqlsrv_query(
            $NEI,
            "   INSERT INTO Activity( [User], [Date], [Page] ) VALUES( ?, ?, ? );",
            array(
                $_SESSION['User'],
                date("Y-m-d H:i:s"), 
                "customer.php"
            )
        );
        $result = sqlsrv_query(
            $NEI,
            "   SELECT  Customer.*                    
                FROM    (
                            SELECT  Owner.ID    AS ID,
                                    Rol.Name    AS Name,
                                    Rol.Address AS Street,
                                    Rol.City    AS City,
                                    Rol.State   AS State, 
                                    Rol.Zip     AS Zip,
                                    Owner.Status  AS Status,
                                    Rol.Website AS Website
                            FROM    Owner 
                                    LEFT JOIN Rol ON Owner.Rol = Rol.ID
                    ) AS Customer
                WHERE   Customer.ID = ?;",
            array(
                $_GET['ID']
            )
        );
        $Customer = sqlsrv_fetch_array($result);
?><div class="panel panel-primary">
    <style>
        div.column {display:inline-block;vertical-align:top;}
        div.label1 {display:inline-block;font-weight:bold;width:150px;vertical-align:top;}
        div.data {display:inline-block;width:300px;vertical-align:top;}
        .border-seperate {border-bottom:3px solid #333333;}
    </style>
    <div class='panel-body white-background BankGothic shadow'>
        <table id='Table_Required_Maintenance' class='display' cellspacing='0' width='100%'>
            <thead>
                <th>ID</th>
                <th>State</th>
                <th>Last Maintained</th>
                <th>Maintenance Mechanic</th>
            </thead>
        </table>    
    </div>
    <script>
        var Table_Required_Maintenance = $('#Table_Required_Maintenance').DataTable( {
            "ajax": {
                "url":"cgi-bin/php/reports/Maintenances_by_Customer.php?ID=<?php echo $_GET['ID'];?>",
                "dataSrc":function(json){if(!json.data){json.data = [];}return json.data;}
            },
            "columns": [
                { 
                    "data": "ID", 
                    "className" : "hidden" 
                },{ 
                    "data": "State"
                },{ 
                    "data": "Last_Date",
                    render: function(data){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}
                },{ 
                    "data": "Route"
                }
            ],
            "order": [[1, 'asc']],
            "language":{"loadingRecords":""},
            //"paging":false,
            "searching":false,
            "info":false,
            "lengthMenu":[[10,25,50,100,500,-1,0],[10,25,50,100,500,"All","None"]],
            "initComplete":function(){},
            "paging":false,
            "searching":false
        } );
    </script>    
</div>

<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>