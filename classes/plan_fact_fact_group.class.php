<?

require_once('abstractgroup.php');
require_once('plan_fact_fact_item.class.php');
/*require_once('classes/smarty/SmartyAdm.class.php');
require_once('classes/smarty/SmartyAj.class.php');*/

require_once('plan_fact_fact_notesgroup.php');
require_once('plan_fact_fact_notesitem.php');

require_once('price_kind_group.php');
require_once('supplier_city_item.php'); 
require_once('supplier_country_group.php');
require_once('supplier_city_item.php');

require_once('plan_fact_sales.class.php'); 
require_once('user_s_group.php');

require_once('dogpr_view.class.php');
 
//  группа фактов продаж ОПО
class PlanFactFactGroup extends AbstractGroup {
	protected $_auth_result;
	protected $_view;
	
	public $prefix='';
	
	protected $_item;
	
	//установка всех имен
	protected function init(){
		$this->tablename='plan_fact_fact';
		$this->pagename='plan_fact_fact_opo.php';		
		$this->subkeyname='position_id';	
		$this->vis_name='is_shown';		
		
			$this->_item=new PlanFactFactItem;
		 
		
		$this->_auth_result=NULL;
		$this->_view=new Dogpr_ViewGroup;
		
	}
	
	
	
	
	
	public function GainSql(&$sql, &$sql_count){
		
		$sql='select p.*,
					 sp.full_name as supplier_name,  
					spo.name as opf_name, 
					 
					u.name_s as confirmed_price_name, u.login as confirmed_price_login,
					pk.name as price_kind_name,
					
					st.name as status_name,
					curr.name as currency_name, curr.signature as currency_signature,
					
					us.id as us_id, us.name_s as  us_name, us.login as us_login,
					
					mn.id as manager_id, mn.name_s as  manager_name, mn.login as manager_login,
					
					prod.name as prod_name, 
					c.name as city_name
					
				from '.$this->tablename.' as p
					
					 left join supplier as sp on p.supplier_id=sp.id
					left join opf as spo on spo.id=sp.opf_id 
					left join user as u on p.user_confirm_id=u.id
					left join pl_price_kind as pk on p.price_kind_id=pk.id
					
					left join user as us on p.user_id=us.id
					left join user as mn on p.posted_user_id=mn.id
					
					 
					left join document_status as st on st.id=p.status_id
					
					left join pl_producer as prod on prod.id=p.producer_id 
					 
					left join pl_currency as curr on curr.id=p.contract_currency_id 
					left join sprav_city as c on c.id=p.city_id	
				
					';
		$sql_count='select count(*)
				from '.$this->tablename.' as p
					
					 left join supplier as sp on p.supplier_id=sp.id
					left join opf as spo on spo.id=sp.opf_id 
					left join user as u on p.user_confirm_id=u.id
					left join pl_price_kind as pk on p.price_kind_id=pk.id
					
					left join user as us on p.user_id=us.id
					left join user as mn on p.posted_user_id=mn.id
					
					 
					left join document_status as st on st.id=p.status_id
					
					 
					left join pl_producer as prod on prod.id=p.producer_id 
					left join pl_currency as curr on curr.id=p.contract_currency_id 	
					left join sprav_city as c on c.id=p.city_id	
					';
		
	}
	
	
	
	
	
	
	public function ShowPos($template, DBDecorator $dec,$from=0,$to_page=ITEMS_PER_PAGE,
		$can_delete=false,
		$can_confirm=false,  
		 
		$has_header=true, 
		$is_ajax=false, 
		$can_restore=false,
		$can_unconfirm=false,
		
		$can_edit=false,
		$can_edit_super=false,
		$can_print=false,
		&$alls,
		$can_change_date=false,
		$limited_user=NULL
		){
	 /*$can_add=false, $can_edit=false, $can_delete=false, $add_to_bill='', $can_confirm=false,  $can_super_confirm=false, $has_header=true, $is_ajax=false, $can_restore=false, $nested_bill_positions=NULL, $can_unconfirm=false, $can_send_email=false, $can_view_re=false){*/
				
		$_notes_group=new PlanFactFactNotesGroup;
		$_city=new SupplierCityItem;
		$_pfs=new PlanFactSales;
		
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		
		
		$this->GainSql($sql, $sql_count);
				 
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' where '.$db_flt;
			$sql_count.=' where '.$db_flt;	
			
		
		}
		
		if($limited_user!==NULL){
			if(strlen($db_flt)==0){
				 $sql.=' where us.id in('.implode(', ', $limited_user).')';
				  $sql_count.=' where us.id in('.implode(', ', $limited_user).')';
				 
			}else{
				 $sql.=' and us.id in('.implode(', ', $limited_user).')';
				  $sql_count.=' and us.id in('.implode(', ', $limited_user).')';
				 
			}
			
		}
		
		
		$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
		//echo $sql;
		
