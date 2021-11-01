<li>
    <a href="#"><i class="fa fa-link fa-fw"></i> <span class=''>Repair</span><span c<span class="fa arrow masked"></span></a>
    <ul class="nav nav-third-level collapse">
    	<?php if(isset($My_Privileges['Executive']) && $My_Privileges['Executive']['Other_Privilege'] >= 4){?>
        <li>
            <a href="repair.php"><?php $Icons->Job();?> <span class=''>Summary</span></a>
        </li>
        <?php }?>
    	
    </ul>
</li>