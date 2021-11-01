<script>
//Flot Pie Chart
$(function() {
    var data43 = [<?php 
        $data = array();
        $data2 = array();
        $job_result = sqlsrv_query($NEI,"
            SELECT Loc.Zone AS Division, Sum(TicketD.Total) as Total
            FROM (TicketD LEFT JOIN nei.dbo.Job ON Job.ID = TicketD.Job) LEFT JOIN nei.dbo.Loc ON Job.Loc = Loc.Loc
            WHERE Job.Type='0' AND TicketD.EDate >= '2017-03-30 00:00:00.000' AND TicketD.Total <= 24
            GROUP BY Loc.Zone
        ;");
        if($job_result){
            $Jobs = array();
            while($Job = sqlsrv_fetch_array($job_result)){$Jobs[] = $Job;}
            foreach($Jobs as $Job){
                if($Job['Total'] == ''){continue;}
                $Division = '';
                switch($Job['Division']){
                    case 1:$Division = "Base";break;
                    case 2:$Division = "Division #1";break;
                    case 3:$Division = "Division #2";break;
                    case 4:$Division = "Division #4";break;
                    case 5:$Division = "Division #3";break;
                    case 6:$Division = "Repair";break;
                    default:continue;

                }
                $data[$Division] = !isset($data[$Division]) ? $Job['Total'] : $data[$Division] + $Job['Total'];}
            foreach($data as $Division=>$total){
                $data2[] = "{label: '{$Division}',data:{$total}}";
            }
        }
        echo implode(",",$data2);?>];
    var plotObj3 = $.plot($("#flot-pie-chart-maintenance-jobs-hours-by-division"), data43, {
        series: {
            pie: {
                show: true
            }
        },
        grid: {
            hoverable: true
        },
        tooltip: true,
        tooltipOpts: {
            content: "%p.0% of Jobs' Hours were Supervised by %s", // show percentages, rounding to 2 decimal places
            shifts: {
                x: 20,
                y: 0
            },
            defaultTheme: false
        }
    });

});
</script>