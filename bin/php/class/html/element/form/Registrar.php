<?php 
$Form_Splash = new \DOM\Form(array(
	'ID'        => 'Registrar',
	'Class'     => '',
	'Name'      => 'Registrar',
	'Rel'       => 'Registrar',
	'Method'    => 'POST',
	'Enctype'   => 'multipart/form-data'
	'Action'    => 'CGI-BIN/PHP/POST/User/Registrar.php',
	'FieldSets' => Array(
		new FieldSet(Array(
			'Fields' => Array(
				$Input_Anonymous,
				$Input_User,
				$Input_Password,
				$Input_EXƎ
			)
		))
	),
	'Buttons'  => Array($Button_New)
));
?>