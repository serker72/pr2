<?
 
require_once('abstractitem.php');
 
require_once('supplieritem.php');
require_once('suppliercontactitem.php');
require_once('suppliercontactgroup.php');
require_once('suppliercontactdatagroup.php');
require_once('user_s_group.php');
require_once('supplier_cities_group.php');

require_once('supplier_responsible_user_group.php');
require_once('supplier_responsible_user_item.php');
require_once('actionlog.php');

require_once('tender_history_group.php');
require_once('tender_history_item.php');
require_once('user_s_item.php');
require_once('discr_man.php');

require_once('pl_curritem.php');
require_once('tender_view_item.php');
require_once('lead.class.php');
require_once('user_s_group.php');

require_once('tender_field_rules.php');
require_once('docstatusitem.php');
require_once('tender.class.php');

require_once('tender_monitor_view1.class.php');
require_once('tender_monitor_view2.class.php');

//библиотека классов мониторинга тендера


//абстрактная запись мониторинга тендера
class TenderMonitor_AbstractItem extends AbstractItem{
	public $kind_id=1;
	protected function init(){
		$this->tablename='tender_monitor';
		$this->item=NULL;
		$this->pagename='ed_tender.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}	
	
	 	 
	
	
	
	
	
	//запрос о возможности аннулирования и возвращение причины, почему нельзя аннулировать
	public function DocCanAnnul($id,&$reason,$item=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		/*if($item===NULL) $item=$this->GetItemById($id);
		
			
		
		$_dsi=new DocStatusItem;
		if($item['status_id']!=1){
			
			$can=$can&&false;
			$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='статус документа: '.$dsi['name'];
			$reason.=implode(', ',$reasons);
		} 
		*/
		 
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
	
	
	
	
//Запрос о возм снятия утв цен
	public function DocCanUnconfirmPrice($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed']!=1){
			
			$can=$can&&false;
			$reasons[]='у мониторинга тендеров не утверждено заполнение';
			$reason.=implode(', ',$reasons);
		} 
		
		return $can;
	}
	
	//запрос о возм утв цен
	public function DocCanConfirmPrice($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed']!=0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='у мониторинга тендеров утверждено заполнение';
			$reason.=implode(', ',$reasons);
		}else{
			
		/**/
		}
		
		return $can;
	}
	 
	
	
	
	//проверка и автосмена статуса 
	public function ScanDocStatus($id, $old_params, $new_params, $item=NULL, $_result=NULL){
		$log=new ActionLog();
		$au=new AuthUser;
		if($_result===NULL) $_result=$au->Auth(false,false);
		$_stat=new DocStatusItem;
		if($item===NULL) $item=$this->GetItemById($id);
		
		
		$setted_status_id=$item['status_id'];
		
	 
		if(isset($new_params['is_confirmed'])&&isset($old_params['is_confirmed'])){
			if(($new_params['is_confirmed']==1)&&($old_params['is_confirmed']==0)){
				//смена статуса на 2
				
				 
				$setted_status_id=2;
				 
				
				$this->Edit($id,array('status_id'=>$setted_status_id));
				
				$stat=$_stat->GetItemById($setted_status_id);
				$log->PutEntry($_result['id'],'смена статуса тендера',NULL,931,NULL,'установлен статус '.$stat['name'],$item['id']);
			}elseif(($new_params['is_confirmed']==0)&&($old_params['is_confirmed']==1)){
				//смена статуса на 1
				$setted_status_id=1;
				$this->Edit($id,array('status_id'=>$setted_status_id));
				
				$stat=$_stat->GetItemById($setted_status_id);
				$log->PutEntry($_result['id'],'смена статуса тендера',NULL,931,NULL,'установлен статус '.$stat['name'],$item['id']);
			}
		} 
		//die();
	}
	 
	
	
	public function ConstructName($id, $item=NULL){
		 $_stat=new DocStatusItem;
		
		if($item===NULL) $item=$this->getitembyid($id); $stat=$_stat->getitembyid($kp['status_id']);
		
		return 'Тендер, статус '.$stat['name'];
	}
	
	
	public function ConstructFullName($id, $item=NULL){
		 $_stat=new DocStatusItem;
		
		if($item===NULL) $item=$this->getitembyid($id); $stat=$_stat->getitembyid($kp['status_id']);
		
		return 'Тендер '.$item['code'].', статус '.$stat['name'];
	}
	
	public function ConstructBeginDate($id, $item=NULL){
		 
		
		if($item===NULL) $item=$this->getitembyid($id);  
		
		return date('d.m.Y', $item['pdate_beg']);
	}
	
	public function ConstructEndDate($id, $item=NULL){
		if($item===NULL) $item=$this->getitembyid($id);  
		
		$res='';
		
	 	if($item['pdate_end']!="") $res.=$item['pdate_end'].'T'.$item['ptime_end'];
		
		return $res; 
	}
	
	//получить адресата или их список
	public function ConstructContacts($id, $item=NULL){
		
	}
	
