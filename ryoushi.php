<?php

  //=================//
 // ERROR REPORTING //
//=================//

    error_reporting(E_ALL);
    ini_set('display_errors', 1);


// Defining the basic cURL function
function curl($url) {

    $ch = curl_init();  // Initialising cURL

    $timeout = 2; // Initialise cURL timeout
    $useragent = 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:65.0) Gecko/20100101 Firefox/65.0';

    curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
    curl_setopt($ch, CURLOPT_URL, $url);    // Setting cURL's URL option with the $url variable passed into the function
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); // Return the webpage data as a string
    
    // curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
    // curl_setopt ($ch, CURLOPT_HEADER, 0);
    // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    // curl_setopt($ch, CURLOPT_CAINFO, 'https://'.$_SERVER['HTTP_HOST'].'/scotiabeautycom.crt');
    // curl_setopt($ch, CURLOPT_CAINFO, 'https://'.$_SERVER['HTTP_HOST'].'/cacert.pem');

    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout); // Setting cURL's timeout option

    if (curl_exec($ch) === FALSE) {

      curl_exec($ch);
      
      echo '<p><strong>Curl returns FALSE.</strong></p>';
      echo '<p><strong>Curl Error:</strong> <em>'.curl_error($ch).'</em></p>';

      echo '<pre>';
      print_r(curl_getinfo($ch));
      echo '</pre>';

    }    

    $data = curl_exec($ch); // Executing the cURL request and assigning the returned data to the $data variable
    curl_close($ch);    // Closing cURL
    
    return $data;   // Returning the data from the function
}


// Isolate and Extract Data
function Extract_Data($data, $start, $end) {
    $data = stristr($data, $start); // Stripping all data from before $start
    $data = substr($data, strlen($start));  // Stripping $start
    $stop = stripos($data, $end);   // Getting the position of the $end of the data
    $data = substr($data, 0, $stop);    // Stripping all data from after and including the $end of the data
    return $data;   // Returning the data from the function
}



echo '<!DOCTYPE html>

<html lang="en-GB">
<head>
<meta charset="utf-8">
<title>Ryoushi by Rounin Media</title>
<meta name="viewport" content="initial-scale=1.0" />
</head>

<body>

'; /* <form class="ryoushi" method="post" action="https://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'].'?ryoushi=true"> */ echo '


<form class="ryoushi" method="post" action="https://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'].'?ryoushi=true">

<h1 class="ryoushiHeading">Ryoushi by Rounin Media - Test</h1>

<input type="submit" class="readPagesSubmit" value="Get Data from Pages" />
';


