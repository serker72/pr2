<?
require_once('abstractitem.php');
require_once('positem.php');

//������� ��-�� � �����-�����
class PlProdItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='pl_producer';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='position_id';	
	}
	
	
	
	
}
?>