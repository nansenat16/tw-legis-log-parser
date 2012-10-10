<?php

class parser_main{
	private $data=null;
	public function load($str){
		$this->data=explode("\n", $str);
	}
	
	private function get_title(){
		$title=false;
		reset($this->data);
		for($n=0;$n<count($this->data);$n++){
			if(trim($this->data[$n])=='議事錄' ){
				$title=true;
				continue;
			}
			if($title){
				if(trim($this->data[$n])!==''){
					return trim($this->data[$n]);
				}
			}
		}
	}
	
	private function get_num($str){
		$str=trim($str);
		$n=mb_strpos($str,'：',0,'utf-8');
		if($n===false){
			$n=mb_strpos($str,'　',0,'utf-8');
		}
		return (int)mb_substr($str,$n+1,mb_strlen($str,'utf-8')-$n-2,'utf-8');
	}
	
	private function fix_endspace($str){
		$str=trim($str);
		//去除結尾多餘的全型空白
		for($n=mb_strlen($str,'utf-8')-1;$n>0;$n--){
			$c=mb_substr($str,-1,1,'utf-8');
			if($c=='　'){
				$str=mb_substr($str,0,$n,'utf-8');
			}else{
				break;
			}
		}
		return $str;
	}
	
	private function name_list($str,$item_len=5){
		$str=$this->fix_endspace($str);
		$tmp_name=explode('　　', mb_substr($str,$item_len,mb_strlen($str,'utf-8'),'utf-8'));
		for($n=0;$n<count($tmp_name);$n++){
			if(mb_strlen($tmp_name[$n],'utf-8')>4){
				$tmp=explode('　',$tmp_name[$n]);
				$tmp_name[$n]=$tmp[0];
				for($m=1;$m<count($tmp);$m++){
					if(trim($tmp[$m])!==''){
						$tmp_name[]=$tmp[$m];
					}
				}
			}
		}
		return $tmp_name;
	}
	
	private function get_people(){
		$user['join']=null;
		$user['join_num']=-1;
		$user['leave']=null;
		$user['leave_num']=-1;

		reset($this->data);
		for($n=0;$n<count($this->data);$n++){
			$item=mb_substr($this->data[$n],0,4,'utf-8');
			if($item=='出席委員'){$user['join']=trim($this->data[$n]);}
			if($item=='委員出席'){$user['join_num']=$this->data[$n];}			
			if($item=='請假委員'){$user['leave']=$this->data[$n];}
			if($item=='委員請假'){$user['leave_num']=$this->data[$n];break;}

		}
		//print_r($user);
		
		$user['join_num']=$this->get_num($user['join_num']);
		$user['leave_num']=$this->get_num($user['leave_num']);
		$user['join']=$this->name_list($user['join']);
		if($user['leave_num']>0){
			$user['leave']=$this->name_list($user['leave']);
		}
		//print_r($user);
		
		if(count($user['join'])!=$user['join_num']){
			return false;
		}
		if($user['leave_num']>-1 and count($user['leave'])!=$user['leave_num']){
			return false;
		}
		
		return $user;
	}
	
	private function get_vote(){
		reset($this->data);
		$start=false;
		$vote_str='';
		$key=array(
			array(9,'記名投票表決結果：'),
			array(9,'記名表決結果名單：'));
			
		for($n=0;$n<count($this->data);$n++){
			for($m=0;$m<count($key);$m++){
				if(mb_substr($this->data[$n],(0-$key[$m][0]),$key[$m][0],'utf-8')==$key[$m][1]){
					$start=true;
					continue 2;
				}
			}
			if($start){
				$vote_str.=$this->data[$n]."\r\n";
			}
		}
		
		$vote=$this->parser_vote($vote_str);
		return $vote;
	}
	
	private function parser_vote($str){
		$tmp_str=explode("\r\n",$str);
		$vote=array();
		$tpl_vote=array('title'=>'','yes_num'=>-1,'yes_list'=>null,'no_num'=>-1,'no_list'=>null,'pass_num'=>-1,'pass_list'=>null);
		$tmp_vote=array('title'=>'','yes_num'=>-1,'yes_list'=>null,'no_num'=>-1,'no_list'=>null,'pass_num'=>-1,'pass_list'=>null);
		
		for($n=0;$n<count($tmp_str);$n++){
			$item=mb_substr(trim($tmp_str[$n]),0,2,'utf-8');
			//echo $item;
			if($item=='贊成'){$tmp_vote['yes_num']=$this->get_num($tmp_str[$n]);continue;}
			if(trim($tmp_str[$n])==''){continue;}
			if($tmp_vote['yes_num']==-1){$tmp_vote['title']=$tmp_str[$n];}
			if($tmp_vote['yes_num']>-1 and $tmp_vote['yes_list']==null){$tmp_vote['yes_list']=$this->name_list($tmp_str[$n],0);}
			
			if($item=='反對'){$tmp_vote['no_num']=$this->get_num($tmp_str[$n]);continue;}
			if($tmp_vote['no_num']>-1 and $tmp_vote['no_list']==null){$tmp_vote['no_list']=$this->name_list($tmp_str[$n],0);}
			
			if($item=='棄權'){
				$tmp_vote['pass_num']=$this->get_num($tmp_str[$n]);
				if($tmp_vote['pass_num']>0){continue;}
			}
			if($tmp_vote['pass_num']>0 and $tmp_vote['pass_list']==null){$tmp_vote['pass_list']=$this->name_list($tmp_str[$n],0);}
			
			if($tmp_vote['pass_num']>-1){
				$vote[]=$tmp_vote;
				$tmp_vote=$tpl_vote;
			}
		}
		
		return $vote;
	}
	
	public function get($type){
		if($type=='title'){
			return $this->get_title();
		}
		if($type=='people'){
			return $this->get_people();
		}
		if($type=='vote'){
			return $this->get_vote();
		}
	}
}


?>