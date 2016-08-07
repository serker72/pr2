<?
require_once('abstractitem.php');
require_once('delivery_user_sync.php');


//������� ��������
class SupplierContactDataItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='supplier_contact_data';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='contact_id';	
	}
	
	
	
	
	//�������
	public function Edit($id,$params){
		
		 
		$item=$this->GetItemById($id);
		if($item['value']!=$params['value']) DeliveryUserSync::Put(array(array('action'=>1, 'tablename'=>'supplier_contact_data', 'key'=>$id, 'field'=>'value', 'value'=>$params['value'])));
		
		parent::Edit($id, $params);
	}
	
	//�������
	public function Del($id){
		
		DeliveryUserSync::Put(array(array('action'=>2, 'tablename'=>'supplier_contact_data', 'key'=>$id)));
		
		parent::Del($id);
	}	
	
	
	
}
?>