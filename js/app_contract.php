<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');
require_once('../classes/db_decorator.php');
 

require_once('../classes/app_contract_group.php');
require_once('../classes/app_contract_item.php');

 

$au=new AuthUser();
$result=$au->Auth(false,false);
$log=new ActionLog;

if($result===NULL){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();		
}
 
if(!isset($_GET['id'])){
    if(!isset($_POST['id'])){
        header("HTTP/1.1 404 Not Found");
        header("Status: 404 Not Found");
        include("404.php");
        die();		
    } else $id = abs((int)$_POST['id']);
} else $id = abs((int)$_GET['id']); 

$_appc_item = new AppContractItem;
$appc_item = $_appc_item->Getitembyid($id);

$appcg = new AppContractGroup;

$ret = '';
  

 if(isset($_POST['action'])&&($_POST['action']=="check_unconfirm")){
    //если ноль - то все хорошо
    if(!$_appc_item->CanUnconfirm($id, $rss55)) $ret = $rss55;
    else $ret=0;
} elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm")){
    //если ноль - то все хорошо
    if(!$_appc_item->CanConfirm($id, $rss55)) $ret = $rss55;
    else $ret=0;
//utv- razutv
} elseif(isset($_POST['action'])&&($_POST['action']=="toggle_confirm")){
    if($appc_item['is_confirmed']==1){
        //есть права: либо сам утв.+есть права, либо есть искл. права:
        if($au->user_rights->CheckAccess('w',1155) && $_appc_item->CanUnconfirm($id, $rss)){
            $_appc_item->Edit($id, array('is_confirmed'=>0, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()), true, $result);
            $log->PutEntry($result['id'], 'снял утверждение заявки на договор', NULL, 1155, NULL, NULL, $appc_item['code']);
            $ret = 0;
        } 
    } else {
        if($au->user_rights->CheckAccess('w',1154) && $_appc_item->CanConfirm($id, $rss)){
            $_appc_item->Edit($id, array('is_confirmed'=>1, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()), true, $result);
            $log->PutEntry($result['id'], 'утвердил заявку на договор', NULL, 1154, NULL, NULL, $appc_item['code']);
            $ret = 0;
        } 
    }
    
    // Если операция выполнилась - обновим строчку таблицы
    if ($ret == 0) {
        $dec = new  DBDecorator;
        $template='app_contract/app_contract_list.html';
        $dec->AddEntry(new SqlEntry('p.id', $id, SqlEntry::E));
        
        $appcg->SetAuthResult($result);

        
        $ret = $appcg->ShowPos(
            $template, 
            $dec, 
            0,
            100,
            $result['id'], 
            $au->user_rights->CheckAccess('w',1151) , 
            $au->user_rights->CheckAccess('w',1152), 
            $au->user_rights->CheckAccess('w',1153),
            $au->user_rights->CheckAccess('w',1154),
            $au->user_rights->CheckAccess('w',1155),
            $au->user_rights->CheckAccess('w',1156),
            $au->user_rights->CheckAccess('w',1157),
            false
        );
    }
} elseif(isset($_POST['action'])&&($_POST['action']=="toggle_annul")){
    if (($appc_item['status_id']==1) && ($appc_item['is_confirmed']==0)) {
        if($au->user_rights->CheckAccess('w',1156) && $_appc_item->CanUnconfirm($id, $rss)){
            $_appc_item->Edit($id, array('status_id'=>3), true, $result);
            $log->PutEntry($result['id'], 'аннулирование заявки на договор', NULL, 1156, NULL, NULL, $appc_item['code']);
            $ret = 0;
        } 
    } else {
        if($au->user_rights->CheckAccess('w',1157) && $_appc_item->CanUnconfirm($id, $rss)){
            $_appc_item->Edit($id, array('status_id'=>4), true, $result);
            $log->PutEntry($result['id'], 'восстановление заявки на договор', NULL, 1157, NULL, NULL, $appc_item['code']);
            $ret = 0;
        } 
    }
    
    // Если операция выполнилась - обновим строчку таблицы
    if ($ret == 0) {
        $dec = new  DBDecorator;
        $template='app_contract/app_contract_list.html';
        $dec->AddEntry(new SqlEntry('p.id', $id, SqlEntry::E));
        
        $appcg->SetAuthResult($result);

        
        $ret = $appcg->ShowPos(
            $template, 
            $dec, 
            0,
            100,
            $result['id'], 
            $au->user_rights->CheckAccess('w',1151) , 
            $au->user_rights->CheckAccess('w',1152), 
            $au->user_rights->CheckAccess('w',1153),
            $au->user_rights->CheckAccess('w',1154),
            $au->user_rights->CheckAccess('w',1155),
            $au->user_rights->CheckAccess('w',1156),
            $au->user_rights->CheckAccess('w',1157),
            false
        );
    }
}


//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>