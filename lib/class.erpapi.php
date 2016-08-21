<?php

if( !function_exists('apache_request_headers') ) {
///
function apache_request_headers() {
  $arh = array();
  $rx_http = '/\AHTTP_/';
  foreach($_SERVER as $key => $val) {
    if( preg_match($rx_http, $key) ) {
      $arh_key = preg_replace($rx_http, '', $key);
      $rx_matches = array();
      // do some nasty string manipulations to restore the original letter case
      // this should work in most cases
      $rx_matches = explode('_', $arh_key);
      if( count($rx_matches) > 0 and strlen($arh_key) > 2 ) {
        foreach($rx_matches as $ak_key => $ak_val) $rx_matches[$ak_key] = ucfirst($ak_val);
        $arh_key = implode('-', $rx_matches);
      }
      $arh[$arh_key] = $val;
    }
  }
  return( $arh );
}
///
}

class erpAPI 
{

  function erpAPI($app)
  {
    $this->app=&$app;
  }

  function Lieferzeit($artikel)
  {
    $tmp = $this->app->DB->SelectArr("SELECT * FROM artikel WHERE artikel='$artikel' LIMIT 1");


  if($tmp[0][lieferzeit]=="green"||$tmp[0][lieferzeit]=="lagernd")
      $this->app->Tpl->Set(COLOR,"Green");
    else if($tmp[0][lieferzeit]=="yellow" || $tmp[0][lieferzeit]=="")
    {
      $this->app->Tpl->Set(COLOR,"Yellow");
    }
    else
      $this->app->Tpl->Set(COLOR,"Red");


  if($tmp[0][lieferzeitmanuell]!="")
//        if($_SESSION['language']=="de")
    return $tmp[0][lieferzeitmanuell];

    if($tmp[0][lieferzeit]=="green"||$tmp[0][lieferzeit]=="lagernd")
    {


        if($_SESSION['language']=="de")
        return "2-3 Tage";
        else
        return "2-3 Days";
     }
    else if($tmp[0][lieferzeit]=="yellow")
    {

      if(stristr($tmp[0]['name_de'], 'GNUBLIN') === FALSE) {
        if($_SESSION['language']=="de")
        return "2-3 Wochen";
        else
        return "2-3 Weeks";
      }
      else {
  if($_SESSION['language']=="de")
        return "3-5 Tage <br>(<a href=\"http://www.youtube.com/watch?v=ZXw9hGB2kos\" target=\"_blank\">in Produktion</a>)";
        else
        return "3-5 Days<br>(<a href=\"http://www.youtube.com/watch?v=ZXw9hGB2kos\" target=\"_blank\">in production</a>)";


      } 
    }
    else
    {
        if($_SESSION['language']=="de")
        return "z.Z. nicht lieferbar";
        else
        return "not available";
    }

  }

  function Captcha()
  {
    // Text erzeugen
    $str = "";
    $length = 0;
    for ($i = 0; $i < 6; $i++)
      $str .= chr(rand(97, 122));

    //setcookie("wawision_info", $str.'!');
    setcookie("wawision_info", $str);

    // Dimensionen
    $imgX = 80;
    $imgY = 35;
    $image = imagecreatetruecolor($imgX, $imgY);

    // Farben
    $rgb1 = rand(0, 255);
    $rgb2 = rand(0, 255);
    $rgb3 = rand(0, 255);

    // Bild füllen
    $backgr_col = imagecolorallocate($image, $rgb1, $rgb2, $rgb3);
    $border_col = imagecolorallocate($image, 208,208,208);
    $text_col = imagecolorallocate($image, ($rgb1 - 50), ($rgb2 - 50), ($rgb3 - 50));

    imagefilledrectangle($image, 0, 0, $imgX, $imgY, $backgr_col);
    imagerectangle($image, 0, 0, $imgX-1, $imgY-1, $border_col);


    // zufaellige pixel
    for($i=0;$i<177;$i++)
    {
      $randx = rand(0,$imgX);
      $randy = rand(0,$imgY);
      imagesetpixel($image,$randx,$randy,rand(0, 255));
    }


    $font = "./fonts/VeraSe.ttf";
    $font_size = 15;
    $angleMax = 20;
    $angle = rand(-$angleMax, $angleMax);
    $box = imagettfbbox($font_size, $angle, $font, $str);
    $x = (int)($imgX - $box[4]) / rand(1.8,2.2);
    $y = (int)($imgY - $box[5]) / 2;
    imagettftext($image, $font_size, $angle, $x, $y, $text_col, $font, $str);

    // Bild schicken
    header("Content-type: image/png");
    imagepng($image);
    imagedestroy ($image);
  }


  function ForceSSL()
  {
    if($_SERVER['HTTPS']!="on")
    {
     $this->app->Tpl->Set(HTTPS,"s");
     $redirect= "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
     header("Location:$redirect");
     exit;
    }
  }


  function Ausverkauft($id)
  {

    return $this->app->DB->Select("SELECT ausverkauft FROM artikel WHERE artikel='$id' LIMIT 1");
  }

  function MailSend($from,$from_name,$to,$to_name,$betreff,$text,$files,$signatur=true)
  {
    $this->app->mail->From       = $from;
    $this->app->mail->FromName   = utf8_decode($from_name);

    $this->app->mail->Subject    = utf8_decode($betreff);
    $this->app->mail->AddAddress($to, utf8_decode($to_name));

    if($signatur)
    $this->app->mail->Body = utf8_decode(str_replace('\r\n',"\n",$text).$this->Signatur());
    else
    $this->app->mail->Body = utf8_decode(str_replace('\r\n',"\n",$text));

    $this->app->mail->AddBCC('sauter@embedded-projects.net');
    $this->app->mail->AddBCC('claudia.sauter@embedded-projects.net');

    for($i=0;$i<count($files);$i++)
      $this->app->mail->AddAttachment($files[$i]);

    if(!$this->app->mail->Send()) {
      $error =  "Mailer Error: " . $this->app->mail->ErrorInfo;
      return 0;
    } else {
      $error = "Message sent!";
      return 1;
    }
  }


