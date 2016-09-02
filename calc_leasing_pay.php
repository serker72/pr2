<?
ini_set("display_errors", 1);
ini_set("track_errors", 1);
ini_set("html_errors", 1);
error_reporting(E_ERROR);


// Проверка и получение значений переменной из $_GET или $_POST
// $var_type = 's' - string, 'i' - integer, 'f' - float
function ksk_get_var_from_get_post($name, $var_type='s', $value_default='') {
    if (!isset($name) || ($name == '')) {
        $value = $value_default;
    } elseif (isset($_GET[$name])){
        if ($var_type == 's') { $value = $_GET[$name]; }
        elseif ($var_type == 'i') { $value = abs((int)$_GET[$name]); }
        elseif ($var_type == 'f') { $value = abs((float)$_GET[$name]); }
        else { $value = $value_default; }
    } elseif (isset($_POST[$name])){
        if ($var_type == 's') { $value = $_POST[$name]; }
        elseif ($var_type == 'i') { $value = abs((int)$_POST[$name]); }
        elseif ($var_type == 'f') { $value = abs((float)$_POST[$name]); }
        else { $value = $value_default; }
    } else {
        $value = $value_default;
    }
    
    return $value;
}

session_start();
Header("Cache-Control: no-store, no-cache, must-revalidate"); //для протокола HTTP/1.1
Header("Pragma: no-cache"); // для протокола HTTP/1.1
Header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT"); // дата и время генерации страницы
header("Expires: " . date("r")); // дата и время время, когда страница будет считаться устаревшей

require_once('classes/global.php');
require_once('classes/authuser.php');
require_once('classes/smarty/SmartyAdm.class.php');
require_once('classes/smarty/Smarty.class.php');
require_once('classes/discr_table_group.php');
require_once('classes/discr_table_objects.php');
require_once('classes/discr_table_user.php');
require_once('classes/actionlog.php');
require_once('classes/PHPExcel.php');


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Рассчитать лизинговые платежи');


$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

// Получим переменные из запроса
$action = ksk_get_var_from_get_post('action', 'i', 0);
$equipment_cost = ksk_get_var_from_get_post('equipment_cost', 'f', 0);
$additional_services_cost = ksk_get_var_from_get_post('additional_services_cost', 'f', 0);
$prepayment_cost = ksk_get_var_from_get_post('prepayment_cost', 'f', 0);
$contract_time = ksk_get_var_from_get_post('contract_time', 'i', 3);
$equipment_service_life = ksk_get_var_from_get_post('equipment_service_life', 'i', 7);
$k_amortiz = ksk_get_var_from_get_post('k_amortiz', 'f', 2);
$credit_rate = ksk_get_var_from_get_post('credit_rate', 'f', 0);
$nds_rate = ksk_get_var_from_get_post('nds_rate', 'f', 18);
$leasing_rate = ksk_get_var_from_get_post('leasing_rate', 'f', 4.5);

// Первоначальные значения
$Ha = 0;
$S = 0;
$Y = 0;
$Y_m = 0;
$u_amortiz = array();
$sy_cost = array();
$lead_pay = array();


// Рассчет
if($action == 1) {
    require_once('calc_leasing_pay_inc.php');
}




