<?php

class YUI
{

  function YUI(&$app)
  {
    $this->app = &$app;
  }


  function DateiUpload($parsetarget,$objekt,$parameter)
  {

    $speichern = $this->app->Secure->GetPOST("speichern");
    $module = $this->app->Secure->GetGET("module");
    $action = $this->app->Secure->GetGET("action");
    $id = $this->app->Secure->GetGET("id");

    if($speichern !="")
    {
      $titel= $this->app->Secure->GetPOST("titel");
      $beschreibung= $this->app->Secure->GetPOST("beschreibung");
      $stichwort= $this->app->Secure->GetPOST("stichwort");

      $this->app->Tpl->Set(TITLE,$titel);
      $this->app->Tpl->Set(BESCHREIBUNG,$beschreibung);

      if($_FILES['upload']['tmp_name']=="")
      {
        $this->app->Tpl->Set(ERROR,"<div class=\"error\">Keine Datei ausgew&auml;hlt!</div>");
      } else {
        $fileid = $this->app->erp->CreateDatei($_FILES['upload']['name'],$titel,$beschreibung,"",$_FILES['upload']['tmp_name'],$this->app->User->GetName());

        // stichwoerter hinzufuegen
        $this->app->erp->AddDateiStichwort($fileid,$stichwort,$objekt,$parameter);
        header("Location: index.php?module=$module&action=$action&id=$id");
      }

    }

    $this->app->Tpl->Set(SUBSUBHEADING,"Dateien");
    $table = new EasyTable($this->app);
    $table->Query("SELECT titel, nummer, id FROM datei");

    $table->DisplayNew(INHALT,"edit");
  
    $this->app->Tpl->Parse(TAB1,"rahmen70.tpl");



    $this->app->Tpl->Parse(TAB2,"datei_neudirekt.tpl");


    $this->app->Tpl->Set(AKTIV_TAB1,"selected");
    $this->app->Tpl->Parse($parsetarget,"dateienuebersicht.tpl");


  } 


  function SortList($parsetarget,&$ref,$menu,$sql,$sort=true)
  {
    $module = $this->app->Secure->GetGET("module");
    $id = $this->app->Secure->GetGET("id");

    $table = new EasyTable($this->app);
    if($sort)
      $table->Query($sql." ORDER by sort"); 
    else
      $table->Query($sql); 

    foreach($menu as $key=>$value)
    {

      // im popup öffnen
      if($key=="add" || $key=="del")
	$tmp .= "&nbsp;<a href=\"index.php?module=$module&action=$value&id=%value%&frame=false&pid=$id\" onclick=\"makeRequest(this);return false\">$key</a>";
      else if($key=="edit")
	$tmp .= "&nbsp;<a href=\"index.php?module=$module&action=$value&id=%value%&frame=false&pid=$id\" onclick=\"makeRequest(this);return false\">$key</a>";

      // nur aktion ausloesen und liste neu anzeigen
      else
	$tmp .= "&nbsp;<a href=\"index.php?module=$module&action=$value&sid=%value%&id=$id\">$key</a>";
    }
    $table->DisplayNew($parsetarget, $tmp);
  }

  function SortListEvent($event,$table,$fremdschluesselindex)
  {
    $sid = $this->app->Secure->GetGET("sid");
    $id = $this->app->Secure->GetGET("id");
    
    $sort = $this->app->DB->Select("SELECT sort FROM $table WHERE id='$sid' LIMIT 1");   

    if($event=="up")
    {
      //gibt es ein element an hoeherer stelle?
      $nextsort = $this->app->DB->Select("SELECT sort FROM $table WHERE $fremdschluesselindex='$id' AND sort ='".($sort+1)."' LIMIT 1");
      if($nextsort > $sort)
      {
	$nextid = $this->app->DB->Select("SELECT id FROM $table WHERE $fremdschluesselindex='$id' AND sort = '".($sort+1)."' LIMIT 1");
	$this->app->DB->Update("UPDATE $table SET sort='$nextsort' WHERE id='$sid' LIMIT 1");
	$this->app->DB->Update("UPDATE $table SET sort='$sort' WHERE id='$nextid' LIMIT 1");
      } else {
	// element ist bereits an oberster stelle
      }
    }
    else if($event=="down")
    {
      //gibt es ein element an hoeherer stelle?
      $prevsort = $this->app->DB->Select("SELECT sort FROM $table WHERE $fremdschluesselindex='$id' AND sort = '".($sort-1)."' LIMIT 1");
      if($prevsort < $sort && $prevsort!=0)
      {
	$previd = $this->app->DB->Select("SELECT id FROM $table WHERE $fremdschluesselindex='$id' AND sort = '".($sort-1)."' LIMIT 1");
	$this->app->DB->Update("UPDATE $table SET sort='$prevsort' WHERE id='$sid' LIMIT 1");
	$this->app->DB->Update("UPDATE $table SET sort='$sort' WHERE id='$previd' LIMIT 1");
      } else {
	// element ist bereits an oberster stelle
      }
    }
    else {}

  }

