<?
require_once('abstract_working_group.php');


//����������� ������ ������� � ������ � �����/������ ������ �� ������������� �������, � ����������� �� ��������� ��������
class Abstract_WorkingKindGroup extends Abstract_WorkingGroup {
	 
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='sched_working';
		$this->pagename='view.php';		
		$this->subkeyname='sched_id';	
		$this->vis_name='is_shown';		
		
		
		 
	}
	
	
	
	//������ �������
	public function GetItemsByIdArr($id, $kind_id){
		$arr=array();
		
		$sql='select p.*
		from '.$this->tablename.' as p
		 
		where
		'.$this->subkeyname.'="'.$id.'"
		and kind_id="'.$kind_id.'"
		 
		order by p.id asc';
		
		 
		 
		
		//echo $sql."<p>";
		
		$set=new MysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			 
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			 
			
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	//����� ����� ���������� ������ - ������������� ���
	public function CalcWorkingTime($id, $kind_id, &$formatted, &$arr, &$working){
		$working=false;
		$working_time=0; $formatted=''; $arr=array();
		
		$arr=$this->GetItemsByIdArr($id, $kind_id);
		
		
		$last_record=array();
		
		foreach($arr as $k=>$v){
			//���� ����� �����
			if($v['in_or_out']==0){
				
				//��� ���� ����� ����� ���� ����� ������
				//��� ��������� ����� ��� in_or_out==1
				//���� ��� ��� ���������� - �� ��������� ������
				$found=false; $out_data=array();	
				foreach($arr as $k1=>$v1){
					if($k1>$k){
						if($v1['in_or_out']==1){
							$found=true;
							$out_data=$v1;
						}
						break;
					}
				}
				
				if($found){
					$delta=$out_data['pdate']-$v['pdate'];	
				}else $delta=0;
				
				$working_time+=$delta;
			}
			$last_record=$v;
		}
		
		if(($last_record!=array())&&($last_record['in_or_out']==0)){
			//�������� ������� ����� ���� �������� � ������
			
			$working_time+=(time()-$last_record['pdate']);	
			$working=$working||true;	
		}
		
		
		$days=floor($working_time/(24*60*60));
		
		$hours = floor(($working_time - $days*24*60*60)/(60*60));
		
		$mins= floor(($working_time - $days*24*60*60 - $hours*60*60)/(60));
		
		$secs=$working_time - $days*24*60*60 - $hours*60*60 - $mins*60;
		
		$formatted=" $days �. $hours �. $mins ���. $secs ���.";
		
		$arr=array(
			'days'=>$days,
			'hours'=>$hours,
			'mins'=>$mins,
			'secs'=>$secs
		
		);
		
		//echo $working_time;
		
		return $working_time;
	}
	
	
	
	
	//����� ������� ������� ���������
	public function CalcTotalWorkingTime($id, &$formatted, &$arr, &$working){
		$working=false;
		
		//����� ������ ����� �������� �� ���������
		$sql='select distinct p.kind_id
		from '.$this->tablename.' as p
		where
		'.$this->subkeyname.'="'.$id.'"
		order by p.kind_id asc';
		
		$set=new mysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows(); $kinds=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			 
			$kinds[]=$f['kind_id'];
		}
		
		//var_dump($kinds);
		
		$working_time=0; $formatted=''; $arr=array();
		
		//$arr=$this->GetItemsByIdTotalArr($id);
		
		foreach($kinds as $kk=>$kind_id){
			
			$working_time+=$this->CalcWorkingTime($id,$kind_id,$fm1,$ar1,$working1);
			$working=$working||$working1;
			/*$last_record=array();
			
			$working_time1=0;
			foreach($arr as $k=>$v){
				if($v['kind_id']!=$kind_id) continue;
				
				//���� ����� �����
				if($v['in_or_out']==0){
					
					//��� ���� ����� ����� ���� ����� ������
					//��� ��������� ����� ��� in_or_out==1
					//���� ��� ��� ���������� - �� ��������� ������
					$found=false; $out_data=array();	
					foreach($arr as $k1=>$v1){
						if($v['kind_id']!=$kind_id) continue;
						
						if($k1>$k){
							if($v1['in_or_out']==1){
								$found=true;
								$out_data=$v1;
							}
							break;
						}
					}
					
					if($found){
						$delta=$out_data['pdate']-$v['pdate'];	
					}else $delta=0;
					
					$working_time1+=$delta;
				}
				$last_record=$v;
			}
			
			if(($last_record!=array())&&($last_record['in_or_out']==0)){
				//�������� ������� ����� ���� �������� � ������
				
				$working_time1+=(time()-$last_record['pdate']);
				$working=$working||true;	
			}
			echo $kind_id.' '.$working_time1."<br>";*/
			
		}
		
		
		$days=floor($working_time/(24*60*60));
		
		$hours = floor(($working_time - $days*24*60*60)/(60*60));
		
		$mins= floor(($working_time - $days*24*60*60 - $hours*60*60)/(60));
		
		$secs=$working_time - $days*24*60*60 - $hours*60*60 - $mins*60;
		
		$formatted=" $days �. $hours �. $mins ���. $secs ���.";
		
		$arr=array(
			'days'=>$days,
			'hours'=>$hours,
			'mins'=>$mins,
			'secs'=>$secs
		
		);
		
		 
		return $working_time;
		
	}
	
	
	//������ ������� ���� ����� ���������
	public function GetItemsByIdTotalArr($id){
		$arr=array();
		
		$sql='select p.*
		from '.$this->tablename.' as p
		 
		where
		'.$this->subkeyname.'="'.$id.'"
	 
		order by p.id asc';
		
		 
		 
		
		//echo $sql."<p>";
		
		$set=new MysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			 
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			 
			
			$arr[]=$f;
		}
		
		return $arr;
	}
	
}

?>