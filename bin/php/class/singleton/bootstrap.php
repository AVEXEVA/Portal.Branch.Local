<?php
namespace singleton;
class bootstrap extends \singleton\index {
	public function primary_card_header( $singular, $plural, $primary_key ){
		?><div class='card-heading'>
          <div class='row g-0 px-3 py-2'>
            <div class='col-12 col-lg-6'>
              <h5><?php \singleton\fontawesome::getInstance( )->$singular( 1 );?><a href='<?php echo strtolower( $plural );?>.php?<?php
                echo http_build_query( is_array( $_SESSION[ 'Tables' ][ $plural ][ 0 ] ) ? $_SESSION[ 'Tables' ][ $plural ][ 0 ] : array( ) );
              ?>'><?php echo $singular;?></a>: <span><?php
                echo is_null( $primary_key )
                    ? 'New'
                    : '#' . $primary_key;
              ?></span></h5>
            </div>
            <div class='col-6 col-lg-3'>
              <div class='row g-0'>
                <div class='col-4'>
                  <button
                    class='form-control rounded'
                    onClick="document.location.href='<?php echo strtolower( $singular );?>.php';"
                    type='submit'
                  ><?php \singleton\fontawesome::getInstance( 1 )->Save( 1 );?><span class='desktop'> Save</span></button>
                </div>
                <div class='col-4'>
                  <button
                    class='form-control rounded'
                    onClick="document.location.href='<?php echo strtolower( $singular );?>.php?ID=<?php echo $primary_key;?>';"
                    type='button'
                  ><?php \singleton\fontawesome::getInstance( 1 )->Refresh( 1 );?><span class='desktop'> Refresh</span></button>
                </div>
                <div class='col-4'>
                  <button
                    class='form-control rounded'
                    onClick="document.location.href='<?php echo strtolower( $singular );?>.php';"
                    type='button'
                  ><?php \singleton\fontawesome::getInstance( 1 )->Add( 1 );?><span class='desktop'> New</span></button>
                </div>
              </div>
            </div>
            <div class='col-6 col-lg-3'>
              <div class='row g-0'>
                <div class='col-4'>
                  <button
                    type='button'
                    class='form-control rounded'
                    onClick="document.location.href='<?php echo strtolower( $singular );?>.php?ID=<?php echo !is_null( $primary_key ) ? array_keys( $_SESSION[ 'Tables' ][ $plural ], true )[ array_search( $primary_key, array_keys( $_SESSION[ 'Tables' ][ $plural ], true ) ) - 1 ] : null;?>';"
                  ><?php \singleton\fontawesome::getInstance( 1 )->Previous( 1 );?><span class='desktop'> Previous</span></button></div>
                <div class='col-4'>
                  <button
                    type='button'
                    class='form-control rounded'
                    onClick="document.location.href='<?php echo strtolower( $plural );?>.php?<?php echo http_build_query( is_array( $_SESSION[ 'Tables' ][ $plural ][ 0 ] ) ? $_SESSION[ 'Tables' ][ $plural ][ 0 ] : array( ) );?>';"
                  ><?php \singleton\fontawesome::getInstance( 1 )->Table( 1 );?><span class='desktop'> Table</span></button></div>
                <div class='col-4'>
                  <button
                    type='button'
                    class='form-control rounded'
                    onClick="document.location.href='<?php echo strtolower( $singular );?>.php?ID=<?php echo !is_null( $primary_key )? array_keys( $_SESSION[ 'Tables' ][ $plural ], true )[ array_search( $primary_key, array_keys( $_SESSION[ 'Tables' ][ $plural ], true ) ) + 1 ] : null;?>';"
                  ><?php \singleton\fontawesome::getInstance( 1 )->Next( 1 );?><span class='desktop'> Next</span></button></div>
              </div>
            </div>
          </div>
        </div><?php
	}
  public function card_map( $element, $title, $latitude, $longitude ){
    if( !in_array( $latitude, array( null, 0 ) ) && !in_array( $longitude, array( null, 0 ) ) ){
      ?><div class='card card-primary my-3 col-12 col-lg-3'>
        <?php self::card_header( 'Map' );?>
        <div id='<?php echo $element;?>' class='card-body p-0 bg-dark position-relative overflow-hidden' style='width:100%;height:350px;z-index:0;<?php echo isset( $_SESSION[ 'Cards' ][ 'Map' ] ) && $_SESSION[ 'Cards' ][ 'Map' ] == 0 ? 'display:none;' : null;?>'></div>
        <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB05GymhObM_JJaRCC3F4WeFn3KxIOdwEU"></script>
        <script type="text/javascript">
          var map;
          function initialize() {
            map = new google.maps.Map(
              document.getElementById( '<?php echo $element;?>' ),
              {
                zoom: 10,
                center: new google.maps.LatLng( <?php echo $latitude;?>, <?php echo $longitude;?> ),
                mapTypeId: google.maps.MapTypeId.ROADMAP
              }
            );
            var markers = [];
            markers[0] = new google.maps.Marker({
              position: {
                lat:<?php echo $latitude;?>,
                lng:<?php echo $longitude;?>
              },
              map: map,
              title: '<?php echo $title;?>'
            });
          }
          $(document).ready(function(){ initialize(); });
        </script>
      </div><?php
    }
  }
	public function card_header( $label, $singular = null, $plural = null, $reference = null, $id = null ){
    ?><div class='card-heading'>
      <div class='row g-0 px-3 py-2'>
        <div class='col-8'><h5><?php \singleton\fontawesome::getInstance( )->$label( 1 );?><span><?php echo $label;?></span></h5></div>
        <?php if( !empty( $id ) ){?>
          <div class='col-2'><button type='button' class='h-100 w-100' onClick="document.location.href='<?php echo strtolower( $singular );?>.php?<?php echo $reference;?>=<?php echo $id;?>';"><?php \singleton\fontawesome::getInstance( )->Add( 1 );?></button></div>
          <div class='col-2'><button type='button' class='h-100 w-100' onClick="document.location.href='<?php echo strtolower( $plural );?>.php?<?php echo $reference;?>=<?php echo $id;?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
        <?php } else {?>
          <div class='col-2'>&nbsp;</div>
          <div class='col-2'>&nbsp;</div>
        <?php }?>
      </div>
    </div><?php
  }
  public function card_row_label( $label, $col = '4', $icon = null ){
    $icon = empty( $icon ) ? $label : $icon;
    ?><label class='col-<?php echo $col;?> border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->$icon( 1 );?> <?php echo str_replace( '_', ' ', $label );?>:</label><?php
  }
	public function card_row_form_aggregated( $label, $onclick = null ){
		?><div class='row g-0'>
      <?php self::card_row_label( $label );?>
      <div class='col-6'>&nbsp;</div>
      <?php if( !is_null( $onclick ) ){
        ?><div class='col-2'><a href="<?php echo $onclick;?>"><button class='h-100 w-100' type='button'><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></a></div><?php
      } else {
        ?><div class='col-2'>&nbsp;</div><?php
      }?>
    </div><?php
	}
  public function card_row_form_input( $label, $value, $sub = false, $disabled = false, $onclick = false, $classes = 'edit' ){
    ?><div class='row g-0'>
      <?php echo $sub ? "<div class='col-1'>&nbsp;</div>" : null;?>
      <?php self::card_row_label( $label, $sub ? 3 : 4 );?>
      <div class='col-<?php echo $onclick ? 6 : 8;?>'><input <?php echo $disabled ? 'disabled' : null;?> placeholder='<?php echo $label;?>' type='text' class='form-control <?php echo $classes;?>' name='<?php echo $label;?>' value='<?php echo $value;?>' /></div>
      <?php if( $onclick ){?>
        <div class='col-2'><button type='button' class='h-100 w-100' onClick="document.location.href='<?php echo $onclick;?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
      <?php }?>
    </div><?php
  }
	public function card_row_form_input_sub( $label, $value ){
		?><div class='row g-0'>
			<div class='col-1'>&nbsp;</div>
			<?php self::card_row_label( $label, 3 );?>
			<div class='col-8'><input placeholder='<?php echo $label;?>' type='text' class='form-control edit' name='<?php echo $label;?>' value='<?php echo $value;?>' /></div>
        </div><?php
	}
	public function card_row_form_input_url( $label, $value ){
		?><div class='row g-0'>
			<?php self::card_row_label( $label );?>
			<div class='col-8'><input placeholder='https://www.domain.com' type='text' class='form-control edit' name='<?php echo $label;?>' value='<?php echo $value;?>' /></div>
        </div><?php
	}
  public function card_row_form_input_checkbox( $label, $value ){
    ?><div class='row g-0'>
      <?php self::card_row_label( $label );?>
      <div class='col-8'><input type='checkbox' class='form-input edit' name='<?php echo $label;?>' value='<?php echo $value ?>' <?php  echo $value == 1  ? 'checked' : ''; ?> /></div>
    </div><?php
  }
	public function card_row_form_input_tel( $label, $value ){
		?><div class='row g-0'>
			<?php self::card_row_label( $label );?>
			<div class='col-8'><input placeholder='(XXX) XXX-XXXX' type='text' class='form-control edit' name='<?php echo $label;?>' value='<?php echo $value;?>' /></div>
    </div><?php
	}
	public function card_row_form_input_email( $label, $value ){
		?><div class='row g-0'>
			<?php self::card_row_label( $label );?>
			<div class='col-8'><input placeholder='email@domain.com' type='text' class='form-control edit' name='<?php echo $label;?>' value='<?php echo $value;?>' /></div>
    </div><?php
	}
  public function card_row_form_input_password( $label, $value ){
    ?><div class='row g-0'>
      <?php self::card_row_label( $label );?>
      <div class='col-8'><input placeholder='password' type='password' class='form-control edit' name='<?php echo $label;?>' value='<?php echo $value;?>' /></div>
    </div><?php
  }
	public function card_row_form_input_date( $singular, $value, $label = null ){
    $label = is_null( $label ) ? $singular : $label;
		?><div class='row g-0'>
			<?php self::card_row_label( $label, 4, 'Date' );?>
			<div class='col-8'><input placeholder='mm/dd/yy' class='form-control date edit' autocomplete='off' name='<?php echo $singular;?>' value='<?php echo empty( $value ) || $value == '1969-12-31 00:00:00.000' ? null : date( 'm/d/Y', strtotime( $value ) );?>' /></div>
    </div><?php
	}
	public function card_row_form_input_currency( $singular, $value ){
		?><div class='row g-0'>
			<?php self::card_row_label( $singular, 4, 'Currency' );?>
			<div class='col-8'><input placeholder='$.00' type='number' min='0.00' max='999999999999' class='form-control edit' autocomplete='off' name='<?php echo $singular;?>' value='<?php echo empty( $value ) ? null : $value;?>' /></div>
    </div><?php
	}
  public function card_row_form_input_number( $singular, $value, $label = null ){
    $label = is_null( $label ) ? $singular : $label;
    ?><div class='row g-0'>
      <?php self::card_row_label( $label, 4, 'Number' );?>
      <div class='col-8'><input placeholder='0' type='number' min='0' max='999999999999' class='form-control edit' autocomplete='off' name='<?php echo $singular;?>' value='<?php echo empty( $value ) ? null : $value;?>' /></div>
    </div><?php
  }
  public function card_row_form_input_sub_number( $singular, $value, $label = null ){
    $label = is_null( $label ) ? $singular : $label;
    ?><div class='row g-0'>
      <div class='col-1'>&nbsp;</div>
      <?php self::card_row_label( $label, 3, 'Number' );?>
      <div class='col-8'><input placeholder='0' type='number' min='0' max='999999999999' class='form-control edit' autocomplete='off' name='<?php echo $singular;?>' value='<?php echo empty( $value ) ? null : $value;?>' /></div>
    </div><?php
  }
	public function card_row_form_select( $label, $value, $options ){
		?><div class='row g-0'>
			<?php self::card_row_label( $label );?>
			<div class='col-8'><select name='<?php echo $label;?>' class='form-control'>
				<option value=''>Select</option>
				<?php if( is_array( $options ) && count( $options ) > 0 ){ foreach( $options as $k=>$v ){
					?><option value='<?php echo $k;?>' <?php echo $value == $k ? 'selected' : null;?>><?php echo $v;?></option>
				<?php } }?>
			</select></div>
		</div><?php
	}
	public function card_row_form_select_sub( $label, $value, $options ){
		?><div class='row g-0'>
			<div class='col-1'>&nbsp;</div>
			<?php self::card_row_label( $label, 3 );?>
			<div class='col-8'><select name='<?php echo $label;?>' class='form-control'>
				<option value=''>Select</option>
				<?php if( is_array( $options ) && count( $options ) > 0 ){ foreach( $options as $k=>$v ){
					?><option value='<?php echo $k;?>' <?php echo $value == $k ? 'selected' : null;?>><?php echo $v;?></option>
				<?php } }?>
			</select></div>
		</div><?php
	}
	public function card_row_form_autocomplete( $singular, $plural, $id, $name, $requestTypes = ''  ){
        $autocompleteRequest = !empty( $requestTypes ) ? 'notSubmitted' : 'form';
		?><div class='row g-0'>
      <?php self::card_row_label( $singular, 4 );?>
      <div class='col-6'>
        <?php self::autocomplete( $singular, $plural, $id, $name, $autocompleteRequest );?>
      </div>
      <div class='col-2'><button class='h-100 w-100' type='button' <?php
        if( in_array( $id, array( null, 0, '', ' ') ) ){
          echo "onClick=\"document.location.href='" . strtolower( $plural  ) . ".php';\"";
        } else {
          echo "onClick=\"document.location.href='" . strtolower( $singular ) . ".php?ID=" . $id . "';\"";
        }
      ?>><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
    </div><?php
	}
  public function autocomplete( $singular, $plural, $id, $name, $type, $Key_Name = null ){
    $Key_Name = is_null( $Key_Name ) ? $singular . '_ID' : $Key_Name;
  	$class = '';
  	switch( $type ){
  		case 'form':
  			$class = 'edit';
  			break;
        case 'notSubmitted':
            $class = 'edit';
            break;
  		case 'datatable':
  			$class = 'redraw';
  			break;
  	}
    ?><input placeholder='<?php echo $singular;?>' type='hidden' autocomplete='off' class='form-control <?php echo $class;?>' name='<?php echo $Key_Name;?>' value='<?php echo $id;?>' />
    <input placeholder='<?php echo $singular;?>' type='text' autocomplete='off' class='form-control' name='<?php echo $singular;?>_Name' value='<?php echo $name;?>' />
    <script>
      $( 'input[name="<?php echo $singular;?>_Name"]' )
        .typeahead({
          minLength : 4,
          hint: true,
          highlight: true,
          limit : 5,
          display : 'FieldValue',
          source: function( query, result ){
            $.ajax({
              url : 'bin/php/get/autocomplete/<?php echo $plural;?>.php',
              method : 'GET',
              data    : {
                search :  $('input:visible[name="<?php echo $singular;?>_Name"]').val( )
              },
              dataType : 'json',
              beforeSend : function( ){
                  abort( );
              },
              success : function( data ){
                result( $.map( data, function( item ){
                    return item;
                } ) );
              }
            });
          },
			displayText: function(item) { return item.FieldValue; },
          	afterSelect: function( item ){<?php
          	switch( $type ){
          		case 'form':?>
          			$( 'input[name="<?php echo $singular;?>_ID"]').val( item.ID );
            		//$( 'input[name="<?php echo $singular;?>_ID"]').closest( 'form' ).submit( );
            		<?php break;
                case 'notSubmitted':?>
                $( 'input[name="<?php echo $singular;?>_ID"]').val( item.ID );
                <?php break;
            	case 'datatable':
            		break;
          	}?>
          }
        }
      );
    </script><?php
  }

	public function card_row_form_textarea( $singular, $value ){
		?><div class='row g-0'>
      <?php self::card_row_label( $singular );?>
      <div class='col-12'><textarea class='form-control' name='<?php echo $singular;?>' rows='8' placeholder='<?php echo $singular;?>'><?php echo $value;?></textarea></div>
    </div><?php
	}
}?>
