<?
require_once('classes/abstractitem.php');

$export_filename='regions.csv';
$final='';

$sql='select r.name as name, d.name as d_name from sprav_region as r left join sprav_district as d on r. 	district_id=d.id  order by r.id asc';
$set=new mysqlset($sql);
$rs=$set->GetResult();
$rc=$set->GetResultNumRows();

for($i=0; $i<$rc; $i++){
	$f=mysqli_fetch_array($rs);
		
	$final.=$f['name'].','.$f['d_name']."\n";
}


	
	header("Content-disposition: filename=".$export_filename);
      
	header("Content-type: text/csv"); 

	header("Pragma: no-cache"); 
	header("Expires: 0"); 
	
   
   $final="Region,District\n".$final;
   
    $final=iconv('windows-1251', 'utf-8', $final);
   echo $final;


?>