<?
require_once('abstractitem.php');

//����������� �������
class KpSupplyItem extends AbstractItem{
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='kp_supply';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
}
?>