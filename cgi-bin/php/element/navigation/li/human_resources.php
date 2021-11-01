<li> 
    <a href="#"><i class="fa fa-child fa-fw"></i> <span class='masked'>Human Resources</span><span c<span class="fa arrow masked"></span></a>
    <ul class="nav nav-second-level collapse">
        <!--<li>
            <a href="payroll.php"><i class="fa fa-money fa-fw"></i> <span class=''>Payroll</span></a>
        </li>-->
        <li>
            <a href="time_sheet.php?Mechanic=<?php echo $_SESSION['User'];?>"><i class="fa fa-clock-o fa-fw"></i> <span class=''>Timesheet</span></a>
        </li>
        <li>
            <a href="pdf/employee-handbook.pdf"><i class="fa fa-book fa-fw"></i> <span class=''>Employee Handbook</span></a>
        </li>
        <li>
            <a href="pdf/Safety_Manual.pdf"><i class="fa fa-book fa-fw"></i> <span class=''>Safety Manual</span></a>
        </li>
        <li>
            <a href="pdf/Tax_W4.pdf"><i class="fa fa-book fa-fw"></i> <span class=''>Tax W4</span></a>
        </li>
        <li>
            <a href="pdf/return_to_work.pdf"><i class="fa fa-book fa-fw"></i> <span class=''>Return To Work</span></a>
        </li>
        <li>
            <a href="pdf/i-9.pdf"><i class="fa fa-book fa-fw"></i> <span class=''>I-9</span></a>
        </li>
        <li>
            <a href="pdf/Drug_Free_Policy.pdf"><i class="fa fa-book fa-fw"></i> <span class=''>Drug Free Policy</span></a>
        </li>
        <li>
            <a href="pdf/know_the_code.pdf"><i class="fa fa-book fa-fw"></i> <span class=''>Know The Code</span></a>
        </li>
        <?php require(PROJECT_ROOT.'php/element/navigation/li/osha_resources.php');?>
    </ul>
</li>