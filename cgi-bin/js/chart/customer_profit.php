<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( ); 
    require( '/var/www/nouveautexas.com/html/portal/cgi-bin/php/index.php' );
}
if( isset( $_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
    $r = sqlsrv_query(
        $NEI,
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
    $User = sqlsrv_query(
        $NEI,
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
    $r = sqlsrv_query(
        $NEI,
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
function random_color_part() {
    return str_pad( dechex( mt_rand( 0, 255 ) ), 2, '0', STR_PAD_LEFT);
}

function random_color() {
    return random_color_part() . random_color_part() . random_color_part();
}?>
<script>
function gd(year, month, day) {return new Date(year, month - 1, day).getTime();}
	<?php
	$data = array();
	$Jobs = array();
	$dates = array();
	$totals = array();
	$r = sqlsrv_query($NEI,"SELECT Loc.Custom3 FROM Loc WHERE Loc.Owner = ? AND Loc.Custom3 IS NOT NULL AND Loc.Custom3 <> ' ' AND Loc.Custom3 <> '';", array($_GET['ID']));
	$groups = array();
	$groups2 = array();
	if($r){while($array = sqlsrv_fetch_array($r)){
		$r2 = sqlsrv_query($NEI,"SELECT Loc.Owner FROM Loc WHERE Loc.Custom3 = ?;", array($array['Custom3']));
		if($r2){while($row2 = sqlsrv_fetch_array($r2)){
			$groups2[] = "Job.Owner = {$row2['Owner']}";
		}}
	}}
	$groups2 = count($groups2) > 0 ? implode(" OR ", $groups2) : "'1' = '2'";
	$r = sqlsrv_query($NEI,
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
	;",array($_GET['ID']),array("Scrollable"=>SQLSRV_CURSOR_KEYSET));
	if( ($errors = sqlsrv_errors() ) != null) {
        foreach( $errors as $error ) {
            echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
            echo "code: ".$error[ 'code']."<br />";
            echo "message: ".$error[ 'message']."<br />";
        }
    }
	if($r){
		$i = 0;
		$row_count = sqlsrv_num_rows($r);
		while($i < $row_count){
			$array = sqlsrv_fetch_array($r);
			$date = substr($array['fDate'],0,10);
			$dates[$array['Loc']] = isset($dates[$array['Loc']]) ? $dates[$array['Loc']] : array();
			$dates[$array['Loc']][$date] = isset($dates[$array['Loc']][$date]) ? $dates[$array['Loc']][$date] + ($array['Amount']) : $array['Amount'];
			$i++;
		}
	}
	$r = sqlsrv_query($NEI,
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
	;",array($_GET['ID']),array("Scrollable"=>SQLSRV_CURSOR_KEYSET));

	if($r){
		$i = 0;
		$row_count = sqlsrv_num_rows($r);
		while($i < $row_count){
			$array = sqlsrv_fetch_array($r);
			$date = substr($array['fDate'],0,10);
			$dates[$array['Loc']] = isset($dates[$array['Loc']]) ? $dates[$array['Loc']] : array();
			$dates[$array['Loc']][$date] = isset($dates[$array['Loc']][$date]) ? $dates[$array['Loc']][$date] - floatval($array['Amount']) : 0 - floatval($array['Amount']);
			$i++;
		}
	}
	$r = sqlsrv_query($NEI,
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
	;",array($_GET['ID']),array("Scrollable"=>SQLSRV_CURSOR_KEYSET));
	if($r){
		$i = 0;
		$row_count = sqlsrv_num_rows($r);
		while($i < $row_count){
			$array = sqlsrv_fetch_array($r);
			$date = substr($array['fDate'],0,10);
			$dates[$array['Loc']] = isset($dates[$array['Loc']]) ? $dates[$array['Loc']] : array();
			$dates[$array['Loc']][$date] = isset($dates[$array['Loc']][$date]) ? $dates[$array['Loc']][$date] - floatval($array['Amount']) : 0 - floatval($array['Amount']);
			$i++;
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
		?>var location_data_<?php echo $Loc;?> = [<?php
		echo implode(",",$data);?>];
		<?php
		?><?php }?>
		var location_dataset = [<?php $i = 0;?>
				<?php foreach($Locs as $Loc){?><?php echo $i == 0 ? NULL : ',';$i=1;?>{
						label: "<?php $r = sqlsrv_query($NEI,"SELECT Loc.Tag FROM Loc WHERE Loc.Loc = ?;", array($Loc));echo sqlsrv_fetch_array($r)['Tag'];?>",
						data: location_data_<?php echo $Loc;?>,
						color: "#<?php $color =  random_color(); echo $color;?>",
						points: { fillColor: "#<?php echo $color;?>", show: true },
						lines: { show: true }
				}<?php }?>
		];
var job_profit_options = {
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
    $.plot($("#flot-placeholder-profit"), location_dataset, job_profit_options);
    $("#flot-placeholder-profit").UseTooltip();
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
<?php }
}?>