  function IframeDialog($width,$height)
  {
    $id = $this->app->Secure->GetGET("id");
    $module = $this->app->Secure->GetGET("module");
    $action = $this->app->Secure->GetGET("action");
    $this->app->Tpl->Set(PAGE,"<iframe width=\"$width\"  height=\"$height\" frameborder=\"0\" src=\"index.php?module=$module&action=$action&id=$id\"></iframe>");
    $this->app->BuildNavigation=false;

  }

  function Dialog($table,$parsetarget,$ueberschrift,$index_beschriftung,$formtemplate,&$object_for_function, $function_for_content,$width=320)
  {

    for($i=0; $i < count($table->datasets); $i++){
      $id = $table->datasets[$i][id]; 
      $beschriftung = $table->datasets[$i][$index_beschriftung]; 
      $js .=  '
       // Instantiate a Panel from markup
       YAHOO.example.container.panel'.$id.' = new YAHOO.widget.Panel("panel'.$id.'", { width:"'.$width.'px", visible:false, constraintoviewport:true } );
       YAHOO.example.container.panel'.$id.'.render();

       YAHOO.util.Event.addListener("show'.$id.'", "click", YAHOO.example.container.panel'.$id.'.show, YAHOO.example.container.panel'.$id.', true);
       YAHOO.util.Event.addListener("hide'.$id.'", "click", YAHOO.example.container.panel'.$id.'.hide, YAHOO.example.container.panel'.$id.', true);';

      $yui_html = '
       <div><a id="show'.$id.'">Details</a></div>
       <div id="panel'.$id.'"><div class="hd">'.$ueberschrift.' '.$beschriftung.'</div> 
       <div class="bd">[PANEL'.$id.']</div> 
       <div class="ft" align="right"><input type="submit" value="OK"></div> 
       </div>';

    if($i==0)
      $this->app->Tpl->Set(DETAILS.$id,'<div id="container">'.$yui_html);
    else if($i==count($table->datasets)-1)
      $this->app->Tpl->Set(DETAILS.$id,$yui_html."</div>");
    else
      $this->app->Tpl->Set(DETAILS.$id,$yui_html);

    // aufrufen der uebergebenen funktion
    $object_for_function->$function_for_content($id,$this);

    // formular parsen
    $this->app->Tpl->Parse(PANEL.$id,$formtemplate);
  }
    $this->app->Tpl->Add(YUI,$js);
  }


  function AutoComplete($parsetarget,$name,$cols,$returncol,$table="")
  {

    $tpl_start = '
	<!--begin custom header content for this example-->
	<style type="text/css">
	#myAutoComplete'.$name.' {
	    width:100%; /* set width here or else widget will expand to fit its container */
	    padding-bottom:2em;
	}
	.match {
	    font-weight:bold;
	}
	</style>


	<div id="myAutoComplete'.$name.'">
	    ';

      $tpl_end = '
	<div id="myContainer'.$name.'"></div>
	</div>

