<script type="text/javascript">
    <?php
        $r = $database->query(null,"
            SELECT OpenAR.*
            FROM   nei.dbo.OpenAR
                   LEFT JOIN nei.dbo.Invoice ON Invoice.Ref = OpenAR.Ref 
				   LEFT JOIN nei.dbo.Job     ON Invoice.Job = Job.ID
            WHERE  Job.ID = ?
        ;",array($_GET['ID']));
        $OpenAR = array();
        $Now = new DateTime();
        if($r){while($array = sqlsrv_fetch_array($r)){
            $Due = new DateTime($array['Due']);
            if($Now > $Due){
                $OpenAR[$array['Due']] += $array['Balance'];
            }
        }}
        $Dates = array();
        $Dates[0] = 0;
        $Dates[1] = 0;
        $Dates[2] = 0;
        $Dates[3] = 0;
        $Dates[4] = 0;
        if(count($OpenAR) > 0){
            $Interval         = new DateInterval("P30D");
            foreach($OpenAR as $Date=>$Balance){
                $Date = new DateTime($Date);
                $Now              = new DateTime();
                if(    $Now     > $Date && $Date >= $Now->sub($Interval)){ $Dates[0] += $Balance;}
                elseif($Now > $Date && $Date >= $Now->sub($Interval)){ $Dates[1] += $Balance;}
                elseif($Now > $Date && $Date >= $Now->sub($Interval)){ $Dates[2] += $Balance;}
                elseif($Now > $Date && $Date >= $Now->sub($Interval)){ $Dates[3] += $Balance;}
                else{                                                  $Dates[4] += $Balance;}
            }
        }
    ?>
        //******* 2012 Average Temperature - BAR CHART
        var data = [[0, <?php echo $Dates[0];?>],[1, <?php echo $Dates[1];?>],[2, <?php echo $Dates[2];?>],[3, <?php echo $Dates[3];?>],[4, <?php echo $Dates[4];?>]];
        var dataset = [{ label: "Overdue Balances", data: data, color: "#5482FF" }];
        var ticks = [[0, "0 to 30"], [1, "30 to 60"], [2, "60 to 90"], [3, "90 to 120"],[4, "120+"]];
 
        var options = {
            series: {
                bars: {
                    show: true
                }
            },
            bars: {
                align: "center",
                barWidth: 0.5
            },
            xaxis: {
                axisLabel: "Date Ranges",
                axisLabelUseCanvas: true,
                axisLabelFontSizePixels: 12,
                axisLabelFontFamily: 'Verdana, Arial',
                axisLabelPadding: 10,
                ticks: ticks
            },
            yaxis: {
                axisLabel: "Overdue Balance",
                axisLabelUseCanvas: true,
                axisLabelFontSizePixels: 12,
                axisLabelFontFamily: 'Verdana, Arial',
                axisLabelPadding: 3,
                tickFormatter : function(val, axis){
					return "$" + parseFloat(val).toLocaleString();
				},
            },
            legend: {
                noColumns: 0,
                labelBoxBorderColor: "#000000",
                position: "nw"
            },
            grid: {
                hoverable: true,
                borderWidth: 2,
                backgroundColor: { colors: ["#ffffff", "#EDF5FF"] }
            }
        };
 
        $(document).ready(function () {
            $.plot($("#flot-placeholder-open-ar-by-customer"), dataset, options);
        });
 
        function gd(year, month, day) {
            return new Date(year, month, day).getTime();
        }
 
        var previousPoint = null, previousLabel = null;
    </script>