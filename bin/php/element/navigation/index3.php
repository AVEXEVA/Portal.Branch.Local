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
<nav class="navbar navbar-default navbar-static-top" role="navigation" style="<?php if(isMobile()){?>border-color:#151515 !important;<?php }?>margin-bottom: 0;background-color:#151515;color:white;<?php if(!isMobile()){?>position:fixed;width:100%;<?php }?>">
    <div class="navbar-header">
        <a class="navbar-brand BankGothic" href="home.php" style='font-size:30px;color:white;'>
            <img src='http://www.nouveauelevator.com/Images/Icons/logo.png' width='30px' style='padding-right:5px;' align='left' />
            <?php if(isMobile()){?><span style='font-size:22px;'><?php }?>Nouveau Texas<?php if(isMobile()){?></span><?php }?>
        </a>
    </div>
</nav>
