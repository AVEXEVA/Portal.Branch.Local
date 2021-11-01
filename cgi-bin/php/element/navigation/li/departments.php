<?php if(isset($My_Privileges['Maintenance']) || isset($My_Privileges['Executive']) || isset($My_Privileges['Modernization']) || isset($My_Privileges['Repair']) || isset($My_Privileges['Testing']) || isset($My_Privileges['Purchasing']) || isset($My_Privileges['Job']) && $My_Privileges['Job']['Other_Privilege'] >= 4){?><li>
    <a href="#"><i class="fa fa-link fa-fw"></i> <span class='masked'>Departments</span><span c<span class="fa arrow masked"></span></a>
    <ul class="nav nav-second-level collapse">
    	<?php 
        if(isset($My_Privileges['Admin']) || isset($My_Privileges['Executive']) && $My_Privileges['Executive']['Other_Privilege'] >= 4){require(PROJECT_ROOT.'php/element/navigation/li/overview.php');}
    	if(isset($My_Privileges['Admin']) || (isset($My_Privileges['Maintenance']) && $My_Privileges['Executive']['Other_Privilege'] >= 4) || (isset($My_Privileges['Executive']) && $My_Privileges['Executive']['Other_Privilege'] >= 4)){require(PROJECT_ROOT.'php/element/navigation/li/maintenance.php');}
    	if(isset($My_Privileges['Admin']) || (isset($My_Privileges['Modernization']) && $My_Privileges['Modernization']['Other_Privilege'] >= 4) || (isset($My_Privileges['Executive']) && $My_Privileges['Executive']['Other_Privilege'] >= 4)){require(PROJECT_ROOT.'php/element/navigation/li/modernization.php');}
    	if(isset($My_Privileges['Admin']) || (isset($My_Privileges['Repair']) && $My_Privileges['Repair']['Other_Privilege'] >= 4) || (isset($My_Privileges['Executive']) && $My_Privileges['Executive']['Other_Privilege'] >= 4)){require(PROJECT_ROOT.'php/element/navigation/li/repair.php');}
    	if(isset($My_Privileges['Admin']) || (isset($My_Privileges['Testing']) && $My_Privileges['Testing']['Other_Privilege'] >= 4) || (isset($My_Privileges['Executive']) && $My_Privileges['Executive']['Other_Privilege'] >= 4)){require(PROJECT_ROOT.'php/element/navigation/li/testing.php');}
        if(isset($My_Privileges['Admin']) || (isset($My_Privileges['Purchasing']) && $My_Privileges['Purchasing']['Other_Privilege'] >= 4) || (isset($My_Privileges['Executive']) && $My_Privileges['Executive']['Other_Privilege'] >= 4) || TRUE){require(PROJECT_ROOT.'php/element/navigation/li/purchasing.php');}
    	?>
    </ul>
</li><?php }?>