  function Steuerbefreit($land,$ustid)
  {
    if($land=="DE")
      return false;

    foreach($this->GetUSTEU() as $euland)
    {
      if($land==$euland && $ustid!="")
	return true;
      else if ($land==$euland && $ustid=="")
	return false;
    }

    // alle anderen laender sind export!
    return true;
  }

  function RechnungMitUmsatzeuer($rechnung)
  {
    return true;
    $adresse = $this->app->DB->Select("SELECT adresse FROM rechnung WHERE id='$rechnung' LIMIT 1");
    $land = $this->app->DB->Select("SELECT land FROM adresse WHERE id='$adresse' LIMIT 1");
    if($land =="DE")
      return true;

   // if($this->CheckLieferantEU($adresse))
    //  return false;

    // wenn lieferant DE dann mit 19% oder 7% einkaufen
    // wenn lieferant in der EU kann man mit 0% bezahlen 

    // wenn lieferant in der welt sowieso keine steuer sondern zoll

    // wenn wir von privat EU kaufen dann muss mit steuer gekauft werden! (SPAETER KANN ES SEIN)
    return false;
  }


  function Preis($id)
  {
    $artikel = $this->app->DB->SelectArr("SELECT * FROM artikel WHERE artikel='$id' LIMIT 1");
    if($artikel[0][umsatzsteuer]=="normal" || $artikel[0][umsatzsteuer]=="") $artikel[0][preis] = $artikel[0][preis] *1.19; else $artikel[0][preis]= $artikel[0][preis]*1.07;
    $artikel[0][preis] = number_format($artikel[0][preis],2,',','');
    return $artikel[0][preis];
  } 

//<div id="mainNavItem"><a class="first" [FIRSTNAV]</div>
//<div id="subNavItem"><a [SECNAV]</a></div>


  function Navigation($id=0)
  {
    //linke Navigationsleiste aufbauen
    $oberpunkte = $this->app->DB->SelectArr("SELECT id, bezeichnung, bezeichnung_en, plugin,pluginparameter FROM shopnavigation WHERE parent=$id ORDER BY position");
    $navigation = "";

    foreach($oberpunkte as $punkt){

    if($_SESSION['language']=="en") $bezeichnung = $punkt["bezeichnung_en"]; else $bezeichnung = $punkt["bezeichnung"];

      if($punkt["plugin"]=="PageID")
      {
        $navigation = $navigation.'<div id="mainNavItem"><a class="first" itemprop="url"
      href="[WEBROOT]/index.php?module=content&action=show&page='.$punkt["pluginparameter"].'"
                      >'.$bezeichnung.'</a></div>';

      }
      else    { 
        //href="index.php?module=artikel&action='.$punkt["plugin"].'&id='
//      $navigation = $navigation.'<div id="mainNavItem"><a class="first" 
//        href="[WEBROOT]/index.php?module=artikel&action='.$punkt["plugin"].'&id='
//                      .$punkt["pluginparameter"].'">'.$bezeichnung.'</a></div>';


        $org_bezeichnung = $bezeichnung;
        $bezeichnung = str_replace(" ","_",$bezeichnung);

      $navigation = $navigation.'<div id="mainNavItem"><a class="first" 
        href="[WEBROOT]/'.$bezeichnung.'" itemprop="url">
                      '.$org_bezeichnung.'</a></div>';

      }

      $unterpunkte = $this->getNavList($punkt['id']);
          //href="index.php?module=artikel&action='.$upunkt["plugin"].'&id='
      foreach($unterpunkte as $upunkt){

        $bezeichnung = str_replace(" ","_",$bezeichnung);
        $org_bezeichnung = $upunkt["bezeichnung"];
        $upunkt["bezeichnung"] = str_replace(" ","_",$upunkt["bezeichnung"]);

        $navigation = $navigation.'<div id="subNavItem"><a 
          href="[WEBROOT]/'.$bezeichnung.'/'.$upunkt["bezeichnung"].'" itemprop="url"
                      >'.$org_bezeichnung.'</a></div>';
      }
    }
    return $navigation;
  }



  function GetDateiDB($id)
  {
    $last_modified_time = $this->app->DB->Select("SELECT UNIX_TIMESTAMP(logdatei) FROM datei WHERE datei='$id' LIMIT 1");
    $inhalt64 = $this->app->DB->Select("SELECT inhalt FROM datei WHERE datei='$id' LIMIT 1");  
    $etag = md5_file($inhalt64); 


    // Getting headers sent by the client.
    $headers = apache_request_headers(); 

    
    // Checking if the client is validating his cache and if it is current.
    if (isset($headers['If-Modified-Since']) && (strtotime($headers['If-Modified-Since']) == $last_modified_time)) {
        // Client's cache IS current, so we just respond '304 Not Modified'.
        header('Last-Modified: '.gmdate('D, d M Y H:i:s', $last_modified_time).' GMT', true, 304);
    } else {
	$inhalt = base64_decode($inhalt64);
	$length = strlen($inhalt);
        // Image not cached or cache outdated, we respond '200 OK' and output the image.
        header('Last-Modified: '.gmdate('D, d M Y H:i:s', $last_modified_time).' GMT', true, 200);
        header('Content-Length: '.$length);
	$file_name = $etag.".jpg";
	header("Content-Disposition: inline; filename=\"$file_name\";\n\n");
        header('Content-Type: image/jpg');
	echo $inhalt;
    }

    exit; 
  }


  function getNavList($id)
  {
    // Gebe alle Unterpunkte von $id zurueck
    $result = $this->app->DB->SelectArr("SELECT id, bezeichnung, bezeichnung_en, plugin, pluginparameter FROM shopnavigation WHERE parent=$id ORDER BY position");
    foreach($result as $row){
      if($_SESSION['language']=="en") $bezeichnung = $row["bezeichnung_en"]; else $bezeichnung = $row["bezeichnung"];
      $unterpunkte[] = array('id'=>$row['id'], 'bezeichnung'=>$bezeichnung, 'plugin'=>$row['plugin'], 'pluginparameter' =>$row['pluginparameter']);
    }
    return $unterpunkte;
  }


