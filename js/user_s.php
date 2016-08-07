<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');

require_once('../classes/user_s_item.php');
require_once('../classes/questionitem.php');


require_once('../classes/usercontactdatagroup.php');
require_once('../classes/suppliercontactkindgroup.php');
require_once('../classes/usercontactdataitem.php');

require_once('../classes/user_int_item.php');

require_once('../classes/user_int_group.php');


require_once('../classes/user_pos_item.php');
require_once('../classes/user_pos_group.php');

require_once('../classes/upos_direction.php');

require_once('../classes/user_dep_item.php');
require_once('../classes/user_dep_group.php');

require_once('../classes/user_main_dep_item.php');
require_once('../classes/user_main_dep_group.php');

require_once('../classes/pl_dismaxval_user_item.php');
require_once('../classes/pl_dismaxval_user_group.php');
require_once('../classes/pl_disitem.php');
require_once('../classes/pl_proditem.php');

require_once('../classes/suppliersgroup.php');

require_once('../classes/sched.class.php');

require_once('../classes/user_view.class.php');

$au=new AuthUser();
$result=$au->Auth();
$log=new ActionLog;

if($result===NULL){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();		
}

$ret='';

$ui=new UserSItem;
if(isset($_POST['action'])&&($_POST['action']=="redraw_dics")){
	$sm=new SmartyAj;
	if(isset($_POST['user_id'])) $user_id=abs((int)$_POST['user_id']);
	else $user_id=0;
	
	
	$questions=$ui->GetQuestionsAllArr($user_id);
	$sm->assign('items',$questions);
	
	$sm->assign('can_expand_questions',$au->user_rights->CheckAccess('w',152)); 
	$sm->assign('can_edit_questions',$au->user_rights->CheckAccess('w',13)); 
	
	$sm->assign('qpp',ceil(count($questions)/5));
	
		
	$ret=$sm->fetch('users/s_user_questions_dic.html');
}if(isset($_POST['action'])&&($_POST['action']=="redraw_dics_page")){
	$sm=new SmartyAj;
	
	if(isset($_POST['user_id'])) $user_id=abs((int)$_POST['user_id']);
	else $user_id=0;
	
	
	$arr=$ui->GetQuestionsAllArr($user_id);
	$sm->assign('items',$arr);
	$sm->assign('qpp',ceil(count($arr)/2));	
	$sm->assign('can_expand_questions',$au->user_rights->CheckAccess('w',152)); 
		$sm->assign('can_edit_questions',$au->user_rights->CheckAccess('w',13)); 
	
	
	$ret=$sm->fetch('users/s_user_questions.html');
}elseif(isset($_POST['action'])&&($_POST['action']=="add_question")){
	
	//dostup
	if(!$au->user_rights->CheckAccess('w',13)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$qi=new QuestionItem;
	$params=array();
	$params['name']=SecStr(iconv("utf-8","windows-1251",$_POST['question']),9);
	$qi->Add($params);
	
	$log->PutEntry($result['id'],'������� ������, ���������� ������������',NULL,13,NULL,$params['name']);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_question")){
	if(!$au->user_rights->CheckAccess('w',13)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$qi=new QuestionItem;
	$params=array();
	$id=abs((int)$_POST['id']);
	$params['name']=SecStr(iconv("utf-8","windows-1251",$_POST['question']),9);
	$qi->Edit($id,$params);	
	
	$log->PutEntry($result['id'],'������������ ������, ���������� ������������',NULL,13,NULL,$params['name']);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="delete_question")){
	
	if(!$au->user_rights->CheckAccess('w',13)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$qi=new QuestionItem;
	
	$id=abs((int)$_POST['id']);
	$qi->Del($id);
	
	$log->PutEntry($result['id'],'������ ������, ���������� ������������',NULL,13,NULL,$params['name']);
}
//������ � ����������
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_contact")){
	$sm=new SmartyAj;
	
	$user_id=abs((int)$_POST['user_id']);
	
	$rg=new UserContactDataGroup;
	
	$ui=new UserSItem;
	$uitem=$ui->getitembyid($user_id);
	
	$sm->assign('items',$rg->GetItemsByIdArr($user_id));
	$sm->assign('word','contact');
	$sm->assign('user_id',$user_id);
	$sm->assign('named','��������');
	
	$rrg=new SupplierContactKindGroup;
	$sm->assign('kinds',$rrg->GetItemsArr());
	
	$sm->assign('can_edit', $au->user_rights->CheckAccess('w',11)||($uitem['id']==$result['id']));
	
	
	$ret=$sm->fetch('users/contacts.html');
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="add_contact")){
	//dostup
	/*if(!$au->user_rights->CheckAccess('w',11)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}*/
	
	$user_id=abs((int)$_POST['user_id']);
	$ui=new UserSItem;
	$uitem=$ui->getitembyid($user_id);
	
	
	//dostup
	if(!$au->user_rights->CheckAccess('w',11)&&($uitem['id']!=$result['id'])){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	
	$ri=new UserContactDataItem;
	$ri->Add(array(
		'value'=>SecStr(iconv("utf-8","windows-1251",$_POST['value']),9),
		'user_id'=>abs((int)$_POST['user_id']),
		'kind_id'=>abs((int)$_POST['kind_id'])
	));
	
	
	$log->PutEntry($result['id'],'������� ������ �������� ����������', $user_id,11,NULL, SecStr(iconv("utf-8","windows-1251",$_POST['value']),9),$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_nest_contact")){
	//dostup
	
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	
	$ui=new UserSItem;
	$uitem=$ui->getitembyid($user_id);
	
	
	//dostup
	if(!$au->user_rights->CheckAccess('w',11)&&($uitem['id']!=$result['id'])){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$params=array();
	$params['kind_id']=abs((int)$_POST['kind_id']);
	$params['value']=SecStr(iconv("utf-8","windows-1251",$_POST['value']),9);
	
	
	$ri=new UserContactDataItem;
	$ri->Edit($id, $params);
	
	$log->PutEntry($result['id'],'������������ ������ �������� ����������', $user_id,11,NULL,'����������� �������� '.SecStr(iconv("utf-8","windows-1251",$_POST['value']),9),$user_id);
}elseif(isset($_POST['action'])&&($_POST['action']=="delete_nest_contact")){
	//dostup
	
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	$ui=new UserSItem;
	$uitem=$ui->getitembyid($user_id);
	
	
	//dostup
	if(!$au->user_rights->CheckAccess('w',11)&&($uitem['id']!=$result['id'])){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$ri=new UserContactDataItem;
	$ri->Del($id);
	
	$log->PutEntry($result['id'],'������ ������ �������� ����������', $user_id,11,NULL,NULL,$user_id);
	
}
//������ � ��������� ������
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_ints")){
	$sm=new SmartyAj;
	
	$user_id=abs((int)$_POST['user_id']);
	
	$rg=new UserIntGroup;
	
	$sm->assign('items',$rg->GetItemsByIdArr($user_id));
	
	$ui=new UserSItem;
	$uitem=$ui->getitembyid($user_id);
	
	$sm->assign('word','ints');
	$sm->assign('user_id',$user_id);
	$sm->assign('named','����� ������');
	
	
	$sm->assign('can_edit', $au->user_rights->CheckAccess('w',11)||($uitem['id']==$result['id']));
	
	
	$ret=$sm->fetch('users/userint.html');
	
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="add_ints")){
	
	
	$user_id=abs((int)$_POST['user_id']);
	
	$ui=new UserSItem;
	$uitem=$ui->getitembyid($user_id);
	
	
	//dostup
	if(!$au->user_rights->CheckAccess('w',11)&&($uitem['id']!=$result['id'])){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	
	$ri=new UserIntItem;
	$ri->Add(array(
		'comments'=>SecStr(iconv("utf-8","windows-1251",$_POST['comments']),9),
		'user_id'=>abs((int)$_POST['user_id']),
		'time_from_h_s'=>abs((int)$_POST['time_from_h_s']),
		'time_from_m_s'=>abs((int)$_POST['time_from_m_s']),
		'time_to_h_s'=>abs((int)$_POST['time_to_h_s']),
		'time_to_m_s'=>abs((int)$_POST['time_to_m_s']),
		'pdate'=>time(),
		'posted_user_id'=>$result['id']
	));
	
	
	$log->PutEntry($result['id'],'������� �������������� ����� ������ ����������', NULL,11,NULL, '� '.sprintf("%02d",abs((int)$_POST['time_from_h_s'])).':'.sprintf("%02d",abs((int)$_POST['time_from_m_s'])).' �� '.sprintf("%02d",abs((int)$_POST['time_to_h_s'])).':'.sprintf("%02d",abs((int)$_POST['time_to_m_s'])),$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_nest_ints")){
	//dostup
	
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	
	$ui=new UserSItem;
	$uitem=$ui->getitembyid($user_id);
	
	
	//dostup
	if(!$au->user_rights->CheckAccess('w',11)&&($uitem['id']!=$result['id'])){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	
	$params=array(
		'comments'=>SecStr(iconv("utf-8","windows-1251",$_POST['comments']),9),
		'time_from_h_s'=>abs((int)$_POST['time_from_h_s']),
		'time_from_m_s'=>abs((int)$_POST['time_from_m_s']),
		'time_to_h_s'=>abs((int)$_POST['time_to_h_s']),
		'time_to_m_s'=>abs((int)$_POST['time_to_m_s'])
	
	);
	
	
	
	$ri=new UserIntItem;
	$ri->Edit($id, $params);
	
	$log->PutEntry($result['id'],'������������ ����� ������ ����������', NULL,11,NULL,'� '.sprintf("%02d",abs((int)$_POST['time_from_h_s'])).':'.sprintf("%02d",abs((int)$_POST['time_from_m_s'])).' �� '.sprintf("%02d",abs((int)$_POST['time_to_h_s'])).':'.sprintf("%02d",abs((int)$_POST['time_to_m_s'])),$user_id);
}elseif(isset($_POST['action'])&&($_POST['action']=="delete_nest_ints")){
	//dostup
	
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	
	$ui=new UserSItem;
	$uitem=$ui->getitembyid($user_id);
	
	
	//dostup
	if(!$au->user_rights->CheckAccess('w',11)&&($uitem['id']!=$result['id'])){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	
	
	$ri=new UserIntItem;
	$ri->Del($id);
	
	$log->PutEntry($result['id'],'������ ����� ������ ����������', NULL,11,NULL,NULL,$user_id);
	
}



//������ � �����������
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_userpos_dics")){
	$sm=new SmartyAj;
	if(isset($_POST['user_id'])) $user_id=abs((int)$_POST['user_id']);
	else $user_id=0;
	
	$opg=new UserPosGroup;
	$sm->assign('opfs_total', $opg->GetItemsArr());
	$sm->assign('word', 'userpos');
	$sm->assign('named', '���������');
	
	
	$ret=$sm->fetch('users/userpos.html');
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="redraw_userpos_page")){
	//$sm=new SmartyAj;
	
	if(isset($_POST['user_id'])) $user_id=abs((int)$_POST['user_id']);
	else $user_id=0;
	
	$opg=new UserPosGroup;
	$ret=$opg->GetItemsOpt($user_id, 'name', true);
	
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="add_userpos")){
	
	//dostup
	/*if(!$au->user_rights->CheckAccess('w',19)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	*/
	$qi=new UserPosItem;
	$params=array();
	$params['name']=SecStr(iconv("utf-8","windows-1251",$_POST['userpos']),9);
	
	$params['can_sign_as_dir_pr']=abs((int)$_POST['can_sign_as_dir_pr']);
	$params['can_sign_as_manager']=abs((int)$_POST['can_sign_as_manager']);
	$params['is_ruk_otd']=abs((int)$_POST['is_ruk_otd']);
	
	
	$qi->Add($params);
	
	//$log->PutEntry($result['id'],'������� ���',NULL,19,NULL,$params['name']);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_userpos")){
	/*if(!$au->user_rights->CheckAccess('w',19)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}*/
	
	$qi=new UserPosItem;
	$params=array();
	$id=abs((int)$_POST['id']);
	$params['name']=SecStr(iconv("utf-8","windows-1251",$_POST['question']),9);
	
	$params['can_sign_as_dir_pr']=abs((int)$_POST['can_sign_as_dir_pr']);
	$params['can_sign_as_manager']=abs((int)$_POST['can_sign_as_manager']);
	$params['is_ruk_otd']=abs((int)$_POST['is_ruk_otd']);
	
	$qi->Edit($id,$params);	
	
	/*
	
	$dirs=$_POST['dirs'];
	$_upd=new UposDirection;
	
	$_upd->AddDirsToPositionArray($id,$dirs);
	*/
	
	
	//$log->PutEntry($result['id'],'������������ ���',NULL,19,NULL,$params['name']);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="delete_userpos")){
	
	/*if(!$au->user_rights->CheckAccess('w',13)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}*/
	
	$qi=new UserPosItem;
	
	$id=abs((int)$_POST['id']);
	$qi->Del($id);
	
	//$log->PutEntry($result['id'],'������ ���',NULL,19,NULL,$params['name']);
}



//������ � ��������
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_deps_dics")){
	$sm=new SmartyAj;
	if(isset($_POST['user_id'])) $user_id=abs((int)$_POST['user_id']);
	else $user_id=0;
	
	$opg=new UserDepGroup;
	$sm->assign('opfs_total', $opg->GetItemsArr());
	$sm->assign('word', 'deps');
	$sm->assign('named', '�����');
	
	
	$ret=$sm->fetch('users/deps.html');
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="redraw_deps_page")){
	//$sm=new SmartyAj;
	
	if(isset($_POST['user_id'])) $user_id=abs((int)$_POST['user_id']);
	else $user_id=0;
	
	$opg=new UserDepGroup;
	$ret=$opg->GetItemsOpt($user_id, 'name', true);
	
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="add_deps")){
	
	//dostup
	/*if(!$au->user_rights->CheckAccess('w',19)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	*/
	$qi=new UserDepItem;
	$params=array();
	$params['name']=SecStr(iconv("utf-8","windows-1251",$_POST['deps']),9);
	//$params['is_in_plan_fact_sales']=abs((int)$_POST['is_in_plan_fact_sales']);
	
	 
	
	$qi->Add($params);
	
	//$log->PutEntry($result['id'],'������� ���',NULL,19,NULL,$params['name']);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_deps")){
	/*if(!$au->user_rights->CheckAccess('w',19)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}*/
	
	$qi=new UserDepItem;
	$params=array();
	$id=abs((int)$_POST['id']);
	$params['name']=SecStr(iconv("utf-8","windows-1251",$_POST['question']),9);
	//$params['is_in_plan_fact_sales']=abs((int)$_POST['is_in_plan_fact_sales']);
	
	 
	$qi->Edit($id,$params);	
	
	
	
	 	
	$dirs=$_POST['dirs'];
	$_upd=new UposDirection;
	
	$_upd->AddDirsToPositionArray($id,$dirs);
	
	
	
	//$log->PutEntry($result['id'],'������������ ���',NULL,19,NULL,$params['name']);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="delete_deps")){
	
	/*if(!$au->user_rights->CheckAccess('w',13)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}*/
	
	$qi=new UserDepItem;
	
	$id=abs((int)$_POST['id']);
	$qi->Del($id);
	
	//$log->PutEntry($result['id'],'������ ���',NULL,19,NULL,$params['name']);

}elseif(isset($_POST['action'])&&($_POST['action']=="add_max_dis")){
	
	//dostup
	/*if(!$au->user_rights->CheckAccess('w',19)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	*/
	$qi=new PlDisMaxValUserItem;
	$params=array();
	
	$params['parent_group_id']=abs((int)$_POST['parent_group_id']);
	$params['producer_id']=abs((int)$_POST['producer_id']);
	$params['discount_id']=abs((int)$_POST['discount_id']);
	$params['user_id']=abs((int)$_POST['user_id']);
	
	$params['value']=abs((float)$_POST['value']);
	
	  
	
	$qi->Add($params);
	
	$value='';
	
	$_gi=new PosGroupItem;
	$_pi=new  PlProdItem;
	$_dis=new PlDisItem;
	
	
	$dis=$_dis->GetItemById($params['discount_id']);
	$gi=$_gi->GetItemById($params['parent_group_id']);
	$pi=$_pi->GetItemById($params['producer_id']);
	
	$value.=' '.$dis['name'].' '.$params['value'].'%';
	if($pi!==false) $value.=', ��������� '.$pi['name'];
	if($gi!==false) $value.=', ������ '.$gi['name'];
	$value=SecStr($value);
	
	
	$log->PutEntry($result['id'],'������� �������������� ������������ ������ ����������', $params['user_id'],11,NULL, $value);


}elseif(isset($_POST['action'])&&($_POST['action']=="redraw_max_dis")){
	 
	
	if(isset($_POST['user_id'])) $user_id=abs((int)$_POST['user_id']);
	else $user_id=0;
	
	$opg=new PlDisMaxValUserGroup;
	$gr=$opg->GetItemsByIdArr($user_id);
	
	$sm=new SmartyAj;
	$sm->assign('items', $gr);
	$sm->assign('word', 'max_dis');
	$sm->assign('user_id', $user_id);
	$sm->assign('named', '������� ����. ������');
	$sm->assign('can_edit', $au->user_rights->CheckAccess('w',11));
	
	$ret=$sm->fetch('users/max_dis.html');
	
}elseif(isset($_POST['action'])&&($_POST['action']=="delete_nest_max_dis")){
	
	/*if(!$au->user_rights->CheckAccess('w',13)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}*/
	
	$qi=new PlDisMaxValUserItem;
	
	
	
	$id=abs((int)$_POST['id']);
	
	$params=$qi->Getitembyid($id);
	
	$qi->Del($id);
	
	//$log->PutEntry($result['id'],'������ ���',NULL,19,NULL,$params['name']);
	
	
	
	$value='';
	
	$_gi=new PosGroupItem;
	$_pi=new  PlProdItem;
	$_dis=new PlDisItem;
	
	
	$dis=$_dis->GetItemById($params['discount_id']);
	$gi=$_gi->GetItemById($params['parent_group_id']);
	$pi=$_pi->GetItemById($params['producer_id']);
	
	$value.=' '.$dis['name'].' '.$params['value'].'%';
	if($pi!==false) $value.=', ��������� '.$pi['name'];
	if($gi!==false) $value.=', ������ '.$gi['name'];
	$value=SecStr($value);
	
	
	$log->PutEntry($result['id'],'������ �������������� ������������ ������ ����������', $params['user_id'],11,NULL, $value);



 

}elseif(isset($_POST['action'])&&($_POST['action']=="find_suppliers")){
	
	
	//������� ������ ������� �� �������
	$_pg=new SuppliersGroup;
	
	$dec=new DBDecorator;
	
	//except_ids
	if(is_array($_POST['except_ids'])&&(count($_POST['except_ids'])>0)){
		$dec->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::NOT_IN_VALUES, NULL,$_POST['except_ids']));	
	}
	
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['code'])))>0) $dec->AddEntry(new SqlEntry('p.code',SecStr(iconv("utf-8","windows-1251",$_POST['code'])), SqlEntry::LIKE));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['full_name'])))>0) $dec->AddEntry(new SqlEntry('p.full_name',SecStr(iconv("utf-8","windows-1251",$_POST['full_name'])), SqlEntry::LIKE));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['inn'])))>0) $dec->AddEntry(new SqlEntry('p.inn',SecStr(iconv("utf-8","windows-1251",$_POST['inn'])), SqlEntry::LIKE));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['kpp'])))>0) $dec->AddEntry(new SqlEntry('p.kpp',SecStr(iconv("utf-8","windows-1251",$_POST['kpp'])), SqlEntry::LIKE));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['legal_address'])))>0) $dec->AddEntry(new SqlEntry('p.legal_address',SecStr(iconv("utf-8","windows-1251",$_POST['legal_address'])), SqlEntry::LIKE));
	
	
	
	$ret=$_pg->GetItemsForSelect('users/suppliers_list.html',  $dec,true,$all7,$result);
	

	
}


