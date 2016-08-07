<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/suppliers_users_num.php');
 


$it=new SuppliersUsersNum;

$it->PutData(); //datefromdmy('21.10.2015')); 

//echo (date('d.m.Y H:i:s', 1445464740));
	
//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>