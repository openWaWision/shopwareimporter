<?php
/* Author: Benedikt Sauter, sauter@sistecs.de, 2007
 * Player for PHP Applications
 */

class Player {

  public $DefautTemplates;
  public $DefautTheme;

  // the application object
  public $app;

  function Player()
  {
    $this->DefautTemplates="defaulttemplates";
    $this->DefautTheme="default";
  }

  function SetDefaultTemplates($path)
  {
  }

  function SetDefaultTheme($path)
  {

  }

  function BuildNavigation()
  {
    $type = $this->app->User->GetType();
    $this->app->Page->CreateNavigation($this->app->Conf->WFconf[menu][$type]); 
  }

  function Run($sessionObj)
  {
    $this->app = $sessionObj->app;
    $module = $this->app->Secure->GetGET("module");
    $action = $this->app->Secure->GetGET("action");
    // plugin instanzieren
    // start module
    if(file_exists("pages/".$module.".php")){
      include("pages/".$module.".php");
      //create dynamical an object

      $constr=strtoupper($module{0}).substr($module, 1);
      $myApp = new $constr($this->app);
    }
    else {
      if(file_exists("pages/_gen/".$module.".php")){
	include("pages/_gen/".$module.".php");
	//create dynamical an object
	$constr="Gen".strtoupper($module{0}).substr($module, 1);
	$myApp = new $constr($this->app);
      }
      else {
	//echo "Dieses Modul gibt es nicht!";
	//echo $this->app->WFM->Error("Module <b>$module</b> doesn't exists in pages/");
      }
    }
    // jetzt noch alles anzeigen
    //$this->app->Tpl->ReadTemplatesFromPath("../../conductor/themes/default/templates/");
    //$this->app->Tpl->ReadTemplatesFromPath("../../conductor/themes/default/templates/");
    /*if($this->app->BuildNavigation==true)
      $this->BuildNavigation();

    if($this->app->BuildNavigation==true)
      echo $this->app->Tpl->FinalParse('page.tpl');
    else
      echo $this->app->Tpl->FinalParse('popup.tpl');*/
  }

}
