<?
require_once('price_kind_group.php');
require_once('kpitem.php');

require_once('posgroupgroup.php');
require_once('pl_prodgroup.php');
require_once('kpnotesgroup.php');

require_once('kp_form_item.php');
require_once('rl/rl_man.php');

class AnPlChanges{
	public $objects;
	public $descriptions;
	
	public function __construct(){
		$this->objects=array(601, 602, 603);
		$this->descriptions=array(
		'редактировал базовую скидку поставщика',
		'редактировал дополнительную скидку поставщика',
		'редактировал рентабельность ExW',
		'редактировал рентабельность DDPM',
		'редактировал доставку до Москвы',
		'редактировал сбор',
		
		'редактировал СВХ, брокер', 
		
		
		'изменена цена',
		'создал позицию прайс-листа',
		'создана опция позиции',
		'удалил позицию прайс-листа',
		'удалил опцию прайс-листа'
		
		);
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
	$can_edit=false
	){
		
		
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
		$flt= '';
		$restricted_prods=$_rl->GetBlockedItemsArr($result['id'], 34, 'w', 'pl_producer', 0);
		if(count($restricted_prods)>0) $flt.=' and pos.producer_id not in('.implode(', ', $restricted_prods).') ';
		
		//запросить из базы список закрытых для пол-ля категорий
		$restricted_cats=$_rl->GetBlockedItemsArr($result['id'], 1, 'w', 'catalog_group', 0);
		if(count($restricted_cats)>0){
			//если закрыта подгруппа
			$flt.=' and pos.group_id not in('.implode(', ', $restricted_cats).') ';
			//если закрыта группа - учесть ее подгруппы
			$flt.=' and pos.group_id not in(select id from catalog_group where parent_group_id in('.implode(', ', $restricted_cats).') )';
		}
		
		
		
		$sm=new SmartyAdm;
		
		
		
	 	$_gi=new PosGroupItem;
		
		
		$_descrs=array();
		foreach($this->descriptions as $k=>$v) $_descrs[]='"'.$v.'"';
		
		
		$sql='select p.*,
			pos.id as pos_id, pos.name, pos.code, pos.group_id, pos.producer_id,
			g.name as group_name,
			pp.name as producer_name,
			
			mn.id as manager_id, mn.name_s as  manager_name, mn.login as manager_login
			
			from action_log as p
			left join catalog_position as pos on p.affected_object_id=pos.id
			left join catalog_group as g on pos.group_id=g.id
			left join pl_producer as pp on pos.producer_id=pp.id
			left join user as mn on mn.id=p.user_subj_id
		where p.object_id in('.implode(', ',$this->objects).')
			and p.description in('.implode(',', $_descrs).')
			and pdate between '.$pdate1.'  and '.$pdate2.'
			and pos.parent_id=0
		';	
		
		
		$sql.=$db_flt;
		$sql.=$flt;
		
		
		$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
		
		//echo $sql;
		
		$alls=array();
		 
		
		//echo $sql;
		if($do_it){  
		 $set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
		  $rs=$set->GetResult();
		  $rc=$set->GetResultNumRows();
		  
		 // echo $sql.'<br>';
		 // echo ' результат: '.$rc;
		  
		  
		  for($i=0; $i<$rc; $i++){
			   $f=mysqli_fetch_array($rs);
			  foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			  
			  $f['pdate']=date("d.m.Y H:i:s",$f['pdate']);
			  /*$gi=$_gi->GetItemById($f['group_id']);
			  if($gi['parent_group_id']>0){
				  $gi2=$_gi->GetItemById($gi['parent_group_id']);	
				  if($gi2['parent_group_id']>0){
					  $gi3=$_gi->GetItemById($gi2['parent_group_id']);		
					  
					  $f['group_name']=stripslashes($gi3['name'].'-> '.$gi2['name'].'-> '.$gi['name']);
				  }else{
					  
					  $f['group_name']=stripslashes($gi2['name'].'-> '.$gi['name']);	
				  }
			  }
			  		*/	  
			  
			  $alls[]=$f;	
		  }
			
		  $alls1=array();		  
		  $obor=array();
		  //пересоритируем массив - выведем уник. оборудование:
		  foreach($alls as $k=>$v){
			$_eq=array('pos_id'=>$v['pos_id'], 'name'=>$v['name'], 'code'=>$v['code'], 'group_name'=>$v['group_name'], 'group_id'=>$v['group_id'], 'producer_name'=>$v['producer_name']);  
			
		  	if(!in_array($_eq, $obor)) $obor[]=$_eq;	  
			  
		  }
		  
		  
		  
		 // print_r($obor);
		  
		  //сформируем выходной массив:
		  foreach($obor as $k=>$v){
			 $items=array();
			 
			 $gi=$_gi->GetItemById($v['group_id']);
			  if($gi['parent_group_id']>0){
				  $gi2=$_gi->GetItemById($gi['parent_group_id']);	
				  if($gi2['parent_group_id']>0){
					  $gi3=$_gi->GetItemById($gi2['parent_group_id']);		
					  
					  $v['group_name']=stripslashes($gi3['name'].'-> '.$gi2['name'].'-> '.$gi['name']);
				  }else{
					  
					  $v['group_name']=stripslashes($gi2['name'].'-> '.$gi['name']);	
				  }
			  }
			 
			 
			 
			 foreach($alls as $kk=>$f){
				 $f['group_name']=$v['group_name'];
				 
			 	if($f['pos_id']==$v['pos_id']) $items[]=$f;
			
			 }
			
			 $alls1[]=array('obor'=>$v, 'items'=>$items);  
		  }
		  
		  
		}
		
		//echo ' счетов: '.count($alls);	
		$sm->assign('items',$alls1);
		
		
		
		
		
		
		//заполним шаблон полями
		 
		$current_eq_name='';
		$current_group_id='';
		$current_producer_id='';
		$current_two_group_id='';
		
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			if($v->GetName()=='eq_name') $current_eq_name=$v->GetValue();
		
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
		/*$price_kinds=$_pk->GetItemsByFieldsArr(array('is_calc_price'=>1));
		$sm->assign('price_kinds',$price_kinds);	*/
		
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