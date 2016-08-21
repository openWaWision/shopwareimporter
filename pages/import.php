<?php

class Import 
{
  //var $error = array();
  function Import($app)
  {
    $this->app=$app; 

    $this->app->ActionHandlerInit($this);

    $this->dump=false;

    $this->app->ActionHandler("auth","ImportAuth");
    $this->app->ActionHandler("getlist","ImportGetList");
    $this->app->ActionHandler("sendlist","ImportSendList");
    $this->app->ActionHandler("sendlistlager","ImportSendListLager");
    $this->app->ActionHandler("getarticle","ImportGetArticle");
    $this->app->ActionHandler("getfilelist","ImportGetFileList");
    $this->app->ActionHandler("getfilelistarticle","ImportGetFileListArticle");
    $this->app->ActionHandler("getauftraegeanzahl","ImportGetAuftraegeAnzahl");
    $this->app->ActionHandler("getauftrag","ImportGetAuftrag");
    $this->app->ActionHandler("deletearticle","ImportDeleteArticle");
    $this->app->ActionHandler("sendfile","ImportSendFile");
    $this->app->ActionHandler("deletefile","ImportDeleteFile");
    $this->app->ActionHandler("deleteauftrag","ImportDeleteAuftrag");
    $this->app->ActionHandler("updateauftrag","ImportUpdateAuftrag");
    $this->app->ActionHandler("navigation","ImportNavigation");
    $this->app->ActionHandler("artikelgruppen","ImportArtikelgruppen");
    $this->app->ActionHandler("exportlink","ImportExportlink");
    $this->app->ActionHandler("artikelartikelgruppen","ImportArtikelArtikelGruppe");
    $this->app->ActionHandler("addfilesubjekt","ImportAddFileSubjekt");
    $this->app->ActionHandler("inhalt","ImportInhalt");
    $this->app->ActionHandler("test","ImportTest");
    $this->app->ActionHandler("partnerlist","ImportPartnerList");

    $this->app->DefaultActionHandler("cmd");

    //file_put_contents("/tmp/log", "Init\r\n", FILE_APPEND | LOCK_EX);
    $this->DumpVar("Init"); 

    //  $this->DumpVar($this->app->Secure->GetGET("action"));
    // token pruefen!!! sonst abbruch DIE FOLGENDE ZEILE DARF NIE IM KOMMENTAR STEHEN! SONDERN MUSS AKTIV SEIN
    $this->CatchRemoteAuth();

    $this->app->ActionHandlerListen($app);
  }

  function GetIdbyNummer($nummer)
  { 
    $params = array(
    'useNumberAsId' => true
    );
    $result = $this->article = $this->app->client->call('articles/'.$nummer,ApiClient::METHODE_GET, $params);
    $this->DumpVar($result);
    $check = strpos($result ,"error:");

    if($check===0)
    {
      return null;
    } else {
      return $result["data"]["id"];
    }
  }

  function ImportPartnerList()
  {
    $tmp = $this->CatchRemoteCommand("data");
    $this->DumpVar($tmp); // hier alle Datenfelder sehen
    if(count($tmp) > 0)
    {
      foreach($tmp as $key=>$value)
      {
        $this->DumpVar("id ".$key);
        
        $checkid = $this->app->DB->Select("SELECT id FROM s_emarketing_partner WHERE idcode='".$value['ref']."' AND idcode!='' LIMIT 1");
        if($checkid<=0)
        {
          $this->app->DB->Insert("INSERT INTO s_emarketing_partner (id,idcode,datum,active,userID) VALUES ('','".$value['ref']."',NOW(),1,0)");
          $checkid = $this->app->DB->GetInsertID();
        }

        foreach($value as $column=>$cvalue)
        { 
          $this->DumpVar(" colum: $column ($cvalue)");
          switch($column)
          {
            case "name": $this->app->DB->Update("UPDATE s_emarketing_partner SET company='$cvalue' WHERE id='$checkid' LIMIT 1");break;
            case "netto": $this->app->DB->Update("UPDATE s_emarketing_partner SET percent='$cvalue' WHERE id='$checkid' LIMIT 1");break;
            case "strasse": $this->app->DB->Update("UPDATE s_emarketing_partner SET street='$cvalue' WHERE id='$checkid' LIMIT 1");break;
            case "email": $this->app->DB->Update("UPDATE s_emarketing_partner SET email='$cvalue' WHERE id='$checkid' LIMIT 1");break;
            case "telefax": $this->app->DB->Update("UPDATE s_emarketing_partner SET fax='$cvalue' WHERE id='$checkid' LIMIT 1");break;
            case "telefon": $this->app->DB->Update("UPDATE s_emarketing_partner SET phone='$cvalue' WHERE id='$checkid' LIMIT 1");break;
            case "ort": $this->app->DB->Update("UPDATE s_emarketing_partner SET city='$cvalue' WHERE id='$checkid' LIMIT 1");break;
            case "plz": $this->app->DB->Update("UPDATE s_emarketing_partner SET zipcode='$cvalue' WHERE id='$checkid' LIMIT 1");break;
            case "land": $this->app->DB->Update("UPDATE s_emarketing_partner SET country='$cvalue' WHERE id='$checkid' LIMIT 1");break;
          }
        }
      }
    }
    echo $this->SendResponse('ok');
    exit;
  }


