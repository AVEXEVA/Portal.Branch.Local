<?php
if(!trait_exists('functional_magic_methods')){require(PROJECT_ROOT."php/traits/functional/magic_methods.php");}
if(!trait_exists('functional_magic_html')){require(PROJECT_ROOT."php/traits/functional/magical_html.php");}
class magic {
	use functional_magic_methods;
	use functional_magic_html;
}
?>