<li>
    <a href="#"><i class="fa fa-link fa-fw"></i> <span class='masked'>Office</span><span c<span class="fa arrow masked"></span></a>
    <ul class="nav nav-second-level collapse">
        <li>
            <a href="customers.php"><i class="fa fa-link fa-fw"></i> <span class=''>Customers</span></a>
        </li>
		<li>
            <a href="collections.php"><?php $Icons->Collection();?> <span class=''>Leads</span></a>
        </li>
		<li>
            <a href="contracts.php"><?php $Icons->Customer();?> <span class=''>Contracts</span></a>
        </li>
        <li>
            <a href="master_accounts.php"><i class="fa fa-link fa-fw"></i> <span class=''>Master Accounts</span></a>
        </li>
        <!--<li>
            <a href="invoices.php"><?php $Icons->Invoice();?> <span class=''>Invoices</span></a>
        </li>
        <li>
            <a href="proposals.php"><?php $Icons->Proposal();?> <span class=''>Proposals</span></a>
        </li>-->
        <li>
            <a href="collections.php"><?php $Icons->Collection();?> <span class=''>Collections</span></a>
        </li>
        <li>
            <a href="archive.php"><i class="fa fa-archive fa-fw"></i> <span class=''>Archive</span></a>
        </li>
        <?php if(isset($My_Privileges['Legal']) && $My_Privileges['Legal']['User_Privilege'] >= 4 && $My_Privileges['Legal']['Group_Privilege'] >= 4  && $My_Privileges['Legal']['Other_Privilege'] >= 4){?><li>
            <a href="legal.php"><?php $Icons->Legal();?> Legal</a>
        </li><?php }?>
        <?php if((isset($My_Privileges['Admin']) && $My_Privileges['Admin']['Other_Privilege'] >= 4) || (isset($My_Privileges['Insurance']) && $My_Privileges['Insurance']['User_Privilege'] >= 4 && $My_Privileges['Insurance']['Group_Privilege'] >= 4  && $My_Privileges['Insurance']['Other_Privilege'] >= 4)){?><li>
            <a href="insured_companies.php"><?php $Icons->Legal();?> Insurance</a>
        </li><?php }?>
        <?php if((isset($My_Privileges['Admin']) && $My_Privileges['Admin']['Other_Privilege'] >= 4) || (isset($My_Privileges['Financials']) && $My_Privileges['Financials']['User_Privilege'] >= 4 && $My_Privileges['Financials']['Group_Privilege'] >= 4  && $My_Privileges['Financials']['Other_Privilege'] >= 4)){?><li>
            <a href="financials.php"><?php $Icons->Chart();?> Financials</a>
        </li><?php }?>
        <li>
            <a href="directory.php"><i class="fa fa-info fa-fw"></i> <span class=''>Directory</span></a>
        </li>
        <li><form action="search_all.php" method="GET"><div class='input-group' style='width:100%;'><input type='text' class='form-control' name='Keyword' style='width:100%;' placeholder='Search Relative Records' /></div></form></li>
    </ul>
</li>