elseif(isset($_POST['action'])&&($_POST['action']=="check_primary_email")){
	
		$id=abs((int)$_POST['id']);
		$email_s=SecStr(iconv('utf-8', 'windows-1251', $_POST['email_s']));
		
		$sql='select * from user where email_s="'.$email_s.'"';
		if($id!=0) $sql.=' and id<>"'.$id.'" ';
		
		$sql.=' order by name_s asc';
		 
		$set=new mysqlset($sql);
		$rs=$set->getresult();
		$rc=$set->getResultnumrows();
		
		if($rc==0) $ret=0;
		else{
			$rets=array();
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				$rets[]=$f['name_s'].' '.$f['login'];
			}
			$ret='��������� �������� ����������� ����� ��� ����� � �����������: '.implode(', ', $rets);
		}
		
	
		//���� ���� - �� ��� ������
	
}



//������ �������� � ������������ ������ �����������
elseif(isset($_POST['action'])&&($_POST['action']=="load_users")){
	//dostup
	
	
	$user_id=abs((int)$_POST['user_id']);
	//$id=abs((int)$_POST['id']);
	
	
	$_svg=new Sched_ViewGroup;
	
	
	$ret=$_svg->ForWindow($user_id, 'users/sched_edit.html',true,true);
	
	
	 
	
	 
}

