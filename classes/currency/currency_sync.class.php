<?
 

//����������� �������
class CurrencySyncItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='currency_syncs';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
}
?>