<?
require_once('abstractitem.php');

//����������� �������
class KpFormItem extends AbstractItem{
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='pl_kp_form';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
}
?>