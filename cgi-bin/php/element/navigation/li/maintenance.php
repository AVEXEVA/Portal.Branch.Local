<li>
    <a href="#"><i class="fa fa-link fa-fw"></i> <span class=''>Maintenance</span><span c<span class="fa arrow masked"></span></a>
    <ul class="nav nav-third-level collapse">
        <?php if(isset($My_Privileges['Admin']) || (isset($My_Privileges['Executive']) && $My_Privileges['Executive']['Other_Privilege'] >= 4)){?>
        <li>
            <a href="maintenance.php"><?php $Icons->Job();?> <span class=''>Summary</span></a>
        </li>
        <?php }?>
        <?php if(isset($My_Privileges['Admin']) || (isset($My_Privileges['Maintenance']) && $My_Privileges['Maintenance']['Other_Privilege'] >= 4) || (isset($My_Privileges['Executive']) && $My_Privileges['Executive']['Other_Privilege'] >= 4)){?>
        <li>
            <a href="maintenances.php"><?php $Icons->Job();?> <span class=''>Required Work</span></a>
        </li>
		<li>
            <a href="service_calls.php"><?php $Icons->Phone();?> <span class=''>Service Calls</span></a>
        </li>
        <?php }?>
    	
        
        <!--<li>
            <a href="modernizations.php"><?php $Icons->Job();?> Jobs</a>
        </li>-->
    </ul>
</li>