  function GetProjektSelectMitarbeiter($adresse)
  {
    // Adresse ist Mitglied von Projekt xx
    // gibt man kein parameter an soll alles zurueck
    // entsprechen weitere parameter filtern die ausgabe
   $arr = $this->app->DB->SelectArr("SELECT adresse FROM bla bla where rolle=mitarbeiter von projekt xxx");
   foreach($arr as $value)
    {
      if($selected==$value) $tmp = "selected"; else $tmp="";
      $ret .= "<option value=\"$value\" $tmp>$value</option>";
    }
    return $ret;


  }

  function GetArtikelPreisvorlageProjekt($kunde,$projekt)
  {

    return 77.21;
  }

  function GetAuftragSteuersatz($auftrag)
  {
    //ermitteln aus Land und UST-ID Prüfung

    return 1.19;
  }


  function GetBetreff()
  {

    return array('Sonstige Anfrage','Frage zu einem Artikel','Frage zu einer Bestellung','Technische Frage','Anfrage / Angebot');
  }

  function GetBetreffSelect($selected)
  {
    foreach($this->GetBetreff() as $value)
    {
      if($selected==$value) $tmp = "selected"; else $tmp="";
      $ret .= "<option value=\"$value\" $tmp>$value</option>";
    }
    return $ret;
  }


  function GetKreditkarten()
  {

    return array('MasterCard','Visa','American Express');
  }

  function GetKreditkartenSelect($selected)
  {
    foreach($this->GetKreditkarten() as $value)
    {
      if($selected==$value) $tmp = "selected"; else $tmp="";
      $ret .= "<option value=\"$value\" $tmp>$value</option>";
    }
    return $ret;
  }


  function GetKundeSteuersatz($kunde)
  {


  }

  function AddUSTIDPruefungKunde($kunde)
  {
    //gebunden an eine adresse


  }

  function GetVersandkosten($projekt)
  {

    return 3.32;
  }

  function AddArtikelAuftrag($artikel,$auftrag)
  {
    // an letzter stelle artikel einfuegen mit standard preis vom auftrag

  }

  function DelArtikelAuftrag($id)
  {
    //loesche artikel von auftrag und schiebe positionen nach


  }

  function CreateAuftrag($kunde,$projekt)
  {



  }

  function GetAuftragStatus($auftrag)
  {



  }

  function EULand($land)
  {
    if($land=="" || $land=="DE")
      return false;

    foreach($this->GetUSTEU() as $euland)
    { 
      if($land==$euland)
        return true;
    }

    // alle anderen laender sind export!
    return false;
  }



  function GetUSTEU()
  {
    return
    array('BE','IT','RO',
	  'BG','LV','SE',
	  'DK','LT','SK',
	  'DE','LU','SI',
	  'EE','MT','ES',
	  'FI','NL','CZ',
	  'FR','AT','HU',
	  'GR','PL','GB',
	  'IE','PT','CY');
  }