elseif(isset($_POST['action'])&&($_POST['action']=="transfer_users")){
	
	$user_id=abs((int)$_POST['user_id']);
	//$mode=abs((int)$_POST['mode']);
	
	$_svg=new Sched_ViewGroup;
	
	$data=$_POST['data'];
	
	//������ � ������
	$_user=new UserSItem;
	$log=new ActionLog;
	
	$_kpi=new Sched_ViewItem;
	foreach($data as $k=>$v){
		$valarr=explode(";",$v);
		
		//$positions[]=array('user_id'=>$user_id, 'allowed_id'=>abs((int)$valarr[0]));	
		$test_kpi=$_kpi->GetItemByFields(array('user_id'=>$user_id, 'kind_id'=>$valarr[1], 'allowed_id'=>$valarr[0]));
		
		
		$user=$_user->GetItemById($valarr[0]);
				  
		$description='���������� ���������: ';
		if($valarr[0]==0){
		   $description.='��� ����������';  
		}else{
			$description.=SecStr($user['name_s'].' '.$user['login']).'';
		}
				  
		switch($valarr[1]){
			case 1:
				$description.=', ������ ������';
			break;	
			case 2:
				$description.=', ������ ������������';
			break;	
			case 3:
				$description.=', ������ �������';
			break;	
			case 4:
				$description.=', ������ ������';
			break;	
			case 5:
				$description.=', ������ �������';
			break;	
			
		}
		
		
		if($valarr[2]==0){
			$_kpi->Del($test_kpi['id']);
			
			 $log->PutEntry($result['id'],'������ � ���������� ������ � ������� ������������ ������� ����������',$user_id,11,NULL,$description,$user_id);	
		}else{
			if($test_kpi==false){
				$_kpi->Add(array('user_id'=>$user_id, 'kind_id'=>$valarr[1], 'allowed_id'=>$valarr[0]));
				
				$log->PutEntry($result['id'],'������� ���������� ������ � ������� ������������ ������� ����������', $user_id,11,NULL,$description,$user_id);	
			}
		}
		
	}
	
	/*$users=$_POST['users'];
	
	if($mode==0){
		$positions=array(
			array('user_id'=>$user_id, 'allowed_id'=>0)
			);
	}else{
		//$_users=explode(';',$users);
		$positions=array();
		foreach($users as $k=>$v) $positions[]=array('user_id'=>$user_id, 'allowed_id'=>abs((int)$v));
		
		//print_r($users);
	}
	
	$log_entries=$_svg->AddUsers($user_id, $positions);
	
	//������ � ������
	$_user=new UserSItem;
	$log=new ActionLog;
	
	
	if($mode==0){
		 $log->PutEntry($result['id'],'����������� ���������� ������ � ������� ������������ ���� �����������', $user_id,11,NULL,'',$user_id);	
		 
	}else foreach($log_entries as $k=>$v){
				 // $user1=$_user->GetItemById($v['user_id']);
				  $user=$_user->GetItemById($v['allowed_id']);
				  
				 
				 
				  $description='����������� ���������: ';
				  if($v['allowed_id']==0){
					 $description.='��� ����������';  
				  }else{
					  $description.=SecStr($user['name_s'].' '.$user['login']).'';
				  }
				  
				  if($v['action']==0){
					  $log->PutEntry($result['id'],'������� ���������� ������ � ������� ������������ ������� ����������', $user_id,11,NULL,$description,$user_id);	
				  }elseif($v['action']==1){
					  $log->PutEntry($result['id'],'������������ � ���������� ������ � ������� ������������ ������� ����������',$user_id,11,NULL,$description,$user_id);
				  }elseif($v['action']==2){
					  $log->PutEntry($result['id'],'������ � ���������� ������ � ������� ������������ ������� ����������',$user_id,11,NULL,$description,$user_id);
				  }
				  
			  }
			  
			  */
}

