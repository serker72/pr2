<?
session_start();
Header("Cache-Control: no-store, no-cache, must-revalidate"); //��� ��������� HTTP/1.1
Header("Pragma: no-cache"); // ��� ��������� HTTP/1.1
Header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT"); // ���� � ����� ��������� ��������
header("Expires: " . date("r")); // ���� � ����� �����, ����� �������� ����� ��������� ����������

require_once('classes/global.php');
require_once('classes/authuser.php');
require_once('classes/smarty/SmartyAdm.class.php');
require_once('classes/smarty/Smarty.class.php');
require_once('classes/discr_table_group.php');
require_once('classes/discr_table_objects.php');
require_once('classes/discr_table_user.php');
require_once('classes/actionlog.php');

 require_once('classes/pl_currgroup.php');
require_once('classes/pl_curritem.php');

require_once('classes/suppliersgroup.php');
require_once('classes/supplieritem.php');
 

 

require_once('classes/user_s_item.php');

 

 
require_once('classes/orgitem.php');
require_once('classes/opfitem.php');

 

require_once('classes/suppliercontactitem.php');
require_once('classes/supcontract_group.php');

require_once('classes/tender.class.php');

 
require_once('classes/lead.class.php');
require_once('classes/tz.class.php');
require_once('classes/kp_in.class.php');
require_once('classes/bdr.class.php');



require_once('classes/lead_fileitem.php');
require_once('classes/lead_filegroup.php');
 

require_once('classes/supplier_country_group.php');
require_once('classes/supplier_city_item.php');
 
require_once('classes/docstatusitem.php');


require_once('classes/lead_view_item.php');
require_once('classes/kpgroup.php');

require_once('classes/an_lead_actions.class.php');

require_once('classes/doc_in.class.php');
require_once('classes/doc_out.class.php');
require_once('classes/doc_vn.class.php');
require_once('classes/tender_history_item.php');

require_once('classes/app_contract_group.php');
require_once('classes/app_contract_item.php');
require_once('classes/app_contract_filegroup.php');
require_once('classes/app_contract_fileitem.php');
require_once('classes/app_contract_creator.php');
require_once('classes/app_contract_historygroup.php');
require_once('classes/app_contract_historyitem.php'); 



$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'������ �� �������');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}
//require_once('inc/restr.php');

$_supplier=new SupplierItem;
$log=new ActionLog;
$_supgroup=new SuppliersGroup;

 
$_orgitem=new OrgItem;
$orgitem=$_orgitem->GetItemById($result['org_id']);
$_opf=new OpfItem;
$opfitem=$_opf->GetItemById($orgitem['opf_id']);


$_appc_group = new AppContractGroup;
$_appc_item = new AppContractItem;

$available_appc = $_appc_group->GetAvailableAppContractIds($result['id']);

if(!isset($_GET['action'])){
	if(!isset($_POST['action'])){
		$action=0;
	}else $action=abs((int)$_POST['action']);
}else $action=abs((int)$_GET['action']);

if(!isset($_GET['from_begin'])){
	if(!isset($_POST['from_begin'])){
		$from_begin=0;
	}else $from_begin=1; 
}else $from_begin=1;

$object_id=array();
switch($action){
    case 0:
	$object_id[]=1151;
	break;
    case 1:
	$object_id[]=1152;
	break;
    case 2:
	$object_id[]=950;
	break;
    default:
	$object_id[]=950;
	break;
}

$_editable_status_id = array(1, 4);


if(!isset($_GET['print'])){
	if(!isset($_POST['print'])){
		$print=0;
	}else $print=abs((int)$_POST['print']); 
}else $print=abs((int)$_GET['print']);

//echo $object_id;
//die();
$cond=false;
foreach($object_id as $k=>$v){
    if($au->user_rights->CheckAccess('w',$v)){
	$cond=$cond||true;
    }
}

if(!$cond){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
} 


