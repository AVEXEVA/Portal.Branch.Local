<?php
namespace singleton;
class table extends \singleton\index {
	public function th( $label, $icon = null ){
		$icon = is_null( $icon ) ? $label : $icon;
		?><th class='text-white border border-white'><?php \singleton\fontawesome::getInstance( )->$icon( );?> <?php echo str_replace( '_', ' ', $label );?></th><?php
	}
	public function th_hide( $label, $icon = null ){
		$icon = is_null( $icon ) ? $label : $icon;
		?><th class='text-white border border-white hidden'><?php \singleton\fontawesome::getInstance( )->$icon( );?> <?php echo str_replace( '_', ' ', $label );?></th><?php
	}
	public function th_2( $label, $icon = null ){
		$icon = is_null( $icon ) ? $label : $icon;
		?><th class='text-white border border-white' colspan='2'><?php \singleton\fontawesome::getInstance( )->$icon( );?> <?php echo str_replace( '_', ' ', $label );?></th><?php
	}
	public function th_3( $label, $icon = null ){
		$icon = is_null( $icon ) ? $label : $icon;
		?><th class='text-white border border-white' colspan='3'><?php \singleton\fontawesome::getInstance( )->$icon( );?> <?php echo str_replace( '_', ' ', $label );?></th><?php
	}
	public function th_4( $label, $icon = null ){
		$icon = is_null( $icon ) ? $label : $icon;
		?><th class='text-white border border-white'><?php \singleton\fontawesome::getInstance( )->$icon( );?> <?php echo str_replace( '_', ' ', $label );?></th><?php
	}
	public function th_input( $label, $value ){ ?><th class='text-white border border-white'><input autocomplete='off' class='form-control redraw' type='text' name='<?php echo $label;?>' placeholder='<?php echo str_replace( '_', ' ', $label );?>' value='<?php echo $value;?>' /></th><?php }
	public function th_input_entity( $Entity, $ID, $Name ){ ?><th class='text-white border border-white'>
		<input autocomplete='off' class='form-control redraw' type='hidden' name='<?php echo $Entity;?>_ID' placeholder='<?php echo $Entity;?>' value='<?php echo $ID;?>' />
		<input autocomplete='off' class='form-control redraw' type='text' name='<?php echo $Entity;?>_Name' placeholder='<?php echo $Entity;?>' value='<?php echo $Name;?>' />
	</th><?php }
	public function th_input_currency( $label, $value ){ ?><th class='text-white border border-white'><input autocomplete='off' class='form-control redraw' type='number' step='0.01' name='<?php echo $label;?>' placeholder='<?php echo $label;?>' value='<?php echo $value;?>' /></th><?php }
	public function th_input_date( $label, $value ){ ?><th class='text-white border border-white'>
		<div class='row'>
			<div class='col-12'><input autocomplete='off' class='form-control redraw date' type='text' name='<?php echo $label;?>_Start' placeholder='<?php echo $label;?> Start' value='<?php echo $value;?>' /></div>
			<div class='col-12'><input autocomplete='off' class='form-control redraw date' type='text' name='<?php echo $label;?>_End' placeholder='<?php echo $label;?> End' value='<?php echo $value;?>' /></div>
		</div>
	</th><?php }
	public function th_select( $label, $value, $options ){ ?><th class='text-white border border-white'><select name='<?php echo $label;?>' class='form-control redraw'><option value=''>Select</option><?php
		if( count( $options ) > 0 ){ foreach( $options as $k=>$v ){?><option value='<?php echo $k;?>' <?php echo $value == $k && $value != '' ? 'selected' : null;?>><?php echo $v;?></option><?php }}
	?></select></th><?php }
	public function th_autocomplete( $singular, $plural, $id, $name ){
		?><th class='text-white border border-white'><?php \singleton\bootstrap::getInstance( )->autocomplete( $singular, $plural, $id, $name, 'datatable' );?></th><?php
	}
}?>
