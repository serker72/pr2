<?
session_start();
header('Content-type: text/html; charset=windows-1251');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/phpmailer/class.phpmailer.php');


$mail=new PHPMailer;

$sm=new SmartyAj;

$body=$sm->fetch('backup_send.html');

//echo $text;

$mail->SetFrom('support@gydex.ru', '����� ����������� ��������� CRM GYDEX');
		
//$mail->AddAddress('vpolikarpov@mail.ru');
$mail->AddAddress('info@aerotechgroup.ru');
$mail->AddAddress('support@gydex.ru');

$mail->Subject = "��������� ����� �� CRM GYDEX"; 
$mail->Body=$body;
 
$mail->CharSet = "windows-1251";
$mail->IsHTML(true);  

if(!$mail->Send())
{
	//echo "������ �������� ������: " . $mail->ErrorInfo;
}
else 
{
	
}


?>