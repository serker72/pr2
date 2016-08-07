<?
require_once('abstractitem.php');
require_once('memoitem.php');


class MemoBlink{
	
	
	
	
	
	
	
	//обобщенная функция цвета и мигания
	public function OverallBlink($task_id,  &$color, $task=NULL){
		$res=false;
		
		
		$color='black';
		
		$_ti=new memoitem;
		if($task===NULL) $task=$_ti->getitembyid($task_id);
		
		$_pdate= DateFromdmY(DateFromYmd($task['task_pdate']));
		
		
		
		if($task['task_ptime']!=""){
			$_time=((int)substr($task['task_ptime'], 0, 2))*60*60  + ((int)substr($task['task_ptime'], 3, 2))*60;
			$_pdate+=$_time;
		
		}else{
			$_pdate+=60*60*24-1;	
		}
		
//		echo date('d m Y H:i:s ',$_pdate);
		
		if($task['status_id']==5){
			$color='silver';	
		}
		elseif($task['status_id']==4){
			$color='green';	
		}elseif($_pdate<time()){
			//просрочена	
			$color='red';
			$res=true;
			
		}else{
			//не просрочена	
			if($task['status_id']==1){
				 $color='#996633';
				 $res=true;
				 
			}else $color='black';
		}
		
		
		
		
	
		
		
		return $res;
	}
}
?>