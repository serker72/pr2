<?
require_once('classes/abstractitem.php');

$export_filename='districts.csv';
$final='';

$sql='select name from sprav_district order by id asc';
$set=new mysqlset($sql);
$rs=$set->GetResult();
$rc=$set->GetResultNumRows();

for($i=0; $i<$rc; $i++){
	$f=mysqli_fetch_array($rs);
		
	$final.=$f['name']."\n";
}


	
	header("Content-disposition: filename=".$export_filename);
      
	header("Content-type: text/csv"); 

	header("Pragma: no-cache"); 
	header("Expires: 0"); 
	
   
   $final="District\n".$final;
   
   
   $final=iconv('windows-1251', 'utf-8', $final);
   
   echo $final;


?>