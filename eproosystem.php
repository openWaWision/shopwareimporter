<?php
/* Author: Benedikt Sauter <sauter@embedded-projects.net> 2007
 *
 * Hier werden alle Plugins, Widgets usw instanziert die
 * fuer die Anwendung benoetigt werden.
 * Diese Klasse ist von class.application.php abgleitet.
 * Das hat den Vorteil, dass man dort bereits einiges starten kann,
 * was man eh in jeder Anwendung braucht.
 * - DB Verbindung
 * - Template Parser
 * - Sicherheitsmodul
 * - String Plugin
 * - usw....
 */



include("phpwf/class.application.php");

 

include('lib/class.client.php');

include("lib/class.erpapi.php");

include("lib/class.aes.php");



class erpooSystem extends Application
{
  public $obj;

  public function __construct($config,$group="") 
  {
    parent::Application($config,$group);
   
    $this->erp = new erpAPI($this);
    $this->client = new ApiClient(

      //URL des Shopware Rest Servers
      $this->Conf->ImportShopwareApiUrl,

      //Benutzername
      $this->Conf->ImportShopwareApiUser,

      //API-Key des Benutzers
      $this->Conf->ImportShopwareKey
    );
  }

  function calledWhenAuth($type)
  {
    $id = $this->Secure->GetGET("id");
    $module = $this->Secure->GetGET("module");
    //ende fenster rechts offene vorgaenge ***
  }



}







?>
