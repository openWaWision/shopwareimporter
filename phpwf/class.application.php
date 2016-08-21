<?php

//include ("phpwf/engine/class.engine.php");
//include ("phpwf/plugins/class.formhandler.php");
include ("phpwf/plugins/class.acl.php");
include ("phpwf/plugins/class.user.php");
include ("phpwf/plugins/class.page.php");
include ("phpwf/plugins/class.phpwfapi.php");
include ("phpwf/plugins/class.databaseform.php");
//include ("phpwf/plugins/class.templateparser.php");
include ("phpwf/plugins/class.secure.php");
include ("phpwf/plugins/class.db.php");
include ("phpwf/plugins/class.wfmonitor.php");
include ("phpwf/plugins/class.string.php");
//include ("phpwf/plugins/class.pagebuilder.php");
include ("phpwf/plugins/class.objectapi.php");
//include ("phpwf/plugins/class.widgetapi.php");
//include ("phpwf/widgets/easytable.php");
//include ("phpwf/widgets/grouptable.php");
//include ("phpwf/widgets/childtable.php");
//include ("phpwf/widgets/table.php");


//include("phpwf/htmltags/all.php");
include("phpwf/types/class.simplelist.php");


class Application
{

    var $ActionHandlerList;
    var $ActionHandlerDefault;

    function Application($config,$group="")
    {
      //session_cache_limiter('private');

      ini_set('session.gc_maxlifetime', 3600*8);
      ini_set('session.gc_divisor', 1);

      session_start();

      $this->Conf= $config;

      if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=="on")
	$this->http = "https";
      else
	$this->http = "http";

    
      $this->Secure         =  new Secure();   // empty $_GET, and $_POST so you
                                                // have to need the secure layer always
      //$this->FormHandler    =  new FormHandler($this);
      $this->User           =  new User($this);
      $this->acl            =  new Acl($this);
      $this->WF             =  new phpWFAPI($this);
      $this->WFM            =  new WFMonitor($this);
      //$this->Tpl            =  new TemplateParser();
      $this->Page           =  new Page($this);
      $this->String         =  new String();
      $this->DatabaseForm   =  new DatabaseForm($this);
      //$this->PageBuilder    =  new PageBuilder($this);
      $this->ObjAPI	    =  new ObjectAPI($this);
      //$this->Widget	    =  new WidgetAPI($this);
      //$this->Table	    =  new Table($this);

      //$this->BuildNavigation = true;
         
      if($this->Conf->WFdbname!="") 
        $this->DB             = new DB($this->Conf->WFdbhost,$this->Conf->WFdbname,$this->Conf->WFdbuser,$this->Conf->WFdbpass,$this);

      //$this->Tpl->ReadTemplatesFromPath("phpwf/widgets/templates/");

    }

    function ActionHandlerInit(&$caller)
    {
      $this->caller=&$caller;
    }

 
    function ActionHandler($command,$function)
    {
      $this->ActionHandlerList[$command]=$function; 
    }
    
    function DefaultActionHandler($command)
    {
      $this->ActionHandlerDefault=$command;
    }

   
    function ActionHandlerListen(&$app)
    {
      $action = $app->Secure->GetGET("action","alpha");
      if($action!="")
	$fkt = $this->ActionHandlerList[$action];
      else
	$fkt = $this->ActionHandlerList[$this->ActionHandlerDefault];


      // check permissions
      @$this->caller->$fkt();
    }

    
}

?>
