<?

require_once('maxformer.php');
require_once('pl_posgroup.php');
require_once('pl_positem.php');

// класс для редактирования позиций ком предл на основе позиций прайслиста
class KpPrepare {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='pl_position';
		$this->pagename='view.php';		
		$this->subkeyname='pl_position_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	public function GetItemsByIdArr($pos, $main_id=0, $currency_id, $price_kind_id, $do_find_max=false){
		$arr=array();
		
		$mf=new MaxFormer;
		$_pdm=new PlDisMaxValGroup;
		$_pi=new PlPosItem;
		
		
		
		//дублировать  функции прайслиста
		$_plg=new PlPosGroup;
		$_plg->GainSql($sql, $sql_count, $currency_id, $price_kind_id);
		$sql.=' and pl.id="'.$main_id.'" ';
		
		//получим позицию
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$h=mysqli_fetch_array($rs);
			
			$h['pl_position_id']=$h['pl_id'];
			
		 	$h['pl_discount_id']=	$h['pl_discount_id'];
			$h['pl_discount_value']=	$h['pl_discount_value'];
			$h['pl_discount_rub_or_percent']=	$h['pl_discount_rub_or_percent'];
					  
			//также получить набор макс. скидок для позиции
			$max_vals=array();
			$max_vals=$_pdm->GetItemsByKindPosArr($h['pl_position_id'], $price_kind_id); //>GetItemsByIdArr($h['pl_position_id']);
			$h['discs1']=$max_vals;
			
			

			$h['in_bills']='-';
			$h['in_sh']='-';
			$h['in_free']='-';
			$h['in_pol']='-';
				
				
			//переопределить цену...
			$h['price']=$_pi->CalcPrice($h['pl_position_id'], $currency_id, NULL,$price_kind_id);
			//echo $h['price'].'<br>';	 		
					
			
			$h['hash']=md5($h['pl_position_id'].'_'.$h['position_id'].'_'.$h['pl_discount_id'].'_'.$h['pl_discount_value'].'_'.$h['pl_discount_rub_or_percent']);
			$h['position_name']=$h['name'];
					
			
			//опции
			$options=array();
			$_plg->GainSqlOptions($h['pl_position_id'],$sql_o, $currency_id, $price_kind_id);
			
			//echo $sql_o.'<p>';
			$set2=new mysqlset($sql_o);
			$rs2=$set2->GetResult();
			$rc2=$set2->GetResultNumRows();
			
			$old_group_id=0;
			for($j=0; $j<$rc2; $j++){
				$f=mysqli_fetch_array($rs2);
				foreach($f as $k=>$v) $f[$k]=stripslashes($v);
				
				
				//echo "-".$f['position_id'].'-';
				$f['pl_position_id']=$f['pl_id'];
			
				$f['pl_discount_id']=	$f['pl_discount_id'];
				$f['pl_discount_value']=	$f['pl_discount_value'];
				$f['pl_discount_rub_or_percent']=	$f['pl_discount_rub_or_percent'];
				
				$f['discs1']=$max_vals;
			
				$f['in_bills']='-';
				$f['in_sh']='-';
				$f['in_free']='-';
				$f['in_pol']='-';
				
				//переопределить цену...
				$f['price']=$_pi->CalcOptionPrice($f['pl_position_id'], $currency_id, NULL,$price_kind_id);
				//echo $f['price'].'<br>';	 		
			
					
				
				$f['hash']=md5($f['pl_position_id'].'_'.$f['position_id'].'_'.$f['pl_discount_id'].'_'.$f['pl_discount_value'].'_'.$f['pl_discount_rub_or_percent']);
				$f['position_name']=$f['name'];
			
			
			
				if($f['pl_group_id']!=$old_group_id){
					$options[]=array('kind'=>'group', 'name'=>$f['pl_group_name']);
				}
				
				$f['kind']='option';
				
				//echo $f['currency_id'];
				
				$options[]=$f;
				$old_group_id=$f['pl_group_id'];
			}
			
			
			$h['options']=$options;
					  
					 
				/*	  'dim_name'=>$h['dim_name'],
					  'dimension_id'=>$h['dimension_id'],
					  'quantity'=>$qua,
					  
					  
					  'price'=>0,
					  'price_f'=>0,
					  'price_pm'=>0,
					  'has_pm'=>false,
					  'cost'=>0,
					  'total'=>0,
					  'plus_or_minus'=>0,
					  'rub_or_percent'=>0,
					  'value'=>0,
					  
					  'discount_rub_or_percent'=>0,
					  'discount_value'=>0,
					  'nds_proc'=>NDS,
					  'nds_summ'=>0,
					 
					  
					  
					  'currency_id'=>$v[19],
					  'signature'=>$h['signature'],
					  'code'=>$h['code'],
					  'parent_id'=>$v[18]*/
			
			
			$arr[]=$h;	
		}
		
		
	
		
		
		return $arr;
	}
	
	
	
	
	
}
?>