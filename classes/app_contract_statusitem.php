<?
require_once('abstractitem.php');

//����������� �������
class AppContractStatusItem extends AbstractItem{
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='app_contract_status';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
}
?>