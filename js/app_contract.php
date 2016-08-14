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
require_once('../classes/app_contract_notesitem.php');

 

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
$appcg_view = new AppContract_ViewGroup;

$ret = '';
$rec_upd = false;
  

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
    if($appc_item['is_confirmed'] == 1){
        //есть права: либо сам утв.+есть права, либо есть искл. права:
        if($au->user_rights->CheckAccess('w',1155) && $_appc_item->CanUnconfirm($id, $rss)){
            $_appc_item->Edit($id, array('status_id'=>4, 'is_confirmed'=>0, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()), true, $result);
            $log->PutEntry($result['id'], 'снял утверждение заявки на договор', NULL, 1155, NULL, NULL, $appc_item['code']);
            $rec_upd = true;
        } 
    } else {
        if($au->user_rights->CheckAccess('w',1154) && $_appc_item->CanConfirm($id, $rss)){
            $_appc_item->Edit($id, array('status_id'=>2, 'is_confirmed'=>1, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()), true, $result);
            $log->PutEntry($result['id'], 'утвердил заявку на договор', NULL, 1154, NULL, NULL, $appc_item['code']);
            $rec_upd = true;
        } 
    }
} elseif(isset($_POST['action'])&&($_POST['action']=="toggle_annul")){
    if ((($appc_item['status_id'] == 1) || ($appc_item['status_id'] == 4)) && ($appc_item['is_confirmed'] == 0)) {
        if($au->user_rights->CheckAccess('w',1156)){
            $_appc_item->Edit($id, array('status_id'=>3), true, $result);
            $log->PutEntry($result['id'], 'аннулирование заявки на договор', NULL, 1156, NULL, NULL, $appc_item['code']);
            
            //внести примечание
            $note = SecStr(iconv("utf-8","windows-1251", $_POST['note']));
            $_ni = new AppContractNotesItem;
            $_ni->Add(array(
                'app_contract_id' => $id,
                'posted_user_id' => $result['id'],
                'note' => 'Автоматическое примечание: документ был аннулирован пользователем '.SecStr($result['name_s']).' ('.$result['login'].'), причина: '.$note,
                'is_auto' => 1,
                'pdate' => time()
            ));	
        } 
    } elseif ($appc_item['status_id'] == 3) {
        if($au->user_rights->CheckAccess('w',1157)){
            $_appc_item->Edit($id, array('status_id'=>4, 'restore_pdate'=>time()), true, $result);
            $log->PutEntry($result['id'], 'восстановление заявки на договор', NULL, 1157, NULL, NULL, $appc_item['code']);
            
            //внести примечание
            $_ni = new AppContractNotesItem;
            $_ni->Add(array(
                'app_contract_id' => $id,
                'posted_user_id' => $result['id'],
                'note' => 'Автоматическое примечание: документ был восстановлен пользователем '.SecStr($result['name_s']).' ('.$result['login'].')',
                'is_auto' => 1,
                'pdate' => time()
            ));	
        } 
    }
}


//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>