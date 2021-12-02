<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [ 'read_and_close' => true ] ); 
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
function contains($needle, $haystack)
{
    return strpos($haystack, $needle) !== false;
}
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    $Privileged = FALSE;
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
        $r = $database->query(null,"SELECT * FROM nei.dbo.Emp WHERE ID = ?",array($_GET['User']));
        $My_User = sqlsrv_fetch_array($r);
        $Field = ($User['Field'] == 1 && $User['Title'] != "OFFICE") ? True : False;
        $r = $database->query($Portal,"
            SELECT Access, Owner, Group, Other
            FROM   Portal.dbo.Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access']] = $array2;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Job']) && $My_Privileges['Job']['Owner'] >= 4 && $My_Privileges['Job']['Group'] >= 4 && $My_Privileges['Job']['Other'] >= 4){$Privileged = TRUE;}
    }
    if(!isset($array['ID'],$_GET['ID']) || !$Privileged){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
   		//$r = $database->query(null, "SELECT Loc.Loc AS ID, Loc.Tag AS Name FROM Loc WHERE Loc.Owner = ?",array($_GET['ID']));
   		?><script>
      var availableJobs = [<?php 
        if($_GET['ID'] == ''){$SQL = "'1' = '1'";}
        else{$SQL = "Job.Loc = '{$_GET['ID']}'";}
        $r = $database->query(null,"
			SELECT   Job.ID    AS Job_ID, 
				     Job.fDesc AS Job_Name 
			FROM     nei.dbo.Job 
			WHERE    {$SQL} 
			         AND Job.Type   <> 9
					 AND Job.Type   <> 12
					 AND Job.Status =  1 
			ORDER BY Job.ID ASC");
        $Jobs = array();
        if($r){while($Job = sqlsrv_fetch_array($r)){$Jobs[$Job['Job_ID']] = $Job['Job_Name'];}}
        $data = array();
        if(count($Jobs) > 0){foreach($Jobs as $id=>$name){
          	$name = str_replace("'","",$name);
		  	$name = str_replace('"',"",$name);
		  	$name = str_replace("\n","",$name);
			$name = str_replace("\r","",$name);
			$data[] = '{value:' . '"'. $id . '"' . ', label:' . '"' . $id . " | " . $name . '"' . '}';
        }}
        if(count($data) > 0){echo implode(",",$data);}
        ?>
      ];
	$(document).ready(function(){
		$("input[name='Job_Name']").autocomplete({
			minLength: <?php if(is_numeric($_GET['ID'])){?>0<?php } else {?>3<?php }?>,
			source: function(request, response) {
				var results = $.ui.autocomplete.filter(availableJobs, request.term);

				response(results.slice(0, 10));
			},
			focus: function( event, ui ) {
				$("input[name='Job_Name']").val( ui.item.label );
				return false;
			},
			select: function( event, ui ) {
				$("input[name='Job_Name']").val( ui.item.label );
				$("input[name='Job_ID']").val( ui.item.value );
				lookupUnits(this);
				return false;
			}
		})
		.autocomplete( "instance" )._renderItem = function( ul, item ) {
			return $( "<li>" )
				.append( "<div>" + item.label + "</div>" )
				.appendTo( ul );
		};
	});
    </script><input id='Jobs' placeholder='Job' type='text' name='Job_Name' size='30' />
                                  <input id='Job' name='Job_ID' type='hidden' /><?php
    }
}?>