<?
 
require_once('abstract_view.class.php');


//группа стобцов конфигурации
class DocOut_ViewsGroup1 extends Abstract_ViewGroup{
	 
	//установка всех имен
	protected function init(){
		$this->tablename='doc_out_views1';
		$this->col_tablename='doc_out_view_field1';
		$this->pagename='view.php';		
		$this->subkeyname='user_id';	
		$this->vis_name='is_shown';		
		
		$this->_auth_result=NULL;
		
	}
}

//элемент столбец конфигурации
class DocOut_ViewsItem1 extends Abstract_ViewItem{
	protected function init(){
		$this->tablename='doc_out_views1';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
}

//элемент колонка
class DocOut_ColItem1 extends Abstract_ColItem{
	protected function init(){
		$this->tablename='doc_out_view_field1';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
}

//группа колонок
class DocOut_ColGroup1 extends Abstract_ColGroup {
	 
	//установка всех имен
	protected function init(){
		$this->tablename='doc_out_view_field1';
		$this->pagename='view.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';		
		
		$this->_auth_result=NULL;
		
	}
}


/******************************************************************************************************/

//группа стобцов конфигурации
class DocOut_ViewsGroup2 extends Abstract_ViewGroup{
	 
	//установка всех имен
	protected function init(){
		$this->tablename='doc_out_views2';
		$this->col_tablename='doc_out_view_field2';
		$this->pagename='view.php';		
		$this->subkeyname='user_id';	
		$this->vis_name='is_shown';		
		
		$this->_auth_result=NULL;
		
	}
}

//элемент столбец конфигурации
class DocOut_ViewsItem2 extends Abstract_ViewItem{
	protected function init(){
		$this->tablename='doc_out_views2';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
}

//элемент колонка
class DocOut_ColItem2 extends Abstract_ColItem{
	protected function init(){
		$this->tablename='doc_out_view_field2';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
}

//группа колонок
class DocOut_ColGroup2 extends Abstract_ColGroup {
	 
	//установка всех имен
	protected function init(){
		$this->tablename='doc_out_view_field2';
		$this->pagename='view.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';		
		
		$this->_auth_result=NULL;
		
	}
}


/******************************************************************************************************/

//группа стобцов конфигурации
class DocOut_ViewsGroup3 extends Abstract_ViewGroup{
	 
	//установка всех имен
	protected function init(){
		$this->tablename='doc_out_views3';
		$this->col_tablename='doc_out_view_field3';
		$this->pagename='view.php';		
		$this->subkeyname='user_id';	
		$this->vis_name='is_shown';		
		
		$this->_auth_result=NULL;
		
	}
}

//элемент столбец конфигурации
class DocOut_ViewsItem3 extends Abstract_ViewItem{
	protected function init(){
		$this->tablename='doc_out_views3';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
}

//элемент колонка
class DocOut_ColItem3 extends Abstract_ColItem{
	protected function init(){
		$this->tablename='doc_out_view_field3';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
}

//группа колонок
class DocOut_ColGroup3 extends Abstract_ColGroup {
	 
	//установка всех имен
	protected function init(){
		$this->tablename='doc_out_view_field3';
		$this->pagename='view.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';		
		
		$this->_auth_result=NULL;
		
	}
}

?>