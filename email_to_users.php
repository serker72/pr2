<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<title>�������� ��� ��������</title>
</head>

<body>

<?
require_once('classes/abstractitem.php');



require_once('classes/supcontract_item.php');

require_once('classes/actionlog.php');

require_once('classes/messageitem.php');


$topic='������� ����� �� ������������ � CRM GYDEX';

$sql='select * from user where is_active=1 and id in(select distinct user_id from user_rights where object_id=87)';
$set=new mysqlSet($sql);
$rs=$set->getresult();
$rc=$set->getresultnumrows();

$users=array();
for($i=0; $i<$rc; $i++){
	$f=mysqli_fetch_array($rs);
	
	 
	$users[]=$f;
		
}

//$users=array(
/*
	array(
		'name_s'=>'������� ��������� ����������',
		'email'=>'dariso2007@gmail.com',
		//'email'=>'vpolikarpov@mail.ru',
		'password'=>'1111111w',
		'login'=>'S0042'
		
	) /*
	
	array(
		'name_s'=>'�������� ������ ����������',
		'email'=>'krengel@yta.ru',
		//'email'=>'vpolikarpov@mail.ru',
		'password'=>'1111111t',
		'login'=>'S0034'
		
	)
	 */
	/*array(
		'name_s'=>'������� �������� ����������',
		'email'=>'kopenkin@yta.ru',
		//'email'=>'vpolikarpov@mail.ru',
		'password'=>'1111111w',
		'login'=>'S0043'
		
	)*//*,
	array(
		'name_s'=>'������� ���� ����������',
		'email'=>'nikitin@yta.ru',
		//'email'=>'vpolikarpov@mail.ru',
		'password'=>'1111111w',
		'login'=>'S0011'
		
	),
	array(
		'name_s'=>'������� ������� �������������',
		'email'=>'bogachev@yta.ru',
		//'email'=>'vpolikarpov@mail.ru',
		'password'=>'1111111w',
		'login'=>'S0006'
		
	),
	array(
		'name_s'=>'�������� ������� ����������',
		'email'=>'indushkin@yta.ru',
		//'email'=>'vpolikarpov@mail.ru',
		'password'=>'1111111w',
		'login'=>'S0009'
		
	)*/
	/*,
	array(
		'name_s'=>'��������� ������� ����������',
		'email'=>'ga@lidtech.ru',
		//'email'=>'vpolikarpov@gmail.com',
		'password'=>'dealer1',
		'login'=>'S0005'
		
	)
	*/
	/*array(
		'name_s'=>'������� ����� ����������',
		'email'=>'peskova@yta.ru',
		//'email'=>'vpolikarpov@gmail.com',
		'password'=>'111111q',
		'login'=>'S0013'
		
	),
	
	array(
		'name_s'=>'�������� ������ ���������',
		'email'=>'salahova@yta.ru',
		//'email'=>'vpolikarpov@gmail.com',
		'password'=>'111111q',
		'login'=>'S0026'
		
	)
*/

//);

 
foreach($users as $k=>$user){
	
	/*if($user['id']!=2){
		echo $user['name_s'].' skipping, demo mode<br />';	
		continue;
	}*/
	
	$text='';
	
	$text.='<div>';
	
	$text.='<em>������ ��������� ������������ �������������, ������� �� �������� �� ����.</em>';
	
	$text.='</div>';
	
	
	$text.='<div>';
	$text.='&nbsp;';
	$text.='</div>';

	$text.='<div>';
	
	$text.='���������(��) '.$user['name_s'].'!';
	
	$text.='</div>';
	
	
	$text.='<div>';
	$text.='&nbsp;';
	$text.='</div>';	
	
	
	$text.='<div>';
	
	$text.='��� �������� ������� �������� ������ � ������� ������������.<br />
��� ������� ������� ��� ����, ����� �� ��������� ��������� ���� ������������.';
	$text.='</div>';	
	
	
	$text.='<div>';
	$text.='��� ����, ����� ��������������� ��������, ������� ������ <img src="http://www.aet.gydex.ru/img/icons/pre_find.png" /> ��� ������� ������������ � ������� ������������. ��������� ���� ������. ������� ���� ��� ��������� ��������� ������ � ���� � ������� "�����".<br />
<br />
���� ����������� �� ��������� ��������� ����������, �� ��� ����� �������� � �������.<br />
���� ��� ����� ����������� ��� ��������, �� ����� ������� ������ �� ���. ����� - ��� ��������� ���� �������� �� ������ ��������� � ������������� �� ��� ����� �����������, ������� ����� ������ � ��������������� �������.';
	$text.='</div>';	
	
	 
	
	
	/*$text.='��� ������ � ��������� "���������������� �������" <a href="http://www.gydex.ru">www.gydex.ru</a>:';
	
	
	
	$text.='</div>';
	
	$text.='<div>';
	
	$text.='�����: '.$user['login'];
	
	
	
	$text.='</div>';
	
	$text.='<div>';
	
	$text.='������: '.$user['password'];
	*/
	
	
 
	
	
	
		$text.='<div>';
	$text.='&nbsp;';
	$text.='</div>';

	
	
	$text.='<div>';
	
	$text.='������ ������� ������!<br>� ���������, ��������� "CRM GYDEX. ���������������� �������".';
	
	
	
	$text.='</div>';
	
	$res=@mail($user['email_s'],$topic,$text,"From: \"".FEEDBACK_EMAIL."\" <".FEEDBACK_EMAIL.">\n"."Reply-To: ".FEEDBACK_EMAIL."\n"."Content-Type: text/html; charset=\"windows-1251\"\n");
	
	//var_dump($res);
	
	echo $user['email_s'];
	echo $text;
}	


?>
</body>
</html>