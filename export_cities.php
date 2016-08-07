<?
require_once('classes/abstractitem.php');

$export_filename='cities.csv';
$final='';

$sql='select 
c.name as name, r.name as r_name, 
d.name as d_name, cc.name as cc_name
from sprav_city as c 
left join sprav_district as d on c.district_id=d.id 
left join sprav_region as r  on c.region_id=r.id  
left join sprav_country as cc  on c.country_id=cc.id  

where cc.id=11
order by cc.name asc,  d.name asc, r.name asc, c.name asc,  c.id asc';
$set=new mysqlset($sql);
$rs=$set->GetResult();
$rc=$set->GetResultNumRows();


$final.='Страна;Федеральный округ;Область;Город'."\n";
for($i=0; $i<$rc; $i++){
	$f=mysqli_fetch_array($rs);
	
	$final.=$f['cc_name'].';'.$f['d_name'].';'.$f['r_name'].';'.$f['name']."\n";
		
	//$final.=$f['name'].','.$f['r_name']."\n";
}


	
	header("Content-disposition: filename=".$export_filename);
      
	header("Content-type: text/csv"); 

	header("Pragma: no-cache"); 
	header("Expires: 0"); 
	
   
   
   
  //  $final=iconv('windows-1251', 'utf-8', $final);
   echo $final;


?>