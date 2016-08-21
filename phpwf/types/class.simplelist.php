<?php


class SimpleList
{
  var $actual = 0;
  var $items = 0; 

  var $List = array();

  function SimpleList(){}

  function Add($data)
  {
    $this->List[] = $data;
    $this->actual = $this->items;
    $this->items++;
    return TRUE; 
  }


  function &getFirst() 
  {
    $this->actual = 0;
    return $this->getActual();
  }


  function getLast() 
  {
    $last = count($this->List);
    $this->actual = $last;
    return $this->getActual();
  }


  function &getNext() 
  {
    $this->actual++;
    return $this->getActual();
  } 

  function &getActual()
  {
    if($this->actual >=0 && $this->actual < $this->items) 
      return $this->List[$this->actual];

    return FALSE;
  }

  
  function getPrev() 
  {
    $this->actual++;
    return $this->getActual();
  } 

}
?>