		$set=new mysqlSet($sql,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$total=$set->GetResultNumRowsUnf();
		
		
		//page
		if($from>$total) $from=ceil($total/$to_page)*$to_page;
		$navig = new PageNavigator($this->pagename,$total,$to_page,$from,10,'&'.$dec->GenFltUri('&', $this->prefix));
		$navig->SetFirstParamName('from'.$this->prefix);
		$navig->setDivWrapperName('alblinks');
		$navig->setPageDisplayDivName('alblinks1');			
		$pages= $navig->GetNavigator();
		
		
	
		
		
		$alls=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date("d.m.Y",$f['pdate']);
			
			 
			
			
			$city=$_city->GetFullCity($f['city_id']);
			$f['city']=$city['fullname'];
			
			 
			
			
			 $f['month_name']=$_pfs->GetMonthByNumber($f['month']);
			
			
			if($f['confirm_pdate']!=0) $f['confirm_pdate']=date("d.m.Y H:i:s",$f['confirm_pdate']);
			else $f['confirm_pdate']='-';
			
			
			$restricted_by_period=false;
				$can_ed=true; 
			if($f['status_id']==1){
			
				if(!$can_edit_super&&!$can_edit) $can_ed=false;
				elseif(!$can_edit_super){
					//проверить дату
					 //проверим ограничения по периоду
					 $check=mktime(0,0,0,(int)$f['month']+1,1,$f['year']); //1й день след. месяца
					 $check+=31*24*60*60;
					
					 if(!$can_edit_super&&(time()>$check)){
						$restricted_by_period=true;
					 }
					 
					 /*if($restricted_by_period){
						 echo 'закрыто для изменений, т.к. сегодня позже, чем '.date('d.m.Y', $check).'<br>';
					 }*/
				}
			}
			$f['restricted_by_period']=$restricted_by_period;
			$f['can_edit']=$can_ed;
			
			
			
		 	$f['notes']=$_notes_group->GetItemsByIdArr($f['id'],0,0,false,false,false,0,false);
			
			
			$f['can_annul']=$this->_item->DocCanAnnul($f['id'],$reason,$f)&&$can_delete;
			if(!$can_delete) $reason='недостаточно прав для данной операции';
			$f['can_annul_reason']=$reason;
			 
			
			
			$alls[]=$f;
		}
		
		
		
		
		//заполним шаблон полями
		$current_storage='';
		$current_supplier='';
		$current_user_confirm_price=''; $current_user_confirm_price_id='';
		$current_sector='';
		$current_price_kind_id='';
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			
		//	if($v->GetName()=='sector_id'.$add_to_bill) $current_sector=$v->GetValue();
			if($v->GetName()=='supplier_id'.$add_to_bill) $current_supplier=$v->GetValue();
		//	if($v->GetName()=='storage_id'.$add_to_bill) $current_storage=$v->GetValue();
			
			
			if($v->GetName()=='user_confirm_price_id'.$add_to_bill) $current_user_confirm_price_id=$v->GetValue();
			if($v->GetName()=='price_kind_id'.$add_to_bill) $current_price_kind_id=$v->GetValue();
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		
		
		$au=new AuthUser();
		//$result=$au->Auth();
		
		if($this->_auth_result===NULL){
			$result=$au->Auth();
			$this->_auth_result=$result;
		}else{
			$result=$this->_auth_result;	
		}
		
		
		
		$_pkg=new PriceKindGroup;
		$pkg=$_pkg->GetItemsByFieldsArr(array('is_calc_price'=>1));
		//print_r($pkg);
		$price_kind_ids=array(); $price_kind_id_vals=array();
		$price_kind_ids[]=0; 	 $price_kind_id_vals[]='все';	
		foreach($pkg as $k=>$v){
			$price_kind_ids[]=$v['id'];
			$price_kind_id_vals[]=$v['name'];	
		}
		$sm->assign('price_kind_ids', $price_kind_ids);
		$sm->assign('price_kind_id_vals', $price_kind_id_vals);
		
		
		//месяцы
		$monthes=$_pfs->GetMonthes();
		$sm->assign('monthes',$monthes);
	
		
		//сотрудники, кто может редактировать любой факт продаж
		$_usg=new UsersSGroup;
		$users=$_usg->GetUsersByRightArr('w', 813);
		$sm->assign('users_instead', $users);
		
		 
		//страны
		$_cous=new SupplierCountryGroup;
		$cous=$_cous->GetItemsArr();
		$sm->assign('cous', $cous);
		
		$sm->assign('pagename',$this->pagename);
		
		$sm->assign('from',$from);
		$sm->assign('to_page',$to_page);
		$sm->assign('pages',$pages);
		$sm->assign('items',$alls);
		
		$sm->assign('can_add',$can_add);
		$sm->assign('can_edit',$can_edit);
		$sm->assign('can_delete',$can_delete);
		
			$sm->assign('can_confirm_price',$can_confirm);
			$sm->assign('can_unconfirm_price',$can_unconfirm);
		$sm->assign('can_super_confirm_price',$can_unconfirm);
		
		$sm->assign('can_change_date',$can_change_date);
		 
	 
		
		$sm->assign('can_restore',$can_restore);
		
		$sm->assign('can_edit',$can_edit);
		$sm->assign('can_edit_super',$can_edit_super);
		
		$sm->assign('can_print',$can_print);
		
		$sm->assign('prefix',$this->prefix);
		
		
		
		$sm->assign('has_header',$has_header);
		
	
		
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri('&', $this->prefix);
		//echo $link;
		$link=$this->pagename.'?'.eregi_replace('&sortmode'.$this->prefix.'=[[:digit:]]+','',$link);
		$sm->assign('link',$link);
		
		//показ конфигурации
		$sm->assign('view', $this->_view->GetColsArr($result['id']));
		$sm->assign('unview', $this->_view->GetColsUnArr($result['id']));
		
		//echo $sm->fetch($template);
		
		return $sm->fetch($template);
	}
	
	
}
?>