<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [ 'read_and_close' => true ] ); 
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/bin/php/index.php' );
}
if( isset( $_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
    $r = $database->query(
        null,
        "   SELECT  *
            FROM    Connection
            WHERE       Connection.Connector = ?
                    AND Connection.Hash = ?;",
        array(
          $_SESSION[ 'User' ],
          $_SESSION[ 'Hash' ]
        )
      );
    $Connection = sqlsrv_fetch_array( $r );
    $User = $database->query(
        null,
        "   SELECT  Emp.*,
                    Emp.fFirst AS First_Name,
                    Emp.Last   AS Last_Name
            FROM    Emp
            WHERE   Emp.ID = ?;",
        array(
          $_SESSION[ 'User' ]
        )
    );
    $User = sqlsrv_fetch_array( $User );
    $r = $database->query(
        null,
        "   SELECT  Privilege.Access_Table,
                    Privilege.User_Privilege,
                    Privilege.Group_Privilege,
                    Privilege.Other_Privilege
            FROM    Privilege
            WHERE   Privilege.User_ID = ?;",
        array( 
          $_SESSION[ 'User' ] 
        ) 
    );
    $Privileges = array();
    while( $Privilege = sqlsrv_fetch_array( $r ) ){ $Privileges[ $Privilege[ 'Access_Table' ] ] = $Privilege; }
    $Privileged = False;
    if( isset( $Privileges[ 'Invoice' ] )
        && $Privileges[ 'Invoice' ][ 'Other_Privilege' ]  >= 4
    ){ $Privileged = True; }
    if(!isset($Connection['ID']) || !$Privileged){print json_encode(array('data'=>array()));}
    else {
        $_GET['rand'] = rand( 0, 999999 );
?>
<script>
function gd(year, month, day) {return new Date(year, month - 1, day).getTime();}
var job_profit_data_<?php echo $_GET['rand'];?> = [
	<?php
	$data = array();
	$Jobs = array();
	$dates = array('2017-03-30 00:00:00.000' => 0.00);
	$totals = array();
	$r = $database->query(null,
	" SELECT   Invoice.Amount AS Amount,
				 		Invoice.fDate  AS fDate
		FROM     Invoice
							LEFT JOIN Job ON Invoice.Job = Job.ID
		WHERE    Invoice.Loc       =  ?
				 AND Invoice.fDate >= '2017-03-30 00:00:00.000'
				 AND (Job.Type <> 2 OR (
					 	Job.Type = 2
						AND Job.Status <> 0
						AND Job.fDate >= '2017-03-30 00:00:00.000'
					 ))
		ORDER BY Invoice.fDate ASC
	;",array($_GET['ID']));
	if($r){
		$i = 0;
		$row_count = sqlsrv_num_rows($r);
		while($i < $row_count){
			$array = sqlsrv_fetch_array($r);
			$date = substr($array['fDate'],0,10);
			$dates[$date] = (isset($dates[$date])) ? $dates[$date] + ($array['Amount']) : $array['Amount'];
			$i++;
		}
	}
	ksort($dates);
	$r = $database->query(null,
	" SELECT JobI.Amount AS Amount,
			   JobI.fDate as fDate
		FROM   Loc 
			   LEFT JOIN Job  ON Loc.Loc = Job.Loc
			   LEFT JOIN JobI ON Job.ID  = JobI.Job
		WHERE  Loc.Loc = ?
					AND JobI.Type   = 1
					AND JobI.Labor  = 1
			   AND JobI.fDate >= '2017-03-30 00:00:00.000'
				 AND (Job.Type <> 2 OR (
					 	Job.Type = 2
						AND Job.Status <> 0
						AND Job.fDate >= '2017-03-30 00:00:00.000'
					 ))
	;",array($_GET['ID']));

	if($r){
		$i = 0;
		$row_count = sqlsrv_num_rows($r);
		while($i < $row_count){
			$array = sqlsrv_fetch_array($r);
			$date = substr($array['fDate'],0,10);
			$dates[$date] = (isset($dates[$date])) ? $dates[$date] - floatval($array['Amount']) : 0 - floatval($array['Amount']);
			$i++;
		}
	}
	ksort($dates);
	$r = $database->query(null,
	"	SELECT   JobI.Amount AS Amount,
				 JobI.fDate as fDate
		FROM     Loc
				 LEFT JOIN Job  ON Loc.Loc = Job.Loc
				 LEFT JOIN JobI ON Job.ID  = JobI.Job
		WHERE    Loc.Loc = ?
				AND (
					JobI.Labor <> 1
					OR JobI.Labor = ''
					OR JobI.Labor = 0
					OR JobI.Labor = ' '
					OR JobI.Labor IS NULL
				)
				AND JobI.fDate >= '2017-03-30 00:00:00.000'
				AND JobI.Type = 1
				 AND (Job.Type <> 2 OR (
					 	Job.Type = 2
						AND Job.Status <> 0
						AND Job.fDate >= '2017-03-30 00:00:00.000'
					 ))

		ORDER BY JobI.fDate ASC
	;",array($_GET['ID']));
	if($r){
		$i = 0;
		$row_count = sqlsrv_num_rows($r);
		while($i < $row_count){
			$array = sqlsrv_fetch_array($r);
			$date = substr($array['fDate'],0,10);
			$dates[$date] = (isset($dates[$date])) ? $dates[$date] - floatval($array['Amount']) : 0 - floatval($array['Amount']);
			$i++;
		}
	}
	ksort($dates);
	$total = 0;
	foreach($dates as $date=>$value){
		$total = $total + $value;
		$totals[$date] = $total;
	}
	ksort($totals);
	foreach($totals as $date=>$total){
		$Year = substr($date,0,4);
		$Month = substr($date,5,2);
		$Day = substr($date,8,2);
		if($date == ""){continue;}
		if($Year == '' || is_null($Year)){continue;}
		$data[] = "[gd({$Year},{$Month},{$Day}),{$total}]";
	}
	echo implode(",",$data);?>
];
lineFit = function(points){
  sI = slopeAndIntercept(points);
  if (sI){
    // we have slope/intercept, get points on fit line
    var N = points.length;
    var rV = [];
    rV.push([points[0][0], sI.slope * points[0][0] + sI.intercept]);
    rV.push([points[N-1][0], sI.slope * points[N-1][0] + sI.intercept]);
    return rV;
  }
  return [];
}

// simple linear regression
slopeAndIntercept = function(points){
  var rV = {},
      N = points.length,
      sumX = 0,
      sumY = 0,
      sumXx = 0,
      sumYy = 0,
      sumXy = 0;

  // can't fit with 0 or 1 point
  if (N < 2){
    return rV;
  }

  for (var i = 0; i < N; i++){
    var x = points[i][0],
        y = points[i][1];
    sumX += x;
    sumY += y;
    sumXx += (x*x);
    sumYy += (y*y);
    sumXy += (x*y);
  }

  // calc slope and intercept
  rV['slope'] = ((N * sumXy) - (sumX * sumY)) / (N * sumXx - (sumX*sumX));
  rV['intercept'] = (sumY - rV['slope'] * sumX) / N;
  rV['rSquared'] = Math.abs((rV['slope'] * (sumXy - (sumX * sumY) / N)) / (sumYy - ((sumY * sumY) / N)));

  return rV;
}
var lineFitSeries = lineFit(job_profit_data_<?php echo $_GET['rand'];?>);
var job_profit_dataset_<?php echo $_GET['rand'];?> = [
    {
        label: "Profit",
        data: job_profit_data_<?php echo $_GET['rand'];?>,
        color: "#FF0000",
        points: { fillColor: "#FF0000", show: true },
        lines: { show: true }
    },{
        label: "Trendline",
        data: lineFitSeries,
        color: "#000000",
        points: { fillColor: "#FF0000", show: true },
        lines: { show: true }
    }
];
var job_profit_options_<?php echo $_GET['rand'];?> = {
    series: {shadowSize: 5},
    xaxes: [{
        mode: "time",
        tickFormatter: function (val, axis) {
            var date = new Date(val);
			return (date.getMonth() + 1) + '/' + date.getDate() + '/' +  date.getFullYear();
        },
        color: "black",
        position: "top",
        axisLabel: "Date",
        axisLabelUseCanvas: true,
        axisLabelFontSizePixels: 12,
        axisLabelFontFamily: 'Verdana, Arial',
        axisLabelPadding: 5
    },
    {
        mode: "time",
        timeformat: "%m/%d",
        tickSize: [3, "day"],
        color: "black",
        axisLabel: "Date",
        axisLabelUseCanvas: true,
        axisLabelFontSizePixels: 12,
        axisLabelFontFamily: 'Verdana, Arial',
        axisLabelPadding: 10
    }],
    yaxis: {
        color: "black",
        tickDecimals: 2,
		tickFormatter : function(val, axis){
			return "$" + parseFloat(val).toLocaleString();
		},
        axisLabel: "Hours A Day",
        axisLabelUseCanvas: true,
        axisLabelFontSizePixels: 12,
        axisLabelFontFamily: 'Verdana, Arial',
        axisLabelPadding: 5
    },
    legend: {
        noColumns: 0,
        labelFormatter: function (label, series) {
            return "<font color=\"white\">" + label + "</font>";
        },
        backgroundColor: "#000",
        backgroundOpacity: 0.9,
        labelBoxBorderColor: "#000000",
        position: "nw"
    },
    grid: {
        hoverable: true,
        borderWidth: 3,
        mouseActiveRadius: 50,
        backgroundColor: { colors: ["#ffffff", "#EDF5FF"] },
        axisMargin: 20
    }
};

$(document).ready(function () {
    $.plot($("#flot-placeholder-profit-<?php echo $_GET['rand'];?>"), job_profit_dataset_<?php echo $_GET['rand'];?>, job_profit_options_<?php echo $_GET['rand'];?>);
    $("#flot-placeholder-profit-<?php echo $_GET['rand'];?>").UseTooltip();
});




function gd(year, month, day) {
    return new Date(year, month - 1, day).getTime();
}

var previousPoint = null, previousLabel = null;
var monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

$.fn.UseTooltip = function () {
    $(this).bind("plothover", function (event, pos, item) {
        if (item) {
            if ((previousLabel != item.series.label) || (previousPoint != item.dataIndex)) {
                previousPoint = item.dataIndex;
                previousLabel = item.series.label;
                $("#tooltip").remove();

                var x = item.datapoint[0];
                var y = item.datapoint[1];
                var date = new Date(x);
                var color = item.series.color;

                showTooltip(item.pageX, item.pageY, color,
                            "<strong>" + item.series.label + "</strong><br>"  +
                            (date.getMonth() + 1) + "/" + date.getDate() +
                            "/" + date.getFullYear() + " : <strong>$" + parseFloat(y).toLocaleString() + "</strong>");
            }
        } else {
            $("#tooltip").remove();
            previousPoint = null;
        }
    });
};

function showTooltip(x, y, color, contents) {
    $('<div id="tooltip">' + contents + '</div>').css({
        position: 'absolute',
        display: 'none',
        top: y - 40,
        left: x - 120,
        border: '2px solid ' + color,
        padding: '3px',
        'font-size': '9px',
        'border-radius': '5px',
        'background-color': '#fff',
        'font-family': 'Verdana, Arial, Helvetica, Tahoma, sans-serif',
        opacity: 0.9
    }).appendTo("body");
}
</script>
<div id='flot-placeholder-profit-<?php echo $_GET['rand'];?>' style='width:100%;height:350px;'></div>
<?php }
}?>