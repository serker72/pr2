<?
require_once('abstractgroup.php');


//абстрактный список записей в журнал о входе/выходе записи из определенного статуса
class Abstract_WorkingGroup extends AbstractGroup {
	 
	
	//установка всех имен
	protected function init(){
		$this->tablename='sched_working';
		$this->pagename='view.php';		
		$this->subkeyname='sched_id';	
		$this->vis_name='is_shown';		
		
		
		 
	}
	
	
	
	//список позиций
	public function GetItemsByIdArr($id){
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
	
	
	//найти время выполнения заявки - анализировать лог
	public function CalcWorkingTime($id, &$formatted, &$arr){
		$working_time=0; $formatted=''; $arr=array();
		
		$arr=$this->GetItemsByIdArr($id);
		
		
		$last_record=array();
		
		foreach($arr as $k=>$v){
			//ищем точки входа
			if($v['in_or_out']==0){
				
				//для этой точки входа ищем точку выхода
				//это следующий после нее in_or_out==1
				//либо при его отсутствии - не учитываем период
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
			//добавить разницу между этим временем и сейчас
			
			$working_time+=time()-$last_record['pdate'];	
		}
		
		
		$days=floor($working_time/(24*60*60));
		
		$hours = floor(($working_time - $days*24*60*60)/(60*60));
		
		$mins= floor(($working_time - $days*24*60*60 - $hours*60*60)/(60));
		
		$secs=$working_time - $days*24*60*60 - $hours*60*60 - $mins*60;
		
		$formatted=" $days д. $hours ч. $mins мин. $secs сек.";
		
		$arr=array(
			'days'=>$days,
			'hours'=>$hours,
			'mins'=>$mins,
			'secs'=>$secs
		
		);
		
		//echo $working_time;
		
		return $working_time;
	}
	
	
	static public function FormatTime($working_time){
		$days=floor($working_time/(24*60*60));
		
		$hours = floor(($working_time - $days*24*60*60)/(60*60));
		
		$mins= floor(($working_time - $days*24*60*60 - $hours*60*60)/(60));
		
		$secs=$working_time - $days*24*60*60 - $hours*60*60 - $mins*60;
		
		$formatted=" $days д. $hours ч. $mins мин. $secs сек.";
		
		return $formatted;	
	}
	
}

?>