  function CheckUSTFormat($ust)
  {
    $land = substr($ust,0,2);
    $nummer = substr($ust,2);

    switch($land)
    {
      case "BE":
	//zehn, nur Ziffern; (alte neunstellige USt-IdNrn. werden durch Voranstellen der Ziffer Ø ergänzt)
	if(is_numeric($nummer) && strlen($nummer)==9)
	  return $land."0".$nummer;
	else if(is_numeric($nummer) && strlen($nummer)==10)
	  return $land.$nummer;
	else
	  return 0;
      break;

      case "BG":
	//   neun oder zehn, nur Ziffern
	if(is_numeric($nummer) && strlen($nummer)==9)
	  return $land.$nummer;
	else if(is_numeric($nummer) && strlen($nummer)==10)
	  return $land.$nummer;
	else
	  return 0;
      break;

      case "DK":
	//acht, nur ziffern
	if(is_numeric($nummer) && strlen($nummer)==8)
	  return $land.$nummer;
	else return 0;
      break;

      case "DE":
	//neun, nur ziffern
	if(is_numeric($nummer) && strlen($nummer)==9)
	  return $land.$nummer;
	else return 0;
      break;

      case "EE":
 	//neun, nur ziffern
	if(is_numeric($nummer) && strlen($nummer)==9)
	  return $land.$nummer;
	else return 0;
      break;

      case "FI":
 	//acht, nur ziffern
	if(is_numeric($nummer) && strlen($nummer)==8)
	  return $land.$nummer;
	else return 0;
      break;

      case "FR":
 	//elf, nur Ziffern bzw. die erste und / oder die zweite Stelle kann ein Buchstabe sein
	if(is_numeric($nummer) && strlen($nummer)==11)
	  return $land.$nummer;
	else if(ctype_digit(substr($nummer,0,1)) &&  is_numeric(substr($nummer,1)) && strlen($nummer)==11)
	  return $land.$nummer;
	else if(ctype_digit(substr($nummer,0,2)) &&  is_numeric(substr($nummer,2)) && strlen($nummer)==11)
	  return $land.$nummer;
	else return 0;
      break;

      case "EL":
 	//neun, nur ziffern
	if(is_numeric($nummer) && strlen($nummer)==9)
	  return $land.$nummer;
	else return 0;
      break;


      case "IE":
 	//acht, die zweite Stelle kann und die letzte Stelle muss ein Buchstabe sein
	if(ctype_digit(substr($nummer,7,1)) &&  is_numeric(substr($nummer,0,7)) && strlen($nummer)==8)
	  return $land.$nummer;
	else if(ctype_digit(substr($nummer,7,1)) && ctype_digit(substr($nummer,1,1)) && is_numeric(substr($nummer,0,7)) && strlen($nummer)==8)
	  return $land.$nummer;
	else return 0;
      break;

      case "IT":
 	//elf, nur ziffern
	if(is_numeric($nummer) && strlen($nummer)==11)
	  return $land.$nummer;
	else return 0;
      break;


      case "LV":
 	//elf, nur ziffern
	if(is_numeric($nummer) && strlen($nummer)==11)
	  return $land.$nummer;
	else return 0;
      break;

      case "LT":
 	//neu oder zwoelf, nur ziffern
	if(is_numeric($nummer) && strlen($nummer)==9)
	  return $land.$nummer;
	else if(is_numeric($nummer) && strlen($nummer)==12)
	  return $land.$nummer;
	else return 0;
      break;

      case "LU":
 	//acht, nur ziffern
	if(is_numeric($nummer) && strlen($nummer)==8)
	  return $land.$nummer;
	else return 0;
      break;

      case "MT":
 	//acht, nur ziffern
	if(is_numeric($nummer) && strlen($nummer)==8)
	  return $land.$nummer;
	else return 0;
      break;

      case "AT":
 	//neun, nur ziffern die erste Stelle muss U sein
	if(is_numeric(substr($nummer,1,8)) && $nummer[0]=="U" && strlen($nummer)==9)
	  return $land.$nummer;
	else return 0;
      break;


      case "PL":
 	//zehn, nur ziffern
	if(is_numeric($nummer) && strlen($nummer)==10)
	  return $land.$nummer;
	else return 0;
      break;

      case "PT":
 	//neun, nur ziffern
	if(is_numeric($nummer) && strlen($nummer)==9)
	  return $land.$nummer;
	else return 0;
      break;


      case "RO":
 	//maximal zehn, nur ziffern, erste stelle !=0
	if(is_numeric($nummer) && strlen($nummer)>=10 && $nummer[0]!=0)
	  return $land.$nummer;
	else return 0;
      break;

      case "SE":
 	//zwölf, nur Ziffern, die beiden letzten Stellen bestehen immer aus der Ziffernkombination „Ø1“
	if(is_numeric($nummer) && strlen($nummer)==12 && $nummer[10] == 0 && $nummer[11]==1)
	  return $land.$nummer;
	else return 0;
      break;


      case "SK":
 	//zehn, nur ziffern
	if(is_numeric($nummer) && strlen($nummer)==10)
	  return $land.$nummer;
	else return 0;
      break;

      case "SI":
 	//acht, nur ziffern
	if(is_numeric($nummer) && strlen($nummer)==8)
	  return $land.$nummer;
	else return 0;
      break;

      case "ES":
 	//neun, die erste und die letzte Stelle bzw. die erste oder die letzte Stelle kann ein Buchstabe sein
	if(is_numeric($nummer) && strlen($nummer)==9)
	  return $land.$nummer;
	else if(is_numeric(substr($nummer,1,7)) && strlen($nummer)==9 && ctype_digit(substr($nummer,0,1)) && ctype_digit(substr($nummer,8,1)) )
	  return $land.$nummer;
	else if(is_numeric(substr($nummer,1,8)) && strlen($nummer)==9 && ctype_digit(substr($nummer,0,1)))
	  return $land.$nummer;
	else if(is_numeric(substr($nummer,0,8)) && strlen($nummer)==9 && ctype_digit(substr($nummer,8,1)))
	  return $land.$nummer;
	else return 0;
      break;

      case "CZ":
 	//   acht, neun oder zehn, nur Ziffern
	if(is_numeric($nummer) && strlen($nummer)>=8 && strlen($nummer)<=10)
	  return $land.$nummer;
	else return 0;
      break;

      case "HU":
 	//acht, nur ziffern
	if(is_numeric($nummer) && strlen($nummer)==8)
	  return $land.$nummer;
	else return 0;
      break;

      case "GB":
 	//neu oder zwoelf, nur ziffern, für Verwaltungen und Gesundheitswesen: fünf, die ersten zwei Stellen GD oder HA

	if(is_numeric($nummer) && strlen($nummer)==9)
	  return $land.$nummer;
	else if(is_numeric($nummer) && strlen($nummer)==12)
	  return $land.$nummer;
	else if(is_numeric(substr($nummer,2,3)) && $nummer[0]=="G" && $nummer[1]=="D")
	  return $land.$nummer;
	else if(is_numeric(substr($nummer,2,3)) && $nummer[0]=="H" && $nummer[1]=="A")
	  return $land.$nummer;
	else return 0;
      break;


      case "CY":
 	//neun, die letzte Stelle muss ein Buchstaben sein
	if(is_numeric(substr($nummer,0,8)) && strlen($nummer)==9 && ctype_digit(substr($nummer,8,1)))
	  return $land.$nummer;
	else return 0;
      break;


    }

  }
  
  function CheckUst($ust1,$ust2, $firmenname, $ort, $strasse, $plz, $druck="nein"){
    $tmp = new USTID();
    //echo $tmp->check("DE263136143","SE556459933901","Wind River AB","Kista","Finlandsgatan 52","16493","ja");
    $status = $tmp->check($ust1, $ust2, $firmenname, $ort, $strasse, $plz, $druck);

    //print_r($tmp->answer);
    if($tmp->answer['Erg_Name'] == 'A')$tmp->answer['Erg_Name'] = '';     
    if($tmp->answer['Erg_Ort'] == 'A')$tmp->answer['Erg_Ort'] = '';     
    if($tmp->answer['Erg_Str'] == 'A')$tmp->answer['Erg_Str'] = '';     
    if($tmp->answer['Erg_PLZ'] == 'A')$tmp->answer['Erg_PLZ'] = '';     
        $erg = array(
	'ERG_NAME' => $tmp->answer['Erg_Name'],
	'ERG_ORT' => $tmp->answer['Erg_Ort'],
	'ERG_STR' => $tmp->answer['Erg_Str'],
	'ERG_PLZ' => $tmp->answer['Erg_PLZ']);

    $error = 0;
    //1 wenn UST-ID. korrekt
    if($status == 1){
      if($tmp->answer['Erg_Name'] == 'B')$error++;
      if($tmp->answer['Erg_Ort'] == 'B')$error++;
      if($tmp->answer['Erg_Str'] == 'B')$error++;
      if($tmp->answer['Erg_PLZ'] == 'B')$error++;

      if($error > 0)
	return $erg;
      else{
        //Brief bestellen 
	$status = $tmp->check($ust1, $ust2, $firmenname, $ort, $strasse, $plz, "ja");	
	return 1;
    }
    }else{
      return 0;
    }
    //echo $tmp->check("DE2631361d3","SE556459933901","Wind River AB","Kista","Finlandsgatan 52","16493","ja");

  }

