<?
require_once('abstractitem.php');
require_once('positem.php');

//������� ���� ������ �� ������
class PlDisMaxValItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='pl_discount_maxval';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='pl_position_id';	
	}
	
	
	
	
}
?>