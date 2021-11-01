<script>
//Flot Pie Chart
$(function() {
    <?php 
    $Vars = array();
        $r = sqlsrv_query($NEI,"
            SELECT
                Loc.Loc
            FROM 
                Loc
            WHERE
                Loc.Owner = '{$_GET['ID']}'
        ;");
        $Locations = array();
        if($r){while($array = sqlsrv_fetch_array($r)){$Locations[] = "Invoice.Loc='{$array['Loc']}'";}}
        if(count($Locations) > 0){
            $SQL_Locations = implode(" OR ",$Locations);
            $SQL_Start_Date = date('Y-01-01 00:00:00.000');
            $SQL_End_Date = date('Y-m-t 23:59:59.999');
            $r = sqlsrv_query($NEI,"
                SELECT 
                    Invoice.Ref,
                    Invoice.fDate,
                    Invoice.Total,
                    JobType.Type as Job_Type
                FROM
                    (Invoice
                    LEFT JOIN nei.dbo.Job ON Invoice.Job = Job.ID)
                    LEFT JOIN nei.dbo.JobType ON Job.Type = JobType.ID
                WHERE 
                    ({$SQL_Locations})
                    AND Invoice.fDate >= '{$SQL_Start_Date}'
                    AND Invoice.fDate <= '{$SQL_End_Date}'
            ;");
            $Types = array();
            if($r){while($array = sqlsrv_fetch_array($r)){$Types[$array['Job_Type']] = (isset($Types[$array['Job_Type']])) ? $Types[$array['Job_Type']] + $array['Total'] : $array['Total'];}}
            $data = array();
            if(count($Types) > 0){
                foreach($Types as $Type=>$Total){$data[] = "{label: '{$Type}', data:{$Total}}";}
            }
        }
    ?>
    var data3 = [<?php echo implode(",",$data);?>];
    var plotObj = $.plot($("#flot-pie-chart"), data3, {
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