elseif(isset($_POST['action'])&&($_POST['action']=="reload_users")){
	//dostup
	
	
	$user_id=abs((int)$_POST['user_id']);
	//$id=abs((int)$_POST['id']);
	
	
	$_svg=new Sched_ViewGroup;
	
	
	$ret=$_svg->ForCard($user_id, 'users/sched_view.html', $au->user_rights->CheckAccess('w',11), true); //>ForWindow($user_id, 'users/sched_edit.html',true,true);
	
	
	 
	
	 
}


//������ � ��������������

elseif(isset($_POST['action'])&&($_POST['action']=="find_main_deps")){
	$sm=new SmartyAj;
	if(isset($_POST['user_id'])) $user_id=abs((int)$_POST['user_id']);
	else $user_id=0;
	
	$opg=new UserMainDepGroup;
 
	
	$sm->assign('can_edit',$au->user_rights->CheckAccess('w',11)); 
	
	
	$sm->assign('items', $opg->GetItemsArr());
	$ret=$sm->fetch('users/main_deps_list.html');
	
	


}


elseif(isset($_POST['action'])&&($_POST['action']=="add_main_dep")){
	
	//dostup
	 
	$qi=new UserMainDepItem;
	$params=array();
	$params['name']=SecStr(iconv("utf-8","windows-1251",$_POST['name']),9);
	 
	 
	
	$qi->Add($params);
	
	//$log->PutEntry($result['id'],'������� ���',NULL,19,NULL,$params['name']);
	
}


