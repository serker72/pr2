<?
require_once('abstractitem.php');
require_once('sh_i_pospmitem.php');
require_once('sh_i_item.php');

//абстрактный элемент
class ShIPosItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='sh_i_position';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='sh_i_id';	
	}
	
	
	
	//добавить 
	public function Add($params, $pms=NULL){
		
		$code=AbstractItem::Add($params);
		
		if($pms!==NULL){
			//создать +/- для позиции
			$bpm=new ShIPosPMItem;
			
			if($code>0){
				$pms['sh_i_position_id']=$code;
				$bpm->Add($pms);	
			}
		}
		
		return $code;
	}
	
	
	//редактировать
	public function Edit($id,$params,$pms=NULL){
		
		//пересчитать тотал
		//$_itm=new ShIItem;
		$item=$this->GetItemById($id);
		
		if(isset($params['quantity'])&&($params['quantity']!=$item['quantity'])){
			 if(isset($params['price_pm'])&&($params['price_pm']!=$item['price_pm'])) $price=$params['price_pm'];
			 else $price=$item['price_pm'];
			 
			 $params['total']=$params['quantity']*$price;
		}
		
		
		AbstractItem::Edit($id,$params);
		
		if($pms!==NULL){
			//если уже есть пм, то найти иобработать его
			//если нет - то создать
			$_bpm=new ShIPosPMItem;
			$bpm=$_bpm->GetItemByFields(array('sh_i_position_id'=>$id));
			if($bpm===false){
				$pms['sh_i_position_id']=$id;
				$_bpm->Add($pms);	
			}else{
				$pms['sh_i_position_id']=$id;
				$_bpm->Edit($bpm['id'],$pms);	
			}
		}
	}
	
	
	
	//удалить
	public function Del($id){
		
		$query = 'delete from sh_i_position_pm where sh_i_position_id='.$id.';';
		$it=new nonSet($query);
		
		
		parent::Del($id);
	}	
	
	
	
}
?>