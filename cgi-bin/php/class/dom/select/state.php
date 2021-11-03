<?php
namespace dom\select;
class state {
	public function __construct( ){
            ?><select multiple name='State'><?php 
                  foreach( USA_STATES as $USA_STATE_ACRONYM=>$USA_STATE_NAME ){
                        ?><option value='<?php echo $USA_STATE_ACRONYM;?>'><?php echo $USA_STATE_NAME;?></option><?php
                  }
            ?></select><?php 
      }
}?>