
        <div class='panel-heading'>Unit Details</div>
        <div class='panel-body'>
            <div class='row'>
                <div class='col-xs-4'><b>ID:</b></div>
                <div class='col-xs-8'><a href="unit.php?ID=<?php echo $Unit['Unit_ID'];?>"><?php echo $Unit["Unit_ID"];?></a></div>
            </div>
            <div class='row'>
                <div class='col-xs-4'><b>Label:</b></div>
                <div class='col-xs-8'><?php echo $Unit["Unit_Label"];?></div>
            </div>
            <div class='row'>
                <div class='col-xs-4'><b>State:</b></div>
                <div class='col-xs-8'><?php echo $Unit["Unit_State"];?></div>
            </div>
            <div class='row'>
                <div class='col-xs-4'><b>Category:</b></div>
                <div class='col-xs-8'><?php echo $Unit["Unit_Category"];?></div>
            </div>
            <div class='row'>
                <div class='col-xs-4'><b>Type:</b></div>
                <div class='col-xs-8'><?php echo $Unit["Unit_Type"];?></div>
            </div>
        </div>