<?
require_once('price_kind_group.php');
require_once('kpitem.php');

require_once('posgroupgroup.php');
require_once('pl_prodgroup.php');
require_once('kpnotesgroup.php');

require_once('kp_form_item.php');
require_once('rl/rl_man.php');
require_once('kp_rights_group.php');

class AnKp{
	public $view_rules; //экземпляр класса группы правил просмотра КП
	
	function __construct(){
		$this->view_rules=new KpRightsGroup;	
	}
	
	
	public function ShowData(
	$org_id, $pdate1, $pdate2,
	$prefix,
	$template, 
	DBDecorator $dec,
	$pagename='files.php', 
	$do_it=false, 
	$can_print=false,
	$result=NULL,
	$can_edit=false,
	$can_view_all=false 
 
	){
		
		$_users=array();
		$_suppliers=array();
		
		$_pk=new PriceKindGroup;
		$_prg=new PlProdGroup;
		$_pgg=new PosGroupGroup;	
		$_kp=new KpItem;
		$_notes_group=new KpNotesGroup;
		
		$_rl=new RLMan;
		
		$pdate2+=24*60*60-1;
		
		
		
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			
			$db_flt=' and '.$db_flt;
		}
		
		
		
		
		//фильтровать оборудование из закрытых категорий, пр-лей
		$flt= ''; $flts=array();
		$restricted_prods=$_rl->GetBlockedItemsArr($result['id'], 34, 'w', 'pl_producer', 0);
		if(count($restricted_prods)>0) $flts[]='  eq.producer_id not in('.implode(', ', $restricted_prods).') ';
		
		//запросить из базы список закрытых для пол-ля категорий
		$restricted_cats=$_rl->GetBlockedItemsArr($result['id'], 1, 'w', 'catalog_group', 0);
		if(count($restricted_cats)>0){
			//если закрыта подгруппа
			$flts[]='  eq.group_id not in('.implode(', ', $restricted_cats).') ';
			//если закрыта группа - учесть ее подгруппы
			$flts[]='  eq.group_id not in(select id from catalog_group where parent_group_id in('.implode(', ', $restricted_cats).') )';
		}
		
		
		
		
		$sm=new SmartyAdm;
		
		
		
		
		$sql='select p.*,
					
					 sp.full_name as supplier_name, 
					spo.name as opf_name, 
					c.name, c.position, 
					u.name_s as confirmed_price_name, u.login as confirmed_price_login,
					pk.name as price_kind_name,
					sd.name as supply_pdate_name,
					st.name as status_name,
					
					mn.id as manager_id, u.name_s as  manager_name, u.login as manager_login 
				 
					
				from kp as p				
					 left join supplier as sp on p.supplier_id=sp.id
					left join opf as spo on spo.id=sp.opf_id 
					
					left join user as u on p.user_confirm_price_id=u.id
				 	left join user as mn on p.manager_id=mn.id
					left join pl_price_kind as pk on p.price_kind_id=pk.id
					left join kp_supply_pdate_mode as sd on p.supply_pdate_id=sd.id
					left join document_status as st on st.id=p.status_id
					left join supplier_contact as c on p.contact_id=c.id
					
				 
				where p.org_id="'.$org_id.'" and p.is_confirmed_price=1
					and pdate between '.$pdate1.'  and '.$pdate2.'
					';
		
		$sql.=$db_flt;
		
		if(count($flts)>0) {
			$flt=' and p.id in(select kp_id from kp_position where position_id in(select eq.id from catalog_position as eq where '.implode(' and ',$flts).')) ';
			$sql.=$flt;
		}
		
		
		$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
		
		
		
		$alls=array();
		 
		
		//echo $sql;
		if($do_it){  
		 $set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
		  $rs=$set->GetResult();
		  $rc=$set->GetResultNumRows();
		  
		 //echo $sql.'<br>';
		 // echo ' результат: '.$rc;
		  //подчиненные менеджеры (если есть)
		$subords=$this->view_rules->GetManagers($result);
		
		//оборудование по спецправам (если есть)
		$eqs=$this->view_rules->GetList($result, 2);
		
		  
		  for($i=0; $i<$rc; $i++){
			  
			  
			  $f=mysqli_fetch_array($rs);
			  foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			  
			  $f['pdate']=date("d.m.Y",$f['pdate']);
			
				if($f['supply_pdate']>0) $f['supply_pdate']=date("d.m.Y",$f['supply_pdate']);
				else $f['supply_pdate']='-';
				
				if($f['valid_pdate']>0) $f['valid_pdate']=date("d.m.Y",$f['valid_pdate']);
				else $f['valid_pdate']='-';
				
				
				
				
				$positions=$_kp->GetPositionsArr($f['id'], false);
				
				$f['positions']=$positions;
				
				$f['total_cost']=$_kp->CalcCost($f['id'], $positions,  $result);
				$f['currency']=$_kp->currency;
				
				 
				 
				//есть ли печатная форма...
				$main_id=0; $txt_kp='';
				foreach($positions as $k=>$v){
					if($v['parent_id']==0){
						$main_id=$v['pl_position_id'];
						$txt_kp=$v['txt_for_kp'];
						break;	
					}
				}	
				$_pl=new PlPosItem;
				$pl=$_pl->GetItemById($main_id);
				$_kp_form=new KpFormItem;
				$kp_form=$_kp_form->GetItemById($pl['kp_form_id']);
				$f['has_print_form']=(($kp_form!==false)&&(strlen($txt_kp)>0));
				
				
				if($f['confirm_price_pdate']!=0) $f['confirm_price_pdate']=date("d.m.Y H:i:s",$f['confirm_price_pdate']);
				else $f['confirm_price_pdate']='-';
				
				
				
				$f['notes']=$_notes_group->GetItemsByIdArr($f['id'],0,0,false,false,false,0,false);
			  
			  
			  	
			//флаг доступности операций
			$has_access=false;
			
			$has_access=$has_access||$can_view_all;
			//свои кп
			if(!$has_access) $has_access=$has_access||($result['id']== $f['user_manager_id']);
			//кп подчиненных менеджеров
			if(!$has_access) $has_access=$has_access||in_array($f['user_manager_id'], $subords);		
			
			//кп по Особым правам по оборудованию
			if(!$has_access) $has_access=$has_access||in_array($main_id, $eqs);
			
			$f['has_access']=$has_access;
			
			if(!in_array($f['user_manager_id'], $_users)) $_users[]=$f['user_manager_id'];
			
			if(!in_array($f['supplier_name'].', '.$f['opf_name'],  $_suppliers)) $_suppliers[]=$f['supplier_name'].', '.$f['opf_name'];
			  
			  $alls[]=$f;	
		  }
			  
			 
		  
		}
		
