<?

require_once('abstractgroup.php');
require_once('posgroupgroup.php');
require_once('posgroupitem.php');
require_once('positem.php');
require_once('pl_positem.php');

//  ������ �����-����
class PlDisGroup extends AbstractGroup {
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='pl_discount';
		$this->pagename='pricelist.php';		
		$this->subkeyname='position_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
}
?>