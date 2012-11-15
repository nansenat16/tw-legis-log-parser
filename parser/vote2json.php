<?php
include('./parser_main.php');

$data_dir='../data';

$dir=opendir($data_dir);

$people_count=array();

while($dirname=readdir($dir)){
	if(in_array($dirname,array('.','..','.DS_Store'))){
		continue;
	}

	echo $dirname."\r\n";
	
	$f_path=$data_dir.'/'.$dirname;
	$f=fopen($f_path,'r');
	$file_str=fread($f,filesize($f_path));
	fclose($f);
	
	//echo $file_str;
	$parser=new parser_main();
	$parser->load($file_str);
	
	
	//記名投票資料
	$vote=$parser->get('vote');
	//print_r($vote);
	if(count($vote)>0){
		$json=json_encode($vote);
		$f=fopen('../json/vote/vote_'.substr($dirname,0,strlen($dirname)-4).'.json','w');
		fwrite($f,$json);
		fclose($f);
	}
}

?>