		//echo ' счетов: '.count($alls);	
		$sm->assign('items',$alls);
		
		//итого
		$sm->assign('total_kp', count($alls));
		$sm->assign('total_managers', count($_users));
		$sm->assign('total_suppliers', count($_suppliers));
		$sm->assign('total_suppliers_list', $_suppliers);
		
		
		
		
		
		//заполним шаблон полями
		 
		$current_eq_name='';
		$current_group_id='';
		$current_producer_id='';
		$current_two_group_id='';
		
		$supplier='';
		
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			if($v->GetName()=='eq_name') $current_eq_name=$v->GetValue();
			
				if($v->GetName()=='supplier_name') $supplier=$v->GetValue();
		
			if($v->GetName()=='group_id') $current_group_id=$v->GetValue();
			if($v->GetName()=='producer_id') $current_producer_id=$v->GetValue();
			if($v->GetName()=='two_group_id') $current_two_group_id=$v->GetValue();
			
			
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		if($current_eq_name!=''){
			$current_group_id='0';
			$current_producer_id='0';
			$current_two_group_id='0';
			$sm->assign('group_id',$current_group_id);
			$sm->assign('producer_id',$current_producer_id);
			$sm->assign('two_group_id',$current_two_group_id);
			
		}
		
		
		//виды цен КП
		$price_kinds=$_pk->GetItemsByFieldsArr(array('is_calc_price'=>1));
		$sm->assign('price_kinds',$price_kinds);	
		
		
		//группы, подгруппы, пр-ль
		$groups=$_pgg->GetItemsArr(0);
		$groups1=array();
		foreach($groups as $k=>$v){
			if(	$_rl->CheckFullAccess($result['id'], $v['id'], 1, 'w', 'catalog_group', 0)) $groups1[]=$v; 
		}
		 
		
		
		$sm->assign('groups',$groups1);
		
		$producers=array(); $producers1=array();
		if(($current_group_id!='')&&($current_group_id>0)){
			$producers=$_prg->GetItemsByIdArr($current_group_id);
		}
		
		foreach($producers as $k=>$v){
			if(	$_rl->CheckFullAccess($result['id'], $v['id'], 34, 'w', 'pl_producer', 0)) $producers1[]=$v; 
		}
		$sm->assign('producers',$producers1);
		
		$two_groups=array(); $two_groups1=array();
		if(($current_group_id!='')&&($current_group_id>0)&&($current_producer_id!='')&&($current_producer_id>0)){
			$two_groups=$_pgg->GetItemsArrByCategoryProducer($current_group_id, $current_producer_id);
		}
		foreach($two_groups as $k=>$v){
			if(	$_rl->CheckFullAccess($result['id'], $v['id'], 1, 'w', 'catalog_group', 0)) $two_groups1[]=$v; 
		}
		
		$sm->assign('two_groups',$two_groups1);
		
		//контрагент
		if(strlen($supplier)>0){
			$_ids=explode(';', $supplier);
			
			$sql='select s.*, opf.name as opf_name from supplier as s left join opf as opf on s.opf_id=opf.id where s.id in('.implode(', ', $_ids).') order by s.full_name';
			
			 
			 
			$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			$our_users=array();
			for($i=0; $i<$rc; $i++){
				
				$f=mysqli_fetch_array($rs);
				$our_users[]=$f;
			}
			$sm->assign("our_suppliers", $our_users);
		 
		}
		
		
		
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri('&',$prefix );
		$link=$pagename.'?'.eregi_replace('&sortmode'.$prefix.'=[[:digit:]]+','',$link).'&doSub'.$prefix.'=1';
		$sm->assign('link',$link);
		//$sm->assign('sortmode',$sortmode);
		
		
		$sm->assign('pagename',$pagename);
		
		$sm->assign('can_print',$can_print);
		$sm->assign('can_edit',$can_edit);
		 
		$sm->assign('do_it',$do_it);	
		
	 	$sm->assign('prefix',$prefix);
		
		
		$sm->assign('pagename',$pagename);
		//$sm->assign('loadname',$loadname);		
			
		return $sm->fetch($template);
	}
	
	
	
}
?>