  // get checksum list from the files 
  function ImportGetArticle()
  {
    $tmp = $this->CatchRemoteCommand("data");
    $nummer = $tmp['nummer'];
    $params = array(
    'useNumberAsId' => true
    );
    $result = $this->article = $this->app->client->call('articles/'.$nummer,ApiClient::METHODE_GET, $params);

  
    $this->DumpVar($result);

/*
Array ( [data] => Array ( [id] => 903 [mainDetailId] => 892 [supplierId] => 51 [taxId] => 1 [priceGroupId] => 1 [filterGroupId] => [configuratorSetId] => [name] => LPC3131FET180 [description] => 180 MHz ARM926EJ-S CPU core, high-speed USB 2.0 On-The-Go (OTG), up to 192 KB SRAM, NAND fash controller, flexible external bus interface, four channel 10-bit ADC [descriptionLong] =>

180 MHz ARM926EJ-S CPU, USB 2.0 Highspeed On-The-Go (OTG), bis zu 192 KB SRAM, NAND Flash-Controller, flexible externes Businterface, 4 Kanäle 10-bit ADC

Eagle Bauteil in unserer Bibliothek:

Embedded Eagle Library

[added] => 2014-02-09T00:00:00+0100 [active] => [pseudoSales] => 0 [highlight] => [keywords] => 1 [changed] => 2014-02-09T07:24:32+0100 [priceGroupActive] => [lastStock] => 1 [crossBundleLook] => 0 [notification] => [template] => [mode] => 0 [availableFrom] => [availableTo] => [configuratorSet] => [mainDetail] => Array ( [id] => 892 [articleId] => 903 [unitId] => [number] => 400129 [supplierNumber] => [kind] => 1 [additionalText] => [active] => 0 [inStock] => 0 [stockMin] => [weight] => [width] => [len] => [height] => [ean] => [position] => 0 [minPurchase] => [purchaseSteps] => [maxPurchase] => [purchaseUnit] => [referenceUnit] => [packUnit] => [shippingFree] => [releaseDate] => [shippingTime] => [prices] => Array ( [0] => Array ( [id] => 20508 [articleId] => 903 [articleDetailsId] => 892 [customerGroupKey] => EK [from] => 1 [to] => beliebig [price] => 3.605 [pseudoPrice] => 0 [basePrice] => 0 [percent] => 0 ) ) [attribute] => Array ( [id] => 892 [articleId] => 903 [articleDetailId] => 892 [attr1] => [attr2] => [attr3] => [attr4] => [attr5] => [attr6] => [attr7] => [attr8] => [attr9] => [attr10] => [attr11] => [attr12] => [attr13] => [attr14] => [attr15] => [attr16] => [attr17] => [attr18] => [attr19] => [attr20] => ) ) [tax] => Array ( [id] => 1 [tax] => 19.00 [name] => 19% ) [categories] => Array ( [108] => Array ( [id] => 108 [name] => Mikrocontroller ) ) [links] => Array ( ) [images] => Array ( [0] => Array ( [id] => 2025 [articleId] => 903 [articleDetailId] => [description] => [path] => index52f71f2082eff [main] => 1 [position] => 1 [width] => 0 [height] => 0 [relations] => [extension] => jpg [parentId] => [mediaId] => 2191 ) ) [downloads] => Array ( ) [related] => Array ( ) [propertyValues] => Array ( ) [similar] => Array ( ) [customerGroups] => Array ( ) [supplier] => Array ( [id] => 51 [name] => Sonstige [image] => [link] => [description] => ) [details] => Array ( ) [propertyGroup] => ) [success] => 1 ) 

*/
    if(!isset($result['data']['name']))
    {
       $this->error[]="Artikel in der Shop Datenbank nicht gefunden!";
    } else {
      $data['name']=$result['data']['name'];
      $data['kurztext_de']=$result['data']['description'];
      $data['uebersicht_de']=str_replace('<br />','</p><p>',$result['data']['descriptionLong']);

      if($result['data']['price']!="")
        $data['preis_netto']=$result['data']['price'];
      else
        $data['preis_netto']=$result['data']['mainDetail']['prices'][0]['price'];

      $data['aktiv']=$result['data']['active'];
      $data['restmenge']=$result['data']['lastStock'];

      if($result['data']['pseudoPrice']!="")
        $data['pseudopreis']=$result['data']['pseudoPrice'];
      else
        $data['pseudopreis']=$result['data']['mainDetail']['prices'][0]['pseudoPrice']*((100+$result['data']['tax']['tax'])/100);

      //$data['pseudolager']=$result['data']['mainDetail']['inStock'];
      $data['lieferzeitmanuell']=$result['data']['mainDetail']['shippingTime'];

      $this->DumpVar($data);
    }

    echo $this->SendResponse($data);
    exit;
  }

  function ImportTest()
  {
    //print_r($this->GetIdbyNummer("7777"));
    echo "TEST";
    exit;
  }

