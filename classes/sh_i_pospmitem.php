<?
require_once('abstractitem.php');

//����������� �������
class ShIPosPMItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='sh_i_position_pm';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='sh_i_position_id';	
	}
	
	
	/*public function Add($params){
		echo "DDDDDDDDDDDDDDDD";
		print_r($params);
		parent::Add($params);	
	}*/
}
?>