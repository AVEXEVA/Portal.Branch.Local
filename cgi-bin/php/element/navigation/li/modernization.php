<li>
    <a href="#"><i class="fa fa-link fa-fw"></i> <span class=''>Modernization</span><span c<span class="fa arrow masked"></span></a>
    <ul class="nav nav-third-level collapse">
    	<?php if(isset($My_Privileges['Admin']) || (isset($My_Privileges['Executive']) && $My_Privileges['Executive']['Other_Privilege'] >= 4)){?>
        <li>
            <a href="modernization.php"><?php $Icons->Job();?> <span class=''>Summary</span></a>
        </li>
        <?php }?>
        <?php if(isset($My_Privileges['Admin']) || (isset($My_Privileges['Modernization']) && $My_Privileges['Modernization']['Other_Privilege'] >= 4) || (isset($My_Privileges['Executive']) && $My_Privileges['Executive']['Other_Privilege'] >= 4)){?>
        <li>
            <a href="modernizations.php"><?php $Icons->Job();?> <span class=''>Jobs</span></a>
        </li>
	   <li>
            <a href="modernization_tracker.php"><?php $Icons->Job();?> <span class=''>Tracker</span></a>
        </li>
        <?php }?>
    </ul>
</li>