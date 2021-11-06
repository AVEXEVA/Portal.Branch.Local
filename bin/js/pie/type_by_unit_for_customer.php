<script>
//Flot Pie Chart
$(function() {
    <?php 
    $Vars = array();
    $r = $database->query(null,"
        SELECT 
            Elev.State AS Unit_State,
            Elev.Type AS Unit_Type
        FROM
            Elev
            LEFT JOIN nei.dbo.Loc ON Elev.Loc = Loc.Loc
        WHERE 
            Loc.Owner = '{$_GET['ID']}'
    ;");
    $Units = array();
    if($r){while($array = sqlsrv_fetch_array($r)){$Units[$array['Unit_Type']] = (isset($Units[$array['Unit_Type']])) ? $Units[$array['Unit_Type']] + 1 : 1;}}
    $data = array();
    if(count($Units) > 0){
        foreach($Units as $Unit=>$Total){if($Unit == ""){$Unit = "Unlisted";}$data[] = "{label: '{$Unit}', data:{$Total}}";}
    }
    ?>
    var data33 = [<?php echo implode(",",$data);?>];
    var plotObj = $.plot($("#flot-pie-chart-units-type"), data33, {
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