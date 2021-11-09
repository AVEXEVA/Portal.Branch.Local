<?php
session_start( [ 'read_and_close' => true ] );
require('index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = \singleton\database::getInstance( )->query(
        null,
    " SELECT *
      FROM Connection 
      WHERE Connector = ?
      AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $SQL_Result_Privileges = \singleton\database::getInstance( )->query(
        null,,
      " SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
        FROM   Privilege
        WHERE User_ID='{$_SESSION['User']}';"
    );
    $Privileges = array();
    if($SQL_Result_Privileges){while($Privilege = sqlsrv_fetch_array($SQL_Result_Privileges)){$Privileges[$Privilege['Access_Table']] = $Privilege;}}
    if(!isset($array['ID']) || !isset($_GET['Keyword']) || trim($_GET['Keyword']) == ''){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
        $data = array();
        $Keyword = addslashes($_GET['Keyword']);
        $Keywords = explode(" ",$Keyword);
        $Objects = array("Location","Job","Ticket","Proposal","Invoice","Unit","Customer");
        if(isset($Privileges['Job']) && (empty(array_intersect(array_map("strtolower",$Keywords),array_map("strtolower",$Objects))) || in_array("job",array_map("strtolower",$Keywords)))){
            $Job_ID = "Job.ID LIKE '%" . str_replace(" ","%' OR Job.ID LIKE '%",$Keyword) . "%'";
            $Job_fDesc = "Job.fDesc LIKE '%" . str_replace(" ","%' OR Job.fDesc LIKE '%",$Keyword) . "%'";
            $Job_Type = "JobType.Type LIKE '%" . str_replace(" ","%' OR JobType.Type LIKE '%",$Keyword) . "%'";
            $Job_fDate = "Job.fDate LIKE '%" . str_replace(" ","%' OR Job.fDate LIKE '%",$Keyword) . "%'";
            $Job_Status = "Job_Status.Status LIKE '%" . str_replace(" ","%' OR Job_Status.Status LIKE '%",$Keyword) . "%'";
            if($Privileges['Job']['User_Privilege'] >= 4 && $Privileges['Job']['Group_Privilege'] >= 4 && $Privileges['Job']['Other_Privilege'] >= 4){
                $SQL_Result_Jobs = \singleton\database::getInstance( )->query(
                    null,,
                  " SELECT DISTINCT
                        Job.ID                  AS  ID,
                        'Job'                   AS  Object,
                        Job.fDesc               AS  Name,
                        JobType.Type            AS  Description,
                        Loc.Tag                 AS  Other
                    FROM
                        Job
                        LEFT JOIN JobType       ON  Job.Type 		= JobType.ID
                        LEFT JOIN Job_Status    ON  Job.Status + 1  = Job_Status.ID
                        LEFT JOIN Loc           ON  Loc.Loc 		= Job.Loc
                    WHERE
                        {$Job_ID}
                       	/*OR {$Job_fDesc}
                        OR {$Job_Type}
                        OR {$Job_fDate}
                        OR {$Job_Status}*/
                ;");
                if($SQL_Result_Jobs){while($Job = sqlsrv_fetch_array($SQL_Result_Jobs)){$data[] = $Job;}}
            } else {
                $SQL_Jobs = array();
                if($Privileges['Job']['Group_Privilege'] >= 4){
                    $SQL_Result_Jobs = \singleton\database::getInstance( )->query(
                        null,
                    "   SELECT TicketO.Job AS Job
                        FROM   TicketO
                               LEFT JOIN Emp ON TicketO.fWork = Emp.fWork
                        WHERE  Emp.ID = '{$_SESSION['User']}'
                    ;");
                    while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$SQL_Jobs[] = "Job.ID='{$array['Job']}'";}
                    $r = $database->query(null,
                      " SELECT TicketD.Job AS Job
                        FROM   TicketD
                               LEFT JOIN Emp ON TicketD.fWork = Emp.fWork
                        WHERE  Emp.ID = '{$_SESSION['User']}'
                    ;");
                    if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$SQL_Jobs[] = "Job.ID='{$array['Job']}'";}}

                }
                if($Privileges['Job']['User_Privilege'] >= 4){
                    $r = \singleton\database::getInstance( )->query(
                        null,
                      " SELECT DISTINCT Job.ID AS Job
                        FROM   Job
                               LEFT JOIN Loc       ON Elev.Loc = Loc.Loc)
                               LEFT JOIN Route     ON Loc.Route = Route.ID)
                               LEFT JOIN Emp       ON Route.Mech = Emp.fWork
                        WHERE  Emp.ID = '{$_SESSION['User']}'
                    ;");
                    if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$SQL_Jobs[] = "Job.ID='{$array['Job']}'";}}
                }
                $SQL_Jobs = array_unique($SQL_Jobs);
                if(count($SQL_Jobs) > 0){
                    $SQL_Jobs = implode(' OR ',$SQL_Jobs);
                    $SQL_Result_Jobs = $database->query(null,
                      " SELECT DISTINCT
                               Job.ID                  AS  ID,
                               'Job'                   AS  Object,
                               Job.fDesc               AS  Name,
                               JobType.Type            AS  Description,
                               Loc.Tag                 AS  Other
                        FROM   Job
                               LEFT JOIN JobType       ON  Job.Type = JobType.ID)
                               LEFT JOIN Loc           ON  Job.Loc = Loc.Loc)
                               LEFT JOIN Job_Status    ON  Job.Status + 1 = Job_Status.ID
                        WHERE
                               Loc.Maint               =   1
                               AND ({$SQL_Jobs})
                               AND ({$Job_ID}
                                OR {$Job_fDesc}
                                OR {$Job_Type}
                                OR {$Job_fDate}
                                OR {$Job_Status})
                    ;");
                    if($SQL_Result_Jobs){while($Job = sqlsrv_fetch_array($SQL_Result_Jobs)){$data[] = $Job;}}
                }
            }
        }
        if(isset($Privileges['Customer']) && $Privileges['Customer']['User_Privilege'] >= 4 && $Privileges['Customer']['Group_Privilege'] >= 4 && $Privileges['Customer']['Other_Privilege'] >= 4 && (empty(array_intersect(array_map("strtolower",$Keywords),array_map("strtolower",$Objects))) || in_array("customer",array_map("strtolower",$Keywords)))){
            $SQL_Result_Customers = \singleton\database::getInstance( )->query(
                null,
              " SELECT DISTINCT
                    OwnerWithRol.ID         AS ID,
                    'Customer'              AS Object,
                    OwnerWithRol.Name       AS Name,
                    OwnerWithRol.Status     AS Description,
                    ''                      AS Other
                FROM
                    OwnerWithRol
                    LEFT JOIN Loc           ON OwnerWithRol.ID = Loc.Owner
                    LEFT JOIN Elev          ON Loc.Loc = Elev.Loc
                WHERE
                    OwnerWithRol.Name       LIKE '%{$Keyword}%'
                    OR OwnerWithRol.ID      LIKE '%{$Keyword}%'
            ;");
            if($SQL_Result_Customers){while($Customer = sqlsrv_fetch_array($SQL_Result_Customers)){$data[] = $Customer;}}
        }
        if(isset($Privileges['Ticket']) && (empty(array_intersect(array_map("strtolower",$Keywords),array_map("strtolower",$Objects))) || in_array("ticket",array_map("strtolower",$Keywords)))){
            $TicketO_ID = "TicketO.ID LIKE '%" . str_replace(" ","%' OR TicketO.ID LIKE '%",$Keyword) . "%'";
            $TicketO_fDesc = "TicketO.fDesc LIKE '%" . str_replace(" ","%' OR TicketO.fDesc LIKE '%",$Keyword) . "%'";
            $TicketD_ID = "TicketD.ID LIKE '%" . str_replace(" ","%' OR TicketD.ID LIKE '%",$Keyword) . "%'";
            $TicketD_fDesc = "TicketD.fDesc LIKE '%" . str_replace(" ","%' OR TicketD.fDesc LIKE '%",$Keyword) . "%'";
            $TicketD_DescRes = "TicketD.DescRes LIKE '%" . str_replace(" ","%' OR TicketD.DescRes LIKE '%",$Keyword) . "%'";
            $TicketDArchive_ID = "TicketDArchive.ID LIKE '%" . str_replace(" ","%' OR TicketDArchive.ID LIKE '%",$Keyword) . "%'";
            $TicketDArchive_fDesc = "TicketDArchive.fDesc LIKE '%" . str_replace(" ","%' OR TicketDArchive.fDesc LIKE '%",$Keyword) . "%'";
            $TicketDArchive_DescRes = "TicketDArchive.DescRes LIKE '%" . str_replace(" ","%' OR TicketDArchive.DescRes LIKE '%",$Keyword) . "%'";
            $Emp_fFirst = "Emp.fFirst LIKE '%" . str_replace(" ","%' OR Emp.fFirst LIKE '%",$Keyword) . "%'";
            $Emp_Last = "Emp.Last LIKE '%" . str_replace(" ","%' OR Emp.Last LIKE '%",$Keyword) . "%'";
            if($Privileges['Ticket']['User_Privilege'] >= 4 && $Privileges['Ticket']['Group_Privilege'] >= 4 && $Privileges['Ticket']['Other_Privilege'] >= 4){
            	$r = \singleton\database::getInstance( )->query(
                  null,
                "   SELECT TicketO.ID                  AS ID,
                           'Ticket'                    AS Object,
                           TicketO.fDesc               AS Name,
                           TickOStatus.Type            AS Description,
                           Loc.Tag                     AS Other
                    FROM   TicketO
                           LEFT JOIN Loc               ON TicketO.LID      = Loc.Loc
                           LEFT JOIN Job               ON TicketO.Job      = Job.ID
                           LEFT JOIN OwnerWithRol      ON TicketO.Owner    = OwnerWithRol.ID
                           LEFT JOIN JobType           ON Job.Type         = JobType.ID
                           LEFT JOIN Elev              ON TicketO.LElev    = Elev.ID
                           LEFT JOIN TickOStatus       ON TicketO.Assigned = TickOStatus.Ref
                           LEFT JOIN Emp               ON TicketO.fWork    = Emp.fWork
                    WHERE  {$TicketO_ID}
                           /*OR {$TicketO_fDesc}
                           OR {$Emp_fFirst}
                           OR {$Emp_Last}*/
                ;");
                $Tickets = array();
                if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
                $r = \singleton\database::getInstance( )->query(
                    null,
                    " SELECT TicketD.ID              AS ID,
                             'Ticket'                AS Object,
                             TicketD.fDesc           AS Name,
                             'Completed'             AS Description,
                             Loc.Tag                 AS Other,
						   'Completed'   		   AS Status
                    FROM   TicketD
                           LEFT JOIN Loc           ON  TicketD.Loc   = Loc.Loc
                           LEFT JOIN Job           ON  TicketD.Job   = Job.ID
                           LEFT JOIN OwnerWithRol  ON  Loc.Owner     = OwnerWithRol.ID
                           LEFT JOIN JobType       ON  Job.Type      = JobType.ID
                           LEFT JOIN Elev          ON  TicketD.Elev  = Elev.ID
                           LEFT JOIN Emp           ON  TicketD.fWork = Emp.fWork
                    WHERE  {$TicketD_ID}
                           /*OR {$TicketD_fDesc}
                           OR {$TicketD_DescRes}
                           OR {$Emp_fFirst}
                           OR {$Emp_Last}*/
                ;");
                if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
				$r = \singleton\database::getInstance( )->query(
            null,
            "SELECT
						TicketDArchive.ID       AS ID,
						'Ticket'                AS Object,
						TicketDArchive.fDesc    AS Name,
						'Completed'             AS Description,
						Loc.Tag                 AS Other,
						'Completed'				      AS Status
					FROM
						TicketDArchive
						LEFT JOIN Loc           ON  TicketDArchive.Loc = Loc.Loc
						LEFT JOIN Job           ON  TicketDArchive.Job = Job.ID
						LEFT JOIN OwnerWithRol  ON  Loc.Owner = OwnerWithRol.ID
						LEFT JOIN JobType       ON  Job.Type = JobType.ID
						LEFT JOIN Elev          ON  TicketDArchive.Elev = Elev.ID
						LEFT JOIN Emp           ON  TicketDArchive.fWork = Emp.fWork
					WHERE
						{$TicketDArchive_ID}
						/*OR {$TicketDArchive_fDesc}
						OR {$TicketDArchive_DescRes}
						OR {$Emp_fFirst}
						OR {$Emp_Last}*/
                ;");
                if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
            } elseif($Privileges['Ticket']['User_Privilege'] >= 4) {
                $r = \singleton\database::getInstance( )->query(
                    null,
                  " SELECT
                        TicketO.ID                  AS ID,
                        'Ticket'                    AS Object,
                        TicketO.fDesc               AS Name,
                        TickOStatus.Type            AS Description,
                        Loc.Tag                     AS Other
                    FROM
                        TicketO
                        LEFT JOIN Loc               ON TicketO.LID      = Loc.Loc
                        LEFT JOIN Job               ON TicketO.Job      = Job.ID
                        LEFT JOIN OwnerWithRol      ON TicketO.Owner    = OwnerWithRol.ID
                        LEFT JOIN JobType           ON Job.Type         = JobType.ID
                        LEFT JOIN Elev              ON TicketO.LElev    = Elev.ID
                        LEFT JOIN TickOStatus       ON TicketO.Assigned = TickOStatus.Ref
                        LEFT JOIN Emp               ON TicketO.fwork    = Emp.fWork
                    WHERE
                            TicketO.fWork           =   ?
                        AND ({$TicketO_ID}
                            OR {$TicketO_fDesc}
                            OR {$Emp_fFirst}
                            OR {$Emp_Last})
                ;",array($Employee_ID));
                $Tickets = array();
                if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){
                    if($array['ClearPR'] != 1){$array['ClearPR'] = 0;}
                    $Tickets[$array['ID']] = $array;}}
                $r = \singleton\database::getInstance( )->query(
                    null,
                  " SELECT
                        TicketD.ID              AS  ID,
                        'Ticket'                AS  Object,
                        TicketD.fDesc           AS  Name,
                        'Completed'             AS  Description,
                        Loc.Tag                 AS  Other
                    FROM
                        TicketD
                        LEFT JOIN Loc           ON  TicketD.Loc = Loc.Loc
                        LEFT JOIN Job           ON  TicketD.Job = Job.ID
                        LEFT JOIN OwnerWithRol  ON  Loc.Owner = OwnerWithRol.ID
                        LEFT JOIN JobType       ON  Job.Type = JobType.ID
                        LEFT JOIN Elev          ON  TicketD.Elev = Elev.ID
                        LEFT JOIN Emp           ON  TicketD.fWork = Emp.fWork
                    WHERE
                            TicketD.fWork           =  ?
                        AND ({$TicketD_ID}
                            OR {$TicketD_fDesc}
                            OR {$TicketD_DescRes}
                            OR {$Emp_fFirst}
                            OR {$Emp_Last})
                ;",array($Employee_ID));
                if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){
                    $Tickets[$array['ID']] = $array;
                    $Tickets[$array['ID']]['Status'] = "Completed";}}
                   $r = $database->query(null,
                    " SELECT
                        TicketDArchive.ID       AS  ID,
                        'Ticket'                AS  Object,
                        TicketDArchive.fDesc    AS  Name,
                        'Completed'             AS  Description,
                        Loc.Tag                 AS  Other
                    FROM
                        ((((()    TicketDArchive
                        LEFT JOIN Loc           ON  TicketDArchive.Loc = Loc.Loc)
                        LEFT JOIN Job           ON  TicketDArchive.Job = Job.ID)
                        LEFT JOIN OwnerWithRol  ON  Loc.Owner = OwnerWithRol.ID)
                        LEFT JOIN JobType       ON  Job.Type = JobType.ID)
                        LEFT JOIN Elev          ON  TicketDArchive.Elev = Elev.ID)
                        LEFT JOIN Emp           ON  TicketDArchive.fWork = Emp.fWork
                    WHERE
                            TicketDArchive.fWork            =   '{$Employee_ID}'
                        AND ({$TicketDArchive_ID}
                            OR {$TicketDArchive_fDesc}
                            OR {$TicketDArchive_DescRes}
                            OR {$Emp_fFirst}
                            OR {$Emp_Last})
                ;");
                if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
            }
        }
        if(isset($Privileges['Location']) && (empty(array_intersect(array_map("strtolower",$Keywords),array_map("strtolower",$Objects))) || in_array("location",array_map("strtolower",$Keywords)))){
            $Location_ID = "Loc.Loc LIKE '%" . str_replace(" ","%' OR Loc.Loc LIKE '%",$Keyword) . "%'";
            $Location_Tag = "Loc.Tag LIKE '%" . str_replace(" ","%' OR Loc.Tag LIKE '%",$Keyword) . "%'";
            $Location_Address = "Loc.Address LIKE '%" . str_replace(" ","%' OR Loc.Address LIKE '%",$Keyword) . "%'";
            $Location_City = "Loc.City LIKE '%" . str_replace(" ","%' OR Loc.City LIKE '%",$Keyword) . "%'";
            $Location_State = "Loc.State LIKE '%" . str_replace(" ","%' OR Loc.State LIKE '%",$Keyword) . "%'";
            $Location_Zip = "Loc.Zip LIKE '%" . str_replace(" ","%' OR Loc.Zip LIKE '%",$Keyword) . "%'";
            if($Privileges['Location']['User_Privilege'] >= 4 && $Privileges['Location']['Group_Privilege'] >= 4 && $Privileges['Location']['Other_Privilege'] >= 4){
                $SQL_Result_Locations = \singleton\database::getInstance( )->query(
                    null,
                  " SELECT DISTINCT
                        Loc.Loc     AS ID,
                        'Location'  AS Object,
                        Loc.Tag     AS Name,
                        Loc.Address AS Description,
                        Loc.Zone    AS Other
                    FROM
                        Loc
                    WHERE
                        {$Location_ID}
                        OR {$Location_Tag}
                        OR {$Location_Address}
                        /*OR {$Location_City}
                        OR {$Location_State}
                        OR {$Location_Zip}*/
                ;");
                if($SQL_Result_Locations){while($Location = sqlsrv_fetch_array($SQL_Result_Locations)){$data[] = $Location;}}
            } else {
                $SQL_Locations = array();
                if($Privileges['Location']['Group_Privilege'] >= 4){
                    $r = \singleton\database::getInstance( )->query(
                        null,
                      " SELECT
                            LID                 AS Location
                        FROM
                                        TicketO
                            LEFT JOIN   Emp     ON TicketO.fWork = Emp.fWork
                        WHERE
                            Emp.ID = '{$_SESSION['User']}'
                    ;");

                    while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$SQL_Locations[] = "Loc.Loc='{$array['Location']}'";}
                    $r = $database->query(null,
                      " SELECT
                            Loc                 AS Location
                        FROM
                                        TicketD
                            LEFT JOIN   Emp     ON TicketD.fWork = Emp.fWork
                        WHERE
                            Emp.ID = '{$_SESSION['User']}'
                    ;");

                    while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$SQL_Locations[] = "Loc.Loc='{$array['Location']}'";}
                }
                if($Privileges['Location']['User_Privilege'] >= 4){
                    $r = \singleton\database::getInstance( )->query(
                        null,
                      " SELECT Loc.Loc          AS Location
                        FROM
                            (Loc
                            LEFT JOIN .Route     ON Loc.Route = Route.ID)
                            LEFT JOIN Emp       ON Route.Mech = Emp.fWork
                        WHERE
                            Emp.ID = '{$_SESSION['User']}';");
                    while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$SQL_Locations[] = "Loc.Loc='{$array['Location']}'";}
                }
                $SQL_Locations = array_unique($SQL_Locations);
                if(count($SQL_Locations) > 0){
                    $SQL_Locations = implode(' OR ',$SQL_Locations);
                    $SQL_Result_Locations = \singleton\database::getInstance( )->query(
                        null,
                      " SELECT DISTINCT
                            Loc.Loc     AS ID,
                            'Location'  AS Object,
                            Loc.Tag     AS Name,
                            Loc.Address AS Description,
                            Loc.Zone    AS Other
                        FROM
                            Loc
                        WHERE
                            (   {$Location_ID}
                                OR {$Location_Tag}
                                OR {$Location_Address}
                                OR {$Location_City}
                                OR {$Location_State}
                                OR {$Location_Zip})
                            AND ({$SQL_Locations})
                    ;");
                    while($Location = sqlsrv_fetch_array($SQL_Result_Locations)){$data[] = $Location;}
                }
            }
        }
        if(isset($Privileges['Unit']) && (empty(array_intersect(array_map("strtolower",$Keywords),array_map("strtolower",$Objects))) || in_array("unit",array_map("strtolower",$Keywords)))){
            $Unit_ID = "Elev.ID LIKE '%" . str_replace(" ","%' OR Elev.ID LIKE '%",$Keyword) . "%'";
            $Unit_State = "Elev.State LIKE '%" . str_replace(" ","%' OR Elev.State LIKE '%",$Keyword) . "%'";
            $Unit_Label = "Elev.Unit LIKE '%" . str_replace(" ","%' OR Elev.Unit LIKE '%",$Keyword) . "%'";
            $Unit_Type = "Elev.Type LIKE '%" . str_replace(" ","%' OR Elev.Type LIKE '%",$Keyword) . "%'";
            $Unit_Loc = "Loc.Loc LIKE '%" . str_replace(" ","%' OR Loc.Loc LIKE '%",$Keyword) . "%'";
            $Unit_Tag = "Loc.Tag LIKE '%" . str_replace(" ","%' OR Loc.Tag LIKE '%",$Keyword) . "%'";
            if($Privileges['Unit']['User_Privilege'] > 4 && $Privileges['Unit']['Group_Privilege'] > 4 && $Privileges['Unit']['Other_Privilege'] > 4){
                $SQL_Result_Units = \singleton\database::getInstance( )->query(
                    null,
                  " SELECT DISTINCT
                        Elev.ID         AS  ID,
                        'Unit'          AS  Object,
                        CASE
                            WHEN Elev.State = ''
                                THEN Elev.Unit
                                ELSE Elev.State
                        END
                                        AS Name,
                        Elev.Type       AS  Description,
                        Loc.Tag         AS  Other
                    FROM
                        (Elev
                        LEFT JOIN .Loc   ON  Elev.Loc = Loc.Loc)
                        LEFT JOIN .OwnerWithRol ON Loc.Owner = OwnerWithRol.ID
                    WHERE
                        {$Unit_ID}
                        OR {$Unit_State}
                        /*OR {$Unit_Label}
                        OR {$Unit_Type}
                        OR {$Unit_Loc}
                        OR {$Unit_Tag}*/
                ;");
                if($SQL_Result_Units){while($Unit = sqlsrv_fetch_array($SQL_Result_Units)){$data[] = $Unit;}}
            } else {
                $SQL_Units = array();
                if($Privileges['Unit']['Group_Privilege'] >= 4){
                    $r = \singleton\database::getInstance( )->query(
                        null,
                      " SELECT
                            LElev               AS Unit
                        FROM
                                        TicketO
                            LEFT JOIN   Emp     ON TicketO.fWork = Emp.fWork
                        WHERE
                            Emp.ID = '{$_SESSION['User']}'
                    ;");
                    if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$SQL_Units[] = "Elev.ID='{$array['Unit']}'";}}
                    $r = \singleton\database::getInstance( )->query(
                        null,
                      " SELECT
                            Elev                AS Unit
                        FROM
                                        TicketD
                            LEFT JOIN   Emp     ON TicketD.fWork = Emp.fWork
                        WHERE
                            Emp.ID = '{$_SESSION['User']}'
                    ;");

                    if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$SQL_Units[] = "Elev.ID='{$array['Unit']}'";}}
                }
                if($Privileges['Unit']['User_Privilege'] >= 4){
                    $r = \singleton\database::getInstance( )->query(
                        null,
                      " SELECT Elev.ID          AS Unit
                        FROM
                            ((Elev
                            LEFT JOIN Loc       ON Elev.Loc = Loc.Loc)
                            LEFT JOIN Route     ON Loc.Route = Route.ID)
                            LEFT JOIN Emp       ON Route.Mech = Emp.fWork
                        WHERE
                            Emp.ID = '{$_SESSION['User']}'
                    ;");
                    if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$SQL_Units[] = "Elev.ID='{$array['Unit']}'";}}
                }
                $SQL_Units = array_unique($SQL_Units);
                if(count($SQL_Units) > 0){
                    $SQL_Units = implode(' OR ',$SQL_Units);
                    $SQL_Result_Units = \singleton\database::getInstance( )->query(
                        null,
                      " SELECT DISTINCT
                            Elev.ID                 AS  ID,
                            'Unit'                  AS  Object,
                            CASE
                                WHEN Elev.State = ''
                                    THEN Elev.Unit
                                    ELSE Elev.State
                            END
                                                    AS  Name,
                            Elev.Type               AS  Description,
                            Loc.Tag                 AS  Other
                        FROM
                            (Elev
                            LEFT JOIN .Loc           ON  Elev.Loc = Loc.Loc)
                            LEFT JOIN .OwnerWithRol  ON Loc.Owner = OwnerWithRol.ID
                        WHERE
                            (   {$Unit_ID}
                                OR {$Unit_State}
                                OR {$Unit_Label}
                                OR {$Unit_Type}
                                OR {$Unit_Loc}
                                OR {$Unit_Tag})
                            AND {$SQL_Units}
                    ;");
                    if($SQL_Result_Units){while($Unit = sqlsrv_fetch_array($SQL_Result_Units)){$data[] = $Unit;}}
                }
            }
        }
        if(isset($Privileges['Invoice']) && $Privileges['Invoice']['User_Privilege'] >= 4 && $Privileges['Invoice']['Group_Privilege'] >= 4 && $Privileges['Invoice']['Other_Privilege'] >= 4 && (empty(array_intersect(array_map("strtolower",$Keywords),array_map("strtolower",$Objects))) || in_array("invoice",array_map("strtolower",$Keywords)))){
            $Invoice_Ref = "Invoice.Ref LIKE '%" . str_replace(" ","%' OR Invoice.Ref LIKE '%",$Keyword) . "%'";
            $Invoice_fDesc = "Invoice.fDesc LIKE '%" . str_replace(" ","%' OR Invoice.fDesc LIKE '%",$Keyword) . "%'";
            $Invoice_Total = "Invoice.Total LIKE '%" . str_replace(" ","%' OR Invoice.Total LIKE '%",$Keyword) . "%'";
            $SQL_Result_Invoices = \singleton\database::getInstance( )->query(
                null,
              " SELECT
                    Invoice.Ref         AS  ID,
                    'Invoice'           AS  Object,
                    Invoice.fDesc       AS  Name,
                    Invoice.Total       AS  Description,
                    OwnerWithRol.Name   AS  Other
                FROM
                    ((Invoice
                    LEFT JOIN .Loc   ON  Invoice.Loc = Loc.Loc)
                    LEFT JOIN .Job   ON  Invoice.Job = Job.ID)
                    LEFT JOIN .OwnerWithRol ON OwnerWithRol.ID = Loc.Owner
                WHERE
                    {$Invoice_Ref}
                    /*OR {$Invoice_fDesc}
                    OR {$Invoice_Total}*/
            ;");
            if($SQL_Result_Invoices){while($Invoice = sqlsrv_fetch_array($SQL_Result_Invoices)){$data[] = $Invoice;}}
        }
        if(isset($Privileges['Proposal']) && $Privileges['Proposal']['User_Privilege'] >= 4 && $Privileges['Proposal']['Group_Privilege'] >= 4 && $Privileges['Proposal']['Other_Privilege'] >= 4 && (empty(array_intersect(array_map("strtolower",$Keywords),array_map("strtolower",$Objects))) || in_array("proposal",array_map("strtolower",$Keywords)))){
            $Proposal_ID = "Estimate.ID LIKE '%" . str_replace(" ","%' OR Estimate.ID LIKE '%",$Keyword) . "%'";
            $Proposal_Name = "Estimate.Name LIKE '%" . str_replace(" ","%' OR Estimate.Name LIKE '%",$Keyword) . "%'";
            $Proposal_fDesc = "Estimate.fDesc LIKE '%" . str_replace(" ","%' OR Estimate.fDesc LIKE '%",$Keyword) . "%'";
            $Proposal_fDate = "Estimate.fDate LIKE '%" . str_replace(" ","%' OR Estimate.fDate LIKE '%",$Keyword) . "%'";
            $Proposal_Cost = "Estimate.Cost LIKE '%" . str_replace(" ","%' OR Estimate.Cost LIKE '%",$Keyword) . "%'";
            $Proposal_Price = "Estimate.Price LIKE '%" . str_replace(" ","%' OR Estimate.Price LIKE '%",$Keyword) . "%'";
            $SQL_Result_Proposals = $database->query(null,
              " SELECT
                    Estimate.ID             AS  ID,
                    'Proposal'              AS  'Object',
                    Estimate.Name           AS  Name,
                    Estimate.Cost           AS  Description,
                    OwnerWithRol.Name       AS  Other
                FROM
                    (Estimate
                    LEFT JOIN .Loc           ON  Estimate.LocID = Loc.Loc)
                    LEFT JOIN .OwnerWithRol  ON  OwnerWithRol.ID = Loc.Owner
                WHERE
                    {$Proposal_ID}
                    /*OR {$Proposal_Name}
                    OR {$Proposal_fDesc}
                    OR {$Proposal_fDate}
                    OR {$Proposal_Cost}
                    OR {$Proposal_Price}*/
            ");
            if($SQL_Result_Proposals){while($Proposal = sqlsrv_fetch_array($SQL_Result_Proposals)){$data[] = $Proposal;}}
        }
        print json_encode(array('data'=>$data));
    }
}