  function CreateTicket($projekt,$quelle,$kunde,$mailadresse,$betreff,$text,$medium="email")
  {

    $i=rand(300,700); 
    while(1)
    {
      $testschluessel = date('Ymd').sprintf("%04d",$i++);
      $check = $this->app->DB->Select("SELECT schluessel FROM ticket WHERE schluessel='$testschluessel' LIMIT 1");
      if($check=="") break;
    }

    $sql = "INSERT INTO ticket (`id`, `schluessel`, `zeit`, `projekt`, `quelle`, `status`, `kunde`, `mailadresse`, `prio`, `betreff`)
      VALUES (NULL, '$testschluessel', NOW(), '$projekt', '$quelle', 'offen', '$kunde', '$mailadresse', 
      '3','$betreff');";
    $this->app->DB->InsertWithoutLog($sql);
    $id = $this->app->DB->GetInsertID();


    $sql = "INSERT INTO `ticket_nachricht` (`id`, `ticket`, `zeit`,`text`,`betreff`,`medium`,`verfasser`, `mail`) 
     VALUES (NULL, '$testschluessel', NOW(), '$text','$betreff','$medium','$kunde', '$mailadresse');";

    $this->app->DB->InsertWithoutLog($sql);

    return $id;
  }

  function TicketMail($message,$error)
  {
    $tmp = $this->app->DB->SelectArr("SELECT * FROM ticket_nachricht WHERE id='$message' LIMIT 1"); 

    $email = "sauter@ixbat.de";
    $name = "Benedikt Sauter";

    $this->app->mail->From       = "support@embedded-projects.net";
    $this->app->mail->FromName   = "embedded projects GmbH";

    $this->app->mail->Subject    = $tmp[0]['betreff']." Ticket #".$tmp[0]['ticket'];;
    $this->app->mail->AddAddress($email, $name);

    $this->app->mail->Body = $tmp[0]['textausgang']."\r\n\r\nIhre Mail:\r\n\r\n".$tmp[0]['text'].$this->Signatur();

    if(!$this->app->mail->Send()) {
      $error =  "Mailer Error: " . $this->app->mail->ErrorInfo;
      $this->app->DB->Update("UPDATE ticket_nachricht SET status='beantwortet',versendet='0' WHERE id=".$message);  
      return 0;
    } else {
      $error = "Message sent!";
      $this->app->DB->Update("UPDATE ticket_nachricht SET status='beantwortet',versendet='1' WHERE id=".$message);  
      return 1;
    }
  }

  function Signatur()
  {
//P.S. Betriebsferien vom 15.08. - 26.08.2011
//    (In dieser Zeit berechnen wir keine Versandkosten bei Bestellungen über den Online-Shop)

    return "

embedded projects GmbH
Holzbachstraße 4
D-86152 Augsburg

Tel +49 821 2795990
Fax +49 821 27959920

Name der Gesellschaft: embedded projects GmbH
Sitz der Gesellschaft: Augsburg

Handelsregister: Augsburg, HRB 23930
Geschäftsführung: Benedikt Sauter, Dipl.-Inf.(FH)
USt-IdNr.: DE263136143

AGB: http://shop.embedded-projects.net/
";


  }

  function GetQuelleTicket()
  {
    return array('Telefon','Fax','Brief','Selbstabholer');
  }


  function GetPrioTicketSelect($prio)
  {
    $prios = array('5'=>'sehr niedrig','4'=>'niedrig','3'=>'normal','2'=>'wichtig','1'=>'sehr wichtig');

    foreach($prios as $key=>$value)
    {
      if($prio==$key) $selected="selected"; else $selected="";
      $ret .="<option value=\"$key\" $selected>$value</option>";
    }
    return $ret;
  }


  function GetWarteschlangeTicket()
  {
    return array('verwaltung'=>'Verwaltung','technik'=>'Technik','buchhaltung'=>'Buchhaltung');
  }

  function GetWarteschlangeTicketSelect($warteschlange)
  {
    $prios = $this->GetWarteschlangeTicket();

    foreach($prios as $key=>$value)
    {
      if($warteschlange==$key) $selected="selected"; else $selected="";
      $ret .="<option value=\"$key\" $selected>$value</option>";
    }
    return $ret;
  }


  function GetWartezeitTicket($zeit)
  {
    $timestamp = strToTime($zeit, null);
  

    $td = $this->makeDifferenz($timestamp,time());
    return $td['day'][0] . ' ' . $td['day'][1] . ', ' . $td['std'][0] . ' ' . $td['std'][1] . 
    ', ' . $td['min'][0] . ' ' . $td['min'][1];// . ', ' . $td['sec'][0] . ' ' . $td['sec'][1];
  }

  function makeDifferenz($first, $second){
    
    if($first > $second)
        $td['dif'][0] = $first - $second;
    else
        $td['dif'][0] = $second - $first;
    
    $td['sec'][0] = $td['dif'][0] % 60; // 67 = 7

    $td['min'][0] = (($td['dif'][0] - $td['sec'][0]) / 60) % 60; 
    
    $td['std'][0] = (((($td['dif'][0] - $td['sec'][0]) /60)- 
    $td['min'][0]) / 60) % 24;
    
    $td['day'][0] = floor( ((((($td['dif'][0] - $td['sec'][0]) /60)- 
    $td['min'][0]) / 60) / 24) );
    
    $td = $this->makeString($td);
    
    return $td;
    
  }