	//статусы, доступные к переходу
	public function GetStatuses($current_id=0){
		 
		$arr=Array();
		 $set=new MysqlSet('select * from document_status where id in(9,10,3) order by  id asc');
		 
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$arr[]=$f;
		}
		
		return $arr;
	 
	}
	
	 
	
}


/*************************************************************************************************/
//мониторинг тендера - вид оборудования
class TenderMonitor1_Item extends TenderMonitor_AbstractItem{
	public $kind_id=1;
	
	  
	
	
	public function Edit($id,$params,$scan_status=false,$_result=NULL){
		$item=$this->GetItemById($id);
		
		
		
		 
		
		AbstractItem::Edit($id, $params);
		
		
		 
		 
		if($scan_status) $this->ScanDocStatus($id,$item,$params,NULL,$_result);
		 
	}
	
	
	   
	
	
	 
	
	
	 
	 
}



/*************************************************************************************************/
//мониторинг тендера - контрагент
class TenderMonitor2_Item extends TenderMonitor_AbstractItem{
	public $kind_id=2;
	
	  
	
	
	public function Edit($id,$params,$scan_status=false,$_result=NULL){
		$item=$this->GetItemById($id);
		
		
		
		 
		
		AbstractItem::Edit($id, $params);
		
		
		 
		 
		if($scan_status) $this->ScanDocStatus($id,$item,$params,NULL,$_result);
		 
	}
	
	 
	
	
	 
	 
}
   

 


/***********************************************************************************************/
//определение класса записи мониторинга тендера
class TenderMonitor_Resolver{
	public $instance;
	public $group_instance;
	function __construct($kind_id=1){
		 switch($kind_id){
			case 1:
				$this->instance= new TenderMonitor1_Item;
				$this->group_instance=new TenderMonitor1_Group;
			break;
			case 2:
				$this->instance= new TenderMonitor2_Item;
				$this->group_instance=new TenderMonitor2_Group;
			break;
			 
			default:
				$this->instance=new TenderMonitor1_Item;
				$this->group_instance=new TenderMonitor1_Group;
			break;
		}; 
	}
	
	public function SetAuthResult($result){
		$this->group_instance->SetAuthResult($result);	
	}
	
	 
}




/***************************************************************************************************/
//абстрактная группа мониторинга тендеров
class TenderMonitor_AbstractGroup extends AbstractGroup{
	protected $_auth_result;
	protected $_view;
	public $kind_id=1;
	protected $_sql, $_sql_count;
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='tender_monitor';
		$this->pagename='tenders.php';		
		$this->subkeyname='kind_id';	
		$this->vis_name='is_shown';		
		
		
		
