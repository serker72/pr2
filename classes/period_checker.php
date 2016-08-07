<?
require_once('abstractitem.php');
require_once('pergroup.php');

//класс для проверки вводимых дат (не ранее закрытого периода)
class PeriodChecker{
	
	
	public function GetDate(){
		//позже - будем подгружать из таблицы периодов
		
		return '01.03.2015';	
	}
	
	
	//проверка даты, не должна попадать в закрытый период
	public function CheckDateByPeriod($pdate, $org_id, &$rss, $periods=NULL){
		$rss='';
		$res=true;
		
		if($periods===NULL){
			$_pg=new PerGroup;
			$periods=$_pg->GetItemsByIdArr($org_id,0,1);	
		}
		
		foreach($periods as $k=>$v){
			if(($pdate>=$v['pdate_beg_unf'])&&($pdate<=$v['pdate_end_unf'])){
				$rss.=' попадает в закрытый период с '.$v['pdate_beg'].' по '.$v['pdate_end'];
				$res=$res&&false;
				break;	
			}
		}
		
		return $res;
	}
	
	
}
?>