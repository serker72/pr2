<?
require_once('pl_posgroup.php');
require_once('abstractgroup.php');
require_once('posgroupgroup.php');
require_once('posgroupitem.php');
require_once('positem.php');
require_once('pl_positem.php');

require_once('pl_disgroup.php');
require_once('pl_dismaxvalgroup.php');
require_once('pl_prodgroup.php');
require_once('pl_proditem.php');
require_once('pl_currgroup.php');
require_once('price_kind_group.php');
require_once('price_kind_item.php');
require_once('pl_curritem.php');
require_once('user_s_item.php');
require_once('authuser.php');

require_once('orgitem.php');
require_once('opfitem.php');
require_once('usercontactdatagroup.php');

require_once('rl/rl_man.php');

//  группа прайс-лист для формиррования pdf
class PlPosGroupPDF extends PlPosGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='pl_position';
		$this->pagename='pricelist.php';		
		$this->subkeyname='position_id';	
		$this->vis_name='is_shown';		
		$this->itemsname='items';
		$this->items=array();
		
	}
	
	
	
	public function ShowPrint($group_id, $price_kind_id, $producer_id, $two_group_id, $id, $with_options, $template, $result=NULL, $show_price_f=0, $manager_id=0, $org_id=0, $lang_rus=1, $lang_en=0, $three_group_id=0){
		$alls=array();
		$au=new AuthUser;
		if($result===NULL) $result=$au->Auth();
		
		$_rl=new RLMan;
		
		
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		
		
		$_pgi=new PosGroupItem();
		$group=$_pgi->GetItemById($group_id);
		$two_group=$_pgi->GetItemById($two_group_id);
		$three_group=$_pgi->GetItemById($three_group_id);
		
		$currency_id=$this->GetCurrencyId($group_id);
		
		
		$_pki=new PriceKindItem;
		$pki=$_pki->GetItemById($price_kind_id);
		
		
		$_pi=new PlPosItem;
		$pi=$_pi->GetItemById($id);
		
		$_prod=new PlProdItem;
		$prod=$_prod->GetItemById($producer_id);
		
		$_ppi=new PlPositionPriceItem;
		
		$_ci=new PlCurrItem;
		$currency=$_ci->GetItemById($currency_id);
		
		$_producer_names=array();
		$_category_names=array();
		$_subcategory_names=array();
		
		
		//получим всех пр-лей
		$flt='';
		if($producer_id>0) $flt.=' and id="'.$producer_id.'" ';
		
		//фильтровать по rl-уровню
		//найти айди всех пр-лей, к кому закрыт доступ?
		//запросить из базы массив айди объектов, к кому перекрыт доступ текущему пол-лю $result['id'] pl_producer 34
		$restricted_prods=$_rl->GetBlockedItemsArr($result['id'], 34, 'w', 'pl_producer', 0);
		if(count($restricted_prods)>0) $flt.=' and id not in('.implode(', ', $restricted_prods).') ';
		
		
		$sql='select * from pl_producer where group_id="'.$group_id.'" '.$flt.' order by name, id';
		
		
		$set=new mysqlset($sql);
		$rs=$set->getResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			$_producer_names[]=$f['name'];
			
			//по пр-лю получим категорию
			$cats=array();
			$flt=''; 
			if($two_group_id>0) $flt.=' and id="'.$two_group_id.'" ';
			
			 
				
			
			//запросить из базы список закрытых для пол-ля категорий
			$restricted_cats=$_rl->GetBlockedItemsArr($result['id'], 1, 'w', 'catalog_group', 0);
			if(count($restricted_cats)>0) $flt.=' and id not in('.implode(', ', $restricted_cats).') ';
			
			
			$sql1='select * from catalog_group where parent_group_id="'.$group_id.'" and producer_id="'.$f['id'].'" /*and id not in(10,11)*/ '.$flt.' order by name, id';
			$set1=new mysqlset($sql1);
			$rs1=$set1->getResult();
			$rc1=$set1->GetResultNumRows();
			
		//	echo $sql1.'<br>';
			
			 
			for($i1=0; $i1<$rc1; $i1++){
				$g=mysqli_fetch_array($rs1);
				foreach($g as $k=>$v) $g[$k]=stripslashes($v);
				
				$_category_names[]=$g['name'];
				
				//по категории получим товар, добавим ограничение на товар, если есть
				$this-> GainSql($sql2, $sql_count2,  $currency_id, $price_kind_id);
				
				
				
				$flt='';
				if($id>0) $flt.=' and pl.id="'.$id.'" ';
				//$sql2.=$flt.' and p.group_id="'.$g['id'].'" ';
				////нужно также получать товар из подкатегорий...
			 	$_pgg=new PosGroupGroup;
				
				$pgg=$_pgg->GetItemsByIdArr($g['id']);
				$group_ids=array();
				foreach($pgg as $p_k=>$p_v) { $group_ids[]=$p_v['id']; $_subcategory_names[]=$p_v['name'];}
				
				$group_ids[]=$g['id'];
				
				$sql2.=$flt.' and p.group_id in ('.implode(', ',$group_ids).') ';
			 	
				
				//$sql2.=' order by name asc, id asc';
				
				//echo $sql2.'<br><br>';
				
				$set2=new mysqlset($sql2);
				$rs2=$set2->getResult();
				$rc2=$set2->GetResultNumRows();
				
				$goods=array();
				for($i2=0; $i2<$rc2; $i2++){
					$h=mysqli_fetch_array($rs2);
					foreach($h as $k=>$v) $h[$k]=stripslashes($v);
				
					//перезагрузить цену 
					
					
					if($pki['is_calc_price']==1){
						 //найдем базовый вид цены
						 $base_pki=$_pki->GetItemByFields(array('is_calc_price'=>0, 'group_id'=>$pki['group_id']));
						 //найдем базовую цену
						 $base_pi=$_ppi->GetItemByFields(array('pl_position_id'=>$h['pl_id'], 'currency_id'=>$f['currency_id'], 'price_kind_id'=>$base_pki['id']));
						 $h['price']=$_pi->CalcPrice($h['pl_id'], $f['currency_id'],  $h, $price_kind_id, NULL,  $base_pki, $base_pi);
					}else{
						$base_pki=NULL;
						$base_pi=NULL;
						$h['price_f']=$_pi->CalcPriceF($h['pl_id'], $f['currency_id'],  NULL, NULL, NULL,  NULL,  NULL, $price_kind_id, NULL);
					}
					
					//по товару получить опции, если необходимо
					$opts=array();
					if($with_options==1){
						
						//$opts=$this->ShowOptionsArr($h['pl_id'], $currency_id, $price_kind_id,NULL,NULL,true);
						//var_dump($opts);
						$this->GainSqlOptions($h['pl_id'], $sql3,  $f['currency_id'], $price_kind_id);
						
						 
						$set3=new mysqlset($sql3);
						$rs3=$set3->getResult();
						$rc3=$set3->GetResultNumRows();
						
						
						$old_group_id=0;
						
						for($i3=0; $i3<$rc3; $i3++){
							$h3=mysqli_fetch_array($rs3);
							foreach($h3 as $k=>$v) $h3[$k]=stripslashes($v);
							
							if($h3['pl_group_id']!=$old_group_id){
								$opts[]=array('kind'=>'group', 'name'=>$h3['pl_group_name'], 'name_en'=>$h3['pl_group_name_en']);
							}
							
							$f['kind']='option';
											
							
							if($pki['is_calc_price']==0){
								$h3['price_f']=$_pi->CalcOptionPriceF($h3['pl_id'], $f['currency_id'],   NULL,  $h3,  NULL,   NULL,   NULL,   $price_kind_id ,   $pki);
							}else{
								 //переопределить цену
								 $h3['price']=$_pi->CalcOptionPrice($h3['pl_id'], $f['currency_id'],  $h3, $price_kind_id,$pki, $base_pki, NULL);
								 
								 
							}
							
							
							$opts[]=$h3;
							$old_group_id=$h3['pl_group_id'];
						}
						
						
					}
					
					$h['opts']=$opts;
					
					$goods[]=$h;
				}
				
				$g['goods']=$goods;
				$cats[]=$g;
			}
			$f['cats']=$cats;
			
			
			$alls[]=$f;	
		}
		
		/*echo '<pre>';
		print_r($alls);
		echo '</pre>';
		*/
		
		
		$sm->assign('items', $alls);
		
		$sm->assign('group', $group);
		$sm->assign('two_group', $two_group);
		$sm->assign('three_group', $three_group);
		$sm->assign('pki', $pki);
		$sm->assign('pi', $pi);
		$sm->assign('prod', $prod);
		$sm->assign('currency', $currency);
		
		$sm->assign('group_id', $group_id);
		$sm->assign('price_kind_id', $price_kind_id);
		$sm->assign('producer_id', $producer_id);
		$sm->assign('two_group_id', $two_group_id);
		$sm->assign('three_group_id', $three_group_id);
		$sm->assign('id', $id);
		$sm->assign('with_options', $with_options);
		
		$sm->assign('lang_rus', $lang_rus);
		$sm->assign('lang_en', $lang_en);
		
		$sm->assign('show_price_f',  $show_price_f); //показать цену со скидками для прайса п-ка
		
		if((count($_producer_names)>1)||($prod==false)) $sm->assign('producer_names', implode(', ',$_producer_names));
		else $sm->assign('producer_names', $prod['name']);
		
		if((count($_category_names)>1)||($two_group===false)) $sm->assign('category_names', implode(', ',$_category_names));
		else  $sm->assign('category_names', $two_group['name']);
		
		if((count($_subcategory_names)>1)||($three_group===false)) $sm->assign('subcategory_names', implode(', ',$_subcategory_names));
		else  $sm->assign('subcategory_names', $three_group['name']);
		
		
		
		
		$sm->assign('manager_id',  $manager_id); //ващ менеджер
		//кто подписал
		if($manager_id>0){
			$_ui=new UserSItem;
			$user=$_ui->GetItemById($manager_id);
			$sm->assign('m_user',$user);
			//контакты
			$rg=new UserContactDataGroup;
			$sm->assign('m_contacts',$rg->GetItemsByIdArr($manager_id));
			
			
			$_orgitem=new OrgItem;	
			$orgitem=$_orgitem->getitembyid($org_id);
			$_opf=new OpfItem;
			$opf=$_opf->GetItemById($orgitem['opf_id']);
			$sm->assign('org', $orgitem);
			$sm->assign('opf', $opf);		
					
		}
		
		$sm->assign('pdate', date("d.m.Y H:i:s"));
		$username=$result['login'];
		$username=stripslashes($result['name_s']).' '.$username;	
		
		$sm->assign('username',$username);
		
		return $sm->fetch($template);
	}
	
	
	
	
	
	
	 
}
?>