elseif(isset($_POST['action'])&&($_POST['action']=="edit_main_dep")){
	/*if(!$au->user_rights->CheckAccess('w',19)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}*/
	
	$qi=new UserMainDepItem;
	$params=array();
	$id=abs((int)$_POST['id']);
	$params['name']=SecStr(iconv("utf-8","windows-1251",$_POST['name']),9);
	//$params['is_in_plan_fact_sales']=abs((int)$_POST['is_in_plan_fact_sales']);
	
	 
	$qi->Edit($id,$params);	
	
	
	
	/* 	
	$dirs=$_POST['dirs'];
	$_upd=new UposDirection;
	
	$_upd->AddDirsToPositionArray($id,$dirs);
	
	*/
	
	//$log->PutEntry($result['id'],'������������ ���',NULL,19,NULL,$params['name']);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="del_main_dep")){
	
	/*if(!$au->user_rights->CheckAccess('w',13)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}*/
	
	$qi=new UserMainDepItem;
	
	$id=abs((int)$_POST['id']);
	$qi->Del($id);
	
	//$log->PutEntry($result['id'],'������ ���',NULL,19,NULL,$params['name']);

}

elseif(isset($_GET['action'])&&($_GET['action']=="retrieve_main_dep")){
	$_si=new UserMainDepItem;
	
	$si=$_si->GetItemById(abs((int)$_GET['id']));
	
	
	 
	if($si!==false){
		$rret=array();
		foreach($si as $k=>$v){
			 
			
			
			$rret[]='"'.$k.'":"'.htmlspecialchars(str_replace("\r", "", str_replace("\n",  "",  $v))).'"';
		}
		
		 
		$ret='{'.implode(', ',$rret).'}';
	}
	
}


