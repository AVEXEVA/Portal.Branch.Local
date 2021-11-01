<?php require(PROJECT_ROOT.'php/element/navigation/li/field.php');?>
<?php require(PROJECT_ROOT.'php/element/navigation/li/dispatch.php');?>
<?php require(PROJECT_ROOT.'php/element/navigation/li/departments.php');?>
<?php /*require('php/element/navigation/li/procurement.php');*/?>
<?php require(PROJECT_ROOT.'php/element/navigation/li/office.php');?>
<?php /*<li>
    <a href="#"><i class="fa fa-bolt fa-fw"></i> Field<span class="fa arrow"></span></a>
    <ul class="nav nav-second-level collapse">
        <li>
            <a href="tickets.php?Start_Date=<?php echo date('m/01/Y');?>"><i class="fa fa-ticket fa-fw"></i> Tickets</a>
        </li>
        <li>
            <a href="units.php"><i class="fa fa-cogs fa-fw"></i> Units</a>
        </li>
        <li>
            <a href="jobs.php?Start_Date=01/01/1980&End_Date=12/31/2017&Job_Type=0&Job_Status=1"><i class="fa fa-wrench fa-fw"></i> Jobs</a>
        </li>
        <li>
            <a href="violations.php"><i class="fa fa-warning fa-fw"></i> Violations</a>
        </li>
        </li>
        <?php $RouteNav = False;
        if($Field){
            $r = sqlsrv_query($NEI,"SELECT * FROM Route WHERE Mech='{$User['fWork']}';");
            $RouteNav = sqlsrv_fetch_array($r);
        }
        if(is_array($RouteNav) && isset($RouteNav['ID']) && $RouteNav['ID'] > 0){?><li>
            <a href="route.php?ID=<?php echo $RouteNav['ID'];?>"><i class="fa fa-road fa-fw"></i> Route</a>
        </li><?php }?>
    </ul>
</li>
<li>
    <a href="#"><i class="fa fa-cloud fa-fw"></i> Support<span class="fa arrow"></span></a>
    <ul class="nav nav-second-level collapse">
        <li>
            <a href="requisition.php"><i class="fa fa-barcode fa-fw"></i> Requisition</a>
        </li>
        <li>
            <a href="personnel_request.php"><i class="fa fa-users fa-fw"></i> Personnel Request</a>
        </li>
        <li>
            <a href="safety_report.php"><i class="fa fa-exclamation fa-fw"></i> Safety Report</a>
        </li>
    </ul>
</li>
<li>
    <a href="#"><i class="fa fa-file-text-o fa-fw"></i> Accounting<span class="fa arrow"></span></a>
    <ul class="nav nav-second-level collapse">
        <li>
            <a href="payroll.php"><i class="fa fa-money fa-fw"></i> Payroll</a>
        </li>
        <li>
            <a href="time_sheet.php?Mechanic=<?php echo $_SESSION['User'];?>"><i class="fa fa-clock-o fa-fw"></i> Timesheet</a>
        </li>
    </ul>
</li>
<li> 
    <a href="#"><i class="fa fa-child fa-fw"></i> Human Resources<span class="fa arrow"></span></a>
    <ul class="nav nav-second-level collapse">
        <li>
            <a href="pdf/employee-handbook.pdf"><i class="fa fa-book fa-fw"></i> Employee Handbook</a>
        </li>
        <li>
            <a href="pdf/Safety_Manual.pdf"><i class="fa fa-book fa-fw"></i> Safety Manual</a>
        </li>
        <li>
            <a href="pdf/Tax_W4.pdf"><i class="fa fa-book fa-fw"></i> Tax W4</a>
        </li>
        <li>
            <a href="pdf/return_to_work.pdf"><i class="fa fa-book fa-fw"></i> Return To Work</a>
        </li>
        <li>
            <a href="pdf/i-9.pdf"><i class="fa fa-book fa-fw"></i> I-9</a>
        </li>
        <li>
            <a href="pdf/Drug_Free_Policy.pdf"><i class="fa fa-book fa-fw"></i> Drug Free Policy</a>
        </li>
        <li>
            <a href="pdf/know_the_code.pdf"><i class="fa fa-book fa-fw"></i> Know The Code</a>
        </li>
        <li>
            <a href="#"><i class="fa fa-book fa-fw"></i> OSHA Resources</a>
            <ul class='nav nav-third-level'>
                <li><a href='pdf/amputation-factsheet.pdf' ><i class="fa fa-book fa-fw"></i> Amputation Fact Sheet</a></li>
                <li><a href='pdf/asbestos-factsheet.pdf' ><i class="fa fa-book fa-fw"></i> Asbestos Fact Sheet</a></li>
                <li><a href='pdf/carbon_monoxide_fact_sheet.pdf' ><i class="fa fa-book fa-fw"></i> Carbon Monoxide Fact Sheet</a></li>
                <li><a href='pdf/circuit_breakers.pdf' ><i class="fa fa-book fa-fw"></i> Circuit Breakers</a></li>
                <li><a href='pdf/cleanup_hazards.pdf' ><i class="fa fa-book fa-fw"></i> Cleanup Hazards</a></li>
                <li><a href='pdf/combustible_dust_explosions.pdf' ><i class="fa fa-book fa-fw"></i> Combustible Dust Explosions</a></li>
                <li><a href='pdf/combustible_dust_fact_sheet.pdf' ><i class="fa fa-book fa-fw"></i> Combustible Dust Fact Sheet</a></li>
                <li><a href='pdf/confined_space_quick_card.pdf' ><i class="fa fa-book fa-fw"></i> Confined Space Quick Card</a></li>
                <li><a href='pdf/construction_personal_protective_equipment.pdf' ><i class="fa fa-book fa-fw"></i> Construction Personal Protective Equipment</a></li>
                <li><a href='pdf/demolition.pdf' ><i class="fa fa-book fa-fw"></i> Demolition</a></li>
                <li><a href='pdf/downed_electrical_wires.pdf' ><i class="fa fa-book fa-fw"></i> Downed Electrical Wires</a></li>
                <li><a href='pdf/electrical_safety.pdf' ><i class="fa fa-book fa-fw"></i> Electrical Safety</a></li>
                <li><a href='pdf/emergency_exit_routes.pdf' ><i class="fa fa-book fa-fw"></i>Emergency Exit Routes</a></li>
                <li><a href='pdf/evacuating_high_rise_buildings.pdf' ><i class="fa fa-book fa-fw"></i> Evacuating High Rise Buildings</a></li>
                <li><a href='pdf/fall_protection_fact_sheet.pdf' ><i class="fa fa-book fa-fw"></i> Fall Protection Fact Sheet</a></li>
                <li><a href='pdf/flood_clean_up.pdf' ><i class="fa fa-book fa-fw"></i> Flood Clean Up</a></li>
                <li><a href='pdf/hand_and_power_tools.pdf' ><i class="fa fa-book fa-fw"></i> Hand and Power Tools</a></li>
                <li><a href='pdf/improper_elevator_controller_wiring.pdf' ><i class="fa fa-book fa-fw"></i> Improper Elevator Controller Wiring</a></li>
                <li><a href='pdf/ladder_safety.pdf' ><i class="fa fa-book fa-fw"></i> Ladder Safety</a></li>
                <li><a href='pdf/lead_in_construction.pdf' ><i class="fa fa-book fa-fw"></i> Lead in Construction</a></li>
                <li><a href='pdf/lockout_tagout.pdf' ><i class="fa fa-book fa-fw"></i> Lockout Tagout</a></li>
                <li><a href='pdf/mercury_exposure.pdf' ><i class="fa fa-book fa-fw"></i> Mercury Exposure</a></li>
                <li><a href='pdf/mold.pdf' ><i class="fa fa-book fa-fw"></i> Mold</a></li>
                <li><a href='pdf/nail_gun_safety.pdf' ><i class="fa fa-book fa-fw"></i> Nail Gun Safety</a></li>
                <li><a href='pdf/noise_in_construction.pdf' ><i class="fa fa-book fa-fw"></i> Noise in Construction</a></li>
                <li><a href='pdf/personal_protective_equipment_fact_sheet.pdf' ><i class="fa fa-book fa-fw"></i> Personal Protective Equipment</a></li>
                <li><a href='pdf/portable_generator_safety.pdf' ><i class="fa fa-book fa-fw"></i> Portable Generator Safety</a></li>
                <li><a href='pdf/working_safely_with_electricity.pdf' ><i class="fa fa-book fa-fw"></i> Working Safely with Electricity</a></li>
            </ul>
        </li>
    </ul>
</li>
<li>
    <a href="https://mail.google.com"><i class="fa fa-envelope fa-fw"></i> Email</a>
</li>
<li>
    <a href="http://www.nouveauelevator.com/w/index.php"><i class="fa fa-globe fa-fw"></i> Wiki</a>
</li>*/?>