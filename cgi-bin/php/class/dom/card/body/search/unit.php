<?php 
namespace dom\card\body\search;
class unit {
	public function __construct( ){
		?><div class='card-body unit filters'>
	        <div class='row'>
				<div class='col-sm-4'>City ID:</div>
				<div class='col-sm-8'><input type='text' name='City_ID' placeholder='City ID' onChange='' /></div>
	        </div>
	        <div class='row'>
				<div class='col-sm-4'>Building ID:</div>
				<div class='col-sm-8'><input type='text' name='Building_ID' placeholder='Building ID' onChange='' /></div>
	        </div>
	        <div class='row'>
				<div class='col-sm-4'>Type:</div>
				<div class='col-sm-8'><select name='Type' onChange=''>
					<option value=''>Select</option><?php
					foreach( UNIT_TYPES as $UNIT_TYPE ){
						?><option value='<?php echo $UNIT_TYPE;?>'><?php echo $UNIT_TYPE;?></option><?php
					}
				?></select></div>
	        </div>
	        <div class='row'>
				<div class='col-sm-4'>Status:</div>
				<div class='col-sm-8'><select name='Status' onChange=''>
					<option value=''>Select</option>
					<option value='0'>Active</option>
					<option value='1'>Inactive</option>
				</select></div>
	        </div>
	    </div><?php
	}
}
?>