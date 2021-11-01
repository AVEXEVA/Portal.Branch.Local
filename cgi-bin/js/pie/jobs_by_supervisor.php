<script>
//Flot Pie Chart
$(function() {
    var data42 = [<?php 
        $data = array();
        $job_result = sqlsrv_query($NEI,"
            SELECT Custom1 AS Supervisor
            FROM Job
            WHERE Job.Type='2';
        ;");
        if($job_result){
            $Jobs = array();
            while($Job = sqlsrv_fetch_array($job_result)){$Jobs[] = $Job;}
            foreach($Jobs as $Job){$data[$Job['Supervisor']] = !isset($data[$Job['Supervisor']]) ? 1 : $data[$Job['Supervisor']] + 1;}
            foreach($data as $Supervisor=>$total){
                $data[] = "{label: '{$Supervisor}',data:{$total}}";
            }
        }
        echo implode(",",$data);?>];
    var plotObj2 = $.plot($("#flot-pie-chart-locations"), data42, {
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
            content: "%p.0%, %s", // show percentages, rounding to 2 decimal places
            shifts: {
                x: 20,
                y: 0
            },
            defaultTheme: false
        }
    });

});
</script>