<script>
//Flot Pie Chart
$(function() {
    var data42 = [<?php 
        $data = array();
        $data2 = array();
        $job_result = $database->query(null,"
            SELECT Custom1 AS Supervisor
            FROM Job
            WHERE Job.Type = '8';
        ;");
        if($job_result){
            $Jobs = array();
            while($Job = sqlsrv_fetch_array($job_result)){$Jobs[] = $Job;}
            foreach($Jobs as $Job){$data[$Job['Supervisor']] = !isset($data[$Job['Supervisor']]) ? 1 : $data[$Job['Supervisor']] + 1;}
            foreach($data as $Supervisor=>$total){
                $data2[] = "{label: '{$Supervisor}',data:{$total}}";
            }
        }
        echo implode(",",$data2);?>];
    var plotObj2 = $.plot($("#flot-pie-chart-testing-jobs-by-supervisor"), data42, {
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
            content: "%p.0% of Jobs are/were Supervised by %s", // show percentages, rounding to 2 decimal places
            shifts: {
                x: 20,
                y: 0
            },
            defaultTheme: false
        }
    });

});
</script>