		$this->_auth_result=NULL;
		$this->_view=new TenderMonitor_View1Group;
	} 
	
	
	
	//получить запрос  
	protected function GainSql(){
		$this->_sql='select distinct p.*,
		s.name as status_name, s.weight as status_weight,
		 
		up.name_s as confirmed_price_name, up.login as confirmed_price_login, p.confirm_pdate as confirm_price_pdate,
		 
			
		cr.name_s as cr_name, cr.login as cr_login, cr.is_active as cr_is_active,
		 
		
		eq.name as eq_name,  
		sup.full_name as supplier_name, opf.name as opf_name
		 
					 
				from '.$this->tablename.' as p
				left join document_status as s on s.id=p.status_id
				 
				left join user as up on up.id=p.user_confirm_id
				 
				
				 
				 
				left join supplier as sup on p.supplier_id=sup.id
				left join opf as opf on opf.id=sup.opf_id
				
				 
				
				left join user as cr on cr.id=p.created_id
				 
				left join tender_eq_types as eq on eq.id=p.eq_type_id
				
			where p.kind_id="'.$this->kind_id.'" 	 
				 ';
				 
		$this->_sql_count='select count(*)
					 
				from '.$this->tablename.' as p
				left join document_status as s on s.id=p.status_id
				 
				left join user as up on up.id=p.user_confirm_id
				 
				
				 
				 
				left join supplier as sup on p.supplier_id=sup.id
				left join opf as opf on opf.id=sup.opf_id
				
				 
				
				left join user as cr on cr.id=p.created_id
				 
				left join tender_eq_types as eq on eq.id=p.eq_type_id
				
			where p.kind_id="'.$this->kind_id.'" 	 
				 ';
			 
	}
	
	//получить список		 
	public function ShowPos(
		$template, //0
		DBDecorator $dec, //1
		$can_create=false, //2
		$from, //3
		$to_page, //4
		$has_header=true,  //5
		$is_ajax=false, //6
		$can_delete=true, //7
		$can_confirm_price=true, //8
		$can_unconfirm_price=true, //9
		$can_expand=true //10
	
	){
			 
		 if($is_ajax) $sm=new SmartyAj;
		 else $sm=new SmartyAdm;
		 
		 $sm->assign('has_header', $has_header);
		 $sm->assign('can_restore', $can_restore);
		  $sm->assign('can_create', $can_create);
		 $sm->assign('can_delete', $can_delete);
		 $sm->assign('can_confirm_price', $can_confirm_price);
		 $sm->assign('can_unconfirm_price', $can_unconfirm_price);
		 $sm->assign('can_expand', $can_expand);
		 
		 //получим список тех, кто может снять утверждение заполнения
		 $right_id=999;
		 if($this->kind_id==1) $right_id=999;
		 elseif($this->kind_id==2) $right_id=998;
		$_usg1=new UsersSGroup;
		$usg1=$_usg1->GetUsersByRightArr('w', $right_id);
		$sm->assign('can_unconfirm_users',$usg1); 
 
		
		 
		 
		 
		 $this->GainSql();
		 $sql=$this->_sql;
		 $sql_count=$this->_sql_count;
		 
		 $db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' and '.$db_flt;
			$sql_count.=' and '.$db_flt;	
		}
		
		
		
		$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
		//echo $sql.'<br>';
		
		$set=new mysqlSet($sql,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$total=$set->GetResultNumRowsUnf();
		
		
		//page
		if($from>$total) $from=ceil($total/$to_page)*$to_page;
		$navig = new PageNavigator($this->pagename,$total,$to_page,$from,10,'&'.$dec->GenFltUri('&', $this->kind_id));
		$navig->SetFirstParamName('from'.$kind_id);
		$navig->setDivWrapperName('alblinks');
		$navig->setPageDisplayDivName('alblinks1');			
		$pages= $navig->GetNavigator();
		
		$alls=array();
		 
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			 
			

			 
		//	$f['pdate_beg']=DateFromYmd($f['pdate_beg']);
			 
			$f['pdate']=date('d.m.Y H:i:s', $f['pdate']);
			
			$f['given_pdate']=date('d.m.Y', $f['given_pdate']);
			
			if($f['confirm_pdate']!=0) $f['confirm_pdate']=date('d.m.Y H:i:s', $f['confirm_pdate']);
			else $f['confirm_pdate']='-';
			
			 
			 
			$_res=new TenderMonitor_Resolver($f['kind_id']);
				//$f['contact_value']=$_res->instance->ConstructContacts($f['id'], $f);
			 
			
			$f['can_annul']=$_res->instance->DocCanAnnul($f['id'],$reason,$f)&&(
			
			$can_delete 
			
			);
			if(!($can_delete)) $reason='недостаточно прав для данной операции';
			$f['can_annul_reason']=$reason;
			
			
			 
			 
			 
				
			 
			 
			
			
			
			 
			//print_r($f);	
			$alls[]=$f;
		}
		
		//заполним шаблон полями
	
		$current_supplier='';
		$user_confirm_id='';
		
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			
			
		 
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		
		 
		
		//типы оборудования 
		 $_eqs=new Tender_EqTypeGroup;
		$eqs1=$_eqs->GetItemsArr();
		$eqs[]=array('id'=>'', 'name'=>'-все-');
		foreach($eqs1 as $k=>$v) $eqs[]=$v;
		$sm->assign('eqs', $eqs);
		 
		
	 
		
		$sm->assign('pagename',$this->pagename);
		
		$sm->assign('from',$from);
		$sm->assign('to_page',$to_page);
		$sm->assign('pages',$pages);
		$sm->assign('items',$alls);
		
		$sm->assign('kind_id',$this->kind_id);
		$sm->assign('prefix',$this->kind_id);
		
		$sm->assign('now', date('d.m.Y')); 
	 
	 	//показ конфигурации
		$sm->assign('view', $this->_view->GetColsArr($this->_auth_result['id']));
		$sm->assign('unview', $this->_view->GetColsUnArr($this->_auth_result['id']));
		
	 	
	 
		
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri('&', $this->kind_id);
		//echo $link;
		$link=$this->pagename.'?'.eregi_replace('&sortmode'.$this->kind_id.'=[[:digit:]]+','',$link);
		//$link=eregi_replace('&action'.$this->kind_id,'&action',$link);
		//$link=eregi_replace('&id'.$this->kind_id,'&id',$link);
		$sm->assign('link',$link);
  
 
		 
		
		return $sm->fetch($template);
		
	}
	
	
}


/***************************************************************************************************/
//группа мониторинга по виду оборудования
class TenderMonitor1_Group extends TenderMonitor_AbstractGroup{
	public $kind_id=1;
	
	//установка всех имен
	protected function init(){
		$this->tablename='tender_monitor';
		$this->pagename='tenders.php';		
		$this->subkeyname='kind_id';	
		$this->vis_name='is_shown';		
		
		
		
		$this->_auth_result=NULL;
		$this->_view=new TenderMonitor_View1Group;
	} 
	
	
	
}



/***************************************************************************************************/
//группа мониторинга по контрагенту
class TenderMonitor2_Group extends TenderMonitor_AbstractGroup{
	public $kind_id=2;
	//установка всех имен
	protected function init(){
		$this->tablename='tender_monitor';
		$this->pagename='tenders.php';		
		$this->subkeyname='kind_id';	
		$this->vis_name='is_shown';		
		
		
		
		$this->_auth_result=NULL;
		$this->_view=new TenderMonitor_View2Group;
	} 
	
}


 

?>