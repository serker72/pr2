<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<title>Документ без названия</title>
</head>

<body>

<?
require_once('classes/abstractitem.php');



require_once('classes/supcontract_item.php');

require_once('classes/actionlog.php');

require_once('classes/messageitem.php');


$topic='Быстрый поиск по контрагентам в CRM GYDEX';

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
		'name_s'=>'Макухин Александр Евгеньевич',
		'email'=>'dariso2007@gmail.com',
		//'email'=>'vpolikarpov@mail.ru',
		'password'=>'1111111w',
		'login'=>'S0042'
		
	) /*
	
	array(
		'name_s'=>'Кренгель Михаил Евгеньевич',
		'email'=>'krengel@yta.ru',
		//'email'=>'vpolikarpov@mail.ru',
		'password'=>'1111111t',
		'login'=>'S0034'
		
	)
	 */
	/*array(
		'name_s'=>'Копёнкин Вячеслав Евгеньевич',
		'email'=>'kopenkin@yta.ru',
		//'email'=>'vpolikarpov@mail.ru',
		'password'=>'1111111w',
		'login'=>'S0043'
		
	)*//*,
	array(
		'name_s'=>'Никитин Олег Витальевич',
		'email'=>'nikitin@yta.ru',
		//'email'=>'vpolikarpov@mail.ru',
		'password'=>'1111111w',
		'login'=>'S0011'
		
	),
	array(
		'name_s'=>'Богачев Алексей Александрович',
		'email'=>'bogachev@yta.ru',
		//'email'=>'vpolikarpov@mail.ru',
		'password'=>'1111111w',
		'login'=>'S0006'
		
	),
	array(
		'name_s'=>'Индюшкин Дмитрий Викторович',
		'email'=>'indushkin@yta.ru',
		//'email'=>'vpolikarpov@mail.ru',
		'password'=>'1111111w',
		'login'=>'S0009'
		
	)*/
	/*,
	array(
		'name_s'=>'Голавский Алексей Викторович',
		'email'=>'ga@lidtech.ru',
		//'email'=>'vpolikarpov@gmail.com',
		'password'=>'dealer1',
		'login'=>'S0005'
		
	)
	*/
	/*array(
		'name_s'=>'Пескова Ольга Евгеньевна',
		'email'=>'peskova@yta.ru',
		//'email'=>'vpolikarpov@gmail.com',
		'password'=>'111111q',
		'login'=>'S0013'
		
	),
	
	array(
		'name_s'=>'Салахова Резеда Ринатовна',
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
	
	$text.='<em>Данное сообщение сформировано автоматически, просьба не отвечать на него.</em>';
	
	$text.='</div>';
	
	
	$text.='<div>';
	$text.='&nbsp;';
	$text.='</div>';

	$text.='<div>';
	
	$text.='Уважаемый(ая) '.$user['name_s'].'!';
	
	$text.='</div>';
	
	
	$text.='<div>';
	$text.='&nbsp;';
	$text.='</div>';	
	
	
	$text.='<div>';
	
	$text.='Вам доступна функция быстрого поиска в реестре контрагентов.<br />
Эта функция полезна для того, чтобы не создавать дубликаты карт контрагентов.';
	$text.='</div>';	
	
	
	$text.='<div>';
	$text.='Для того, чтобы воспользоваться функцией, нажмите значок <img src="http://www.aet.gydex.ru/img/icons/pre_find.png" /> над списком контрагентов в реестре контрагентов. Откроется окно поиска. Введите один или несколько критериев поиска в поля и нажмите "Найти".<br />
<br />
Если контрагенты по указанным критериям существуют, то они будут показаны в таблице.<br />
Если эта карта контрагента Вам доступна, то будет активна ссылка на нее. Иначе - для уточнения всех вопросов Вы можете связаться с ответственным за эту карту контрагента, который будет указан в соответствующем столбце.';
	$text.='</div>';	
	
	 
	
	
	/*$text.='Ваш доступ к программе "Интеллектуальные продажи" <a href="http://www.gydex.ru">www.gydex.ru</a>:';
	
	
	
	$text.='</div>';
	
	$text.='<div>';
	
	$text.='Логин: '.$user['login'];
	
	
	
	$text.='</div>';
	
	$text.='<div>';
	
	$text.='Пароль: '.$user['password'];
	*/
	
	
 
	
	
	
		$text.='<div>';
	$text.='&nbsp;';
	$text.='</div>';

	
	
	$text.='<div>';
	
	$text.='Желаем удачной работы!<br>С уважением, программа "CRM GYDEX. Интеллектуальные продажи".';
	
	
	
	$text.='</div>';
	
	$res=@mail($user['email_s'],$topic,$text,"From: \"".FEEDBACK_EMAIL."\" <".FEEDBACK_EMAIL.">\n"."Reply-To: ".FEEDBACK_EMAIL."\n"."Content-Type: text/html; charset=\"windows-1251\"\n");
	
	//var_dump($res);
	
	echo $user['email_s'];
	echo $text;
}	


?>
</body>
</html>