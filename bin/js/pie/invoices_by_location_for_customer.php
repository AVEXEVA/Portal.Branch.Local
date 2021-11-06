<script>
//Flot Pie Chart
$(function() {
    <?php 
    $Vars = array();
        $r = $database->query(null,"
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
            $r = $database->query(null,"
                SELECT 
                    Invoice.Ref,
                    Loc.Tag AS Location_Name,
                    Invoice.fDate,
                    Invoice.Total
                FROM
                    Invoice
                    LEFT JOIN nei.dbo.Loc ON Invoice.Loc = Loc.Loc
                WHERE 
                    ({$SQL_Locations})
                    AND Invoice.fDate >= '{$SQL_Start_Date}' 
                    AND Invoice.fDate <= '{$SQL_End_Date}'
            ;");
            $Locations = array();
            if($r){while($array = sqlsrv_fetch_array($r)){$Locations[$array['Location_Name']] = (isset($Locations[$array['Location_Name']])) ? $Locations[$array['Location_Name']] + $array['Total'] : $array['Total'];}}
            $data42 = array();
            if(count($Locations) > 0){
                foreach($Locations as $Location=>$Total){$data42[] = "{label: '{$Location}', data:{$Total}}";}
            }
        }
    ?>
    var data42 = [<?php echo implode(",",$data42);?>];
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