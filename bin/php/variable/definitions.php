<?php 

define('PROJECT_ROOT', '/var/www/html/Portal.Branch.Local/bin/');
define( 
	'bin', 
	'/var/www/html/Portal.Branch.Local/bin/'
);
define( 
	'bin_meta', 
	bin . 'meta/'
);
define(
	'bin_php',
	bin . 'php/'
); 
define( 
	'bin_js', 
	bin . 'js/'
);
define(
	'bin_css',
	bin . 'css/'
);
define(
	'bin_library',
	bin . 'library/'
);
define( 'privilege_read', 8 );
define( 'privilege_write', 4 );
define( 'privilege_delete', 2 );
define( 'privilege_exec', 1 );
define( 'privilege_execute', 1 );
define( 'level_internet', 0 );
define( 'level_token', ( 4 * 1 ) );
define( 'level_other', ( 4 * 2 ) );
define( 'level_server', ( 4 * 3 ) );
define( 'level_database', ( 4 * 4 ) );
define( 'level_department', ( 4 * 5 ) );
define( 'level_group', ( 4 * 6 ) );
define( 'level_owner', ( 4 * 7 ) );
?>