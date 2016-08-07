<?
 
require_once('abstract_view.class.php');


//������ ������� ������������
class TZ_ViewsGroup extends Abstract_ViewGroup{
	 
	//��������� ���� ����
	protected function init(){
		$this->tablename='tz_views';
		$this->col_tablename='tz_view_field';
		$this->pagename='view.php';		
		$this->subkeyname='user_id';	
		$this->vis_name='is_shown';		
		
		$this->_auth_result=NULL;
		
	}
}

//������� ������� ������������
class TZ_ViewsItem extends Abstract_ViewItem{
	protected function init(){
		$this->tablename='tz_views';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
}

//������� �������
class TZ_ColItem extends Abstract_ColItem{
	protected function init(){
		$this->tablename='tz_view_field';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
}

//������ �������
class TZ_ColGroup extends Abstract_ColGroup {
	 
	//��������� ���� ����
	protected function init(){
		$this->tablename='tz_view_field';
		$this->pagename='view.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';		
		
		$this->_auth_result=NULL;
		
	}
}
?>