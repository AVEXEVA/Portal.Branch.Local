<script>
//Flot Pie Chart
$(function() {
    var data42 = [<?php 
        $data = array();
        $data2 = array();
        $job_result = sqlsrv_query($NEI,"
            SELECT Zone.Name as Division
            FROM (Job LEFT JOIN nei.dbo.Loc ON Job.Loc = Loc.Loc) LEFT JOIN nei.dbo.Zone ON Loc.Zone = Zone.ID
            WHERE Job.Type='0';
        ;");
        if($job_result){
            $Jobs = array();
            while($Job = sqlsrv_fetch_array($job_result)){$Jobs[] = $Job;}
            foreach($Jobs as $Job){$data[$Job['Division']] = !isset($data[$Job['Division']]) ? 1 : $data[$Job['Division']] + 1;}
            foreach($data as $Division=>$total){
                $data2[] = "{label: '{$Division}',data:{$total}}";
            }
        }
        echo implode(",",$data2);?>];
    var plotObj2 = $.plot($("#flot-pie-chart-maintenance-jobs-by-division"), data42, {
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