  // receive all new articles
  function ImportSendListLager()
  {
    $tmp = $this->CatchRemoteCommand("data");
    $this->DumpVar($tmp); // hier alle Datenfelder sehen

    $anzahl = 0;
    for($i=0;$i<count($tmp);$i++)
    {
      $artikel = $tmp[$i][artikel];
      $nummer = $tmp[$i][nummer];
      $lageranzahl = $tmp[$i][anzahl_lager];
      $laststock = $tmp[$i][restmenge];
      $inaktiv = $tmp[$i][inaktiv];
      $shippingtime = $tmp[$i][lieferzeitmanuell];
      $pseudolager = trim($tmp[$i][pseudolager]);
      if($pseudolager > 0) $lageranzahl=$pseudolager;

      if($inaktiv)$aktiv=0;
      else $aktiv=1;

      if($tmp[$i][ausverkauft]=="1"){
        $lageranzahl=0; $laststock="1";
      } 

      if($laststock!="1") $laststock=0;

      if($artikel!="ignore")
      {
        $testArticle = array(
            'name'     => $name_de,
            'lastStock'     => $laststock,
               'active'   => $aktiv,
            'mainDetail' => array(
            'shippingtime'     => $shippingtime,
                'active'   => $aktiv,
                'number' => $nummer,
                'inStock' => $lageranzahl,
            ),
        );

         $this->DumpVar("Nummer $nummer");
         $check = $this->GetIdbyNummer($nummer);
         $this->DumpVar($check);

         if($this->GetIdbyNummer($nummer)==NULL)
         {
            $this->DumpVar("UPDATE1");
            $result = $this->app->client->call('articles', ApiClient::METHODE_POST, $testArticle);
            $this->DumpVar("UPDATE11");
            $this->DumpVar($result);
            //if($result['data']['id'] > 0) $anzahl++;
         } else {
            $this->DumpVar("UPDATE2");
                $updateInStock = array(
                    'active'   => $aktiv,
                    'lastStock'     => $laststock,
                    'mainDetail' => array(
                    'shippingtime'     => $shippingtime,
                    
                    'active'   => $aktiv,
                        'number' => $nummer,
                        'inStock' => $lageranzahl,
                    )
                );
            $result = $this->app->client->call('articles/'.$this->GetIdbyNummer($nummer), ApiClient::METHODE_PUT, $updateInStock);
            $this->DumpVar($result);
         }
            $anzahl++;
      }
    }

    // anzahl erfolgreicher updates
    echo $this->SendResponse($anzahl);
    exit;
  }

