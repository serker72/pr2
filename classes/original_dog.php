<?
require_once('abstractgroup.php');
require_once('billpospmformer.php');
require_once('supplieritem.php');

require_once('orgitem.php');
require_once('opfitem.php');
require_once('posonstor.php');
require_once('posgroupgroup.php');
require_once('posgroupitem.php');
require_once('acc_item.php');

require_once('supplieritem.php');

require_once('supcontract_group.php');

class OriginalDog{
	
	
	public $prefix='3';
	
	public function ShowData($has_dog3, $has_uch3, $has_dog_in3, $has_no_dog3, $has_no_uch3, $has_no_dog_in3, DBDecorator $dec, $template, $pagename='original.php',$can_print=false,$do_show_data=true){
		
			
		$_si=new SupplierItem;
		$sm=new SmartyAdm;
		$alls=array();
		
		$_scg=new SupContractGroup;
	
		
		$was_suppliers_arr=array();
		$count_of_docs=0;
		$count_of_accs=0;
		
		
		$storage_flt='';
		$sector_flt='';
		
		$is_storage_flt='';
		$is_sector_flt='';
		
		$mode_flt='';
		
		
		
		
		
		
		
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$db_flt=' and '.$db_flt;
		//	$sql_count.=' and '.$db_flt;	
		}
		
		
		
			$sql='select p.*,
					spo.name as opf_name
					
				from supplier as p 
					left join opf as spo on spo.id=p.opf_id
					where p.is_org=0 
					';
					
			$sql.=' '.$db_flt.' '.$mode_flt.' ';
		
			$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
		
		
		
		
		
		
		if($do_show_data){
		
		//echo $sql;
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		
		
	
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
		    
			
			//проверка наличия исход договоров
			if($has_no_dog3==1){
				$dogs=$_scg->GetItemsByIdArr($f['id'],0,0);	
				
				//echo 'zozozoz';
			//var_dump($dogs);
				$total_dogs_count=count($dogs);
				$count_original_dogs=0;
				foreach($dogs as $k=>$v){
					if($v['has_dog']==1){
						$count_original_dogs++;
					}
				}
				
				
				if($total_dogs_count==0){
					//echo 'zozozoz';
					$f['has_dog']=0;
					if($has_no_dog3==1) $count_of_docs++;	
				}elseif($count_original_dogs<$total_dogs_count){
					$f['has_dog']=0;
					if($has_no_dog3==1) $count_of_docs=$count_of_docs+($total_dogs_count-$count_original_dogs);
					//echo $total_dogs_count-$count_original_dogs;	
				}else{
					$f['has_dog']=1; 
				}
				
				if(($f['has_dog']==1)&&($has_no_dog3==1)) continue;
			}
			
			
			//проверка наличия вход договоров
			if($has_no_dog_in3==1){
				$dogs=$_scg->GetItemsByIdArr($f['id'],0,1);	
				
				//echo 'zozozoz';
			//var_dump($dogs);
				$total_dogs_count=count($dogs);
				$count_original_dogs=0;
				foreach($dogs as $k=>$v){
					if($v['has_dog_in']==1){
						$count_original_dogs++;
					}
				}
				
				
				if($total_dogs_count==0){
					//echo 'zozozoz';
					$f['has_dog_in']=0;
					if($has_no_dog_in3==1) $count_of_docs++;	
				}elseif($count_original_dogs<$total_dogs_count){
					$f['has_dog_in']=0;
					if($has_no_dog_in3==1) $count_of_docs=$count_of_docs+($total_dogs_count-$count_original_dogs);
					//echo $total_dogs_count-$count_original_dogs;	
				}else{
					$f['has_dog_in']=1;
				}
				if(($f['has_dog_in']==1)&&($has_no_dog_in3==1)) continue;
			}
			
			
			
			if(!in_array($f['id'], $was_suppliers_arr)){
				 $was_suppliers_arr[]=$f['id'];
			}
			$count_of_accs++;
			
			
			//if($f['has_dog']==0) $count_of_docs++;
			if($f['has_uch']==0) $count_of_docs++;
			
			
			//echo "$f[full_name] -> $count_of_docs <br>";
			
			
			$alls[]=$f;
		}
				
		}
		
		//заполним шаблон полями
		$current_storage='';
		$current_supplier='';
		$current_user_confirm_price='';
		$current_sector='';
		
		$current_group='';
		$current_two_group='';
		$current_three_group='';
		$current_dimension_id='';
		
		$sortmode=0;
		
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			
		if($v->GetName()=='mode3') $current_mode=$v->GetValue();
			
			if($v->GetName()=='sortmode3') $sortmode=$v->GetValue();
			
			
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		
		
		
		
		
		$sm->assign('current_mode3',$current_mode);
		
		
		$sm->assign('items',$alls);
		$sm->assign('pagename',$pagename);
		
		$sm->assign('can_print',$can_print);
		
		
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri('&', $this->prefix);
		//echo $link;
		$link=$this->pagename.'?'.eregi_replace('&sortmode'.$this->prefix.'=[[:digit:]]+','',$link).'&doSub'.$this->prefix.'=1';
		$sm->assign('link',$link);
		$sm->assign('sortmode3',$sortmode);
		
		$sm->assign('do_it',$do_show_data);
		
		$sm->assign('count_of_suppliers',count($was_suppliers_arr));
		$sm->assign('count_of_docs',$count_of_docs);
		
		$sm->assign('prefix',$this->prefix);
		
		/*echo '<pre>';
		print_r($alls);
		echo '</pre>';*/
		return $sm->fetch($template);
	}
	
	
	
}
?>