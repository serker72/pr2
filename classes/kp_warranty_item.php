<?
require_once('abstractitem.php');

//����������� �������
class KpWarrantyItem extends AbstractItem{
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='kp_warranty';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
}
?>