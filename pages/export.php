<?php

class Export 
{
  function Export(&$app)
  {
    $this->app=&$app; 
     
    $this->app->ActionHandlerInit($this);

    $this->app->ActionHandler("google","ExportGoogle");
  
    $this->app->DefaultActionHandler("list");
  
    $this->app->ActionHandlerListen($app);
  }
 
  //www.google.de/merchants/default
  function ExportGoogle()
  {
header("Content-Type: text/plain");
/*echo '
id  title beschreibung	link  bild_url	preis preisart	währung	versand	zahlungsmethode	menge marke Modelname zustand produktart  standort
33  Rolloleinwand WS Brilliant R-Rollo	Rolloleinwand in schönem weißen Lackgehäuse. Hochwertiger Rollomechanismus mit sanftem Einzugsystem. Abwaschbares Tuch in Gain 1,0, 16:9 maskiert mit sehr guter Planlage. Mit schwarzem Rand und langen Vorlauf zur Deckenmontage. Decken- und Wandmontageset incl.. Das Tuch ist ein SE - Gewebeträger mit sehr guter Planlage.   http://shop.heimkinoraum.de/produkt_33_Rolloleinwand-WS-Brilliant-R-Rollo.html  http://heimkinoraum.de/products/33.jpg  349	unverhandelt  EUR :::0	"Paypal; Bank"	1 WS-Spalluto Rolloleinwand WS Brilliant R-Rollo  neu Leinwände	"Balanstrasse 358; 81549 München; Deutschland"';
*/
//echo utf8_decode('id  title beschreibung  link  bild_url  preis preisart  währung versand zahlungsmethode	menge marke Modelname zustand produktart  standort');
echo utf8_decode("\"ID\"|\"Titel\"|\"Beschreibung\"|\"Preis\"|\"Zustand\"|\"Link\"|\"bild_url\"|\"verfügbarkeit\"|\"Marke\"|\"MPN\"\n");


  $arr = $this->app->DB->SelectArr("SELECT * FROM artikel WHERE gesperrt=0 AND inaktiv=0 AND ausverkauft=0");

  foreach($arr as $key=>$value)
  {
    $id = $value[id];
    $name_de = ($value[name_de]);
    $artikel = $value[artikel];
    $standardbild = $value[standardbild];
    $marke = $value[hersteller];
    $mpn = $value[nummer];

    if($standardbild==0) {
      $datei = $this->app->DB->Select("SELECT datei FROM datei_stichwoerter WHERE artikel='$artikel' AND subjekt='Shopbild' LIMIT 1");
      //$standardbild = $this->app->DB->Select("SELECT id FROM datei WHERE datei='$datei'");
      $standardbild = $datei;
    }
    $kurztext_de = strip_tags($value[kurztext_de]);
    if($kurztext_de=="") $kurztext_de=$name_de;
    $preis = number_format($value[preis]*1.19,"2",",","");

    if($value[lieferzeit]=="lager") $verfuegbarkeit = "auf lager"; else $verfuegbarkeit="bestellbar";
    //$hesteller = $value[hersteller];
    //$warengruppe = $this->app->DB->Select("SELECT ag.bezeichnung FROM artikel_artikelgruppe aag LEFT JOIN artikelgruppen ag ON ag.id=aag.artikelgruppe WHERE aag.artikel='$artikel' LIMIT 1");
      echo utf8_decode("\"$id\"|\"$name_de\"|\"$kurztext_de\"|\"$preis\"|\"neu\"|\"http://shop.embedded-projects.net/index.php?module=artikel&action=artikel&id=$artikel&ref=1\"|\"http://shop.embedded-projects.net/index.php?module=artikel&action=datei&file=$standardbild\"|\"$verfuegbarkeit\"|\"$marke\"|\"$mpn\"\n");
 //     echo utf8_decode("$id\t$name_de\t$kurztext_de\thttp://www.embedded-projects.net/index.php?module=artikel&action=artikel&id=$artikel&ref=1\thttp://www.embedded-projects.net/index.php?module=artikel&action=datei&file=$standardbild\t$preis\tunverhandelt\tEUR\t:::0\tPaypal; Bank; Kreditkarte,Rechnung,Vorkasse,Nachanhme\t1\t$hersteller\tneu\t$warengruppe\t\"Holzbachstraße 4; 86152 Augsburg;Deutschland\"\n");
      //echo utf8_decode("$id\t$name_de\t$preis\n");
  }


    $this->app->BuildNavigation=false;
    exit;
  }
 


}
?>
