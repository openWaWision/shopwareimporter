<?php

class Exportlink 
{
  function Exportlink(&$app) 
  {
    $this->app=&$app;
    
    $this->app->ActionHandlerInit($this);

    $this->app->ActionHandler("list","ExportlinkList");

    $this->app->DefaultActionHandler("list");
    
    $this->app->ActionHandlerListen($app);
  }
  
  function ExportlinkNav()
  {
    $this->app->Tpl->Set(NAVIGATION, $this->app->erp->Navigation(0));
    //$this->app->Tpl->Parse(MESSAGEBOXLEFT, "messagebox_left.tpl");
    //$this->app->Tpl->Parse(MESSAGEBOXRIGHT,"messagebox_right.tpl");
    //$this->app->Tpl->Parse(FREIBOX, "auchgekauft.tpl");
   
    $this->app->Tpl->Set(UEBERSICHT,CartTinyShow($_SESSION[articlelist]));
    $this->app->Tpl->Parse(WARENKORB, "warenkorb.tpl");

    $this->app->Tpl->Parse(NEUERSCHEINUNGEN, "neuerscheinungen.tpl");
  }


  
  function ExportlinkList()
  {
    $this->ExportlinkNav();

    // was kann es sein / manuelle storno moeglichkeit 

    $submit = $this->app->Secure->GetPOST("submit");
    $same= $this->app->Secure->GetPOST("same");
    $reg = $this->app->Secure->GetGET("regkey");

    $check_done = $this->app->DB->Select("SELECT check_done FROM exportlink WHERE reg='$reg' LIMIT 1");
    $reg_check = $this->app->DB->Select("SELECT reg FROM exportlink WHERE reg='$reg' AND reg!='' LIMIT 1");

    if($reg == $reg_check)
      $reg_valid = 1;
    else
      $reg_valid = 0;

    if($submit =="" && $check_done==0 && $reg_valid==1)
    { 
      $artikel = $this->app->DB->Select("SELECT objekt FROM exportlink WHERE reg='$reg' LIMIT 1");
      $artikelname = $this->app->DB->Select("SELECT name_de FROM artikel WHERE artikel='$artikel' LIMIT 1");
      $nummer = $this->app->DB->Select("SELECT nummer FROM artikel WHERE artikel='$artikel' LIMIT 1");


      $this->app->Tpl->Set(ARTIKEL,$artikelname);
      $this->app->Tpl->Set(NUMMER,$nummer);
      $this->app->Tpl->Set(REG,$reg);

      $this->app->Tpl->Parse(INHALT,"exportlink.tpl");
    } else if( $reg_valid==0)
    {
      $this->app->Tpl->Set(INHALT,"<div class=\"info\">Ung&uuml;tiger Link</div>");
    } else {
      // speichern same und check_done
      $this->app->DB->Update("UPDATE exportlink SET auswahl='$same',check_done=1 WHERE reg='$reg' LIMIT 1");

      $this->app->Tpl->Set(INHALT,"<div class=\"info\">Vielen Dank f&uuml;r Ihre Mithilfe. Wir werden umgehend Ihre Bestellung weiter bearbeiten.</div>");
    }
    $this->app->Tpl->Parse(PAGE, "index.tpl");    
  }
 
 
}
?>
