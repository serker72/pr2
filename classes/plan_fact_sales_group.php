<?

require_once('abstractgroup.php');
 
//  группа план/факт продаж
class PlanFactSalesGroup extends AbstractGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='plan_fact_sales';
		$this->pagename='plan_fact_sales.php';		
		$this->subkeyname='position_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
}
?>