//работа с хедером
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
	
	
	//демонстрация  страницы
	$smarty = new SmartyAdm;
	
	$sm1=new SmartyAdm;
	
	$sm=new SmartyAdm;
        
        $sm->assign('equipment_cost', $equipment_cost);
        $sm->assign('additional_services_cost', $additional_services_cost);
        $sm->assign('prepayment_cost', $prepayment_cost);
        $sm->assign('contract_time', $contract_time);
        $sm->assign('equipment_service_life', $equipment_service_life);
        $sm->assign('k_amortiz', $k_amortiz);
        $sm->assign('credit_rate', $credit_rate);
        $sm->assign('nds_rate', $nds_rate);
        $sm->assign('leasing_rate', $leasing_rate);
        $sm->assign('has_save_excel', ($action == 1) && (count($lead_pay) > 0));
        
        
	$content = $sm->fetch('leasing/calc.html');
        
        if($action == 1) {
            $content .= '<hr><div><p>';
            $content .= 'Норма амортизационных отчислений - ' . $Ha . '<br>';
            $content .= 'Рассчитаем ускоренную амортизацию:<br>';
            $content .= '<table class="messagetable">';
            $content .= '<tr>';
            $content .= '<th>Год</th>';
            $content .= '<th>Годовая сумма амортизации</th>';
            $content .= '<th>Накопленная сумма амортизации</th>';
            $content .= '<th>Остаточная стоимость</th>';
            $content .= '<th>Ежемесячная сумма амортизации</th>';
            $content .= '</tr>';
        
            for($i=1;$i<=count($u_amortiz);$i++) {
                $content .= '<tr>';

                for($j=0;$j<5;$j++) {
                    $content .= '<td align="right">'.$u_amortiz[$i][$j].'</td>';
                }
                $content .= '</tr>';
            }

            $content .= '</table><br>';

            $content .= 'Плата за использованные кредитными ресурсами - ' . $S . '<br>';
            $content .= 'Ежегодный платёж за использованные кредитными ресурсами - ' . $Y . '<br>';
            $content .= 'Ежемесячный платёж за использованные кредитными ресурсами - ' . $Y_m . '<br>';

            $content .= 'Рассчитаем лизинговую премию за предоставленную услугу:<br>';
            $content .= '<table class="messagetable">';
            $content .= '<tr>';
            $content .= '<th>Год</th>';
            $content .= '<th>Месяц 1</th>';
            $content .= '<th>Месяц 2</th>';
            $content .= '<th>Месяц 3</th>';
            $content .= '<th>Месяц 4</th>';
            $content .= '<th>Месяц 5</th>';
            $content .= '<th>Месяц 6</th>';
            $content .= '<th>Месяц 7</th>';
            $content .= '<th>Месяц 8</th>';
            $content .= '<th>Месяц 9</th>';
            $content .= '<th>Месяц 10</th>';
            $content .= '<th>Месяц 11</th>';
            $content .= '<th>Месяц 12</th>';
            $content .= '<th>Месяц 13</th>';
            $content .= '<th>Среднегодовая стоимость</th>';
            $content .= '<th>Лизинговая премия</th>';
            $content .= '<th>Ежемесячная лизинговая премия</th>';
            $content .= '</tr>';
        
            for($i=1;$i<=count($u_amortiz);$i++) {
                $content .= '<tr>';

                for($j=0;$j<17;$j++) {
                    $content .= '<td align="right">'.$sy_cost[$i][$j].'</td>';
                }

                $content .= '</tr>';
            }

            $content .= '</table><br>';

            $content .= 'Итоговый расчёт ежемесячной суммы лизинга:<br>';
            $content .= '<table class="messagetable">';
            $content .= '<tr>';
            $content .= '<th>Месяц</th>';
            $content .= '<th>Амортизационные отчисления</th>';
            $content .= '<th>Плата за кредитные ресурсы</th>';
            $content .= '<th>Лизинговая премия</th>';
            $content .= '<th>Дополнительные услуги</th>';
            $content .= '<th>Сумма без НДС</th>';
            $content .= '<th>Сумма с НДС</th>';
            $content .= '<th>Зачет аванса</th>';
            $content .= '<th>Сумма ЛП</th>';
            $content .= '</tr>';
            
            $lps1 = 0;
            $lps2 = 0;
            $lps3 = 0;
        
            for($i=1;$i<=count($lead_pay);$i++) {
                $content .= '<tr>';

                for($j=0;$j<9;$j++) {
                    $content .= '<td align="right">'.(j > 0 ? number_format($lead_pay[$i][$j], 3) : $lead_pay[$i][$j]).'</td>';
                }
                $content .= '</tr>';
                
                $lps1 = $lps1 + $lead_pay[$i][6];
                $lps2 = $lps2 + $lead_pay[$i][7];
                $lps3 = $lps3 + $lead_pay[$i][8];
            }

            $content .= '<tr>';
            $content .= '<td>ИТОГО</td>';
            $content .= '<td>&nbsp;</td>';
            $content .= '<td>&nbsp;</td>';
            $content .= '<td>&nbsp;</td>';
            $content .= '<td>&nbsp;</td>';
            $content .= '<td>&nbsp;</td>';
            $content .= '<td align="right">'.number_format($lps1, 3).'</td>';
            $content .= '<td align="right">'.number_format($lps2, 3).'</td>';
            $content .= '<td align="right">'.number_format($lps3, 3).'</td>';
            $content .= '</tr>';
            
            $content .= '</table><br>';


            $content .= '</p></div>';
            
        }	
	 
	
	$smarty->assign('fast_menu', $menu_arr_fast);
	  $smarty->assign('main_menu',$menu_res);
	$smarty->assign('content',$content);
	if($print==0) $smarty->display('page.html');
	else echo $content;
	unset($smarty);


$smarty = new SmartyAdm;

//работа с футером
require_once('inc/footer.php');
if(isset($footer_res)){
	$smarty->assign('footer',$footer_res);
}else $smarty->assign('footer','');

if($print==0) $smarty->display('bottom.html');
else $smarty->display('bottom_print.html');
unset($smarty); 
?>