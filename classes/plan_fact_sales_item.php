<?
require_once('abstractitem.php');
 

//элемент план/факт продаж
class PlanFactSalesItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='plan_fact_sales';
		$this->item=NULL;
		$this->pagename='plan_fact_sales.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='position_id';	
	}
	
	
	public function GetSales($month, $year, $user_id, $plan_or_fact, $currency_id,  $department_id, $org_id=1, $can_add_plan=false, 
		$can_edit_plan=false, 
		$can_add_fact=false, 
		$can_edit_fact=false,
		$dec=NULL, //включено дл€ общей совместимости, в этом методе не используем
		$can_edit_fact_super=false
		){
			 /*
		array('can_modify'=>
			'data'=>
			
			);
		 */
		 
		//echo 'zzzzzzzzzzzzzzz '; 
		$data=$this->GetItemByFields(array('user_id'=>$user_id, 'month'=>$month, 'year'=>$year, 'plan_or_fact'=>$plan_or_fact,'currency_id'=>$currency_id, 'org_id'=>$org_id));
		
		 
		$can_modify=false;
		
		
		$restricted_by_period=false;
		if($data===false){
			//не введено - работают права на создание
			if($plan_or_fact==0) $can_modify=$can_add_plan;
			else $can_modify=$can_add_fact;
		}else{
			//введено - работают права на правку
			if($plan_or_fact==0) $can_modify=$can_edit_plan;
			else{
				 $can_modify=$can_edit_fact;
				 
				 //проверим ограничени€ по периоду
				 $check=mktime(0,0,0,$month+1,1,$year); //1й день след. мес€ца
				 $check+=31*24*60*60;
				
				 if(!$can_edit_fact_super&&(time()>$check)){
					$restricted_by_period=true;
				 }
				 
				/* if($restricted_by_period){
					 echo 'закрыто дл€ изменений, т.к. сегодн€ позже, чем '.date('d.m.Y', $check).'<br>';
				 }*/
			}
		}
		
		$result=array('can_modify'=>$can_modify,
					  'restricted_by_period'=>$restricted_by_period,
					  'data'=>$data);
					  
		return $result;			  
		
	}
	
}
?>