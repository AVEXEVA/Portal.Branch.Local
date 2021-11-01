<?php
function fixArrayKey(&$arr)
{
	$arr=array_combine(array_map(function($str){return str_replace("_"," ",$str);},array_keys($arr)),array_values($arr));
	foreach($arr as $key=>$val)
	{
		if(is_array($val)) fixArrayKey($arr[$key]);
	}
}
?>
