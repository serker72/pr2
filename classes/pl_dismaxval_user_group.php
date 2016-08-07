<?

require_once('abstractgroup.php');
require_once('posgroupgroup.php');
require_once('posgroupitem.php');
require_once('positem.php');
require_once('pl_positem.php');

//  группа макс скидка по товару
class PlDisMaxValUserGroup extends AbstractGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='pl_discount_user_maxval';
		$this->pagename='pricelist.php';		
		$this->subkeyname='user_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	//список позиций
	public function GetItemsByIdArr($id, $current_id=0){
		$arr=array();
		
	 	$_gi=new PosGroupItem;
		 
		 $sql='select d.*,
		 cat.name as cat_name,
		 prod.name as prod_name,
		 kind.name as kind_name
		 
		 from pl_discount_user_maxval as d
		 left join catalog_group as cat on d.parent_group_id=cat.id
		 left join pl_producer as prod on d.producer_id=prod.id
		 left join pl_discount as kind on d.discount_id=kind.id
		 
		 where 
		 	d.user_id="'.$id.'"
			';
		 
		
		//echo $sql.'<p>';
		
		 $set=new MysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$f['is_current']=(bool)($f['id']==$current_id);
			
			
				
				$gi=$_gi->GetItemById($f['parent_group_id']);
				if($gi['parent_group_id']>0){
					$gi2=$_gi->GetItemById($gi['parent_group_id']);	
					
					if($gi2['parent_group_id']>0){
						$gi3=$_gi->GetItemById($gi2['parent_group_id']);		
						
						$f['group_name']=stripslashes($gi3['name'].'-> '.$gi2['name'].'-> '.$gi['name']);
					}else{
						
						$f['group_name']=stripslashes($gi2['name'].'-> '.$gi['name']);	
					}
				}else $f['group_name']=stripslashes($gi['name']); 
			
			
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	//список макс. скидок для позиции п.л. и вида цен
	//виды скидок, включая их ограничения
	public function GetItemsByKindPosArr($position_id, $price_kind_id, $current_id=0){
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
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$arr[]=$f;
		}
		
		return $arr;
	}
}
?>