<script>
//Flot Pie Chart
$(function() {
    var data43 = [<?php 
        $data = array();
        $data2 = array();
        $job_result = sqlsrv_query($NEI,"
            SELECT Sum(TicketD.Total) as Total, Job.Custom1 AS Supervisor
            FROM Job LEFT JOIN TicketD ON Job.ID = TicketD.Job
            WHERE Job.Type='2' AND TicketD.EDate >= '2017-03-30 00:00:00.000' AND TicketD.Total <= 24
            GROUP BY Job.Custom1;
        ;");
        if($job_result){
            $Jobs = array();
            while($Job = sqlsrv_fetch_array($job_result)){$Jobs[] = $Job;}
            foreach($Jobs as $Job){
                if($Job['Total'] == ''){continue;}
                $data[$Job['Supervisor']] = !isset($data[$Job['Supervisor']]) ? $Job['Total'] : $data[$Job['Supervisor']] + $Job['Total'];}
            foreach($data as $Supervisor=>$total){
                $data2[] = "{label: '{$Supervisor}',data:{$total}}";
            }
        }
        echo implode(",",$data2);?>];
    var plotObj3 = $.plot($("#flot-pie-chart-modernization-jobs-hours-by-supervisor"), data43, {
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