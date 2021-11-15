<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
if( isset( $_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
    $r = \singleton\database::getInstance( )->query(
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
    $User = \singleton\database::getInstance( )->query(
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
    $r = \singleton\database::getInstance( )->query(
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
    	function random_color_part() { return str_pad( dechex( mt_rand( 0, 255 ) ), 2, '0', STR_PAD_LEFT); }
		function random_color() { return random_color_part() . random_color_part() . random_color_part(); }
?><script>
	function gd(year, month, day) {return new Date(year, month - 1, day).getTime();}
<?php
	$data = array();
	$Jobs = array();
	$dates = array();
	$totals = array();
	$r = \singleton\database::getInstance( )->query(null,"SELECT Loc.Custom3 FROM Loc WHERE Loc.Owner = ? AND Loc.Custom3 IS NOT NULL AND Loc.Custom3 <> ' ' AND Loc.Custom3 <> '';", array($_GET['ID']));
	$groups = array();
	$groups2 = array();
	if($r){while($array = sqlsrv_fetch_array($r)){
		$r2 = \singleton\database::getInstance( )->query(null,"SELECT Loc.Owner FROM Loc WHERE Loc.Custom3 = ?;", array($array['Custom3']));
		if($r2){while($row2 = sqlsrv_fetch_array($r2)){
			$groups2[] = "Job.Owner = {$row2['Owner']}";
		}}
	}}
	$groups2 = count($groups2) > 0 ? implode(" OR ", $groups2) : "'1' = '2'";
	$r = \singleton\database::getInstance( )->query(null,
	" SELECT   Job.Loc,
							Invoice.Amount AS Amount,
				 		Invoice.fDate  AS fDate
		FROM     Invoice
							LEFT JOIN Job ON Invoice.Job = Job.ID
							LEFT JOIN Loc ON Job.Loc = Loc.Loc
		WHERE    (Job.Owner       =  ?  OR {$groups2})
				 AND Invoice.fDate >= '2017-03-30 00:00:00.000'
				 AND (Job.Type <> 2 OR (
					 	Job.Type = 2
						AND Job.Status <> 0
						AND Job.fDate >= '2017-03-30 00:00:00.000'
					 ))

		ORDER BY Invoice.fDate ASC
	;",array($_GET['ID']));
	if($r){
		while( $row = sqlsrv_fetch_array( $r ) ){
			$date = substr($row['fDate'],0,10);
			$dates[$row['Loc']] = isset($dates[$row['Loc']]) ? $dates[$row['Loc']] : array();
			$dates[$row['Loc']][$date] = isset($dates[$row['Loc']][$date]) ? $dates[$row['Loc']][$date] + ($row['Amount']) : $row['Amount'];
		}
	}
	$r = \singleton\database::getInstance( )->query(null,
	" SELECT Job.Loc, JobI.Amount AS Amount,
			   JobI.fDate as fDate
		FROM   Loc
			   LEFT JOIN Job  ON Loc.Loc = Job.Loc
			   LEFT JOIN JobI ON Job.ID  = JobI.Job
		WHERE  (Job.Owner       =  ?  OR {$groups2})
					AND JobI.Type   = 1
					AND JobI.Labor  = 1
			   AND JobI.fDate >= '2017-03-30 00:00:00.000'
				 AND (Job.Type <> 2 OR (
					 	Job.Type = 2
						AND Job.Status <> 0
						AND Job.fDate >= '2017-03-30 00:00:00.000'
					 ))
			ORDER BY JobI.fDate ASC
	;",array($_GET['ID']));

	if($r){
		while( $row = sqlsrv_fetch_array( $r ) ){
			$date = substr($row['fDate'],0,10);
			$dates[$row['Loc']] = isset($dates[$row['Loc']]) ? $dates[$row['Loc']] : array();
			$dates[$row['Loc']][$date] = isset($dates[$row['Loc']][$date]) ? $dates[$row['Loc']][$date] - floatval($row['Amount']) : 0 - floatval($row['Amount']);$dates[$row['Loc']][$date] = isset($dates[$row['Loc']][$date]) ? $dates[$row['Loc']][$date] + ($row['Amount']) : $row['Amount'];
		}
	}

	$r = \singleton\database::getInstance( )->query(null,
	"	SELECT   Job.Loc, JobI.Amount AS Amount,
				 JobI.fDate as fDate
		FROM     Loc
				 LEFT JOIN Job  ON Loc.Loc = Job.Loc
				 LEFT JOIN JobI ON Job.ID  = JobI.Job
		WHERE    (Job.Owner       =  ?  OR {$groups2})
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
		while( $row = sqlsrv_fetch_array( $r ) ){
			$date = substr($row['fDate'],0,10);
			$dates[$row['Loc']] = isset($dates[$row['Loc']]) ? $dates[$row['Loc']] : array();
			$dates[$row['Loc']][$date] = isset($dates[$row['Loc']][$date]) ? $dates[$row['Loc']][$date] - floatval($row['Amount']) : 0 - floatval($row['Amount']);
		}
	}
	$total = 0;
	$totals = array();
	foreach($dates as $Loc=>$array){
		$total = 0;
		$totals[$Loc] = array();
		ksort($array);
		foreach($array as $date=>$value){
			$total = $total + $value;
			$totals[$Loc][$date] = $total;
		}
		ksort($totals[$Loc]);
	}
	$Locs = array();
	foreach($totals as $Loc=>$array){
		$Locs[] = $Loc;
		$data = array();
		ksort($array);
		foreach($array as $date=>$total){
			$Year = substr($date,0,4);
			$Month = substr($date,5,2);
			$Day = substr($date,8,2);
			if($date == ""){continue;}
			if($Year == '' || is_null($Year)){continue;}
			$data[] = "[gd({$Year},{$Month},{$Day}),{$total}]";
		}
		?>var customer_data_<?php echo $Loc;?> = [<?php
		echo implode(",",$data);?>];
		<?php
		?><?php }?>
		var customer_dataset = [<?php $i = 0;?>
				<?php foreach($Locs as $Loc){?><?php echo $i == 0 ? NULL : ',';$i=1;?>{
						label: "<?php $r = \singleton\database::getInstance( )->query(null,"SELECT Loc.Tag FROM Loc WHERE Loc.Loc = ?;", array($Loc));echo sqlsrv_fetch_array($r)['Tag'];?>",
						data: customer_data_<?php echo $Loc;?>,
						color: "#<?php $color =  random_color(); echo $color;?>",
						points: { fillColor: "#<?php echo $color;?>", show: true },
						lines: { show: true }
				}<?php }?>
		];
var customer_profit_options = {
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
        	return null;
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
    $.plot($("#customer-profit"), customer_dataset, customer_profit_options);
    $("#customer-profit").UseTooltip();
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
    }).appendTo("body").fadeIn(200);
}
</script>
<div id='customer-profit' style='width:100%;height:350px;'></div>
<?php }
}?>
