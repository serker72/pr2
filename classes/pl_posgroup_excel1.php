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
require_once('authuser.php');

require_once('pl_dismaxvalitem.php');

//  группа прайс-лист для формиррования pdf
class PlPosGroupExcel1 extends PlPosGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='pl_position';
		$this->pagename='pricelist.php';		
		$this->subkeyname='position_id';	
		$this->vis_name='is_shown';		
		$this->itemsname='items';
		$this->items=array();
		
	}
	
	
	
	public function ShowPrint($group_id, $price_kind_id, $producer_id, $two_group_id, $id, $with_options, $template, $result=NULL, &$alls, $price_kind_id_for_max_discount=1){
		$alls=array();
		$au=new AuthUser;
		if($result===NULL) $result=$au->Auth();
		
		$_dmi=new PlDisMaxValItem;
		
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		
		
		$_pgi=new PosGroupItem();
		$group=$_pgi->GetItemById($group_id);
		$two_group=$_pgi->GetItemById($two_group_id);
		
		
		
		$_pki=new PriceKindItem;
		$pki=$_pki->GetItemById($price_kind_id);
		
		
		$_pi=new PlPosItem;
		$pi=$_pi->GetItemById($id);
		
		$_prod=new PlProdItem;
		$prod=$_prod->GetItemById($producer_id);
		
			$_ppi=new PlPositionPriceItem;
		
		$_ci=new PlCurrItem;
		$currency=$_ci->GetItemById(CURRENCY_DEFAULT_ID);
		
		$_producer_names=array();
		$_category_names=array();
		
		
		//получим всех пр-лей
		$flt='';
		if($producer_id>0) $flt.=' and id="'.$producer_id.'" ';
		
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
			$sql1='select * from catalog_group where parent_group_id="'.$group_id.'" and id not in(10,11) and producer_id="'.$f['id'].'" '.$flt.' order by name, id';
			$set1=new mysqlset($sql1);
			$rs1=$set1->getResult();
			$rc1=$set1->GetResultNumRows();
			for($i1=0; $i1<$rc1; $i1++){
				$g=mysqli_fetch_array($rs1);
				foreach($g as $k=>$v) $g[$k]=stripslashes($v);
				
				$_category_names[]=$g['name'];
				
				//по категории получим товар, добавим ограничение на товар, если есть
				$this-> GainSql($sql2, $sql_count2,  CURRENCY_DEFAULT_ID, $price_kind_id);
				
				$flt='';
				if($id>0) $flt.=' and pl.id="'.$id.'" ';
				$sql2.=$flt.' and p.group_id="'.$g['id'].'" ';
				$set2=new mysqlset($sql2);
				$rs2=$set2->getResult();
				$rc2=$set2->GetResultNumRows();
				
				$goods=array();
				for($i2=0; $i2<$rc2; $i2++){
					$h=mysqli_fetch_array($rs2);
					foreach($h as $k=>$v) $h[$k]=stripslashes($v);
				
					//перезагрузить цену 
					
					
					 
				//базовая цена Exw
					$h['price_base_exw']=$_pi->CalcPriceF($h['pl_id'], $f['currency_id'],  NULL, NULL, NULL,  NULL,  NULL, 3, NULL);
				
				$h['price_exw']=$_pi->CalcPriceF($h['pl_id'],$f['currency_id'],NULL,NULL,1,0,0,2);
				$h['price_ddpm']=$_pi->CalcPriceF($h['pl_id'],$f['currency_id'],NULL,NULL,1,0,0,1);
				
					
					//Найдем макс. скидку менеджера и рук-ля
					$dmi_1=$_dmi->GetItemByFields(array('pl_position_id'=>$h['pl_id'], 'discount_id'=>1, 'price_kind_id'=>$price_kind_id_for_max_discount 	));
					
					$h['max_discount_manager']=$dmi_1['value'];
					
					$dmi_2=$_dmi->GetItemByFields(array('pl_position_id'=>$h['pl_id'], 'discount_id'=>2, 'price_kind_id'=>$price_kind_id_for_max_discount 	));
					
					$h['max_discount_ruk']=$dmi_2['value'];
					
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
		$sm->assign('pki', $pki);
		$sm->assign('pi', $pi);
		$sm->assign('prod', $prod);
		$sm->assign('currency', $currency);
		
		$sm->assign('group_id', $group_id);
		$sm->assign('price_kind_id', $price_kind_id);
		$sm->assign('producer_id', $producer_id);
		$sm->assign('two_group_id', $two_group_id);
		$sm->assign('id', $id);
		$sm->assign('with_options', $with_options);
		
		
		
		if(count($_producer_names)>1) $sm->assign('producer_names', implode(', ',$_producer_names));
		else $sm->assign('producer_names', $prod['name']);
		
		if(count($_category_names)>1) $sm->assign('category_names', implode(', ',$_category_names));
		else  $sm->assign('category_names', $two_group['name']);
		
		$sm->assign('pdate', date("d.m.Y H:i:s"));
		$username=$result['login'];
		$username=stripslashes($result['name_s']).' '.$username;	
		
		$sm->assign('username',$username);
		
		return $sm->fetch($template);
	}
	
	
	
	
	
	
	 
}
?>