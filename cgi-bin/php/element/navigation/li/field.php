<li>
    <a href="#"><i class="fa fa-bolt fa-fw"></i> <span class='masked'>Field</span><span c<span class="fa arrow masked"></span></a>
    <ul class="nav nav-second-level collapse">
        <li>
            <a href="tickets.php?Start_Date=<?php echo date('m/01/Y');?>"><i class="fa fa-ticket fa-fw"></i> <span class=''>Tickets</span></a>
        </li>
        <li>
            <a href="units.php"><i class="fa fa-cogs fa-fw"></i> <span class=''>Units</span></a>
        </li> 
        <li>
            <a href="jobs.php?Start_Date=01/01/1980&End_Date=12/31/2017&Job_Type=0&Job_Status=1"><i class="fa fa-suitcase fa-fw"></i> <span class=''>Jobs</span></a> 
        </li>
        <li>
            <a href="violations.php"><i class="fa fa-warning fa-fw"></i> <span class=''>Violations</span></a>
        </li>
        <li>
            <a href="locations.php"><i class="fa fa-building fa-fw"></i> <span class=''>Locations</span></a>
        </li>
        <?php $RouteNav = False;
        if($Field){
            $r = sqlsrv_query($NEI,"SELECT * FROM Route WHERE Mech='{$User['fWork']}';");
            $RouteNav = sqlsrv_fetch_array($r);
        }
        if(is_array($RouteNav) && isset($RouteNav['ID']) && $RouteNav['ID'] > 0){?><li>
            <a href="route.php?ID=<?php echo $RouteNav['ID'];?>"><i class="fa fa-road fa-fw"></i> <span class=''>Route</span></a>
        </li><?php }?>
        <?php if(isset($My_Privileges['Route']) && ($My_Privileges['Route']['Other_Privilege'] >= 4 || $My_Privileges['Admin']['User_Privilege'] >= 7)){?><li>
            <a href="routes.php"><i class="fa fa-road fa-fw"></i> <span class=''>Routes</span></a>
        </li><?php }?>
        <?php if(isset($My_Privileges['Map']) && $My_Privileges['Map']['Other_Privilege'] >= 4){?><li>
            <a href="map.php?Type=Live"><i class="fa fa-map fa-fw"></i> <span class=''>Map</span></a>
        </li><?php }?>
    </ul>
</li>