  function makeString($td){
    
    if ($td['sec'][0] == 1)
        $td['sec'][1] = 'Sekunde';
    else 
        $td['sec'][1] = 'Sekunden';
    
    if ($td['min'][0] == 1)
        $td['min'][1] = 'Minute';
    else 
        $td['min'][1] = 'Minuten';
        
    if ($td['std'][0] == 1)
        $td['std'][1] = 'Stunde';
    else 
        $td['std'][1] = 'Stunden';
        
    if ($td['day'][0] == 1)
        $td['day'][1] = 'Tag';
    else 
        $td['day'][1] = 'Tage';
    
    return $td;
    
  }


  function GetProjektSelect($projekt,$color_selected)
  {

    $sql = "SELECT id,name,farbe FROM projekt order by name";
    $tmp = $this->app->DB->SelectArr($sql);
    for($i=0;$i<count($tmp);$i++)
    {
      if($tmp[$i]['farbe']=="") $tmp[$i]['farbe']="white";
      if($projekt==$tmp[$i]['id']){
	$options = $options."<option value=\"{$tmp[$i]['id']}\" selected 
	  style=\"background-color:{$tmp[$i]['farbe']};\">{$tmp[$i]['name']}</option>";
	$color_selected = $tmp[$i]['farbe'];
      }
      else
        $options = $options."<option value=\"{$tmp[$i]['id']}\" 
	  style=\"background-color:{$tmp[$i]['farbe']};\">{$tmp[$i]['name']}</option>";
    }
    return $options;

  }

  function GetAdressName($id)
  {
    $result = $this->app->DB->SelectArr("SELECT name,vorname FROM adresse WHERE id='$id' LIMIT 1");
    return $result[0][vorname]." ".$result[0][name];
  }

  function GetAdressSubject()
  {
    return array('Kunde','Lieferant','Mitarbeiter','Ansprechpartner');
  }

  function GetAdressPraedikat()
  {
    return array('','von','fuer','ist');
  }

  function GetAdressObjekt()
  {
    return array('','Projekt');
  }

  function GetVersandartLieferant()
  {
    return array('DHL','UPS','Hermes','DPD','GLS','Post','Spedition');
  }

  function GetZahlungsweiseLieferant()
  {
    return array('Rechnung','Vorkasse','Nachnahme','Kreditkarte','Bar');
  }


  function GetArtikelWarengruppe()
  {
    //return array('SMD','THT','EBG','BGP');
    $tmp = array('','Bauteil','Eval-Board','Adapter','Progammer','Ger&auml;t','Kabel','Software','Dienstleistung','Spezifikation');
    sort($tmp);
    return $tmp;
  }

  function GetStatusBestellung()
  {
    return array('offen','freigegeben','bestellt','angemahnt','empfangen');
  }

  function GetSelectValueWieText($array, $selected)
  {
    foreach($array as $value)
    {
      if($selected==$value) $tmp = "selected"; else $tmp="";
      $ret .= "<option value=\"$value\" $tmp>$value</option>";
    }
    return $ret;
  }

  function GetSelect($array, $selected)
  {
    foreach($array as $key=>$value)
    {
      if($selected==$key) $tmp = "selected"; else $tmp="";
      $ret .= "<option value=\"$key\" $tmp>$value</option>";
    }
    return $ret;
  }

  function AddRolleZuAdresse($adresse, $subjekt, $praedikat, $objekt, $parameter)
  {
    // Insert ....  
    $sql ="INSERT INTO adresse_rolle (id, adresse, subjekt, praedikat, objekt, parameter)
	    VALUES ('','$adresse','$subjekt','$praedikat','$objekt','$parameter')";
    $this->app->DB->Insert($sql);
    $id =  $this->app->DB->GetInsertID();


    // wenn adresse zum erstenmal die rolle erhält wird kundennummer bzw. lieferantennummer vergeben
    if($subjekt=="Kunde")
    {
      $kundennummer = $this->GetNextKundennummer();
      $this->app->DB->Update("UPDATE adresse SET kundennummer='$kundennummer' WHERE id='$adresse' AND kundennummer='0' LIMIT 1");
    }

    if($subjekt=="Lieferant")
    {
      $lieferantennummer = $this->GetNextLieferantennummer();
      $this->app->DB->Update("UPDATE adresse SET lieferantennummer='$lieferantennummer' WHERE id='$adresse' AND lieferantennummer='0' LIMIT 1");
    }

  }

  function AddArbeitszeit($adr_id, $vonZeit, $bisZeit, $aufgabe, $beschreibung, $projekt, $paketauswahl)
  {
    $insert = "";
    if($paketauswahl=="manuell"){
      if($projekt=="")
        $projekt=0;
      $insert = 'INSERT INTO zeiterfassung (adresse, von, bis, aufgabe, beschreibung, projekt, buchungsart) VALUES ('.$adr_id.',"'.$vonZeit.'","'.$bisZeit.'","'.$aufgabe.'", "'.$beschreibung.'",'.$projekt.', "manuell")';
    }else{
      $projekt = $this->app->DB->SelectArr("SELECT aufgabe, beschreibung, projekt, kostenstelle FROM arbeitspakete WHERE id = $paketauswahl");
      $myArr = $projekt[0];
      $insert = 'INSERT INTO zeiterfassung (adresse, von, bis, arbeitspaket, aufgabe, beschreibung, projekt, buchungsart) VALUES ('.$adr_id.',"'.$vonZeit.'","'.$bisZeit.'",'.$paketauswahl.' , "'.$myArr["aufgabe"].'", "'.$myArr["beschreibung"].'",'.$myArr["projekt"].', "AP")';
    }
    $this->app->DB->Insert($insert);

      // wenn art=="AP" hole projekt und kostenstelle aus arbeitspaket beschreibung
      // und update zuvor angelegten datensatz
  }


