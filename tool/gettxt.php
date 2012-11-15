<?php

$list='./journals_link.json';

$str=fread(fopen($list,'r'),filesize($list));

$list=json_decode($str,true);

//print_r($list);

while($key=key($list)){
	
	$path=$list[$key]['txt'];
	$url='http://opentw.net63.net/';
	$cmd='wget '.$url.$path;

	echo $cmd."\r\n";

	exec($cmd);
	
	next($list);

}

?>