if($action==0){
    //	
} elseif(($action==1)||($action==2)||($action==10)||($action==3)||($action==4)){
    if(!isset($_GET['id'])){
        if(!isset($_POST['id'])){
            header("HTTP/1.1 404 Not Found");
            header("Status: 404 Not Found");
            include('404.php');
            die();
        } else $id = abs((int)$_POST['id']);	
    } else $id = abs((int)$_GET['id']);

    //�������� ������� ������������
    $editing_user = $_appc_item->GetItemById($id);
    if($editing_user === false){
        header("HTTP/1.1 404 Not Found");
        header("Status: 404 Not Found");
        include('404.php');
        die();
    }
    
    foreach($editing_user as $k => $v) $editing_user[$k] = stripslashes($v);
    
    if(!$au->user_rights->CheckAccess('w',1160)){	
            $is_shown = in_array($id, $available_appc);

            if(!$is_shown){
                    header("HTTP/1.1 404 Not Found");
                    header("Status: 404 Not Found");
                    include('404.php');
                    die();
            }
    }
}

//��������� ������
if(($action==0)&&(isset($_POST['doNew'])||isset($_POST['doNewEdit']))){
	$params=array();
	//������� �������� ������ ����������
	$params['posted_user_id'] = $result['id'];
	$params['pdate'] = time();
	$params['user_id'] = abs((int)$_POST['manager_id']);
	$params['description'] = SecStr($_POST['description']);
	$params['status_id'] = 1;
			
	//�����������
        foreach($_POST as $k=>$v){
            if(eregi("^supplier_id_([0-9]+)",$k)){
                $hash=abs((int)eregi_replace("^supplier_id_","",$k));

                $supplier_id = $hash; 
                //abs((int)$_POST['new_share_user_id_'.$hash]);
                //$right_id=abs((int)$_POST['new_share_right_id_'.$hash]);

                //������ ��������
                $contacts = array();
                //supplier_contact_id_%{$suppliers[supsec].id}%_%{$contact.id}%

                foreach($_POST as $k1=>$v1) if(eregi("^supplier_contact_id_".$supplier_id."_([0-9]+)",$k1)){
                    $contacts[]=abs((int)$v1);
                }
            }
        }
        
	$params['supplier_id'] = $supplier_id;
	$params['supplier_contact_id'] = $contacts[0];
	
        // ����������� �����
	$lc = new AppContractCreator();
	$lc->ses->ClearOldSessions();
	
	$params['code'] = SecStr($lc->GenLogin($result['id'])); //SecStr($_POST['code']);
		
		
	$code =	$_appc_item->Add($params);
	 
	//$code=1;
	//������ � �������
	if($code>0){
            $log->PutEntry($result['id'], '������ ������ �� �������', NULL, 1151, NULL, NULL, $code);	

            foreach($params as $k => $v){
                $log->PutEntry($result['id'], '������ ������ �� �������', NULL, 1151, NULL, '� ���� '.$k.' ����������� �������� '.$v, $code);
            }
	}
	 
	//�������� �����!
	//upload_file_6A83_tmp" value="_ZpaGsu91PI.jpg" 
	$fmi = new AppContractFileItem;
	foreach($_POST as $k=>$v){
            if(eregi("^upload_file_",$k)){
		$filename=eregi_replace("^upload_file_", '', eregi_replace("_tmp$",".tmp",$k));
		$fmi->Add(array('bill_id'=>$code, 'filename'=>SecStr(basename($filename)), 'orig_name'=>SecStr($v), 'user_id'=>$result['id'], 'pdate'=>time()));
		  
		$log->PutEntry($result['id'], '��������� ���� � ������ �� �������', NULL, 1151, NULL, '��������� ��� �����: '.SecStr(basename($filename)).' ��� �����: '.SecStr($v), $code);
            }
	}
	 
	//�����������
        $_supplier = new SupplierItem;
        $_opf = new OpfItem;
		
        //die();
        //������� � ������
        foreach($log_entries as $k=>$v){
            $supplier = $_supplier->GetItemById($v['supplier_id']);
            $opf = $_opf->GetItemById($supplier['opf_id']); 

            $description = SecStr($supplier['full_name'].' '.$opf['name'].', ����������: '.$v['note']);

            if($v['action']==0){
                  $log->PutEntry($result['id'],'������� ����������� � ������ �� �������',NULL,1151,NULL,$description,$code);	
            }elseif($v['action']==1){
                  $log->PutEntry($result['id'],'������������ ����������� � ������ �� �������',NULL,1151,NULL,$description,$code);
            }elseif($v['action']==2){
                  $log->PutEntry($result['id'],'������ ����������� �� ������ �� �������',NULL,1151,NULL,$description,$code);
            }
        }
		  
	if($au->user_rights->CheckAccess('w',1151)&&($_POST['do_confirm']==1)){
            $_appc_item->Edit($code,array('status_id'=>2, 'is_confirmed'=>1, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);

            $log->PutEntry($result['id'],'������������� �������� ���������� ������ �� �������',NULL,1151, NULL, NULL,$code);	
	}else{
            $log->PutEntry($result['id'],'��������� �� ��������������� ����������� ���������� ������ �� �������',NULL,1151, NULL, NULL,$code);	
	}
	 
	
	//�������� ��������� � �������� �������	
	//$_res->instance->NewTenderMessage($code);	
	
	/*
	echo '<pre>';
	print_r($_POST);
	print_r($params);
	echo '</pre>';
	die();
	*/
	//���������������
	if(isset($_POST['doNew'])){
		 header("Location: app_contract.php#user_".$code);
		die();
	}elseif(isset($_POST['doNewEdit'])){
		  
		header("Location: ed_app_contract.php?action=1&id=".$code.'&from_begin='.$from_begin);
		die();	
		
	}else{
		header("Location: app_contract.php");
		die();
	}
	 
	die();
}
elseif(($action==1)&&(isset($_POST['doEdit'])||isset($_POST['doEditStay']))){
	$params=array();
	
	//�������������� ��������, ���� ��������� ������
	//���� ��������� � ����������� �� �� ���������� � ������� �������
	$condition = in_array($id, $available_appc) && in_array($_POST['current_status_id'],$_editable_status_id) && in_array($editing_user['status_id'],$_editable_status_id);
	
	if($condition){
            $params['user_id']=abs((int)$_POST['manager_id']);
            $params['description'] = SecStr($_POST['description']);
            
            //�����������
            foreach($_POST as $k=>$v){
                if(eregi("^supplier_id_([0-9]+)",$k)){
                    $hash=abs((int)eregi_replace("^supplier_id_","",$k));

                    $supplier_id = $hash; 
                    //abs((int)$_POST['new_share_user_id_'.$hash]);
                    //$right_id=abs((int)$_POST['new_share_right_id_'.$hash]);

                    //������ ��������
                    $contacts = array();
                    //supplier_contact_id_%{$suppliers[supsec].id}%_%{$contact.id}%

                    foreach($_POST as $k1=>$v1) if(eregi("^supplier_contact_id_".$supplier_id."_([0-9]+)",$k1)){
                        $contacts[]=abs((int)$v1);
                    }
                }
            }

            $params['supplier_id'] = $supplier_id;
            $params['supplier_contact_id'] = $contacts[0];
	}
	else{
		//��������� �� ����� ���������
		if(($editing_user['is_confirmed']==1)&&in_array($editing_user['status_id'], $_editable_status_id)&&($editing_user['manager_id']==$result['id'])){
			$params['user_id']=abs((int)$_POST['manager_id']);
		}
	}
//	
	
	$_appc_item->Edit($id, $params);
	
		
	//$_appc_item->Edit($id, $params);
	//die();
	//������ � �������
	//������ � ���. �������� ������ � ����� ������
	foreach($params as $k=>$v){
            if(addslashes($editing_user[$k])!=$v){
                $log->PutEntry($result['id'],'������������ ������ �� �������',NULL,1150, NULL, '� ���� '.$k.' ����������� �������� '.$v,$id);
            }	
	}
	
 	if($condition){
            //�����������
            $_supplier = new SupplierItem;
            $_opf = new OpfItem;

            //die();
            //������� � ������
            foreach($log_entries as $k=>$v){
                $supplier = $_supplier->GetItemById($v['supplier_id']);
                $opf = $_opf->GetItemById($supplier['opf_id']); 

                $description = SecStr($supplier['full_name'].' '.$opf['name'].', ����������: '.$v['note']);

                if($v['action']==0){
                      $log->PutEntry($result['id'],'������� ����������� � ������ �� �������',NULL,1151,NULL,$description,$code);	
                }elseif($v['action']==1){
                      $log->PutEntry($result['id'],'������������ ����������� � ������ �� �������',NULL,1151,NULL,$description,$code);
                }elseif($v['action']==2){
                      $log->PutEntry($result['id'],'������ ����������� �� ������ �� �������',NULL,1151,NULL,$description,$code);
                }
            }
	}
	 
		//����������� ����������
        if($editing_user['is_confirmed']==1){
            // 
            if(($au->user_rights->CheckAccess('w',1155)) ){
                if(!isset($_POST['is_confirmed'])){
                    $_appc_item->Edit($id,array('status_id'=>4, 'is_confirmed'=>0, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
                    $log->PutEntry($result['id'],'���� ����������� ����������',NULL,1155, NULL, NULL,$id);	
                }
            } 
	}else{
            //���� �����
            if($au->user_rights->CheckAccess('w',1154) ){
                if(isset($_POST['is_confirmed'])&&($_POST['is_confirmed']==1)){
                    $_appc_item->Edit($id,array('status_id'=>2, 'is_confirmed'=>1, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
                    $log->PutEntry($result['id'],'�������� ����������',NULL,1154, NULL, NULL,$id);	
                    //die();
                }
            } 
	}
		
		
	//die();
	
	//���������������
	if(isset($_POST['doEdit'])){
		//header("Location: shedule.php#user_".$id);
		
		/*if($editing_user['task_id']>0) header("Location: ed_tender.php?action=1&id=".$editing_user['task_id'].'&from_begin='.$from_begin);
		
		else */
		header("Location: app_contract.php#user_".$code);
		die();
	}elseif(isset($_POST['doWork'])||isset($_POST['doEditStay'])){
	 
		header("Location: ed_app_contract.php?action=1&id=".$id.'&from_begin='.$from_begin);
		die();	
		
	}else{
		header("Location: app_contract.php");
		die();
	}
	
	die();
}


 //������ ������� 
if($action==1){
	$log=new ActionLog;
	 
	$log->PutEntry($result['id'],'������ ������ �� �������',NULL,1150, NULL, '������ �� ������� � '.$editing_user['code'],$id);
	 
	//������� ��� ��� �������������
	//$_tview=new Lead_ViewItem;
	//$test_view=$_tview->GetItemByFields(array('lead_id'=>$id, 'user_id'=>$result['id']));
	//if($test_view===false) $_tview->Add(array('lead_id'=>$id, 'user_id'=>$result['id']));	
	
	//����� ������ - ������� �� ��� ������� ������������, ��� �����������
		$_hg=new AppContractHistoryGroup;
		$history = $_hg->ShowHistory(
                    $editing_user['id'],
                    'app_contract/lenta'.$print_add.'.html', 
                    new DBDecorator(),
                    true,
                    true,
                    false,
                    $result,
                    $au->user_rights->CheckAccess('w',1158),
                    $au->user_rights->CheckAccess('w',1159),
                    $history_data,
                    true,
                    true
		);		
} 



//������ � �������
$stop_popup=true;

require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

if($print==0) $smarty->display('top.html');
else $smarty->display('top_print.html');
unset($smarty);


$_menu_id=84;
	
	if($print==0) include('inc/menu.php');
	
	
	//������������  ��������
	$smarty = new SmartyAdm;
	
	$sm1=new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	//�������� �������
	 if($action==0){
		if(isset($_GET['supplier_id'])&&($_GET['supplier_id']!=0)){
			//���� ����� ����������:
			//���������� � ����� ����� �����������
			//������� ������ ������ �����������, ����� ���� � ��� ���:
			//������� 2 -3- ���������� ��� �������� ���������� �����������
			//������ 4 - ���������� ��� �������� � ���������� ���������� �����������
			
			$supplier_id=abs((int)$_GET['supplier_id']);
			$sm1->assign('supplier_id', $supplier_id);
			
			$_si=new SupplierItem;
			$supplier=$_si->getitembyid($supplier_id);
			$sm1->assign('supplier', $supplier);
		} 
		 
		$sm1->assign('now_time',  date('d.m.Y H:i:s')); 
		$sm1->assign('now_date',  date('d.m.Y')); 
		 
		//������� ������ ���, ��� ����� ����� ����������� ����������
		$_usg1=new UsersSGroup;
		$usg1=$_usg1->GetUsersByRightArr('w', 1155);
		$sm1->assign('can_unconfirm_users',$usg1);
		$sm1->assign('can_unconfirm',$au->user_rights->CheckAccess('w',1155));
		
		
		 
		$sm1->assign('session_id', session_id());
		
		$sm1->assign('can_modify_supplier', true);
		$sm1->assign('can_modify_manager', true);
		
		$sm1->assign('can_confirm', $au->user_rights->CheckAccess('w',1154));
		
		
		$user_form=$sm1->fetch('app_contract/create.html');
	 } elseif($action==1){
		//�������������� �������
		
		if($print==0) $print_add='';
		else $print_add='_print';
		
		
		//�������� �������
		$can_modify = in_array($editing_user['status_id'],$_editable_status_id) && $au->user_rights->CheckAccess('w',1152);
		$can_modify_ribbon = !in_array($editing_user['status_id'], array(3));
		
		$editing_user['pdate'] = date('d.m.Y H:i:s', $editing_user['pdate']);
		$sm1->assign('pdate', $editing_user['pdate']);
                
		//���� �������������
		/*$editing_user['can_annul']=$_res->instance->DocCanAnnul($editing_user['id'],$reason,$editing_user, $result)&&$au->user_rights->CheckAccess('w',950);
		if(!$au->user_rights->CheckAccess('w',950)) $reason='������������ ���� ��� ������ ��������';
		$editing_user['can_annul_reason']=$reason;
		
		 
		
		$editing_user['can_restore']=$_res->instance->DocCanRestore($editing_user['id'],$reason,$editing_user)&&$au->user_rights->CheckAccess('w',961);
			if(!$au->user_rights->CheckAccess('w',961)) $reason='������������ ���� ��� ������ ��������';
		*/
		
		
			//������� ������ ���, ��� ����� ����� ����������� ����������
		$_usg1=new UsersSGroup;
		$usg1=$_usg1->GetUsersByRightArr('w', 1155);
		$sm1->assign('can_unconfirm_users',$usg1);
		$sm1->assign('can_unconfirm',$au->user_rights->CheckAccess('w',1155));
		
		
		 
		
		//���� �����������!
		if(($editing_user['is_confirmed']==1)&&($editing_user['user_confirm_id']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['user_confirm_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.date("d.m.Y H:i:s",$editing_user['confirm_pdate']);
			
			 
			$sm1->assign('confirmer',$confirmer);
			
			$sm1->assign('is_confirmed_confirmer',$confirmer);
		}
		
		$can_confirm_price=false;
		if($editing_user['is_confirmed_done']==0){
		  if($editing_user['is_confirmed']==1){
			  if($au->user_rights->CheckAccess('w',1154)){
				  //���� ����� + ��� ��������
				  $can_confirm_price=true;	
			  }else{
				  $can_confirm_price=false;
			  }
		  }else{
			  //95
			  $can_confirm_price=$au->user_rights->CheckAccess('w',1154)&&in_array($editing_user['status_id'],$_editable_status_id)  ;
		  }
		}
		$sm1->assign('can_confirm',$can_confirm_price);
		
		$reason='';
		
		
		//$sm1->assign('can_unconfirm_by_document',(int)$_res->instance->DocCanUnconfirmShip($editing_user['id'],$reason));
		//$sm1->assign('can_unconfirm_by_document_reason',$reason);
		
		
		
		//������� ����-�
		$_user_s=new UserSItem;
		$user_s=$_user_s->GetItemById($editing_user['manager_id']);
		$editing_user['manager_string']=$user_s['name_s'];
		
		
		
		
		//����� ������
		$_hg = new AppContractHistoryGroup;
		$history = $_hg->ShowHistory(
                    $editing_user['id'],
                    'app_contract/lenta'.$print_add.'.html', 
                    new DBDecorator(),
                    true,
                    true,
                    false,
                    $result,
                    $au->user_rights->CheckAccess('w',1158),
                    $au->user_rights->CheckAccess('w',1159),
                    $history_data,
                    true,
                    true
		);
		$sm1->assign('lenta',$history);
		$sm1->assign('lenta_len',count($history_data));
		
		 
		//�����������
		//$_suppliers=new Lead_SupplierGroup;
		//$sup=$_suppliers->GetItemsByIdArr($editing_user['id']);
                $sup = $_appc_item->GetSupplierItemByIdArr($editing_user['id']);
		$sm1->assign('suppliers', $sup);
		
		
		$sm1->assign('can_modify', $can_modify);  
		$sm1->assign('can_modify_ribbon', $can_modify_ribbon);  
		 
		 
		 $sm1->assign('can_modify_supplier', $can_modify);
		$sm1->assign('can_add_supplier', $au->user_rights->CheckAccess('w',87)); 
		
		//����� ��������� ��� ������������� 1 ������� 
		$sm1->assign('can_change_manager', ($editing_user['is_confirmed']==1)&&($editing_user['is_confimed_done']==0)&&(in_array($editing_user['status_id'], array(33,2,28)))&&($au->user_rights->CheckAccess('w',980)||($editing_user['manager_id']==$result['id'])));   
		 
		$sm1->assign('can_modify_manager', ( $can_modify&&($editing_user['tender_id']==0)));
		$sm1->assign('can_modify_iam', $editing_user['user_id'] == $result['id']);
		 
		
		$sm1->assign('bill', $editing_user);
		
		
		//������ ������������� ������
		$folder_id=0;
			 
			  $decorator=new DBDecorator;
			  
			  $decorator->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::DESC));
			 
			$decorator->AddEntry(new UriEntry('id',$id));
			  
			  $decorator->AddEntry(new SqlEntry('folder_id',$folder_id, SqlEntry::E));
			 $decorator->AddEntry(new UriEntry('folder_id',$folder_id));
		
			  $navi_dec=new DBDecorator;
			  $navi_dec->AddEntry(new UriEntry('action',1));
			  
			  
			  
			  
			  $ffg=new AppContractFileGroup(1,  $id,  new FileDocFolderItem(1,  $id, new AppContractFileItem(1)));;
			  
			  $filetext=$ffg->ShowFiles('app_contract/files_list.html', $decorator,0,10000,'ed_app_contract.php', 'app_contract_file.html', 'swfupl-js/app_contract_files.php',  
			  false,  
			 false, 
			 false , 
			  $folder_id, 
			  false, 
			false , 
			 false, 
			 false ,    
			  '',  
			  
			 false,  
			   $result, 
			   $navi_dec, 'file_' 
			   );
		
		
		$sm1->assign('files', $filetext);
		$user_form=$sm1->fetch('app_contract/edit'.$print_add.'.html');
	}
	
	
		
	 
	$sm->assign('from_begin',$from_begin);	
	
	$sm->assign('print_pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']).' '.$username;	
	$sm->assign('print_username',$username);
	
	$sm->assign('users',$user_form);
	
	
	$content=$sm->fetch('app_contract/ed_page'.$print_add.'.html');
	
	 
	
	$smarty->assign('fast_menu', $menu_arr_fast);
	  $smarty->assign('main_menu',$menu_res);
	$smarty->assign('content',$content);
	if($print==0) $smarty->display('page.html');
	else echo $content;
	unset($smarty);


$smarty = new SmartyAdm;

//������ � �������
require_once('inc/footer.php');
if(isset($footer_res)){
	$smarty->assign('footer',$footer_res);
}else $smarty->assign('footer','');

if($print==0) $smarty->display('bottom.html');
else $smarty->display('bottom_print.html');
unset($smarty); 
?>