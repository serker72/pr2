<?
require_once('abstractitem.php');
require_once('positem.php');

//������� �����������
class PlDirectionItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='pl_direction';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='position_id';	
	}
	
	
	
	
}
?>