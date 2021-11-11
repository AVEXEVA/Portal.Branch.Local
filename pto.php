<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/bin/php/index.php' );
}
if(isset(
  $_SESSION['User'],
  $_SESSION['Hash'] ) ) {
    $r = \singleton\database::getInstance( )->query(
    	null,
      " SELECT  *
        FROM    Connection
        WHERE   Connection.Connector = ?
        AND     Connection.Hash  = ?
 ;",array($_SESSION['User'],$_SESSION['Hash']));
    $Connection = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $r = \singleton\database::getInstance( )->query(
    	null,
      " SELECT  *,
                Emp.fFirst AS First_Name,
                Emp.Last   AS Last_Name
        FROM    Emp
        WHERE   Emp.ID = ?
 ;",array($_SESSION['User']));
    $User = sqlsrv_fetch_array($r);
 $r = \singleton\database::getInstance( )->query(
   null,
    " SELECT  *
      FROM    Privilege
      WHERE   Privilege.User_ID = ?
 ;",array($_SESSION['User']));
 $Privileges = array();
 if($r){while($Privilege = sqlsrv_fetch_array($r)){$Privileges[$Privilege['Access_Table']] = $Privilege;}}
    if(	!isset($Connection['ID'])
     || !isset($Privileges['Ticket'])
       || $Privileges['Ticket']['User_Privilege']  < 4
       || $Privileges['Ticket']['Group_Privilege'] < 4){
       ?><?php require('../404.html');?><?php }
    else {
      \singleton\database::getInstance( )->query(
        null,
    " INSERT INTO Activity([User], [Date], [Page])
      VALUES(?,?,?)
   ;",array($_SESSION['User'],date("Y-m-d H:i:s"), "pto.php"));

?><!DOCTYPE html>
<html lang="en">
<head>
    <?php require( bin_meta . 'index.php');?>
    <title>Nouveau Texas | Portal</title>
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>

</head>

<body>
<div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
  <?php require( bin_php . 'element/navigation.php');?>
  <?php require( bin_php . 'element/loading.php');?>
  <div id="page-wrapper" class='content' style='height:100%;'>
   <div class="panel panel-primary" style='margin-bottom:0px;height:100%;overflow-y:scroll;'>
     <div class="panel-heading">Paid Time Off</div>
     <div class='panel-body'>
      <?php
        $r = $database->query(null,
          " SELECT
                Emp.*,
                Emp.Last as Last_Name,
                Emp.Last AS Last,
                Rol.*,
                PRWage.Reg as Wage_Regular,
                PRWage.OT1 as Wage_Overtime,
                PRWage.OT2 as Wage_Double_Time
            FROM
                (Emp LEFT JOIN PRWage ON Emp.WageCat = PRWage.ID)
                LEFT JOIN Rol ON Emp.Rol = Rol.ID
            WHERE Emp.ID = ?;",array($_SESSION['User']));
        $User = sqlsrv_fetch_array($r);
        $serverName = "172.16.12.45";
        nullectionOptions = array(
          "Database" => "ATTENDANCE",
          "Uid" => "sa",
          "PWD" => "SQLABC!23456",
          'ReturnDatesAsStrings'=>true
        );
        //Establishes the connection
        $c2 = sqlsrv_connect($serverName, nullectionOptions);
        $r = $database->query($c2,"select * from Employee where EmpID= ?;",array($User['Ref']));
        $Attendance = sqlsrv_fetch_array($r);
        while($temp = sqlsrv_fetch_array($r));
      ?>
       <table spacing='3' style='width:100%;'>
         <thead>
           <th></th>
           <th style='text-align:center;'><b>Available</b></th>
           <th style='text-align:center;'><b>Used</b></th>
           <th style='text-align:center;'><b>Allowed</b></th>
         </thead>
         <tbody>
           <tr>
             <td style='color:white !important;padding:5px;'><b>Sick Days</b></td>
             <td style='text-align:center;color:white !important;padding:5px;'><?php echo $Attendance['SickAvail'];?></td>
             <td style='text-align:center;color:white !important;padding:5px;'><?php echo $Attendance['SickAllowed'] - $Attendance['SickAvail'];?></td>
             <td style='text-align:center;color:white !important;padding:5px;'><?php echo $Attendance['SickAllowed'];?></td>
           </tr>
           <tr>
             <td style='color:white !important;padding:5px;'><b>Vacation Days</b></td>
             <td style='text-align:center;color:white !important;padding:5px;'><?php echo $Attendance['VacAvail'];?></td>
             <td style='text-align:center;color:white !important;padding:5px;'><?php echo $Attendance['VacAllowed'] - $Attendance['VacAvail'];?></td>
             <td style='text-align:center;color:white !important;padding:5px;'><?php echo $Attendance['VacAllowed'];?></td>
           </tr>
           <tr>
             <td style='color:white !important;padding:5px;'><b>Medical Days</b></td>
             <td style='text-align:center;color:white !important;padding:5px;'><?php echo $Attendance['MedAvail'];?></td>
             <td style='text-align:center;color:white !important;padding:5px;'><?php echo $Attendance['MedicalDayAllowed'] - $Attendance['MedAvail'];?></td>
             <td style='text-align:center;color:white !important;padding:5px;'><?php echo $Attendance['MedicalDayAllowed'];?></td>
           </tr>
           <tr>
             <td style='color:white !important;padding:5px;'><b>Lieu Days</b></td>
             <td style='text-align:center;color:white !important;padding:5px;'><?php echo $Attendance['LieuAvail'];?></td>
             <td style='text-align:center;color:white !important;padding:5px;'><?php echo $Attendance['LieuDayAllowed'] - $Attendance['LieuAvail'];?></td>
             <td style='text-align:center;color:white !important;padding:5px;'><?php echo $Attendance['LieuDayAllowed'];?></td>
           </tr>
         </tbody>
       </table>
     </div>
     <div class="panel-body" style='height:100%;overflow-y:scroll;'>
       <table id='Table_Tickets' class='display' cellspacing='0' width='100%' <?php if(isMobile()){?>style='font-size:10px;'<?php }?>>
         <thead>
           <th>Type</th>
           <th>Date</th>
         </thead>
       </table>
     </div>
   </div>
  </div>
</div>
    <!-- Bootstrap Core JavaScript -->


    <!-- Metis Menu Plugin JavaScript -->


    <!-- Morris Charts JavaScript -->
    <!--<script src="https://www.nouveauelevator.com/vendor/raphael/raphael.min.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/morrisjs/morris.min.js"></script>
    <script src="../data/morris-data.php"></script>-->

    <?php require(PROJECT_ROOT.'js/datatables.php');?>


    <!-- Custom Theme JavaScript -->


    <!--Moment JS Date Formatter-->


    <!-- JQUERY UI Javascript -->


    <!-- Custom Date Filters-->

    <style>
    div.column {display:inline-block;vertical-align:top;}
    div.label1 {display:inline-block;font-weight:bold;width:150px;vertical-align:top;}
    div.data {display:inline-block;width:300px;vertical-align:top;}
    </style>
 <script src="bin/js/function/formatTicket.js"></script>
    <script>
        $(document).ready(function() {
            var Table_Tickets = $('#Table_Tickets').DataTable( {
              "ajax": {
                      "url": "bin/php/reports/PTO.php",
                      "dataSrc":function(json){
                          if(!json.data){json.data = [];}
                          return json.data;}
              },
              "columns": [
                  {
                    "data":"Type"
                   },{
                     "data": "Date"
                  }
              ],
              "lengthMenu":[[10,25,50,100,500,-1],[10,25,50,100,500,"All"]],
              "order": [[1, 'desc']],
              "searching":false,
              "language":{"loadingRecords":""},
              "initComplete":function(){finishLoadingPage();},
               "paging":false,
                "drawCallback": function ( settings ) {
                  hrefTickets(this.api());
                }
            } );
        });
        function hrefTickets(tbl){
          $("table#Table_Tickets tbody tr").each(function(){
            $(this).on('click',function(){
              document.location.href='ticket2.php?ID=' + tbl.row(this).data().ID;
            });
          });
        }
    </script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=tickets.php';</script></head></html><?php }?>