//������ � ��������

elseif(isset($_POST['action'])&&($_POST['action']=="find_deps")){
	$sm=new SmartyAj;
	if(isset($_POST['main_department_id'])) $main_department_id=abs((int)$_POST['main_department_id']);
	else $main_department_id=0;
	
	$opg=new UserDepGroup;
 
	
	$sm->assign('can_edit',$au->user_rights->CheckAccess('w',11)); 
	
	
	$sm->assign('items', $opg->GetItemsByIdArr($main_department_id));
	$ret=$sm->fetch('users/deps_list.html');
	
	


}


elseif(isset($_POST['action'])&&($_POST['action']=="add_dep")){
	
	//dostup
	 
	$qi=new UserDepItem;
	$params=array();
	
	$params['parent_id']=abs((int)$_POST['main_department_id']);
	$params['name']=SecStr(iconv("utf-8","windows-1251",$_POST['name']),9);
	 
	 
	
	$qi->Add($params);
	
	//$log->PutEntry($result['id'],'������� ���',NULL,19,NULL,$params['name']);
	
}


elseif(isset($_POST['action'])&&($_POST['action']=="edit_dep")){
	/*if(!$au->user_rights->CheckAccess('w',19)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}*/
	
	$qi=new UserDepItem;
	$params=array();
	$id=abs((int)$_POST['id']);
	$params['name']=SecStr(iconv("utf-8","windows-1251",$_POST['name']),9);
	//$params['is_in_plan_fact_sales']=abs((int)$_POST['is_in_plan_fact_sales']);
	
	 
	$qi->Edit($id,$params);	
	
	
	
	/* 	
	$dirs=$_POST['dirs'];
	$_upd=new UposDirection;
	
	$_upd->AddDirsToPositionArray($id,$dirs);
	
	*/
	
	//$log->PutEntry($result['id'],'������������ ���',NULL,19,NULL,$params['name']);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="del_dep")){
	
	/*if(!$au->user_rights->CheckAccess('w',13)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}*/
	
	$qi=new UserDepItem;
	
	$id=abs((int)$_POST['id']);
	$qi->Del($id);
	
	//$log->PutEntry($result['id'],'������ ���',NULL,19,NULL,$params['name']);

}

