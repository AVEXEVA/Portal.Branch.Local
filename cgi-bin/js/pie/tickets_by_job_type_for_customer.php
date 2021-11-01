<script>
//Flot Pie Chart
$(function() {
    <?php 
    $Vars = array();
    $r = sqlsrv_query($NEI,"
        SELECT 
            TicketD.Total AS Total,
            JobType.Type AS Job_Type
        FROM
            (TicketD
            LEFT JOIN nei.dbo.Job ON TicketD.Job = Job.ID)
            LEFT JOIN nei.dbo.JobType ON Job.Type = JobType.ID
        WHERE 
            Job.Owner = '{$_GET['ID']}'
    ;");
    $Types = array();
    if($r){while($array = sqlsrv_fetch_array($r)){$Types[$array['Job_Type']] = (isset($Types[$array['Job_Type']])) ? $Types[$array['Job_Type']] + $array['Total'] : $array['Total'];}}
    $data = array();
    if(count($Types) > 0){
        foreach($Types as $Type=>$Total){$data[] = "{label: '{$Type}', data:{$Total}}";}
    }
    ?>
    var data222 = [<?php echo implode(",",$data);?>];
    var plotObj = $.plot($("#flot-pie-chart-ticket-jobs"), data222, {
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