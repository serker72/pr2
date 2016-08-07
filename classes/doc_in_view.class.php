<?
 
require_once('abstract_view.class.php');


//группа стобцов конфигурации
class DocIn_ViewsGroup1 extends Abstract_ViewGroup{
	 
	//установка всех имен
	protected function init(){
		$this->tablename='doc_in_views1';
		$this->col_tablename='doc_in_view_field1';
		$this->pagename='view.php';		
		$this->subkeyname='user_id';	
		$this->vis_name='is_shown';		
		
		$this->_auth_result=NULL;
		
	}
}

//элемент столбец конфигурации
class DocIn_ViewsItem1 extends Abstract_ViewItem{
	protected function init(){
		$this->tablename='doc_in_views1';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
}

//элемент колонка
class DocIn_ColItem1 extends Abstract_ColItem{
	protected function init(){
		$this->tablename='doc_in_view_field1';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
}

//группа колонок
class DocIn_ColGroup1 extends Abstract_ColGroup {
	 
	//установка всех имен
	protected function init(){
		$this->tablename='doc_in_view_field1';
		$this->pagename='view.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';		
		
		$this->_auth_result=NULL;
		
	}
}


/******************************************************************************************************/

//группа стобцов конфигурации
class DocIn_ViewsGroup2 extends Abstract_ViewGroup{
	 
	//установка всех имен
	protected function init(){
		$this->tablename='doc_in_views2';
		$this->col_tablename='doc_in_view_field2';
		$this->pagename='view.php';		
		$this->subkeyname='user_id';	
		$this->vis_name='is_shown';		
		
		$this->_auth_result=NULL;
		
	}
}

//элемент столбец конфигурации
class DocIn_ViewsItem2 extends Abstract_ViewItem{
	protected function init(){
		$this->tablename='doc_in_views2';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
}

//элемент колонка
class DocIn_ColItem2 extends Abstract_ColItem{
	protected function init(){
		$this->tablename='doc_in_view_field2';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
}

//группа колонок
class DocIn_ColGroup2 extends Abstract_ColGroup {
	 
	//установка всех имен
	protected function init(){
		$this->tablename='doc_in_view_field2';
		$this->pagename='view.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';		
		
		$this->_auth_result=NULL;
		
	}
}

 
?>