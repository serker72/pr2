<?
 
require_once('abstract_view.class.php');


//������ ������� ������������
class TenderMonitor_View1Group extends Abstract_ViewGroup{
	 
	//��������� ���� ����
	protected function init(){
		$this->tablename='tender_monitor_1_view';
		$this->col_tablename='tender_monitor_1_view_field';
		$this->pagename='view.php';		
		$this->subkeyname='user_id';	
		$this->vis_name='is_shown';		
		
		$this->_auth_result=NULL;
		
	}
}

//������� ������� ������������
class TenderMonitor_View1Item extends Abstract_ViewItem{
	protected function init(){
		$this->tablename='tender_monitor_1_view';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
}

//������� �������
class TenderMonitor_Col1Item extends Abstract_ColItem{
	protected function init(){
		$this->tablename='tender_monitor_1_view_field';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
}

//������ �������
class TenderMonitor_Col1Group extends Abstract_ColGroup {
	 
	//��������� ���� ����
	protected function init(){
		$this->tablename='tender_monitor_1_view_field';
		$this->pagename='view.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';		
		
		$this->_auth_result=NULL;
		
	}
}
?>