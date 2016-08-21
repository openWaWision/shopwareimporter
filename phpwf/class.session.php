<?php


class Session {
  
  // set check to true when user have permissions
  private $check = false;

  public $module;
  public $action;

  // application object
  public  $app;


  function Session() 
  {


  }


  function Check($appObj)
  {
    $this->app = $appObj;
    $this->check =  true;

    //return true;
    
    $this->module = $this->app->Secure->GetGET("module");
    $this->action = $this->app->Secure->GetGET("action");

    if($this->module==""){
      $this->module="welcome";
      $this->action="main";
    }

    if(!$this->app->acl->CheckTimeOut()){
      $this->check = false;
      $this->reason = 'PLEASE_LOGIN';
    } else {
      //benutzer ist schon mal erfolgreich angemeldet
      if($this->app->acl->Check($this->app->User->GetType(),$this->module,$this->action)){
	$this->check =  true;
	$this->app->calledWhenAuth($this->app->User->GetType());
      } else {
	$this->reason = 'NO_PERMISSIONS';
	$this->check = false;
      }

    }

  }

  function GetCheck() { return $this->check; }

  function UserSessionCheck()
  {
    $this->check=false;
    $this->reason="PLEASE_LOGIN";
    //$this->reason="SESSION_TIMEOUT";
    return true;
  }


}





?>