  // receive all new articles
  function ImportSendList()
  {
    $tmp = $this->CatchRemoteCommand("data");
    $this->DumpVar($tmp); // hier alle Datenfelder sehen

    $anzahl = 0;
    for($i=0;$i<count($tmp);$i++)
    {
      $artikel = $tmp[$i][artikel];
      $hersteller = $tmp[$i][hersteller];
      $herstellerlink = $tmp[$i][herstellerlink];
      $nummer = $tmp[$i][nummer];
      $name_de = $tmp[$i][name_de];
      $name_en = $tmp[$i][name_en];
      $lageranzahl = $tmp[$i][anzahl_lager];
      $description = $tmp[$i][kurztext_de];
      $description_en = $tmp[$i][kurztext_en];
      $laststock = $tmp[$i][restmenge];
      $inaktiv = $tmp[$i][inaktiv];

      if($hersteller=="")$hersteller="Sonstige";

      if($inaktiv)$aktiv=0;
      else $aktiv=1;

      if($tmp[$i][ausverkauft]=="1"){
        $lageranzahl=0; $laststock="1";
      } 


      if($laststock!="1") $laststock=0;

      if($tmp[$i][kurztext_en]=="") $tmp[$i][kurztext_en] = $tmp[$i][kurztext_de];
      if($tmp[$i][uebersicht_en]=="") $tmp[$i][uebersicht_en] = $tmp[$i][uebersicht_de];
      if($tmp[$i][beschreibung_en]=="") $tmp[$i][beschreibung_en] = $tmp[$i][beschreibung_de];

      /*
      if($tmp[$i][links_de]!=""){
        $description_long = htmlspecialchars_decode($tmp[$i][uebersicht_de])."<br>".htmlspecialchars_decode($tmp[$i][beschreibung_de])."<br><b>Links:</b><br><br>".htmlspecialchars_decode($tmp[$i][links_de]);
      } else {
        $description_long = htmlspecialchars_decode($tmp[$i][uebersicht_de])."<br>".htmlspecialchars_decode($tmp[$i][beschreibung_de]);
      }*/
      $description_long = htmlspecialchars_decode($tmp[$i][uebersicht_de]);

      $preis = $tmp[$i][bruttopreis];
      $einkaufspreis = $tmp[$i][einkaufspreis];

      $pseudopreis = $tmp[$i][pseudopreis];//*1.19;
      $steuersatz = $tmp[$i][steuersatz];

      $pseudolager = trim($tmp[$i][pseudolager]);
      if($pseudolager > 0) $lageranzahl=$pseudolager;

      //for($x=1;$x<=$tmp[$i][anzahl_bilder];$x++){
      //  $images[]=array('link' => 'http://shop2011.embedded-projects.net/index.php?module=artikel&action=bildnummer&nummer='.$nummer.'&pos='.$x);
      //}

      if($artikel!="ignore")
      {

        $testArticle = array(
            'name'     => $name_de,
            'lastStock'     => $laststock,
//            'name_2'     => $name_en,
            'priceGroupId' => 1,
            'tax'      => $steuersatz,          // alternativ 
//             'taxId' => 1,
            'supplier' => $hersteller, // alternativ 'supplierId' => 2,
 //           'description'=>$description,
 //           'description_2'=>$description_en,
//            'descriptionLong'=>$description_long,
               'active'   => $aktiv,
      //      'images' => 
       //       $images
        //    ,

            'mainDetail' => array(
                'active'   => $aktiv,
                'number' => $nummer,
    //            'inStock' => $lageranzahl, //UPDATE LAGER
                'prices' => array(
                    array(
                        'customerGroupKey' => 'EK',
                        'price' => $preis,
                        'pseudoPrice' => $pseudopreis,
                    ),
/*		    array(
                        'customerGroupKey' => '8',
                        'price' => $einkaufspreis*1.19,
                    )
*/
                )
            ),
        );

         $this->DumpVar("Nummer $nummer");
         $check = $this->GetIdbyNummer($nummer);
         $this->DumpVar($check);
         if($this->GetIdbyNummer($nummer)==NULL)
         {
            $this->DumpVar("UPDATE1");
            $result = $this->app->client->call('articles', ApiClient::METHODE_POST, $testArticle);
            $this->DumpVar("UPDATE11");
            $this->DumpVar($result);
            //if($result['data']['id'] > 0) $anzahl++;
         } else {
            $this->DumpVar("UPDATE2");
                $updateInStock = array(
                    'name'     => $name_de,
                    'priceGroupId' => 1,
                    'tax' => $steuersatz,
                    //'taxId' => 1,
                    'description'=>$description,
                    'descriptionLong'=>$description_long,
                    'supplier' => $hersteller, // alternativ 'supplierId' => 2,
                    'active'   => $aktiv,
                    'lastStock'     => $laststock,
                    'descriptionLong'=>$description_long,
                    'mainDetail' => array(
                    'active'   => $aktiv,
                        'number' => $nummer,
//                        'inStock' => $lageranzahl, //UPDATE LAGER
                        'prices' => array(
                    array(
                        'customerGroupKey' => 'EK',
                        'price' => $preis,
                        'pseudoPrice' => $pseudopreis,
                    ),
/*
                    array(
                        'customerGroupKey' => '2',
                        'price' => 99999//$einkaufspreis*1.19,
                    )
*/

)

                    )
                );
            $result = $this->app->client->call('articles/'.$this->GetIdbyNummer($nummer), ApiClient::METHODE_PUT, $updateInStock);
            $this->DumpVar("ERGEBNIS ".$this->GetIdbyNummer($nummer));
            $this->DumpVar($result);
         }
            $anzahl++;
          
      }

    }

    // anzahl erfolgreicher updates
    echo $this->SendResponse($anzahl);
    exit;
  }

