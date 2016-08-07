<?

require_once('abstractgroup.php');
 

 
 
require_once('memoitem.php');
require_once('authuser.php');
require_once('memo_field_rules.php');

//отчет служебные записки
class AnMemos {
	 
	
	
	public function ShowPos(  $template, //0
	 DBDecorator $dec2, //1
	 $pagename='files.php', //2  
	 $do_it=false, //3
	 $can_print=false, //4 
	 $can_edit=false, //5
	 &$alls, //6
	 $result=NULL //7
	 
	 ){
		
		
		
		$_au=new AuthUser;
		if($result===NULL) $result=$_au->Auth();
		
		//echo $from;
		
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		$alls=array();
		
		
	
		if($do_it){
				
		
		$sql='select t.*,
			k.name as kind_name,
			st.name as status_name,
			u.login as login, u.name_s as name_s, u.position_s as position_s,
			u1.login as confirmed_login, u1.name_s as confirmed_name,
			crea.name_s as crea_name_s
		from memo as t
			left join memo_kind as k on t.kind_id=k.id
			left join document_status as st on st.id=t.status_id
			left join user as u on u.id=t.manager_id
			left join user as crea on crea.id=t.user_id
			left join user as u1 on u1.id=t.user_confirm_id
		
		';
		
			
			$db_flt=$dec2->GenFltSql(' and ');
			if(strlen($db_flt)>0){
				$sql.=' where '.$db_flt;
				
			}
			
			
			
			$ord_flt=$dec2->GenFltOrd();
			if(strlen($ord_flt)>0){
				$sql.=' order by '.$ord_flt;
			}
			 
			//echo $sql.'<br>';
			 
			$set=new mysqlSet($sql); //,$to_page, $from,$sql_count);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			$total=$set->GetResultNumRowsUnf();
			
			
			 $_item=new MemoItem;
			
			$alls=array();
			$_rules=new Memo_FieldRules;
		
		 
		 
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				foreach($f as $k=>$v) $f[$k]=stripslashes($v);
				$f['pdate']=date("d.m.Y",$f['pdate']);
				
				 
				 
				
				$f['can_annul']=$_item->DocCanAnnul($f['id'],$reason,$f)&&$can_delete;
				if(!$can_delete) $reason='недостаточно прав для данной операции';
				$f['can_annul_reason']=$reason;
				 
				
				$f['field_rules']=$_rules->GetFields($f,$result['id'],$f['status_id']);  
			
				
				
				if($f['confirm_pdate']==0) $f['confirm_pdate']='';
				else $f['confirm_pdate']=date('d.m.Y H:i:s', $f['confirm_pdate']);
				
				
				//print_r($f);	
				$alls[]=$f;
			}	
			 
			
			
			
		
			
		}
		
			
		//заполним шаблон полями
		
		$current_kind=''; $tab_page='1'; $prefix=0;
		$fields=$dec2->GetUris();
		foreach($fields as $k=>$v){
			if($v->GetName()=='kind_id') $current_kind=$v->GetValue();
			if($v->GetName()=='status_id') $current_status=$v->GetValue();
			
			if($v->GetName()=='print') $print=$v->GetValue();
			if($v->GetName()=='prefix') $prefix=$v->GetValue();
		 
				
			if($v->GetName()=='tab_page') $tab_page=$v->GetValue();
			
			$sm->assign($v->GetName(),$v->GetValue());	
		}
 
			
			$sm->assign('items',$alls);
		$sm->assign('pagename',$pagename);
		
	  
		$sm->assign('can_edit', $can_edit);
		$sm->assign('can_print', $can_print);
	 
		
		$sm->assign('do_it',$do_it);	
		
		 
		 
		
		
		$sm->assign('prefix',$prefix);
		
		
			//ссылка для кнопок сортировки
	/*	$link=$dec->GenFltUri('&',$prefix);
		//echo $link;
		$link=$this->pagename.'?'.eregi_replace('&sortmode'.$this->prefix.'=[[:digit:]]+','',$link).'&doSub'.$this->prefix.'=1&tab_page='.$tab_page;
		$sm->assign('link',$link);
		*/
		
		   $link=$dec2->GenFltUri('&', $prefix);
	    $link=$pagename.'?'.eregi_replace('&sortmode=[[:digit:]]+','',$link).'&doSub'.$prefix.'=1';
		$sm->assign('link',$link);
		
		return $sm->fetch($template);
	
	}
	
	
	 
	
	
}
?>