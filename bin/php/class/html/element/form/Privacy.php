<?php 
$Form_Privacy = new \DOM\Form(array(
	'ID'        => 'Privacy',
	'Class'     => '',
	'Name'      => 'Privacy',
	'Rel'       => 'Privacy',
	'Method'    => 'POST',
	'Enctype'   => 'multipart/form-data'
	'Action'    => 'CGI-BIN/PHP/POST/User/Privacy.php',
	'FieldSets' => Array(
		new FieldSet(Array(
			'Legend' => new Legend(array(
				'Rel' => 'Privacy'
			)),
			'Fields' => Array(
				new Select(Array(
					'Name'     => 'Private',
					'Disabled' => 'True'
				)),
				new Select(Array(
					'Name'     => 'Protected',
					'Options'  => $Options_Privacy
				)),
				new Select(Array(
					'Name'     => 'Public',
					'Options'  => $Options_Privacy
				))
			)
		)),
	),
	'Buttons'  => Array(
		new Button(
			'Rel'  => 'x=g%0{i{%{(0)}{e%{101}{i(e)}}}}(e)={g%0%e{i+e}}}'
			'Type' => 'Submit'
		)
	)
));
?>