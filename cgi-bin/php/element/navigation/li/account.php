<li>
    <a href="#"><i class="fa fa-file-text-o fa-fw"></i> <span class='masked'>Accounting</span><span c<span class="fa arrow masked"></span></a>
    <ul class="nav nav-second-level collapse">
        <li>
            <a href="payroll.php"><?php $Icons->Invoice();?> <span class=''>Invoices</span></a>
        </li>
        <li>
            <a href="time_sheet.php?Mechanic=<?php echo $_SESSION['User'];?>"><?php $Icons->Proposal();?> <span class=''>Proposals</span></a>
        </li>
    </ul>
</li>