  /**
   * \brief   Anlegen eines Arbeitspakets
   *
   *         Diese Funktion legt ein Arbeitspaket an.
   *
   * \param   aufgabe      Kurzbeschreibung (ein paar Woerter)  
   * \param   beschreibung  Textuelle Beschreibung 
   * \param   projekt      Projekt ID 
   * \param   zeit_geplant  Stundenanzahl Integer Wert
   * \param   kostenstelle  Kostenstelle 
   * \param   initiator            user id des Initiators
   * \param   abgabedatum   Datum fuer Abgabe 
   * \return                Status-Code
   *
   */
  function CreateArbeitspaket($adressse, $aufgabe,$beschreibung,$projekt,$zeit_geplant,$kostenstelle,$initiator,$abgabedatum="")
  {
      if(($abgabe != "") && ($beschreibung != "") && ($projekt != "") && ($zeit_geplant != "") && ($kostenstelle != "") && ($initiator != "")){
       $this->app->DB->Insert('INSERT INTO arbeitspakete                                                                                                                                   (adresse, aufgabe, beschreibung, projekt, zeit_geplant, kostenstelle, initiator, abgabedatum)                                                                VALUES (                                                                                                                                                      '.$adresse.',"'.$aufgabe.'", "'.$beschreibung.'", '.$projekt.', '.$zeit_geplant.','.$kostenstelle.', '.$initiator.',"'.$abgabedatum.'")');
       return 1;
      }else
       return 0;
  }

  function IsAdresseSubjekt($adresse,$subjekt)
  {
    $id = $this->app->DB->Select("SELECT id FROM adresse_rolle WHERE adresse='$adresse' AND subjekt='$subjekt' LIMIT 1");  
    if($id > 0)
      return 1;
    else return 0;
  }

  function AddOffenenVorgang($adresse, $titel, $href, $beschriftung="", $linkremove="")
  {
    $sql = "INSERT INTO offenevorgaenge (id,adresse,titel,href,beschriftung,linkremove) VALUES
	    ('','$adresse','$titel','$href','$beschriftung','$linkremove')";
    $this->app->DB->Insert($sql);
  }


  function RemoveOffenenVorgangID($id)
  {
    $sql = "DELETE FROM offenevorgaenge WHERE id='$id' LIMIT 1";
    $this->app->DB->Delete($sql);
  }


  function GetNextKundennummer()
  {
    $sql = "SELECT MAX(kundennummer) FROM adresse";
    $nummer = $this->app->DB->Select($sql) + 1;
    if($nummer==1)
      $nummer = 10000;
    return $nummer;
  }

  function GetNextLieferantennummer()
  {
    $sql = "SELECT MAX(lieferantennummer) FROM adresse";
    $nummer = $this->app->DB->Select($sql) + 1;
    if($nummer==1)
      $nummer = 70000;
    return $nummer;
  }


  function LoadBestellungStandardwerte($id,$adresse)
  {
    // standard adresse von lieferant       
    $arr = $this->app->DB->SelectArr("SELECT * FROM adresse WHERE id='$adresse' LIMIT 1");
    $field = array('name','vorname','abteilung','unterabteilung','strasse','adresszusatz','plz','ort','land','ustid','email','telefon','telefax','lieferantennummer');
    foreach($field as $key=>$value)
    {
      $this->app->Secure->POST[$value] = $arr[0][$value];
      $uparr[$value] = $arr[0][$value];
    }
    $this->app->DB->UpdateArr("bestellung",$id,"id",$uparr);
    $uparr="";

    //liefernantenvorlage
    $arr = $this->app->DB->SelectArr("SELECT * FROM lieferantvorlage WHERE adresse='$adresse' LIMIT 1");
    $field = array('kundennummer','zahlungsweise','zahlungszieltage','zahlungszieltageskonto','zahlungszielskonto','versandart');
    foreach($field as $key=>$value)
    {
      //$uparr[$value] = $arr[0][$value];
      $this->app->Secure->POST[$value] = $arr[0][$value];
    }
    //$this->app->DB->UpdateArr("bestellung",$id,"id",$uparr);

  }


