<?
require_once('abstractitem.php');

//����������� �������
class KpPaymodeItem extends AbstractItem{
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='kp_paymode';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
}
?>