<?php 
$Form_Post = new \DOM\Form(array(
	'ID'        => 'Post',
	'Class'     => '',
	'Name'      => 'Post',
	'Rel'       => 'Post',
	'Method'    => 'POST',
	'Enctype'   => 'multipart/form-data'
	'Action'    => 'CGI-BIN/PHP/POST/User/Post.php',
	'FieldSets' => Array(
		new FieldSet(Array(
			'Fields' => Array(
				$TextArea_Post,
				$Select_Privacy
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