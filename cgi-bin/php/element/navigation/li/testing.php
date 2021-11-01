<li>
    <a href="#"><i class="fa fa-link fa-fw"></i> <span class=''>Testing</span><span class="fa arrow masked"></span></a>
    <ul class="nav nav-third-level collapse">
    	<?php if(isset($My_Privileges['Executive']) && $My_Privileges['Executive']['Other_Privilege'] >= 4){?>
        <li>
            <a href="testing.php"><?php $Icons->Job();?> <span class=''>Summary</span></a>
        </li>
        <?php }?>
    	<?php if((isset($My_Privileges['Testing']) && $My_Privileges['Testing']['Other_Privilege'] >= 4) || (isset($My_Privileges['Executive']) && $My_Privileges['Executive']['Other_Privilege'] >= 4)){?>
        <li>
            <a href="due_violations.php"><?php $Icons->Job();?> <span class=''>Due Violations</span></a>
        </li>
        <?php }?>
    </ul>
</li>