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
	
	//記錄標題
	//$title=$parser->get('title');
	//echo $title."\r\n";
	
	//出席資料
	//$people=$parser->get('people');
	//print_r($people);
	
	//記名投票資料
	//$vote=$parser->get('vote');
	//print_r($vote);
	
	//請假統計
	$people=$parser->get('people');
	//print_r($people);
	if($people['leave_num']>0){
		//print_r($people['leave']);
		for($n=0;$n<count($people['leave']);$n++){
			if(array_key_exists($people['leave'][$n],$people_count)){
				$people_count[$people['leave'][$n]]++;
			}else{
				$people_count[$people['leave'][$n]]=1;
			}
		}
	}
}
print_r($people_count);

?>