<script>
//Flot Pie Chart
$(function() {
    <?php 
    $Vars = array();
    $r = $database->query(null,"
        SELECT 
            TicketD.ID,
            TicketD.EDate,
            TicketD.Total,
            Elev.State AS Unit_State
        FROM
            TicketD
            LEFT JOIN nei.dbo.Elev ON TicketD.Elev = Elev.ID
        WHERE 
            TicketD.Loc = '{$_GET['ID']}'
    ;");
    $Units = array();
    if($r){while($array = sqlsrv_fetch_array($r)){$Units[$array['Unit_State']] = (isset($Units[$array['Unit_State']])) ? $Units[$array['Unit_State']] + $array['Total'] : $array['Total'];}}
    $data = array();
    if(count($Units) > 0){
        foreach($Units as $Unit=>$Total){if($Unit == ""){$Unit = "Unlisted";}$data[] = "{label: '{$Unit}', data:{$Total}}";}
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