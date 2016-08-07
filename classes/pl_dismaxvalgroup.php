<?

require_once('abstractgroup.php');
require_once('posgroupgroup.php');
require_once('posgroupitem.php');
require_once('positem.php');
require_once('pl_positem.php');

require_once('pl_dismaxval_user_group.php');

//  группа макс скидка по товару
class PlDisMaxValGroup extends AbstractGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='pl_discount_maxval';
		$this->pagename='pricelist.php';		
		$this->subkeyname='pl_position_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	//список позиций
	public function GetItemsByIdArr($id, $current_id=0){
		$arr=array();
		
		
		$sql='select distinct d.*, 
			dl.value as dl_value, dl.rub_or_percent as dl_rub_or_percent
		from 
		pl_discount as d left join 
		pl_discount_maxval as dl on d.id=dl.discount_id
		
		 and pl_position_id="'.$id.'" order by  id asc';
		
		//echo $sql;
		
		 $set=new MysqlSet($sql);
		
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
	
	//список макс. скидок для позиции п.л. и вида цен
	//виды скидок, включая их ограничения
	public function GetItemsByKindPosArr($position_id, $price_kind_id, $current_id=0, $user_id=0){
		$arr=array();
		
		
		$sql='select distinct d.*, 
			dl.value as dl_value, dl.rub_or_percent as dl_rub_or_percent
		from 
		pl_discount as d left join 
		pl_discount_maxval as dl on d.id=dl.discount_id
		
		 and pl_position_id="'.$position_id.'" and price_kind_id="'.$price_kind_id.'" order by  id asc';
		
		//echo $sql;
		
		 $set=new MysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		$_gi=new PosGroupItem;
		$_pi=new PlPosItem;
		$_skg=new PlDisMaxValUserGroup;
		
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			//$f['dl_value'] заменять на что-то другое.
			 
			
			$dl_value=$f['dl_value'];
			if($user_id!=0){
				
				$pi=$_pi->GetItemById($position_id); 
			 
			 	//найти группу (последовательность) и/или поставщика
				//по ним найдем правила!
				$_resolve=array();
				
				
				$gi=$_gi->GetItemById($pi['group_id']);
				
				//print_r($gi);
				
				
				if($gi['parent_group_id']>0){
					$gi2=$_gi->GetItemById($gi['parent_group_id']);	
					if($gi2['parent_group_id']>0){
						$gi3=$_gi->GetItemById($gi2['parent_group_id']);		
						
						//$f['group_name']=stripslashes($gi3['name'].'-> '.$gi2['name'].'-> '.$gi['name']);
						
						 
						
						$_resolve[]=array(
							'name'=>'parent_group_id',
							'value'=>$pi['group_id']				
						);
						
						
						
						$_resolve[]=array(
							'name'=>'parent_group_id',
							'value'=>$gi2['id']				
						);
												
						$_resolve[]=array(
							'name'=>'producer_id',
							'value'=>$pi['producer_id']				
						);	
						
						$_resolve[]=array(
							'name'=>'parent_group_id',
							'value'=>$gi3['id']				
						);						
						
					}else{
						
						//$f['group_name']=stripslashes($gi2['name'].'-> '.$gi['name']);	
											
							
						$_resolve[]=array(
							'name'=>'parent_group_id',
							'value'=>$pi['group_id']				
						);
						
						$_resolve[]=array(
							'name'=>'producer_id',
							'value'=>$pi['producer_id']				
						);
						
						$_resolve[]=array(
							'name'=>'parent_group_id',
							'value'=>$gi2['id']				
						);				
						
					}
				}else{
					$_resolve[]=array(
						'name'=>'parent_group_id',
						'value'=>$pi['group_id']				
					);
				}
				
				
				
				
				
				
				$rules=$_skg->GetItemsByIdArr($user_id);
				//var_dump($rules);	
				
				//перебор критериев совпадения
				//внутри цикла - перебор правил
				//если совпало хоть одно из правил на текущем критерии - подставить макс. значение, выход
				//echo '<pre>';
				//print_r($_resolve);
				
				foreach($_resolve as $k=>$v){
					$matched=false;
					
					//print_r($v);
					
					foreach($rules as $rk=>$rv){
						//if($rk	
						//print_r($f);
						//$v['name']==$rv[
						if(($v['value']==$rv[$v['name']])&&($f['id']==$rv['discount_id'])){
							$matched=$matched||true;
							
							$dl_value=$rv['value'];
							//echo $dl_value;
							break;	
						}
					}
					if($matched) break;
					
				}
				
				
				//echo '</pre>';
				
			}
			
			$f['dl_value']=$dl_value;
			//echo $f['dl_value'];
			
			$arr[]=$f;
		}
		
		return $arr;
	}
}
?>