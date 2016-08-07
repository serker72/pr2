<?
 
require_once('abstract_view.class.php');


//������ ������� ������������
class DocVn_ViewsGroup1 extends Abstract_ViewGroup{
	 
	//��������� ���� ����
	protected function init(){
		$this->tablename='doc_vn_views1';
		$this->col_tablename='doc_vn_view_field1';
		$this->pagename='view.php';		
		$this->subkeyname='user_id';	
		$this->vis_name='is_shown';		
		
		$this->_auth_result=NULL;
		
	}
}

//������� ������� ������������
class DocVn_ViewsItem1 extends Abstract_ViewItem{
	protected function init(){
		$this->tablename='doc_vn_views1';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
}

//������� �������
class DocVn_ColItem1 extends Abstract_ColItem{
	protected function init(){
		$this->tablename='doc_vn_view_field1';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
}

//������ �������
class DocVn_ColGroup1 extends Abstract_ColGroup {
	 
	//��������� ���� ����
	protected function init(){
		$this->tablename='doc_vn_view_field1';
		$this->pagename='view.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';		
		
		$this->_auth_result=NULL;
		
	}
}


/******************************************************************************************************/

//������ ������� ������������
class DocVn_ViewsGroup2 extends Abstract_ViewGroup{
	 
	//��������� ���� ����
	protected function init(){
		$this->tablename='doc_vn_views2';
		$this->col_tablename='doc_vn_view_field2';
		$this->pagename='view.php';		
		$this->subkeyname='user_id';	
		$this->vis_name='is_shown';		
		
		$this->_auth_result=NULL;
		
	}
}

//������� ������� ������������
class DocVn_ViewsItem2 extends Abstract_ViewItem{
	protected function init(){
		$this->tablename='doc_vn_views2';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
}

//������� �������
class DocVn_ColItem2 extends Abstract_ColItem{
	protected function init(){
		$this->tablename='doc_vn_view_field2';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
}

//������ �������
class DocVn_ColGroup2 extends Abstract_ColGroup {
	 
	//��������� ���� ����
	protected function init(){
		$this->tablename='doc_vn_view_field2';
		$this->pagename='view.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';		
		
		$this->_auth_result=NULL;
		
	}
}

 
?>