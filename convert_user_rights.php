<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<title>Документ без названия</title>
</head>

<body>

<?
require_once('classes/abstractitem.php');

/*require_once('classes/usercontactdataitem.php');
//require_once('classes/suppliercontactitem.php');
require_once('classes/suppliercontactkindgroup.php');
require_once('classes/komplconfitem.php');
require_once('classes/komplnotesitem.php');



require_once('classes/komplgroup.php');

require_once('classes/komplitem.php');
require_once('classes/komplmarkitem.php');
require_once('classes/komplmarkpositem.php');
require_once('classes/komplpositem.php');
require_once('classes/komplmarkposgroup.php');
require_once('classes/komplmarkgroup.php');
require_once('classes/positem.php');
require_once('classes/komplnotesitem.php');

require_once('classes/komplconfitem.php');
require_once('classes/komplconfroleitem.php');

require_once('classes/actionlog.php');

*/

$user_id=91;
$old_user_id=2;

$sql='select * from user_rights where user_id='.$old_user_id;
$set=new mysqlSet($sql);
$rs=$set->getresult();
$rc=$set->getresultnumrows();

for($i=0; $i<$rc; $i++){
	$f=mysqli_fetch_array($rs);
	
	$sql1='insert into user_rights (user_id, right_id, object_id) values('.$user_id.', '.$f['right_id'].', '.$f['object_id'].')';
	new NonSet($sql1);
	 
	
		
}


$sql='select * from rl_user_rights  where user_id='.$old_user_id;
$set=new mysqlSet($sql);
$rs=$set->getresult();
$rc=$set->getresultnumrows();

for($i=0; $i<$rc; $i++){
	$f=mysqli_fetch_array($rs);
	
	$sql1='insert into rl_user_rights  (user_id, right_id, rl_object_id, rl_record_id) values('.$user_id.', '.$f['right_id'].', '.$f['rl_object_id'].', '.$f['rl_record_id'].')';
	new NonSet($sql1);
	 
	
		
}

?>
</body>
</html>