elseif(isset($_GET['action'])&&($_GET['action']=="retrieve_dep")){
	$_si=new UserDepItem;
	
	$si=$_si->GetItemById(abs((int)$_GET['id']));
	
	
	 
	if($si!==false){
		$rret=array();
		foreach($si as $k=>$v){
			 
			
			
			$rret[]='"'.$k.'":"'.htmlspecialchars(str_replace("\r", "", str_replace("\n",  "",  $v))).'"';
		}
		
		 
		$ret='{'.implode(', ',$rret).'}';
	}
	
}


//������� �����������
elseif(isset($_POST['action'])&&($_POST['action']=="find_managers")){
	
	$sm=new SmartyAj;
	//������� ������ ������� �� �������
	$_pg=new Sched_UsersSGroup;
	 
	
	$dec=new DBDecorator;
	
	//except_ids
	if(is_array($_POST['except_ids'])&&(count($_POST['except_ids'])>0)){
		$dec->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::NOT_IN_VALUES, NULL,$_POST['except_ids']));	
	}
	
	 
	$items=$_pg->GetItemsForBill($dec);
	
	//$ret=$_pg->GetItemsForSelect('users/suppliers_list.html',  $dec,true,$all7,$result);
	$sm->assign('manager_id', (int)$_POST['manager_id']);
	$sm->assign('items', $items);
	$ret=$sm->fetch('users/managers_list.html');
	
}

elseif(isset($_GET['action'])&&($_GET['action']=="retrieve_manager")){
	$_si=new UserSItem;
	
	$si=$_si->GetItemById(abs((int)$_GET['id']));
	
	
	 
	if($si!==false){
		$rret=array();
		foreach($si as $k=>$v){
			 
			
			
			$rret[]='"'.$k.'":"'.htmlspecialchars(str_replace("\r", "", str_replace("\n",  "",  $v))).'"';
		}
		
		 
		$ret='{'.implode(', ',$rret).'}';
	}
	
}

