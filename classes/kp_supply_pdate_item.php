<?
require_once('abstractitem.php');

//���� ��������
class KpSupplyPdateItem extends AbstractItem{
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='kp_supply_pdate_mode';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
}
?>