if ((isset($_GET['ryoushi'])) && ($_GET['ryoushi'] === 'true')) {

  $Skip_Folders = array('.', '..', 'index.php', 'page.json');

  $FilePath = $_SERVER['DOCUMENT_ROOT'].'/.assets/content/pages/nail-products/';

  $Subfolders = scandir($FilePath);

  for ($i = 0; $i < count($Subfolders); $i++) {

    if (in_array($Subfolders[$i], $Skip_Folders)) continue;

    $SubFilePath = $FilePath.'/'.$Subfolders[$i];
    $SubSubfolders = scandir($SubFilePath);

    for ($j = 0; $j < count($SubSubfolders); $j++) {

      if (in_array($SubSubfolders[$j], $Skip_Folders)) continue;

      $Page_To_Read = 'https://'.$_SERVER['HTTP_HOST'].'/nail-products/'.$Subfolders[$i].'/'.$SubSubfolders[$j].'/';

      $Page_Markup = curl($Page_To_Read);

      $Page_Data = array();

      $Page_Data['Page_Heading'] = Extract_Data($Page_Markup, '<h1>', '</h1>');
      $Page_Data['Title'] = Extract_Data($Page_Markup, '<title>', ' &bull; Scotia Beauty');
      $Page_Data['Description'] = Extract_Data($Page_Markup, '<meta name="description" content="', ' #ScotiaBeauty');
      $Page_Data['Keywords'] = explode(', ', Extract_Data($Page_Markup, '<meta name="keywords" content="', ', #ScotiaBeauty'));
      $Page_Data['Canonical_URL'] = $Page_To_Read;
      
      
      /* EVERYTHING UP TO THIS POINT WAS, PRETTY MUCH, RYOUSHI */
      
      /*********************************************************/
      /*********************************************************/
      
      /* EVERYTHING AFTER THIS POINT ISN'T REALLY RYOUSHI - BUT IT USES DATA FISHED BY RYOUSHI */
      
      
      $Page_Manifest = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/.assets/content/pages/nail-products/'.$Subfolders[$i].'/'.$SubSubfolders[$j].'/page.json');

      $Page_Manifest_Array = json_decode($Page_Manifest, true);

      $Page_Manifest_Array['Document_Overview']['Editorial_Elements']['Page_Heading'] = $Page_Data['Page_Heading'];

      $Page_Manifest_Array['Document_Overview']['Meta_Information']['Title'] = $Page_Data['Title'];
      $Page_Manifest_Array['Document_Overview']['Meta_Information']['Description'] = $Page_Data['Description'];
      $Page_Manifest_Array['Document_Overview']['Meta_Information']['Keywords'] = $Page_Data['Keywords'];

      $Page_Manifest_Array['Document_Overview']['Social_Media']['Social_Title'] = $Page_Data['Title'];
      $Page_Manifest_Array['Document_Overview']['Social_Media']['Social_Description'] = $Page_Data['Description'];

      $Page_Manifest_Array['Document_Overview']['Document_Information']['Canonical_URL'] = $Page_Data['Canonical_URL'];

      $Page_Manifest_Array['Document_Overview']['Bibliographic_References']['Facebook'] = array();
      $Page_Manifest_Array['Document_Overview']['Bibliographic_References']['Facebook'][0] = FALSE;
      $Page_Manifest_Array['Document_Overview']['Bibliographic_References']['Facebook'][1] = array();
      $Page_Manifest_Array['Document_Overview']['Bibliographic_References']['Facebook'][1]['Page_Author'] = 'https://www.facebook.com/AlanLansdowne';
      $Page_Manifest_Array['Document_Overview']['Bibliographic_References']['Facebook'][1]['Page_Publisher'] = 'https://www.facebook.com/page/RouninMedia';

      $Page_Manifest_Array['Document_Overview']['Bibliographic_References']['Twitter'] = array();
      $Page_Manifest_Array['Document_Overview']['Bibliographic_References']['Twitter'][0] = FALSE;
      $Page_Manifest_Array['Document_Overview']['Bibliographic_References']['Twitter'][1] = array();
      $Page_Manifest_Array['Document_Overview']['Bibliographic_References']['Twitter'][1]['Page_Author'] = 'AlanLansdowne';
      $Page_Manifest_Array['Document_Overview']['Bibliographic_References']['Twitter'][1]['Page_Publisher'] = 'RouninMedia';

      unset($Page_Manifest_Array['Document_Overview']['Bibliographic_References']['Page_Author_on_Facebook']);
      unset($Page_Manifest_Array['Document_Overview']['Bibliographic_References']['Page_Publisher_on_Facebook']);
      unset($Page_Manifest_Array['Document_Overview']['Bibliographic_References']['Page_Author_on_Twitter']);
      unset($Page_Manifest_Array['Document_Overview']['Bibliographic_References']['Page_Publisher_on_Twitter']);

      $New_Page_Data = json_encode($Page_Manifest_Array, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

      $fp = fopen($_SERVER['DOCUMENT_ROOT'].'/.assets/content/pages/nail-products/'.$Subfolders[$i].'/'.$SubSubfolders[$j].'/page.json', 'w');
      fwrite($fp, $New_Page_Data);
      fclose($fp);
    }
  }
}


echo '</form>

</body>
</html>';

?>
