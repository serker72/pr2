<?
 
require_once('abstract_view.class.php');


//������ ������� ������������
class KpOut_ViewsGroup extends Abstract_ViewGroup{
	 
	//��������� ���� ����
	protected function init(){
		$this->tablename='kp_in_out_views';
		$this->col_tablename='kp_in_out_view_field';
		$this->pagename='view.php';		
		$this->subkeyname='user_id';	
		$this->vis_name='is_shown';		
		
		$this->_auth_result=NULL;
		
	}
}

//������� ������� ������������
class KpOut_ViewsItem extends Abstract_ViewItem{
	protected function init(){
		$this->tablename='kp_in_out_views';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
}

//������� �������
class KpOut_ColItem extends Abstract_ColItem{
	protected function init(){
		$this->tablename='kp_in_out_view_field';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
}

//������ �������
class KpOut_ColGroup extends Abstract_ColGroup {
	 
	//��������� ���� ����
	protected function init(){
		$this->tablename='kp_in_out_view_field';
		$this->pagename='view.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';		
		
		$this->_auth_result=NULL;
		
	}
}
?>