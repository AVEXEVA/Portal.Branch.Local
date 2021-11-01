<?php
$r = sqlsrv_query($NEI,"SELECT TicketO.* FROM nei.dbo.TicketO LEFT JOIN nei.dbo.Emp ON TicketO.fWork = Emp.fWork WHERE Emp.ID = ? AND TicketO.High = 1 AND TicketO.Assigned < 5 AND TicketO.ID NOT IN (SELECT Alert.Ticket FROM Portal.dbo.Alert);",array($_SESSION['User']));
if($r){
  $Tickets = array();
  while($row = sqlsrv_fetch_array($r)){
    sqlsrv_query($NEI,"INSERT INTO Portal.dbo.Alert(Ticket) VALUES(?);",array($row['ID']));
    $Tickets[] = $row;
  }
  if(count($Tickets) > 0){
    if(count($Tickets) == 1){
      $row = array_pop($Tickets);
      ?><div class='panel panel-primary' id='Banner-Alert'>
        <div class='panel-heading' style='background-color:#ffd700 !important;color:black !important;text-align:center;'>
          <div class='row'>
            <div class='col-xs-12'>You have recieved a  <a href='ticket5.php?ID=<?php echo $row['ID'];?>' style='text-decoration:underline;'><b>high priorty ticket</b></a></div>
            <div class='col-xs-12' style='height:5px;'>&nbsp;</div>
            <div class='col-xs-6'><button style='width:100%;' onClick="document.location.href='ticket5.php?ID=<?php echo $row['ID'];?>';">View</button></div>
            <div class='col-xs-6'><button style='width:100%;' onClick="closeAlert();">Close</button></div>
          </div>
        </div>
      </div><?php
    } else {
      ?><div class='panel panel-primary' id='Banner-Alert'>
        <div class='panel-heading' style='background-color:#ffd700 !important;color:black !important;text-align:center;'>
          <div class='row'>
            <div class='col-xs-12'>You have recieved <a href="document.location.href='work.php';"><b><?php echo count($Tickets);?> high priorty tickets</b></a></div>
            <div class='col-xs-12' style='height:5px;'>&nbsp;</div>
            <div class='col-xs-6'><button style='width:100%;' onClick="document.location.href='work.php';">View</button></div>
            <div class='col-xs-6'><button style='width:100%;' onClick="closeAlert();">Close</button></div>
          </div>
        </div>
      </div><?php
    }
    ?><script>
    function closeAlert(){
      document.getElementById("Banner-Alert").remove();
    }
    </script><?php
  }
}
?>
<?php
$Dispatch_Users = array(673,925,223,767,1137,465,371,569,418,772,254,763,273,19,232,17,1011,987,773,472,480,133,881,183,225,906);
//$Dispatch_Users = array(673,925,250,895,223,767,1137,465,371,569,418,772,254,763,273,19,232,17,1011,987,773,472,480,133,881,183,225,906);
//$Admin_Users = array(250,895);
//if(!isset($Field)){$Field = True;}
$Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
$Today = date('l');
if($Today == 'Wednesday'){$Wednesday = date('m/d/Y');}
elseif($Today == 'Thursday'){$Wednesday = date('m/d/Y', strtotime($Today . ' +6 days'));}
elseif($Today == 'Friday'){$Wednesday = date('m/d/Y', strtotime($Today . ' +5 days'));}
elseif($Today == 'Saturday'){$Wednesday = date('m/d/Y', strtotime($Today . ' +4 days'));}
elseif($Today == 'Sunday'){$Wednesday = date('m/d/Y', strtotime($Today . ' +3 days'));}
elseif($Today == 'Monday'){$Wednesday = date('m/d/Y', strtotime($Today . ' +2 days'));}
elseif($Today == 'Tuesday'){$Wednesday = date('m/d/Y', strtotime($Today . ' +1 days'));}?><!-- Navigation -->
<?php if(isMobile() || true){?>
<style>
    .show-on-hover:hover > ul.dropdown-menu {
    display: block;
}
.mobile-btn {
    padding-top:10px;
}
</style>
<nav class="navbar navbar-default navbar-static-top" role="navigation" style="border-color:#151515 !important;margin-bottom: 0 !important;;background-color:#151515;color:white;">
    <div class="navbar-header" style="float:left;">
        <a class="navbar-brand BankGothic" href="home.php" style='font-size:30px;color:white;'>
            <img src='https://www.nouveauelevator.com/Images/Icons/logo.png' width='25px' style='padding-right:5px;' align='left' />
           <span style='font-size:20px;'>Nouveau Texas</span>
        </a>
    </div>
  <?php /*<div class="btn btn-dark show-on-hover mobile-btn" style="float:left;padding:0px !important;marin:0px !important;position:relative;top:5px;">
         <div class="btn-group" role="group">
    		<button id="btnGroupDrop1" type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style='background-color:transparent !important;color: white !important'><?php $Icons->Home(2);?></button>
		</div>
	</div>*/?>
	<div style='clear:both;'></div>
</nav>

<?php }
else {?>
<nav class="navbar navbar-default navbar-static-top" role="navigation" style="<?php if(isMobile()){?>border-color:#151515 !important;<?php }?>margin-bottom: 0;background-color:#151515;color:white;<?php if(!isMobile()){?>position:fixed;width:100%;<?php }?>">
    <div class="navbar-header">
        <a class="navbar-brand BankGothic" href="home.php" style='font-size:30px;color:white;'>
            <img src='https://www.nouveauelevator.com/Images/Icons/logo.png' width='30px' style='padding-right:5px;' align='left' />
            <?php if(isMobile()){?><span style='font-size:22px;'><?php }?>Nouveau Texas<?php if(isMobile()){?></span><?php }?>
        </a>
    </div>
	<?php if(!isMobile() && !($My_User['Field'] == 1 && $My_User['Title'] != 'OFFICE') && is_array($My_User)){?><div class='menu-container' style='background-color:#151515;color:white;'>
		<div class='menu'>
			<ul <?php if(!isMobile()){?>style='float:left;width:60%;'<?php }?>>
				<li><a href="dashboard.php">Home</a></li>
				<li><a href="#">Lists</a>
                    <ul>
                        <li><a href="main.php">Office</a>
                            <ul>
                                <?php if(isset($My_Privileges['Customer']) && $My_Privileges['Customer']['Other_Privilege'] >= 4){?><li><a href="customers.php"><?php $Icons->Customer();?> Customers</a></li><?php }?>
                                <?php if(isset($My_Privileges['Location']) && $My_Privileges['Location']['Other_Privilege'] >= 4){?><li><a href="locations.php"><?php $Icons->Location();?> Locations</a></li><?php }?>
                                <?php if(isset($My_Privileges['Job']) && $My_Privileges['Job']['Other_Privilege'] >= 4){?><li><a href="jobs.php"><?php $Icons->Job();?> Jobs</a></li><?php }?>
                                <?php if(isset($My_Privileges['Unit']) && $My_Privileges['Unit']['Other_Privilege'] >= 4){?><li><a href="units.php"><?php $Icons->Unit();?> Units</a></li><?php }?>
                            </ul>
                        </li>
                        <li><a href="#">Accounting</a>
                            <ul>
                                <?php if(isset($My_Privileges['Customer']) && $My_Privileges['Customer']['Other_Privilege']){?><li><a href="collections.php"><?php $Icons->Collection();?> Collections</a></li><?php }?>
								<?php if(isset($My_Privileges['Customer']) && $My_Privileges['Customer']['Other_Privilege']){?><li><a href="invoices.php"><?php $Icons->Invoice();?> Invoices</a></li><?php }?>
                            </ul>
                        </li>
                        <li><a href="#">Sales</a>
                            <ul>
                                <?php if(isset($My_Privileges['Customer']) && $My_Privileges['Customer']['Other_Privilege']){?><li><a href="leads.php"><?php $Icons->Customer();?> Leads</a></li><?php }?>
								<?php if(isset($My_Privileges['Customer']) && $My_Privileges['Customer']['Other_Privilege']){?><li><a href="proposals.php"><?php $Icons->Proposal();?> Proposals</a></li><?php }?>
								<?php if(isset($My_Privileges['Customer']) && $My_Privileges['Customer']['Other_Privilege']){?><li><a href="contracts.php"><?php $Icons->Contract();?> Contracts</a></li><?php }?>
                            </ul>
                        </li>
                        <li><a href="#">Operations</a>
                            <ul>
								<?php if(isset($My_Privileges['Customer']) && $My_Privileges['Customer']['Other_Privilege']){?><li><a href="dispatch.php"><?php $Icons->Phone();?> Dispatch</a></li><?php }?>
								<?php if(isset($My_Privileges['Customer']) && $My_Privileges['Customer']['Other_Privilege']){?><li><a href="tickets.php"><?php $Icons->Ticket();?> Tickets</a></li><?php }?>
                                <?php if(isset($My_Privileges['Customer']) && $My_Privileges['Customer']['Other_Privilege']){?><li><a href="violations.php"><?php $Icons->Violation();?> Violations</a></li><?php }?>
                                <?php if(isset($My_Privileges['Customer']) && $My_Privileges['Customer']['Other_Privilege']){?><li><a href='routes.php'><?php $Icons->Route();?> <span class=''>Routes</span></a></li><?php }?>
                            </ul>
                        </li>
						<li><a href="#">Warehouse</a>
                            <ul>
								<?php if(isset($My_Privileges['Customer']) && $My_Privileges['Customer']['Other_Privilege']){?><li><a href="rmas.php"><?php $Icons->Customer();?> RMAs</a></li><?php }?>
								<?php if(isset($My_Privileges['Customer']) && $My_Privileges['Customer']['Other_Privilege']){?><li><a href="products.php"><?php $Icons->Customer();?> Products</a></li><?php }?>
								<?php if(isset($My_Privileges['Customer']) && $My_Privileges['Customer']['Other_Privilege']){?><li><a href="product_types.php"><?php $Icons->Customer();?> Product Types</a></li><?php }?>
                                <?php if(isset($My_Privileges['Customer']) && $My_Privileges['Customer']['Other_Privilege']){?><li><a href="#"><?php $Icons->Customer();?> Items</a></li><?php }?>
                                <?php if(isset($My_Privileges['Customer']) && $My_Privileges['Customer']['Other_Privilege']){?><li><a href="requisitions.php"><?php $Icons->Customer();?> Requisitions</a></li><?php }?>
                            </ul>
                        </li>
						<li><a href="#">Purchasing</a>
                            <ul>
								<?php if(isset($My_Privileges['Customer']) && $My_Privileges['Customer']['Other_Privilege']){?><li><a href='#'><?php $Icons->Customer();?> Purchase Orders</a></li><?php }?>
								<?php if(isset($My_Privileges['Customer']) && $My_Privileges['Customer']['Other_Privilege']){?><li><a href='#'><?php $Icons->Customer();?> Purchase Order Items</a></li><?php }?>
                            </ul>
                        </li>
						<li><a href="#">Other</a>
                            <ul>
								<?php if(isset($My_Privileges['Legal']) && $My_Privileges['Legal']['Other_Privilege']){?><li><a href='legal.php'><?php $Icons->Legal();?> <span class=''>Legal</span></a></li><?php }?>
								<?php if(isset($My_Privileges['Customer']) && $My_Privileges['Customer']['Other_Privilege']){?><li><a href="permits.php"><?php $Icons->Customer();?> Permits</a></li><?php }?>
								<?php if(isset($My_Privileges['Customer']) && $My_Privileges['Customer']['Other_Privilege']){?><li><a href="insured_companies.php"><?php $Icons->Customer();?> Insurance</a></li><?php }?>
                            </ul>
                        </li>
						<?php if(isset($My_Privileges['Admin'])){?><li><a href="#">Admin</a>
                            <ul>
								<?php if(isset($My_Privileges['Connection']) && $My_Privileges['Connection']['Other_Privilege']){?><li><a href='connections.php'><?php $Icons->Connection();?> <span class=''>Connections</span></a></li><?php }?>
								<?php if(isset($My_Privileges['Privilege']) && $My_Privileges['Privilege']['Other_Privilege']){?><li><a href='privileges.php'><?php $Icons->Customer();?> <span class=''>Privileges</span></a></li><?php }?>
								<?php if(isset($My_Privileges['User']) && $My_Privileges['User']['Other_Privilege']){?><li><a href='users.php'><?php $Icons->Users();?> <span class=''>Users</span></a></li><?php }?>
                            </ul>
                        </li><?php }?>
                    </ul>
                </li>
				<li><a href="#">Reports</a>
                    <ul>
                        <li><a href="main.php">Accounting</a>
                            <ul>
								<li><a href='billed_jobs.php'><?php $Icons->Customer();?> <span class=''>Billed Jobs</span></a></li>
                               	<li><a href='job_closure.php'><?php $Icons->Customer();?> <span class=''>Job Closure</span></a></li>
								<li><a href='job_hours.php'><?php $Icons->Customer();?> <span class=''>Job Hours</span></a></li>
								<li><a href='job_labor.php'><?php $Icons->Customer();?> <span class=''>Job Labor</span></a></li>
								<li><a href='jobs_no_supervisor.php'><?php $Icons->Customer();?> <span class=''>Jobs w/ No Supervisor</span></a></li>
								<li><a href='job_tickets.php'><?php $Icons->Customer();?> <span class=''>Job Tickets</span></a></li>
								<li><a href='jobs_no_supervisor.php'><?php $Icons->Customer();?> <span class=''>Jobs Underbilled</span></a></li>
								<li><a href='location_labor.php'><?php $Icons->Location();?> <span class=''>Location Labor</span></a></li>
								<li><a href='unit_labor.php'><?php $Icons->Ticket();?> <span class=''>Unit Labor</span></a></li>
                            </ul>
                        </li>
						<li><a href="#">Sales</a>
							<ul>
								<li><a href="https://www.nouveauelevator.com/portal/proposals_for_Mike_Hannan.php"><?php $Icons->Customer();?> <span class=''>Proposals for Mike H.</span></a></li>
							</ul>
						</li>
                        <li><a href="#">Payroll</a>
                            <ul>
								<li><a href='payroll.php'><?php $Icons->Customer();?> <span class=''>Payroll</span></a></li>
								<li><a href='review.php'><?php $Icons->Customer();?> <span class=''>Review</span></a></li>
                            </ul>
                        </li>
                        <li><a href="#">Operations</a>
                            <ul>
								<li><a href='active_tickets.php'><?php $Icons->Ticket();?> <span class=''>Active Tickets</span></a></li>
								<li><a href='due_violations.php'><?php $Icons->Customer();?> <span class=''>Due Violations</span></a></li>
								<li><a href='maintenances.php'><?php $Icons->Customer();?> <span class=''>Maintenances</span></a></li>
								<li><a href='modernizations.php'><?php $Icons->Customer();?> <span class=''>Modernizations</span></a></li>
								<li><a href='outstanding_jobs.php'><?php $Icons->Customer();?> <span class=''>Outstanding Jobs</span></a></li>
								<li><a href='service_calls.php'><?php $Icons->Customer();?> <span class=''>Service Calls</span></a></li>
								<li><a href='time_sheet.php'><?php $Icons->Customer();?> <span class=''>Time Sheet</span></a></li>
                            </ul>
                        </li>
						<?php if(isset($My_Privileges['Admin'])){?><li><a href="#">Admin</a>
                            <ul>
								<li><a href='birthdays.php'><?php $Icons->Birthday();?> <span class=''>Birthdays</span></a></li>
                            </ul>
                        </li><?php }?>
                    </ul>
                </li>
				<li><a href="#">Other</a>
					<ul>
						<?php if(isset($My_Privileges['Admin'])){?><li><a href="#">Roles</a>
							<ul>
								<li><a href="admin.php">Admin</a></li>
								<li><a href="collector.php">Collector</a></li>
							</ul>
						</li><?php }?>
						<?php if(isset($My_Privileges['Admin'])){?><li><a href="#">Company Overview</a>
							<ul>
								<li><a href="overview.php">Company</a></li>
								<li><a href="maintenance.php">Maintenance</a></li>
								<li><a href="modernization.php">Modernization</a></li>
								<li><a href="repair.php">Repair</a></li>
								<li><a href="testing.php">Testing</a></li>
							</ul>
						</li><?php }?>
						<li><a href="map.php?Type=Live">Map</a></li>
						<li><a href="user.php?ID=<?php echo $My_User['ID'];?>">Profile</a></li>
					</ul>
				</li>
				<li><a href="#">Options</a>
					<ul>
						<li><a href="../login.php?Logout=TRUE">Log Out</a></li>
					</ul>
				</li>
			</ul>
			<?php if(!isMobile() && false){?><form style='height:100%;float:left;width:auto;' action='search.php' method='GET'><input name='Keyword' type='text' size='30' placeholder='Search' style='height:50px;color:black;'/></form><?php }?>
		</div>
	</div><?php }?>
</nav>
<style>
/*
- Name: megamenu.js - style.css
- Version: 1.0
- Latest update: 29.01.2016.
- Author: Mario Loncarek
- Author web site: http://marioloncarek.com
*/


/* ––––––––––––––––––––––––––––––––––––––––––––––––––
Body - not related to megamenu
–––––––––––––––––––––––––––––––––––––––––––––––––– */

body {
    font-family: 'Source Sans Pro', sans-serif;
}

* {
    box-sizing: border-box;
}

a {
    color: black;
}

.description {
    position: absolute;
    top: 50%;
    left: 50%;
    -webkit-transform: translateY(-50%);
    -ms-transform: translateY(-50%);
    transform: translateY(-50%);
    -webkit-transform: translateX(-50%);
    -ms-transform: translateX(-50%);
    transform: translateX(-50%);
}


/* ––––––––––––––––––––––––––––––––––––––––––––––––––
megamenu.js STYLE STARTS HERE
–––––––––––––––––––––––––––––––––––––––––––––––––– */


/* ––––––––––––––––––––––––––––––––––––––––––––––––––
Screen style's
–––––––––––––––––––––––––––––––––––––––––––––––––– */

.menu-container {
    width: 80%;
    margin: 0 auto;
    background: #151515;
	color:white;
}

.menu-mobile {
    display: none;
    padding: 20px;
}
.menu>ul>li>a {
	color:white;
	font-size:16px;
	font-family:"BankGothic";
}
.menu>ul>li:hover>a {
	color:black;
}

.menu-mobile:after {
    content: "\f394";
    font-family: "Ionicons";
    font-size: 2.5rem;
    padding: 0;
    float: right;
    position: relative;
    top: 50%;
    -webkit-transform: translateY(-25%);
    -ms-transform: translateY(-25%);
    transform: translateY(-25%);
}

.menu-dropdown-icon:before {
    content: "\f489";
    font-family: "Ionicons";
    display: none;
    cursor: pointer;
    float: right;
    padding: 1.5em 2em;
    background: #151515;
    color: #black;
}

.menu > ul {
    margin: 0 auto;
    width: 100%;
    list-style: none;
    padding: 0;
    position: relative;
    /* IF .menu position=relative -> ul = container width, ELSE ul = 100% width */
    box-sizing: border-box;
}

.menu > ul:before,
.menu > ul:after {
    content: "";
    display: table;
}

.menu > ul:after {
    clear: both;
}

.menu > ul > li {
    float: left;
    background: #151515;
    padding: 0;
    margin: 0;
}

.menu > ul > li a {
    text-decoration: none;
    padding: 1em 2em;
    display: block;
}

.menu > ul > li:hover {
    background: #e5e5e5;
	color:black;
}
	.menu > ul> li > a:hover {
		color:black !important;;
	}

.menu > ul > li > ul {
    display: none;
    width: 100%;
    background: #f0f0f0;
    padding: 20px;
    position: absolute;
    z-index: 99;
    left: 0;
    margin: 0;
    list-style: none;
    box-sizing: border-box;
}

.menu > ul > li > ul:before,
.menu > ul > li > ul:after {
    content: "";
    display: table;
}

.menu > ul > li > ul:after {
    clear: both;
}

.menu > ul > li > ul > li {
    margin: 0;
    padding-bottom: 0;
    list-style: none;
    width: 25%;
    background: none;

    float: left;
}

.menu > ul > li > ul > li a {
    color: black;
	font-family:'BankGothic';
	font-size:16px;
    padding: .2em 0;
    width: 95%;
    display: block;
    border-bottom: 1px solid #ccc;
}

.menu > ul > li > ul > li > ul {
    display: block;
    padding: 0;
    margin: 10px 0 0;
    list-style: none;
    box-sizing: border-box;
}

.menu > ul > li > ul > li > ul:before,
.menu > ul > li > ul > li > ul:after {
    content: "";
    display: table;
}

.menu > ul > li > ul > li > ul:after {
    clear: both;
}

.menu > ul > li > ul > li > ul > li {
    float: left;
    width: 100%;
    padding: 10px;
    margin: 0;
    font-size: .8em;
}

.menu > ul > li > ul > li > ul > li a {
    border: 0;
}
.menu > ul > li > ul > li > ul >li:hover {
	background-color:#d9d9d9 !important;
	color:white !important;
}
.menu > ul > li > ul.normal-sub {
    width: 300px;
    left: auto;
    padding: 10px 20px;
}

.menu > ul > li > ul.normal-sub > li {
    width: 100%;
}

.menu > ul > li > ul.normal-sub > li a {
    border: 0;
    padding: 1em 0;
}


/* ––––––––––––––––––––––––––––––––––––––––––––––––––
Mobile style's
–––––––––––––––––––––––––––––––––––––––––––––––––– */

@media only screen and (max-width: 959px) {
    .menu-container {
        width: 100%;
    }
    .menu-mobile {
        display: block;
    }
    .menu-dropdown-icon:before {
        display: block;
    }
    .menu > ul {
        display: none;
    }
    .menu > ul > li {
        width: 100%;
        float: none;
        display: block;
    }
    .menu > ul > li a {
        padding: 1.5em;
        width: 100%;
        display: block;
    }
    .menu > ul > li > ul {
        position: relative;
    }
    .menu > ul > li > ul.normal-sub {
        width: 100%;
    }
    .menu > ul > li > ul > li {
        float: none;
        width: 100%;
        margin-top: 20px;
    }
    .menu > ul > li > ul > li:first-child {
        margin: 0;
    }
    .menu > ul > li > ul > li > ul {
        position: relative;
    }
    .menu > ul > li > ul > li > ul > li {
        float: none;
    }
    .menu .show-on-mobile {
        display: block;
    }
}
</style>
<script>
/*global $ */
$(document).ready(function () {

    "use strict";

    $('.menu > ul > li:has( > ul)').addClass('menu-dropdown-icon');
    //Checks if li has sub (ul) and adds class for toggle icon - just an UI


    $('.menu > ul > li > ul:not(:has(ul))').addClass('normal-sub');
    //Checks if drodown menu's li elements have anothere level (ul), if not the dropdown is shown as regular dropdown, not a mega menu (thanks Luka Kladaric)

    $(".menu > ul").before("<a href=\"#\" class=\"menu-mobile\" style='color:white;'>Navigation</a>");

    //Adds menu-mobile class (for mobile toggle menu) before the normal menu
    //Mobile menu is hidden if width is more then 959px, but normal menu is displayed
    //Normal menu is hidden if width is below 959px, and jquery adds mobile menu
    //Done this way so it can be used with wordpress without any trouble

    $(".menu > ul > li").hover(
        function (e) {
            if ($(window).width() > 943) {
                $(this).children("ul").fadeIn(150);
                e.preventDefault();
            }
        }, function (e) {
            if ($(window).width() > 943) {
                $(this).children("ul").fadeOut(150);
                e.preventDefault();
            }
        }
    );
    //If width is more than 943px dropdowns are displayed on hover

    $(".menu > ul > li").click(function() {
        if ($(window).width() < 943) {
          $(this).children("ul").fadeToggle(150);
        }
    });
    //If width is less or equal to 943px dropdowns are displayed on click (thanks Aman Jain from stackoverflow)

    $(".menu-mobile").click(function (e) {
        $(".menu > ul").toggleClass('show-on-mobile');
        e.preventDefault();
    });
    //when clicked on mobile-menu, normal menu is shown as a list, classic rwd menu story (thanks mwl from stackoverflow)

});
</script>
<?php } ?>
