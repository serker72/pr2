<?
require_once('abstractitem.php');
require_once('missionitem.php');

require_once('missionexpgroup.php');


class AnMissionBlink{
	
	
	
	
	
	
	
	//обобщенная функция цвета и мигания
	public function OverallBlink($task_id,  &$color, $task=NULL, $plan=NULL, $fact=NULL){
		$res=false;
		
		
		$color='black';
		
		$_ti=new MissionItem; $_me=new MissionExpGroup;
		if($task===NULL) $task=$_ti->getitembyid($task_id);
		
		if($plan===NULL) $plan=round((float)$_me->CalcPlan($task_id),2);
		if($fact===NULL) $fact=round((float)$_me->CalcFact($task_id),2);
		
		
		if($plan>$fact) $color='green';
		elseif($fact>$plan) $color='red';
		
		
		
		
		
		
		
		return $res;
	}
}
?>