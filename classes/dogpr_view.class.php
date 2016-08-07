<?
 
require_once('abstract_view.class.php');


//������ ������� ������������
class Dogpr_ViewGroup extends Abstract_ViewGroup{
	 
	//��������� ���� ����
	protected function init(){
		$this->tablename='dogpr_view';
		$this->col_tablename='dogpr_view_field';
		$this->pagename='view.php';		
		$this->subkeyname='user_id';	
		$this->vis_name='is_shown';		
		
		$this->_auth_result=NULL;
		
	}
}

//������� ������� ������������
class Dogpr_ViewItem extends Abstract_ViewItem{
	protected function init(){
		$this->tablename='dogpr_view';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
}

//������� �������
class Dogpr_ColItem extends Abstract_ColItem{
	protected function init(){
		$this->tablename='dogpr_view_field';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
}

//������ �������
class Dogpr_ColGroup extends Abstract_ColGroup {
	 
	//��������� ���� ����
	protected function init(){
		$this->tablename='dogpr_view_field';
		$this->pagename='view.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';		
		
		$this->_auth_result=NULL;
		
	}
}
?>