	<script type="text/javascript">
	YAHOO.example.FnMultipleFields = function(){
	    var myContacts'.$name.' = [
      ';


      $colsstring = implode(',',$cols);
      
      if($name=="lieferant")
      {
	$arr = $this->app->DB->SelectArr("SELECT $colsstring, $returncol FROM adresse ORDER by 1"); 
      } else {
	if($table!="")
	  $arr = $this->app->DB->SelectArr("SELECT $colsstring, $returncol FROM $table ORDER by 1"); 
	else
	  $arr = $this->app->DB->SelectArr("SELECT $colsstring, $returncol FROM $name ORDER by 1"); 
      }

      foreach($arr as $key=>$value){
	$tpl_end .= '{id:"'.$value[$returncol].'", cola:"'.$value[$cols[0]].'", colb:"'.$value[$cols[1]].'", colc:"'.$value[$cols[2]].'"},';
      }

    $tpl_end .= '
    {id:"0", cola:"", colb:"", colc:""}
    ];

   // Define a custom search function for the DataSource
    var matchNames'.$name.' = function(sQuery) {
        // Case insensitive matching
        var query = sQuery.toLowerCase(),
            contact,
            i=0,
            l=myContacts'.$name.'.length,
            matches = [];
        
        // Match against each name of each contact
        for(; i<l; i++) {
            contact = myContacts'.$name.'[i];
            if((contact.cola.toLowerCase().indexOf(query) > -1) ||
                (contact.colb.toLowerCase().indexOf(query) > -1) ||
                (contact.colc && (contact.colc.toLowerCase().indexOf(query) > -1))) {
                matches[matches.length] = contact;
            }
        }
        return matches;
    };
 
    // Use a FunctionDataSource
    var oDS = new YAHOO.util.FunctionDataSource(matchNames'.$name.');
    oDS.responseSchema = {
        fields: ["id", "cola", "colb", "colc"]
    }

    // Instantiate AutoComplete
    var oAC = new YAHOO.widget.AutoComplete("'.$name.'", "myContainer'.$name.'", oDS);
    oAC.useShadow = true;
    oAC.autoHighlight= false;
    oAC.useIFrame= true;
    oAC.resultTypeList = false;
    
    
    // Custom formatter to highlight the matching letters
    oAC.formatResult = function(oResultData, sQuery, sResultMatch) {
        var query = sQuery.toLowerCase(),
            cola = oResultData.cola,
            colb = oResultData.colb,
            colc = oResultData.colc || "", // Guard against null value
            query = sQuery.toLowerCase(),
            colaMatchIndex = cola.toLowerCase().indexOf(query),
            colbMatchIndex = colb.toLowerCase().indexOf(query),
            colcMatchIndex = colc.toLowerCase().indexOf(query),
            displaycola, displaycolb, displaycolc;

    if(colaMatchIndex > -1) {
            displaycola = highlightMatch(cola, query, colaMatchIndex);
        }
        else {
            displaycola = cola;
        }

        if(colbMatchIndex > -1) {
            displaycolb = highlightMatch(colb, query, colbMatchIndex);
        }
        else {
            displaycolb = colb;
        }

        if(colcMatchIndex > -1) {
            displaycolc = "(" + highlightMatch(colc, query, colcMatchIndex) + ")";
        }
        else {
            displaycolc = colc ? "(" + colc + ")" : "";
        }

        return displaycola + " " + displaycolb + " " + displaycolc;
        
    };
    
    // Helper function for the formatter
    var highlightMatch = function(full, snippet, matchindex) {
        return full.substring(0, matchindex) + 
                "<span class=\'match\'>" + 
                full.substr(matchindex, snippet.length) + 
                "</span>" +
                full.substring(matchindex + snippet.length);
    };

    // when an item gets selected and populate the input field
    var myHandler = function(sType, aArgs) {
        var myAC = aArgs[0]; // reference back to the AC instance
        var elLI = aArgs[1]; // reference to the selected LI element
        var oData = aArgs[2]; // object literal of selected items result data
        
        
        myAC.getInputEl().value = oData.id;
    };
    oAC.itemSelectEvent.subscribe(myHandler);
    
    return {
        oDS: oDS,
        oAC: oAC 
    };
}();
</script>';

    $this->app->Tpl->Add($parsetarget.START,$tpl_start);
    $this->app->Tpl->Add($parsetarget."END",$tpl_end);
  }

}
?>
