<li>
    <a href="#"><i class="fa fa-link fa-fw"></i> <span class=''>Purchasing</span><span c<span class="fa arrow masked"></span></a>
    <ul class="nav nav-third-level collapse">
    	<?php if(isset($My_Privileges['Executive']) && $My_Privileges['Executive']['Other_Privilege'] >= 4){?><?php }?>
	   <li>
            <a href="purchasing.php"><?php $Icons->Job();?> <span class=''>Tracker</span></a>
        </li>
        <li>
            <a href="job_item_search.php"><?php $Icons->Job();?><span class=''>Job Item Search</span></a>
        </li>
        <li>
            <a href="rmas.php"><?php $Icons->Job();?> <span class=''>RMAs</span></a>
        </li>
    </ul>
</li>