  function CreateBestellung()
  {
    $belegmax = $this->app->DB->Select("SELECT MAX(belegnr) FROM bestellung WHERE firma='".$this->app->User->GetFirma()."'");
    if($belegmax==0) $belegmax = 10000;  else $belegmax++;

    $this->app->DB->Insert("INSERT INTO bestellung (id,datum,bearbeiter,firma,belegnr) 
      VALUES ('',NOW(),'".$this->app->User->GetAdresse()."','".$this->app->User->GetFirma()."','$belegmax')");

    return $this->app->DB->GetInsertID();
  }

  function GetUserKalender($adresse)
  {
    return $this->app->DB->SelectArr("SELECT id, name, farbe FROM kalender WHERE id IN (SELECT kalender FROM kalender_user WHERE adresse = $adresse);");
  }
  function GetAllKalender($adresse="")
  {
    return $this->app->DB->SelectArr("SELECT id, name, farbe".($adresse!=""?", IFNULL((SELECT 1 FROM kalender_user WHERE adresse=$adresse AND kalender_user.kalender=kalender.id),0) zugriff":"")." FROM kalender;");
  }
  
  function GetUserKalenderIds($adresse)
  {
    $arr = array();
    foreach ($this->GetUserKalender($adresse) as $value)
      array_push($arr,$value["id"]);
    return $arr;
  }

  function GetAllKalenderIds($adresse="")
  {
    $arr = array();
    foreach ($this->GetAllKalender($adresse) as $value)
      array_push($arr,$value["id"]);
    return $arr;
  }
  
  function GetKalenderSelect($adresse,$selectedKalender=array())
  {
    $arr = $this->GetUserKalender($adresse);
    foreach($arr as $value)
    { 
      $tmp = (in_array($value["id"],$selectedKalender))?" selected=\"selected\"":"";
      $ret .= "<option value=\"".$value["id"]."\"$tmp>".$value["name"]."</option>";
    }
    return $ret;
  }

  function GetKwSelect($selectedKW="")
  {
    foreach(range(1,52) as $kw)
    { 
      $tmp = ($selectedKW==$kw)?" selected=\"selected\"":"";
      $ret .= "<option value=\"$kw\"$tmp>$kw</option>";
    }
    return $ret;
  }

  function GetYearSelect($selectedYear="", $yearsBefore=2, $yearsAfter=10)
  {
    foreach(range(date("Y")-$yearsBefore, date("Y")+$yearsAfter) as $year)
    { 
      $tmp = ($selectedYear==$year)?" selected=\"selected\"":"";
      $ret .= "<option value=\"$year\"$tmp>$year</option>";
    }
    return $ret;
  }


  function CreateDatei($name,$titel,$beschreibung,$nummer,$datei,$ersteller)
  {
    $this->app->DB->Insert("INSERT INTO datei (id,titel,beschreibung,nummer) VALUES
      ('','$titel','$beschreibung','$nummer')");

    $fileid = $this->app->DB->GetInsertID();
    $this->AddDateiVersion($fileid,$ersteller,$name,"Initiale Version",$datei);

    return  $fileid;
  }


  function AddDateiVersion($id,$ersteller,$dateiname, $bemerkung,$datei)
  {
    // ermittle neue Version
    $version = $this->app->DB->Select("SELECT COUNT(id) FROM datei_version WHERE datei='$id'") + 1;

    // speichere werte ab 
    $this->app->DB->Insert("INSERT INTO datei_version (id,datei,ersteller,datum,version,dateiname,bemerkung)
    VALUES ('','$id','$ersteller',NOW(),'$version','$dateiname','$bemerkung')");

    $versionid = $this->app->DB->GetInsertID();
    move_uploaded_file($datei,"/home/eproo/shop/webroot/dms/".$versionid);
  }


  function AddDateiStichwort($id,$subjekt,$objekt,$parameter)
  {
    $this->app->DB->Insert("INSERT INTO datei_stichwoerter (id,datei,subjekt,objekt,parameter)
    VALUES ('','$id','$subjekt','$objekt','$parameter')");
  }


  function Wochenplan($adr_id,$parsetarget){
    $this->app->Tpl->Set(SUBSUBHEADING, "Wochenplan");
    $this->app->Tpl->Set(INHALT,"");

    $anzWochentage = 5;
    $startStunde = 6;
    $endStunde = 22;

    $wochentage = $this->getDates($anzWochentage);

    $inhalt = "";
    for($i=$startStunde;$i<=$endStunde;$i++){ // fuelle Zeilen 06:00 bis 22:00
        $zeile = array();
        $zeileCount = 0;
        foreach($wochentage as $tag){ // hole Daten fuer Uhrzeit $i und Datum $tage
          $result = $this->checkCell($tag, $i, $adr_id);
          if($result[0]['aufgabe'] != "")
	  {
	    if($result[0]['adresse']==0) $color = '#ccc'; else $color='#BCEE68';
	    if($result[0]['prio']==1) $color = 'red';
	    
            $zeile[$zeileCount] = '<div style="background-color: '.$color.'">'.$result[0]['aufgabe'].'</div>';
	  }
          else
            $zeile[$zeileCount] = "&nbsp;";
          $zeileCount++;
        }
        //print_r($zeile);
        $inhalt = $inhalt.$this->makeRow($zeile, $anzWochentage,$i.":00");
    }
    $this->app->Tpl->Set(WOCHENDATUM, $this->makeRow($wochentage, $anzWochentage));
    $this->app->Tpl->Set(INHALT,$inhalt);

    $this->app->Tpl->Parse($parsetarget,"zeiterfassung_wochenplan.tpl");

    $this->app->Tpl->Add($parsetarget,"<table><tr>                                                                                                                                     <td style=\"background-color:#BCEE68\">".$this->app->User->GetName()."</td>
      <td style=\"background-color:red\">Prio: Sehr Hoch (".$this->app->User->GetName().")</td>
      <td style=\"background-color:#ccc\">Allgemein</td></tr></table>");
  }

  function getDates($anzWochentage){
    // hole Datum der Wochentage von Mo bis $anzWochentage
    $montag = $this->app->DB->Select("SELECT DATE_SUB(CURDATE(),INTERVAL WEEKDAY(CURDATE()) day)");
    $week = array();
    for($i=0;$i<$anzWochentage;$i++)
      $week[$i] = $this->app->DB->Select("SELECT DATE_ADD('$montag',INTERVAL $i day)");
  return $week;
  }
  function makeRow($data, $spalten, $erstefrei="frei"){
    // erzeuge eine Zeile in der Tabelle
    // $erstefrei = 1 -> erste Spalte ist leer

    $row = '<tr>';
      if($erstefrei=="frei")
        $row = $row.'<td class="wochenplan">&nbsp;</td>';
      else
        $row = $row.'<td class="wochenplan">'.$erstefrei.'</td>';
      for($i=0;$i<$spalten; $i++)
        $row = $row.'<td class="wochenplan">'.$data[$i].'</td>';
    $row = $row.'</tr>';
  return $row;
  }


  function checkCell($datum, $stunde, $adr_id){
    // ueberprueft ob in der Stunde eine Aufgabe zu erledigen ist
    //echo $datum." ".$stunde."<br>";
    return  $this->app->DB->SelectArr("SELECT aufgabe,adresse,prio
                                    FROM aufgabe
                                    WHERE DATE(startdatum) = '$datum'
                                     AND HOUR(TIME(startzeit)) <= $stunde 
                                     AND HOUR(TIME(startzeit)) + stunden >= $stunde
                                     AND (adresse = $adr_id OR adresse = 0)
                                    OR 
                                     ((DATE_SUB('$datum', INTERVAL MOD(DATEDIFF('$datum',DATE_FORMAT(startdatum, '%Y:%m:%d')),intervall_tage) day)='$datum'
                                     AND DATE_SUB('$datum', INTERVAL MOD(DATEDIFF('$datum',DATE_FORMAT(startdatum, '%Y:%m:%d')),intervall_tage) day)
                                         > abgeschlossen_am
                                     AND intervall_tage>0 AND (adresse=$adr_id OR adresse=0))
                                     AND HOUR(TIME(startzeit)) <= $stunde AND HOUR(TIME(startzeit)) + stunden >= $stunde) 
                                    LIMIT 1");
}


}

?>
