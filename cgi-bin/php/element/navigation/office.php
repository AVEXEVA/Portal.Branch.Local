<li>
	<a href="#"><i class="fa fa-bolt fa-fw"></i><span class='masked'> Field</span><span class="fa arrow"></span></a>
    <ul class="nav nav-second-level collapse">
        <li>
            <a href="units.php"><i class="fa fa-cogs fa-fw"></i> Units</a>
        </li>
        <li>
            <a href="jobs.php?Start_Date=01/01/1980&End_Date=12/31/2017&Job_Type=0&Job_Status=1"><i class="fa fa-suitcase fa-fw"></i> Jobs</a>
        </li>
        <li>
            <a href="violations.php"><i class="fa fa-warning fa-fw"></i> Violations</a>
        </li>
        <li>
            <a href="locations.php"><i class="fa fa-building fa-fw"></i> Locations</a>
        </li>
        <li>
            <a href="routes.php"><i class="fa fa-road fa-fw"></i> Routes</a>
        </li>
        <li>
            <a href="map.php?Type=Live"><i class="fa fa-map fa-fw"></i> Map</a>
        </li>
    </ul>
</li>
<li>
	<a href="#"><i class="fa fa-bolt fa-fw"></i><span>Dispatch</span></s/pan><span class="fa arrow"></span></a>
    <ul class="nav nav-second-level collapse">
        <li>
            <a href="dispatch.php?Preload=False"><i class="fa fa-headphones fa-fw"></i> Dispatch</a>
        </li>
        <li>
            <a href="review.php?Supervisors=All&Date=<?php echo $Wensday;?>&Preload=False"><i class="fa fa-book fa-fw"></i> Review</a>
        </li>
    </ul>
</li>
<?php require(PROJECT_ROOT.'php/element/navigation/li/departments.php');?>
<li>
	<a href="#"><i class="fa fa-link fa-fw"></i><span> Office</span><span class="fa arrow"></span></a>
    <ul class="nav nav-second-level collapse">
        <li>
            <a href="customers.php"><i class="fa fa-link fa-fw"></i> Customers</a>
        </li>
        <li>
            <a href="invoices.php"><?php $Icons->Invoice();?> Invoices</a>
        </li>
        <li>
            <a href="collections.php"><?php $Icons->Collection();?> <span class=''>Collections</span></a>
        </li>
		<li>
            <a href="contracts.php"><?php $Icons->Customer();?> <span class=''>Contracts</span></a>
        </li>
		<li>
			<a href="financials.php"><i class='fa fa-dollar fa-fw'></i> Financials</a>
		</li>
        <li>
            <a href="archive.php"><i class="fa fa-archive fa-fw"></i> Archive</a>
        </li>
        <li>
            <a href="directory.php"><i class="fa fa-info fa-fw"></i> Directory</a>
        </li>
    </ul>
</li>
<?php if((isset($My_Privileges['Executive']) && $My_Privileges['Executive']['Other_Privilege'] >= 4) || (isset($My_Privileges['Modernization']) && $My_Privileges['Modernization']['Other_Privilege'] >= 4)){require(PROJECT_ROOT.'php/element/navigation/li/departments.php');}?>