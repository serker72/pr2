<?
require_once('abstractitem.php');
require_once('actionlog.php');
require_once('authuser.php');
require_once('period_checker.php'); 
require_once('docstatusitem.php'); 

//элемент факт продаж ОПО
class PlanFactFactItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='plan_fact_fact';
		$this->item=NULL;
		$this->pagename='plan_fact_sales.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='position_id';	
	}
	
	
	public function GetSales($month, $year, $user_id, $plan_or_fact, $currency_id,  $department_id, $org_id=1, $can_add_plan=false, 
		$can_edit_plan=false, 
		$can_add_fact=false, 
		$can_edit_fact=false,
		$dec=NULL,
		$can_edit_fact_super=false
		){
			
		 /*
		array('can_modify'=>
			'data'=>
			
			);
		 */	
		 
		
			
		$sql='select sum(contract_sum) as s from '.$this->tablename.' where status_id=2 and month="'.$month.'" and year="'.$year.'" and user_id="'.$user_id.'" and contract_currency_id="'.$currency_id.'" and org_id="'.$org_id.'" ';
		
		if($dec instanceof DBDecorator){ 
			$db_flt=$dec->GenFltSql(' and ');
			if(strlen($db_flt)>0){
				$sql.=' and '.$db_flt;
				 
			}
		}
		//echo "$sql <br>";
		
		$set=new mysqlset($sql);
		
		$rc=$set->getResultNumRows();
		$rs=$set->getresult();
		$f=mysqli_fetch_array($rs);
		
		$data=array(
			'department_id'=>$department_id,
			'user_id'=>$user_id,
			'plan_or_fact'=>$plan_or_fact,
			'month'=>$month,
			'year'=>$year,
			'currency_id'=>$currency_id,
			'value'=>round((float)$f['s'])
		);
		
		
		$can_modify=$can_add_fact;
		
		
		$result=array('can_modify'=>$can_modify,
						'restricted_by_period'=>false,
					  'data'=>$data);
					  
		return $result;			  
			
		
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	public function Edit($id,$params,$scan_status=false,$_result=NULL){
		$item=$this->GetItemById($id);
		$log=new ActionLog;
		
		//мы устанавливаем утверждение 1 гал.
		/*if(isset($params['is_confirmed_price'])&&($params['is_confirmed_price']==1)&&($item['is_confirmed_price']==0)){
			$params['restore_pdate']=0;	
		}
		
		if(isset($params['status_id'])&&($params['status_id']!=3)&&($item['status_id']==3)){
			$params['restore_pdate']=time();	
		}
		*/
		
		
		AbstractItem::Edit($id, $params);
		 
		
		if($scan_status) $this->ScanDocStatus($id,$item,$params,NULL,$_result);
		
		
	}
	
	
	
	
	
	//контроль возможности удаления
	public function CanDelete($id, &$reason){
		$can_delete=true;
		
		$reason='';
		
		$itm=$this->GetItemById($id);
		
		if(($itm!==false)&&(($itm['is_confirmed']!=0))) {
			$reason.='факт продаж ОПО утвержден';
			$can_delete=$can_delete&&false;
		}
		
		
		
		
		
		return $can_delete;
	}
	
	
	
	
	
	
	
	//проверка и автосмена статуса (1-2)
	public function ScanDocStatus($id, $old_params, $new_params, $item=NULL, $_result=NULL){
		$log=new ActionLog();
		$au=new AuthUser;
		if($_result===NULL) $_result=$au->Auth();
		$_stat=new DocStatusItem;
		if($item===NULL) $item=$this->GetItemById($id);
		
		if(isset($new_params['is_confirmed'])&&isset($old_params['is_confirmed'])){
			
			
			
			if(($new_params['is_confirmed']==1)&&($old_params['is_confirmed']==0)&&($old_params['status_id']==1)){
				//смена статуса с 1 на 2
				$this->Edit($id,array('status_id'=>2));
				
				$stat=$_stat->GetItemById(2);
				$log->PutEntry($_result['id'],'смена статуса факта продаж ОПО ',NULL,788,NULL,'установлен статус '.$stat['name'],$item['id']);
				
			}elseif(($new_params['is_confirmed']==0)&&($old_params['is_confirmed']==1)&&(($old_params['status_id']==2)/*||($old_params['status_id']==9)||($old_params['status_id']==10)||($old_params['status_id']==18)*/)){
				$this->Edit($id,array('status_id'=>1));
				
				$stat=$_stat->GetItemById(1);
				$log->PutEntry($_result['id'],'смена статуса факта продаж ОПО ',NULL,788,NULL,'установлен статус '.$stat['name'],$item['id']);
			}
		}else{
			//отследить переходы из 2-9, 9-2, 9-10, 10-9
		  
		 
			
		}
		
		 
	}
	
	
	
	
	
	
	//запрос о возможности аннулирования и возвращение причины, почему нельзя аннулировать
	public function DocCanAnnul($id,&$reason,$item=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		
		
		
		$_dsi=new DocStatusItem;
		if($item['status_id']!=1){
			
			$can=$can&&false;
			$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='статус документа: '.$dsi['name'];
			$reason.=implode(', ',$reasons);
		}else{
		
		 
		  
		 
		  
		}
		
		return $can;
	}
	
	
	//запрос о возможности аннулирования и возвращение причины, почему нельзя аннулировать
	public function DocCanRestore($id,&$reason,$item=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		if($item['status_id']!=3){
			
			$can=$can&&false;
			$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='статус документа: '.$dsi['name'];
			$reason.=implode(', ',$reasons);
		}
		
		return $can;
	}
	
	//аннулирование документа
	public function DocAnnul($id){
		if($this->DocCanAnnul($id,$rz)){
			$this->Edit($id, array('status_id'=>3));	
		}
	}
	
	
		
	//Запрос о возм снятия утв цен
	public function DocCanUnconfirm($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed']!=1){
			
			$can=$can&&false;
			$reasons[]='факт продаж ОПО не утвержден';
			$reason.=implode(', ',$reasons);
		}else{
		
		  
		   //контроль закрытого периода 
		    $reasons=array();
	 
		   $reason.=implode(', ',$reasons);
		
		  	
		  
		}
		
		return $can;
	}
	
	//запрос о возм утв цен
	public function DocCanConfirm($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed']!=0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='факт продаж ОПО утвержден';
			$reason.=implode(', ',$reasons);
		}else{
			//контроль закрытого периода 
		    $reasons=array();
			 
			 
		
		}
		
		return $can;
	}
	
	
}
?>