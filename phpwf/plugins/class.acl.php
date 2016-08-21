<?php


class Acl 
{
  //var $engine;
  function Acl(&$app)
  {
    $this->app = &$app;
  }


  function CheckTimeOut()
  {

  }

  function Check($usertype,$module,$action)
  {
    return $ret;
  }

  function Login()
  {
  }

  function Logout($msg="")
  {
  }


  function CreateAclDB()
  {

  }

}
?>