elseif(isset($_POST['action'])&&($_POST['action']=="load_submanagers")){
	
	 
	
	$user_id=abs((int)$_POST['user_id']);
	$_bi1=new UserSItem;
	$bi1=$_bi1->GetItemById($user_id);
	
	
 
	$already_in_bill=array();
	
	$complex_positions=$_POST['complex_positions'];
	$except_users=$_POST['except_ids'];
	
 
	$_kpg=new Sched_UsersSGroup;
	
 	$dec=new DBDecorator;
	
	 
	
	if(is_array($except_users)&&(count($except_users)>0)){
		$dec->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::NOT_IN_VALUES, NULL,$except_users));
	}
	
	
	$alls=$_kpg->GetItemsForBill($dec);  
	 
  
	/*echo '<pre>';
	print_r(($alls));
	echo '</pre>';*/
	 
	 
	foreach($alls as $kk=>$v){
				  
	 
		 
		  
		  //print_r($vv);
		  
		
		   //��������� ��������, ���� ��� ������ �����
		 
		  //���� ��������� �������  $complex_positions
		  $index=-1;
		  foreach($complex_positions as $ck=>$ccv){
		  	$cv=explode(';',$ccv);
			
			if(
				($cv[0]==$v['id'])
				/*($cv[7]==$vv['storage_id'])&&
				($cv[8]==$vv['sector_id'])&&
				($cv[9]==$vv['komplekt_ved_id'])	*/
				){
					$index=$ck;
					//echo 'nashli'.$vv['position_id'].' - '.$index;
					break;	
				}
		  	
		  }
		  
		  
		  if($index>-1){
			  //echo 'nn '.' '.$v['position_id'];
			  //var_dump($position['id']);
			  
			  
			  $valarr=explode(';',$complex_positions[$index]);
			  $v['is_in']=1;
			  
			  
			  
			  
		  }else{
			  //echo 'no no ';
			   $v['is_in']=0;
			 
		  }
		  
		   
		  
		  //������� �����������/�������������, � ��� ���������
		  $is_avail=true;
		  if(($v['manager_id']!=0)&&($v['manager_id']!=$user_id)){
			  $is_avail=false;
			
			  $main=$_bi1->getItembyid($v['manager_id']);
			  
			  $v['manager_of_user']=$main['name_s'].', '.$main['position_s'];	   
		  }
		  
		  $v['is_avail']=$is_avail;
		  
		  $v['hash']=md5($v['user_id']);
		  
		 // print_r($v);
		  
		  //$alls[$k]=$v;
		  $arr[]=$v;
		
	}
	
	$sm=new SmartyAj;
	 
	$sm->assign('pospos',$arr);
	 
	$sm->assign('can_edit_common',true); 
	
 
	
	$ret.=$sm->fetch("users/submanagers_edit_set.html");
	
	 
 
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="transfer_submanagers")){
	//������� ��������� �������  �� ��������  
		
	$user_id=abs((int)$_POST['user_id']);
	 $complex_positions=$_POST['complex_positions'];
	
	$alls=array();
	$_user=new UserSItem;
	 

	
	foreach($complex_positions as $k=>$kv){
		$f=array();	
		$v=explode(';',$kv);
		//print_r($v);
		//$do_add=true;
		
		
		
		$user=$_user->GetItemById($v[0]);
		if($user===false) continue;
		
		 
		$f['id']=$v[0];
		$f['user_id']=$v[0];
		
		 
		
		$f['name_s']=$user['name_s'];
		$f['login']=$user['login'];
		$f['position_s']=$user['position_s'];
		
		$f['is_active']=$user['is_active'];
		
		$f['hash']=md5($v[0]);
		
	//	$ret.=$v.' ';
		$alls[]=$f;
	}
	
	$sm=new SmartyAj;
	$sm->assign('pospos',$alls);
	
	$sm->assign('can_modify',true);
	$sm->assign('can_edit_common',true); 
	
 
	 
	$ret=$sm->fetch("users/submanagers_on_page_set.html");
	
	

}	


//��������� �������
elseif(isset($_POST['action'])&&(($_POST['action']=="mode_reestr"))){
	$_views=new User_ViewGroup;
	$_view=new User_ViewItem;
	
	$cols=$_POST['cols'];
	
	$_views->Clear($result['id']);
	$ord=0;
	foreach($cols as $k=>$v){
		$params=array();
		$params['col_id']=(int)$v;
		$params['user_id']=$result['id'];
		$params['ord']=$ord;
			
		$ord+=10;
		$_view->Add($params);
		
		 
	}
}
elseif(isset($_POST['action'])&&(($_POST['action']=="mode_reestr_clear"))){
	$_views=new User_ViewGroup;
 
	 
	
	$_views->Clear($result['id']);
	 
 
}elseif(isset($_POST['action'])&&($_POST['action']=="check_ruk_otd")){
		$user_id=abs((int)$_POST['user_id']);
		$department_id=abs((int)$_POST['department_id']);
		
	    
		$_dem=new UserSItem;
		 
		
		
		if(!$_dem->DocCanRukOt($user_id, $department_id, $rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//���� ���� - �� ��� ������
	

}

//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>