<?
 

//����������� �������
class PlRuleKindItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='pl_rule_kind';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
}
?>