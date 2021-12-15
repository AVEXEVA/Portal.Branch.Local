<?php
namespace singleton;
class table extends \singleton\index {
	public function th( $label, $icon = null ){
		$icon = is_null( $icon ) ? $label : $icon;
		?><th class='text-white border border-white'><?php \singleton\fontawesome::getInstance( )->$icon( );?> <?php echo $label;?></th><?php
	}
	public function th_input( $label, $value ){ ?><th class='text-white border border-white'><input autocomplete='off' class='form-control redraw' type='text' name='<?php echo $label;?>' placeholder='<?php echo $label;?>' value='<?php echo $value;?>' /></th><?php }
	public function th_input_currency( $label, $value ){ ?><th class='text-white border border-white'><input autocomplete='off' class='form-control redraw' type='number' step='0.01' name='<?php echo $label;?>' placeholder='<?php echo $label;?>' value='<?php echo $value;?>' /></th><?php }
	public function th_input_date( $label, $value ){ ?><th class='text-white border border-white'><input autocomplete='off' class='form-control redraw date' type='text' name='<?php echo $label;?>' placeholder='<?php echo $label;?>' value='<?php echo $value;?>' /></th><?php }
	public function th_select( $label, $value, $options ){ ?><th class='text-white border border-white'><select name='<?php echo $label;?>' class='form-control redraw'><option value=''>Select</option><?php
		if( count( $options ) > 0 ){ foreach( $options as $k=>$v ){?><option value='<?php echo $k;?>'><?php echo $v;?></option><?php }}
	?></select></th><?php }
	public function th_autocomplete( $singular, $plural, $id, $name ){
		?><th class='text-white border border-white'><?php \singleton\bootstrap::getInstance( )->autocomplete( $singular, $plural, $id, $name, 'datatable' );?></th><?php
	}
}?>