  function ImportSendList_OLD()
  {
 //   file_put_contents("/tmp/log", "Demo\r\n", FILE_APPEND | LOCK_EX);

    $tmp = $this->CatchRemoteCommand("data");
//    $this->DumpVar($tmp);

    $anzahl = 0;
    for($i=0;$i<count($tmp);$i++)
    {
      $artikel = $tmp[$i][artikel];
      $hersteller = $tmp[$i][hersteller];
      $herstellerlink = $tmp[$i][herstellerlink];
      $nummer = $tmp[$i][nummer];
      $name_de = $tmp[$i][name_de];
      $description = $tmp[$i][kurztext_de];
      $description_long = html_entity_decode($tmp[$i][beschreibung_de]);
      $preis = $tmp[$i][preis];

      if($artikel!="ignore")
      {


        // pruefe ob es hersteller gibt
        $this->app->DB->Update("FLUSH TABLES");
	      $supplierid = $this->app->DB->Select("SELECT id FROM s_articles_supplier WHERE name='$hersteller' LIMIT 1");
        if($supplierid<=0)
        {
	        $this->app->DB->Insert("INSERT INTO s_articles_supplier (id,name,link) VALUES ('','$hersteller','$herstellerlink')");
          $supplierid = $this->app->DB->GetInsertID();
        } 

	      $articleID = $this->app->DB->Select("SELECT articleID FROM s_articles_details WHERE ordernumber='$nummer' LIMIT 1");
	      if(!is_numeric($articleID)) {

            // table s_articles
            $this->app->DB->Insert("INSERT INTO s_articles 
              (id,supplierID,name,description,description_long,datum,`active`,taxID,filtergroupID,main_detail_id) 
                VALUES ('','$supplierid','$name_de','$description','$description_long',NOW(),'1','1','1','')");
            $articleID = $this->app->DB->GetInsertID();

            // table s_articles_details
            $this->app->DB->Insert("INSERT INTO s_articles_details (id,articleID,ordernumber,kind,active) VALUES ('','$articleID','$nummer','3','1')"); 
            $articledetailsID = $this->app->DB->GetInsertID();

            // update s_articles
            $this->app->DB->Update("UPDATE s_articles SET main_detail_id='$articledetailsID' WHERE id='$articleID' LIMIT 1");

            // table s_articles_attributes (id  articleID   articledetailsID)
            $this->app->DB->Insert("INSERT INTO s_articles_attributes (id,articleID,articledetailsID) VALUES ('','$articleID','$articledetailsID')"); 

            // table s_articles_prices ( id   pricegroup  from  to  articleID   articledetailsID  price   pseudoprice   baseprice   percent )
            $this->app->DB->Insert("INSERT INTO s_articles_prices (id,pricegroup,`from`,`to`,articleID,articledetailsID,price) 
              VALUES ('','EK','1','beliebig','$articleID','$articledetailsID','$preis')"); 

            // table s_articles_categories ( id   articleID   categoryID )
            // table s_articles_categories_ro in jede Kategorie darueber (von Deutsch bis zum Ende) (baum)

        } else {
          // Update

          $this->app->DB->Update("UPDATE s_articles SET name='$name_de', description='$description',description_long='$description_long', supplierID='$supplierid'
              WHERE id='$articleID' LIMIT 1");
            // table s_articles_prices ( id   pricegroup  from  to  articleID   articledetailsID  price   pseudoprice   baseprice   percent )
            $this->app->DB->Update("UPDATE s_articles_prices SET price='$preis' WHERE articleID='$articleID' LIMIT 1");

        }

	      $anzahl++;    
      }

    }

    // anzahl erfolgreicher updates
    echo $this->SendResponse($anzahl);
    exit;
  }


  // receive all new articles
  function ImportInhalt()
  {
    $tmp = $this->CatchRemoteCommand("data");
    $anzahl = 0;
    $this->app->DB->Delete("DELETE FROM inhalt");
    for($i=0;$i<count($tmp);$i++)
    { 

      $this->app->DB->Insert("INSERT INTO inhalt (id) VALUES ('')");
      $id = $this->app->DB->GetInsertID();

      foreach($tmp[$i] as $key=>$value)
      { 
        $this->app->DB->Update("UPDATE inhalt SET $key='$value' WHERE id='$id' LIMIT 1");
      }

      $anzahl++;
    }

    // anzahl erfolgreicher updates
    echo $this->SendResponse($anzahl);
    exit;
  }

  function ImportDeleteFile()
  {
    $tmp = $this->CatchRemoteCommand("data");

    // pruefe ob $tmp[datei] vorhanden wenn nicht lege an sonst update [inhalt] und [checksum]
    $datei = $tmp[datei];
    $checksum= $tmp[checksum];

    $this->app->DB->Delete("DELETE FROM datei WHERE datei='$datei' LIMIT 1");
    $this->app->DB->Delete("DELETE FROM datei_stichwoerter WHERE datei='$datei'");

    echo $this->SendResponse("ok");
    exit;
  }

  function ImportSendFile()
  {
    $tmp = $this->CatchRemoteCommand("data");

    // pruefe ob $tmp[datei] vorhanden wenn nicht lege an sonst update [inhalt] und [checksum]
    $datei = $tmp[datei];
    $inhalt= $tmp[inhalt];
    $checksum= $tmp[checksum];

    $this->app->DB->Delete("DELETE FROM datei WHERE datei='$datei' LIMIT 1");
    $this->app->DB->Delete("INSERT INTO datei (id,datei,inhalt,checksum,logdatei) VALUES ('','$datei','$inhalt','$checksum',NOW())");

    echo $this->SendResponse("ok");
    exit;
  }


  function ImportAddFileSubjekt()
  {
    $tmp = $this->CatchRemoteCommand("data");
    $artikel = $tmp[artikel];
    $subjekt= $tmp[subjekt];
    $datei= $tmp[datei];
    //loesche alle stichwoerter und lege alle neu an /subjekt /artikel
    $this->app->DB->Delete("DELETE FROM datei_stichwoerter WHERE artikel='$artikel' AND subjekt='$subjekt' AND datei='$datei' LIMIT 1");
    $this->app->DB->Delete("INSERT INTO datei_stichwoerter (artikel,subjekt,datei) VALUES ('$artikel','$subjekt','$datei')");

    echo $this->SendResponse("ok");
    exit;
  }


  // delete an article
  function ImportDeleteArticle()
  {
    $tmp = $this->CatchRemoteCommand("data");

    $this->app->DB->Select("DELETE FROM artikel WHERE artikel='$tmp' LIMIT 1");

    // anzahl erfolgreicher updates
    echo $this->SendResponse($tmp);
    exit;
  }

  function DumpVar($variable)
  {
    if($this->dump)
    {
      ob_start();
      var_dump($variable);
      $result = ob_get_clean();
      file_put_contents("/tmp/log", "$result\r\n", FILE_APPEND | LOCK_EX);
    }
  }

  // receive all new articles
  function ImportExportlink()
  {
    $tmp = $this->CatchRemoteCommand("data");
    $anzahl = 0;
    //$this->app->DB->Delete("DELETE FROM exportlink WHERE datum < DATE_ADD(NOW(), INTERVAL 5 DAYS)");

    for($i=0;$i<count($tmp);$i++)
    {

      $this->app->DB->Insert("INSERT INTO exportlink (id,datum) VALUES ('',NOW())");
      $id = $this->app->DB->GetInsertID();

      foreach($tmp[$i] as $key=>$value)
      {
        $this->app->DB->Update("UPDATE exportlink SET $key='$value' WHERE id='$id' LIMIT 1");
      }

      $anzahl++;
    }

    // anzahl erfolgreicher updates
    echo $this->SendResponse($anzahl);
    exit;
  }

  // receive all new articles
  function ImportArtikelgruppen()
  {
    $tmp = $this->CatchRemoteCommand("data");
    $anzahl = 0;
    $this->app->DB->Delete("DELETE FROM artikelgruppen");
    for($i=0;$i<count($tmp);$i++)
    {
      $id = $tmp[$i][id];

      $this->app->DB->Insert("INSERT INTO artikelgruppen (id) VALUES ('$id')");

      foreach($tmp[$i] as $key=>$value)
      {
        $this->app->DB->Update("UPDATE artikelgruppen SET $key='$value' WHERE id='$id' LIMIT 1");
      }

      $anzahl++;
    }

    // anzahl erfolgreicher updates
    echo $this->SendResponse($anzahl);
    exit;
  }

  // receive all new articles
  function ImportNavigation()
  {
    $tmp = $this->CatchRemoteCommand("data");
    $anzahl = 0;
    $this->app->DB->Delete("DELETE FROM shopnavigation");
    for($i=0;$i<count($tmp);$i++)
    {
      $id = $tmp[$i][id];

      $this->app->DB->Insert("INSERT INTO shopnavigation (id) VALUES ('$id')");

      foreach($tmp[$i] as $key=>$value)
      {
	      $this->app->DB->Update("UPDATE shopnavigation SET $key='$value' WHERE id='$id' LIMIT 1");
      }

      $anzahl++;
    }

    // anzahl erfolgreicher updates
    echo $this->SendResponse($anzahl);
    exit;
  }

  // receive all new articles
  function ImportArtikelArtikelGruppe()
  {
    $tmp = $this->CatchRemoteCommand("data");
    $anzahl = 0;
    $this->app->DB->Delete("DELETE FROM artikel_artikelgruppe");
    for($i=0;$i<count($tmp);$i++)
    {
      $id = $tmp[$i][id];

      $this->app->DB->Insert("INSERT INTO artikel_artikelgruppe (id) VALUES ('$id')");

      foreach($tmp[$i] as $key=>$value)
      {
	      $this->app->DB->Update("UPDATE artikel_artikelgruppe SET $key='$value' WHERE id='$id' LIMIT 1");
      }

      $anzahl++;
    }

    // anzahl erfolgreicher updates
    echo $this->SendResponse($anzahl);
    exit;
  }

  //TODO fuer Auftragimport
  //get checksum list from onlineshop
  function ImportGetAuftraegeAnzahl()
  {

    // filter nach orderStatusId
    $filterByOrderStatus = array(
        array(
            'property' => 'status',
            'value'    => 0
        ),
    );
     
    $params = array(
        'filter' => $filterByOrderStatus 
    );

    $result = $this->app->client->call('orders', ApiClient::METHODE_GET,$params);

    //$tmp = $this->app->DB->Select("SELECT COUNT(id) FROM auftraege"); // WHERE noch nicht abgeholt
    echo $this->SendResponse(count($result[data]));
    exit;
  }

  //TODO fuer AuftragImport
  function ImportUpdateAuftrag()
  {
    $tmp = $this->CatchRemoteCommand("data");

    // pruefe ob $tmp[datei] vorhanden wenn nicht lege an sonst update [inhalt] und [checksum]
    $auftrag = $tmp[auftrag];
    $zahlungok = $tmp[zahlung];
    $versandok = $tmp[versand];
    $tracking = $tmp[tracking];

    if($zahlungok=="ok" || $zahlungok=="1")
      $status_zahlung=12;
    else
      $status_zahlung=1;

   if($versandok=="ok" || $versandok=="1")
      $status_versand=7;
    else
      $status_versand=1;
/*
    $date = new DateTime();
    $date->modify('+10 days');
    $date = $date->format(DateTime::ISO8601);
*/
    $this->DumpVar("UPD Auftrag");
    $this->DumpVar($auftrag);

    $this->app->client->call('orders/'.$auftrag, ApiClient::METHODE_PUT, array(
   // 'paymentStatusId' => $status_zahlung,
    'orderStatusId' => 7,//$status_versand,
    'trackingCode' => $tracking
    //'comment' => 'Neuer Kommentar',
    //'transactionId' => '0',
 //   'clearedDate' => $date,
     ));

    //$this->app->DB->Delete("DELETE FROM auftraege WHERE id='$auftrag' LIMIT 1");

    echo $this->SendResponse("ok");
    exit;
  }

  //TODO fuer AuftragImport
  function ImportDeleteAuftrag()
  {
    $tmp = $this->CatchRemoteCommand("data");

    // pruefe ob $tmp[datei] vorhanden wenn nicht lege an sonst update [inhalt] und [checksum]
    $auftrag = $tmp[auftrag];

    $this->DumpVar("DEL Auftrag");
    $this->DumpVar($auftrag);
    $this->app->client->call('orders/'.$auftrag, ApiClient::METHODE_PUT, array(
    'orderStatusId' => 1,
     ));

    //$this->app->DB->Delete("DELETE FROM auftraege WHERE id='$auftrag' LIMIT 1");

    echo $this->SendResponse("ok");
    exit;
  }



  //TODO fuer Auftragimport
  // get checksum list from onlineshop
  function ImportGetAuftrag()
  {
        // filter nach orderStatusId
    $filterByOrderStatus = array(
      array(
        'property' => 'status',
        'value'    =>0
      ),
    );
         
    $params = array(
      'filter' => $filterByOrderStatus 
    );

    // holt immer einen Eintrag ab
    $result = $this->app->client->call('orders', ApiClient::METHODE_GET,$params);

    $warenkorb[auftrag] = $result[data][0][id];
    $result = $this->app->client->call('orders/'.$result[data][0][id], ApiClient::METHODE_GET);
    
    // LogFile 
    $this->DumpVar($result);

    $warenkorb[gesamtsumme] = $result[data][invoiceAmount];

    $warenkorb[transaktionsnummer] = $result[data][transactionId];
    $warenkorb[onlinebestellnummer] = $result[data][number];

    $warenkorb[versandkostennetto] = $result[data][invoiceShippingNet];
    $warenkorb[versandkostenbrutto] = $result[data][invoiceShipping];
    $warenkorb[freitext] = $result[data][customerComment];

    if($result[data][billing][company]=="")
      $warenkorb[name] = $result[data][billing][firstName]." ".$result[data][billing][lastName];
    else {
      $warenkorb[name] = $result[data][billing][company];
      $warenkorb[ansprechpartner] = $result[data][billing][firstName]." ".$result[data][billing][lastName];
    }

    if($result[data][billing][salutation]=="mr")
      $warenkorb[anrede]="herr";

    if($result[data][billing][salutation]=="mrs")
      $warenkorb[anrede]="frau";

    if($result[data][billing][company]!="")
      $warenkorb[anrede]="firma";


    $warenkorb[strasse] = $result[data][billing][street]." ".$result[data][billing][streetNumber];
    $warenkorb[plz] = $result[data][billing][zipCode];
    $warenkorb[ort] = $result[data][billing][city];
    $warenkorb[land] = $result[data][billing][country][iso];
    $warenkorb[email] = $result[data][customer][email];
    $warenkorb[affiliate_ref] = $result[data][customer][affiliate];
    $warenkorb[abteilung] = $result[data][billing][department];
    $warenkorb[steuerfrei] = $result[data][taxFree];

    //10 = Komplett in Rechnung gestellt
    //12 = Komplett bezahlt
    //18 = Reserviert ????
    //31 = Der Kredit wurde vorlaeufig akzeptiert.
    //32 = Der Kredit wurde genehmigt.
    //33 = Die Zahlung wurde von der Hanseatic Bank angewiesen.
    if($result[data][paymentStatus][id]==12)
    {
      $warenkorb['vorabbezahltmarkieren']=1;
    } else {
      $warenkorb['vorabbezahltmarkieren']=0;
    }

    switch($result[data][payment][name])
    {
      case "cash": $warenkorb[zahlungsweise] = "bar"; break;
      case "invoice": $warenkorb[zahlungsweise] = "rechnung"; break;
      case "prepayment": $warenkorb[zahlungsweise] = "vorkasse"; break;
      case "ipayment": $warenkorb[zahlungsweise] = "kreditkarte"; break;
      case "paypal": $warenkorb[zahlungsweise] = "paypal"; break;
      case "nachnahme": $warenkorb[zahlungsweise] = "nachnahme"; break;
      case "Amazoncba": $warenkorb[zahlungsweise] = "Amazoncba"; break;
      case "sofortueberweisung": $warenkorb[zahlungsweise] = "sofortueberweisung"; break;
      default: $warenkorb[zahlungsweise] = $result[data][payment][name]; 
    }

    //$warenkorb[lieferung] selbstabholer, versandunternehmen
    if($result[data][dispatch][name]=="Selbstabholung")
      $warenkorb[lieferung] = "selbstabholer";
    else
      $warenkorb[lieferung] = "versandunternehmen";

    $warenkorb[bestelldatum] = substr($result[data][orderTime],0,10);

    $warenkorb[ustid] = $result[data][billing][vatId];
    $warenkorb[telefon] = $result[data][billing][phone];
    $warenkorb[telefax] = $result[data][billing][fax];


    if($result[data][shipping][company]=="")
      $warenkorb2[lieferadresse_name] = $result[data][shipping][firstName]." ".$result[data][shipping][lastName];
    else {
      $warenkorb2[lieferadresse_name] = $result[data][shipping][company];
      $warenkorb2[lieferadresse_ansprechpartner] = $result[data][shipping][firstName]." ".$result[data][shipping][lastName];
    }

    $warenkorb2[lieferadresse_strasse] = $result[data][shipping][street]." ".$result[data][shipping][streetNumber];
    $warenkorb2[lieferadresse_plz] = $result[data][shipping][zipCode];
    $warenkorb2[lieferadresse_ort] = $result[data][shipping][city];
    $warenkorb2[lieferadresse_land] = $result[data][shipping][country][iso];
    $warenkorb2[lieferadresse_abteilung] = $result[data][shipping][department];

    if($warenkorb2[lieferadresse_name]!=$warenkorb[name] ||
       $warenkorb2[lieferadresse_ansprechpartner]!=$warenkorb[ansprechpartner] ||
       $warenkorb2[lieferadresse_strasse]!=$warenkorb[strasse] ||
       $warenkorb2[lieferadresse_plz]!=$warenkorb[plz] ||
       $warenkorb2[lieferadresse_ort]!=$warenkorb[ort] ||
       $warenkorb2[lieferadresse_land]!=$warenkorb[land] ||
       $warenkorb2[lieferadresse_abteilung]!=$warenkorb[abteilung])
    {
      $warenkorb[abweichendelieferadresse]="1";
      $warenkorb[lieferadresse_name]  = $warenkorb2[lieferadresse_name] ;
      $warenkorb[lieferadresse_ansprechpartner] = $warenkorb2[lieferadresse_ansprechpartner];
      $warenkorb[lieferadresse_strasse] = $warenkorb2[lieferadresse_strasse];
      $warenkorb[lieferadresse_plz] = $warenkorb2[lieferadresse_plz];
      $warenkorb[lieferadresse_ort] = $warenkorb2[lieferadresse_ort];
      $warenkorb[lieferadresse_land] = $warenkorb2[lieferadresse_land];
      $warenkorb[lieferadresse_abteilung] = $warenkorb2[lieferadresse_abteilung];
    } 
       
    //articlelist
        //articleid
        //quantity

    for($i=0; $i < count($result[data][details]); $i++)
    {
      $articlearray[] = array('articleid'=>$result[data][details][$i][articleNumber],
                        'name'=>$result[data][details][$i][articleName],
                          'price'=>$result[data][details][$i][price],
                          'quantity'=>$result[data][details][$i][quantity]
      );
    }
    
    foreach($articlearray as $k => $v)
    {
      $articlearray[$k]['price'] = number_format($v['price'],2,'.','');
    }
    
    $warenkorb[articlelist]=$articlearray;

    $tmp[0]['id'] = $warenkorb[auftrag];
    $tmp[0]['sessionid'];
    $tmp[0]['logdatei'];
    $tmp[0]['warenkorb'] = base64_encode(serialize($warenkorb));

    echo $this->SendResponse($tmp);
    exit;
  }

  //TODO fuer Artikelexport
  // get checksum list from onlineshop
  function ImportGetList()
  {
    $tmp = $this->app->DB->SelectArr("SELECT artikel,checksum FROM artikel");
    echo $this->SendResponse($tmp);
    exit;
  }


  // get checksum list from the files 
  function ImportGetFileList()
  {
    $tmp = $this->app->DB->SelectArr("SELECT datei, checksum FROM datei");
    echo $this->SendResponse($tmp);
    exit;
  }
 
  // get checksum list from the files 
  function ImportGetFileListArticle()
  {
    $tmp = $this->CatchRemoteCommand("data");
    $artikel = $tmp[artikel];

    $tmp = $this->app->DB->SelectArr("SELECT d.datei, d.checksum FROM datei d, datei_stichwoerter ds WHERE articleID=$artikel");
    //$tmp = $this->app->DB->SelectArr("SELECT d.datei, d.checksum FROM datei d, datei_stichwoerter ds WHERE d.datei=ds.datei AND ds.artikel=$artikel");
    echo $this->SendResponse($tmp);
    exit;
  }
 
  function ImportAuth()
  {
    $checktoken = $this->app->Conf->ImportToken;
    $result = $this->CatchRemoteCommandAES("token");
    if($result==$checktoken)
      echo $this->SendResponse("success");
    else 
      echo $this->SendResponse("failed");

    exit;
  }

  function CatchRemoteCommandAES($value)
  {
    $tmp = $this->app->Secure->GetPOST($value);

    //$z = "12345678912345678912345678912345"; // 256-bit key
    $z = $this->app->Conf->ImportKey;//"12345678912345678912345678912345"; // 256-bit key
    $aes = new AES($z);
    return unserialize($aes->decrypt(base64_decode($tmp)));
  }


  function CatchRemoteCommand($value)
  {
    $tmp = $this->app->Secure->GetPOST($value);

    return unserialize(base64_decode($tmp));
  }

  function CatchRemoteAuth()
  {
    $checktoken = $this->app->Conf->ImportToken;
    $result = $this->CatchRemoteCommandAES("token");
    if($result!=$checktoken)
    {
      $this->SendResponse("failed"); 
      exit;
    }
  }

  function SendResponse($value)
  { 
//    $this->error[]="Artikel in der Shop Datenbank nicht gefunden!";
    $this->DumpVar("BNEEN9999999999");
    $this->DumpVar($this->error);

    if(count($this->error)>0)
    { 
      return base64_encode(serialize("error: ".implode(',',$this->error)));
    } else {
      return base64_encode(serialize($value));
    }
  }
/*
  function SendResponse($value)
  {
    return base64_encode(serialize($value));
  }
*/
  function SendResponseAES($value)
  {
    $z = $this->app->Conf->ImportKey;//"12345678912345678912345678912345"; // 256-bit key
    $aes = new AES($z);
    return base64_encode($aes->encrypt(serialize($value)));
  }




}
?>
