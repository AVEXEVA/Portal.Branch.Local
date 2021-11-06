<div class='panel-heading'>Customer Details</div>
<div class='panel-body'>
    <div class='row'>
        <div class='col-xs-4'><b>Customer:</b></div>
        <div class='col-xs-8'><?php if($My_Privileges['Customer']['Group_Privilege'] >= 4){?><a href="customer.php?ID=<?php echo $Customer['Customer_ID'];?>"><?php }?><?php echo $Customer['Customer'];?><?php if($My_Privileges['Customer']['Group_Privilege'] >= 4){?></a><?php }?></div>
    </div>
    <div class='row'>
        <div class='col-xs-4'><b>Street:</b></div>
        <div class='col-xs-8'><?php echo $Customer["Customer_Street"];?></div>
    </div>
    <div class='row'>
        <div class='col-xs-4'><b>City:</b></div>
        <div class='col-xs-8'><?php echo $Customer["Customer_City"];?></div>
    </div>
    <div class='row'>
        <div class='col-xs-4'><b>State:</b></div>
        <div class='col-xs-8'><?php echo $Customer["Customer_State"];?></div>
    </div>
    <div class='row'>
        <div class='col-xs-4'><b>Zip:</b></div>
        <div class='col-xs-8'><?php echo $Customer["Customer_Zip"];?></div>
    </div>
    <div class='row'>
        <div class='col-xs-4'><b>Contact:</b></div>
        <div class='col-xs-8'><?php echo $Customer["Contact"];?></div>
    </div>
    <div class='row'>
        <div class='col-xs-4'><b>Remarks:</b></div>
        <div class='col-xs-8'><?php echo $Customer["Remarks"];?></div>
    </div>
    <div class='row'>
        <div class='col-xs-4'><b>Email:</b></div>
        <div class='col-xs-8'><?php echo $Customer["Email"];?></div>
    </div>
    <div class='row'>
        <div class='col-xs-4'><b>Cellular:</b></div>
        <div class='col-xs-8'><?php echo $Customer["Cellular"];?></div>
    </div>
</div>