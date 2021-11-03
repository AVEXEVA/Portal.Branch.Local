<?php 
$Form_Settings = new \DOM\Form(array(
	'ID'        => 'Settings',
	'Class'     => '',
	'Name'      => 'Settings',
	'Rel'       => 'Settings',
	'Method'    => 'POST',
	'Enctype'   => 'multipart/form-data'
	'Action'    => 'CGI-BIN/PHP/POST/User/Settings.php',
	'FieldSets' => Array(
		new FieldSet(Array(
			'Fields' => Array(
				new Input(Array(
					'Name'        => 'User_EXƎ_Link',
					'Placeholder' => 'User EXƎ Link'				
				))
			)
		))
	),
	'Buttons'  => Array(
		new Button(
			'Rel'  => 'x=g%0{i{%{(0)}{e%{101}{i(e)}}}}(e)={g%0%e{i+e}}}'
			'Type' => 'Submit'
		)
	)
));
?>