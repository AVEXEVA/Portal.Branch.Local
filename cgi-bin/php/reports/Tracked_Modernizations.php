<?php 
session_start();
require('index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $r = sqlsrv_query($Portal,"
        SELECT User_Privilege, Group_Privilege, Other_Privilege
        FROM   Portal.dbo.Privilege
        WHERE User_ID = ? AND Access_Table='Job'
    ;",array($_SESSION['User']));
    $My_Privileges = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    if(!isset($array['ID']) || !is_array($My_Privileges)){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
        $data = array();
        if($My_Privileges['User_Privilege'] >= 4 && $My_Privileges['Group_Privilege'] >= 4 && $My_Privileges['Other_Privilege'] >= 4){
            $r = sqlsrv_query($NEI,"
                SELECT Modernization.*,
                       Modernization.ID                    AS  ID,
                       Job.ID                              AS  Job,
                       Job.fDesc                           AS  Name,
                       Loc.Tag                             AS  Location,
                       Emp.fFirst + ' ' + Emp.Last         AS  Supervisor,
                       Modernization.EBN                   AS  EBN,
                       Modernization.Date_Removed          AS  Removed,
                       Modernization.Actual_Return         AS  Returned,
                       (SELECT Sum(TicketD.Total) 
					    FROM   nei.dbo.TicketD 
						WHERE  TicketD.Job = Job.ID 
						       AND TicketD.Elev = Elev.ID
						)                                  AS Total_Hours,
                       Modernization.Budget_Hours                                                                           AS     Budgeted_Hours,
                       (Modernization.Budget_Hours - 
					    (SELECT Sum(TicketD.Total) 
						 FROM nei.dbo.TicketD 
						 WHERE TicketD.Job = Job.ID 
						       AND TicketD.Elev = Elev.ID)
						)                                  AS  Balance,
                       Elev.State                          AS  Unit,
                       Elev.ID                             AS  Unit_ID
                FROM 
                    Portal.dbo.Modernization
                    LEFT JOIN nei.dbo.Emp     ON  Modernization.Supervisor = Emp.ID
                    LEFT JOIN nei.dbo.Job     ON  Modernization.Job        = Job.ID
                    LEFT JOIN nei.dbo.JobType ON  Job.Type                 = JobType.ID
                    LEFT JOIN nei.dbo.Loc     ON  Job.Loc                  = Loc.Loc
                    LEFT JOIN nei.dbo.Elev    ON  Elev.ID                  = Modernization.Unit
            ;");
            function getHolidaysThisYear($Holidays = array()){
                $oneDayDateInterval = new DateInterval('P1D');
                $MartinLutherKing = new DateTime(date("Y")."-01-15");
                while($MartinLutherKing->format("l") != "Monday"){$MartinLutherKing->add($oneDayDateInterval);}
                $WashingtonBirthday = new DateTime(date("Y")."-02-15");
                while($WashingtonBirthday->format("l") != "Monday"){$WashingtonBirthday->add($oneDayDateInterval);}
                $MemorialDay = new DateTime(date("Y")."-05-25");
                while($MemorialDay->format("l") != "Monday"){$MemorialDay->add($oneDayDateInterval);}
                $LaborDay = new DateTime(date("Y")."-09-01");
                while($LaborDay->format("l") != "Monday"){$LaborDay->add($oneDayDateInterval);}
                $ColombusDay = new DateTime(date("Y")."-10-08");
                while($ColombusDay->format("l") != "Monday"){$ColombusDay->add($oneDayDateInterval);}
                $ThanksGiving = new DateTime(date("Y")."-11-22");
                while($ThanksGiving->format("l") != "Thursday"){$ThanksGiving->add($oneDayDateInterval);}
                $Holidays = array_merge($Holidays,array(date("Y") . "-01-01",date("Y") . "-06-04",date("Y") . "-11-11",date("Y"). "-12-25",$MartinLutherKing->format("Y-m-d"),$WashingtonBirthday->format("Y-m-d"),$MemorialDay->format("Y-m-d"),$LaborDay->format("Y-m-d"),$ColombusDay->format("Y-m-d"),$ThanksGiving->format("Y-m-d")));
                return $Holidays;
            }
            function projectionDate($Date, $Budget_Hours, $Workforce_Hours = 8, $Holidays = array()){
              $oneDayDateInterval = new DateInterval('P1D');
              while($Budget_Hours > $Workforce_Hours || in_array($Date->format('Y-m-d'),$Holidays)){
                if($Date->format("N") < 6 && !in_array($Date->format('Y-m-d'), $Holidays)){
                  $Budget_Hours -= $Workforce_Hours;
                }
                $Date = $Date->add($oneDayDateInterval);
              }
              return $Date->format("Y-m-d");
            }
            if($r){
                while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){
                    $r2 = sqlsrv_query($Portal,"
                        SELECT   Mod_Tracker.Time_Stamp, Mod_Status.Title
                        FROM     Mod_Tracker
                                 LEFT JOIN Mod_Status ON Mod_Tracker.Status = Mod_Status.ID
                        WHERE    Mod_Tracker.Modernization = ?
                        ORDER BY 1 DESC
					;",array($array['ID']));
                    if($r2){$array['Status'] = sqlsrv_fetch_array($r2)['Title'];}
                    if($array['Removed'] == '1900-01-01' || $array['Removed'] == ''){
                        $array['Removed'] = '';
                        $array['Projection'] = '';
                    }  elseif($array['Budgeted_Hours'] > 0) {

                        $startDate = strlen($array['Removed']) > 0 ? substr($array['Removed'],0,10) : '';
                        $Budget_Hours = $array['Budgeted_Hours'];
                        if($startDate == '1900-01-01' || $Budget_Hours == '' || $Budget_Hours == 0 || $startDate == ''){}
                        else {
                           $array['Projection'] = $Budget_Hours < 100000 ? projectionDate(new DateTime($startDate),$array['Budgeted_Hours'],8,getHolidaysThisYear()) : "Unavailable";
                        }
                    } else {
                        $array['Projection'] = $array['Removed'];
                    }
                    if($array['Returned'] == '1900-01-01'){$array['Returned'] = '';}
                    $data[] = $array;
				}
            }
        }
        print json_encode(array('data'=>$data));	
    }
}