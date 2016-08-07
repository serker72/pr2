<?
require_once('classes/global.php');
require_once('classes/authuser.php');
require_once('classes/tender.class.php');

require_once('classes/abstractversiongroup.php');

require_once('classes/news.class.php');




/*

require 'classes/vendor/autoload.php';

use PicoFeed\Reader\Reader;*/

//обнаружение рсс-потоков
/*try {

    $reader = new Reader;
    $resource = $reader->download('http://ria.ru/');

    $feeds = $reader->find(
        $resource->getUrl(),
        $resource->getContent()
    );

    print_r($feeds);
}
catch (PicoFeedException $e) {
    // Do something...
}

die();*/

/*
try {

    $reader = new Reader;
    $resource = $reader->download('http://ria.ru/export/rss2/index.xml');

    $parser = $reader->getParser(
        $resource->getUrl(),
        $resource->getContent(),
        $resource->getEncoding()
    );

    $feed = $parser->execute();
	
	//кодировка
	$encoding=$resource->getEncoding();
	echo $encoding;

   // echo $feed;
   $items=$feed->getItems(); 
   //print_r($items);
   
   foreach($items as $k=>$item){
	   if($k>=4) break;
	   
	    echo iconv($encoding, 'windows-1251', $item->getTitle());
		
		 echo $item->getDate()->format ( 'd.m.Y H:i:s' );
	   
	   echo iconv($encoding, 'windows-1251', $item->getContent());
	   
	    echo iconv($encoding, 'windows-1251', $item->getUrl());
	   
	 
	 
   }
}
catch (PicoFeedException  $e) {
    // Do something...
}

die();*/

/*

$_ai=new AbstractVersionGroup;

$_ai->GetItemsArr(0);

//echo 'zzzzzzzzzzzz';

// echo date('d.m.Y H:i:s', 1436779140);

die();*/
//require_once('classes/currency/currency_solver.class.php');



/*
print_r($_SERVER['SCRIPT_NAME']); die();
phpinfo(); die(); 
*/

echo mktime(11,21,13,1,18,2016);
 
//echo mktime(10,29,12,5,23,2015); 
die();

$_rem=new Tender_PopupGroup;

$res= $_rem->ShowKind2(2);
print_r($res);

die();

echo mktime(0,0,0,6,15,2015); 
echo '<br>';
echo mktime(23,59,59,6,15,2015); 

die();

$address='khodarinov@yta.ru';
//$address='vpolikarpov@mail.ru';
$topic='доступ к программе Интеллектуальные продажи';


$text="Уважаемый Сергей Николаевич!

Ваш доступ к программе \"Интеллектуальные продажи\" www.gydex.ru :

Логин: S0035
Пароль: 59dko47

С уважением, 
программа \"Интеллектуальные продажи\".\n
";

//@mail($address,$topic,$text,"From: \"".FEEDBACK_EMAIL."\" <".FEEDBACK_EMAIL.">\n"."Reply-To: ".FEEDBACK_EMAIL."\n"."Content-Type: text/plain; charset=\"windows-1251\"\n");

/*
phpinfo();

$zc=get_browser();
echo '<pre>';
var_dump($zc);
echo '</pre>';

*/


?>