<?php
namespace data;
class _id extends \data\_integer { 
	public function __validate( ){
		return 		parent::__get( 'integer' ) > 0 
				&& 	parent::__validate( );
	}
}?>
